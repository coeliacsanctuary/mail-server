<?php

declare(strict_types=1);

namespace Tests\Feature\Newsletter\Components;

use App\Editor\Support\NewsletterCompiler;
use App\Livewire\Newsletter\Editable\Components\Blog;
use App\Livewire\Newsletter\Editable\Components\Product;
use App\Livewire\Newsletter\Editable\Components\Recipe;
use Illuminate\Support\Facades\Http;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\ComponentData;
use Tests\Support\NewsletterBuilder;
use Tests\TestCase;

/**
 * Blog, Recipe and Product are three copies of the same component. Testing
 * them through one data provider states that up front - and once they share a
 * base class this provider should collapse to little more than a class list.
 */
class SearchableComponentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->fakeCoeliacApi();
    }

    /** @return array<string, array{array<string, mixed>}> */
    public static function searchableComponentProvider(): array
    {
        return [
            'blog' => [[
                'class' => Blog::class,
                'idProperty' => 'blogId',
                'selectMethod' => 'selectBlog',
                'indexEndpoint' => 'api/blogs',
                'showEndpoint' => 'api/blogs/1',
                'properties' => ComponentData::blog(),
            ]],
            'recipe' => [[
                'class' => Recipe::class,
                'idProperty' => 'recipeId',
                'selectMethod' => 'selectRecipe',
                'indexEndpoint' => 'api/recipes',
                'showEndpoint' => 'api/recipes/2',
                'properties' => ComponentData::recipe(),
            ]],
            'product' => [[
                'class' => Product::class,
                'idProperty' => 'productId',
                'selectMethod' => 'selectProduct',
                // The odd one out: a flat "data" envelope, not "data.data".
                'indexEndpoint' => 'api/shop/products',
                'showEndpoint' => 'api/shop/products/3',
                'properties' => ComponentData::product(),
            ]],
        ];
    }

    /** @param array<string, mixed> $properties */
    private function mountComponent(string $class, array $properties = [], string $block = 'single'): Testable
    {
        return Livewire::test($class, [
            'blockId' => 'block-1',
            'block' => $block,
            'index' => 0,
            'properties' => $properties,
        ]);
    }

    #[DataProvider('searchableComponentProvider')]
    public function test_it_mounts_empty_without_calling_the_api(array $component): void
    {
        $this->mountComponent($component['class'])->assertOk();

        Http::assertNothingSent();
    }

    #[DataProvider('searchableComponentProvider')]
    public function test_it_fetches_the_stored_item_on_mount(array $component): void
    {
        $this->mountComponent($component['class'], $component['properties'])
            ->assertSet($component['idProperty'], $component['properties']['content']);

        Http::assertSent(
            fn ($request) => $request->url() === "https://coeliac.invalid/{$component['showEndpoint']}",
        );
    }

    /**
     * A single-column block shows the long description; a double or triple
     * shows the short one, because there is less room.
     */
    #[DataProvider('searchableComponentProvider')]
    public function test_a_single_block_uses_the_long_description(array $component): void
    {
        $properties = $component['properties'];
        unset($properties['description']);

        $this->mountComponent($component['class'], $properties, 'single')
            ->assertSet('description', fn (string $value) => str_contains($value, 'long'));
    }

    #[DataProvider('searchableComponentProvider')]
    public function test_a_double_block_uses_the_short_description(array $component): void
    {
        $properties = $component['properties'];
        unset($properties['description']);

        $this->mountComponent($component['class'], $properties, 'double')
            ->assertSet('description', fn (string $value) => str_contains($value, 'short'));
    }

    #[DataProvider('searchableComponentProvider')]
    public function test_a_stored_description_wins_over_the_api(array $component): void
    {
        $properties = $component['properties'];
        $properties['description'] = 'Hand written by the editor.';

        $this->mountComponent($component['class'], $properties)
            ->assertSet('description', 'Hand written by the editor.');
    }

    /**
     * Pins the envelope inconsistency: blogs and recipes are paginated
     * ("data.data"), products are not ("data"). Make it explicit before
     * unifying these three.
     */
    #[DataProvider('searchableComponentProvider')]
    public function test_searching_maps_the_response_into_results(array $component): void
    {
        $this->mountComponent($component['class'])
            ->set('search', 'gluten')
            ->assertSet('results', fn ($results) => $results->count() === 1);

        Http::assertSent(
            fn ($request) => $request->url() === "https://coeliac.invalid/{$component['indexEndpoint']}?search=gluten",
        );
    }

    /**
     * The early return in updated() is why typing in the search box does not
     * overwrite the saved block. Any refactor of that method must keep it.
     */
    #[DataProvider('searchableComponentProvider')]
    public function test_searching_does_not_persist_anything(array $component): void
    {
        $this->mountComponent($component['class'])
            ->set('search', 'gluten')
            ->assertNotDispatched('component-updated');
    }

    #[DataProvider('searchableComponentProvider')]
    public function test_selecting_a_result_clears_the_search_and_persists(array $component): void
    {
        $this->mountComponent($component['class'])
            ->set('search', 'gluten')
            ->call($component['selectMethod'], $component['properties']['content'])
            ->assertSet($component['idProperty'], $component['properties']['content'])
            ->assertSet('search', '')
            ->assertDispatched('component-updated');
    }

    #[DataProvider('searchableComponentProvider')]
    public function test_editing_the_description_persists_the_whole_property_set(array $component): void
    {
        $this->mountComponent($component['class'], $component['properties'])
            ->set('description', 'A new description.')
            ->assertDispatched(
                'component-updated',
                fn ($event, $params) => $params[0] === 'block-1'
                    && $params[2] === 0
                    && $params[1]['description'] === 'A new description.'
                    && $params[1]['title'] === $component['properties']['title'],
            );
    }

    /**
     * BUG PIN. remove() nulls the id but never clears the DTO, and updated()
     * rebuilds the property bag from it - so title, image, created_at and link
     * are re-persisted unchanged. Only "content" and "description" are cleared.
     */
    #[DataProvider('searchableComponentProvider')]
    public function test_remove_leaves_the_old_title_image_and_link_behind(array $component): void
    {
        $properties = $component['properties'];

        $this->mountComponent($component['class'], $properties)
            ->call('remove')
            ->assertSet($component['idProperty'], null)
            ->assertSet('description', '')
            ->assertDispatched(
                'component-updated',
                fn ($event, $params) => $params[1]['content'] === null
                    && $params[1]['description'] === ''
                    && $params[1]['title'] === $properties['title']
                    && $params[1]['image'] === $properties['image']
                    && $params[1]['link'] === $properties['link'],
            );
    }

    /**
     * And the user-visible consequence: the editor shows an empty search box,
     * but the newsletter still renders the removed item. This is the assertion
     * that should flip when the bug is fixed.
     */
    public function test_a_removed_blog_still_renders_in_the_email(): void
    {
        $removed = [];

        $this->mountComponent(Blog::class, ComponentData::blog())
            ->call('remove')
            ->assertDispatched('component-updated', function ($event, $params) use (&$removed) {
                $removed = $params[1];

                return true;
            });

        $mjml = (new NewsletterCompiler(
            NewsletterBuilder::make()->single()->with('blog', $removed)->contentItem(),
        ))->renderMjml();

        $this->assertMjmlContains('A Gluten Free Blog', $mjml);
        $this->assertMjmlContains('src="https://coeliac.invalid/images/blog.jpg"', $mjml);
    }
}

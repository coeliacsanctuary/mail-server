<?php

declare(strict_types=1);

namespace Tests\Feature\Editor;

use App\Editor\Support\NewsletterCompiler;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\ComponentData;
use Tests\Support\NewsletterBuilder;
use Tests\TestCase;

/**
 * Every component must survive being rendered with no properties at all -
 * the state Editor::addComponent() creates when you pick a component from the
 * modal and never touch its fields.
 *
 * These cases used to throw. button, text-with-button, product, and recipe in
 * single-column blocks each read an array key with no fallback, so choosing a
 * component and hitting save without filling it in produced a 500.
 */
class NewsletterCompilerMissingPropertiesTest extends TestCase
{
    /** @param array<string, mixed> $properties */
    private function compile(string $component, string $block, array $properties): string
    {
        $builder = NewsletterBuilder::make();
        $block === 'single' ? $builder->single() : $builder->double();

        return (new NewsletterCompiler($builder->with($component, $properties)->contentItem()))->renderMjml();
    }

    #[DataProvider('everyComponentProvider')]
    public function test_a_component_with_no_properties_renders_without_throwing(string $component): void
    {
        foreach (['single', 'double'] as $block) {
            $mjml = $this->compile($component, $block, []);

            $this->assertStringContainsString('<mj-body', $mjml);
        }
    }

    /** Every name the add-component modal offers. */
    public static function everyComponentProvider(): array
    {
        return array_map(
            fn (string $component) => [$component],
            [
                'title', 'title-with-text', 'subtitle', 'button', 'text', 'text-with-button',
                'hr', 'image', 'image-with-button', 'blog', 'recipe', 'product', 'eatery',
            ],
        );
    }

    #[DataProvider('partialPropertiesProvider')]
    public function test_a_component_with_partial_properties_renders_without_throwing(
        string $component,
        array $properties,
    ): void {
        $mjml = $this->compile($component, 'single', $properties);

        $this->assertStringContainsString('<mj-body', $mjml);
    }

    public static function partialPropertiesProvider(): array
    {
        return [
            'button with only a label' => ['button', ['content' => 'Read more']],
            'text with button with only content' => ['text-with-button', ['content' => 'Some text']],
            'recipe with a link but no title' => ['recipe', ['content' => 2, 'link' => 'https://coeliac.invalid']],
            'product with a link but no price' => ['product', ['content' => 3, 'link' => 'https://coeliac.invalid']],
            'eatery with an incomplete reviews array' => ['eatery', ['reviews' => ['number' => 3]]],
        ];
    }

    /**
     * The searchable components render nothing at all when no item is
     * selected, rather than an image with an empty src and a button pointing
     * nowhere. This is what makes Remove actually remove things.
     */
    #[DataProvider('searchableComponentProvider')]
    public function test_a_searchable_component_with_nothing_selected_renders_nothing(string $component): void
    {
        $mjml = $this->compile($component, 'single', ['content' => null]);

        // Only the header logo remains.
        $this->assertSame(1, mb_substr_count($mjml, '<mj-image'));
        $this->assertMjmlNotContains('Read more', $mjml);
        $this->assertMjmlNotContains('View Product', $mjml);
    }

    public static function searchableComponentProvider(): array
    {
        return [
            'blog' => ['blog'],
            'recipe' => ['recipe'],
            'product' => ['product'],
        ];
    }

    public function test_image_with_no_properties_renders_nothing_at_all(): void
    {
        $mjml = $this->compile('image', 'single', []);

        $this->assertSame(1, mb_substr_count($mjml, '<mj-image'));
        $this->assertMjmlContains('<mj-section> <mj-column> </mj-column> </mj-section>', $mjml);
    }

    /**
     * Title, subtitle and title-with-text are guarded, but with a placeholder
     * that would ship to subscribers rather than an empty string.
     */
    public function test_title_with_no_properties_renders_a_visible_placeholder(): void
    {
        $this->assertMjmlContains('[MISSING TITLE]', $this->compile('title', 'single', []));
        $this->assertMjmlContains('[MISSING SUBTITLE]', $this->compile('subtitle', 'single', []));
    }

    /**
     * A link that was typed and then cleared is stored as '', and isset('') is
     * true - which used to wrap the heading in <a href="">. An empty href
     * resolves to the current URL in most mail clients.
     */
    public function test_an_empty_link_does_not_render_an_anchor(): void
    {
        $mjml = $this->compile('title', 'single', ComponentData::title(['link' => '']));

        $this->assertMjmlContains('<h1> A Newsletter Title </h1>', $mjml);
        $this->assertMjmlNotContains('<a href="">', $mjml);
    }

    public function test_a_real_link_still_renders_an_anchor(): void
    {
        $mjml = $this->compile('title', 'single', ComponentData::title(['link' => 'https://coeliac.invalid']));

        $this->assertMjmlContains('<a href="https://coeliac.invalid"> A Newsletter Title </a>', $mjml);
    }
}

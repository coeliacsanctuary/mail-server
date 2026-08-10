<?php

declare(strict_types=1);

namespace Tests\Feature\Editor;

use App\Editor\Support\NewsletterCompiler;
use Illuminate\View\ViewException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\ComponentData;
use Tests\Support\NewsletterBuilder;
use Tests\TestCase;

/**
 * Pins the crash paths.
 *
 * Editor::addComponent() stores a component with `properties: []`, and four
 * rendered views read array keys without a `??` fallback. Adding one of those
 * components and saving without ever touching a field throws while rendering
 * the MJML.
 *
 * Assertions are on ViewException plus a message pattern rather than a bare
 * Throwable, so that fixing the views visibly flips these tests rather than
 * quietly passing for a different reason.
 */
class NewsletterCompilerMissingPropertiesTest extends TestCase
{
    private function compile(string $component, string $block, array $properties): string
    {
        $builder = NewsletterBuilder::make();
        $block === 'single' ? $builder->single() : $builder->double();

        return (new NewsletterCompiler($builder->with($component, $properties)->contentItem()))->renderMjml();
    }

    #[DataProvider('crashingComponentProvider')]
    public function test_a_component_with_missing_properties_throws(
        string $component,
        string $block,
        array $properties,
        string $expectedMessage,
    ): void {
        $this->expectException(ViewException::class);
        $this->expectExceptionMessageMatches($expectedMessage);

        $this->compile($component, $block, $properties);
    }

    public static function crashingComponentProvider(): array
    {
        return [
            'button with no properties' => [
                'button', 'single', [], '/Undefined array key "link"/',
            ],
            'text with button with no properties' => [
                'text-with-button', 'single', [], '/Undefined array key "content"/',
            ],
            'product with no properties' => [
                'product', 'single', [], '/Undefined array key "link"/',
            ],
            // recipe.blade.php:19 is the only unguarded access in that view,
            // and it only renders for single-column blocks.
            'recipe with no title in a single block' => [
                'recipe', 'single', ['link' => 'https://coeliac.invalid'], '/Undefined array key "title"/',
            ],
            'eatery with an incomplete reviews array' => [
                'eatery', 'single', ['reviews' => ['number' => 3]], '/Undefined array key "average"/',
            ],
        ];
    }

    /**
     * The contrast that makes the recipe case precise: the crash is
     * single-column only, and the fix should preserve that distinction.
     */
    public function test_recipe_with_no_title_renders_fine_in_a_double_block(): void
    {
        $mjml = $this->compile('recipe', 'double', ['link' => 'https://coeliac.invalid']);

        $this->assertMjmlContains('<mj-column css-class="double-0">', $mjml);
    }

    /**
     * And the shape the crashing views should converge on - blog guards every
     * access with ?? and degrades to empty attributes.
     */
    public function test_blog_with_no_properties_renders_without_crashing(): void
    {
        $mjml = $this->compile('blog', 'single', []);

        $this->assertMjmlContains('<mj-image href="" src="" fluid-on-mobile="true">', $mjml);
        $this->assertMjmlContains('> Read more </mj-button>', $mjml);
    }

    /**
     * Image is the one component that vanishes silently instead of crashing:
     * the whole view is wrapped in @isset($properties['content']).
     */
    public function test_image_with_no_properties_renders_nothing_at_all(): void
    {
        $mjml = $this->compile('image', 'single', []);

        // Only the header logo remains; the component's column is left empty.
        $this->assertSame(1, mb_substr_count($mjml, '<mj-image'));
        $this->assertMjmlContains('<mj-section> <mj-column> </mj-column> </mj-section>', $mjml);
    }

    /**
     * Title, subtitle and title-with-text are guarded, but with a placeholder
     * that would ship to subscribers rather than an empty string.
     */
    public function test_title_with_no_properties_renders_a_visible_placeholder(): void
    {
        $mjml = $this->compile('title', 'single', []);

        $this->assertMjmlContains('[MISSING TITLE]', $mjml);
    }

    /**
     * Components persist link as '' once a field has been touched and cleared,
     * and @isset is true for '' - so an empty link becomes an anchor with an
     * empty href rather than no anchor at all.
     */
    public function test_an_empty_link_still_renders_an_anchor(): void
    {
        $mjml = $this->compile('title', 'single', ComponentData::title(['link' => '']));

        $this->assertMjmlContains('<a href=""> A Newsletter Title </a>', $mjml);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Editor;

use App\Editor\Support\NewsletterCompiler;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\ComponentData;
use Tests\Support\NewsletterBuilder;
use Tests\TestCase;

class NewsletterCompilerMissingPropertiesTest extends TestCase
{
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

    #[DataProvider('searchableComponentProvider')]
    public function test_a_searchable_component_with_nothing_selected_renders_nothing(string $component): void
    {
        $mjml = $this->compile($component, 'single', ['content' => null]);

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

    public function test_title_with_no_properties_renders_a_visible_placeholder(): void
    {
        $this->assertMjmlContains('[MISSING TITLE]', $this->compile('title', 'single', []));
        $this->assertMjmlContains('[MISSING SUBTITLE]', $this->compile('subtitle', 'single', []));
    }

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

<?php

declare(strict_types=1);

namespace Tests\Feature\Editor;

use App\Editor\Editor;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\NewsletterBuilder;
use Tests\TestCase;

class EditorChromeTest extends TestCase
{
    private function html(string $component = 'hr'): string
    {
        $contentItem = NewsletterBuilder::make()
            ->single()->with($component)
            ->single()->with('hr')
            ->create();

        return html_entity_decode(
            Livewire::test(Editor::class, ['model' => $contentItem])->html(),
        );
    }

    public function test_the_move_chevrons_are_focusable_buttons(): void
    {
        $html = $this->html();

        foreach (['up', 'down'] as $direction) {
            $this->assertMatchesRegularExpression(
                '/<button[^>]*type="button"[^>]*wire:key="block-1-actions-' . $direction . '"/s',
                $html,
            );
        }
    }

    public function test_the_chevron_is_disabled_at_the_boundary(): void
    {
        $html = $this->html();

        $this->assertMatchesRegularExpression(
            '/wire:key="block-1-actions-up".*?disabled/s',
            $html,
        );

        $this->assertStringNotContainsString("moveBlock('block-1', 'up')", $html);
        $this->assertStringContainsString("moveBlock('block-1', 'down')", $html);
    }

    public function test_the_toolbar_is_not_positioned_with_an_inline_offset(): void
    {
        $html = $this->html();

        $this->assertStringContainsString('class="block-actions"', $html);
        $this->assertDoesNotMatchRegularExpression('/style="[^"]*right:\s*-/', $html);
    }

    public function test_every_toolbar_control_has_an_accessible_name(): void
    {
        $html = $this->html();

        foreach (['Move block up', 'Move block down', 'Duplicate block', 'Add a block below'] as $label) {
            $this->assertStringContainsString('aria-label="' . $label . '"', $html);
        }

        $this->assertStringContainsString('<span class="visually-hidden">Delete block</span>', $html);
    }

    #[DataProvider('labelledComponents')]
    public function test_every_component_renders_its_name(string $component, string $expected): void
    {
        $this->fakeCoeliacApi();

        $this->assertStringContainsString($expected, $this->html($component));
    }

    public static function labelledComponents(): array
    {
        return [
            'hr' => ['hr', 'Horizontal rule'],
            'eatery' => ['eatery', 'Eatery'],
            'blog' => ['blog', 'Blog'],
            'recipe' => ['recipe', 'Recipe'],
            'product' => ['product', 'Product'],
            'title' => ['title', 'Title'],
            'text' => ['text', 'Text'],
        ];
    }
}

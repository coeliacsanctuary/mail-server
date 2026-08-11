<?php

declare(strict_types=1);

namespace Tests\Feature\Editor;

use App\Editor\Editor;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\NewsletterBuilder;
use Tests\TestCase;

/**
 * Guards on the editor's chrome — the toolbar and the per-component labels.
 *
 * Deliberately narrow. Asserting on presentation is brittle, so this only
 * covers the things whose regression is SILENT: markup that still renders and
 * still looks roughly right while having quietly lost a capability. Anything
 * you would notice immediately in the browser is on the manual checklist
 * instead, not in here.
 */
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

    /**
     * The chevrons are the only keyboard route to reordering — Sortable has no
     * keyboard support. They spent a while as <div wire:click>, which is not
     * focusable, so the comment claiming that was untrue.
     */
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

    /** The first block cannot move up, and the browser should enforce it too. */
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

    /**
     * The toolbar used to hang off style="right: -200px", which overlapped the
     * block's own inputs by 47px at every width and pushed the last two buttons
     * off-screen below ~1280px. It now sits in the gutter .newsletter reserves,
     * positioned entirely from CSS.
     */
    public function test_the_toolbar_is_not_positioned_with_an_inline_offset(): void
    {
        $html = $this->html();

        $this->assertStringContainsString('class="block-actions"', $html);
        $this->assertDoesNotMatchRegularExpression('/style="[^"]*right:\s*-/', $html);
    }

    /**
     * Every icon-only control needs a name. The toolbar's plain buttons carry
     * aria-label; the two confirm-buttons cannot (x-mailcoach::confirm-button
     * forwards only `class` to its inner <button>, so an aria-label would land
     * on the wrapping <form>) and use a visually hidden span instead.
     */
    public function test_every_toolbar_control_has_an_accessible_name(): void
    {
        $html = $this->html();

        foreach (['Move block up', 'Move block down', 'Duplicate block', 'Add a block below'] as $label) {
            $this->assertStringContainsString('aria-label="' . $label . '"', $html);
        }

        $this->assertStringContainsString('<span class="visually-hidden">Delete block</span>', $html);
    }

    /**
     * Five of the thirteen components rendered no label at all, so a column
     * holding one gave no clue what it was.
     */
    #[DataProvider('labelledComponents')]
    public function test_every_component_renders_its_name(string $component, string $expected): void
    {
        $this->fakeCoeliacApi();

        $this->assertStringContainsString($expected, $this->html($component));
    }

    /** @return array<string, array{string, string}> */
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

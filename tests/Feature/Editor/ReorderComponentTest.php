<?php

declare(strict_types=1);

namespace Tests\Feature\Editor;

use App\Editor\Editor;
use Livewire\Livewire;
use Tests\Support\ComponentData;
use Tests\Support\Concerns\ReadsStructuredHtml;
use Tests\Support\NewsletterBuilder;
use Tests\TestCase;

class ReorderComponentTest extends TestCase
{
    use ReadsStructuredHtml;

    private function stackedColumn(): object
    {
        return NewsletterBuilder::make()
            ->single()->with('title', ComponentData::title())
            ->create();
    }

    private function withStack(): object
    {
        $contentItem = $this->stackedColumn();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('addComponent', 'block-1', ['hr', 'button'], 0);

        return $contentItem;
    }

    public function test_it_moves_a_component_up_its_column(): void
    {
        $contentItem = $this->withStack();

        $buttonId = $this->componentsAt($contentItem, 0, 0)[2]['id'];

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('reorderComponent', $buttonId, 0);

        $this->assertSame(
            ['button', 'title', 'hr'],
            array_column($this->componentsAt($contentItem, 0, 0), 'name'),
        );
    }

    public function test_a_position_past_the_end_is_ignored(): void
    {
        $contentItem = $this->withStack();

        $titleId = $this->componentsAt($contentItem, 0, 0)[0]['id'];

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('reorderComponent', $titleId, 3);

        $this->assertSame(
            ['title', 'hr', 'button'],
            array_column($this->componentsAt($contentItem, 0, 0), 'name'),
        );
    }

    public function test_a_negative_position_is_ignored(): void
    {
        $contentItem = $this->withStack();

        $titleId = $this->componentsAt($contentItem, 0, 0)[0]['id'];

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('reorderComponent', $titleId, -1);

        $this->assertSame(
            ['title', 'hr', 'button'],
            array_column($this->componentsAt($contentItem, 0, 0), 'name'),
        );
    }

    public function test_an_unknown_component_is_ignored(): void
    {
        $contentItem = $this->withStack();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('reorderComponent', 'nope', 0)
            ->assertOk();

        $this->assertSame(
            ['title', 'hr', 'button'],
            array_column($this->componentsAt($contentItem, 0, 0), 'name'),
        );
    }

    public function test_each_column_is_its_own_sortable_container(): void
    {
        $contentItem = $this->withStack();

        $html = html_entity_decode(
            Livewire::test(Editor::class, ['model' => $contentItem->refresh()])->html(),
        );

        $this->assertStringContainsString('wire:sort.ghost="reorderComponent"', $html);

        foreach ($this->componentsAt($contentItem, 0, 0) as $component) {
            $this->assertStringContainsString("wire:sort:item=\"{$component['id']}\"", $html);
        }
    }
}

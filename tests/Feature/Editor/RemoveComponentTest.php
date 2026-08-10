<?php

declare(strict_types=1);

namespace Tests\Feature\Editor;

use App\Editor\Editor;
use Livewire\Livewire;
use RuntimeException;
use Tests\Support\ComponentData;
use Tests\Support\Concerns\ReadsStructuredHtml;
use Tests\Support\NewsletterBuilder;
use Tests\TestCase;

/**
 * Emptying a column restores the "Add Component" placeholder, which is also
 * how you swap one component for another. Before this existed the only way to
 * change your mind was to delete the whole block and rebuild it.
 */
class RemoveComponentTest extends TestCase
{
    use ReadsStructuredHtml;

    public function test_it_empties_the_column(): void
    {
        $contentItem = NewsletterBuilder::make()
            ->single()->with('title', ComponentData::title())
            ->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('removeComponent', 'block-1', 0);

        $this->assertNull($this->componentAt($contentItem, 0, 0));
    }

    public function test_it_leaves_the_other_columns_alone(): void
    {
        $contentItem = NewsletterBuilder::make()
            ->double()
            ->with('title', ComponentData::title())
            ->with('hr')
            ->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('removeComponent', 'block-1', 0);

        $this->assertNull($this->componentAt($contentItem, 0, 0));
        $this->assertSame('hr', $this->componentAt($contentItem, 0, 1)['name']);
    }

    public function test_the_block_keeps_its_layout_and_column_count(): void
    {
        $contentItem = NewsletterBuilder::make()
            ->double()->with('title', ComponentData::title())->with('hr')
            ->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('removeComponent', 'block-1', 0);

        $block = $this->blocks($contentItem)[0];

        $this->assertSame('double', $block['block']);
        $this->assertCount(2, $block['properties']);
    }

    public function test_the_placeholder_comes_back_so_a_component_can_be_swapped(): void
    {
        $contentItem = NewsletterBuilder::make()
            ->single()->with('title', ComponentData::title())
            ->create();

        $component = Livewire::test(Editor::class, ['model' => $contentItem]);

        $component->call('removeComponent', 'block-1', 0)
            ->assertSee('Add Component');

        $component->call('addComponent', 'block-1', 'hr', 0);

        $this->assertSame('hr', $this->componentAt($contentItem, 0, 0)['name']);
    }

    public function test_an_out_of_range_index_is_ignored(): void
    {
        $contentItem = NewsletterBuilder::make()
            ->single()->with('title', ComponentData::title())
            ->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('removeComponent', 'block-1', 2);

        $this->assertSame('title', $this->componentAt($contentItem, 0, 0)['name']);
    }

    public function test_removing_from_an_already_empty_column_is_harmless(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->empty()->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('removeComponent', 'block-1', 0)
            ->assertOk();

        $this->assertNull($this->componentAt($contentItem, 0, 0));
    }

    /**
     * The control is a confirm-button, which is easy to lose silently in a
     * Blade edit. Assert the wiring is actually in the rendered output.
     *
     * Entities are decoded first: Blade escapes the on-confirm attribute, and
     * the browser un-escapes it again when parsing the attribute value.
     */
    public function test_a_filled_column_renders_a_remove_control(): void
    {
        $contentItem = NewsletterBuilder::make()
            ->single()->with('title', ComponentData::title())
            ->create();

        $html = html_entity_decode(Livewire::test(Editor::class, ['model' => $contentItem])->html());

        $this->assertStringContainsString("removeComponent('block-1', 0)", $html);
    }

    public function test_an_empty_column_renders_no_remove_control(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->empty()->create();

        $html = Livewire::test(Editor::class, ['model' => $contentItem])->html();

        $this->assertStringNotContainsString('removeComponent', $html);
        $this->assertStringContainsString('Add Component', $html);
    }

    public function test_it_throws_when_the_block_does_not_exist(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->with('hr')->create();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No block');

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('removeComponent', 'nope', 0);
    }
}

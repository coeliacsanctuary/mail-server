<?php

declare(strict_types=1);

namespace Tests\Feature\Editor;

use App\Editor\Editor;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Spatie\Mailcoach\Domain\Content\Models\ContentItem;
use Tests\Support\ComponentData;
use Tests\Support\Concerns\ReadsStructuredHtml;
use Tests\Support\NewsletterBuilder;
use Tests\TestCase;

/**
 * The server half of drag-and-drop. Livewire's wire:sort reports the block's
 * index AFTER the drop, so remove-then-insert-at-position is the right
 * semantic in both directions.
 *
 * The client half has no PHP coverage and needs a browser — see the plan.
 */
class ReorderBlockTest extends TestCase
{
    use ReadsStructuredHtml;

    private function threeBlocks(): ContentItem
    {
        return NewsletterBuilder::make()
            ->single()->with('hr')
            ->single()->with('hr')
            ->single()->with('hr')
            ->create();
    }

    public function test_it_reorders_a_block_to_the_top(): void
    {
        $contentItem = $this->threeBlocks();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('reorderBlock', 'block-3', 0);

        $this->assertSame(['block-3', 'block-1', 'block-2'], $this->blockIds($contentItem));
    }

    public function test_it_reorders_a_block_to_the_bottom(): void
    {
        $contentItem = $this->threeBlocks();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('reorderBlock', 'block-1', 2);

        $this->assertSame(['block-2', 'block-3', 'block-1'], $this->blockIds($contentItem));
    }

    public function test_it_reindexes_the_blocks_array(): void
    {
        $contentItem = $this->threeBlocks();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('reorderBlock', 'block-3', 0);

        $this->assertSame([0, 1, 2], array_keys($this->blocks($contentItem)));
    }

    /** reorderBlock is a public Livewire method, so the position is hostile input. */
    #[DataProvider('outOfRangeProvider')]
    public function test_an_out_of_range_position_does_nothing(int $position): void
    {
        $contentItem = $this->threeBlocks();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('reorderBlock', 'block-2', $position);

        $this->assertSame(['block-1', 'block-2', 'block-3'], $this->blockIds($contentItem));
    }

    public static function outOfRangeProvider(): array
    {
        return [
            'negative' => [-1],
            'one past the end' => [3],
            'far past the end' => [99],
        ];
    }

    public function test_it_throws_when_the_block_does_not_exist(): void
    {
        $contentItem = $this->threeBlocks();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No block');

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('reorderBlock', 'nope', 0);
    }

    public function test_reordering_preserves_component_state(): void
    {
        $contentItem = NewsletterBuilder::make()
            ->single()->with('title', ComponentData::title())
            ->single()->with('hr')
            ->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('reorderBlock', 'block-1', 1);

        $this->assertEquals(
            ['name' => 'title', 'properties' => ComponentData::title()],
            $this->componentAt($contentItem, 1, 0),
        );
    }

    /** Consistent with moveBlock: the preview pane stays stale until save. */
    public function test_reordering_does_not_refresh_the_preview(): void
    {
        $contentItem = $this->threeBlocks();

        $component = Livewire::test(Editor::class, ['model' => $contentItem]);

        $compiledOnMount = $this->mjml->timesCompiled();

        $component->call('reorderBlock', 'block-3', 0)
            ->assertNotDispatched('editorUpdated');

        $this->assertSame($compiledOnMount, $this->mjml->timesCompiled());
    }

    /**
     * The guard for the nested-Livewire hazard.
     *
     * Each block holds a child Livewire component, and Blog fetches from the
     * API in mount(). Reordering must not re-mount them, or a drag would fire
     * one request per API-backed block. Livewire's SupportNestingComponents
     * hijacks the mount for any previously-seen wire:key and returns a stub —
     * and the keys here are derived from block id, not position.
     *
     * If someone ever "tidies" those keys into something position-based, this
     * goes from 3 to 6 and names the problem.
     */
    public function test_reordering_does_not_remount_the_api_backed_components(): void
    {
        $this->fakeCoeliacApi();

        $contentItem = NewsletterBuilder::make()
            ->single()->with('blog', ComponentData::blog())
            ->single()->with('blog', ComponentData::blog())
            ->single()->with('blog', ComponentData::blog())
            ->create();

        $component = Livewire::test(Editor::class, ['model' => $contentItem]);

        Http::assertSentCount(3);

        $component->call('reorderBlock', 'block-3', 0);

        Http::assertSentCount(3);
    }

    public function test_the_blocks_are_rendered_inside_a_sortable_container(): void
    {
        $contentItem = $this->threeBlocks();

        $html = html_entity_decode(Livewire::test(Editor::class, ['model' => $contentItem])->html());

        $this->assertStringContainsString('wire:sort.ghost="reorderBlock"', $html);
        $this->assertStringContainsString('wire:sort:handle', $html);

        // One sortable item per block, each carrying its id as a JS string.
        $this->assertSame(3, mb_substr_count($html, 'wire:sort:item='));
        foreach (['block-1', 'block-2', 'block-3'] as $id) {
            $this->assertStringContainsString("wire:sort:item=\"'{$id}'\"", $html);
        }

        // Whether "Add Block" sits OUTSIDE the sort container is a structural
        // property that a flat string can't prove — it's on the browser
        // checklist. All this asserts is that it still renders.
        $this->assertStringContainsString('Add Block', $html);
    }
}

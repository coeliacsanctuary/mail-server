<?php

declare(strict_types=1);

namespace Tests\Feature\Editor;

use App\Editor\Editor;
use ErrorException;
use Livewire\Livewire;
use RuntimeException;
use Spatie\Mailcoach\Domain\Content\Models\ContentItem;
use Tests\Support\Concerns\ReadsStructuredHtml;
use Tests\Support\NewsletterBuilder;
use Tests\TestCase;

class MoveBlockTest extends TestCase
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

    public function test_it_moves_a_block_up(): void
    {
        $contentItem = $this->threeBlocks();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('moveBlock', 'block-2', 'up');

        $this->assertSame(['block-2', 'block-1', 'block-3'], $this->blockIds($contentItem));
    }

    public function test_it_moves_a_block_down(): void
    {
        $contentItem = $this->threeBlocks();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('moveBlock', 'block-2', 'down');

        $this->assertSame(['block-1', 'block-3', 'block-2'], $this->blockIds($contentItem));
    }

    public function test_it_reindexes_the_blocks_array(): void
    {
        $contentItem = $this->threeBlocks();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('moveBlock', 'block-2', 'up');

        $this->assertSame([0, 1, 2], array_keys($this->blocks($contentItem)));
    }

    public function test_it_moves_the_second_of_two_blocks_up(): void
    {
        $contentItem = NewsletterBuilder::make()
            ->single()->with('hr')
            ->single()->with('hr')
            ->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('moveBlock', 'block-2', 'up');

        $this->assertSame(['block-2', 'block-1'], $this->blockIds($contentItem));
    }

    public function test_it_moves_the_first_of_two_blocks_down(): void
    {
        $contentItem = NewsletterBuilder::make()
            ->single()->with('hr')
            ->single()->with('hr')
            ->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('moveBlock', 'block-1', 'down');

        $this->assertSame(['block-2', 'block-1'], $this->blockIds($contentItem));
    }

    /**
     * Pins an out-of-bounds read. moveItemUp() does array_slice($array, 0, -1)
     * for index 0, which returns everything but the LAST element, then reads
     * $array[-1]. The UI hides the button at the boundary, but moveBlock is a
     * public Livewire method.
     */
    public function test_moving_the_first_block_up_throws(): void
    {
        $contentItem = $this->threeBlocks();

        $this->expectException(ErrorException::class);
        $this->expectExceptionMessageMatches('/Undefined array key -1/');

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('moveBlock', 'block-1', 'up');
    }

    /** And the mirror: moveItemDown() reads $array[$index + 1]. */
    public function test_moving_the_last_block_down_throws(): void
    {
        $contentItem = $this->threeBlocks();

        $this->expectException(ErrorException::class);
        $this->expectExceptionMessageMatches('/Undefined array key 3/');

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('moveBlock', 'block-3', 'down');
    }

    public function test_it_throws_when_the_block_does_not_exist(): void
    {
        $contentItem = $this->threeBlocks();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No block');

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('moveBlock', 'nope', 'up');
    }

    /**
     * Pins that moving a block leaves the preview pane stale - it neither
     * recompiles nor dispatches editorUpdated. Adding a dispatch later should
     * be a visible decision, not an accident.
     */
    public function test_moving_a_block_does_not_refresh_the_preview(): void
    {
        $contentItem = $this->threeBlocks();

        $component = Livewire::test(Editor::class, ['model' => $contentItem]);

        $compiledOnMount = $this->mjml->timesCompiled();

        $component->call('moveBlock', 'block-2', 'up')
            ->assertNotDispatched('editorUpdated');

        $this->assertSame($compiledOnMount, $this->mjml->timesCompiled());
    }
}

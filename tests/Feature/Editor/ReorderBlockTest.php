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

    public function test_it_reorders_blocks_created_through_the_editor(): void
    {
        $contentItem = NewsletterBuilder::make()->create();

        $component = Livewire::test(Editor::class, ['model' => $contentItem]);
        $component->call('addBlock', 'single');
        $component->call('addBlock', 'single');

        [$first, $second] = $this->blockIds($contentItem);

        $component->call('reorderBlock', $second, 0);

        $this->assertSame([$second, $first], $this->blockIds($contentItem));
    }

    public function test_a_quoted_id_is_not_silently_accepted(): void
    {
        $contentItem = $this->threeBlocks();

        $this->expectException(RuntimeException::class);

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('reorderBlock', "'block-1'", 0);
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

    public function test_reordering_does_not_refresh_the_preview(): void
    {
        $contentItem = $this->threeBlocks();

        $component = Livewire::test(Editor::class, ['model' => $contentItem]);

        $compiledOnMount = $this->mjml->timesCompiled();

        $component->call('reorderBlock', 'block-3', 0)
            ->assertNotDispatched('editorUpdated');

        $this->assertSame($compiledOnMount, $this->mjml->timesCompiled());
    }

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

        $this->assertSame(3, mb_substr_count($html, 'wire:sort:item='));
        foreach (['block-1', 'block-2', 'block-3'] as $id) {
            $this->assertStringContainsString("wire:sort:item=\"{$id}\"", $html);
        }
        $this->assertStringNotContainsString('wire:sort:item="\'', $html);

        $this->assertStringContainsString('Add Block', $html);
    }
}

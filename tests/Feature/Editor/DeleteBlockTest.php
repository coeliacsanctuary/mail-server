<?php

declare(strict_types=1);

namespace Tests\Feature\Editor;

use App\Editor\Editor;
use Livewire\Livewire;
use RuntimeException;
use Tests\Support\Concerns\ReadsStructuredHtml;
use Tests\Support\NewsletterBuilder;
use Tests\TestCase;

class DeleteBlockTest extends TestCase
{
    use ReadsStructuredHtml;

    public function test_it_deletes_the_given_block(): void
    {
        $contentItem = NewsletterBuilder::make()
            ->single()->with('hr')
            ->single()->with('hr')
            ->single()->with('hr')
            ->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('deleteBlock', 'block-2');

        $this->assertSame(['block-1', 'block-3'], $this->blockIds($contentItem));
    }

    public function test_it_reindexes_the_blocks_array(): void
    {
        $contentItem = NewsletterBuilder::make()
            ->single()->with('hr')
            ->single()->with('hr')
            ->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('deleteBlock', 'block-1');

        $this->assertSame([0], array_keys($this->blocks($contentItem)));
    }

    /**
     * Without the array_values() call this would encode as {"0":…} rather than
     * [{…}], and the @foreach in editor.blade.php would break.
     */
    public function test_deleting_the_only_block_leaves_a_json_array(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->with('hr')->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('deleteBlock', 'block-1');

        $this->assertStringContainsString('"blocks":[]', $contentItem->refresh()->structured_html);
    }

    public function test_it_throws_when_the_block_does_not_exist(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->with('hr')->create();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No block');

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('deleteBlock', 'nope');
    }

    /** Same stale-preview behaviour as moveBlock. */
    public function test_deleting_a_block_does_not_refresh_the_preview(): void
    {
        $contentItem = NewsletterBuilder::make()
            ->single()->with('hr')
            ->single()->with('hr')
            ->create();

        $component = Livewire::test(Editor::class, ['model' => $contentItem]);

        $compiledOnMount = $this->mjml->timesCompiled();

        $component->call('deleteBlock', 'block-1')
            ->assertNotDispatched('editorUpdated');

        $this->assertSame($compiledOnMount, $this->mjml->timesCompiled());
    }
}

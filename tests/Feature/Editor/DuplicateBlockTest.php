<?php

declare(strict_types=1);

namespace Tests\Feature\Editor;

use App\Editor\Editor;
use Illuminate\Support\Str;
use Livewire\Livewire;
use RuntimeException;
use Tests\Support\ComponentData;
use Tests\Support\Concerns\ReadsStructuredHtml;
use Tests\Support\NewsletterBuilder;
use Tests\TestCase;

class DuplicateBlockTest extends TestCase
{
    use ReadsStructuredHtml;

    public function test_it_inserts_the_copy_directly_after_the_original(): void
    {
        $contentItem = NewsletterBuilder::make()
            ->single()->with('hr')
            ->single()->with('hr')
            ->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('duplicateBlock', 'block-1');

        $ids = $this->blockIds($contentItem);

        $this->assertCount(3, $ids);
        $this->assertSame('block-1', $ids[0]);
        $this->assertSame('block-2', $ids[2]);
        $this->assertTrue(Str::isUuid($ids[1]));
    }

    public function test_the_copy_carries_the_layout_and_components(): void
    {
        $contentItem = NewsletterBuilder::make()
            ->double()
            ->with('title', ComponentData::title())
            ->empty()
            ->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('duplicateBlock', 'block-1');

        $copy = $this->blocks($contentItem)[1];

        $this->assertSame('double', $copy['block']);
        $this->assertEquals(
            ['name' => 'title', 'properties' => ComponentData::title()],
            $copy['properties'][0]['component'],
        );
        $this->assertNull($copy['properties'][1]['component']);
    }

    /** The copy must be independent: editing one must not change the other. */
    public function test_editing_the_copy_leaves_the_original_alone(): void
    {
        $contentItem = NewsletterBuilder::make()
            ->single()->with('title', ComponentData::title())
            ->create();

        $component = Livewire::test(Editor::class, ['model' => $contentItem]);
        $component->call('duplicateBlock', 'block-1');

        $copyId = $this->blockIds($contentItem)[1];

        $component->call('saveComponent', $copyId, ComponentData::title(['content' => 'Changed']), 0);

        $this->assertSame('A Newsletter Title', $this->componentAt($contentItem, 0, 0)['properties']['content']);
        $this->assertSame('Changed', $this->componentAt($contentItem, 1, 0)['properties']['content']);
    }

    public function test_it_preserves_other_top_level_keys(): void
    {
        $contentItem = NewsletterBuilder::make()
            ->preserving(['templateValues' => ['html' => null]])
            ->single()->with('hr')
            ->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('duplicateBlock', 'block-1');

        $this->assertSame(['html' => null], $this->structuredHtml($contentItem)['templateValues']);
    }

    public function test_the_block_actions_render_a_duplicate_and_a_confirmed_delete(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->with('hr')->create();

        // Entities decoded: Blade escapes the on-confirm attribute, and the
        // browser un-escapes it again when parsing the attribute value.
        $html = html_entity_decode(Livewire::test(Editor::class, ['model' => $contentItem])->html());

        $this->assertStringContainsString("duplicateBlock('block-1')", $html);

        // Deletion goes through the confirm modal, not a bare wire:click.
        $this->assertStringContainsString("deleteBlock('block-1')", $html);
        $this->assertStringNotContainsString('wire:click="deleteBlock', $html);
    }

    public function test_it_throws_when_the_block_does_not_exist(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->with('hr')->create();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No block');

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('duplicateBlock', 'nope');
    }
}

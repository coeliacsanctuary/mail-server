<?php

declare(strict_types=1);

namespace Tests\Feature\Editor;

use App\Editor\Editor;
use Illuminate\Support\Str;
use Livewire\Livewire;
use RuntimeException;
use Spatie\Mailcoach\Domain\Content\Models\ContentItem;
use Tests\Support\ComponentData;
use Tests\Support\NewsletterBuilder;
use Tests\TestCase;

class AddBlockTest extends TestCase
{
    /** @return array<int, array<string, mixed>> */
    private function blocks(ContentItem $contentItem): array
    {
        return json_decode($contentItem->refresh()->structured_html, true)['blocks'];
    }

    public function test_it_adds_a_single_column_block(): void
    {
        $contentItem = NewsletterBuilder::make()->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('addBlock', 'single')
            ->assertDispatched('block-added');

        $blocks = $this->blocks($contentItem);

        $this->assertCount(1, $blocks);
        $this->assertSame('single', $blocks[0]['block']);
        $this->assertEquals([['component' => null]], $blocks[0]['properties']);
    }

    public function test_it_adds_a_double_column_block(): void
    {
        $contentItem = NewsletterBuilder::make()->create();

        Livewire::test(Editor::class, ['model' => $contentItem])->call('addBlock', 'double');

        $this->assertCount(2, $this->blocks($contentItem)[0]['properties']);
    }

    public function test_it_adds_a_triple_column_block(): void
    {
        $contentItem = NewsletterBuilder::make()->create();

        Livewire::test(Editor::class, ['model' => $contentItem])->call('addBlock', 'triple');

        $this->assertCount(3, $this->blocks($contentItem)[0]['properties']);
    }

    public function test_an_unknown_block_type_falls_back_to_a_single_column(): void
    {
        $contentItem = NewsletterBuilder::make()->create();

        Livewire::test(Editor::class, ['model' => $contentItem])->call('addBlock', 'quadruple');

        $blocks = $this->blocks($contentItem);

        $this->assertSame('quadruple', $blocks[0]['block']);
        $this->assertCount(1, $blocks[0]['properties']);
    }

    public function test_it_generates_a_uuid_for_the_block_id(): void
    {
        $contentItem = NewsletterBuilder::make()->create();

        Livewire::test(Editor::class, ['model' => $contentItem])->call('addBlock', 'single');

        $this->assertTrue(Str::isUuid($this->blocks($contentItem)[0]['id']));
    }

    public function test_it_appends_when_no_target_is_given(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->with('hr')->create();

        Livewire::test(Editor::class, ['model' => $contentItem])->call('addBlock', 'single');

        $blocks = $this->blocks($contentItem);

        $this->assertCount(2, $blocks);
        $this->assertSame('block-1', $blocks[0]['id']);
    }

    public function test_it_inserts_immediately_after_the_given_block(): void
    {
        $contentItem = NewsletterBuilder::make()
            ->single()->with('hr')
            ->single()->with('hr')
            ->single()->with('hr')
            ->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('addBlock', 'double', 'block-1');

        $blocks = $this->blocks($contentItem);

        $this->assertCount(4, $blocks);
        $this->assertSame('block-1', $blocks[0]['id']);
        $this->assertSame('double', $blocks[1]['block']);
        $this->assertSame('block-2', $blocks[2]['id']);
        $this->assertSame('block-3', $blocks[3]['id']);
    }

    /**
     * addBlock is the only mutation that tolerates a missing document - the
     * others assume $data['blocks'] exists. See CorruptStructuredHtmlTest.
     */
    public function test_it_creates_the_blocks_key_when_structured_html_is_null(): void
    {
        $contentItem = NewsletterBuilder::make()->create();
        $contentItem->update(['structured_html' => null]);

        Livewire::test(Editor::class, ['model' => $contentItem])->call('addBlock', 'single');

        $this->assertCount(1, $this->blocks($contentItem));
    }

    /**
     * Mailcoach stores its own templateValues alongside our blocks. A mutation
     * that rewrote the whole document would silently drop them.
     */
    public function test_it_preserves_other_top_level_keys(): void
    {
        $contentItem = NewsletterBuilder::make()
            ->preserving(['templateValues' => ['html' => null]])
            ->single()->with('title', ComponentData::title())
            ->create();

        Livewire::test(Editor::class, ['model' => $contentItem])->call('addBlock', 'single');

        $data = json_decode($contentItem->refresh()->structured_html, true);

        $this->assertSame(['html' => null], $data['templateValues']);
    }

    public function test_it_throws_when_the_target_block_does_not_exist(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->with('hr')->create();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No block');

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('addBlock', 'single', 'nope');
    }
}

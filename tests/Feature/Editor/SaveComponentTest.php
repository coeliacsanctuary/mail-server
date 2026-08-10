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

class SaveComponentTest extends TestCase
{
    use ReadsStructuredHtml;

    public function test_it_persists_the_given_properties(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->with('title')->create();

        $properties = ComponentData::title(['content' => 'Updated']);

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('saveComponent', 'block-1', $properties, 0);

        $this->assertEquals(
            ['name' => 'title', 'properties' => $properties],
            $this->componentAt($contentItem, 0, 0),
        );
    }

    public function test_it_dispatches_editor_updated(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->with('title')->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('saveComponent', 'block-1', ComponentData::title(), 0)
            ->assertDispatched('editorUpdated');
    }

    /**
     * Pins the stale preview: saveComponent dispatches editorUpdated with
     * previewHtml(), but never calls renderFullHtml() first. The HTML sent to
     * the preview pane is therefore whatever was last compiled - it does not
     * include the edit that just happened.
     */
    public function test_the_dispatched_preview_does_not_include_the_new_content(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->with('title', ComponentData::title())->create();

        $component = Livewire::test(Editor::class, ['model' => $contentItem]);

        $compiledOnMount = $this->mjml->timesCompiled();

        $component->call('saveComponent', 'block-1', ComponentData::title(['content' => 'Brand New Title']), 0);

        // Nothing was recompiled, so the preview cannot contain the new title.
        $this->assertSame($compiledOnMount, $this->mjml->timesCompiled());
        $this->assertStringNotContainsString('Brand New Title', $this->mjml->lastInput());
    }

    /**
     * Saving into a column that holds no component is ignored.
     *
     * This used to auto-vivify an array with a "properties" key but no "name",
     * which the renderer skipped (it keys off component.name) while
     * editable/block.blade.php read that key unguarded - so the editor page
     * itself broke, with no way to recover through the UI.
     */
    public function test_saving_into_an_empty_column_is_ignored(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->empty()->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('saveComponent', 'block-1', ComponentData::title(), 0)
            ->assertOk();

        $this->assertNull($this->componentAt($contentItem, 0, 0));
    }

    /** A document that already contains a nameless component still renders. */
    public function test_a_legacy_nameless_component_is_treated_as_an_empty_column(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->empty()->create();

        $contentItem->update(['structured_html' => json_encode([
            'blocks' => [[
                'id' => 'block-1',
                'block' => 'single',
                'properties' => [['component' => ['properties' => ComponentData::title()]]],
            ]],
        ])]);

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->assertOk()
            ->assertSee('Add Component');
    }

    public function test_it_throws_when_the_block_does_not_exist(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->with('title')->create();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No block');

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('saveComponent', 'nope', ComponentData::title(), 0);
    }
}

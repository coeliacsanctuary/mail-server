<?php

declare(strict_types=1);

namespace Tests\Feature\Editor;

use App\Editor\Editor;
use Illuminate\View\ViewException;
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
     * Pins a data-loss path: saving into a column whose component is null
     * auto-vivifies an array with a "properties" key but no "name".
     */
    public function test_saving_into_an_empty_column_creates_a_component_with_no_name(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->empty()->create();

        try {
            Livewire::test(Editor::class, ['model' => $contentItem])
                ->call('saveComponent', 'block-1', ComponentData::title(), 0);
        } catch (ViewException) {
            // The write lands before the re-render blows up - see the next test.
        }

        $component = $this->componentAt($contentItem, 0, 0);

        $this->assertArrayNotHasKey('name', $component);
        $this->assertArrayHasKey('properties', $component);
    }

    /**
     * And the consequence, which is worse than it first looks: the nameless
     * component is skipped by the renderer (which guards on component.name)
     * but editable/block.blade.php reads that key unguarded, so the editor
     * page itself breaks and cannot be recovered through the UI.
     */
    public function test_a_nameless_component_breaks_the_editor_render(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->empty()->create();

        $this->expectException(ViewException::class);
        $this->expectExceptionMessageMatches('/Undefined array key "name"/');

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('saveComponent', 'block-1', ComponentData::title(), 0);
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

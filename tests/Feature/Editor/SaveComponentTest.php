<?php

declare(strict_types=1);

namespace Tests\Feature\Editor;

use App\Editor\Editor;
use Livewire\Livewire;
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
            ->call('saveComponent', 'title-0', $properties);

        $this->assertEquals(
            ['name' => 'title', 'properties' => $properties],
            $this->componentAt($contentItem, 0, 0),
        );
    }

    public function test_it_dispatches_editor_updated(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->with('title')->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('saveComponent', 'title-0', ComponentData::title())
            ->assertDispatched('editorUpdated');
    }

    public function test_the_dispatched_preview_does_not_include_the_new_content(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->with('title', ComponentData::title())->create();

        $component = Livewire::test(Editor::class, ['model' => $contentItem]);

        $compiledOnMount = $this->mjml->timesCompiled();

        $component->call('saveComponent', 'title-0', ComponentData::title(['content' => 'Brand New Title']));

        $this->assertSame($compiledOnMount, $this->mjml->timesCompiled());
        $this->assertStringNotContainsString('Brand New Title', $this->mjml->lastInput());
    }

    public function test_saving_into_an_empty_column_is_ignored(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->empty()->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('saveComponent', 'title-0', ComponentData::title())
            ->assertOk();

        $this->assertSame([], $this->componentsAt($contentItem, 0, 0));
    }

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

    public function test_saving_an_unknown_component_is_ignored(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->with('title', ComponentData::title())->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('saveComponent', 'nope', ComponentData::title(['content' => 'Changed']))
            ->assertOk();

        $this->assertSame(
            'A Newsletter Title',
            $this->componentAt($contentItem, 0, 0)['properties']['content'],
        );
    }
}

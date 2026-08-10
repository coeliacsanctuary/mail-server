<?php

declare(strict_types=1);

namespace Tests\Feature\Editor;

use App\Editor\Editor;
use Livewire\Livewire;
use Tests\Support\ComponentData;
use Tests\Support\Concerns\ReadsStructuredHtml;
use Tests\Support\NewsletterBuilder;
use Tests\TestCase;

class SaveQuietlyTest extends TestCase
{
    use ReadsStructuredHtml;

    public function test_it_writes_the_compiled_html_to_the_content_item(): void
    {
        $contentItem = NewsletterBuilder::make()
            ->single()->with('title', ComponentData::title())
            ->create();

        Livewire::test(Editor::class, ['model' => $contentItem])->call('saveQuietly');

        $this->assertStringContainsString('A Newsletter Title', $contentItem->refresh()->html);
    }

    public function test_it_dispatches_the_save_events(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->with('hr')->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('saveQuietly')
            ->assertDispatched('editorUpdated')
            ->assertDispatched('editorSavedQuietly');
    }

    /**
     * Guards the fix in commit 9950189. Overriding a parent method drops the
     * #[On] attribute from the parent's declaration, so the listener has to be
     * re-declared or autosave silently stops working.
     */
    public function test_the_save_content_quietly_listener_is_registered(): void
    {
        $contentItem = NewsletterBuilder::make()
            ->single()->with('title', ComponentData::title())
            ->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->dispatch('saveContentQuietly');

        $this->assertStringContainsString('A Newsletter Title', $contentItem->refresh()->html);
    }

    /**
     * The override deliberately drops the parent's setTemplateFieldValues()
     * call - this editor stores its state under "blocks", not "templateValues".
     */
    public function test_it_does_not_write_template_field_values(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->with('hr')->create();

        Livewire::test(Editor::class, ['model' => $contentItem])->call('saveQuietly');

        $this->assertArrayNotHasKey('templateValues', $this->structuredHtml($contentItem));
    }

    /**
     * The end-to-end form of the headline crash: choose a component from the
     * modal, never touch its fields, hit save. This used to throw
     * "Undefined array key link" while rendering the MJML.
     */
    public function test_saving_a_newsletter_with_an_unfilled_component_succeeds(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->empty()->create();

        $component = Livewire::test(Editor::class, ['model' => $contentItem]);

        $component->call('addComponent', 'block-1', 'button', 0);
        $component->call('saveQuietly')->assertDispatched('editorSavedQuietly');

        $this->assertStringContainsString('<mj-body', $contentItem->refresh()->html);
    }

    public function test_saving_compiles_the_newsletter_once(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->with('hr')->create();

        $component = Livewire::test(Editor::class, ['model' => $contentItem]);

        $compiledOnMount = $this->mjml->timesCompiled();

        $component->call('saveQuietly');

        $this->assertSame($compiledOnMount + 1, $this->mjml->timesCompiled());
    }
}

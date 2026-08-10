<?php

declare(strict_types=1);

namespace Tests\Feature\Editor;

use App\Editor\Editor;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Mailcoach\Domain\Campaign\Models\Campaign;
use Tests\Support\ComponentData;
use Tests\Support\NewsletterBuilder;
use Tests\TestCase;

class EditorMountTest extends TestCase
{
    public function test_it_mounts_a_content_item_with_no_structured_html(): void
    {
        $contentItem = Campaign::factory()->create()->contentItem;

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->assertOk()
            ->assertSee('Add Block');
    }

    public function test_it_dispatches_editor_updated_on_mount(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->with('hr')->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->assertDispatched('editorUpdated');
    }

    public function test_it_compiles_the_newsletter_exactly_once_on_mount(): void
    {
        $contentItem = NewsletterBuilder::make()
            ->single()->with('hr')
            ->single()->with('title', ComponentData::title())
            ->create();

        Livewire::test(Editor::class, ['model' => $contentItem]);

        $this->assertCompiledTimes(1);
    }

    public function test_it_renders_a_placeholder_for_each_empty_column(): void
    {
        $contentItem = NewsletterBuilder::make()->double()->empty()->empty()->create();

        $rendered = Livewire::test(Editor::class, ['model' => $contentItem])->html();

        $this->assertSame(2, mb_substr_count($rendered, 'Add Component'));
    }

    /**
     * Every API-backed component fetches in mount(), so opening a newsletter
     * issues one request per component - re-fetching data that is already
     * duplicated into structured_html.
     */
    public function test_mounting_a_newsletter_with_three_blogs_calls_the_api_three_times(): void
    {
        $this->fakeCoeliacApi();

        $contentItem = NewsletterBuilder::make()
            ->triple()
            ->with('blog', ComponentData::blog())
            ->with('blog', ComponentData::blog())
            ->with('blog', ComponentData::blog())
            ->create();

        Livewire::test(Editor::class, ['model' => $contentItem]);

        Http::assertSentCount(3);
    }

    public function test_a_newsletter_with_no_api_components_makes_no_requests(): void
    {
        $contentItem = NewsletterBuilder::make()
            ->single()->with('title', ComponentData::title())
            ->create();

        Livewire::test(Editor::class, ['model' => $contentItem]);

        Http::assertNothingSent();
    }
}

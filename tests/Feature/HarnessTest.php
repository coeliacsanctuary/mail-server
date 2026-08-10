<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Spatie\Mailcoach\Domain\Campaign\Models\Campaign;
use Spatie\Mailcoach\Domain\Content\Models\ContentItem;
use Spatie\Mailcoach\Domain\Shared\Actions\InitializeMjmlAction;
use Tests\Support\Mjml\FakeMjml;
use Tests\TestCase;
use RuntimeException;

/**
 * A canary for the test harness itself. Everything else in the suite assumes
 * these five things hold; when one of them silently stops holding, the failure
 * elsewhere is usually baffling.
 */
class HarnessTest extends TestCase
{
    public function test_it_runs_against_in_memory_sqlite(): void
    {
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
    }

    public function test_mailcoach_models_use_the_test_connection(): void
    {
        // If config('mailcoach.database_connection') is ever set, Mailcoach
        // models get a second handle to a different :memory: database and
        // every test silently loses its data between arrange and assert.
        $this->assertSame('', config('mailcoach.database_connection'));
        $this->assertSame(
            DB::connection()->getName(),
            (new ContentItem())->getConnectionName() ?? DB::connection()->getName(),
        );
    }

    public function test_a_campaign_factory_creates_a_content_item(): void
    {
        $campaign = Campaign::factory()->create();

        $this->assertNotNull($campaign->contentItem);
        $this->assertSame('campaign', $campaign->contentItem->model_type);
        $this->assertNull($campaign->contentItem->structured_html);
    }

    public function test_mjml_is_faked(): void
    {
        $mjml = resolve(InitializeMjmlAction::class)->execute();

        $this->assertInstanceOf(FakeMjml::class, $mjml);

        $html = $mjml->toHtml('<mjml><mj-body></mj-body></mjml>');

        $this->assertSame(1, $this->mjml->timesCompiled());

        // Must not start with "<mjml", or Mailcoach's previewHtml() will
        // re-compile it through a real Mjml instance.
        $this->assertStringStartsNotWith('<mjml', mb_trim($html));
    }

    public function test_the_coeliac_api_is_faked_and_points_away_from_production(): void
    {
        $this->assertSame('https://coeliac.invalid', config('services.coeliac.url'));

        $this->fakeCoeliacApi();

        $response = Http::coeliac()->get('api/blogs/1')->json();

        $this->assertSame('A Gluten Free Blog', $response['title']);
    }

    public function test_unfaked_requests_fail_rather_than_reaching_the_network(): void
    {
        $this->expectException(RuntimeException::class);

        Http::coeliac()->get('api/blogs/1');
    }
}

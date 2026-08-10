<?php

declare(strict_types=1);

namespace Tests\Feature\Newsletter\Components;

use App\Livewire\Newsletter\Editable\Components\Eatery;
use Illuminate\Support\Facades\Http;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\Support\ComponentData;
use Tests\TestCase;

class EateryComponentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->fakeCoeliacApi();
    }

    /** @param array<string, mixed> $properties */
    private function mountComponent(array $properties = []): Testable
    {
        return Livewire::test(Eatery::class, [
            'blockId' => 'block-1',
            'block' => 'single',
            'index' => 0,
            'properties' => $properties,
        ]);
    }

    /**
     * Pins something significant: an eatery with nothing stored randomises on
     * mount AND immediately persists the result, so merely opening a campaign
     * in the editor mutates the newsletter.
     */
    public function test_an_empty_eatery_randomises_and_persists_on_mount(): void
    {
        $this->mountComponent()
            ->assertSet('eateryId', 4)
            ->assertDispatched('component-updated');

        Http::assertSent(fn ($request) => $request->url() === 'https://coeliac.invalid/api/wheretoeat/random');
    }

    public function test_a_stored_eatery_is_fetched_without_randomising(): void
    {
        $this->mountComponent(ComponentData::eatery())
            ->assertSet('eateryId', 4);

        Http::assertSent(fn ($request) => $request->url() === 'https://coeliac.invalid/api/wheretoeat/4');
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'random'));
    }

    /**
     * Eatery is the only component whose API field names differ from
     * ApiResult's, so it translates twice: name -> title -> name,
     * info -> description -> info, full_location -> meta_description ->
     * location. created_at and main_image are forced to empty strings because
     * the DTO requires them.
     */
    /**
     * The contrast with the randomise path above: a stored eatery is fetched
     * but not re-persisted, so opening the editor only mutates the newsletter
     * when the component was empty.
     */
    public function test_a_stored_eatery_does_not_persist_on_mount(): void
    {
        $this->mountComponent(ComponentData::eatery())
            ->assertNotDispatched('component-updated');
    }

    public function test_it_translates_api_field_names_through_the_generic_dto(): void
    {
        $this->mountComponent()
            ->assertDispatched('component-updated', function ($event, $params) {
                $properties = $params[1];

                return $properties['name'] === 'A Gluten Free Cafe'
                    && $properties['info'] === 'A cafe with a dedicated gluten free kitchen.'
                    && $properties['location'] === 'Crewe, Cheshire'
                    && $properties['reviews'] === ['number' => 12, 'average' => 4.5];
            });
    }

    public function test_randomising_replaces_the_current_eatery(): void
    {
        $this->mountComponent(ComponentData::eatery())
            ->call('randomEatery')
            ->assertSet('eateryId', 4)
            ->assertDispatched('component-updated');

        Http::assertSent(fn ($request) => $request->url() === 'https://coeliac.invalid/api/wheretoeat/random');
    }
}

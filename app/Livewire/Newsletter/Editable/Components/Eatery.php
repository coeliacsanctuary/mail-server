<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

use App\Dto\ApiResult;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

/**
 * Not folded in with Blog/Recipe/Product: no search, and it is the only
 * component whose API field names differ from ApiResult's, so it translates
 * name/info/full_location in both directions.
 */
class Eatery extends NewsletterComponent
{
    public ?int $eateryId = null;

    public ApiResult $eatery;

    public function mount(): void
    {
        $this->eateryId = $this->properties['content'] ?? null;

        if ($this->eateryId) {
            $this->eatery = $this->getEatery();

            return;
        }

        $this->randomEatery();
    }

    public function randomEatery(): void
    {
        $this->eatery = $this->toApiResult(
            Http::coeliac()->get('api/wheretoeat/random')->json(),
        );

        $this->eateryId = $this->eatery->id;

        $this->syncProperties();
    }

    public function render(): View
    {
        return view('livewire.newsletter.editable.components.eatery');
    }

    protected function getEatery(?int $id = null): ApiResult
    {
        $id ??= $this->eateryId;

        return $this->toApiResult(
            Http::coeliac()->get("api/wheretoeat/{$id}")->json(),
        );
    }

    /** @param array<string, mixed> $response */
    protected function toApiResult(array $response): ApiResult
    {
        return new ApiResult(
            id: $response['id'],
            title: $response['name'],
            description: $response['info'],
            meta_description: $response['full_location'],
            created_at: '',
            main_image: '',
            link: $response['link'],
            extra: ['reviews' => $response['reviews']],
        );
    }

    /** @return array<string, mixed> */
    protected function savedProperties(): array
    {
        return [
            'content' => $this->eateryId,
            'name' => $this->eatery->title,
            'info' => $this->eatery->description,
            'location' => $this->eatery->meta_description,
            'link' => $this->eatery->link,
            'reviews' => $this->eatery->extra['reviews'],
        ];
    }
}

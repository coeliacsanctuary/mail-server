<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

use App\Dto\ApiResult;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class Eatery extends NewsletterComponent
{
    public ?int $eateryId = null;

    public ApiResult $eatery;

    public function mount(): void
    {
        $this->eateryId = $this->properties['content'] ?? null;

        if ($this->eateryId) {
            $this->eatery = $this->getEatery();
        } else {
            $this->randomEatery();
        }
    }

    public function updated(): void
    {
        $this->properties = [
            'content' => $this->eateryId,
            'name' => $this->eatery->title,
            'info' => $this->eatery->description,
            'location' => $this->eatery->meta_description,
            'link' => $this->eatery->link,
            'reviews' => $this->eatery->extra['reviews'],
        ];

        $this->dispatch('component-updated', $this->blockId, $this->properties, $this->index);
    }

    protected function getEatery(?int $id = null): ApiResult
    {
        $id ??= $this->eateryId;

        $response = Http::coeliac()->get("api/wheretoeat/{$id}")->json();

        return new ApiResult(
            id: $response['id'],
            title: $response['name'],
            description: $response['info'],
            meta_description: $response['full_location'],
            created_at: '',
            main_image: '',
            link: $response['link'],
            extra: ['reviews' => $response['reviews']]
        );
    }

    public function randomEatery(): void
    {
        $response = Http::coeliac()->get("api/wheretoeat/random")->json();

        $this->eateryId = $response['id'];
        $this->eatery = new ApiResult(
            id: $response['id'],
            title: $response['name'],
            description: $response['info'],
            meta_description: $response['full_location'],
            created_at: '',
            main_image: '',
            link: $response['link'],
            extra: ['reviews' => $response['reviews']]
        );

        $this->updated();
    }

    public function render(): View
    {
        return view('livewire.newsletter.editable.components.eatery');
    }
}

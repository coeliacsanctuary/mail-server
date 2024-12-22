<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

use App\Dto\ApiResult;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class Product extends NewsletterComponent
{
    public string $description = '';

    public ?int $productId = null;

    public string $search = '';

    public Collection $results;

    public ApiResult $product;

    public function mount(): void
    {
        $this->productId = $this->properties['content'] ?? null;
        $this->results = new Collection();

        if ($this->productId) {
            $this->product = $this->getProduct();
            $this->description = $this->block === 'single' ? $this->product->description : $this->product->meta_description;
        }

        if (isset($this->properties['description'])) {
            $this->description = $this->properties['description'];
        }
    }

    public function updated($property = null): void
    {
        if ($property === 'search') {
            $this->handleSearch();

            return;
        }

        $this->properties = [
            'content' => $this->productId,
            'description' => $this->description,
            'title' => $this->product->title,
            'image' => $this->product->main_image,
            'created_at' => $this->product->created_at,
            'link' => $this->product->link,
            'price' => $this->product->extra['price'],
        ];

        $this->dispatch('component-updated', $this->blockId, $this->properties, $this->index);
    }

    protected function handleSearch(): void
    {
        $this->results = Http::coeliac()
            ->get('api/shop/products', ['search' => $this->search])
            ->collect('data')
            ->map(fn ($product) => new ApiResult(
                id: $product['id'],
                title: $product['title'],
                description: $product['description'],
                meta_description: $product['meta_description'],
                created_at: $product['created_at'],
                main_image: $product['main_image'],
                link: $product['link'],
                extra: [
                    'price' => $product['price'],
                ]
            ));
    }

    protected function getProduct(?int $id = null): ApiResult
    {
        $id ??= $this->productId;

        $response = Http::coeliac()->get("api/shop/products/{$id}")->json();

        $params = array_merge(Arr::except($response, ['price']), [
            'extra' => ['price' => $response['price']]
        ]);

        return new ApiResult(...$params);
    }

    public function selectProduct($id): void
    {
        $this->productId = $id;
        $this->product = $this->getProduct($id);
        $this->description = $this->block === 'single' ? $this->product->description : $this->product->meta_description;
        $this->results = collect();
        $this->search = '';
        $this->updated();
    }

    public function remove(): void
    {
        $this->productId = null;
        $this->description = '';
        $this->results = collect();
        $this->search = '';
        $this->updated();
    }


    public function render(): View
    {
        return view('livewire.newsletter.editable.components.product');
    }
}

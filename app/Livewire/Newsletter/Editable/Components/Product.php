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

    /** @var Collection<int, ApiResult> */
    public Collection $results;

    public ApiResult $product;

    public function mount(): void
    {
        $this->productId = $this->properties['content'] ?? null;
        $this->results = new Collection();

        if ($this->productId) {
            $this->product = $this->getProduct();
            $this->description = $this->block === 'single'
                ? $this->product->description
                : $this->product->meta_description;
        }

        if (isset($this->properties['description'])) {
            $this->description = $this->properties['description'];
        }
    }

    /** Products are not paginated, so the results sit under "data", not "data.data". */
    public function updatedSearch(): void
    {
        $this->results = Http::coeliac()
            ->get('api/shop/products', ['search' => $this->search])
            ->collect('data')
            ->map(fn (array $product) => new ApiResult(
                id: $product['id'],
                title: $product['title'],
                description: $product['description'],
                meta_description: $product['meta_description'],
                created_at: $product['created_at'],
                main_image: $product['main_image'],
                link: $product['link'],
                extra: ['price' => $product['price']],
            ));
    }

    public function updatedDescription(): void
    {
        $this->syncProperties();

        $this->skipRender();
    }

    public function selectProduct(int $id): void
    {
        $this->productId = $id;
        $this->product = $this->getProduct($id);
        $this->description = $this->block === 'single'
            ? $this->product->description
            : $this->product->meta_description;

        $this->clearSearch();
        $this->syncProperties();
    }

    public function remove(): void
    {
        $this->productId = null;
        $this->description = '';

        $this->clearSearch();
        $this->syncProperties();
    }

    public function render(): View
    {
        return view('livewire.newsletter.editable.components.product');
    }

    protected function getProduct(?int $id = null): ApiResult
    {
        $id ??= $this->productId;

        $response = Http::coeliac()->get("api/shop/products/{$id}")->json();

        return new ApiResult(...[
            ...Arr::except($response, ['price']),
            'extra' => ['price' => $response['price']],
        ]);
    }

    protected function clearSearch(): void
    {
        $this->search = '';
        $this->results = new Collection();
    }

    /** @return array<string, mixed> */
    protected function savedProperties(): array
    {
        return [
            'content' => $this->productId,
            'description' => $this->description,
            'title' => $this->product->title,
            'image' => $this->product->main_image,
            'created_at' => $this->product->created_at,
            'link' => $this->product->link,
            'price' => $this->product->extra['price'],
        ];
    }
}

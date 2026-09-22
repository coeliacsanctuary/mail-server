<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

use App\Dto\ApiResult;

class Product extends SearchableApiComponent
{
    protected function endpoint(): string
    {
        return 'api/shop/products';
    }

    protected function label(): string
    {
        return 'products';
    }

    protected function searchResultsPath(): string
    {
        return 'data';
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function extra(array $payload): array
    {
        return ['price' => $payload['price']];
    }

    /** @return array<string, mixed> */
    protected function extraProperties(ApiResult $result): array
    {
        return ['price' => $result->extra['price']];
    }

    protected function meta(ApiResult $result): string
    {
        return $result->extra['price'];
    }
}

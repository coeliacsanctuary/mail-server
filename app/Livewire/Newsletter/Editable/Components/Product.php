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

    /** Products are not paginated, so results sit under "data", not "data.data". */
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

    /** A product shows its price where the others show a publish date. */
    protected function meta(ApiResult $result): string
    {
        return $result->extra['price'];
    }
}

<?php

declare(strict_types=1);

namespace Tests\Support\Concerns;

use Illuminate\Support\Facades\Http;

trait FakesCoeliacApi
{
    protected function fakeCoeliacApi(array $overrides = []): void
    {
        Http::fake([
            ...$overrides,

            'coeliac.invalid/api/blogs?*' => Http::response(['data' => ['data' => [self::blogPayload()]]]),
            'coeliac.invalid/api/blogs/*' => Http::response(self::blogPayload()),

            'coeliac.invalid/api/recipes?*' => Http::response(['data' => ['data' => [self::recipePayload()]]]),
            'coeliac.invalid/api/recipes/*' => Http::response(self::recipePayload()),

            'coeliac.invalid/api/shop/products?*' => Http::response(['data' => [self::productPayload()]]),
            'coeliac.invalid/api/shop/products/*' => Http::response(self::productPayload()),

            'coeliac.invalid/api/wheretoeat/random' => Http::response(self::eateryPayload()),
            'coeliac.invalid/api/wheretoeat/*' => Http::response(self::eateryPayload()),
        ]);
    }

    protected static function blogPayload(array $overrides = []): array
    {
        return [
            'id' => 1,
            'title' => 'A Gluten Free Blog',
            'description' => '<p>The long blog description.</p>',
            'meta_description' => 'The short blog description.',
            'created_at' => '1st January 2026',
            'main_image' => 'https://coeliac.invalid/images/blog.jpg',
            'link' => 'https://coeliac.invalid/blog/a-gluten-free-blog',
            ...$overrides,
        ];
    }

    protected static function recipePayload(array $overrides = []): array
    {
        return [
            'id' => 2,
            'title' => 'A Gluten Free Recipe',
            'description' => '<p>The long recipe description.</p>',
            'meta_description' => 'The short recipe description.',
            'created_at' => '2nd January 2026',
            'main_image' => 'https://coeliac.invalid/images/recipe.jpg',
            'link' => 'https://coeliac.invalid/recipe/a-gluten-free-recipe',
            ...$overrides,
        ];
    }

    protected static function productPayload(array $overrides = []): array
    {
        return [
            'id' => 3,
            'title' => 'A Gluten Free Product',
            'description' => '<p>The long product description.</p>',
            'meta_description' => 'The short product description.',
            'created_at' => '3rd January 2026',
            'main_image' => 'https://coeliac.invalid/images/product.jpg',
            'link' => 'https://coeliac.invalid/shop/a-gluten-free-product',
            'price' => '£4.99',
            ...$overrides,
        ];
    }

    protected static function eateryPayload(array $overrides = []): array
    {
        return [
            'id' => 4,
            'name' => 'A Gluten Free Cafe',
            'info' => 'A cafe with a dedicated gluten free kitchen.',
            'full_location' => 'Crewe, Cheshire',
            'link' => 'https://coeliac.invalid/wheretoeat/a-gluten-free-cafe',
            'reviews' => [
                'number' => 12,
                'average' => 4.5,
            ],
            ...$overrides,
        ];
    }
}

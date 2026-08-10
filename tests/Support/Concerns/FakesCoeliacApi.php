<?php

declare(strict_types=1);

namespace Tests\Support\Concerns;

use Illuminate\Support\Facades\Http;

/**
 * Stubs the coeliacsanctuary.co.uk API the editor components read from.
 *
 * Pattern order matters - Http::fake() returns the first match. The show
 * patterns ("…/blogs/*") and index patterns ("…/blogs?*") are disjoint because
 * only "*" is a wildcard, but "wheretoeat/random" genuinely overlaps
 * "wheretoeat/*" and must come first.
 */
trait FakesCoeliacApi
{
    /** @param array<string, mixed> $overrides */
    protected function fakeCoeliacApi(array $overrides = []): void
    {
        Http::fake([
            ...$overrides,

            'coeliac.invalid/api/blogs?*' => Http::response(['data' => ['data' => [self::blogPayload()]]]),
            'coeliac.invalid/api/blogs/*' => Http::response(self::blogPayload()),

            'coeliac.invalid/api/recipes?*' => Http::response(['data' => ['data' => [self::recipePayload()]]]),
            'coeliac.invalid/api/recipes/*' => Http::response(self::recipePayload()),

            // Products are the odd one out: a flat "data" envelope, not "data.data".
            'coeliac.invalid/api/shop/products?*' => Http::response(['data' => [self::productPayload()]]),
            'coeliac.invalid/api/shop/products/*' => Http::response(self::productPayload()),

            'coeliac.invalid/api/wheretoeat/random' => Http::response(self::eateryPayload()),
            'coeliac.invalid/api/wheretoeat/*' => Http::response(self::eateryPayload()),
        ]);
    }

    /**
     * Blog and Recipe spread the response straight into ApiResult's constructor,
     * so these keys must match its parameter names exactly.
     *
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
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

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
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

    /**
     * Product carries an extra "price" key that Product::getProduct() lifts
     * into ApiResult::$extra.
     *
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
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

    /**
     * Eatery is the only component whose API field names differ from
     * ApiResult's: name/info/full_location rather than
     * title/description/meta_description.
     *
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
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

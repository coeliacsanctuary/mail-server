<?php

declare(strict_types=1);

namespace Tests\Support;

final class ComponentData
{
    public static function blog(array $overrides = []): array
    {
        return [
            'content' => 1,
            'title' => 'A Gluten Free Blog',
            'image' => 'https://coeliac.invalid/images/blog.jpg',
            'description' => 'The short blog description.',
            'created_at' => '1st January 2026',
            'link' => 'https://coeliac.invalid/blog/a-gluten-free-blog',
            ...$overrides,
        ];
    }

    public static function recipe(array $overrides = []): array
    {
        return [
            'content' => 2,
            'description' => 'The short recipe description.',
            'title' => 'A Gluten Free Recipe',
            'image' => 'https://coeliac.invalid/images/recipe.jpg',
            'created_at' => '2nd January 2026',
            'link' => 'https://coeliac.invalid/recipe/a-gluten-free-recipe',
            ...$overrides,
        ];
    }

    public static function product(array $overrides = []): array
    {
        return [
            'content' => 3,
            'description' => 'The short product description.',
            'title' => 'A Gluten Free Product',
            'image' => 'https://coeliac.invalid/images/product.jpg',
            'created_at' => '3rd January 2026',
            'link' => 'https://coeliac.invalid/shop/a-gluten-free-product',
            'price' => '£4.99',
            ...$overrides,
        ];
    }

    public static function eatery(array $overrides = []): array
    {
        return [
            'content' => 4,
            'name' => 'A Gluten Free Cafe',
            'info' => 'A cafe with a dedicated gluten free kitchen.',
            'location' => 'Crewe, Cheshire',
            'link' => 'https://coeliac.invalid/wheretoeat/a-gluten-free-cafe',
            'reviews' => [
                'number' => 12,
                'average' => 4.5,
            ],
            ...$overrides,
        ];
    }

    public static function title(array $overrides = []): array
    {
        return [
            'content' => 'A Newsletter Title',
            'link' => null,
            ...$overrides,
        ];
    }

    public static function subtitle(array $overrides = []): array
    {
        return [
            'content' => 'A Newsletter Subtitle',
            'link' => null,
            ...$overrides,
        ];
    }

    public static function titleWithText(array $overrides = []): array
    {
        return [
            'title' => 'A Newsletter Title',
            'link' => null,
            'content' => "First line.\nSecond line.",
            ...$overrides,
        ];
    }

    public static function text(array $overrides = []): array
    {
        return [
            'content' => "First line.\nSecond line.",
            ...$overrides,
        ];
    }

    public static function textWithButton(array $overrides = []): array
    {
        return [
            'content' => "First line.\nSecond line.",
            'label' => 'Read more',
            'link' => 'https://coeliac.invalid/blog',
            ...$overrides,
        ];
    }

    public static function button(array $overrides = []): array
    {
        return [
            'content' => 'Read more',
            'link' => 'https://coeliac.invalid/blog',
            'text_align' => 'left',
            'background' => 'secondary',
            ...$overrides,
        ];
    }

    public static function image(array $overrides = []): array
    {
        return [
            'content' => 'https://coeliac.invalid/images/upload.jpg',
            'link' => 'https://coeliac.invalid/blog',
            ...$overrides,
        ];
    }

    public static function imageWithButton(array $overrides = []): array
    {
        return [
            'content' => 'https://coeliac.invalid/images/upload.jpg',
            'label' => 'Read more',
            'link' => 'https://coeliac.invalid/blog',
            ...$overrides,
        ];
    }
}

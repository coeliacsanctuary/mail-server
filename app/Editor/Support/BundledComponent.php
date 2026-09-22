<?php

declare(strict_types=1);

namespace App\Editor\Support;

final class BundledComponent
{
    /**
     * @param array<string, mixed> $properties
     * @return list<array{name: string, properties: array<string, mixed>}>|null
     */
    public static function expand(string $name, array $properties): ?array
    {
        return match ($name) {
            'image-with-button' => self::imageWithButton($properties),
            'text-with-button' => self::textWithButton($properties),
            'title-with-text' => self::titleWithText($properties),
            default => null,
        };
    }

    /**
     * @param array<string, mixed> $properties
     * @return list<array{name: string, properties: array<string, mixed>}>
     */
    private static function imageWithButton(array $properties): array
    {
        $components = [];

        if (filled($properties['content'] ?? null)) {
            $components[] = self::image($properties);
        }

        if (filled($properties['label'] ?? null)) {
            $components[] = self::button($properties);
        }

        return $components;
    }

    /**
     * @param array<string, mixed> $properties
     * @return list<array{name: string, properties: array<string, mixed>}>
     */
    private static function textWithButton(array $properties): array
    {
        $components = [self::text($properties)];

        if (filled($properties['label'] ?? null)) {
            $components[] = self::button($properties);
        }

        return $components;
    }

    /**
     * @param array<string, mixed> $properties
     * @return list<array{name: string, properties: array<string, mixed>}>
     */
    private static function titleWithText(array $properties): array
    {
        return [
            [
                'name' => 'title',
                'properties' => [
                    'content' => $properties['title'] ?? '',
                    'link' => $properties['link'] ?? null,
                ],
            ],
            self::text($properties),
        ];
    }

    /**
     * @param array<string, mixed> $properties
     * @return array{name: string, properties: array<string, mixed>}
     */
    private static function image(array $properties): array
    {
        return [
            'name' => 'image',
            'properties' => [
                'content' => $properties['content'] ?? '',
                'link' => $properties['link'] ?? '',
                'alt' => $properties['alt'] ?? '',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $properties
     * @return array{name: string, properties: array<string, mixed>}
     */
    private static function button(array $properties): array
    {
        return [
            'name' => 'button',
            'properties' => [
                'content' => $properties['label'] ?? '',
                'link' => $properties['link'] ?? '',
                'text_align' => 'left',
                'background' => BrandColour::default()->value,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $properties
     * @return array{name: string, properties: array<string, mixed>}
     */
    private static function text(array $properties): array
    {
        return [
            'name' => 'text',
            'properties' => ['content' => $properties['content'] ?? ''],
        ];
    }
}

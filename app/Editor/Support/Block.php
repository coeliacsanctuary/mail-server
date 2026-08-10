<?php

declare(strict_types=1);

namespace App\Editor\Support;

use Illuminate\Support\Str;

/**
 * A row of the newsletter: an id, a layout, and one to three columns each
 * holding a component or nothing.
 */
final class Block
{
    /** @param list<BlockComponent|null> $columns */
    public function __construct(
        public string $id,
        public string $layout,
        public array $columns,
    ) {
    }

    public static function make(string $layout, ?string $id = null): self
    {
        $columns = array_fill(0, self::columnCountFor($layout), null);

        return new self($id ?? (string) Str::uuid(), $layout, $columns);
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $columns = array_map(
            fn (mixed $column) => BlockComponent::fromArray(
                is_array($column) ? ($column['component'] ?? null) : null,
            ),
            array_values($data['properties'] ?? []),
        );

        return new self(
            (string) ($data['id'] ?? ''),
            (string) ($data['block'] ?? 'single'),
            $columns,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'block' => $this->layout,
            'properties' => array_map(
                fn (?BlockComponent $component) => ['component' => $component?->toArray()],
                $this->columns,
            ),
        ];
    }

    /**
     * Column indexes arrive from the browser. Out-of-range ones used to grow
     * the block a sparse extra column; they are now ignored.
     */
    public function putComponent(int $index, BlockComponent $component): void
    {
        if ( ! array_key_exists($index, $this->columns)) {
            return;
        }

        $this->columns[$index] = $component;
    }

    /** @param array<string, mixed> $properties */
    public function updateComponentProperties(int $index, array $properties): void
    {
        $component = $this->columns[$index] ?? null;

        if ( ! $component instanceof BlockComponent) {
            return;
        }

        $component->properties = $properties;
    }

    private static function columnCountFor(string $layout): int
    {
        return match ($layout) {
            'triple' => 3,
            'double' => 2,
            default => 1,
        };
    }
}

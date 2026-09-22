<?php

declare(strict_types=1);

namespace App\Editor\Support;

use Illuminate\Support\Str;

final class Block
{
    /** @param list<list<BlockComponent>> $columns */
    public function __construct(
        public string $id,
        public string $layout,
        public array $columns,
    ) {
    }

    public static function make(string $layout, ?string $id = null): self
    {
        $columns = array_fill(0, self::columnCountFor($layout), []);

        return new self($id ?? (string) Str::uuid(), $layout, $columns);
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $id = (string) ($data['id'] ?? '');

        $columns = [];

        foreach (array_values($data['properties'] ?? []) as $columnIndex => $column) {
            $stored = is_array($column)
                ? ($column['components'] ?? [$column['component'] ?? null])
                : [null];

            $components = [];

            foreach (array_values(is_array($stored) ? $stored : []) as $position => $component) {
                $component = BlockComponent::fromArray(
                    $component,
                    "{$id}-{$columnIndex}-{$position}",
                );

                if ($component instanceof BlockComponent) {
                    $components[] = $component;
                }
            }

            $columns[] = $components;
        }

        return new self($id, (string) ($data['block'] ?? 'single'), $columns);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'block' => $this->layout,
            'properties' => array_map(
                fn (array $components) => [
                    'components' => array_map(
                        fn (BlockComponent $component) => $component->toArray(),
                        $components,
                    ),
                ],
                $this->columns,
            ),
        ];
    }

    public function appendComponent(int $columnIndex, BlockComponent $component): void
    {
        if ( ! array_key_exists($columnIndex, $this->columns)) {
            return;
        }

        $this->columns[$columnIndex][] = $component;
    }

    /** @param array<string, mixed> $properties */
    public function updateComponentProperties(string $componentId, array $properties): void
    {
        foreach ($this->columns as $components) {
            foreach ($components as $component) {
                if ($component->id === $componentId) {
                    $component->properties = $properties;

                    return;
                }
            }
        }
    }

    public function removeComponent(string $componentId): void
    {
        foreach ($this->columns as $columnIndex => $components) {
            foreach ($components as $position => $component) {
                if ($component->id === $componentId) {
                    array_splice($this->columns[$columnIndex], $position, 1);

                    return;
                }
            }
        }
    }

    public function moveComponentTo(string $componentId, int $position): void
    {
        foreach ($this->columns as $columnIndex => $components) {
            foreach ($components as $current => $component) {
                if ($component->id !== $componentId) {
                    continue;
                }

                if ($position < 0 || $position >= count($components)) {
                    return;
                }

                array_splice($this->columns[$columnIndex], $current, 1);
                array_splice($this->columns[$columnIndex], $position, 0, [$component]);

                return;
            }
        }
    }

    public function copy(?string $id = null): self
    {
        return new self(
            $id ?? (string) Str::uuid(),
            $this->layout,
            array_map(
                fn (array $components) => array_map(
                    fn (BlockComponent $component) => $component->copy(),
                    $components,
                ),
                $this->columns,
            ),
        );
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

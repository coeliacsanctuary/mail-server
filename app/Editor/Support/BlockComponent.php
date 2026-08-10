<?php

declare(strict_types=1);

namespace App\Editor\Support;

/**
 * One component sitting in one column.
 *
 * The name is both a Livewire component path and a Blade view path, so it is
 * stored data - renaming one is a migration of every saved campaign, not a
 * refactor.
 */
final class BlockComponent
{
    /** @param array<string, mixed> $properties */
    public function __construct(
        public string $name,
        public array $properties = [],
    ) {
    }

    /**
     * Anything without a usable name is treated as an empty column. That state
     * used to be reachable by saving properties into a column that had no
     * component; it is no longer created, but old documents may contain it.
     */
    public static function fromArray(mixed $data): ?self
    {
        if ( ! is_array($data) || ! isset($data['name']) || ! is_string($data['name'])) {
            return null;
        }

        return new self($data['name'], $data['properties'] ?? []);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'properties' => $this->properties,
        ];
    }

    /**
     * A detached copy. $properties is an array, so PHP copies it by value and
     * the two components share no state.
     */
    public function copy(): self
    {
        return new self($this->name, $this->properties);
    }
}

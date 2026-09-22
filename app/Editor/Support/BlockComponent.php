<?php

declare(strict_types=1);

namespace App\Editor\Support;

final class BlockComponent
{
    /** @param array<string, mixed> $properties */
    public function __construct(
        public string $name,
        public array $properties = [],
    ) {
    }

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

    public function copy(): self
    {
        return new self($this->name, $this->properties);
    }
}

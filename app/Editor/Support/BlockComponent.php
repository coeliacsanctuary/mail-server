<?php

declare(strict_types=1);

namespace App\Editor\Support;

use Illuminate\Support\Str;

final class BlockComponent
{
    /** @param array<string, mixed> $properties */
    public function __construct(
        public string $name,
        public array $properties = [],
        public string $id = '',
    ) {
        if ($this->id === '') {
            $this->id = (string) Str::uuid();
        }
    }

    public static function fromArray(mixed $data, string $fallbackId = ''): ?self
    {
        if ( ! is_array($data) || ! isset($data['name']) || ! is_string($data['name'])) {
            return null;
        }

        $id = $data['id'] ?? null;

        return new self(
            $data['name'],
            $data['properties'] ?? [],
            is_string($id) && $id !== '' ? $id : $fallbackId,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'properties' => $this->properties,
            'id' => $this->id,
        ];
    }

    public function copy(): self
    {
        return new self($this->name, $this->properties);
    }
}

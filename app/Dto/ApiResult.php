<?php

declare(strict_types=1);

namespace App\Dto;

use Livewire\Wireable;

class ApiResult implements Wireable
{
    public function __construct(
        public int    $id,
        public string $title,
        public string $description,
        public string $meta_description,
        public string $created_at,
        public string $main_image,
        public string $link,
        public array $extra = [],
    ) {
    }

    public function toLivewire(): array
    {
        return get_object_vars($this);
    }

    public static function fromLivewire($value): self
    {
        return new self(...$value);
    }
}

<?php

declare(strict_types=1);

namespace App\Editor\Support;

use RuntimeException;

class BlockNotFound extends RuntimeException
{
    public static function make(string $id): self
    {
        return new self("No block [{$id}]");
    }
}

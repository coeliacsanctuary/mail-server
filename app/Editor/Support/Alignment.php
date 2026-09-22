<?php

declare(strict_types=1);

namespace App\Editor\Support;

enum Alignment: string
{
    case Left = 'left';
    case Center = 'center';
    case Right = 'right';

    public static function fromName(?string $name, ?self $fallback = null): self
    {
        return self::tryFrom((string) $name) ?? $fallback ?? self::Left;
    }

    public function icon(): string
    {
        return match ($this) {
            self::Left => 'bars-3-bottom-left',
            self::Center => 'bars-3',
            self::Right => 'bars-3-bottom-right',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Left => 'Align left',
            self::Center => 'Align centre',
            self::Right => 'Align right',
        };
    }
}

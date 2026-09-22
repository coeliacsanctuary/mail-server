<?php

declare(strict_types=1);

namespace App\Editor\Support;

enum ButtonColour: string
{
    case Secondary = 'secondary';
    case Primary = 'primary';
    case PrimaryLight = 'primary-light';
    case PrimaryDark = 'primary-dark';

    public static function default(): self
    {
        return self::Secondary;
    }

    public static function fromName(?string $name): self
    {
        return self::tryFrom((string) $name) ?? self::default();
    }

    public function background(): string
    {
        return match ($this) {
            self::Secondary => '#DBBC25',
            self::Primary => '#80CCFC',
            self::PrimaryLight => '#addaf9',
            self::PrimaryDark => '#29719f',
        };
    }

    public function text(): string
    {
        return match ($this) {
            self::PrimaryDark => '#ffffff',
            default => '#222222',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Secondary => 'Yellow',
            self::Primary => 'Blue',
            self::PrimaryLight => 'Light blue',
            self::PrimaryDark => 'Dark blue',
        };
    }
}

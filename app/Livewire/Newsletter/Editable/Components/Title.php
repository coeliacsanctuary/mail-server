<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

use App\Editor\Support\Alignment;

class Title extends HeadingComponent
{
    protected function label(): string
    {
        return 'Title';
    }

    protected function defaultAlignment(): Alignment
    {
        return Alignment::Center;
    }
}

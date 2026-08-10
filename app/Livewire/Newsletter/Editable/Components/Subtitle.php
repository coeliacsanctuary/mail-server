<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

class Subtitle extends HeadingComponent
{
    protected function label(): string
    {
        return 'Subtitle';
    }

    protected function inputClass(): string
    {
        return 'text-xl';
    }
}

<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

class Recipe extends SearchableApiComponent
{
    protected function endpoint(): string
    {
        return 'api/recipes';
    }

    protected function label(): string
    {
        return 'recipes';
    }
}

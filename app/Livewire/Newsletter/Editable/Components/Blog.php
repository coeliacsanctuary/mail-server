<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

class Blog extends SearchableApiComponent
{
    protected function endpoint(): string
    {
        return 'api/blogs';
    }

    protected function label(): string
    {
        return 'blogs';
    }
}

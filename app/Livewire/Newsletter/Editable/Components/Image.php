<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

use Illuminate\View\View;

class Image extends ImageComponent
{
    public function render(): View
    {
        return view('livewire.newsletter.editable.components.image');
    }
}

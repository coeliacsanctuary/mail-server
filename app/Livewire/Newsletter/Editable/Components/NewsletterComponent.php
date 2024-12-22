<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

use Illuminate\View\View;
use Livewire\Component;

abstract class NewsletterComponent extends Component
{
    public string $blockId;

    public string $block;

    public int $index;

    public array $properties;

    abstract public function render(): View;
}

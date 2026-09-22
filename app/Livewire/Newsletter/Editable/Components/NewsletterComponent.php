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

    /** @var array<string, mixed> */
    public array $properties = [];

    abstract public function render(): View;

    /** @return array<string, mixed> */
    protected function savedProperties(): array
    {
        return $this->properties;
    }

    protected function syncProperties(): void
    {
        $this->properties = $this->savedProperties();

        $this->dispatch('component-updated', $this->blockId, $this->properties, $this->index);
    }
}

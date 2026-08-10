<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

use Illuminate\View\View;

class Button extends NewsletterComponent
{
    public string $label;

    public string $link;

    public function mount(): void
    {
        $this->label = $this->properties['content'] ?? '';
        $this->link = $this->properties['link'] ?? '';
    }

    public function updated(): void
    {
        $this->syncProperties();

        $this->skipRender();
    }

    public function render(): View
    {
        return view('livewire.newsletter.editable.components.button');
    }

    /** @return array<string, mixed> */
    protected function savedProperties(): array
    {
        return [
            'content' => $this->label,
            'link' => $this->link,
        ];
    }
}

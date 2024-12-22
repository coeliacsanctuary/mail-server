<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

use Illuminate\View\View;

class TextWithButton extends NewsletterComponent
{
    public string $content;

    public string $label;

    public string $link;

    public function render(): View
    {
        return view('livewire.newsletter.editable.components.text-with-button');
    }

    public function mount(): void
    {
        $this->content = $this->properties['content'] ?? '';
        $this->label = $this->properties['label'] ?? '';
        $this->link = $this->properties['link'] ?? '';
    }

    public function updated(): void
    {
        $properties = [
            'content' => $this->content,
            'label' => $this->label,
            'link' => $this->link,
        ];

        $this->dispatch('component-updated', $this->blockId, $properties, $this->index);
    }
}

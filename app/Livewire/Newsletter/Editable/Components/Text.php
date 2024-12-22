<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

use Illuminate\View\View;

class Text extends NewsletterComponent
{
    public string $content;

    public function render(): View
    {
        return view('livewire.newsletter.editable.components.text');
    }

    public function mount(): void
    {
        $this->content = '';

        if (isset($this->properties['content'])) {
            $this->content = $this->properties['content'][0] ?? $this->properties['content'];
        }
    }

    public function updated(): void
    {
        $properties = [
            'content' => $this->content,
        ];

        $this->dispatch('component-updated', $this->blockId, $properties, $this->index);
    }
}

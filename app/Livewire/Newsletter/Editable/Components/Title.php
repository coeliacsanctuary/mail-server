<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

use Illuminate\View\View;

class Title extends NewsletterComponent
{
    public string $title;

    public ?string $link = null;

    public function render(): View
    {
        return view('livewire.newsletter.editable.components.title');
    }

    public function mount(): void
    {
        $this->title = $this->properties['content'] ?? '';

        if (isset($this->properties['link'])) {
            $this->link = $this->properties['link'];
        }
    }

    public function updated(): void
    {
        $properties = [
            'content' => $this->title,
        ];

        if ($this->link) {
            $properties['link'] = $this->link;
        }

        $this->dispatch('component-updated', $this->blockId, $properties, $this->index);
    }
}

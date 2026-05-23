<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

use Illuminate\View\View;

class TitleWithText extends NewsletterComponent
{
    public string $title;

    public ?string $link = null;

    public string $content;

    public function render(): View
    {
        return view('livewire.newsletter.editable.components.title-with-text');
    }

    public function mount(): void
    {
        $this->title = $this->properties['title'] ?? '';
        $this->link = $this->properties['link'] ?? null;
        $this->content = $this->properties['content'] ?? '';
    }

    public function updated(): void
    {
        $properties = [
            'title' => $this->title,
            'link' => $this->link,
            'content' => $this->content,
        ];

        $this->dispatch('component-updated', $this->blockId, $properties, $this->index);

        $this->skipRender();
    }
}

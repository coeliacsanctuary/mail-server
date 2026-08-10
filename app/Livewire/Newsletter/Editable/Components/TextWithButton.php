<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

use Illuminate\View\View;

class TextWithButton extends NewsletterComponent
{
    public string $content;

    public string $label;

    public string $link;

    public function mount(): void
    {
        $this->content = $this->properties['content'] ?? '';
        $this->label = $this->properties['label'] ?? '';
        $this->link = $this->properties['link'] ?? '';
    }

    public function updated(): void
    {
        $this->syncProperties();

        $this->skipRender();
    }

    public function render(): View
    {
        return view('livewire.newsletter.editable.components.text-with-button');
    }

    /** @return array<string, mixed> */
    protected function savedProperties(): array
    {
        return [
            'content' => $this->content,
            'label' => $this->label,
            'link' => $this->link,
        ];
    }
}

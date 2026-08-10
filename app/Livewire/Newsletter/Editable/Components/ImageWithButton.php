<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

use Illuminate\View\View;

class ImageWithButton extends ImageComponent
{
    public string $label = '';

    public function mount(): void
    {
        parent::mount();

        $this->label = $this->properties['label'] ?? '';
    }

    public function updatedLabel(): void
    {
        $this->syncProperties();

        $this->skipRender();
    }

    public function render(): View
    {
        return view('livewire.newsletter.editable.components.image-with-button');
    }

    /** @return array<string, mixed> */
    protected function savedProperties(): array
    {
        return [
            'content' => $this->properties['content'],
            'label' => $this->label,
            'link' => $this->link,
        ];
    }
}

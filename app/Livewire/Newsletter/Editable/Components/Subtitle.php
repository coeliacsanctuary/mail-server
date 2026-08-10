<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

use Illuminate\View\View;

class Subtitle extends NewsletterComponent
{
    public string $subtitle;

    public ?string $link = null;

    public function mount(): void
    {
        $this->subtitle = $this->properties['content'] ?? '';

        if (isset($this->properties['link'])) {
            $this->link = $this->properties['link'];
        }
    }

    public function updated(): void
    {
        $this->syncProperties();

        $this->skipRender();
    }

    public function render(): View
    {
        return view('livewire.newsletter.editable.components.subtitle');
    }

    /** @return array<string, mixed> */
    protected function savedProperties(): array
    {
        return [
            'content' => $this->subtitle,
            'link' => $this->link,
        ];
    }
}

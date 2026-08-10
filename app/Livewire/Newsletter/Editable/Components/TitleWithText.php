<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

use Illuminate\View\View;

/**
 * Deliberately not folded into a shared heading base with Title and Subtitle:
 * those store their heading under "content", this one stores it under "title"
 * and uses "content" for the body. Reconciling that would need either a data
 * migration or a per-subclass key indirection, and the keys collide.
 */
class TitleWithText extends NewsletterComponent
{
    public string $title;

    public ?string $link = null;

    public string $content;

    public function mount(): void
    {
        $this->title = $this->properties['title'] ?? '';
        $this->link = $this->properties['link'] ?? null;
        $this->content = $this->properties['content'] ?? '';
    }

    public function updated(): void
    {
        $this->syncProperties();

        $this->skipRender();
    }

    public function render(): View
    {
        return view('livewire.newsletter.editable.components.title-with-text');
    }

    /** @return array<string, mixed> */
    protected function savedProperties(): array
    {
        return [
            'title' => $this->title,
            'link' => $this->link,
            'content' => $this->content,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

use Illuminate\View\View;

/**
 * Title and Subtitle are the same component at two sizes - both persist their
 * text under "content" and an optional link.
 *
 * TitleWithText deliberately stays out: it stores its heading under "title"
 * and uses "content" for the body, so folding it in would need a data
 * migration or a per-subclass key indirection.
 */
abstract class HeadingComponent extends NewsletterComponent
{
    public string $content;

    public ?string $link = null;

    /** Shown as the field label and in the placeholder. */
    abstract protected function label(): string;

    public function mount(): void
    {
        $this->content = $this->properties['content'] ?? '';

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
        return view('livewire.newsletter.editable.components.heading', [
            'label' => $this->label(),
            'inputClass' => $this->inputClass(),
        ]);
    }

    protected function inputClass(): string
    {
        return 'text-2xl';
    }

    /** @return array<string, mixed> */
    protected function savedProperties(): array
    {
        return [
            'content' => $this->content,
            'link' => $this->link,
        ];
    }
}

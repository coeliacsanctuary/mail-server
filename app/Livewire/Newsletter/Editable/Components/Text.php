<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

use App\Editor\Support\Alignment;
use Illuminate\View\View;

class Text extends NewsletterComponent
{
    public string $content;

    public string $align;

    public function mount(): void
    {
        $this->content = '';

        if (isset($this->properties['content'])) {
            $this->content = is_array($this->properties['content'])
                ? $this->properties['content'][0]
                : $this->properties['content'];
        }

        $this->align = Alignment::fromName($this->properties['align'] ?? null)->value;
    }

    public function setAlign(string $align): void
    {
        $this->align = Alignment::fromName($align)->value;

        $this->syncProperties();
    }

    public function updated(): void
    {
        $this->syncProperties();

        $this->skipRender();
    }

    public function render(): View
    {
        return view('livewire.newsletter.editable.components.text');
    }

    /** @return array<string, mixed> */
    protected function savedProperties(): array
    {
        return [
            'content' => $this->content,
            'align' => $this->align,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

use Illuminate\View\View;

class Text extends NewsletterComponent
{
    public string $content;

    public function mount(): void
    {
        $this->content = '';

        if (isset($this->properties['content'])) {
            /** Legacy data: content used to be stored as an array of lines. */
            $this->content = is_array($this->properties['content'])
                ? $this->properties['content'][0]
                : $this->properties['content'];
        }
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
        ];
    }
}

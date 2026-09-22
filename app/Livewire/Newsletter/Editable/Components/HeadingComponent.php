<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

use Illuminate\View\View;

abstract class HeadingComponent extends NewsletterComponent
{
    public string $content;

    public ?string $link = null;

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
        return 'editable-heading editable-heading--title';
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

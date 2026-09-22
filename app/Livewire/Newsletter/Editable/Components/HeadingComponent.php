<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

use App\Editor\Support\Alignment;
use Illuminate\View\View;

abstract class HeadingComponent extends NewsletterComponent
{
    public string $content;

    public ?string $link = null;

    public string $align;

    abstract protected function label(): string;

    public function mount(): void
    {
        $this->content = $this->properties['content'] ?? '';

        if (isset($this->properties['link'])) {
            $this->link = $this->properties['link'];
        }

        $this->align = Alignment::fromName(
            $this->properties['align'] ?? null,
            $this->defaultAlignment(),
        )->value;
    }

    public function updated(): void
    {
        $this->syncProperties();

        $this->skipRender();
    }

    public function setAlign(string $align): void
    {
        $this->align = Alignment::fromName($align, $this->defaultAlignment())->value;

        $this->syncProperties();
    }

    public function render(): View
    {
        return view('livewire.newsletter.editable.components.heading', [
            'label' => $this->label(),
            'inputClass' => $this->inputClass(),
        ]);
    }

    protected function defaultAlignment(): Alignment
    {
        return Alignment::Left;
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
            'align' => $this->align,
        ];
    }
}

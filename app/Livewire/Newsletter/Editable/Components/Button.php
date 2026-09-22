<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

use App\Editor\Support\Alignment;
use App\Editor\Support\BrandColour;
use Illuminate\View\View;

class Button extends NewsletterComponent
{
    public string $label;

    public string $link;

    public string $textAlign;

    public string $background;

    public function mount(): void
    {
        $this->label = $this->properties['content'] ?? '';
        $this->link = $this->properties['link'] ?? '';
        $this->textAlign = Alignment::fromName($this->properties['text_align'] ?? null)->value;
        $this->background = BrandColour::fromName($this->properties['background'] ?? null)->value;
    }

    public function updated(): void
    {
        $this->syncProperties();

        $this->skipRender();
    }

    public function setBackground(string $background): void
    {
        $this->background = BrandColour::fromName($background)->value;

        $this->syncProperties();
    }

    public function setTextAlign(string $textAlign): void
    {
        $this->textAlign = Alignment::fromName($textAlign)->value;

        $this->syncProperties();
    }

    public function colour(): BrandColour
    {
        return BrandColour::fromName($this->background);
    }

    public function render(): View
    {
        return view('livewire.newsletter.editable.components.button');
    }

    /** @return array<string, mixed> */
    protected function savedProperties(): array
    {
        return [
            'content' => $this->label,
            'link' => $this->link,
            'text_align' => $this->textAlign,
            'background' => $this->background,
        ];
    }
}

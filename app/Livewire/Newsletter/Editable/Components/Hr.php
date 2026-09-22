<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

use App\Editor\Support\BrandColour;
use Illuminate\View\View;

class Hr extends NewsletterComponent
{
    public string $colour;

    public function mount(): void
    {
        $this->colour = BrandColour::fromName($this->properties['colour'] ?? null, BrandColour::Primary)->value;
    }

    public function setColour(string $colour): void
    {
        $this->colour = BrandColour::fromName($colour, BrandColour::Primary)->value;

        $this->syncProperties();
    }

    public function brandColour(): BrandColour
    {
        return BrandColour::fromName($this->colour, BrandColour::Primary);
    }

    public function render(): View
    {
        return view('livewire.newsletter.editable.components.hr');
    }

    /** @return array<string, mixed> */
    protected function savedProperties(): array
    {
        return ['colour' => $this->colour];
    }
}

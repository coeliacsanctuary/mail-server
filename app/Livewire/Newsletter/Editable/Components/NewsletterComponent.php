<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

use Illuminate\View\View;
use Livewire\Component;

abstract class NewsletterComponent extends Component
{
    public string $blockId;

    public string $block;

    public int $index;

    /** @var array<string, mixed> */
    public array $properties = [];

    abstract public function render(): View;

    /**
     * The properties persisted into the campaign's structured_html for this
     * component.
     *
     * These keys are stored data - five years of campaigns are already saved
     * with them - so renaming one is a migration, not a refactor. The key
     * ORDER is likewise preserved as-is per component.
     *
     * Concrete rather than abstract: components with no state of their own
     * (Hr) would otherwise need a pointless stub, and re-persisting whatever
     * was hydrated is a safe default.
     *
     * @return array<string, mixed>
     */
    protected function savedProperties(): array
    {
        return $this->properties;
    }

    /**
     * Push this component's current state up to the Editor, which writes it to
     * structured_html.
     *
     * Deliberately does not call skipRender(): persisting and "don't re-render
     * this response" are unrelated concerns, and two components need to
     * re-render after saving. Callers decide.
     */
    protected function syncProperties(): void
    {
        $this->properties = $this->savedProperties();

        $this->dispatch('component-updated', $this->blockId, $this->properties, $this->index);
    }
}

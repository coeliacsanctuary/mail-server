<?php

declare(strict_types=1);

namespace App\Editor;

use App\Editor\Support\Block;
use App\Editor\Support\BlockCollection;
use App\Editor\Support\BlockComponent;
use App\Editor\Support\NewsletterCompiler;
use Livewire\Attributes\On;
use Spatie\Mailcoach\Livewire\Editor\EditorComponent;

class Editor extends EditorComponent
{
    public function render()
    {
        return view('editor.editor', [
            'blocks' => $this->blocks()->toArray(),
        ]);
    }

    /**
     * The #[On] attribute must be re-declared here. Overriding a parent method
     * drops any Livewire attribute on the parent's declaration, which silently
     * unregisters the listener — Mailcoach's own Unlayer editor does the same.
     */
    #[On('saveContentQuietly')]
    public function saveQuietly(): void
    {
        $this->renderFullHtml();

        $this->model->setHtml($this->fullHtml);
        $this->model->save();

        $this->dispatch('editorUpdated', $this->modelUuid(), $this->previewHtml());
        $this->dispatch('editorSavedQuietly', uuid: $this->modelUuid());
    }

    public function renderFullHtml(): void
    {
        $this->fullHtml = (new NewsletterCompiler($this->model))->render();
    }

    #[On('add-block')]
    public function addBlock(string $type, ?string $after = null): void
    {
        $blocks = $this->blocks();

        $blocks->add(Block::make($type), $after);

        $this->persist($blocks);

        $this->dispatch('block-added', $blocks->toArray());
    }

    public function moveBlock(string $blockId, string $direction): void
    {
        $blocks = $this->blocks();

        $blocks->move($blockId, $direction);

        $this->persist($blocks);
    }

    /** Drag-and-drop reordering. $position is the block's index after the drop. */
    public function reorderBlock(string $blockId, int $position): void
    {
        $blocks = $this->blocks();

        $blocks->moveTo($blockId, $position);

        $this->persist($blocks);
    }

    public function duplicateBlock(string $blockId): void
    {
        $blocks = $this->blocks();

        $blocks->duplicate($blockId);

        $this->persist($blocks);
    }

    public function deleteBlock(string $blockId): void
    {
        $blocks = $this->blocks();

        $blocks->remove($blockId);

        $this->persist($blocks);
    }

    #[On('add-component-remote')]
    public function addComponent(string $blockId, string $component, int $index): void
    {
        $blocks = $this->blocks();

        $blocks->find($blockId)->putComponent($index, new BlockComponent($component));

        $this->persist($blocks);
    }

    /** Empties a column, which restores the "Add Component" placeholder. */
    public function removeComponent(string $blockId, int $index): void
    {
        $blocks = $this->blocks();

        $blocks->find($blockId)->removeComponent($index);

        $this->persist($blocks);
    }

    /**
     * @param array<string, mixed> $properties
     */
    #[On('component-updated')]
    public function saveComponent(string $blockId, array $properties, int $index): void
    {
        $blocks = $this->blocks();

        $blocks->find($blockId)->updateComponentProperties($index, $properties);

        $this->persist($blocks);

        $this->dispatch('editorUpdated', $this->modelUuid(), $this->previewHtml());
    }

    /**
     * Mailcoach's HasHtmlContent interface does not declare a uuid, but every
     * implementation of it (ContentItem, Template) is an Eloquent model that
     * has one, and the preview pane is keyed on it.
     */
    protected function modelUuid(): string
    {
        return (string) data_get($this->model, 'uuid');
    }

    protected function blocks(): BlockCollection
    {
        return BlockCollection::fromJson($this->model->getStructuredHtml());
    }

    protected function persist(BlockCollection $blocks): void
    {
        $this->model->update(['structured_html' => $blocks->toJson()]);
    }
}

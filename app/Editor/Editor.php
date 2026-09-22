<?php

declare(strict_types=1);

namespace App\Editor;

use App\Editor\Support\Block;
use App\Editor\Support\BlockCollection;
use App\Editor\Support\BlockComponent;
use App\Editor\Support\NewsletterCompiler;
use Livewire\Attributes\On;
use Spatie\Mailcoach\Domain\Content\Models\Concerns\HasHtmlContent;
use Spatie\Mailcoach\Livewire\Editor\EditorComponent;

class Editor extends EditorComponent
{
    public string $preheader = '';

    public function mount(HasHtmlContent $model): void
    {
        parent::mount($model);

        $this->preheader = $this->blocks()->preheader();
    }

    public function render()
    {
        return view('editor.editor', [
            'blocks' => $this->blocks()->toArray(),
        ]);
    }

    public function updated(): void
    {
    }

    public function updatedPreheader(): void
    {
        $blocks = $this->blocks();

        $blocks->setPreheader($this->preheader);

        $this->persist($blocks);

        $this->skipRender();
    }

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

    /** @param list<string> $components */
    #[On('add-component-remote')]
    public function addComponent(string $blockId, array $components, int $index): void
    {
        $blocks = $this->blocks();

        $block = $blocks->find($blockId);

        foreach ($components as $component) {
            $block->appendComponent($index, new BlockComponent($component));
        }

        $this->persist($blocks);
    }

    public function removeComponent(string $componentId): void
    {
        $blocks = $this->blocks();

        $blocks->removeComponent($componentId);

        $this->persist($blocks);
    }

    public function reorderComponent(string $componentId, int $position): void
    {
        $blocks = $this->blocks();

        $blocks->moveComponentTo($componentId, $position);

        $this->persist($blocks);
    }

    /** @param array<string, mixed> $properties */
    #[On('component-updated')]
    public function saveComponent(string $componentId, array $properties): void
    {
        $blocks = $this->blocks();

        $blocks->updateComponentProperties($componentId, $properties);

        $this->persist($blocks);

        $this->dispatch('editorUpdated', $this->modelUuid(), $this->previewHtml());
    }

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

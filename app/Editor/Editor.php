<?php

declare(strict_types=1);

namespace App\Editor;

use App\Editor\Support\NewsletterCompiler;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Spatie\Mailcoach\Livewire\Editor\EditorComponent;
use RuntimeException;

class Editor extends EditorComponent
{
    public function render()
    {
        return view('editor.editor', [
            'blocks' => Arr::get($this->getBlocks(), 'blocks', [])
        ]);
    }

    public function saveQuietly(): void
    {
        $this->renderFullHtml();

        $this->model->setHtml($this->fullHtml);
        $this->model->save();

        $this->dispatch('editorUpdated', $this->model->uuid, $this->previewHtml());
    }

    public function renderFullHtml(): void
    {
        $compiler = new NewsletterCompiler($this->model);

        $this->fullHtml = $compiler->render();
    }

    #[On('add-block')]
    public function addBlock(string $type, ?string $after = null): void
    {
        $properties = match ($type) {
            'triple' => [
                ['component' => null],
                ['component' => null],
                ['component' => null],
            ],
            'double' => [
                ['component' => null],
                ['component' => null],
            ],
            default => [['component' => null]],
        };

        $block = [
            'id' => Str::uuid(),
            'block' => $type,
            'properties' => $properties,
        ];

        /** @var ?array $data */
        $data = $this->getBlocks();

        if ( ! is_array($data)) {
            $data = [];
        }

        if ( ! isset($data['blocks'])) {
            $data['blocks'] = [];
        }

        if ($after) {
            $index = $this->getBlockToUpdateIndex($data['blocks'], $after);

            $remainingBlocks = array_splice($data['blocks'], $index + 1);

            $blocks = [
                ...$data['blocks'],
                $block,
                ...$remainingBlocks,
            ];

            $data['blocks'] = $blocks;
        } else {
            $data['blocks'][] = $block;
        }

        $this->model->update(['structured_html' => json_encode($data)]);

        $this->dispatch('block-added', $data['blocks']);
    }

    public function moveBlock(string $blockId, string $direction): void
    {
        /** @var array $data */
        $data = $this->getBlocks();

        $indexToUpdate = $this->getBlockToUpdateIndex($data['blocks'], $blockId);

        $newBlocks = $direction === 'up' ? $this->moveItemUp($data['blocks'], $indexToUpdate) : $this->moveItemDown($data['blocks'], $indexToUpdate);

        $data['blocks'] = $newBlocks;

        $this->model->update(['structured_html' => json_encode($data)]);
    }

    public function deleteBlock(string $blockId): void
    {
        /** @var array $data */
        $data = $this->getBlocks();

        $indexToDelete = $this->getBlockToUpdateIndex($data['blocks'], $blockId);

        unset($data['blocks'][$indexToDelete]);

        $data['blocks'] = array_values($data['blocks']);

        $this->model->update(['structured_html' => json_encode($data)]);
    }

    #[On('add-component-remote')]
    public function addComponent(string $blockId, string $component, int $index): void
    {
        /** @var array $data */
        $data = $this->getBlocks();

        $indexToUpdate = $this->getBlockToUpdateIndex($data['blocks'], $blockId);

        $data['blocks'][$indexToUpdate]['properties'][$index]['component'] = [
            'name' => $component,
            'properties' => [],
        ];

        $this->model->update(['structured_html' => json_encode($data)]);
    }

    #[On('component-updated')]
    public function saveComponent(string $blockId, array $properties, int $index): void
    {
        /** @var array $data */
        $data = $this->getBlocks();

        $indexToUpdate = $this->getBlockToUpdateIndex($data['blocks'], $blockId);

        $data['blocks'][$indexToUpdate]['properties'][$index]['component']['properties'] = $properties;

        $this->model->update(['structured_html' => json_encode($data)]);
        $this->dispatch('editorUpdated', $this->model->uuid, $this->previewHtml());
    }

    protected function getBlocks(): mixed
    {
        return json_decode($this->model->getStructuredHtml(), true);
    }

    protected function getBlockToUpdateIndex($blocks, string $blockId): string|int
    {
        $ids = Arr::pluck($blocks, 'id');
        $indexToUpdate = array_search($blockId, $ids);

        if ($indexToUpdate === false) {
            throw new RuntimeException('No block');
        }

        return $indexToUpdate;
    }

    protected function moveItemDown(array $array, int $index): array
    {
        $rtr = array_slice($array, 0, $index, true);
        $rtr[] = $array[$index + 1];
        $rtr[] = $array[$index];
        $rtr += array_slice($array, $index + 2, count($array), true);

        return($rtr);
    }

    protected function moveItemUp(array $array, int $index): array
    {
        $rtr = array_slice($array, 0, ($index - 1), true);
        $rtr[] = $array[$index];
        $rtr[] = $array[$index - 1];
        $rtr += array_slice($array, ($index + 1), count($array), true);
        return($rtr);
    }


}

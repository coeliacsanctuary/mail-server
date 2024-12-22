<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\WithFileUploads;

class ImageWithButton extends NewsletterComponent
{
    use WithFileUploads;

    /** @var UploadedFile */
    public $image;

    public string $label;

    public string $link;

    public function render(): View
    {
        return view('livewire.newsletter.editable.components.image-with-button');
    }

    public function mount(): void
    {
        $this->image = $this->properties['content'] ?? '';
        $this->label = $this->properties['label'] ?? '';
        $this->link = $this->properties['link'] ?? '';
    }

    public function updated($property): void
    {
        if ($property === 'image') {
            $upload = $this->image->storeAs($this->blockId, $this->image->getFilename(), ['disk' => 's3', 'visibility' => 'public']);

            $this->properties['content'] = Storage::disk('s3')->url($upload);
        }

        $properties = [
            'content' => $this->properties['content'],
            'label' => $this->label,
            'link' => $this->link,
        ];

        $this->dispatch('component-updated', $this->blockId, $properties, $this->index);
    }
}

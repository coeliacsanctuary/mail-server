<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\WithFileUploads;

class Image extends NewsletterComponent
{
    use WithFileUploads;

    /** @var UploadedFile */
    public $image;

    public ?string $link = null;

    public function render(): View
    {
        return view('livewire.newsletter.editable.components.image');
    }

    public function mount(): void
    {
        $this->image = $this->properties['content'] ?? '';
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
            'link' => $this->link,
        ];

        $this->dispatch('component-updated', $this->blockId, $properties, $this->index);
    }
}

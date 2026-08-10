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

    /** @var UploadedFile|string Holds the stored URL until a new file is chosen. */
    public $image;

    public ?string $link = null;

    public function mount(): void
    {
        $this->image = $this->properties['content'] ?? '';
        $this->link = $this->properties['link'] ?? '';
    }

    public function updatedImage(): void
    {
        $this->storeImage();

        $this->syncProperties();

        /** No skipRender: the view has to re-render to show the new image. */
    }

    public function updatedLink(): void
    {
        $this->syncProperties();

        $this->skipRender();
    }

    public function render(): View
    {
        return view('livewire.newsletter.editable.components.image');
    }

    protected function storeImage(): void
    {
        $upload = $this->image->storeAs(
            $this->blockId,
            $this->image->getFilename(),
            ['disk' => 's3', 'visibility' => 'public'],
        );

        $this->properties['content'] = Storage::disk('s3')->url($upload);
    }

    /** @return array<string, mixed> */
    protected function savedProperties(): array
    {
        return [
            'content' => $this->properties['content'],
            'link' => $this->link,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

/**
 * Shared upload handling for Image and ImageWithButton.
 *
 * Only the PHP is shared. The two rendered views differ in ways that change
 * what subscribers see (fluid-on-mobile vs fluid-on-width, and whether the
 * output is guarded on having any content at all), so unifying those is a
 * deliberate decision with a test send, not a refactor.
 */
abstract class ImageComponent extends NewsletterComponent
{
    use WithFileUploads;

    /** @var UploadedFile|string Holds the stored URL until a new file is chosen. */
    public $image;

    public string $link = '';

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

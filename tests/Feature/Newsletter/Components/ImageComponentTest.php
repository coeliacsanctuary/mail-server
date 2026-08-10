<?php

declare(strict_types=1);

namespace Tests\Feature\Newsletter\Components;

use App\Livewire\Newsletter\Editable\Components\Image;
use App\Livewire\Newsletter\Editable\Components\ImageWithButton;
use ErrorException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\ComponentData;
use Tests\TestCase;

class ImageComponentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Both the destination disk and Livewire's temporary upload disk
        // (config/livewire.php) are s3, so this has to be faked before any set().
        Storage::fake('s3');
    }

    /** @param array<string, mixed> $properties */
    private function mountComponent(string $class, array $properties = []): Testable
    {
        return Livewire::test($class, [
            'blockId' => 'block-1',
            'block' => 'single',
            'index' => 0,
            'properties' => $properties,
        ]);
    }

    /** @return array<string, array{class-string, array<string, mixed>}> */
    public static function imageComponentProvider(): array
    {
        return [
            'image' => [Image::class, ComponentData::image()],
            'image with button' => [ImageWithButton::class, ComponentData::imageWithButton()],
        ];
    }

    #[DataProvider('imageComponentProvider')]
    public function test_it_stores_an_upload_under_the_block_id(string $class, array $properties): void
    {
        $this->mountComponent($class)
            ->set('image', UploadedFile::fake()->image('photo.jpg'))
            ->assertDispatched(
                'component-updated',
                fn ($event, $params) => str_contains($params[1]['content'], '/block-1/'),
            );

        $this->assertNotEmpty(Storage::disk('s3')->files('block-1'));
    }

    /**
     * Pins that the object key is Livewire's temporary filename, not the name
     * the user uploaded. Unique, but not human readable, and it carries
     * Livewire's base64 original-name marker into the bucket.
     */
    #[DataProvider('imageComponentProvider')]
    public function test_the_stored_name_is_the_livewire_temp_name_not_the_original(string $class, array $properties): void
    {
        $this->mountComponent($class)
            ->set('image', UploadedFile::fake()->image('photo.jpg'));

        $stored = Storage::disk('s3')->files('block-1');

        $this->assertCount(1, $stored);
        $this->assertStringNotContainsString('photo.jpg', $stored[0]);
    }

    #[DataProvider('imageComponentProvider')]
    public function test_updating_the_link_does_not_re_upload(string $class, array $properties): void
    {
        $this->mountComponent($class, $properties)
            ->set('link', 'https://coeliac.invalid/changed')
            ->assertDispatched(
                'component-updated',
                fn ($event, $params) => $params[1]['link'] === 'https://coeliac.invalid/changed'
                    && $params[1]['content'] === $properties['content'],
            );

        $this->assertEmpty(Storage::disk('s3')->allFiles());
    }

    /**
     * Pins the one component that breaks on the editor side rather than at
     * render time: updated() reads $this->properties['content'] unconditionally,
     * and a freshly added component has no properties at all.
     *
     * Not reachable through the UI - both views hide the link input behind
     * @if($image) - but a shared base class could make it reachable.
     */
    #[DataProvider('imageComponentProvider')]
    public function test_setting_a_link_before_uploading_throws(string $class, array $properties): void
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessageMatches('/Undefined array key "content"/');

        $this->mountComponent($class)->set('link', 'https://coeliac.invalid');
    }

    public function test_image_with_button_also_persists_its_label(): void
    {
        $this->mountComponent(ImageWithButton::class, ComponentData::imageWithButton())
            ->set('label', 'Buy now')
            ->assertDispatched(
                'component-updated',
                fn ($event, $params) => array_keys($params[1]) === ['content', 'label', 'link']
                    && $params[1]['label'] === 'Buy now',
            );
    }
}

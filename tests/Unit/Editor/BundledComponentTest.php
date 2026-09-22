<?php

declare(strict_types=1);

namespace Tests\Unit\Editor;

use App\Editor\Support\BlockCollection;
use App\Editor\Support\BundledComponent;
use Tests\TestCase;

class BundledComponentTest extends TestCase
{
    public function test_a_primitive_component_is_not_a_bundle(): void
    {
        foreach (['button', 'image', 'text', 'title', 'hr', 'blog', 'recipe', 'product', 'eatery'] as $name) {
            $this->assertNull(BundledComponent::expand($name, []));
        }
    }

    public function test_image_with_button_moves_the_label_into_the_button(): void
    {
        $expanded = BundledComponent::expand('image-with-button', [
            'content' => 'https://coeliac.invalid/a.jpg',
            'label' => 'Read more',
            'link' => 'https://coeliac.invalid/blog',
            'alt' => 'A loaf',
        ]);

        $this->assertSame(['image', 'button'], array_column($expanded, 'name'));
        $this->assertSame(
            ['content' => 'https://coeliac.invalid/a.jpg', 'link' => 'https://coeliac.invalid/blog', 'alt' => 'A loaf'],
            $expanded[0]['properties'],
        );
        $this->assertSame('Read more', $expanded[1]['properties']['content']);
        $this->assertSame('https://coeliac.invalid/blog', $expanded[1]['properties']['link']);
    }

    public function test_image_with_button_drops_the_button_when_the_label_is_empty(): void
    {
        $expanded = BundledComponent::expand('image-with-button', [
            'content' => 'https://coeliac.invalid/a.jpg',
            'label' => '',
        ]);

        $this->assertSame(['image'], array_column($expanded, 'name'));
    }

    public function test_text_with_button_keeps_the_text_and_moves_the_label(): void
    {
        $expanded = BundledComponent::expand('text-with-button', [
            'content' => "First line.\nSecond line.",
            'label' => 'Read more',
            'link' => 'https://coeliac.invalid/blog',
        ]);

        $this->assertSame(['text', 'button'], array_column($expanded, 'name'));
        $this->assertSame("First line.\nSecond line.", $expanded[0]['properties']['content']);
        $this->assertSame('Read more', $expanded[1]['properties']['content']);
    }

    public function test_title_with_text_moves_the_title_into_a_title_component(): void
    {
        $expanded = BundledComponent::expand('title-with-text', [
            'title' => 'A Newsletter Title',
            'link' => 'https://coeliac.invalid/blog',
            'content' => "First line.\nSecond line.",
        ]);

        $this->assertSame(['title', 'text'], array_column($expanded, 'name'));
        $this->assertSame(
            ['content' => 'A Newsletter Title', 'link' => 'https://coeliac.invalid/blog'],
            $expanded[0]['properties'],
        );
        $this->assertSame("First line.\nSecond line.", $expanded[1]['properties']['content']);
    }

    public function test_a_stored_bundle_reads_back_as_a_stack(): void
    {
        $json = json_encode(['blocks' => [[
            'id' => 'block-1',
            'block' => 'single',
            'properties' => [['component' => [
                'name' => 'image-with-button',
                'properties' => ['content' => 'a.jpg', 'label' => 'Read more', 'link' => 'https://x.test'],
            ]]],
        ]]], JSON_THROW_ON_ERROR);

        $column = BlockCollection::fromJson($json)->find('block-1')->columns[0];

        $this->assertSame(['image', 'button'], array_map(fn ($c) => $c->name, $column));
        $this->assertNotSame($column[0]->id, $column[1]->id);
    }

    public function test_expanding_is_idempotent_once_saved(): void
    {
        $json = json_encode(['blocks' => [[
            'id' => 'block-1',
            'block' => 'single',
            'properties' => [['component' => [
                'name' => 'image-with-button',
                'properties' => ['content' => 'a.jpg', 'label' => 'Read more', 'link' => 'https://x.test'],
            ]]],
        ]]], JSON_THROW_ON_ERROR);

        $once = BlockCollection::fromJson($json)->toJson();
        $twice = BlockCollection::fromJson($once)->toJson();

        $this->assertSame($once, $twice);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Editor;

use App\Editor\Editor;
use Livewire\Livewire;
use RuntimeException;
use Tests\Support\ComponentData;
use Tests\Support\Concerns\ReadsStructuredHtml;
use Tests\Support\NewsletterBuilder;
use Tests\TestCase;

class AddComponentTest extends TestCase
{
    use ReadsStructuredHtml;

    public function test_it_places_a_component_in_the_given_column(): void
    {
        $contentItem = NewsletterBuilder::make()->double()->empty()->empty()->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('addComponent', 'block-1', 'title', 1);

        $this->assertNull($this->componentAt($contentItem, 0, 0));
        $this->assertSame(
            ['name' => 'title', 'properties' => []],
            $this->componentAt($contentItem, 0, 1),
        );
    }

    public function test_it_leaves_sibling_blocks_untouched(): void
    {
        $contentItem = NewsletterBuilder::make()
            ->single()->with('title', ComponentData::title())
            ->single()->empty()
            ->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('addComponent', 'block-2', 'hr', 0);

        $this->assertEquals(
            ['name' => 'title', 'properties' => ComponentData::title()],
            $this->componentAt($contentItem, 0, 0),
        );
    }

    /**
     * Pins that adding a component over an existing one discards its
     * properties without warning.
     */
    public function test_it_overwrites_an_existing_component_destructively(): void
    {
        $contentItem = NewsletterBuilder::make()
            ->single()->with('title', ComponentData::title())
            ->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('addComponent', 'block-1', 'hr', 0);

        $this->assertSame(
            ['name' => 'hr', 'properties' => []],
            $this->componentAt($contentItem, 0, 0),
        );
    }

    /**
     * The index comes from the browser. An out-of-range one used to grow the
     * block a sparse extra column; it is now ignored.
     */
    public function test_an_out_of_range_index_is_ignored(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->empty()->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('addComponent', 'block-1', 'hr', 2);

        $properties = $this->blocks($contentItem)[0]['properties'];

        $this->assertCount(1, $properties);
        $this->assertNull($this->componentAt($contentItem, 0, 0));
    }

    public function test_it_throws_when_the_block_does_not_exist(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->empty()->create();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No block');

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('addComponent', 'nope', 'hr', 0);
    }

    public function test_adding_a_component_does_not_refresh_the_preview(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->empty()->create();

        $component = Livewire::test(Editor::class, ['model' => $contentItem]);

        $compiledOnMount = $this->mjml->timesCompiled();

        $component->call('addComponent', 'block-1', 'hr', 0)
            ->assertNotDispatched('editorUpdated');

        $this->assertSame($compiledOnMount, $this->mjml->timesCompiled());
    }
}

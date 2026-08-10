<?php

declare(strict_types=1);

namespace Tests\Support;

use Spatie\Mailcoach\Domain\Campaign\Models\Campaign;
use Spatie\Mailcoach\Domain\Content\Models\ContentItem;

/**
 * Builds the block tree the editor stores in content_items.structured_html.
 *
 *     NewsletterBuilder::make()
 *         ->single()->with('blog', ComponentData::blog())
 *         ->double()->with('recipe', ComponentData::recipe())->empty()
 *         ->create();
 *
 * Block ids are deterministic ("block-1", "block-2", …) so tests can say
 * ->call('moveBlock', 'block-2', 'up') rather than juggling uuids.
 */
final class NewsletterBuilder
{
    /** @var array<int, array<string, mixed>> */
    private array $blocks = [];

    /** @var array<string, mixed> Sibling top-level keys, e.g. Mailcoach's templateValues. */
    private array $extra = [];

    private int $cursor = 0;

    public static function make(): self
    {
        return new self();
    }

    public function single(?string $id = null): self
    {
        return $this->block('single', 1, $id);
    }

    public function double(?string $id = null): self
    {
        return $this->block('double', 2, $id);
    }

    public function triple(?string $id = null): self
    {
        return $this->block('triple', 3, $id);
    }

    /**
     * Fill the next column. Passing no properties gives the state
     * Editor::addComponent() creates - a component chosen but never edited.
     *
     * @param array<string, mixed> $properties
     */
    public function with(string $component, array $properties = []): self
    {
        $block = array_key_last($this->blocks);

        $this->blocks[$block]['properties'][$this->cursor]['component'] = [
            'name' => $component,
            'properties' => $properties,
        ];

        $this->cursor++;

        return $this;
    }

    /** Leave the next column without a component. */
    public function empty(): self
    {
        $this->cursor++;

        return $this;
    }

    /** @param array<string, mixed> $extra */
    public function preserving(array $extra): self
    {
        $this->extra = $extra;

        return $this;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [...$this->extra, 'blocks' => $this->blocks];
    }

    public function json(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }

    /**
     * Unsaved. Enough for the compiler, which only reads one attribute.
     *
     * If the compiler ever starts touching $campaign->template or ->model this
     * will need to become create().
     */
    public function contentItem(): ContentItem
    {
        return new ContentItem(['structured_html' => $this->json()]);
    }

    /** Persisted, for Livewire tests. */
    public function create(): ContentItem
    {
        $contentItem = $this->createCampaign()->contentItem;

        return $contentItem->refresh();
    }

    public function createCampaign(): Campaign
    {
        /**
         * Campaign's created hook already makes a ContentItem, so we update
         * that rather than using ContentItemFactory - whose defaults would
         * write a random html blob and its own templateValues.
         */
        $campaign = Campaign::factory()->create();

        $campaign->contentItem->update(['structured_html' => $this->json()]);

        return $campaign;
    }

    private function block(string $type, int $columns, ?string $id): self
    {
        $this->blocks[] = [
            'id' => $id ?? 'block-' . (count($this->blocks) + 1),
            'block' => $type,
            'properties' => array_fill(0, $columns, ['component' => null]),
        ];

        $this->cursor = 0;

        return $this;
    }
}

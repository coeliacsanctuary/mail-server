<?php

declare(strict_types=1);

namespace Tests\Support;

use Spatie\Mailcoach\Domain\Campaign\Models\Campaign;
use Spatie\Mailcoach\Domain\Content\Models\ContentItem;

final class NewsletterBuilder
{
    private array $blocks = [];

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

    public function empty(): self
    {
        $this->cursor++;

        return $this;
    }

    public function preserving(array $extra): self
    {
        $this->extra = $extra;

        return $this;
    }

    public function toArray(): array
    {
        return [...$this->extra, 'blocks' => $this->blocks];
    }

    public function json(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }

    public function contentItem(): ContentItem
    {
        return new ContentItem(['structured_html' => $this->json()]);
    }

    public function create(): ContentItem
    {
        $contentItem = $this->createCampaign()->contentItem;

        return $contentItem->refresh();
    }

    public function createCampaign(): Campaign
    {
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

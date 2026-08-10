<?php

declare(strict_types=1);

namespace Tests\Support\Concerns;

use Spatie\Mailcoach\Domain\Content\Models\ContentItem;

/**
 * The editor writes straight to the database, so assertions read back from it
 * rather than from the Livewire component's own model instance.
 */
trait ReadsStructuredHtml
{
    /** @return array<string, mixed> */
    protected function structuredHtml(ContentItem $contentItem): array
    {
        return json_decode($contentItem->refresh()->structured_html, true);
    }

    /** @return array<int, array<string, mixed>> */
    protected function blocks(ContentItem $contentItem): array
    {
        return $this->structuredHtml($contentItem)['blocks'];
    }

    /** @return array<int, string> */
    protected function blockIds(ContentItem $contentItem): array
    {
        return array_column($this->blocks($contentItem), 'id');
    }

    /**
     * Named componentAt() rather than component() - Laravel's TestCase already
     * has a component() method for rendering Blade components.
     *
     * @return array<string, mixed>|null
     */
    protected function componentAt(ContentItem $contentItem, int $block, int $column): ?array
    {
        return $this->blocks($contentItem)[$block]['properties'][$column]['component'];
    }
}

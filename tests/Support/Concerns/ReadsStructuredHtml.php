<?php

declare(strict_types=1);

namespace Tests\Support\Concerns;

use Spatie\Mailcoach\Domain\Content\Models\ContentItem;

trait ReadsStructuredHtml
{
    protected function structuredHtml(ContentItem $contentItem): array
    {
        return json_decode($contentItem->refresh()->structured_html, true);
    }

    protected function blocks(ContentItem $contentItem): array
    {
        return $this->structuredHtml($contentItem)['blocks'];
    }

    protected function blockIds(ContentItem $contentItem): array
    {
        return array_column($this->blocks($contentItem), 'id');
    }

    protected function componentAt(ContentItem $contentItem, int $block, int $column, int $position = 0): ?array
    {
        $component = $this->componentsAt($contentItem, $block, $column)[$position] ?? null;

        if ($component === null) {
            return null;
        }

        unset($component['id']);

        return $component;
    }

    /** @return list<array<string, mixed>> */
    protected function componentsAt(ContentItem $contentItem, int $block, int $column): array
    {
        return $this->blocks($contentItem)[$block]['properties'][$column]['components'] ?? [];
    }
}

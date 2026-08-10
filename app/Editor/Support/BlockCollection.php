<?php

declare(strict_types=1);

namespace App\Editor\Support;

use Illuminate\Support\Arr;

/**
 * The whole structured_html document: the ordered blocks, plus any sibling
 * top-level keys Mailcoach owns (templateValues), which are carried through
 * untouched.
 *
 * All JSON encoding and decoding for the editor lives here, so a malformed or
 * missing document degrades to "no blocks" in one place rather than throwing a
 * PHP warning from four different call sites.
 */
final class BlockCollection
{
    /**
     * @param list<Block>          $blocks
     * @param array<string, mixed> $siblingKeys
     */
    private function __construct(
        private array $blocks,
        private array $siblingKeys = [],
    ) {
    }

    public static function fromJson(?string $json): self
    {
        $data = json_decode($json ?? '', true);

        if ( ! is_array($data)) {
            return new self([]);
        }

        $blocks = array_map(
            fn (array $block) => Block::fromArray($block),
            array_values(array_filter($data['blocks'] ?? [], is_array(...))),
        );

        return new self($blocks, Arr::except($data, 'blocks'));
    }

    public function toJson(): string
    {
        return json_encode([
            ...$this->siblingKeys,
            'blocks' => $this->toArray(),
        ], JSON_THROW_ON_ERROR);
    }

    /** @return list<array<string, mixed>> */
    public function toArray(): array
    {
        return array_map(fn (Block $block) => $block->toArray(), $this->blocks);
    }

    public function find(string $id): Block
    {
        return $this->blocks[$this->indexOf($id)];
    }

    public function add(Block $block, ?string $after = null): void
    {
        if ($after === null) {
            $this->blocks[] = $block;

            return;
        }

        array_splice($this->blocks, $this->indexOf($after) + 1, 0, [$block]);
    }

    /** Moving past either end is a no-op rather than an out-of-bounds read. */
    public function move(string $id, string $direction): void
    {
        $index = $this->indexOf($id);
        $target = $direction === 'up' ? $index - 1 : $index + 1;

        if ($target < 0 || $target >= count($this->blocks)) {
            return;
        }

        [$this->blocks[$index], $this->blocks[$target]] = [$this->blocks[$target], $this->blocks[$index]];
    }

    public function remove(string $id): void
    {
        array_splice($this->blocks, $this->indexOf($id), 1);
    }

    private function indexOf(string $id): int
    {
        foreach ($this->blocks as $index => $block) {
            if ($block->id === $id) {
                return $index;
            }
        }

        throw BlockNotFound::make($id);
    }
}

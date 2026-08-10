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

        $this->moveTo($id, $direction === 'up' ? $index - 1 : $index + 1);
    }

    /**
     * Move a block to an absolute position. $position is the index in the
     * RESULTING list, which is what the drag-and-drop handler reports.
     *
     * An out-of-range position is a no-op rather than a clamp, matching
     * move() and Block::putComponent(). The negative guard is load-bearing:
     * array_splice($a, -1, 0, [$x]) inserts before the last element rather
     * than erroring, which would be a silent wrong answer.
     */
    public function moveTo(string $id, int $position): void
    {
        $index = $this->indexOf($id);

        if ($position < 0 || $position >= count($this->blocks)) {
            return;
        }

        [$block] = array_splice($this->blocks, $index, 1);

        array_splice($this->blocks, $position, 0, [$block]);
    }

    /** Insert a detached copy of a block directly after the original. */
    public function duplicate(string $id): void
    {
        $index = $this->indexOf($id);

        array_splice($this->blocks, $index + 1, 0, [$this->blocks[$index]->copy()]);
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

<?php

declare(strict_types=1);

namespace Tests\Unit\Editor;

use App\Editor\Support\Block;
use App\Editor\Support\BlockCollection;
use App\Editor\Support\BlockComponent;
use App\Editor\Support\BlockNotFound;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BlockCollectionTest extends TestCase
{
    private function json(array $blocks, array $siblingKeys = []): string
    {
        return json_encode([...$siblingKeys, 'blocks' => $blocks], JSON_THROW_ON_ERROR);
    }

    private function block(string $id, string $layout = 'single', array $columns = [null]): array
    {
        return [
            'id' => $id,
            'block' => $layout,
            'properties' => array_map(fn ($component) => ['component' => $component], $columns),
        ];
    }

    public function test_it_round_trips_a_document_unchanged(): void
    {
        $json = $this->json([
            $this->block('block-1', 'double', [
                ['name' => 'title', 'properties' => ['content' => 'Hello']],
                null,
            ]),
        ]);

        $this->assertJsonStringEqualsJsonString(
            $json,
            BlockCollection::fromJson($json)->toJson(),
        );
    }

    public function test_it_carries_sibling_top_level_keys_through(): void
    {
        $json = $this->json([$this->block('block-1')], ['templateValues' => ['html' => null]]);

        $decoded = json_decode(BlockCollection::fromJson($json)->toJson(), true);

        $this->assertSame(['html' => null], $decoded['templateValues']);
    }

    #[DataProvider('unusableDocumentProvider')]
    public function test_an_unusable_document_becomes_an_empty_collection(?string $json): void
    {
        $this->assertSame([], BlockCollection::fromJson($json)->toArray());
    }

    public static function unusableDocumentProvider(): array
    {
        return [
            'null' => [null],
            'empty string' => [''],
            'not json' => ['not json at all'],
            'a json scalar' => ['"a string"'],
            'json without a blocks key' => ['{"templateValues":{}}'],
        ];
    }

    public function test_a_component_without_a_name_is_read_as_an_empty_column(): void
    {
        $json = $this->json([$this->block('block-1', 'single', [['properties' => ['content' => 'Orphaned']]])]);

        $blocks = BlockCollection::fromJson($json);

        $this->assertNull($blocks->find('block-1')->columns[0]);
    }

    public function test_it_reports_a_missing_block_by_id(): void
    {
        $this->expectException(BlockNotFound::class);
        $this->expectExceptionMessage('No block [nope]');

        BlockCollection::fromJson($this->json([$this->block('block-1')]))->find('nope');
    }

    public function test_it_inserts_after_a_given_block(): void
    {
        $blocks = BlockCollection::fromJson($this->json([
            $this->block('block-1'),
            $this->block('block-2'),
        ]));

        $blocks->add(Block::make('single', 'new'), 'block-1');

        $this->assertSame(['block-1', 'new', 'block-2'], array_column($blocks->toArray(), 'id'));
    }

    public function test_it_appends_when_no_target_is_given(): void
    {
        $blocks = BlockCollection::fromJson($this->json([$this->block('block-1')]));

        $blocks->add(Block::make('single', 'new'));

        $this->assertSame(['block-1', 'new'], array_column($blocks->toArray(), 'id'));
    }

    #[DataProvider('moveProvider')]
    public function test_it_moves_blocks_within_bounds(string $id, string $direction, array $expected): void
    {
        $blocks = BlockCollection::fromJson($this->json([
            $this->block('block-1'),
            $this->block('block-2'),
            $this->block('block-3'),
        ]));

        $blocks->move($id, $direction);

        $this->assertSame($expected, array_column($blocks->toArray(), 'id'));
    }

    public static function moveProvider(): array
    {
        return [
            'middle up' => ['block-2', 'up', ['block-2', 'block-1', 'block-3']],
            'middle down' => ['block-2', 'down', ['block-1', 'block-3', 'block-2']],
            'first up is a no-op' => ['block-1', 'up', ['block-1', 'block-2', 'block-3']],
            'last down is a no-op' => ['block-3', 'down', ['block-1', 'block-2', 'block-3']],
        ];
    }

    #[DataProvider('layoutProvider')]
    public function test_it_creates_the_right_number_of_columns(string $layout, int $expected): void
    {
        $this->assertCount($expected, Block::make($layout)->columns);
    }

    public static function layoutProvider(): array
    {
        return [
            'single' => ['single', 1],
            'double' => ['double', 2],
            'triple' => ['triple', 3],
            'anything else falls back to one' => ['quadruple', 1],
        ];
    }

    public function test_an_out_of_range_column_is_ignored(): void
    {
        $block = Block::make('single', 'block-1');

        $block->putComponent(2, new BlockComponent('hr'));

        $this->assertCount(1, $block->columns);
        $this->assertNull($block->columns[0]);
    }

    public function test_properties_cannot_be_saved_into_an_empty_column(): void
    {
        $block = Block::make('single', 'block-1');

        $block->updateComponentProperties(0, ['content' => 'Nowhere to go']);

        $this->assertNull($block->columns[0]);
    }

    public function test_properties_are_saved_onto_an_existing_component(): void
    {
        $block = Block::make('single', 'block-1');
        $block->putComponent(0, new BlockComponent('title'));

        $block->updateComponentProperties(0, ['content' => 'Saved']);

        $this->assertSame(['content' => 'Saved'], $block->columns[0]->properties);
    }

    private function threeBlocks(): BlockCollection
    {
        return BlockCollection::fromJson($this->json([
            $this->block('block-1'),
            $this->block('block-2'),
            $this->block('block-3'),
        ]));
    }

    #[DataProvider('moveToProvider')]
    public function test_it_moves_a_block_to_an_absolute_position(int $position, array $expected): void
    {
        $blocks = $this->threeBlocks();

        $blocks->moveTo('block-2', $position);

        $this->assertSame($expected, array_column($blocks->toArray(), 'id'));
    }

    public static function moveToProvider(): array
    {
        return [
            'to the top' => [0, ['block-2', 'block-1', 'block-3']],
            'to the bottom' => [2, ['block-1', 'block-3', 'block-2']],
            'to its own position' => [1, ['block-1', 'block-2', 'block-3']],
            // array_splice($a, -1, 0, …) inserts before the LAST element.
            'a negative position is a no-op' => [-1, ['block-1', 'block-2', 'block-3']],
            'one past the end is a no-op' => [3, ['block-1', 'block-2', 'block-3']],
            'far past the end is a no-op' => [99, ['block-1', 'block-2', 'block-3']],
        ];
    }

    public function test_moving_to_a_position_reports_a_missing_block_without_mutating(): void
    {
        $blocks = $this->threeBlocks();

        try {
            $blocks->moveTo('nope', 0);
            $this->fail('Expected BlockNotFound.');
        } catch (BlockNotFound) {
            // Expected.
        }

        $this->assertSame(['block-1', 'block-2', 'block-3'], array_column($blocks->toArray(), 'id'));
    }

    /** Catches an implementation that rebuilds blocks instead of relocating them. */
    public function test_a_moved_block_carries_its_components_with_it(): void
    {
        $blocks = BlockCollection::fromJson($this->json([
            $this->block('block-1', 'single', [['name' => 'title', 'properties' => ['content' => 'Kept']]]),
            $this->block('block-2'),
        ]));

        $blocks->moveTo('block-1', 1);

        $moved = $blocks->find('block-1')->columns[0];

        $this->assertSame('title', $moved->name);
        $this->assertSame(['content' => 'Kept'], $moved->properties);
    }

    public function test_moving_to_a_position_keeps_the_blocks_a_list(): void
    {
        $blocks = $this->threeBlocks();

        $blocks->moveTo('block-3', 0);

        $this->assertSame([0, 1, 2], array_keys($blocks->toArray()));
    }

    public function test_moving_to_a_position_carries_sibling_top_level_keys_through(): void
    {
        $json = $this->json([$this->block('block-1'), $this->block('block-2')], ['templateValues' => ['html' => null]]);

        $blocks = BlockCollection::fromJson($json);
        $blocks->moveTo('block-2', 0);

        $decoded = json_decode($blocks->toJson(), true);

        $this->assertSame(['html' => null], $decoded['templateValues']);
    }

    /** Pins the delegation, so the two reorder paths cannot drift apart. */
    #[DataProvider('directionProvider')]
    public function test_moving_by_direction_is_moving_to_the_adjacent_position(
        string $direction,
        int $position,
    ): void {
        $byDirection = $this->threeBlocks();
        $byDirection->move('block-2', $direction);

        $byPosition = $this->threeBlocks();
        $byPosition->moveTo('block-2', $position);

        $this->assertSame(
            array_column($byDirection->toArray(), 'id'),
            array_column($byPosition->toArray(), 'id'),
        );
    }

    public static function directionProvider(): array
    {
        return [
            'up' => ['up', 0],
            'down' => ['down', 2],
        ];
    }

    public function test_it_duplicates_a_block_directly_after_the_original(): void
    {
        $blocks = $this->threeBlocks();

        $blocks->duplicate('block-1');

        $ids = array_column($blocks->toArray(), 'id');

        $this->assertCount(4, $ids);
        $this->assertSame('block-1', $ids[0]);
        $this->assertSame(['block-2', 'block-3'], array_slice($ids, 2));
    }

    public function test_a_duplicated_block_gets_a_new_uuid(): void
    {
        $blocks = $this->threeBlocks();

        $blocks->duplicate('block-1');

        $newId = array_column($blocks->toArray(), 'id')[1];

        $this->assertNotSame('block-1', $newId);
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $newId);
    }

    public function test_a_duplicated_block_copies_its_components(): void
    {
        $blocks = BlockCollection::fromJson($this->json([
            $this->block('block-1', 'double', [
                ['name' => 'title', 'properties' => ['content' => 'Original']],
                null,
            ]),
        ]));

        $blocks->duplicate('block-1');

        $copy = $blocks->toArray()[1];

        $this->assertSame('double', $copy['block']);
        $this->assertSame('title', $copy['properties'][0]['component']['name']);
        $this->assertSame(['content' => 'Original'], $copy['properties'][0]['component']['properties']);
        $this->assertNull($copy['properties'][1]['component']);
    }

    /**
     * The clone must be deep. A shallow copy would leave both blocks sharing
     * one BlockComponent, so editing either would edit both.
     */
    public function test_editing_a_duplicate_does_not_change_the_original(): void
    {
        $blocks = BlockCollection::fromJson($this->json([
            $this->block('block-1', 'single', [['name' => 'title', 'properties' => ['content' => 'Original']]]),
        ]));

        $blocks->duplicate('block-1');

        $copyId = array_column($blocks->toArray(), 'id')[1];
        $blocks->find($copyId)->updateComponentProperties(0, ['content' => 'Changed']);

        $this->assertSame(['content' => 'Original'], $blocks->find('block-1')->columns[0]->properties);
        $this->assertSame(['content' => 'Changed'], $blocks->find($copyId)->columns[0]->properties);
    }

    public function test_it_reports_a_missing_block_when_duplicating(): void
    {
        $this->expectException(BlockNotFound::class);

        $this->threeBlocks()->duplicate('nope');
    }

    public function test_removing_a_component_empties_the_column(): void
    {
        $block = Block::make('double', 'block-1');
        $block->putComponent(0, new BlockComponent('title'));
        $block->putComponent(1, new BlockComponent('hr'));

        $block->removeComponent(0);

        $this->assertNull($block->columns[0]);
        $this->assertSame('hr', $block->columns[1]->name);
    }

    public function test_removing_an_out_of_range_component_is_ignored(): void
    {
        $block = Block::make('single', 'block-1');
        $block->putComponent(0, new BlockComponent('title'));

        $block->removeComponent(2);

        $this->assertCount(1, $block->columns);
        $this->assertSame('title', $block->columns[0]->name);
    }

    public function test_removing_a_component_from_an_already_empty_column_is_harmless(): void
    {
        $block = Block::make('single', 'block-1');

        $block->removeComponent(0);

        $this->assertNull($block->columns[0]);
    }
}

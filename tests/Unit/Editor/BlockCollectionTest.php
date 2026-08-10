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
}

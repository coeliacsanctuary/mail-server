<?php

declare(strict_types=1);

namespace Tests\Feature\Editor;

use App\Editor\Editor;
use App\Editor\Support\BlockNotFound;
use Livewire\Exceptions\ComponentNotFoundException;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Mailcoach\Domain\Campaign\Models\Campaign;
use Spatie\Mailcoach\Domain\Content\Models\ContentItem;
use Tests\Support\ComponentData;
use Tests\Support\Concerns\ReadsStructuredHtml;
use Tests\TestCase;

class CorruptStructuredHtmlTest extends TestCase
{
    use ReadsStructuredHtml;

    private function contentItemWith(?string $structuredHtml): ContentItem
    {
        $contentItem = Campaign::factory()->create()->contentItem;
        $contentItem->update(['structured_html' => $structuredHtml]);

        return $contentItem->refresh();
    }

    #[DataProvider('brokenDocumentProvider')]
    public function test_block_mutations_report_a_missing_block(
        ?string $structuredHtml,
        string $method,
        array $arguments,
    ): void {
        $contentItem = $this->contentItemWith($structuredHtml);

        $this->expectException(BlockNotFound::class);
        $this->expectExceptionMessage('No block [block-1]');

        Livewire::test(Editor::class, ['model' => $contentItem])->call($method, ...$arguments);
    }

    public static function brokenDocumentProvider(): array
    {
        $methods = [
            'moveBlock' => ['block-1', 'up'],
            'deleteBlock' => ['block-1'],
            'addComponent' => ['block-1', ['hr'], 0],
        ];

        $cases = [];

        foreach (self::documentProvider() as $documentName => [$structuredHtml]) {
            foreach ($methods as $method => $arguments) {
                $cases["{$method} with {$documentName}"] = [$structuredHtml, $method, $arguments];
            }
        }

        return $cases;
    }

    #[DataProvider('documentProvider')]
    public function test_component_mutations_tolerate_a_broken_document(?string $structuredHtml): void
    {
        $contentItem = $this->contentItemWith($structuredHtml);

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->call('saveComponent', 'nope', [])
            ->call('removeComponent', 'nope')
            ->call('reorderComponent', 'nope', 0)
            ->assertOk();
    }

    #[DataProvider('documentProvider')]
    public function test_add_block_tolerates_a_broken_document(?string $structuredHtml): void
    {
        $contentItem = $this->contentItemWith($structuredHtml);

        Livewire::test(Editor::class, ['model' => $contentItem])->call('addBlock', 'single');

        $this->assertCount(1, $this->blocks($contentItem));
    }

    #[DataProvider('documentProvider')]
    public function test_the_editor_still_renders_with_a_broken_document(?string $structuredHtml): void
    {
        $contentItem = $this->contentItemWith($structuredHtml);

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->assertOk()
            ->assertSee('Add Block');
    }

    public static function documentProvider(): array
    {
        return [
            'null' => [null],
            'empty string' => [''],
            'not json' => ['not json at all'],
            'json without a blocks key' => ['{"templateValues":{}}'],
        ];
    }

    public function test_an_unknown_component_name_breaks_the_editor(): void
    {
        $contentItem = $this->contentItemWith(json_encode([
            'blocks' => [[
                'id' => 'block-1',
                'block' => 'single',
                'properties' => [['component' => ['name' => 'gone', 'properties' => ComponentData::title()]]],
            ]],
        ]));

        $this->expectException(ComponentNotFoundException::class);
        $this->expectExceptionMessage('newsletter.editable.components.gone');

        Livewire::test(Editor::class, ['model' => $contentItem]);
    }
}

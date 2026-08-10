<?php

declare(strict_types=1);

namespace Tests\Feature\Editor;

use App\Editor\Editor;
use ErrorException;
use Livewire\Exceptions\ComponentNotFoundException;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Mailcoach\Domain\Campaign\Models\Campaign;
use Spatie\Mailcoach\Domain\Content\Models\ContentItem;
use Tests\Support\ComponentData;
use Tests\Support\Concerns\ReadsStructuredHtml;
use Tests\TestCase;

/**
 * addBlock() guards against a missing document; moveBlock, deleteBlock,
 * addComponent and saveComponent all reach straight for $data['blocks'].
 *
 * Not reachable through the UI today - the first thing anyone does is add a
 * block, which creates the document - but it is the asymmetry that makes the
 * typed block objects worth introducing.
 */
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
    public function test_block_mutations_throw_on_a_broken_document(
        ?string $structuredHtml,
        string $expectedMessage,
        string $method,
        array $arguments,
    ): void {
        $contentItem = $this->contentItemWith($structuredHtml);

        $this->expectException(ErrorException::class);
        $this->expectExceptionMessageMatches($expectedMessage);

        Livewire::test(Editor::class, ['model' => $contentItem])->call($method, ...$arguments);
    }

    public static function brokenDocumentProvider(): array
    {
        $documents = [
            'null' => [null, '/Trying to access array offset on/'],
            'empty string' => ['', '/Trying to access array offset on/'],
            'not json' => ['not json at all', '/Trying to access array offset on/'],
            'json without a blocks key' => ['{"templateValues":{}}', '/Undefined array key "blocks"/'],
        ];

        $methods = [
            'moveBlock' => ['block-1', 'up'],
            'deleteBlock' => ['block-1'],
            'addComponent' => ['block-1', 'hr', 0],
            'saveComponent' => ['block-1', [], 0],
        ];

        $cases = [];

        foreach ($documents as $documentName => [$structuredHtml, $expectedMessage]) {
            foreach ($methods as $method => $arguments) {
                $cases["{$method} with {$documentName}"] = [
                    $structuredHtml,
                    $expectedMessage,
                    $method,
                    $arguments,
                ];
            }
        }

        return $cases;
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

    /**
     * The two sides of the editor disagree about unknown component names.
     *
     * editor/rendered.blade.php guards with View::exists() and skips them (see
     * NewsletterCompilerTest), but editable/block.blade.php interpolates the
     * name straight into <livewire:is> with no guard - so a stored name that no
     * longer resolves takes the whole editor page down, with no way to recover
     * through the UI.
     */
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

<?php

declare(strict_types=1);

namespace Tests\Feature\Editor;

use App\Editor\Editor;
use App\Editor\Support\NewsletterCompiler;
use Livewire\Livewire;
use Tests\Support\Concerns\ReadsStructuredHtml;
use Tests\Support\NewsletterBuilder;
use Tests\TestCase;

/**
 * The snippet inboxes show next to the subject line.
 *
 * Without it, Gmail and Outlook fall back to the top of the body — which for
 * these newsletters reads "Having trouble viewing this email? View Online".
 */
class PreheaderTest extends TestCase
{
    use ReadsStructuredHtml;

    private function mjmlFor(?string $preheader): string
    {
        $builder = NewsletterBuilder::make();

        if ($preheader !== null) {
            $builder->preserving(['preheader' => $preheader]);
        }

        return (new NewsletterCompiler($builder->single()->with('hr')->contentItem()))->renderMjml();
    }

    public function test_it_renders_a_hidden_preheader_at_the_top_of_the_body(): void
    {
        $mjml = $this->mjmlFor('This month: three new recipes.');

        $this->assertMjmlContains('<mj-raw>', $mjml);
        $this->assertMjmlContains('id="preheader"', $mjml);
        $this->assertMjmlContains('This month: three new recipes.', $mjml);
        $this->assertMjmlContains('display:none', $mjml);
    }

    /**
     * It carries an id because Mailcoach's Campaign::websiteSummary() reads
     * getElementById('preheader') to build the public archive blurb. MJML's
     * own <mj-preview> cannot carry one, which is why this is hand-written.
     */
    public function test_it_uses_an_id_rather_than_the_mj_preview_tag(): void
    {
        $mjml = $this->mjmlFor('Summary text');

        $this->assertMjmlContains('id="preheader"', $mjml);
        $this->assertMjmlNotContains('<mj-preview>', $mjml);
    }

    public function test_it_renders_before_the_header(): void
    {
        $mjml = $this->mjmlFor('First thing');

        $this->assertLessThan(
            mb_strpos($mjml, 'Having trouble viewing this email?'),
            mb_strpos($mjml, 'id="preheader"'),
        );
    }

    public function test_an_empty_preheader_renders_nothing(): void
    {
        foreach ([null, '', '   '] as $preheader) {
            $mjml = $this->mjmlFor($preheader);

            $this->assertMjmlNotContains('id="preheader"', $mjml);
            $this->assertMjmlNotContains('<mj-raw>', $mjml);
        }
    }

    public function test_it_escapes_the_preheader(): void
    {
        $mjml = $this->mjmlFor('Bread & butter <not html>');

        $this->assertMjmlContains('Bread &amp; butter &lt;not html&gt;', $mjml);
    }

    public function test_the_editor_hydrates_an_existing_preheader(): void
    {
        $contentItem = NewsletterBuilder::make()
            ->preserving(['preheader' => 'Already saved'])
            ->single()->with('hr')
            ->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->assertSet('preheader', 'Already saved');
    }

    public function test_the_editor_starts_empty_when_there_is_no_preheader(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->with('hr')->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->assertSet('preheader', '');
    }

    public function test_updating_the_preheader_persists_it(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->with('hr')->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->set('preheader', 'Newly typed');

        $this->assertSame('Newly typed', $this->structuredHtml($contentItem)['preheader']);
    }

    public function test_updating_the_preheader_leaves_the_blocks_alone(): void
    {
        $contentItem = NewsletterBuilder::make()
            ->single()->with('hr')
            ->single()->with('hr')
            ->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->set('preheader', 'Newly typed');

        $this->assertSame(['block-1', 'block-2'], $this->blockIds($contentItem));
    }

    /** It is invisible, so recompiling the preview for it would be pure cost. */
    public function test_updating_the_preheader_does_not_recompile_the_preview(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->with('hr')->create();

        $component = Livewire::test(Editor::class, ['model' => $contentItem]);

        $compiledOnMount = $this->mjml->timesCompiled();

        $component->set('preheader', 'Newly typed')
            ->assertNotDispatched('editorUpdated');

        $this->assertSame($compiledOnMount, $this->mjml->timesCompiled());
    }

    public function test_it_reaches_the_saved_html(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->with('hr')->create();

        $component = Livewire::test(Editor::class, ['model' => $contentItem]);
        $component->set('preheader', 'Reaches the email');
        $component->call('saveQuietly');

        $this->assertStringContainsString('id="preheader"', $contentItem->refresh()->html);
        $this->assertStringContainsString('Reaches the email', $contentItem->refresh()->html);
    }

    public function test_the_editor_renders_a_preview_text_field(): void
    {
        $contentItem = NewsletterBuilder::make()->single()->with('hr')->create();

        Livewire::test(Editor::class, ['model' => $contentItem])
            ->assertSee('Preview text');
    }
}

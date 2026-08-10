<?php

declare(strict_types=1);

namespace Tests\Feature\Newsletter\Components;

use App\Livewire\Newsletter\Editable\Components\Button;
use App\Livewire\Newsletter\Editable\Components\Hr;
use App\Livewire\Newsletter\Editable\Components\Subtitle;
use App\Livewire\Newsletter\Editable\Components\Text;
use App\Livewire\Newsletter\Editable\Components\TextWithButton;
use App\Livewire\Newsletter\Editable\Components\Title;
use App\Livewire\Newsletter\Editable\Components\TitleWithText;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\ComponentData;
use Tests\TestCase;

/**
 * The components with no external dependencies. What matters here is the exact
 * key set each persists - those keys are stored data, so the refactor must not
 * change them.
 */
class SimpleComponentTest extends TestCase
{
    /** @param array<string, mixed> $properties */
    private function mountComponent(string $class, array $properties = []): Testable
    {
        return Livewire::test($class, [
            'blockId' => 'block-1',
            'block' => 'single',
            'index' => 0,
            'properties' => $properties,
        ]);
    }

    /** @return array<string, array{array<string, mixed>}> */
    public static function simpleComponentProvider(): array
    {
        return [
            'title' => [[
                'class' => Title::class,
                'properties' => ComponentData::title(),
                'field' => 'content',
                'keys' => ['content', 'link'],
            ]],
            'subtitle' => [[
                'class' => Subtitle::class,
                'properties' => ComponentData::subtitle(),
                'field' => 'content',
                'keys' => ['content', 'link'],
            ]],
            'text' => [[
                'class' => Text::class,
                'properties' => ComponentData::text(),
                'field' => 'content',
                'keys' => ['content'],
            ]],
            'button' => [[
                'class' => Button::class,
                'properties' => ComponentData::button(),
                'field' => 'label',
                'keys' => ['content', 'link'],
            ]],
            'text with button' => [[
                'class' => TextWithButton::class,
                'properties' => ComponentData::textWithButton(),
                'field' => 'content',
                'keys' => ['content', 'label', 'link'],
            ]],
            // The odd one out: stores its heading under "title", not "content".
            'title with text' => [[
                'class' => TitleWithText::class,
                'properties' => ComponentData::titleWithText(),
                'field' => 'content',
                'keys' => ['title', 'link', 'content'],
            ]],
        ];
    }

    #[DataProvider('simpleComponentProvider')]
    public function test_it_persists_exactly_the_expected_keys(array $component): void
    {
        $this->mountComponent($component['class'], $component['properties'])
            ->set($component['field'], 'Something new')
            ->assertDispatched(
                'component-updated',
                fn ($event, $params) => array_keys($params[1]) === $component['keys']
                    && $params[0] === 'block-1'
                    && $params[2] === 0,
            );
    }

    public function test_title_hydrates_its_content_and_link(): void
    {
        $this->mountComponent(Title::class, ComponentData::title(['link' => 'https://coeliac.invalid']))
            ->assertSet('content', 'A Newsletter Title')
            ->assertSet('link', 'https://coeliac.invalid');
    }

    public function test_subtitle_shares_the_heading_component_state(): void
    {
        $this->mountComponent(Subtitle::class, ComponentData::subtitle())
            ->assertSet('content', 'A Newsletter Subtitle');
    }

    public function test_title_with_text_hydrates_all_three_fields(): void
    {
        $this->mountComponent(TitleWithText::class, ComponentData::titleWithText())
            ->assertSet('title', 'A Newsletter Title')
            ->assertSet('content', "First line.\nSecond line.");
    }

    /**
     * Pins the link default that makes empty hrefs possible: Title and
     * Subtitle leave it null (so @isset is false and no anchor renders), while
     * Button and TextWithButton default it to '' (so @isset is true).
     */
    public function test_title_and_subtitle_default_their_link_to_null(): void
    {
        $this->mountComponent(Title::class)->assertSet('link', null);
        $this->mountComponent(Subtitle::class)->assertSet('link', null);
    }

    public function test_button_and_text_with_button_default_their_link_to_an_empty_string(): void
    {
        $this->mountComponent(Button::class)->assertSet('link', '');
        $this->mountComponent(TextWithButton::class)->assertSet('link', '');
    }

    /** Legacy data: content used to be stored as an array of lines. */
    public function test_text_accepts_the_legacy_array_content_shape(): void
    {
        $this->mountComponent(Text::class, ['content' => ['Only line.', 'Ignored.']])
            ->assertSet('content', 'Only line.');
    }

    public function test_hr_has_no_state_and_persists_nothing(): void
    {
        $this->mountComponent(Hr::class)
            ->assertOk()
            ->assertNotDispatched('component-updated');
    }
}

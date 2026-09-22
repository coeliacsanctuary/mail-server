<?php

declare(strict_types=1);

namespace Tests\Feature\Newsletter\Components;

use App\Editor\Support\ButtonColour;
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

class SimpleComponentTest extends TestCase
{
    private function mountComponent(string $class, array $properties = []): Testable
    {
        return Livewire::test($class, [
            'blockId' => 'block-1',
            'block' => 'single',
            'componentId' => 'component-1',
            'properties' => $properties,
        ]);
    }

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
                'keys' => ['content', 'link', 'text_align', 'background'],
            ]],
            'text with button' => [[
                'class' => TextWithButton::class,
                'properties' => ComponentData::textWithButton(),
                'field' => 'content',
                'keys' => ['content', 'label', 'link'],
            ]],
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
                    && $params[0] === 'component-1',
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

    public function test_title_and_subtitle_default_their_link_to_null(): void
    {
        $this->mountComponent(Title::class)->assertSet('link', null);
        $this->mountComponent(Subtitle::class)->assertSet('link', null);
    }

    public function test_the_button_editor_renders_a_mockup_carrying_its_own_colours(): void
    {
        $html = $this->mountComponent(Button::class, ComponentData::button([
            'background' => 'primary-dark',
            'text_align' => 'left',
        ]))->html();

        $this->assertStringContainsString('button-mockup', $html);
        $this->assertStringContainsString('background-color: #29719f', $html);
        $this->assertStringContainsString('color: #ffffff', $html);
        $this->assertStringContainsString('text-align: left', $html);
    }

    public function test_the_button_editor_offers_every_colour_and_alignment(): void
    {
        $html = html_entity_decode($this->mountComponent(Button::class, ComponentData::button())->html());

        foreach (ButtonColour::cases() as $colour) {
            $this->assertStringContainsString("setBackground('{$colour->value}')", $html);
        }

        foreach (Button::ALIGNMENTS as $alignment) {
            $this->assertStringContainsString("setTextAlign('{$alignment}')", $html);
        }
    }

    public function test_a_button_with_no_properties_defaults_its_styling(): void
    {
        $component = $this->mountComponent(Button::class);

        $this->assertSame('center', $component->get('textAlign'));
        $this->assertSame('secondary', $component->get('background'));
    }

    public function test_an_unknown_colour_or_alignment_falls_back_to_the_default(): void
    {
        $component = $this->mountComponent(Button::class, ComponentData::button([
            'text_align' => 'diagonal',
            'background' => 'chartreuse',
        ]));

        $this->assertSame('center', $component->get('textAlign'));
        $this->assertSame('secondary', $component->get('background'));
    }

    public function test_choosing_a_colour_persists_it(): void
    {
        $this->mountComponent(Button::class, ComponentData::button())
            ->call('setBackground', 'primary-dark')
            ->assertDispatched(
                'component-updated',
                fn ($event, $params) => $params[1]['background'] === 'primary-dark',
            );
    }

    public function test_choosing_an_alignment_persists_it(): void
    {
        $this->mountComponent(Button::class, ComponentData::button())
            ->call('setTextAlign', 'right')
            ->assertDispatched(
                'component-updated',
                fn ($event, $params) => $params[1]['text_align'] === 'right',
            );
    }

    public function test_button_and_text_with_button_default_their_link_to_an_empty_string(): void
    {
        $this->mountComponent(Button::class)->assertSet('link', '');
        $this->mountComponent(TextWithButton::class)->assertSet('link', '');
    }

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

<?php

declare(strict_types=1);

namespace Tests\Feature\Editor;

use App\Editor\Support\NewsletterCompiler;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Spatie\Mailcoach\Domain\Shared\Actions\InitializeMjmlAction;
use Tests\Support\ComponentData;
use Tests\Support\NewsletterBuilder;
use Tests\TestCase;
use LibXMLError;

/**
 * The golden-MJML tests assert content; these assert structure.
 *
 * A refactor of the Blade views can produce MJML that a string assertion
 * happily accepts but that is not a balanced document - an unclosed tag, a
 * component wrapper that swallowed its closing element. Parsing catches that
 * without needing to compile anything.
 */
class MjmlStructureTest extends TestCase
{
    /** MJML permits HTML entities that XML does not define. */
    private function assertWellFormed(string $mjml): void
    {
        $parsable = str_replace(['&nbsp;', '&amp;nbsp;'], ' ', $mjml);

        libxml_use_internal_errors(true);
        libxml_clear_errors();

        $parsed = simplexml_load_string($parsable);

        $errors = array_map(
            fn (LibXMLError $error) => mb_trim($error->message) . ' on line ' . $error->line,
            libxml_get_errors(),
        );

        libxml_clear_errors();

        $this->assertNotFalse($parsed, 'MJML is not a balanced document: ' . implode('; ', $errors));
    }

    /** @return array<string, array{string}> */
    public static function componentProvider(): array
    {
        return array_combine(
            $names = [
                'title', 'title-with-text', 'subtitle', 'button', 'text', 'text-with-button',
                'hr', 'image', 'image-with-button', 'blog', 'recipe', 'product', 'eatery',
            ],
            array_map(fn (string $name) => [$name], $names),
        );
    }

    #[DataProvider('componentProvider')]
    public function test_each_component_produces_a_balanced_document(string $component): void
    {
        foreach (['single', 'double', 'triple'] as $block) {
            $builder = NewsletterBuilder::make();
            $builder = match ($block) {
                'double' => $builder->double(),
                'triple' => $builder->triple(),
                default => $builder->single(),
            };

            $mjml = (new NewsletterCompiler(
                $builder->with($component, self::propertiesFor($component))->contentItem(),
            ))->renderMjml();

            $this->assertWellFormed($mjml);
        }
    }

    public function test_a_newsletter_containing_every_component_is_balanced(): void
    {
        $builder = NewsletterBuilder::make();

        foreach (array_keys(self::componentProvider()) as $component) {
            $builder->single()->with($component, self::propertiesFor($component));
        }

        $this->assertWellFormed((new NewsletterCompiler($builder->contentItem()))->renderMjml());
    }

    public function test_an_empty_newsletter_is_balanced(): void
    {
        $this->assertWellFormed(
            (new NewsletterCompiler(NewsletterBuilder::make()->contentItem()))->renderMjml(),
        );
    }

    /**
     * The one test that compiles for real, through Sidecar. Excluded from the
     * default suite (see the "mjml" group in phpunit.xml) because it needs AWS
     * credentials and costs a Lambda invocation; run it before deploying.
     */
    #[Group('mjml')]
    public function test_a_full_newsletter_compiles_without_mjml_errors(): void
    {
        $this->app->forgetInstance(InitializeMjmlAction::class);
        $this->app->instance(InitializeMjmlAction::class, new InitializeMjmlAction());

        $builder = NewsletterBuilder::make();

        foreach (array_keys(self::componentProvider()) as $component) {
            $builder->single()->with($component, self::propertiesFor($component));
        }

        $html = (new NewsletterCompiler($builder->contentItem()))->render();

        $this->assertStringContainsString('<html', $html);
        $this->assertStringNotContainsString('<mj-', $html);
    }

    /** @return array<string, mixed> */
    private static function propertiesFor(string $component): array
    {
        return match ($component) {
            'title' => ComponentData::title(['link' => 'https://coeliac.invalid']),
            'subtitle' => ComponentData::subtitle(['link' => 'https://coeliac.invalid']),
            'title-with-text' => ComponentData::titleWithText(['link' => 'https://coeliac.invalid']),
            'text' => ComponentData::text(),
            'text-with-button' => ComponentData::textWithButton(),
            'button' => ComponentData::button(),
            'image' => ComponentData::image(),
            'image-with-button' => ComponentData::imageWithButton(),
            'blog' => ComponentData::blog(),
            'recipe' => ComponentData::recipe(),
            'product' => ComponentData::product(),
            'eatery' => ComponentData::eatery(),
            default => [],
        };
    }
}

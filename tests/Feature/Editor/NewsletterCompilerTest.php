<?php

declare(strict_types=1);

namespace Tests\Feature\Editor;

use App\Editor\Support\NewsletterCompiler;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Mailcoach\Domain\Content\Models\ContentItem;
use Tests\Support\ComponentData;
use Tests\Support\NewsletterBuilder;
use Tests\TestCase;

class NewsletterCompilerTest extends TestCase
{
    private function mjmlFor(NewsletterBuilder $builder): string
    {
        return (new NewsletterCompiler($builder->contentItem()))->renderMjml();
    }

    public function test_an_empty_newsletter_renders_the_skeleton_only(): void
    {
        $mjml = (new NewsletterCompiler(new ContentItem()))->renderMjml();

        $this->assertMjmlContains('<mj-body background-color="#f7f7f7">', $mjml);
        $this->assertMjmlContains('<a href="::webviewUrl::">View Online</a>', $mjml);
        $this->assertMjmlContains('<a href="::unsubscribeUrl::">click here</a>', $mjml);
        $this->assertMjmlContains('::subscriber.email::', $mjml);

        $this->assertSame(2, mb_substr_count($mjml, '<mj-wrapper>'));
    }

    public function test_the_header_logo_comes_from_the_coeliac_config(): void
    {
        $mjml = (new NewsletterCompiler(new ContentItem()))->renderMjml();

        $this->assertMjmlContains(
            '<mj-image src="https://coeliac.invalid/images/email/logo-new.jpg" alt="Coeliac Sanctuary" width="300">',
            $mjml,
        );
    }

    public function test_the_head_makes_the_gutter_columns_border_box(): void
    {
        $mjml = (new NewsletterCompiler(new ContentItem()))->renderMjml();

        $this->assertMjmlContains(
            '.double-0, .double-1, .triple-0, .triple-1, .triple-2 { box-sizing: border-box!important; }',
            $mjml,
        );
    }

    public function test_the_head_pairs_the_default_button_text_with_its_background(): void
    {
        $mjml = (new NewsletterCompiler(new ContentItem()))->renderMjml();

        $this->assertMjmlContains(
            '<mj-button background-color="#DBBC25" color="#222222" '
            . 'padding="0px" font-size="15px" font-weight="bold">',
            $mjml,
        );
    }

    public function test_the_head_restores_the_large_button_on_wide_screens(): void
    {
        $mjml = (new NewsletterCompiler(new ContentItem()))->renderMjml();

        $this->assertMjmlContains(
            '.single-button a { font-size: 20px!important; line-height: 120%!important; padding: 10px 25px!important; }',
            $mjml,
        );
    }

    public function test_the_head_carries_the_media_query_free_fluid_image_rules(): void
    {
        $mjml = (new NewsletterCompiler(new ContentItem()))->renderMjml();

        $this->assertMjmlContains('<mj-style>.fluid-img table { width: 100% !important; }</mj-style>', $mjml);
        $this->assertMjmlContains('<mj-style>.fluid-img td { width: 100% !important; }</mj-style>', $mjml);
        $this->assertMjmlContains(
            '<mj-style>.fluid-img img { width: 100% !important; max-width: 100% !important; }</mj-style>',
            $mjml,
        );
    }

    public function test_structured_html_that_is_not_json_renders_as_empty(): void
    {
        $contentItem = new ContentItem(['structured_html' => 'not json at all']);

        $mjml = (new NewsletterCompiler($contentItem))->renderMjml();

        $this->assertSame(2, mb_substr_count($mjml, '<mj-wrapper>'));
    }

    public function test_stacked_components_are_separated_by_a_spacer(): void
    {
        $mjml = $this->mjmlFor(
            NewsletterBuilder::make()->single()->stack('image', ComponentData::image())->and('hr'),
        );

        $this->assertMjmlContains(
            'fluid-on-mobile="true"></mj-image> <mj-spacer height="15px"></mj-spacer> '
            . '<mj-divider border-width="2px" border-color="#80CCFC"></mj-divider>',
            $mjml,
        );
    }

    public function test_a_lone_component_gets_no_spacer(): void
    {
        $mjml = $this->mjmlFor(NewsletterBuilder::make()->single()->with('hr'));

        $this->assertMjmlNotContains('mj-spacer', $mjml);
    }

    public function test_a_component_sits_directly_in_its_column(): void
    {
        $mjml = $this->mjmlFor(NewsletterBuilder::make()->single()->with('hr'));

        $this->assertMjmlContains(
            '<mj-wrapper> <mj-section> <mj-column css-class="full"> '
            . '<mj-divider border-width="2px" border-color="#80CCFC"></mj-divider> '
            . '</mj-column> </mj-section> </mj-wrapper>',
            $mjml,
        );
    }

    public function test_a_double_block_emits_two_columns_with_position_classes(): void
    {
        $mjml = $this->mjmlFor(
            NewsletterBuilder::make()
                ->double()
                ->with('blog', ComponentData::blog())
                ->with('recipe', ComponentData::recipe()),
        );

        $this->assertMjmlContains('<mj-column css-class="double-0">', $mjml);
        $this->assertMjmlContains('<mj-column css-class="double-1">', $mjml);
    }

    public function test_a_triple_block_emits_three_columns_with_position_classes(): void
    {
        $mjml = $this->mjmlFor(
            NewsletterBuilder::make()
                ->triple()
                ->with('blog', ComponentData::blog())
                ->with('blog', ComponentData::blog())
                ->with('blog', ComponentData::blog()),
        );

        $this->assertMjmlContains('<mj-column css-class="triple-0">', $mjml);
        $this->assertMjmlContains('<mj-column css-class="triple-1">', $mjml);
        $this->assertMjmlContains('<mj-column css-class="triple-2">', $mjml);
    }

    public function test_a_single_block_uses_the_full_column_class(): void
    {
        $mjml = $this->mjmlFor(NewsletterBuilder::make()->single()->with('blog', ComponentData::blog()));

        $this->assertMjmlContains('<mj-column css-class="full">', $mjml);
    }

    public function test_an_empty_column_still_emits_an_empty_mj_column(): void
    {
        $mjml = $this->mjmlFor(
            NewsletterBuilder::make()->double()->with('hr')->empty(),
        );

        $this->assertMjmlContains('</mj-column> <mj-column css-class="double-1"> </mj-column> </mj-section>', $mjml);
    }

    public function test_an_unknown_component_name_is_skipped_without_error(): void
    {
        $mjml = $this->mjmlFor(NewsletterBuilder::make()->single()->with('does-not-exist'));

        $this->assertMjmlContains('<mj-column css-class="full"> </mj-column>', $mjml);
        $this->assertMjmlNotContains('does-not-exist', $mjml);
    }

    #[DataProvider('componentProvider')]
    public function test_it_renders_each_component(
        string $component,
        string $block,
        array $properties,
        string $expected,
    ): void {
        $builder = NewsletterBuilder::make();
        $block === 'single' ? $builder->single() : $builder->double();

        $mjml = $this->mjmlFor($builder->with($component, $properties));

        $this->assertMjmlContains($expected, $mjml);
    }

    public static function componentProvider(): array
    {
        return [
            'hr' => [
                'hr', 'single', [],
                '<mj-divider border-width="2px" border-color="#80CCFC"></mj-divider>',
            ],

            'blog in a single block has a heading' => [
                'blog', 'single', ComponentData::blog(),
                '<mj-text mj-class="inner blue-links"> <h2 class="blue-links"> '
                . '<a href="https://coeliac.invalid/blog/a-gluten-free-blog">A Gluten Free Blog</a> </h2> </mj-text>',
            ],
            'blog in a single block has a large button' => [
                'blog', 'single', ComponentData::blog(),
                'padding="10px 0" border-radius="6px" font-size="16px" line-height="115%" inner-padding="8px 25px" css-class="single-button" > Read more </mj-button>',
            ],
            'blog in a double block has no heading' => [
                'blog', 'double', ComponentData::blog(),
                '<mj-column css-class="double-0"> <mj-image href="https://coeliac.invalid/blog/a-gluten-free-blog"',
            ],
            'blog image carries the fluid-img class' => [
                'blog', 'double', ComponentData::blog(),
                'css-class="fluid-img" fluid-on-mobile="true"',
            ],
            'recipe image carries the fluid-img class' => [
                'recipe', 'double', ComponentData::recipe(),
                'css-class="fluid-img" fluid-on-mobile="true"',
            ],

            'blog description is rendered unescaped' => [
                'blog', 'single', ComponentData::blog(['description' => '<em>Emphasised</em>']),
                '<mj-text css-class="blue-links"> <em>Emphasised</em> </mj-text>',
            ],

            'recipe' => [
                'recipe', 'single', ComponentData::recipe(),
                '<a href="https://coeliac.invalid/recipe/a-gluten-free-recipe">A Gluten Free Recipe</a>',
            ],

            'product has a price block' => [
                'product', 'single', ComponentData::product(),
                '<mj-text css-class="blue-links" padding-bottom="10px"> <h1> £4.99 </h1> </mj-text>',
            ],
            'product has its own button label' => [
                'product', 'single', ComponentData::product(),
                '> View Product </mj-button>',
            ],

            'eatery' => [
                'eatery', 'single', ComponentData::eatery(),
                '<h4 style="margin:0"> Crewe, Cheshire </h4>',
            ],
            'eatery with ratings' => [
                'eatery', 'single', ComponentData::eatery(),
                'Rated <strong style="font-weight: bold">4.5 stars</strong> from 12 ratings.',
            ],

            'title without a link' => [
                'title', 'single', ComponentData::title(),
                '<mj-text align="center" css-class="blue-links"> <h1> A Newsletter Title </h1> </mj-text>',
            ],
            'title with a link' => [
                'title', 'single', ComponentData::title(['link' => 'https://coeliac.invalid/blog']),
                '<h1> <a href="https://coeliac.invalid/blog"> A Newsletter Title </a> </h1>',
            ],
            'title falls back to a placeholder' => [
                'title', 'single', [],
                '<h1> [MISSING TITLE] </h1>',
            ],

            'subtitle' => [
                'subtitle', 'single', ComponentData::subtitle(),
                '<mj-text mj-class="inner" css-class="blue-links"> <h3> A Newsletter Subtitle </h3> </mj-text>',
            ],
            'subtitle falls back to a placeholder' => [
                'subtitle', 'single', [],
                '<h3> [MISSING SUBTITLE] </h3>',
            ],

            'text splits on newlines' => [
                'text', 'single', ComponentData::text(),
                '<mj-text mj-class="inner" css-class="blue-links">First line.</mj-text> '
                . '<mj-text mj-class="inner" css-class="blue-links">Second line.</mj-text>',
            ],
            'text accepts the legacy array shape' => [
                'text', 'single', ['content' => ['Only line.']],
                '<mj-text mj-class="inner" css-class="blue-links">Only line.</mj-text>',
            ],
            'text with no content emits one empty line' => [
                'text', 'single', [],
                '<mj-column css-class="full"> <mj-text mj-class="inner" css-class="blue-links"></mj-text> </mj-column>',
            ],

            'title with text splits the body on newlines' => [
                'title-with-text', 'single', ComponentData::titleWithText(),
                '<mj-text mj-class="inner" css-class="blue-links">First line.</mj-text> '
                . '<mj-text mj-class="inner" css-class="blue-links">Second line.</mj-text>',
            ],

            'image' => [
                'image', 'single', ComponentData::image(),
                '<mj-image href="https://coeliac.invalid/blog" src="https://coeliac.invalid/images/upload.jpg" alt="" fluid-on-mobile="true">',
            ],
            'image renders alt text when it has some' => [
                'image', 'single', ComponentData::image(['alt' => 'A loaf of bread']),
                'alt="A loaf of bread"',
            ],
            'image with button renders alt text when it has some' => [
                'image-with-button', 'single', ComponentData::imageWithButton(['alt' => 'A loaf of bread']),
                'alt="A loaf of bread"',
            ],
            'image with an empty link omits the href' => [
                'image', 'single', ComponentData::image(['link' => '']),
                '<mj-image src="https://coeliac.invalid/images/upload.jpg" alt="" fluid-on-mobile="true">',
            ],

            'image with button' => [
                'image-with-button', 'single', ComponentData::imageWithButton(),
                '<mj-image href="https://coeliac.invalid/blog" src="https://coeliac.invalid/images/upload.jpg" alt="" fluid-on-mobile="true">',
            ],
            'image with button expands into an image and a button' => [
                'image-with-button', 'single', ComponentData::imageWithButton(),
                'fluid-on-mobile="true"></mj-image> <mj-spacer height="15px"></mj-spacer> '
                . '<mj-button href="https://coeliac.invalid/blog" border-radius="6px" '
                . 'font-size="16px" line-height="115%" inner-padding="8px 25px" css-class="single-button" > Read more </mj-button>',
            ],
            'text with button expands into text and a button' => [
                'text-with-button', 'single', ComponentData::textWithButton(),
                '<mj-text mj-class="inner" css-class="blue-links">Second line.</mj-text> '
                . '<mj-spacer height="15px"></mj-spacer> '
                . '<mj-button href="https://coeliac.invalid/blog" border-radius="6px" '
                . 'font-size="16px" line-height="115%" inner-padding="8px 25px" css-class="single-button" > Read more </mj-button>',
            ],
            'title with text expands into a title and text' => [
                'title-with-text', 'single', ComponentData::titleWithText(),
                '<h1> A Newsletter Title </h1> </mj-text> <mj-spacer height="15px"></mj-spacer> '
                . '<mj-text mj-class="inner" css-class="blue-links">First line.</mj-text>',
            ],
            'recipe in a single block has a large button' => [
                'recipe', 'single', ComponentData::recipe(),
                'padding="10px 0" border-radius="6px" font-size="16px" line-height="115%" inner-padding="8px 25px" css-class="single-button" > Read more </mj-button>',
            ],
            'product in a single block has a large button' => [
                'product', 'single', ComponentData::product(),
                'padding="10px 0" border-radius="6px" font-size="16px" line-height="115%" inner-padding="8px 25px" css-class="single-button" > View Product </mj-button>',
            ],
            'a default button emits no colour or alignment' => [
                'button', 'single', ComponentData::button(),
                '<mj-button href="https://coeliac.invalid/blog" border-radius="6px" font-size="16px" '
                . 'line-height="115%" inner-padding="8px 25px" css-class="single-button" > Read more </mj-button>',
            ],
            'a dark button pairs white text with its background' => [
                'button', 'single', ComponentData::button(['background' => 'primary-dark']),
                '<mj-button href="https://coeliac.invalid/blog" background-color="#29719f" color="#ffffff" ',
            ],
            'a light button pairs dark text with its background' => [
                'button', 'single', ComponentData::button(['background' => 'primary']),
                '<mj-button href="https://coeliac.invalid/blog" background-color="#80CCFC" color="#222222" ',
            ],
            'a non default alignment is emitted' => [
                'button', 'single', ComponentData::button(['text_align' => 'center']),
                'text-align="center" border-radius="6px"',
            ],
            'an unknown colour falls back to the default and emits nothing' => [
                'button', 'single', ComponentData::button(['background' => 'chartreuse']),
                '<mj-button href="https://coeliac.invalid/blog" border-radius="6px"',
            ],
            'a button in a double block stays small and unhooked' => [
                'button', 'double', ComponentData::button(),
                '<mj-button href="https://coeliac.invalid/blog" > Read more </mj-button>',
            ],
            'blog in a double block has a small unhooked button' => [
                'blog', 'double', ComponentData::blog(),
                '<mj-button href="https://coeliac.invalid/blog/a-gluten-free-blog" padding="10px 0" > Read more </mj-button>',
            ],
            'image with button hides the button when the label is empty' => [
                'image-with-button', 'single', ComponentData::imageWithButton(['label' => '']),
                '<mj-image href="https://coeliac.invalid/blog" src="https://coeliac.invalid/images/upload.jpg" alt="" fluid-on-mobile="true"></mj-image> </mj-column>',
            ],

            'button' => [
                'button', 'single', ComponentData::button(),
                '<mj-button href="https://coeliac.invalid/blog" border-radius="6px" font-size="16px" '
                . 'line-height="115%" inner-padding="8px 25px" css-class="single-button" > Read more </mj-button>',
            ],

            'text with button' => [
                'text-with-button', 'single', ComponentData::textWithButton(),
                '<mj-text mj-class="inner" css-class="blue-links">First line.</mj-text>',
            ],
        ];
    }

    public function test_blog_and_recipe_render_identically_for_the_same_properties(): void
    {
        $properties = ComponentData::blog();

        $blog = $this->mjmlFor(NewsletterBuilder::make()->single()->with('blog', $properties));
        $recipe = $this->mjmlFor(NewsletterBuilder::make()->single()->with('recipe', $properties));

        $this->assertMjmlSame($blog, $recipe);
    }

    public function test_product_differs_from_blog_only_in_its_known_ways(): void
    {
        $properties = ComponentData::product();

        $blog = $this->normaliseMjml($this->mjmlFor(NewsletterBuilder::make()->single()->with('blog', $properties)));
        $product = $this->normaliseMjml($this->mjmlFor(NewsletterBuilder::make()->single()->with('product', $properties)));

        $this->assertNotSame($blog, $product);

        $this->assertStringContainsString('<mj-text mj-class="inner blue-links">', $blog);
        $this->assertStringContainsString('<mj-text mj-class="inner">', $product);

        $this->assertStringContainsString('<mj-text css-class="blue-links"> The short product description.', $blog);
        $this->assertStringContainsString('<mj-text css-class="blue-links" padding-bottom="10px"> The short product description.', $product);

        $this->assertStringNotContainsString('£4.99', $blog);
        $this->assertStringContainsString('<h1> £4.99 </h1>', $product);

        $this->assertStringContainsString('Read more', $blog);
        $this->assertStringContainsString('View Product', $product);
    }
}

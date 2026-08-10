<?php

declare(strict_types=1);

namespace App\Editor\Support;

use Spatie\Mailcoach\Domain\Content\Models\Concerns\HasHtmlContent;
use Spatie\Mailcoach\Domain\Shared\Actions\InitializeMjmlAction;
use Spatie\Mailcoach\Mailcoach;
use Spatie\Mjml\Mjml;

class NewsletterCompiler
{
    protected Mjml $mjml;

    public function __construct(
        protected HasHtmlContent $campaign,
        ?Mjml $mjml = null,
    ) {
        /**
         * Resolved through Mailcoach's action rather than hardcoding
         * Mjml::new()->sidecar(). In production that returns exactly the same
         * sidecar instance (config/sidecar.php lists MjmlFunction), but it also
         * falls back to local node on a machine without AWS credentials, and it
         * gives tests a single container binding to swap.
         */
        $this->mjml = $mjml
            ?? Mailcoach::getSharedActionClass('initialize_mjml', InitializeMjmlAction::class)->execute();
    }

    /**
     * The MJML document for this newsletter, before compilation.
     *
     * Kept separate from render() so it can be asserted against without any
     * MJML infrastructure - this is the artefact the rendered Blade components
     * actually produce.
     */
    public function renderMjml(): string
    {
        return view('editor.rendered', [
            'blocks' => $this->getBlocks(),
        ])->render();
    }

    public function render(): string
    {
        return $this->mjml
            ->minify()
            ->toHtml($this->renderMjml());
    }

    /** @return list<array<string, mixed>> */
    protected function getBlocks(): array
    {
        return BlockCollection::fromJson($this->campaign->getStructuredHtml())->toArray();
    }
}

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
        $this->mjml = $mjml
            ?? Mailcoach::getSharedActionClass('initialize_mjml', InitializeMjmlAction::class)->execute();
    }

    public function renderMjml(): string
    {
        $document = $this->document();

        return view('editor.rendered', [
            'blocks' => $document->toArray(),
            'preheader' => $document->preheader(),
        ])->render();
    }

    public function render(): string
    {
        return $this->mjml
            ->minify()
            ->toHtml($this->renderMjml());
    }

    protected function document(): BlockCollection
    {
        return BlockCollection::fromJson($this->campaign->getStructuredHtml());
    }
}

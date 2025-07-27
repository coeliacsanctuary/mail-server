<?php

declare(strict_types=1);

namespace App\Editor\Support;

use Spatie\Mailcoach\Domain\Content\Models\Concerns\HasHtmlContent;
use Spatie\Mjml\Mjml;

class NewsletterCompiler
{
    public function __construct(protected HasHtmlContent $campaign)
    {
    }

    protected function getBlocks(): array
    {
        $data = json_decode($this->campaign->getStructuredHtml(), true);

        return $data['blocks'] ?? [];
    }

    public function render(): string
    {
        $mjml = view('editor.rendered', [
            'blocks' => $this->getBlocks(),
        ])->render();

        return Mjml::new()
            ->sidecar()
            ->minify()
            ->toHtml($mjml);
    }
}

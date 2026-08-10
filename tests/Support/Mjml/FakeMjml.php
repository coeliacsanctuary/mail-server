<?php

declare(strict_types=1);

namespace Tests\Support\Mjml;

use Spatie\Mjml\Mjml;
use Spatie\Mjml\MjmlResult;

/**
 * Records the MJML it is handed instead of compiling it.
 *
 * Overriding convert() is enough - toHtml() and canConvertWithoutErrors()
 * both delegate to it.
 */
class FakeMjml extends Mjml
{
    /** @var list<string> */
    public array $inputs = [];

    public function convert(string $mjml, array $options = []): MjmlResult
    {
        $this->inputs[] = $mjml;

        return new MjmlResult([
            /**
             * Deliberately does not start with "<mjml". Mailcoach's
             * containsMjml() helper is str_starts_with(trim($html), '<mjml'),
             * and EditorComponent::previewHtml() re-compiles anything that
             * matches - through its own, real Mjml instance. An identity fake
             * would therefore trigger a genuine Lambda call.
             */
            'html' => "<!-- compiled -->\n" . $mjml,
            'errors' => [],
        ]);
    }

    public function lastInput(): string
    {
        if ($this->inputs === []) {
            return '';
        }

        return $this->inputs[array_key_last($this->inputs)];
    }

    public function timesCompiled(): int
    {
        return count($this->inputs);
    }
}

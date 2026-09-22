<?php

declare(strict_types=1);

namespace Tests\Support\Mjml;

use Spatie\Mjml\Mjml;
use Spatie\Mjml\MjmlResult;

class FakeMjml extends Mjml
{
    public array $inputs = [];

    public function convert(string $mjml, array $options = []): MjmlResult
    {
        $this->inputs[] = $mjml;

        return new MjmlResult([
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

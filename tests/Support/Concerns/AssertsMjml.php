<?php

declare(strict_types=1);

namespace Tests\Support\Concerns;

trait AssertsMjml
{
    protected function normaliseMjml(string $mjml): string
    {
        return mb_trim((string) preg_replace('/\s+/', ' ', $mjml));
    }

    protected function assertMjmlContains(string $expected, string $actual, string $message = ''): void
    {
        $this->assertStringContainsString(
            $this->normaliseMjml($expected),
            $this->normaliseMjml($actual),
            $message,
        );
    }

    protected function assertMjmlNotContains(string $expected, string $actual, string $message = ''): void
    {
        $this->assertStringNotContainsString(
            $this->normaliseMjml($expected),
            $this->normaliseMjml($actual),
            $message,
        );
    }

    protected function assertMjmlSame(string $expected, string $actual, string $message = ''): void
    {
        $this->assertSame(
            $this->normaliseMjml($expected),
            $this->normaliseMjml($actual),
            $message,
        );
    }
}

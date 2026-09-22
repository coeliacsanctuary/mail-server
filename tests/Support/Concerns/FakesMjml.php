<?php

declare(strict_types=1);

namespace Tests\Support\Concerns;

use Spatie\Mailcoach\Domain\Shared\Actions\InitializeMjmlAction;
use Tests\Support\Mjml\FakeInitializeMjmlAction;
use Tests\Support\Mjml\FakeMjml;

trait FakesMjml
{
    protected FakeMjml $mjml;

    protected function fakeMjml(): FakeMjml
    {
        $this->mjml = FakeMjml::new();

        $this->app->instance(
            InitializeMjmlAction::class,
            new FakeInitializeMjmlAction($this->mjml),
        );

        config(['sidecar.functions' => []]);

        return $this->mjml;
    }

    protected function assertCompiledTimes(int $expected): void
    {
        $this->assertSame(
            $expected,
            $this->mjml->timesCompiled(),
            "Expected MJML to be compiled {$expected} time(s), got {$this->mjml->timesCompiled()}.",
        );
    }
}

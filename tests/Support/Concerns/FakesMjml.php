<?php

declare(strict_types=1);

namespace Tests\Support\Concerns;

use Spatie\Mailcoach\Domain\Shared\Actions\InitializeMjmlAction;
use Tests\Support\Mjml\FakeInitializeMjmlAction;
use Tests\Support\Mjml\FakeMjml;

trait FakesMjml
{
    protected FakeMjml $mjml;

    /**
     * Must run before any Livewire::test(): EditorComponent resolves
     * initialize_mjml in its *constructor*, so the binding has to exist by the
     * time Livewire instantiates the component.
     */
    protected function fakeMjml(): FakeMjml
    {
        $this->mjml = FakeMjml::new();

        $this->app->instance(
            InitializeMjmlAction::class,
            new FakeInitializeMjmlAction($this->mjml),
        );

        /**
         * Belt and braces. Http::preventStrayRequests() does not cover Sidecar
         * - it invokes Lambda through the AWS SDK's own client, not the Http
         * facade - so anything that builds its own Mjml instance would still
         * reach out. Emptying this makes InitializeMjmlAction fall back to
         * local node rather than Lambda.
         */
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

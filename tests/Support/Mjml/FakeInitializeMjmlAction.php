<?php

declare(strict_types=1);

namespace Tests\Support\Mjml;

use Spatie\Mailcoach\Domain\Shared\Actions\InitializeMjmlAction;
use Spatie\Mjml\Mjml;

class FakeInitializeMjmlAction extends InitializeMjmlAction
{
    public function __construct(protected FakeMjml $mjml)
    {
    }

    public function execute(): Mjml
    {
        return $this->mjml;
    }
}

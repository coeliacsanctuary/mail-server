<?php

declare(strict_types=1);

namespace Tests\Support\Mjml;

use Spatie\Mailcoach\Domain\Shared\Actions\InitializeMjmlAction;
use Spatie\Mjml\Mjml;

/**
 * Hands out one shared FakeMjml so a test can assert against everything that
 * was compiled during the request, wherever it was compiled from.
 */
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

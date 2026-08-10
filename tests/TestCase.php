<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;
use Tests\Support\Concerns\AssertsMjml;
use Tests\Support\Concerns\FakesCoeliacApi;
use Tests\Support\Concerns\FakesMjml;

/**
 * Note on assertions: property arrays are compared with assertEquals, not
 * assertSame. The components build the same keys in different orders today
 * (Blog puts "title" before "description"; Recipe and Product after), and
 * unifying them is an intended outcome of the refactor. assertEquals still
 * catches any key-name or value change - do not "tighten" it to assertSame.
 */
abstract class TestCase extends BaseTestCase
{
    use AssertsMjml;
    use FakesCoeliacApi;
    use FakesMjml;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        /**
         * Every editor component calls the coeliac API in mount(), so a
         * forgotten fake is easy to write. Fail loudly rather than reach the
         * network. Does not cover Sidecar - see FakesMjml.
         */
        Http::preventStrayRequests();

        $this->fakeMjml();
    }
}

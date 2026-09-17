<?php

declare(strict_types=1);

namespace Tests\Feature\Queue;

use Composer\InstalledVersions;
use Livewire\Livewire;
use Spatie\Mailcoach\Livewire\DebugComponent;
use Tests\TestCase;

/**
 * laravel/horizon is in composer.json but nothing in this app uses it. The
 * provider, config and horizon:snapshot schedule were all removed when the
 * queues moved to Laravel Cloud managed queues, which are SQS-backed and so
 * cannot be driven by Horizon at all.
 *
 * The dependency must stay anyway. Mailcoach's debug screen calls
 * InstalledVersions::getVersion('laravel/horizon') without guarding it, and
 * Composer throws OutOfBoundsException for a package that is not installed -
 * so `composer remove laravel/horizon` turns /debug into a 500. Every other
 * version lookup on that screen uses isInstalled(), so this is an upstream
 * slip rather than something we are meant to satisfy.
 *
 * Delete this test when Mailcoach guards that call, not before.
 */
class HorizonDependencyTest extends TestCase
{
    public function test_horizon_is_still_installed_for_mailcoachs_debug_screen(): void
    {
        $this->assertTrue(InstalledVersions::isInstalled('laravel/horizon'));
    }

    public function test_the_debug_screen_renders(): void
    {
        Livewire::test(DebugComponent::class)->assertOk();
    }
}

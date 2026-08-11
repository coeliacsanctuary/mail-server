<?php

declare(strict_types=1);

namespace Tests\Feature\Users;

use App\Livewire\CreateUserComponent;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Creating a user used to fatal on the line after the insert: it notified
 * App\Notifications\QueuedWelcomeNotification, a class that was referenced but
 * never written, backed by a package that had already left composer.json. The
 * row was committed and then the request 500'd.
 *
 * Two people use this app and both accounts already exist, so this screen is
 * close to unreachable — which is exactly why it went years without anyone
 * noticing. One test, to catch it staying broken.
 */
class CreateUserTest extends TestCase
{
    public function test_it_creates_a_user_without_notifying_anyone(): void
    {
        Livewire::test(CreateUserComponent::class)
            ->set('email', 'ally@coeliacsanctuary.co.uk')
            ->set('name', 'Ally')
            ->call('saveUser');

        $user = User::query()->firstWhere('email', 'ally@coeliacsanctuary.co.uk');

        $this->assertNotNull($user);
        $this->assertSame('Ally', $user->name);
    }
}

<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\User;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CreateUserComponent extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public string $email = '';

    public string $name = '';

    public function saveUser()
    {
        $validated = $this->validate([
            'email' => ['required', 'email:rfc', Rule::unique('users', 'email')],
            'name' => ['required', 'string'],
        ]);

        $user = new User();
        $user->email = $validated['email'];
        $user->name = $validated['name'];
        $user->password = Hash::make(Str::random(64));
        $user->save();

        notify(__mc('The user has been created. :email can set a password using the forgot password link.', ['email' => $user->email]));

        return redirect()->route('users.edit', $user);
    }

    public function render()
    {
        return view('livewire.users.create');
    }
}

<?php

namespace JoelButcher\Socialstream\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use JoelButcher\Socialstream\Contracts\SetsUserPasswords;
use Livewire\Component;

class SetPasswordForm extends Component
{
    /**
     * The component's state.
     *
     * @var array<string, mixed>
     */
    public array $state = [
        'password' => '',
        'password_confirmation' => '',
    ];

    /**
     * Update the user's password.
     */
    public function setPassword(SetsUserPasswords $setter): void
    {
        $this->resetErrorBag();

        $setter->set(Auth::user(), $this->state);

        $this->state = [
            'password' => '',
            'password_confirmation' => '',
        ];

        $this->emit('saved');
    }

    /**
     * Get the current user of the application.
     */
    public function getUserProperty(): mixed
    {
        return Auth::user();
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('profile.set-password-form');
    }
}

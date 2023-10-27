<?php

namespace JoelButcher\Socialstream\Http\Livewire;

use Illuminate\View\View;
use JoelButcher\Socialstream\Contracts\SetsUserPasswords;
use Laravel\Jetstream\InteractsWithBanner;
use Livewire\Component;

class SetPasswordForm extends Component
{
    use InteractsWithBanner;

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

        $setter->set(auth()->user(), $this->state);

        $this->state = [
            'password' => '',
            'password_confirmation' => '',
        ];

        $this->banner(__('Your password has been set.'));

        redirect()->route('profile.show');
    }

    /**
     * Get the current user of the application.
     */
    public function getUserProperty(): mixed
    {
        return auth()->user();
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('profile.set-password-form');
    }
}

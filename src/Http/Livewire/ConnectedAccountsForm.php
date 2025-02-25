<?php

namespace JoelButcher\Socialstream\Http\Livewire;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use JoelButcher\Socialstream\ConnectedAccount;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Jetstream\InteractsWithBanner;
use Livewire\Component;

class ConnectedAccountsForm extends Component
{
    use InteractsWithBanner;

    /**
     * The component's listeners.
     *
     * @var array<string, string>
     */
    protected $listeners = [
        'refresh-navigation-menu' => '$refresh',
    ];

    /**
     * Indicates if user deletion is being confirmed.
     */
    public bool $confirmingAccountRemoval = false;

    /**
     * The user's current password.
     */
    public string $password = '';

    /**
     * The ID of the connected account to remove.
     */
    public string|int $id = '';

    /**
     * Return all socialite providers and whether the application supports them.
     */
    public function getProvidersProperty(): array
    {
        return Socialstream::providers();
    }

    /**
     * Get the current user of the application.
     */
    public function getUserProperty(): mixed
    {
        return auth()->user();
    }

    /**
     * Confirm that the user actually wants to remove the selected connected account.
     */
    public function confirmRemoveAccount(string|int $id): void
    {
        $this->id = $id;

        $this->confirmingAccountRemoval = true;
    }

    /**
     * Set the providers avatar url as the users profile photo url.
     */
    public function setAvatarAsProfilePhoto(string|int $id): void
    {
        $user = auth()->user();

        $account = $user->connectedAccounts
            ->where('user_id', $user->getAuthIdentifier())
            ->where('id', $id)
            ->first();

        if (is_callable([$user, 'setProfilePhotoFromUrl']) && ! is_null($account->avatar_path)) {
            $user->setProfilePhotoFromUrl($account->avatar_path);
        }

        $this->banner(__('Profile photo updated'));

        redirect()->route('profile.show');
    }

    /**
     * Remove an OAuth Provider.
     */
    public function removeConnectedAccount(): void
    {
        $this->resetErrorBag();

        if (! Hash::check($this->password, auth()->user()->getAuthPassword())) {
            throw ValidationException::withMessages([
                'password' => [__('This password does not match our records.')],
            ]);
        }

        Socialstream::connectedAccountModel()::query()
            ->where('id', $this->id)
            ->where('user_id', auth()->user()->id)
            ->delete();

        $this->banner(__('Account removed.'));

        redirect()->route('profile.show');
    }

    /**
     * Get the users connected accounts.
     */
    public function getAccountsProperty(): Collection
    {
        return auth()->user()->connectedAccounts
            ->map(function (ConnectedAccount $account) {
                return (object) $account->getSharedData();
            });
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('profile.connected-accounts-form');
    }
}

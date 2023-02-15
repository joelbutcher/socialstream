<?php

namespace JoelButcher\Socialstream\Http\Livewire;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use JoelButcher\Socialstream\ConnectedAccount;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Jetstream\InteractsWithBanner;
use Livewire\Component;
use Livewire\Redirector;

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
     * Indicates whether removal of a provider is being confirmed.
     */
    public bool $confirmingRemove = false;

    /**
     * The ID of the currently selected account.
     */
    public string|int $selectedAccountId = '';

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
        return Auth::user();
    }

    /**
     * Confirm that the user actually wants to remove the selected connected account.
     */
    public function confirmRemove(string|int $accountId): void
    {
        $this->selectedAccountId = $accountId;

        $this->confirmingRemove = true;
    }

    /**
     * Set the providers avatar url as the users profile photo url.
     */
    public function setAvatarAsProfilePhoto(string|int $accountId): Redirector
    {
        $account = Auth::user()->connectedAccounts
            ->where('user_id', ($user = Auth::user())->getAuthIdentifier())
            ->where('id', $accountId)
            ->first();

        if (is_callable([$user, 'setProfilePhotoFromUrl']) && ! is_null($account->avatar_path)) {
            $user->setProfilePhotoFromUrl($account->avatar_path);
        }

        return redirect()->route('profile.show');
    }

    /**
     * Remove an OAuth Provider.
     */
    public function removeConnectedAccount(string|int $accountId): void
    {
        DB::table('connected_accounts')
            ->where('user_id', Auth::user()->getAuthIdentifier())
            ->where('id', $accountId)
            ->delete();

        $this->confirmingRemove = false;

        $this->banner(__('Connected account removed.'));
    }

    /**
     * Get the users connected accounts.
     *
     * @return Collection
     */
    public function getAccountsProperty(): Collection
    {
        return Auth::user()->connectedAccounts
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

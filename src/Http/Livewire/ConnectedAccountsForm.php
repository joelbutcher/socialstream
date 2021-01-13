<?php

namespace JoelButcher\Socialstream\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use JoelButcher\Socialstream\ConnectedAccount;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Jetstream\InteractsWithBanner;
use Livewire\Component;

class ConnectedAccountsForm extends Component
{
    use InteractsWithBanner;

    /**
     * Indicates whether or not removal of a provider is being confirmed.
     *
     * @var bool
     */
    public $confirmingRemove = false;

    /**
     * @var mixed
     */
    public $selectedAccountId;

    /**
     * Return all socialite providers and whether or not
     * the application supports them.
     *
     * @return array
     */
    public function getProvidersProperty()
    {
        return Socialstream::providers();
    }

    /**
     * Get the current user of the application.
     *
     * @return mixed
     */
    public function getUserProperty()
    {
        return Auth::user();
    }

    /**
     * Confirm that the user actually wants to remove the selected connected account.
     *
     * @param  mixed  $accountId
     * @return void
     */
    public function confirmRemove($accountId)
    {
        $this->selectedAccountId = $accountId;

        $this->confirmingRemove = true;
    }

    /**
     * Remove an OAuth Provider.
     *
     * @param  mixed  $accountId
     * @return void
     */
    public function removeConnectedAccount($accountId)
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
     * @return \Illuminate\Support\Collection
     */
    public function getAccountsProperty()
    {
        return Auth::user()->connectedAccounts
            ->map(function (ConnectedAccount $account) {
                return (object) $account->getSharedData();
            });
    }

    /**
     * Render the component.
     *
     * @return Illuminate\View\View
     */
    public function render()
    {
        return view('profile.connected-accounts-form');
    }
}

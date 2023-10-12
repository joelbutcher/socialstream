<?php

namespace JoelButcher\Socialstream\Actions\Auth\Filament;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use JoelButcher\Socialstream\Contracts\AuthenticatesOauthCallback;
use JoelButcher\Socialstream\Contracts\CreatesConnectedAccounts;
use JoelButcher\Socialstream\Contracts\CreatesUserFromProvider;
use JoelButcher\Socialstream\Contracts\UpdatesConnectedAccounts;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Socialite\Contracts\User as ProviderUser;

class AuthenticateOauthCallback implements AuthenticatesOauthCallback
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected Guard $guard,
        protected CreatesUserFromProvider $createsUser,
        protected CreatesConnectedAccounts $createsConnectedAccounts,
        protected UpdatesConnectedAccounts $updatesConnectedAccounts
    ) {
        //
    }

    public function authenticate(string $provider, ProviderUser $providerAccount): Response|RedirectResponse
    {
        $user = auth()->user();

        if (! $user) {
            $user = Socialstream::newUserModel()->where('email', $providerAccount->getEmail())->first()
                ?? $this->createsUser->create($provider, $providerAccount);
        }

        ($account = Socialstream::findConnectedAccountForProviderAndId($provider, $providerAccount->getId()))
            ? $this->updatesConnectedAccounts->update($user, $account, $provider, $providerAccount)
            : $this->createsConnectedAccounts->create($user, $provider, $providerAccount);

        $this->guard->login($user, Socialstream::hasRememberSessionFeatures());

        return redirect()->route('filament.admin.pages.dashboard');
    }
}

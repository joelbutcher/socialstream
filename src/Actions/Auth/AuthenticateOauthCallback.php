<?php

namespace JoelButcher\Socialstream\Actions\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\MessageBag;
use JoelButcher\Socialstream\ConnectedAccount;
use JoelButcher\Socialstream\Contracts\AuthenticatesOauthCallback;
use JoelButcher\Socialstream\Contracts\CreatesConnectedAccounts;
use JoelButcher\Socialstream\Contracts\CreatesUserFromProvider;
use JoelButcher\Socialstream\Contracts\UpdatesConnectedAccounts;
use JoelButcher\Socialstream\Features;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Features as FortifyFeatures;
use Laravel\Jetstream\Jetstream;
use Laravel\Socialite\Contracts\User as ProviderUser;

class AuthenticateOauthCallback implements AuthenticatesOauthCallback
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected StatefulGuard $guard,
        protected CreatesUserFromProvider $createsUser,
        protected CreatesConnectedAccounts $createsConnectedAccounts,
        protected UpdatesConnectedAccounts $updatesConnectedAccounts
    ) {
        //
    }

    public function authenticate(string $provider, ProviderUser $providerAccount): Response|RedirectResponse|LoginResponse
    {
        $account = Socialstream::findConnectedAccountForProviderAndId($provider, $providerAccount->getId());

        // Authenticated...
        if (! is_null($user = Auth::user())) {
            return $this->alreadyAuthenticated($user, $account, $provider, $providerAccount);
        }

        // Registration...
        $previousUrl = session()->get('socialstream.previous_url');

        if (
            FortifyFeatures::enabled(FortifyFeatures::registration()) && ! $account &&
            (
                $previousUrl === route('register') ||
                (Features::hasCreateAccountOnFirstLoginFeatures() && $previousUrl === route('login'))
            )
        ) {
            $user = Jetstream::newUserModel()->where('email', $providerAccount->getEmail())->first();

            if ($user) {
                return $this->alreadyRegistered($user, $account, $provider, $providerAccount);
            }

            return $this->register($provider, $providerAccount);
        }

        if (! Features::hasCreateAccountOnFirstLoginFeatures() && ! $account) {
            $messageBag = new MessageBag;
            $messageBag->add(
                'socialstream',
                __('An account with this :Provider sign in was not found. Please register or try a different sign in method.', ['provider' => $provider])
            );

            return redirect()->route('login')->withErrors(
                $messageBag
            );
        }

        if (Features::hasCreateAccountOnFirstLoginFeatures() && ! $account) {
            if (Jetstream::newUserModel()->where('email', $providerAccount->getEmail())->exists()) {
                $messageBag = new MessageBag;
                $messageBag->add(
                    'socialstream',
                    __('An account with that email address already exists. Please login to connect your :Provider account.', ['provider' => $provider])
                );

                return redirect()->route('login')->withErrors(
                    $messageBag
                );
            }

            $user = $this->createsUser->create($provider, $providerAccount);

            return $this->login($user);
        }

        $user = $account->user;

        $this->updatesConnectedAccounts->update($user, $account, $provider, $providerAccount);

        $user->forceFill([
            'current_connected_account_id' => $account->id,
        ])->save();

        return $this->login($user);
    }

    /**
     * Handle connection of accounts for an already authenticated user.
     */
    protected function alreadyAuthenticated(Authenticatable $user, ?ConnectedAccount $account, string $provider, ProviderUser $providerAccount): RedirectResponse
    {
        if ($account && $account->user_id !== $user->id) {
            return redirect()->route('profile.show')->dangerBanner(
                __('This :Provider sign in account is already associated with another user. Please try a different account.', ['provider' => $provider]),
            );
        }

        if (! $account) {
            $this->createsConnectedAccounts->create($user, $provider, $providerAccount);

            return redirect()->route('profile.show')->banner(
                __('You have successfully connected :Provider to your account.', ['provider' => $provider])
            );
        }

        return redirect()->route('profile.show')->dangerBanner(
            __('This :Provider sign in account is already associated with your user.', ['provider' => $provider]),
        );
    }

    /**
     * Handle when a user is already registered.
     */
    protected function alreadyRegistered(Authenticatable $user, ?ConnectedAccount $account, string $provider, ProviderUser $providerAccount): RedirectResponse|LoginResponse
    {
        if (Features::hasLoginOnRegistrationFeatures()) {

            // The user exists, but they're not registered with the given provider.
            if (! $account) {
                $this->createsConnectedAccounts->create($user, $provider, $providerAccount);
            }

            return $this->login($user);
        }

        $messageBag = new MessageBag;
        $messageBag->add('socialstream', __('An account with that :Provider sign in already exists, please login.', ['provider' => $provider]));

        return redirect()->route('register')->withErrors($messageBag);
    }

    /**
     * Handle the registration of a new user.
     */
    protected function register(string $provider, ProviderUser $providerAccount): RedirectResponse|LoginResponse
    {
        if (! $providerAccount->getEmail()) {
            $messageBag = new MessageBag;
            $messageBag->add(
                'socialstream',
                __('No email address is associated with this :Provider account. Please try a different account.', ['provider' => $provider])
            );

            return redirect()->route('register')->withErrors($messageBag);
        }

        if (Jetstream::newUserModel()->where('email', $providerAccount->getEmail())->exists()) {
            $messageBag = new MessageBag;
            $messageBag->add(
                'socialstream',
                __('An account with that email address already exists. Please login to connect your :Provider account.', ['provider' => $provider])
            );

            return redirect()->route('register')->withErrors($messageBag);
        }

        $user = $this->createsUser->create($provider, $providerAccount);

        return $this->login($user);
    }

    /**
     * Authenticate the given user and return a login response.
     */
    protected function login(Authenticatable $user): LoginResponse
    {
        $this->guard->login($user, Socialstream::hasRememberSessionFeatures());

        return app(LoginResponse::class);
    }
}

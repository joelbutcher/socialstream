<?php

namespace JoelButcher\Socialstream\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use JoelButcher\Socialstream\Concerns\ConfirmsFilament;
use JoelButcher\Socialstream\Concerns\InteractsWithComposer;
use JoelButcher\Socialstream\Contracts\AuthenticatesOAuthCallback;
use JoelButcher\Socialstream\Contracts\CreatesConnectedAccounts;
use JoelButcher\Socialstream\Contracts\CreatesUserFromProvider;
use JoelButcher\Socialstream\Contracts\OAuthFailedResponse;
use JoelButcher\Socialstream\Contracts\OAuthLoginResponse;
use JoelButcher\Socialstream\Contracts\OAuthProviderLinkedResponse;
use JoelButcher\Socialstream\Contracts\OAuthProviderLinkFailedResponse;
use JoelButcher\Socialstream\Contracts\OAuthRegisterResponse;
use JoelButcher\Socialstream\Contracts\SocialstreamResponse;
use JoelButcher\Socialstream\Contracts\UpdatesConnectedAccounts;
use JoelButcher\Socialstream\Events\NewOAuthRegistration;
use JoelButcher\Socialstream\Events\OAuthFailed;
use JoelButcher\Socialstream\Events\OAuthLogin;
use JoelButcher\Socialstream\Events\OAuthProviderLinked;
use JoelButcher\Socialstream\Events\OAuthProviderLinkFailed;
use JoelButcher\Socialstream\Features;
use JoelButcher\Socialstream\Providers;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Jetstream\Jetstream;
use Laravel\Socialite\Contracts\User as ProviderUser;

class AuthenticateOAuthCallback implements AuthenticatesOAuthCallback
{
    use ConfirmsFilament;
    use InteractsWithComposer;

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

    /**
     * Handle the authentication of the user.
     */
    public function authenticate(string $provider, ProviderUser $providerAccount): SocialstreamResponse|RedirectResponse
    {
        if ($user = auth()->user()) {
            return $this->link($user, $provider, $providerAccount);
        }

        $account = Socialstream::findConnectedAccountForProviderAndId($provider, $providerAccount->getId());
        $user = Socialstream::newUserModel()->where('email', $providerAccount->getEmail())->first();

        if ($account && $user) {
            return $this->login(
                user: $user,
                account: $account,
                provider: $provider,
                providerAccount: $providerAccount
            );
        }

        if ($this->canRegister($user, $account)) {
            return $this->register($provider, $providerAccount);
        }

        if (! $user && $account && $account->user) {
            return $this->login(
                user: $account->user,
                account: $account,
                provider: $provider,
                providerAccount: $providerAccount
            );
        }

        if ($user && Features::authenticatesExistingUnlinkedUsers()) {
            return $this->login(
                user: $user,
                account: $this->createsConnectedAccounts->create(
                    user: $user,
                    provider: $provider,
                    providerUser: $providerAccount,
                ),
                provider: $provider,
                providerAccount: $providerAccount
            );
        }

        event(new OAuthFailed($provider, $providerAccount));

        $this->flashError(
            __('An account already exists for that email address. Please login to connect your :provider account.', ['provider' => Providers::name($provider)]),
        );

        return app(OAuthFailedResponse::class);
    }

    /**
     * Handle the registration of a new user.
     */
    protected function register(string $provider, ProviderUser $providerAccount): SocialstreamResponse
    {
        $user = $this->createsUser->create($provider, $providerAccount);

        $this->guard->login($user, Socialstream::hasRememberSessionFeatures());

        event(new NewOAuthRegistration($user, $provider, $providerAccount));

        return app(OAuthRegisterResponse::class);
    }

    /**
     * Authenticate the given user and return a login response.
     */
    protected function login(Authenticatable $user, mixed $account, string $provider, ProviderUser $providerAccount): SocialstreamResponse
    {
        $this->updatesConnectedAccounts->update($user, $account, $provider, $providerAccount);

        $this->guard->login($user, Socialstream::hasRememberSessionFeatures());

        event(new OAuthLogin($user, $provider, $account, $providerAccount));

        return app(OAuthLoginResponse::class);
    }

    /**
     * Attempt to link the provider to the authenticated user.
     *
     * Attempt to link the provider with the authenticated user.
     */
    private function link(Authenticatable $user, string $provider, ProviderUser $providerAccount): SocialstreamResponse
    {
        $account = Socialstream::findConnectedAccountForProviderAndId($provider, $providerAccount->getId());

        if ($account && $user?->id !== $account->user_id) {
            event(new OAuthProviderLinkFailed($user, $provider, $account, $providerAccount));

            $this->flashError(
                __('It looks like this :provider account is used by another user. Please log in.', ['provider' => Providers::name($provider)]),
            );

            return app(OAuthProviderLinkFailedResponse::class);
        }

        if (! $account) {
            $this->createsConnectedAccounts->create(auth()->user(), $provider, $providerAccount);
        }

        event(new OAuthProviderLinked($user, $provider, $account, $providerAccount));

        $this->flashStatus(
            __('You have successfully linked your :provider account.', ['provider' => Providers::name($provider)]),
        );

        return app(OAuthProviderLinkedResponse::class);
    }

    /**
     * Flash a status message to the session.
     */
    private function flashStatus(string $status): void
    {
        if (class_exists(Jetstream::class)) {
            Session::flash('flash.banner', $status);
            Session::flash('flash.bannerStyle', 'success');

            return;
        }

        Session::flash('status', $status);
    }

    /**
     * Flash an error message to the session.
     */
    private function flashError(string $error): void
    {
        if (auth()->check()) {
            if (class_exists(Jetstream::class)) {
                Session::flash('flash.banner', $error);
                Session::flash('flash.bannerStyle', 'danger');

                return;
            }
        }

        Session::flash('errors', (new ViewErrorBag())->put(
            'default',
            new MessageBag(['socialstream' => $error])
        ));
    }

    /**
     * Determine if we can register a new user.
     */
    private function canRegister(mixed $user, mixed $account): bool
    {
        if (! is_null($user) || !is_null($account)) {
            return false;
        }

        if ($this->usesFilament()) {
            return $this->hasFilamentAuthRoutes();
        }

        if (Route::has('register') && Session::get('socialstream.previous_url') === route('register')) {
            return true;
        }

        if (Route::has('login') && Session::get('socialstream.previous_url') !== route('login')) {
            return Features::hasGlobalLoginFeatures();
        }

        return Features::hasCreateAccountOnFirstLoginFeatures();
    }
}

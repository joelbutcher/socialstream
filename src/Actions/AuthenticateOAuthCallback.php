<?php

namespace JoelButcher\Socialstream\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use JoelButcher\Socialstream\Concerns\InteractsWithComposer;
use JoelButcher\Socialstream\Contracts\AuthenticatesOAuthCallback;
use JoelButcher\Socialstream\Contracts\CreatesConnectedAccounts;
use JoelButcher\Socialstream\Contracts\CreatesUserFromProvider;
use JoelButcher\Socialstream\Contracts\OAuthLoginFailedResponse;
use JoelButcher\Socialstream\Contracts\OAuthLoginResponse;
use JoelButcher\Socialstream\Contracts\OAuthProviderLinkedResponse;
use JoelButcher\Socialstream\Contracts\OAuthProviderLinkFailedResponse;
use JoelButcher\Socialstream\Contracts\OAuthRegisterFailedResponse;
use JoelButcher\Socialstream\Contracts\OAuthRegisterResponse;
use JoelButcher\Socialstream\Contracts\SocialstreamResponse;
use JoelButcher\Socialstream\Contracts\UpdatesConnectedAccounts;
use JoelButcher\Socialstream\Events\NewOAuthRegistration;
use JoelButcher\Socialstream\Events\OAuthLogin;
use JoelButcher\Socialstream\Events\OAuthLoginFailed;
use JoelButcher\Socialstream\Events\OAuthProviderLinked;
use JoelButcher\Socialstream\Events\OAuthProviderLinkFailed;
use JoelButcher\Socialstream\Events\OAuthRegistrationFailed;
use JoelButcher\Socialstream\Features;
use JoelButcher\Socialstream\Providers;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Jetstream\Jetstream;
use Laravel\Socialite\Contracts\User as ProviderUser;

class AuthenticateOAuthCallback implements AuthenticatesOAuthCallback
{
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
        // User authenticated... Trying to link a new provider
        if ($user = auth()->user()) {
            return $this->linkProvider($user, $provider, $providerAccount);
        }

        $user = Socialstream::newUserModel()->where('email', $providerAccount->getEmail())->first();

        // User is not registered yet...
        if (! $user) {
            if (
                (Route::has('register') && session()->get('socialstream.previous_url') === route('register'))
                || Features::hasCreateAccountOnFirstLoginFeatures() && (session()->get('socialstream.previous_url') === route('login') || Features::hasGlobalLoginFeatures())
            ) {
                return $this->register($provider, $providerAccount);
            }

            event(new OAuthLoginFailed($provider, $providerAccount));

            $this->flashError(
                __('We could not find your account. Please register to create an account.'),
            );

            return app(OAuthLoginFailedResponse::class);
        }

        $account = $this->findAccount($provider, $providerAccount);

        // User provider account is not linked...
        if (! $account) {
            if (Features::hasLoginOnRegistrationFeatures()) {
                $account = $this->createsConnectedAccounts->create($user, $provider, $providerAccount);

                return $this->login($user, $account, $provider, $providerAccount);
            }

            event(new OAuthRegistrationFailed($provider, $account, $providerAccount));

            $this->flashError(
                __('An account already exists for that email address. Please login to connect your :provider account.', ['provider' => Providers::name($provider)]),
            );

            return app(OAuthRegisterFailedResponse::class);
        }

        return $this->login(
            $user,
            $account,
            $provider,
            $providerAccount
        );
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
     */
    public function linkProvider(Authenticatable $user, string $provider, ProviderUser $providerAccount): SocialstreamResponse
    {
        $account = $this->findAccount($provider, $providerAccount);

        // Account exists
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
     * Find an existing connected account for the given provider and provider id.
     */
    private function findAccount(string $provider, ProviderUser $providerAccount): mixed
    {
        return Socialstream::findConnectedAccountForProviderAndId($provider, $providerAccount->getId());
    }

    /**
     * Flash a status message to the session.
     */
    private function flashStatus(string $status): void
    {
        if (class_exists(Jetstream::class)) {
            session()->flash('flash.banner', $status);
            session()->flash('flash.bannerStyle', 'success');

            return;
        }

        session()->flash('status', $status);
    }

    /**
     * Flash an error message to the session.
     */
    private function flashError(string $error): void
    {
        if (auth()->check()) {
            if (class_exists(Jetstream::class)) {
                session()->flash('flash.banner', $error);
                session()->flash('flash.bannerStyle', 'danger');

                return;
            }
        }

        session()->flash('errors', (new ViewErrorBag())->put(
            'default',
            new MessageBag(['socialstream' => $error])
        ));
    }
}

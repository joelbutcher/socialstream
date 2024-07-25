<?php

namespace JoelButcher\Socialstream\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Pipeline;
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
use Laravel\Fortify\Actions\CanonicalizeUsername;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Features as FortifyFeatures;
use Laravel\Fortify\Fortify;
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
        protected StatefulGuard $guard,
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
        // If the user is authenticated, link the provider to the authenticated user.
        if ($user = auth()->user()) {
            return $this->link($user, $provider, $providerAccount);
        }

        // Check if the user has an existing OAuth account.
        $account = Socialstream::findConnectedAccountForProviderAndId($provider, $providerAccount->getId());

        // If the user has an existing OAuth account, log the user in.
        if ($account) {
            return $this->login(
                user: $account->user,
                account: $account,
                provider: $provider,
                providerAccount: $providerAccount
            );
        }

        // Otherwise, check if a user exists with the same email address.
        $user = Socialstream::newUserModel()->where('email', $providerAccount->getEmail())->first();

        // If a user exists, check the features to make sure we can link unlinked existing users.
        if ($user) {
            if (! Features::authenticatesExistingUnlinkedUsers()) {
                // If we cannot link, return an error asking the user to log in to link their account.
                return $this->oauthFailed(
                    error: __('An account already exists with the same email address. Please log in to connect your :provider account.', ['provider' => Providers::name($provider)]),
                    provider: $provider,
                    providerAccount: $providerAccount,
                );
            }

            // Otherwise, log the user in.
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

        // If a user does not exist for the provider account, check if registration is supported.
        if ($this->canRegister()) {
            // If registration is supported, register the user.
            return $this->register($provider, $providerAccount);
        }

        // Otherwise, return an error.
        $error = Route::has('login') && Session::get('socialstream.previous_url') === route('login')
            ? __('Account not found, please register to create an account.')
            : __('Registration is disabled.');

        return $this->oauthFailed(
            error: $error,
            provider: $provider,
            providerAccount: $providerAccount,
        );
    }

    /**
     * Handle the registration of a new user.
     */
    protected function register(string $provider, ProviderUser $providerAccount): SocialstreamResponse|RedirectResponse
    {
        $user = $this->createsUser->create($provider, $providerAccount);

        return tap(
            (new Pipeline(app()))->send(request())->through(array_filter([
                function ($request, $next) use ($user) {
                    $this->guard->login($user, Socialstream::hasRememberSessionFeatures());

                    return $next($request);
                },
            ]))->then(fn() => app(OAuthRegisterResponse::class)),
            fn() => event(new NewOAuthRegistration($user, $provider, $providerAccount))
        );
    }

    /**
     * Authenticate the given user and return a login response.
     */
    protected function login(Authenticatable $user, mixed $account, string $provider, ProviderUser $providerAccount): SocialstreamResponse|RedirectResponse
    {
        $this->updatesConnectedAccounts->update($user, $account, $provider, $providerAccount);

        return tap(
            $this->loginPipeline(request(), $user)->then(fn() => app(OAuthLoginResponse::class)),
            fn() => event(new OAuthLogin($user, $provider, $account, $providerAccount)),
        );
    }

    protected function loginPipeline(Request $request, Authenticatable $user): Pipeline
    {
        if (!class_exists(Fortify::class)) {
            return (new Pipeline(app()))->send($request)->through(array_filter([
                function ($request, $next) use ($user) {
                    $this->guard->login($user, Socialstream::hasRememberSessionFeatures());

                    if ($request->hasSession()) {
                        $request->session()->regenerate();
                    }

                    return $next($request);
                },
            ]));
        }

        if (Fortify::$authenticateThroughCallback) {
            return (new Pipeline(app()))->send($request)->through(array_filter(
                call_user_func(Fortify::$authenticateThroughCallback, $request)
            ));
        }

        if (is_array(config('fortify.pipelines.login'))) {
            return (new Pipeline(app()))->send($request)->through(array_filter(
                config('fortify.pipelines.login')
            ));
        }

        return (new Pipeline(app()))->send($request)->through(array_filter([
            config('fortify.limiters.login') ? null : EnsureLoginIsNotThrottled::class,
            config('fortify.lowercase_usernames') ? CanonicalizeUsername::class : null,
            FortifyFeatures::enabled(FortifyFeatures::twoFactorAuthentication()) ? RedirectIfTwoFactorAuthenticatable::class : null,
            function ($request, $next) use ($user) {
                $this->guard->login($user, Socialstream::hasRememberSessionFeatures());

                return $next($request);
            },
            PrepareAuthenticatedSession::class,
        ]));
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

        if (!$account) {
            $this->createsConnectedAccounts->create($user, $provider, $providerAccount);
        }

        event(new OAuthProviderLinked($user, $provider, $account, $providerAccount));

        $this->flashStatus(
            __('You have successfully linked your :provider account.', ['provider' => Providers::name($provider)]),
        );

        return app(OAuthProviderLinkedResponse::class);
    }

    private function oauthFailed(string $error, string $provider, ProviderUser $providerAccount): OAuthFailedResponse
    {
        event(new OAuthFailed($provider, $providerAccount));

        $this->flashError($error);

        return app(OAuthFailedResponse::class);
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
    private function canRegister(): bool
    {
        if ($this->usesFilament() && $this->canRegisterUsingFilament()) {
            return true;
        }

        if (class_exists(Fortify::class) && !FortifyFeatures::enabled(FortifyFeatures::registration())) {
            return false;
        }

        $previousRoute = Session::get('socialstream.previous_url');

        if (Route::has('register') && $previousRoute === route('register')) {
            return true;
        }

        if (Route::has('login') && $previousRoute === route('login')) {
            return Features::hasCreateAccountOnFirstLoginFeatures();
        }

        return Features::hasCreateAccountOnFirstLoginFeatures() && Features::hasGlobalLoginFeatures();
    }
}

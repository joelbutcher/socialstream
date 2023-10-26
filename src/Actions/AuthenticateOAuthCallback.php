<?php

namespace JoelButcher\Socialstream\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\MessageBag;
use JoelButcher\Socialstream\Concerns\InteractsWithComposer;
use JoelButcher\Socialstream\ConnectedAccount;
use JoelButcher\Socialstream\Contracts\AuthenticatesOAuthCallback;
use JoelButcher\Socialstream\Contracts\CreatesConnectedAccounts;
use JoelButcher\Socialstream\Contracts\CreatesUserFromProvider;
use JoelButcher\Socialstream\Contracts\UpdatesConnectedAccounts;
use JoelButcher\Socialstream\Features;
use JoelButcher\Socialstream\Providers;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Features as FortifyFeatures;
use Laravel\Socialite\Contracts\User as ProviderUser;

class AuthenticateOAuthCallback implements AuthenticatesOAuthCallback
{
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

    public function authenticate(string $provider, ProviderUser $providerAccount): Response|RedirectResponse|LoginResponse
    {
        $account = Socialstream::findConnectedAccountForProviderAndId($provider, $providerAccount->getId());

        // Authenticated...
        if (! is_null($user = auth()->user())) {
            return $this->alreadyAuthenticated($user, $account, $provider, $providerAccount);
        }

        // Registration...
        $previousUrl = session()->get('socialstream.previous_url');

        if (
            class_exists(FortifyFeatures::class) &&
            FortifyFeatures::enabled(FortifyFeatures::registration()) && ! $account &&
            (
                $previousUrl === route('register') ||
                (Features::hasCreateAccountOnFirstLoginFeatures() && $previousUrl === route('login'))
            )
        ) {
            $user = Socialstream::newUserModel()->where('email', $providerAccount->getEmail())->first();

            if ($user) {
                return $this->alreadyRegistered($user, $account, $provider, $providerAccount);
            }

            return $this->register($provider, $providerAccount);
        }

        if (! Features::hasCreateAccountOnFirstLoginFeatures() && ! $account) {
            return $this->redirectAuthFailed(
                error: __('An account with this :Provider sign in was not found. Please register or try a different sign in method.', ['provider' => Providers::name($provider)])
            );
        }

        if (Features::hasCreateAccountOnFirstLoginFeatures() && ! $account) {
            if (Socialstream::newUserModel()->where('email', $providerAccount->getEmail())->exists()) {
                return $this->redirectAuthFailed(
                    error: __('An account with that email address already exists. Please login to connect your :Provider account.', ['provider' => Providers::name($provider)])
                );
            }

            $user = $this->createsUser->create($provider, $providerAccount);

            return $this->login($user);
        }

        $user = $account->user;

        $this->updatesConnectedAccounts->update($user, $account, $provider, $providerAccount);

        return $this->login($user);
    }

    /**
     * Handle connection of accounts for an already authenticated user.
     */
    protected function alreadyAuthenticated(Authenticatable $user, ?ConnectedAccount $account, string $provider, ProviderUser $providerAccount): RedirectResponse
    {
        // Connect the account to the user.
        if (! $account) {
            $this->createsConnectedAccounts->create($user, $provider, $providerAccount);

            return redirect()->route('profile.show')->banner(
                __('You have successfully connected :Provider to your account.', ['provider' => Providers::name($provider)])
            );
        }

        if ($account->user_id !== $user->id) {
            return redirect()->route('profile.show')->dangerBanner(
                __('This :Provider sign in account is already associated with another user. Please log in with that user or connect a different :Provider account.', ['provider' => Providers::buttonLabel($provider)])
            );
        }

        // Account already connected
        return redirect()->route('profile.show')->dangerBanner(
            __('This :Provider sign in account is already associated with your user.', ['provider' => Providers::buttonLabel($provider)])
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

        return $this->redirectAuthFailed(
            __('An account with that :Provider sign in already exists, please login.', ['provider' => Providers::buttonLabel($provider)])
        );
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
                __('No email address is associated with this :Provider account. Please try a different account.', ['provider' => Providers::buttonLabel($provider)])
            );

            return redirect()->route('register')->withErrors($messageBag);
        }

        if (Socialstream::newUserModel()->where('email', $providerAccount->getEmail())->exists()) {
            $messageBag = new MessageBag;
            $messageBag->add(
                'socialstream',
                __('An account with that email address already exists. Please login to connect your :Provider account.', ['provider' => Providers::buttonLabel($provider)])
            );

            return redirect()->route('register')->withErrors($messageBag);
        }

        $user = $this->createsUser->create($provider, $providerAccount);

        return $this->login($user);
    }

    /**
     * Authenticate the given user and return a login response.
     */
    protected function login(Authenticatable $user): RedirectResponse|LoginResponse
    {
        $this->guard->login($user, Socialstream::hasRememberSessionFeatures());

        // Because users can have multiple stacks installed for which they may wish to use
        // Socialstream for, we will need to determine the redirect path based on a few
        // different factors, such as the presence of Filament's auth routes etc.

        $previousUrl = session()->pull('socialstream.previous_url');

        return match(true) {
            Route::has('filament.auth.login') && $previousUrl === route('filament.auth.login') => redirect()
                ->route('admin'),
            $this->hasComposerPackage('laravel/breeze') => redirect()
                ->route('dashboard'),
            $this->hasComposerPackage('laravel/jetstream') => app(LoginResponse::class),
            default => redirect()
                ->to(RouteServiceProvider::HOME),
        };
    }

    private function redirectAuthFailed(string $error): RedirectResponse
    {
        $previousUrl = session()->pull('socialstream.previous_url');

        // Because users can have multiple stacks installed for which they may wish to use
        // Socialstream for, we will need to determine the redirect path based on a few
        // different factors, such as the presence of Filament's auth routes etc.

        return redirect()->route(match (true) {
            Route::has('login') && $previousUrl === route('login') => 'login',
            Route::has('register') && $previousUrl === route('register') => 'register',
            Route::has('filament.auth.login') && $previousUrl === route('filament.auth.login') => 'filament.auth.login',
            default => 'login',
        })->withErrors((new MessageBag)->add('socialstream', $error));
    }
}

<?php

namespace JoelButcher\Socialstream\Actions;

use App\Providers\RouteServiceProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
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
            class_exists(FortifyFeatures::class)
            && FortifyFeatures::enabled(FortifyFeatures::registration())
            && ! $account
            && ($previousUrl === route('register') || Features::hasCreateAccountOnFirstLoginFeatures())
        ) {
            $user = Socialstream::newUserModel()->where('email', $providerAccount->getEmail())->first();

            if ($user) {
                return $this->alreadyRegistered($user, $account, $provider, $providerAccount);
            }

            return $this->register($provider, $providerAccount);
        }

        if (! $account) {
            if (! Features::hasCreateAccountOnFirstLoginFeatures()) {
                return $this->redirectAuthFailed(
                    error: __('An account with this :Provider sign in was not found. Please register or try a different sign in method.', ['provider' => Providers::name($provider)])
                );
            }

            return $this->register($provider, $providerAccount);
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
        // Get the route
        $route = match (true) {
            Route::has('filament.admin.home') => route('filament.admin.home'),
            Route::has('filament.home') => route('filament.home'),
            $this->hasComposerPackage('laravel/breeze') => match (true) {
                Route::has('profile.show') => route('profile.show'),
                Route::has('profile.edit') => route('profile.edit'),
                Route::has('profile') => route('profile'),
            },
            Route::has('profile.show') => route('profile.show'),
            Route::has('dashboard') => route('dashboard'),
            Route::has('home') => route('home'),
            default => RouteServiceProvider::HOME
        };

        // Connect the account to the user.
        if (! $account) {
            $this->createsConnectedAccounts->create($user, $provider, $providerAccount);

            $status = __('You have successfully connected :Provider to your account.', ['provider' => Providers::name($provider)]);

            return class_exists(Jetstream::class)
                ? redirect()->to($route)->banner($status)
                : redirect()->to($route)->with('status', $status);
        }

        $error = $account->user_id !== $user->id
            ? __('This :Provider sign in account is already associated with another user. Please log in with that user or connect a different :Provider account.', ['provider' => Providers::name($provider)])
            : __('This :Provider sign in account is already associated with your user.', ['provider' => Providers::name($provider)]);

        return class_exists(Jetstream::class)
            ? redirect()->to($route)->dangerBanner($error)
            : redirect()->to($route)->withErrors((new MessageBag)->add('socialstream', $error));
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
            __('An account with that email address already exists. Please login to connect your :Provider account.', ['provider' => Providers::buttonLabel($provider)])
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

        return match (true) {
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

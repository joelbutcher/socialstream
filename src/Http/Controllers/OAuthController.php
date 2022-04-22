<?php

namespace JoelButcher\Socialstream\Http\Controllers;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\MessageBag;
use JoelButcher\Socialstream\Contracts\CreatesConnectedAccounts;
use JoelButcher\Socialstream\Contracts\CreatesUserFromProvider;
use JoelButcher\Socialstream\Contracts\GeneratesProviderRedirect;
use JoelButcher\Socialstream\Contracts\HandlesInvalidState;
use JoelButcher\Socialstream\Contracts\ResolvesSocialiteUsers;
use JoelButcher\Socialstream\Contracts\UpdatesConnectedAccounts;
use JoelButcher\Socialstream\Features;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Features as FortifyFeatures;
use Laravel\Jetstream\Jetstream;
use Laravel\Socialite\Two\InvalidStateException;

class OAuthController extends Controller
{
    /**
     * The guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected $guard;

    /**
     * The creates user implementation.
     *
     * @var \JoelButcher\Socialstream\Contracts\CreatesUserFromProvider;
     */
    protected $createsUser;

    /**
     * The creates connected accounts implementation.
     *
     * @var \JoelButcher\Socialstream\Contracts\CreatesConnectedAccounts;
     */
    protected $createsConnectedAccounts;

    /**
     * The updates connected accounts implementation.
     *
     * @var \JoelButcher\Socialstream\Contracts\UpdatesConnectedAccounts;
     */
    protected $updatesConnectedAccounts;

    /**
     * The handler for Socialite's InvalidStateException.
     *
     * @var \JoelButcher\Socialstream\Contracts\CreatesConnectedAccounts;
     */
    protected $invalidStateHandler;

    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Contracts\Auth\StatefulGuard  $guard
     * @return void
     */
    public function __construct(
        StatefulGuard $guard,
        CreatesUserFromProvider $createsUser,
        CreatesConnectedAccounts $createsConnectedAccounts,
        UpdatesConnectedAccounts $updatesConnectedAccounts,
        HandlesInvalidState $invalidStateHandler
    ) {
        $this->guard = $guard;
        $this->createsUser = $createsUser;
        $this->createsConnectedAccounts = $createsConnectedAccounts;
        $this->updatesConnectedAccounts = $updatesConnectedAccounts;
        $this->invalidStateHandler = $invalidStateHandler;
    }

    /**
     * Get the redirect for the given Socialite provider.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $provider
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToProvider(Request $request, string $provider, GeneratesProviderRedirect $generator)
    {
        session()->put('socialstream.previous_url', back()->getTargetUrl());

        return $generator->generate($provider);
    }

    /**
     * Attempt to log the user in via the provider user returned from Socialite.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $provider
     * @return mixed
     */
    public function handleProviderCallback(Request $request, string $provider, ResolvesSocialiteUsers $resolver)
    {
        if ($request->has('error')) {
            $messageBag = new MessageBag;
            $messageBag->add('socialstream', $request->error_description);

            return Auth::check()
                ? redirect(config('fortify.home'))->dangerBanner($request->error_description)
                : redirect()->route(
                    FortifyFeatures::enabled(FortifyFeatures::registration()) ? 'register' : 'login'
                )->withErrors($messageBag);
        }

        try {
            $providerAccount = $resolver->resolve($provider);
        } catch (InvalidStateException $e) {
            $this->invalidStateHandler->handle($e);
        }

        $account = Socialstream::findConnectedAccountForProviderAndId($provider, $providerAccount->getId());

        // Authenticated...
        if (! is_null($user = Auth::user())) {
            return $this->alreadyAuthenticated($user, $account, $provider, $providerAccount);
        }

        // Registration...
        if (FortifyFeatures::enabled(FortifyFeatures::registration()) && session()->get('socialstream.previous_url') === route('register') && ! $account) {
            $user = Jetstream::newUserModel()->where('email', $providerAccount->getEmail())->first();

            if ($user) {
                return $this->handleUserAlreadyRegistered($user, $account, $provider, $providerAccount);
            }

            return $this->register($account, $provider, $providerAccount);
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
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  \JoelButcher\Socialstream\ConnectedAccount  $account
     * @param  string  $provider
     * @param  \Laravel\Socialite\AbstractUser  $providerAccount
     * @return mixed
     */
    protected function alreadyAuthenticated($user, $account, $provider, $providerAccount)
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
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  \JoelButcher\Socialstream\ConnectedAccount  $account
     * @param  string  $provider
     * @param  \Laravel\Socialite\AbstractUser  $providerAccount
     * @return mixed
     */
    protected function handleUserAlreadyRegistered($user, $account, $provider, $providerAccount)
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
     *
     * @param  \JoelButcher\Socialstream\ConnectedAccount  $account
     * @param  string  $provider
     * @param  \Laravel\Socialite\AbstractUser  $providerAccount
     * @return mixed
     */
    protected function register($account, $provider, $providerAccount)
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
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable|mixed  $user
     * @return mixed
     */
    protected function login($user)
    {
        $this->guard->login($user, Socialstream::hasRememberSessionFeatures());

        return app(LoginResponse::class);
    }
}

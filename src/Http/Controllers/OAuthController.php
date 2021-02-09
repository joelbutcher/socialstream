<?php

namespace JoelButcher\Socialstream\Http\Controllers;

use App\Actions\Socialstream\HandleInvalidState;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use JoelButcher\Socialstream\Contracts\CreatesConnectedAccounts;
use JoelButcher\Socialstream\Contracts\CreatesUserFromProvider;
use JoelButcher\Socialstream\Contracts\GeneratesProviderRedirect;
use JoelButcher\Socialstream\Features;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Jetstream\Jetstream;
use Laravel\Socialite\Facades\Socialite;
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
     * @var  \JoelButcher\Socialstream\Contracts\CreatesUserFromProvider;
     */
    protected $createsUser;

    /**
     * The creates connected accounts implementation.
     *
     * @var  \JoelButcher\Socialstream\Contracts\CreatesConnectedAccounts;
     */
    protected $createsConnectedAccounts;

    /**
     * The handler for Socialite's InvalidStateException.
     *
     * @var  \JoelButcher\Socialstream\Contracts\CreatesConnectedAccounts;
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
        HandleInvalidState $invalidStateHandler
    ) {
        $this->guard = $guard;
        $this->createsUser = $createsUser;
        $this->createsConnectedAccounts = $createsConnectedAccounts;
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
     * @return \Illuminate\Routing\Pipeline
     */
    public function handleProviderCallback(Request $request, string $provider)
    {
        if ($request->has('error')) {
            return Auth::check()
                ? redirect(config('fortify.home'))->dangerBanner($request->error_description)
                : redirect()->route('register')->withErrors($request->error_description);
        }

        try {
            $providerAccount = Socialite::driver($provider)->user();
        } catch (InvalidStateException $e) {
            $this->invalidStateHandler->handle($e);
        }

        $account = Socialstream::findConnectedAccountForProviderAndId($provider, $providerAccount->getId());

        // Authenticated...
        if (! is_null($user = Auth::user())) {
            if ($account && $account->user_id !== $user->id) {
                return redirect()->route('profile.show')->dangerBanner([
                    __('This :Provider sign in account is already associated with another user. Please try a different account.', ['provider' => $provider]),
                ]);
            }

            if (! $account) {
                $this->createsConnectedAccounts->create($user, $provider, $providerAccount);

                return redirect()->route('profile.show')->banner(
                    __('You have successfully connected :Provider to your account.', ['provider' => $provider])
                );
            }

            return redirect()->route('profile.show')->dangerBanner([
                __('This :Provider sign in account is already associated with your user.', ['provider' => $provider]),
            ]);
        }

        // Registration...
        if (session()->get('socialstream.previous_url') === route('register')) {
            if ($account) {
                return redirect()->route('register')->withErrors(
                    __('An account with that :Provider sign in already exists, please login.', ['provider' => $provider])
                );
            }

            if (! $providerAccount->getEmail()) {
                return redirect()->route('register')->withErrors(
                    __('No email address is associated with this :Provider account. Please try a different account.', ['provider' => $provider])
                );
            }

            if (Jetstream::newUserModel()->where('email', $providerAccount->getEmail())->exists()) {
                return redirect()->route('register')->withErrors(
                    __('An account with that email address already exists. Please login to connect your :Provider account.', ['provider' => $provider])
                );
            }

            $user = $this->createsUser->create($provider, $providerAccount);

            $this->guard->login($user, config('socialstream.remember'));

            return redirect(config('fortify.home'));
        }

        if (! Features::createsAccountsOnFirstLogin() && ! $account) {
            return redirect()->route('login')->withErrors(
                __('An account with this :Provider sign in was not found. Please register or try a different sign in method.', ['provider' => $provider])
            );
        }

        if (Features::createsAccountsOnFirstLogin() && ! $account) {
            if (Jetstream::newUserModel()->where('email', $providerAccount->getEmail())->exists()) {
                return redirect()->route('login')->withErrors(
                    __('An account with that email address already exists. Please login to connect your :Provider account.', ['provider' => $provider])
                );
            }

            $user = $this->createsUser->create($provider, $providerAccount);

            $this->guard->login($user, config('socialstream.remember'));

            return redirect(config('fortify.home'));
        }

        $this->guard->login($account->user, config('socialstream.remember'));

        $account->user->forceFill([
            'current_connected_account_id' => $account->id,
        ]);

        return redirect(config('fortify.home'));
    }
}

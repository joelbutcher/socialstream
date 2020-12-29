<?php

namespace JoelButcher\Socialstream\Http\Controllers;

use App\Actions\Socialstream\HandleInvalidState;
use App\Models\ConnectedAccount;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use JoelButcher\Socialstream\Contracts\CreatesConnectedAccounts;
use JoelButcher\Socialstream\Contracts\CreatesUserFromProvider;
use JoelButcher\Socialstream\Contracts\HandlesInvalidState;
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
     * The handler for Socialite's InvalidStateException
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
    public function __construct(StatefulGuard $guard, CreatesUserFromProvider $createsUser, CreatesConnectedAccounts $createsConnectedAccounts, HandleInvalidState $invalidStateHandler)
    {
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
    public function redirectToProvider(Request $request, string $provider)
    {
        session()->put('origin_url', back()->getTargetUrl());

        return Socialite::driver($provider)->redirect();
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
                ? redirect(config('fortify.home'))->withErrors($request->error_description)
                : redirect()->route('register')->withErrors($request->error_description);
        }

        try {
            $providerAccount = Socialite::driver($provider)->user();
        } catch (InvalidStateException $e) {
            $this->invalidStateHandler->handle($e);
        }

        $account = ConnectedAccount::firstWhere([
            'provider_id' => $providerAccount->getId(),
            'provider_name' => $provider,
        ]);

        // Authenticated...
        if (! is_null($user = Auth::user())) {
            if ($account && $account->user_id !== $user->id) {
                return redirect()->route('profile.show')->withErrors([
                    $provider.'_connect_error' => __('This :Provider sign in account is already associated with another user. Please try a different account.', ['provider' => $provider])
                ]);
            }

            if (! $account) {
                $this->createsConnectedAccounts->create($user, $provider, $providerAccount);

                return redirect()->route('profile.show');
            }

            return redirect()->route('profile.show')->withErrors([
                $provider.'_connect_error' => __('This :Provider sign in account is already associated with your user.', ['provider' => $provider])
            ]);
        }

        // Registration...
        if (session()->get('origin_url') === route('register')) {
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

            if (Jetstream::newUserModel()->where('email', $providerAccount->getEmail())->first()) {
                return redirect()->route('register')->withErrors(
                    __('An account with that email address already exists. Please login to connect your :Provider account.', ['provider' => $provider])
                );
            }

            $user = $this->createsUser->create($provider, $providerAccount);

            $this->guard->login($user);

            return redirect(config('fortify.home'));
        }

        if (! $account) {
            return redirect()->route('login')->withErrors(
                __('An account with this :Provider sign in was not found. Please register or try a different sign in method.', ['provider' => $provider])
            );
        }

        $this->guard->login($account->user);

        $account->user->forceFill([
            'current_connected_account_id' => $account->id,
        ]);

        return redirect(config('fortify.home'));
    }
}

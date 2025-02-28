<?php

namespace JoelButcher\Socialstream\Actions;

use DateInterval;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use JoelButcher\Socialstream\Concerns\InteractsWithComposer;
use JoelButcher\Socialstream\Contracts\AuthenticatesOAuthCallback;
use JoelButcher\Socialstream\Contracts\CreatesConnectedAccounts;
use JoelButcher\Socialstream\Contracts\CreatesUserFromProvider;
use JoelButcher\Socialstream\Contracts\UpdatesConnectedAccounts;
use JoelButcher\Socialstream\Events\NewOAuthRegistration;
use JoelButcher\Socialstream\Events\OAuthFailed;
use JoelButcher\Socialstream\Events\OAuthLogin;
use JoelButcher\Socialstream\Events\OAuthProviderLinked;
use JoelButcher\Socialstream\Events\OAuthProviderLinkFailed;
use JoelButcher\Socialstream\Features;
use JoelButcher\Socialstream\Providers;
use JoelButcher\Socialstream\Socialstream;
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
        protected UpdatesConnectedAccounts $updatesConnectedAccounts,
    ) {
        //
    }

    /**
     * Handle the authentication of the user.
     */
    public function authenticate(Request $request, string $provider, ProviderUser $providerAccount): RedirectResponse
    {
        // User is logged in, prompt the user confirm they wish to link their account.
        if ($user = $request->user()) {
            cache()->put("socialstream.{$user->id}:$provider.provider", $providerAccount, ttl: new DateInterval('PT10M'));

            return to_route('oauth.confirm.show', ['provider' => $provider]);
        }

        try {
            return $this->attempt($request, $provider, $providerAccount);
        } catch (QueryException $exception) {
            report(new \DomainException(
                message: 'Something went wrong while trying to authenticate a user.',
                previous: $exception,
            ));

            event(new OAuthFailed($provider, $providerAccount));

            return to_route('login')
                ->with('socialstream.error', 'Oops! Something went wrong.');
        }
    }

    /**
     * Attempt to authenticate the user.
     */
    protected function attempt(Request $request, string $provider, ProviderUser $providerAccount): RedirectResponse
    {
        // Registration...
        $account = Socialstream::findConnectedAccountForProviderAndId($provider, $providerAccount->getId());
        $user = Socialstream::newUserModel()->where('email', $providerAccount->getEmail())->first();


        if (!$account && !$user) {
            return $this->register($request, $provider, $providerAccount);
        }

        // This should never happen...
        if ($account && !$user) {
            // Notify the developers something went wrong.
            report(new \DomainException(
                message: 'Could not retrieve user information.',
            ));

            // Gracefully handle the error for the user.
            return redirect()->route('login')
                ->with('socialstream.error', 'These credentials do not match our records.');
        }

        if ($user && !$account) {
            if (! Features::authenticatesExistingUnlinkedUsers()) {
                return to_route('login')
                    ->with('socialstream.error', 'These credentials do not match our records.');
            }

            $account = $this->createsConnectedAccounts->create($user, $provider, $providerAccount);
        }

        return $this->login($request, $user, $account, $provider, $providerAccount);
    }

    /**
     * Handle the registration of a new user.
     */
    protected function register(Request $request, string $provider, ProviderUser $providerAccount): RedirectResponse
    {
        if (! $this->canRegister($request)) {
            return to_route('login')
                ->with('socialstream.error', 'These credentials do not match our records.');
        }

        $user = $this->createsUser->create($provider, $providerAccount);

        $this->guard->login($user, Socialstream::hasRememberSessionFeatures());

        event(new NewOAuthRegistration($user, $provider, $providerAccount));

        return redirect()->intended(route('dashboard', absolute: false) ?? config('socialstream.home'));
    }

    /**
     * Authenticate the given user and return a login response.
     */
    protected function login(Request $request, Authenticatable $user, mixed $account, string $provider, ProviderUser $providerAccount): RedirectResponse
    {
        $this->updatesConnectedAccounts->update($user, $account, $provider, $providerAccount);

        $this->ensureLoginIsNotRateLimited($request, $user);

        if (! Auth::loginUsingId($user->getAuthIdentifier(), Socialstream::hasRememberSessionFeatures())) {
            RateLimiter::hit($user->getAuthIdentifier());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($user->getAuthIdentifier());

        event(new OAuthLogin($user, $provider, $account, $providerAccount));

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Ensure the login request is not rate limited.
     */
    protected function ensureLoginIsNotRateLimited(Request $request, Authenticatable $user): void
    {
        if (! RateLimiter::tooManyAttempts($user->getAuthIdentifier(), 5)) {
            return;
        }

        event(new Lockout($request));

        $seconds = RateLimiter::availableIn($user->getAuthIdentifier());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Handle the linking of a provider account to an already authenticated user.
     */
    public function link(Request $request): RedirectResponse
    {
        $user = $request->user();
        $provider = $request->input('provider');

        $providerAccount = cache()->pull("socialstream.{$user->id}:$provider.provider");

        $result = request()->input('result');

        if ($result === 'deny') {
            event(new OAuthProviderLinkFailed($user, $provider, null, $providerAccount));

            return to_route('linked-accounts')
                ->with('socialstream.error', __('Failed to link :provider account. User denied request.', ['provider' => Providers::name($provider)]));
        }

        if (! $providerAccount) {
            throw new \DomainException(
                message: 'Could not retrieve social provider information.',
            );
        }

        $account = Socialstream::findConnectedAccountForProviderAndId($provider, $providerAccount->getId());

        if ($account && $user->getAuthIdentifier() !== $account->user_id) {
            event(new OAuthProviderLinkFailed($user, $provider, $account, $providerAccount));

            return to_route('linked-accounts')
                ->with('socialstream.error', __('It looks like this :provider account is used by another user. Please log in.', ['provider' => Providers::name($provider)]));
        }

        if (! $account) {
            $this->createsConnectedAccounts->create($user, $provider, $providerAccount);
        }

        event(new OAuthProviderLinked($user, $provider, $account, $providerAccount));

        return to_route('linked-accounts')
            ->with('status', __(':provider account linked.', ['provider' => Providers::name($provider)]));
    }

    /**
     * Determine if we can register a new user.
     */
    protected function canRegister(Request $request): bool
    {
        if (Route::has('register') && $request->session()->get('socialstream.previous_url') === route('register')) {
            return true;
        }

        if (Route::has('login') && $request->session()->get('socialstream.previous_url') === route('login')) {
            return Features::hasCreateAccountOnFirstLoginFeatures();
        }

        return Features::hasGlobalLoginFeatures();
    }
}

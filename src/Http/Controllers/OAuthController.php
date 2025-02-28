<?php

namespace JoelButcher\Socialstream\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Inertia\Response as InertiaResponse;
use JoelButcher\Socialstream\Contracts\AuthenticatesOAuthCallback;
use JoelButcher\Socialstream\Contracts\GeneratesProviderRedirect;
use JoelButcher\Socialstream\Contracts\HandlesInvalidState;
use JoelButcher\Socialstream\Contracts\HandlesOAuthCallbackErrors;
use JoelButcher\Socialstream\Contracts\ResolvesSocialiteUsers;
use JoelButcher\Socialstream\Enums\Provider;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Socialite\Two\InvalidStateException;

class OAuthController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected HandlesOAuthCallbackErrors $errorHandler,
        protected ResolvesSocialiteUsers $userResolver,
        protected AuthenticatesOAuthCallback $authenticator,
        protected HandlesInvalidState $invalidStateHandler,
    ) {
        //
    }

    /**
     * Get the redirect for the given Socialite provider.
     */
    public function redirect(Request $request, string $provider, GeneratesProviderRedirect $generator): RedirectResponse
    {
        $request->session()->put('socialstream.previous_url', back()->getTargetUrl());

        return $generator->generate($provider);
    }

    /**
     * Attempt to log the user in via the provider user returned from Socialite.
     */
    public function callback(Request $request, string $provider): RedirectResponse|Response
    {
        $redirect = $this->errorHandler->handle($request);

        if ($redirect instanceof RedirectResponse) {
            return $redirect;
        }

        try {
            $providerAccount = $this->userResolver->resolve($provider);
        } catch (InvalidStateException $e) {
            return $this->invalidStateHandler->handle($e);
        }

        return $this->authenticator->authenticate($request, $provider, $providerAccount);
    }

    /**
     * Show the oauth confirmation page.
     */
    public function prompt(Request $request): View|RedirectResponse|InertiaResponse
    {
        $request->validate([
            'provider' => ['required', Rule::in(config('socialstream.providers'))],
        ]);

        $provider = $request->enum('provider', Provider::class);

        return app()->call(Socialstream::getOAuthConfirmationPrompt(), ['provider' => $provider]);
    }

    public function confirm(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
            'provider' => ['required', 'string'],
        ]);

        return $this->authenticator->link($request);
    }
}

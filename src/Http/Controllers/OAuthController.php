<?php

namespace JoelButcher\Socialstream\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;
use JoelButcher\Socialstream\Contracts\AuthenticatesOAuthCallback;
use JoelButcher\Socialstream\Contracts\GeneratesProviderRedirect;
use JoelButcher\Socialstream\Contracts\HandlesInvalidState;
use JoelButcher\Socialstream\Contracts\HandlesOAuthCallbackErrors;
use JoelButcher\Socialstream\Contracts\ResolvesSocialiteUsers;
use JoelButcher\Socialstream\Contracts\SocialstreamResponse;
use Laravel\Socialite\Two\InvalidStateException;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class OAuthController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected HandlesOAuthCallbackErrors $errorHandler,
        protected ResolvesSocialiteUsers $userResolver,
        protected AuthenticatesOAuthCallback $authenticator,
        protected HandlesInvalidState $invalidStateHandler
    ) {
        //
    }

    /**
     * Get the redirect for the given Socialite provider.
     */
    public function redirect(string $provider, GeneratesProviderRedirect $generator): SymfonyRedirectResponse
    {
        Session::put('socialstream.previous_url', back()->getTargetUrl());

        return $generator->generate($provider);
    }

    /**
     * Attempt to log the user in via the provider user returned from Socialite.
     */
    public function callback(Request $request, string $provider): SocialstreamResponse|RedirectResponse|Response
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

        return $this->authenticator->authenticate($provider, $providerAccount);
    }
}

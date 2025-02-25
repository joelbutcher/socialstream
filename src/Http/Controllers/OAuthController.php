<?php

namespace JoelButcher\Socialstream\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Inertia\Response as InertiaResponse;
use JoelButcher\Socialstream\Contracts\AuthenticatesOAuthCallback;
use JoelButcher\Socialstream\Contracts\GeneratesProviderRedirect;
use JoelButcher\Socialstream\Contracts\HandlesInvalidState;
use JoelButcher\Socialstream\Contracts\HandlesOAuthCallbackErrors;
use JoelButcher\Socialstream\Contracts\ResolvesSocialiteUsers;
use JoelButcher\Socialstream\Contracts\SocialstreamResponse;
use JoelButcher\Socialstream\Events\OAuthProviderLinkFailed;
use JoelButcher\Socialstream\Http\Responses\OAuthProviderLinkFailedResponse;
use JoelButcher\Socialstream\Providers;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Jetstream\Jetstream;
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

    /**
     * Show the oauth confirmation page.
     */
    public function prompt(string $provider): View|InertiaResponse
    {
        return app()->call(Socialstream::getOAuthConfirmationPrompt(), ['provider' => $provider]);
    }

    public function confirm(string $provider): SocialstreamResponse|RedirectResponse
    {
        request()->validate([
            'result' => ['required', 'in:confirm,deny'],
        ]);

        $user = auth()->user();
        $providerAccount = cache()->pull("socialstream.{$user->id}:$provider.provider");

        $result = request()->input('result');

        if ($result === 'deny') {
            event(new OAuthProviderLinkFailed($user, $provider, null, $providerAccount));

            $this->flashError(
                __('Failed to link :provider account. User denied request.', ['provider' => Providers::name($provider)]),
            );

            return app(OAuthProviderLinkFailedResponse::class);
        }

        if (! $providerAccount) {
            throw new \DomainException(
                message: 'Could not retrieve social provider information.'
            );
        }

        return $this->authenticator->link($user, $provider, $providerAccount);
    }

    private function flashError(string $error): void
    {
        if (auth()->check()) {
            if (class_exists(Jetstream::class)) {
                Session::flash('flash.banner', $error);
                Session::flash('flash.bannerStyle', 'danger');

                return;
            }
        }

        Session::flash('errors', (new ViewErrorBag)->put(
            'default',
            new MessageBag(['socialstream' => $error])
        ));
    }
}

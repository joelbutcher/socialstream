<?php

namespace JoelButcher\Socialstream\Http\Responses;

use Illuminate\Http\RedirectResponse;
use JoelButcher\Socialstream\Contracts\OAuthProviderLinkFailedResponse as OAuthProviderLinkFailedResponseContract;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Jetstream\Jetstream;

class OAuthProviderLinkFailedResponse implements OAuthProviderLinkFailedResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        if (class_exists(Jetstream::class)) {
            return redirect()->route('profile.show');
        }

        return Socialstream::redirects('provider-link-failed')
            ? redirect()->intended(Socialstream::redirects('provider-link-failed'))
            : redirect(config('socialstream.home'));
    }
}

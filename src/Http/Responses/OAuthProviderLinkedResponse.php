<?php

namespace JoelButcher\Socialstream\Http\Responses;

use Illuminate\Http\RedirectResponse;
use JoelButcher\Socialstream\Contracts\OAuthProviderLinkedResponse as OAuthProviderLinkedResponseContract;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Jetstream\Jetstream;

class OAuthProviderLinkedResponse implements OAuthProviderLinkedResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        if (class_exists(Jetstream::class)) {
            return redirect()->route('profile.show');
        }

        return Socialstream::redirects('provider-linked')
            ? redirect()->intended(Socialstream::redirects('provider-linked'))
            : redirect(config('socialstream.home'));
    }
}

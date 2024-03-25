<?php

namespace JoelButcher\Socialstream\Http\Responses;

use Illuminate\Http\RedirectResponse;
use JoelButcher\Socialstream\Contracts\OAuthRegisterFailedResponse as OAuthRegisterFailedResponseContract;
use JoelButcher\Socialstream\Socialstream;

/**
 * @deprecated in v7, use OAuthFailedResponse instead.
 */
class OAuthRegisterFailedResponse implements OAuthRegisterFailedResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        return Socialstream::redirects('registration-failed')
            ? redirect()->to(Socialstream::redirects('registration-failed'))
            : redirect(
                session()->pull('socialstream.previous_url')
            );
    }
}

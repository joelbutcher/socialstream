<?php

namespace JoelButcher\Socialstream\Actions;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use JoelButcher\Socialstream\Contracts\GeneratesProviderRedirect;
use Laravel\Socialite\Facades\Socialite;

class GenerateRedirectForProvider implements GeneratesProviderRedirect
{
    /**
     * Generates the redirect for a given provider.
     */
    public function generate(string $provider): RedirectResponse
    {
        Session::put(
            key: 'socialstream.previous_url',
            value: url()->previous(),
        );

        return Socialite::driver($provider)->redirect();
    }
}

<?php

namespace JoelButcher\Socialstream\Actions;

use JoelButcher\Socialstream\Contracts\GeneratesProviderRedirect;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GenerateRedirectForProvider implements GeneratesProviderRedirect
{
    /**
     * Generates the redirect for a given provider.
     */
    public function generate(string $provider): RedirectResponse
    {
        session()->put(
            key: 'socialstream.previous_url',
            value: url()->previous(),
        );

        return Socialite::driver($provider)->redirect();
    }
}

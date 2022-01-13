<?php

namespace JoelButcher\Socialstream\Contracts;

interface GeneratesProviderRedirect
{
    /**
     * Generates the redirect for a given provider.
     *
     * @param  string  $provider
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function generate(string $provider);
}

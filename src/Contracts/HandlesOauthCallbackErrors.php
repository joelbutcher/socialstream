<?php

namespace JoelButcher\Socialstream\Contracts;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

interface HandlesOauthCallbackErrors
{
    /**
     * Handles the request if the "errors" key is present.
     */
    public function handle(Request $request): ?RedirectResponse;
}

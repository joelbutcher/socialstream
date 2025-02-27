<?php

namespace JoelButcher\Socialstream\Actions;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use JoelButcher\Socialstream\Contracts\HandlesOAuthCallbackErrors;

class HandleOAuthCallbackErrors implements HandlesOAuthCallbackErrors
{
    /**
     * Handles the request if the "errors" key is present.
     */
    public function handle(Request $request): ?RedirectResponse
    {
        if (! $request->has('error')) {
            return null;
        }

        $error = $request->get('error_description', $request->get('error'));

        if (! $request->user()) {
            return to_route('login')
                ->with('socialstream.error', $error);
        }

        return to_route('linked-accounts')
            ->with('socialstream.error', $error);
    }
}

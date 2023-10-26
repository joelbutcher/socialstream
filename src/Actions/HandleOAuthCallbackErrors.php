<?php

namespace JoelButcher\Socialstream\Actions;

use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\MessageBag;
use JoelButcher\Socialstream\Concerns\InteractsWithComposer;
use JoelButcher\Socialstream\Contracts\HandlesOAuthCallbackErrors;

class HandleOAuthCallbackErrors implements HandlesOAuthCallbackErrors
{
    use InteractsWithComposer;

    /**
     * Handles the request if the "errors" key is present.
     */
    public function handle(Request $request): ?RedirectResponse
    {
        if (! $request->has('error')) {
            return null;
        }

        $error = $request->get('error_description');

        if (! auth()->check()) {
            return $this->unauthenticatedRedirectWithError($error);
        }

        $previousUrl = session()->pull('socialstream.previous_url');

        return match(true) {
            Route::has('filament.home') && $previousUrl === route('filament.home') => redirect()
                ->route('filament.home')
                ->withErrors((new MessageBag)->add('socialstream', $error)),
            $this->hasComposerPackage('laravel/breeze') => redirect()
                ->route(match(true) {
                    Route::has('profile.show') => 'profile.show',
                    Route::has('profile.edit') => 'profile.edit',
                    Route::has('profile') => 'profile',
                })
                ->withErrors(['callback' => $error]),
            default => redirect()
                ->route('profile.show')
                ->dangerBanner($error),
        };
    }

    private function unauthenticatedRedirectWithError(string $error): RedirectResponse
    {
        $previousUrl = session()->pull('socialstream.previous_url');

        if (Route::has('filament.admin.auth.login') && $previousUrl === route('filament.admin.auth.login')) {
            return redirect()
                ->route('filament.admin.auth.login')
                ->withErrors((new MessageBag)->add('socialstream', $error));
        }

        return redirect()->route('login')->withErrors((new MessageBag)->add('socialstream', $error));
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use JoelButcher\Socialstream\Enums\Provider;
use JoelButcher\Socialstream\Socialstream;

class SocialstreamServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Socialstream::promptOAuthLinkUsing(function (Provider $provider) {
            return Inertia::render('auth/confirm-link-account', [
                'provider' => $provider->toArray(),
            ]);
        });
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use JoelButcher\Socialstream\Enums\Provider;
use JoelButcher\Socialstream\Socialstream;
use Livewire\Volt\Volt;

class SocialstreamServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Socialstream::promptOAuthLinkUsing(function (Provider $provider) {
            return to_route('confirm-link-account', [
                'provider' => $provider,
            ]);
        });
    }
}

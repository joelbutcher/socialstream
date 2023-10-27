<?php

namespace JoelButcher\Socialstream\Tests;

use Illuminate\Support\Facades\Config;
use JoelButcher\Socialstream\Actions\AuthenticateOAuthCallback;
use JoelButcher\Socialstream\Actions\HandleOAuthCallbackErrors;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Fortify\FortifyServiceProvider;
use Laravel\Jetstream\JetstreamServiceProvider;
use Livewire\LivewireServiceProvider;

abstract class JetstreamTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('jetstream.stack', 'livewire');
        Config::set('jetstream.features', []);

        Socialstream::authenticatesOAuthCallbackUsing(AuthenticateOAuthCallback::class);
        Socialstream::handlesOAuthCallbackErrorsUsing(HandleOAuthCallbackErrors::class);
    }

    protected function getPackageProviders($app): array
    {
        return array_merge(
            parent::getPackageProviders($app),
            [LivewireServiceProvider::class, JetstreamServiceProvider::class, FortifyServiceProvider::class]
        );
    }
}

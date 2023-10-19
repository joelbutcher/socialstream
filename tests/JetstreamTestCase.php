<?php

namespace JoelButcher\Socialstream\Tests;

use Illuminate\Support\Facades\Config;
use JoelButcher\Socialstream\Actions\Auth\Jetstream\AuthenticateOAuthCallback;
use JoelButcher\Socialstream\Actions\Auth\Jetstream\HandleOAuthCallbackErrors;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Fortify\FortifyServiceProvider;
use Laravel\Jetstream\JetstreamServiceProvider;

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
            [JetstreamServiceProvider::class, FortifyServiceProvider::class]
        );
    }
}

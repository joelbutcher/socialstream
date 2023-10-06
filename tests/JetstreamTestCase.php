<?php

namespace JoelButcher\Socialstream\Tests;

use Illuminate\Support\Facades\Config;
use JoelButcher\Socialstream\Actions\Auth\Jetstream\AuthenticateOauthCallback;
use JoelButcher\Socialstream\Actions\Auth\Jetstream\HandleOauthCallbackErrors;
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

        Socialstream::authenticatesOauthCallbackUsing(AuthenticateOauthCallback::class);
        Socialstream::handlesOAuthCallbackErrorsUsing(HandleOauthCallbackErrors::class);
    }

    protected function getPackageProviders($app): array
    {
        return array_merge(
            parent::getPackageProviders($app),
            [JetstreamServiceProvider::class, FortifyServiceProvider::class]
        );
    }
}

<?php

namespace JoelButcher\Socialstream\Tests;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use JoelButcher\Socialstream\SocialstreamServiceProvider;
use Laravel\Jetstream\JetstreamServiceProvider;
use Laravel\Socialite\SocialiteServiceProvider;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class OrchestraTestCase extends BaseTestCase
{
    use LazilyRefreshDatabase, WithWorkbench;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['migrator']->path(__DIR__.'/../database/migrations/2022_12_21_000000_make_password_nullable_on_users_table.php');
        $app['migrator']->path(__DIR__.'/../database/migrations/2020_12_22_000000_create_connected_accounts_table.php');

        $app['config']->set('app.debug', true);
        $app['config']->set('database.default', 'testing');

        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('services.github', [
            'client_id' => 'github-client-id',
            'client_secret' => 'github-client-secret',
            'redirect' => 'https://example.test/oauth/github/callback',
        ]);
    }

    protected function getPackageProviders($app): array
    {
        return [
            SocialstreamServiceProvider::class,
            JetstreamServiceProvider::class,
            SocialiteServiceProvider::class,
        ];
    }

    protected function defineRoutes($router): void
    {
        require __DIR__.'/../workbench/routes/web.php';
    }
}

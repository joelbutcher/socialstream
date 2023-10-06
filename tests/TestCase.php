<?php

namespace JoelButcher\Socialstream\Tests;

use App\Actions\Socialstream\CreateConnectedAccount;
use App\Actions\Socialstream\CreateUserFromProvider;
use App\Actions\Socialstream\GenerateRedirectForProvider;
use App\Actions\Socialstream\HandleInvalidState;
use App\Actions\Socialstream\ResolveSocialiteUser;
use App\Actions\Socialstream\SetUserPassword;
use App\Actions\Socialstream\UpdateConnectedAccount;
use Illuminate\Support\Facades\Config;
use JoelButcher\Socialstream\Socialstream;
use JoelButcher\Socialstream\SocialstreamServiceProvider;
use Laravel\Socialite\SocialiteServiceProvider;
use Mockery;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.github', [
            'client_id' => 'github-client-id',
            'client_secret' => 'github-client-secret',
            'redirect' => 'https://example.test/oauth/github/callback',
        ]);

        Socialstream::resolvesSocialiteUsersUsing(ResolveSocialiteUser::class);
        Socialstream::createUsersFromProviderUsing(CreateUserFromProvider::class);
        Socialstream::createConnectedAccountsUsing(CreateConnectedAccount::class);
        Socialstream::updateConnectedAccountsUsing(UpdateConnectedAccount::class);
        Socialstream::setUserPasswordsUsing(SetUserPassword::class);
        Socialstream::handlesInvalidStateUsing(HandleInvalidState::class);
        Socialstream::generatesProvidersRedirectsUsing(GenerateRedirectForProvider::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    protected function getPackageProviders($app): array
    {
        return [SocialstreamServiceProvider::class, SocialiteServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['migrator']->path(__DIR__.'/../database/migrations/2014_10_12_000000_create_users_table.php');
        $app['migrator']->path(__DIR__.'/../database/migrations/2020_12_22_000000_create_connected_accounts_table.php');

        $app['config']->set('database.default', 'testbench');

        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function migrate(): void
    {
        $this->artisan('migrate', ['--database' => 'testbench'])->run();
    }
}

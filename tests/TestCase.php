<?php

namespace JoelButcher\Socialstream\Tests;

use JoelButcher\Socialstream\SocialstreamServiceProvider;
use Laravel\Fortify\FortifyServiceProvider;
use Laravel\Jetstream\JetstreamServiceProvider;
use Laravel\Socialite\SocialiteServiceProvider;
use Mockery;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    protected function getPackageProviders($app): array
    {
        return [SocialstreamServiceProvider::class, JetstreamServiceProvider::class, FortifyServiceProvider::class, SocialiteServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['migrator']->path(__DIR__.'/../database/migrations');

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

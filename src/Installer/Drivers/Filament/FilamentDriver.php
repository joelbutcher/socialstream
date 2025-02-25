<?php

namespace JoelButcher\Socialstream\Installer\Drivers\Filament;

use Illuminate\Support\ServiceProvider;
use JoelButcher\Socialstream\Installer\Drivers\Driver;
use JoelButcher\Socialstream\Installer\Enums\InstallOptions;
use JoelButcher\Socialstream\Installer\Enums\TestRunner;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

use function Laravel\Prompts\spin;
use function Laravel\Prompts\warning;

class FilamentDriver extends Driver
{
    protected function ensureDependenciesAreInstalled(string $composerBinary, InstallOptions ...$options): void
    {
        if (! $this->hasComposerPackage('filament/filament')) {
            warning('Filament Admin Panel is not installed.');

            spin(function () use ($composerBinary) {
            $this->requireComposerPackages(['filament/filament'], $composerBinary);

            (new Process([$this->phpBinary(), 'artisan', 'filament:install', '--panels', '--force', '--quiet'], base_path()))
                ->setTimeout(null)
                ->run(function ($type, $output) {
                    (new BufferedOutput)->write($output);
                });
        }, message: 'Installing Filament Admin Panel...');

            \Laravel\Prompts\info('Filament Admin Panel has been installed successfully!');
        }

        if (! in_array(InstallOptions::Pest, $options)) {
            return;
        }

        if ($this->hasComposerPackage('pestphp/pest')) {
            return;
        }

        warning('Pest is not installed.');

        spin(function () {
            if ($this->hasComposerPackage('phpunit/phpunit')) {
                $this->removeComposerDevPackages(['phpunit/phpunit']);
            }

            $this->requireComposerDevPackages(['pestphp/pest:^2.0', 'pestphp/pest-plugin-laravel:^2.0']);

            $stubs = __DIR__.'/../../../../stubs/filament/pest-tests';

            copy($stubs.'/Pest.php', base_path('tests/Pest.php'));
            copy($stubs.'/ExampleTest.php', base_path('tests/Feature/ExampleTest.php'));
            copy($stubs.'/ExampleUnitTest.php', base_path('tests/Unit/ExampleTest.php'));
        }, message: 'Installing Pest...');
    }

    protected function copyModelsAndFactories(): static
    {
        parent::copyModelsAndFactories();

        copy(__DIR__.'/../../../../stubs/filament/app/Models/User.php', app_path('Models/User.php'));
        copy(__DIR__.'/../../../../stubs/filament/database/factories/UserFactory.php', database_path('factories/UserFactory.php'));

        return $this;
    }

    protected function installServiceProviders(): static
    {
        parent::installServiceProviders();

        ServiceProvider::addProviderToBootstrapFile('JoelButcher\Socialstream\Filament\SocialstreamPanelProvider');

        return $this;
    }

    protected function copyTests(TestRunner $testRunner): static
    {

        copy(from: match ($testRunner) {
            TestRunner::Pest => __DIR__.'/../../../../stubs/filament/pest-tests/SocialstreamRegistrationTest.php',
            TestRunner::PhpUnit => __DIR__.'/../../../../stubs/filament/tests/SocialstreamRegistrationTest.php',
        }, to: base_path('tests/Feature/SocialstreamRegistrationTest.php'));

        return $this;
    }
}

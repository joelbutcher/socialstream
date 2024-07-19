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
    protected function configureFortify(OutputInterface $outputStyle): void
    {
        (new Process([$this->phpBinary(), 'artisan', 'vendor:publish', '--tag=fortify-config', '--force'], base_path()))
            ->setTimeout(null)
            ->run(function ($type, $output) use ($outputStyle) {
                $outputStyle->write($output);
            });

        (new Process([$this->phpBinary(), 'artisan', 'vendor:publish', '--tag=fortify-migrations', '--force'], base_path()))
            ->setTimeout(null)
            ->run(function ($type, $output) use ($outputStyle) {
                $outputStyle->write($output);
            });

        (new Process([$this->phpBinary(), 'artisan', 'vendor:publish', '--tag=fortify-support', '--force'], base_path()))
            ->setTimeout(null)
            ->run(function ($type, $output) use ($outputStyle) {
                $outputStyle->write($output);
            });

        $this->replaceInFile("'views' => true,", "'views' => false,", config_path('fortify.php'));
    }

    protected function ensureDependenciesAreInstalled(string $composerBinary, InstallOptions ...$options): void
    {
        if (class_exists('App\Providers\Filament\AdminPanelProvider')) {
            return;
        }

        warning('Filament Admin Panel is not installed.');

        spin(function () use ($composerBinary) {
            if (! $this->hasComposerPackage('filament/filament')) {
                $this->requireComposerPackages(['filament/filament'], $composerBinary);
            }

            (new Process([
                $this->phpBinary(),
                'artisan',
                'filament:install',
                '--panels',
                '--force',
                '--quiet',
            ], base_path()))
                ->setTimeout(null)
                ->run(function ($type, $output) {
                    (new BufferedOutput)->write($output);
                });
        }, message: 'Installing Filament Admin Panel...');

        \Laravel\Prompts\info('Filament Admin Panel has been installed successfully!');
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

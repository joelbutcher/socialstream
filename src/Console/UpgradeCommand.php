<?php

namespace JoelButcher\Socialstream\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Str;
use JoelButcher\Socialstream\Concerns\InteractsWithComposer;
use JoelButcher\Socialstream\Installer\Drivers\Jetstream\InertiaDriver;
use JoelButcher\Socialstream\Installer\Drivers\Jetstream\JetstreamDriver;
use JoelButcher\Socialstream\Installer\Drivers\Jetstream\LivewireDriver;
use JoelButcher\Socialstream\Installer\Enums\InstallStarterKit;
use JoelButcher\Socialstream\Socialstream;

use function Laravel\Prompts\alert;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\spin;

class UpgradeCommand extends Command implements PromptsForMissingInput
{
    use InteractsWithComposer;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'socialstream:upgrade';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically upgrade your Socialstream install.';

    public function handle(): int
    {
        if (Socialstream::VERSION === '6.0.0') {
            \Laravel\Prompts\info('Nothing to upgrade.');

            return self::INVALID;
        }

        if (! file_exists(config_path('socialstream.php'))) {
            alert('Socialstream is not installed, please run the `socialstream:install` command to setup Socialstream');

            return self::INVALID;
        }

        if (! $starterKit = $this->determineStarterKit()) {
            error('Could not determine starter kit.');

            return self::FAILURE;
        }

        return $this->upgrade($starterKit);
    }

    private function determineStarterKit(): InstallStarterKit|false
    {
        if ($this->hasComposerPackage('laravel/jetstream')) {
            return InstallStarterKit::Jetstream;
        }

        if ($this->hasComposerPackage('laravel/breeze')) {
            return InstallStarterKit::Breeze;
        }

        if ($this->hasComposerPackage('filament/filament')) {
            return InstallStarterKit::Filament;
        }

        return false;
    }

    private function upgrade(InstallStarterKit $starterKit): int
    {
        intro('Let\'s upgrade your Socialstream installation!');

        if (confirm(
            label: 'Have you made any changes to your 5.x Socialstream installation?',
            default: false,
        )) {
            alert('Upgrading using this command will remove any changes you have made to your published files.');
            \Laravel\Prompts\info('Please visit https://docs.socialstream.dev/prologue/upgrade-guide/upgrading-to-v6-from-5.x to upgrade manually');

            return self::FAILURE;
        }

        $method = Str::of($starterKit->value)
            ->ucfirst()
            ->append('upgrade')
            ->toString();

        $this->$method();

        return self::SUCCESS;
    }

    private function upgradeJetstream(): void
    {
        // @todo
    }

    private function upgradeBreeze(): void
    {
        // @todo
    }

    private function upgradeFilament(): void
    {
        // @todo
    }
}

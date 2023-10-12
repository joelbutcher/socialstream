<?php

namespace JoelButcher\Socialstream\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Str;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\search;
use function Laravel\Prompts\table;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

class CreateProviderCommand extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'socialstream:provider
                            {provider : The name of the provider to configure}
                            {client : The Client ID}
                            {secret : The Client Secret}';

    /**
     * The console command description.
     *
     * @var string|null
     */
    protected $description = 'Configure a new OAuth provider';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $provider = $this->argument('provider');

        if (in_array($provider, array_keys(config('services')))) {
            warning("A service configuration already exists for $provider.");

            if (! confirm(label: 'Do you want to overwrite it?', default: false)) {
                return self::INVALID;
            }
        }

        $this->addEnvironmentVariables();
        $this->addServicesToConfig();

        return self::SUCCESS;
    }

    /**
     * Prompt for missing input arguments using the returned questions.
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'provider' => fn () => search(
                label: 'Which provider would you like to create a configuration for?',
                options: fn (string $search) => array_values(array_filter(
                    array_diff(config('socialstream.providers'), array_keys(config('services'))),
                    fn ($choice) => str_contains(strtolower($choice), strtolower($search))
                )),
                placeholder: 'Search...',
                scroll: 10,
            ),
            'client' => fn () => text(
                label: 'Client ID?',
                required: true,
            ),
            'secret' => fn () => text(
                label: 'Client Secret',
                required: true,
            ),
        ];
    }

    private function addEnvironmentVariables(): void
    {
        $provider = Str::of($this->argument('provider'))->replace('-', '_')->upper()->toString();

        $clientIdKey = $provider.'_CLIENT_ID';
        $clientSecretKey = $provider.'_CLIENT_SECRET';
        $redirectKey = $provider.'_REDIRECT';

        table(
            headers: ['Variable', 'Value'],
            rows: [
                ['variable' => $clientIdKey, 'value' => $this->argument('client')],
                ['variable' => $clientSecretKey, 'value' => $this->argument('secret')],
                ['variable' => $redirectKey, 'value' => route('oauth.redirect', $this->argument('provider'))],
            ],
        );

        if (! confirm('Do you want to add the environment variables to your .env file?')) {
            return;
        }

        $dotEnvContents = file_get_contents(
            filename: base_path('.env')
        );

        if (
            str_contains(haystack: $dotEnvContents, needle: $clientIdKey) ||
            str_contains(haystack: $dotEnvContents, needle: $clientSecretKey)
        ) {
            warning(message: 'Environment variables already exist for this provider.');

            if (! confirm('Do you want to continue', required: true)) {
                return;
            }
        }

        $comment = Str::of($provider)->lower()->headline()->toString();

        file_put_contents(
            filename: base_path('.env'),
            data: Str::of(string: $dotEnvContents)
                ->append(PHP_EOL)
                ->append("# $comment Credentials".PHP_EOL)
                ->append("$clientIdKey=".$this->argument('client').PHP_EOL)
                ->append("$clientSecretKey=".$this->argument('secret').PHP_EOL)
                ->append("$redirectKey=".'"${APP_URL}/'.trim($this->buildRedirectPath(), '/').'"'.PHP_EOL)
                ->toString(),
        );

        if (! confirm('Do you want to add the environment variables to your .env.example file?', required: true)) {
            return;
        }

        file_put_contents(
            filename: base_path('.env.example'),
            data: Str::of(
                string: file_get_contents(
                    filename: base_path('.env.example')
                )
            )
                ->append(PHP_EOL)
                ->append("# $comment Credentials".PHP_EOL)
                ->append("$clientIdKey=".PHP_EOL)
                ->append("$clientSecretKey=".PHP_EOL)
                ->append("$redirectKey=".PHP_EOL)
                ->toString(),
        );
    }

    private function addServicesToConfig(): void
    {
        $provider = $this->argument('provider');
        $envProvider = Str::of($this->argument('provider'))->replace('-', '_')->upper()->toString();

        if (config("services.$provider")) {
            \Laravel\Prompts\info("Config already set in services.$provider");

            return;
        }

        $search1 = <<<'PHP'
    ],

];
PHP;

        $search2 = <<<'PHP'
    ],
];
PHP;

        $replace = <<<PHP
    ],

    '$provider' => [
        'client_id' => env("{$envProvider}_CLIENT_ID"),
        'client_secret' => env("{$envProvider}_CLIENT_SECRET"),
        'redirect' => env("{$envProvider}_REDIRECT"),
    ],
];
PHP;

        file_put_contents(
            filename: config_path('services.php'),
            data: Str::of(
                string: file_get_contents(
                    filename: config_path('services.php'),
                )
            )
                ->replace($search1, $search2)
                ->replace(
                    search: $search2,
                    replace: $replace
                )
                ->toString(),
        );
    }

    private function buildRedirectPath(): string
    {
        return '/oauth/'.$this->argument('provider').'/callback';
    }
}

<?php

namespace JoelButcher\Socialstream\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\Facades\Config;
use JoelButcher\Socialstream\Features;

class SocialstreamPlugin implements Plugin
{
    public function getId(): string
    {
        return 'socialstream';
    }

    public function register(Panel $panel): void
    {
        Config::set('socialstream.features', array_merge(config('socialstream.features'), [
            Features::createAccountOnFirstLogin(),
        ]));

        $panel->renderHook('panels::auth.login.form.after', function () {
            return view('components.socialstream', [
                'errors' => session('errors')?->get('socialstream') ?? [],
            ]);
        });
    }

    public function boot(Panel $panel): void
    {
        // TODO: Implement boot() method.
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}

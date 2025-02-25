<?php

namespace JoelButcher\Socialstream\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ViewErrorBag;
use JoelButcher\Socialstream\Features;
use JoelButcher\Socialstream\Socialstream;

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
            return Socialstream::show() ?
                view(config('socialstream.component', 'socialstream::components.socialstream'), [
                    'errors' => session('errors') ?? new ViewErrorBag,
                ]) : '';
        });

        if ($panel->hasRegistration()) {
            $panel->renderHook('panels::auth.register.form.after', function () {
                return Socialstream::show() ?
                    view(config('socialstream.component', 'socialstream::components.socialstream'), [
                        'errors' => session('errors') ?? new ViewErrorBag,
                    ]) : '';
            });
        }
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

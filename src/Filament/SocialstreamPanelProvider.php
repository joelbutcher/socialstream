<?php

namespace JoelButcher\Socialstream\Filament;

use App\Providers\Filament\AdminPanelProvider;
use Filament\Panel;

class SocialstreamPanelProvider extends AdminPanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return parent::panel($panel)->plugin(
            new SocialstreamPlugin(),
        );
    }
}

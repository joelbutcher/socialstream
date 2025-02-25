<?php

use JoelButcher\Socialstream\Providers;

return [
    'guard' => 'web', // used if Fortify is not installed
    'middleware' => ['web'],
    'prompt' => 'Or Login Via',
    'providers' => [
        // Providers::github(),
    ],
    'components' => 'socialstream::components.socialstream',
    'filament-route' => 'filament.admin.pages.dashboard',
];

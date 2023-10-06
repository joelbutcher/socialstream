<?php

use JoelButcher\Socialstream\Features;
use JoelButcher\Socialstream\Providers;

return [
    'middleware' => ['web'],
    'providers' => [
        // Providers::github(),
    ],
    'features' => [
        // Features::createAccountOnFirstLogin(),
        // Features::generateMissingEmails(),
        Features::rememberSession(),
        Features::providerAvatars(),
        Features::refreshOauthTokens(),
    ],
];

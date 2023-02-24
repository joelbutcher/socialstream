<?php

use JoelButcher\Socialstream\Features;
use JoelButcher\Socialstream\Providers;

return [

    /*
    |--------------------------------------------------------------------------
    | Socialstream Route Middleware
    |--------------------------------------------------------------------------
    |
    | Here you may specify which middleware Socialstream will assign to the
    | routes that it registers with the application. When necessary, you may
    | modify these middleware; however, this default value is usually sufficient.
    |
    */

    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Socialstream Providers
    |--------------------------------------------------------------------------
    |
    | Here you may specify the providers your application supports for OAuth.
    | Out of the box, Socialstream provides support for all of the OAuth
    | providers that are supported by Laravel Socialite.
    |
    */

    'providers' => [
        // Providers::github(),
    ],

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | Some of Socialstreams's features are optional. You may disable the features
    | by removing them from this array. You're free to only remove some of
    | these features or you can even remove all of these if you need to.
    |
    */

    'features' => [
        // Features::createAccountOnFirstLogin(),
        // Features::generateMissingEmails(),
        Features::rememberSession(),
        Features::providerAvatars(),
        Features::refreshOauthTokens(),
    ],
];

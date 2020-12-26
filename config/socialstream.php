<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Socialstream Visibility
    |--------------------------------------------------------------------------
    |
    | This value is used to determine whether or not to show the providers
    | on "/login" or "/register" routes.
    |
    | Note: if you have previously enabled socialstream, and have users in
    | your database with any connected accounts, they will still be able to
    | disconnect them from their profile.
    |
    */

    'show' => env('SHOW_SOCIALSTREAM', true),

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
    | Supported: "google", "facebook", "github", "gitlab",
    |            "bitbucket", "linkedin", "twitter"
    |
    */

    'providers' => [
        // 'github',
    ],
];

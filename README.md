<p align="center"><img src="https://ik.imagekit.io/r6kac144kke/logo_tNST3_4jfkSh.png" width="450"></p>

<p align="center">
    <a href="https://github.com/joelbutcher/socialstream/actions">
        <img src="https://github.com/joelbutcher/socialstream/workflows/tests/badge.svg" alt="Build Status">
    </a>
    <a href="https://packagist.org/packages/joelbutcher/socialstream">
        <img src="https://img.shields.io/packagist/dt/joelbutcher/socialstream" alt="Total Downloads">
    </a>
    <a href="https://packagist.org/packages/joelbutcher/socialstream">
        <img src="https://img.shields.io/packagist/v/joelbutcher/socialstream" alt="Latest Stable Version">
    </a>
    <a href="https://packagist.org/packages/joelbutcher/socialstream">
        <img src="https://img.shields.io/packagist/l/joelbutcher/socialstream" alt="License">
    </a>
</p>

## Introduction

Socialstream is a third-party package for [Laravel Jetstream](https://github.com/laravel/jetstream). It replaces the published authentication and profile scaffolding provided by Laravel Jetstream, with scaffolding that has support for [Laravel Socialite](https://laravel.com/docs/8.x/socialite).

If you are unfamiliar with Laravel Socialite, it is strongly advised that you take a look at the [official documentation](https://laravel.com/docs/8.x/socialite). 

## Installation

Getting started with Socialstream is a breeze. With a simple two-step process to get you on your way to creating the next big thing. Inspired by the simplicity of Jetstream's installation process, Socialstream follows the same 'installation':

```sh
composer require joelbutcher/socialstream

php artisan socialstream:install
```

The `socialstream:install` command will overwrite the Jetstream published files which are required for Socialstream to work. 

> Note: If you don't have Laravel Jetstream installed, the above command will walk you through the steps required to install it.

## Configuration & Setup
Once Socialstream is installed, it will publish a config file. In this config file, you can define whether or not the packages alterations should be shown, the middleware used to wrap the routes as well as the providers that you wish to use:

```php
<?php

return [
    'middleware' => ['web'],
    'providers' => [
        \JoelButcher\Socialstream\Providers::github(),
        \JoelButcher\Socialstream\Providers::facebook(),
        \JoelButcher\Socialstream\Providers::google()
    ],
    'features' => [
        // \JoelButcher\Socialstream\Features::rememberSession(),
    ],
];
```

Once you’ve defined your providers, you will need to update your `services.php` config file and create `client_id`, `client_secret` and `redirect` keys for each provider:

```php
'{provider}' => [
    'client_id' => env('{PROVIDER}_CLIENT_ID'),
    'client_secret' => env('{PROVIDER}_CLIENT_SECRET'),
    'redirect' => env('{PROVIDER}_REDIRECT'), // e.g. 'https://your-domain.com/oauth/{provider}/callback'
],
```

### Generating the redirect.

In some cases, you may want to customise how Socialite handles the generation of the redirect to a provider. For example, you may wish to To do this, you may alter the `GenerateRedirectForProvider` action found in `app/Actions/Socialstream`. For example, you may need to define scopes, the response type (e.g. implicit grant type), or any additional request info:

```php
/**
 * Generates the redirect for a given provider.
 * 
 * @param  string  $provider
 * 
 * @return \Symfony\Component\HttpFoundation\RedirectResponse
 */
public function generate(string $provider)
{
    return Socialite::driver($provider)
        ->scopes(['*'])
        ->with(['response_type' => 'token'])
        ->redirect();
}
```

### Resolving users

By default, Socialstream will resolve user information from Socialite using the following logic:

```php
Socialite::driver($provider)->user();
```

Returning an instance of `\Laravel\Socialite\AbstractUser`. However, you may wish to customise the way user resolution is done. For example, you may wish to use the `stateless` method available for some Socialite providers. Socialstream makes this easy for you and publishes a `ResolveSocialiteUser` action to you applications `app/Actions/Socialstream` directory. Simply customise this class with the logic required for your use-casee.

### Handling Invalid State

To handle instances where Socialite throws an `InvalidStateException` a dedicated `HandleInvalidState` action is made available to you when you first install Socialstream. You are free to modify or extend this action according to your needs. 

Alternatively, you may write your own action to handle the exception. To do so, you'll need to implement `JoelButcher\Socialstream\Contracts\HandlesInvalidState` and update the following line in `App\Providers\SocialstreamServiceProvider`

```php
Socialstream::handlesInvalidStateUsing(HandleInvalidState::class);
```

## Socialite Providers

If you wish to use the community driven [socialiteproviders](https://socialiteproviders.com) package with Socialstream, you may do so by following their documentation on installing the package into a Laravel project. There are a few configuration steps you will need to go through first.

To implement a custom provider, you will need to create an SVG icon file (e.g. `twitter-icon.blade.php` or `TwitterIcon.vue`) to be used in the authentication cards and the account management panel.

You will then need to alter the appropriate published components with your new icons and provider condition:

- Connected Account component
- Socialstream Providers component

> Note: Some providers will not return a token in the callback response. As such, you will need to modify the `2020_12_22_000000_create_connected_accounts_table.php` migration to allow the `token` field to accept `NULL` values

## Changelog

Check out the [CHANGELOG](CHANGELOG.md) in this repository for all the recent changes.

## Maintainers

Socialstream is developed and maintained by [Joel Butcher](https://joelbutcher.co.uk)

## Credits

Socialstream has a strong community of contributors helping make it the best package for integrating Socialite into your application. You can view all contributers [here](https://github.com/joelbutcher/socialstream/graphs/contributors)

## License

Socialstream is open-sourced software licensed under the [MIT license](LICENSE.md).

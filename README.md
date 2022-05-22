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

# Introduction

Socialstream is a third-party package for [Laravel Jetstream](https://github.com/laravel/jetstream). It replaces the published authentication and profile scaffolding provided by Laravel Jetstream, with scaffolding that has support for [Laravel Socialite](https://laravel.com/docs/8.x/socialite).

If you are unfamiliar with Laravel Socialite, it is strongly advised that you take a look at the [official documentation](https://laravel.com/docs/8.x/socialite). 

## ⚠️ Important! ⚠️

Socialstream, like Jetstream, should only be installed on NEW applications, installing Socialstream into an existing application will break your applications functionality. It is strongly advised against installing this package within an existing applications.

# Installation

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

## Generating the redirect.

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

## Resolving users

By default, Socialstream will resolve user information from Socialite using the following logic:

```php
Socialite::driver($provider)->user();
```

Returning an instance of `\Laravel\Socialite\AbstractUser`. However, you may wish to customise the way user resolution is done. For example, you may wish to use the `stateless` method available for some Socialite providers. Socialstream makes this easy for you and publishes a `ResolveSocialiteUser` action to you applications `app/Actions/Socialstream` directory. Simply customise this class with the logic required for your use-casee.

## Handling Invalid State

To handle instances where Socialite throws an `InvalidStateException` a dedicated `HandleInvalidState` action is made available to you when you first install Socialstream. You are free to modify or extend this action according to your needs. 

Alternatively, you may write your own action to handle the exception. To do so, you'll need to implement `JoelButcher\Socialstream\Contracts\HandlesInvalidState` and update the following line in `App\Providers\SocialstreamServiceProvider`

```php
Socialstream::handlesInvalidStateUsing(HandleInvalidState::class);
```

## Laravel Passport Support

If you wish to use this package alongside Laravel Passport, you may encounter the following error message when attempting to authorise with Passports OAuth server:

```
Driver [authorize] not supported
```

This is because SocialStream registers routes using the `oauth/{provider}` structure. This conflicts with Laravel Passports `oauth/authorize` route.
This can be resolved by adding the following to the `boot` method of your applications `AuthServiceProvider.php` file:

```

Passport::routes([
    'prefix' => 'passport-oauth',
]);
```

# Features

Below, you can find a complete list of optional features included with Socialstream as of version 3.x.

## Create account on first login

This feature enables the capability to register a new user account (and team if the Jetstream feature is enabled) when a user attempts to authenticate via the '/login' route.

To turn on this feature add the following to `config/socialstream.php`:

```php
'features' => [
    Features::createAccountOnFirstLogin(),
],
```

## Log in on registration

If a user has already registered with a particular email address, and the OAuth account they attempt to register with returns the same email, the provider will be linked to the existing user and they will be logged in.

To turn on this feature add the following to `config/socialstream.php`:

```php
'features' => [
    Features::loginOnRegistration(),
],
```

## Remember Session

This feature passes the boolean value "remember" value to `true` when authenticating with Laravel Fortify 

To turn on this feature add the following to `config/socialstream.php`:

```php
'features' => [
    Features::rememberSession(),
],
```

## Handling Missing Emails

Some providers (such as GitHub), don't always return an email address when attempting to authenticate with them. In this case, Socialstream will generate a random email address for you.
This email will be in the format `user_id.provider@yourappdomain.tld`. E.g. `35362324.github@myawesomeapp.com`

To turn on this feature add the following to `config/socialstream.php`:

```php
'features' => [
    Features::generateMissingEmails(),
],
```

## Provider Avatars

This feature determines whether or not to pull in a users avatar / profile image from a provider.

To turn on this feature add the following to `config/socialstream.php`:

```php
'features' => [
    Features::providerAvatars(),
],
```


# Socialite Providers

If you wish to use the community driven [socialiteproviders](https://socialiteproviders.com) package with Socialstream, you may do so by following their documentation on installing the package into a Laravel project. There are a few configuration steps you will need to go through first.

To implement a custom provider, you will need to create an SVG icon file (e.g. `twitter-icon.blade.php` or `TwitterIcon.vue`) to be used in the authentication cards and the account management panel.

You will then need to alter the appropriate published components with your new icons and provider condition:

- Connected Account component
- Socialstream Providers component

> Note: Some providers will not return a token in the callback response. As such, you will need to modify the `2020_12_22_000000_create_connected_accounts_table.php` migration to allow the `token` field to accept `NULL` values

# Changelog

Check out the [CHANGELOG](CHANGELOG.md) in this repository for all the recent changes.

# Maintainers

Socialstream is developed and maintained by [Joel Butcher](https://joelbutcher.co.uk)

# Credits

Socialstream has a strong community of contributors helping make it the best package for integrating Socialite into your application. You can view all contributers [here](https://github.com/joelbutcher/socialstream/graphs/contributors)

# License

Socialstream is open-sourced software licensed under the [MIT license](LICENSE.md).

# Socialstream

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

The `socialstream:install` command will overwrite the Jetstream publushed files which are required for Socialstream to work. 

> Note: If you don't have Laravel Jetstream installed, the above command will walk you through the steps required to install it.

## Socialite Providers

If you wish to use the community driven [socialiteproviders](https://socialiteproviders.com) package with Socialstream, you may do so by following their documentation on installing the package into a Laravel project. There are a few configuration steps you will need to go through first.

To implement a custom provider, you will need to create an SVG icon file (e.g. `twitter-icon.blade.php` or `TwitterIcon.vue`) to be used in the authentication cards and the account management panel.

You will then need to alter the appropriate published components with your new icons and provider condition:

- Connected Account component
- Socialstream Providers component

## Changelog

Check out the [CHANGELOG](CHANGELOG.md) in this repository for all the recent changes.

## Maintainers

Socialstream is developed and maintained by [Joel Butcher](https://joelbutcher.co.uk)

## License

Socialstream is open-sourced software licensed under the [MIT license](LICENSE.md).

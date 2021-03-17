# Upgrade Guide

## Upgrading From Socialstream 2.x To Socialstream 3.x

### Changes

#### Disabling Socialstream

To disable Socialstream in v3, you will need to update your existing `SocialstreamServiceProvider.php` to include the following code snippet in your providers `boot` method:

```php
    Socialstream::enabled(fn () => false);
```

The function accepts a callback so if you wanted to implement more complex logic, you may do so.

> Note, the callback MUST return a boolean

#### Providers

V3 introduces a new `Providers` class, for defining what Socialite providers you have enabled in your config. This class is also used in the socialstream.blade.php stub and the connected-account.blade.php component stub. Please update any Socialite providers you have in your `socialstream.php` config file to use this class, e.g:


```php
use \JoelButcher\Socialstream\Providers;

return [
    // ...

    'providers' => [
        Providers::google(),
        Providers::facebook(),
    ],

];
```

#### Remember Sessions

V3 of Socialstream move the remember session config variable into the 'features' config array. During your upgrade, if you have previously set this config variable to `true`, you will need to add it to your features list.

```php
return [
    // ...

    'features' => [
        Features::rememberSession(),
    ],

];
```

#### Token Column Lengths

In version 3.x, we've fixed an issue with the length of tokens and refresh tokens being too long for the columns in the database.

To fix this yourself, you should create a new `connected_accounts` migration:

```sh
php artisan make:migration update_connected_accounts_token_lengths --table=connected_accounts
```

Once done, you should then add the following code to the `up` method:

```php
    $table->string('token', 1000)->change();
    $table->string('refresh_token', 1000)->change();
```

#### Provider Avatars

In v3, we've updated the provider avatars feature to download the avatar from the url provided by the Socialite user. If you have opted to use the `providerAvatars` feature in your config's features definition, you should add the `SetsProfilePhotoFromUrl` trait to your user model:

```php
<?php

use JoelButcher\Socialstream\SetsProfilePhotoFromUrl;

class User extends Authenticatable
{
    // ...
    use SetsProfilePhotoFromUrl;

    // ...
}
```

It's worth noting that if you still to load the users profile photo from a URL, you will need to keep the `getProfilePhotoUrlAttribute` method override published by Socialstream, in your user model.

#### Connected Account Policy

V3 uses a new policy to determine whether or not a user can access certain functionality. To ensure compatibility with v3, make sure you copy the stub found [here](https://github.com/joelbutcher/socialstream/blob/3.x/stubs/app/Policies/ConnectedAccountPolicy.php) to your `app/Policies` directory.

#### Updating Connected Accounts

Socialstream v3 will now keep your connected accounts up to date whenever a user uses SSO with your application. updating a users OAuth token and refresh token on every successful login. To make sure this works for you, copy [this stub](https://github.com/joelbutcher/socialstream/blob/3.x/stubs/app/Actions/Socialstream/UpdateConnectedAccount.php) into the `app/Actions/Socialstream` directory. You will then need to add the following to your `app\Providers\SocialstreamServiceProvider.php`:

```php
use App\Actions\Socialstream\UpdateConnectedAccount;
use JoelButcher\Socialstream\Socialstream;

// Add this to the 'boot' method.
Socialstream::updateConnectedAccountsUsing(UpdateConnectedAccount::class);
```

#### Resolving Users from Socialite

To allow additional flexibility, v3 allows you to override how your app resolves users from Socialite. For example, you may wish to call the `stateless` method, like so:

```php
$user = Socialite::driver('facebook')->stateless()->user();
```

To ensure v3 compatibility, copy the `ResolveSocialiteUser` action stub found [here](https://github.com/joelbutcher/socialstream/blob/3.x/stubs/app/Actions/Socialstream/ResolveSocialiteUser.php) to your `app/Actions/Socialstream` directory and add the following to your `app\Providers\SocialstreamServiceProvider.php`:

```php
use App\Actions\Socialstream\ResolveSocialiteUser;
use JoelButcher\Socialstream\Socialstream;

// Add this to the 'boot' method.
Socialstream::resolvesSocialiteUsersUsing(ResolveSocialiteUser::class);
```

## Upgrading From Socialstream 1.x To Socialstream 2.x

### Changes

#### Connected Account Details

Version 2.x of Socialstream now captures more user data from a provider and saves them to your `connected_accounts` table. In order to correctly save this data, you will need to create a new migration to make the appropriate changes.

To do this, you should create a new `connected_accounts` migration:

```sh
php artisan make:migration update_connected_accounts_table --table=connected_accounts
```

The geneated migration should contain the following code:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateConnectedAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('connected_accounts', function (Blueprint $table) {
            $table->string('name')->after('provider_name')->nullable();
            $table->string('nickname')->after('name')->nullable();
            $table->string('email')->after('nickname')->nullable();
            $table->string('telephone')->after('email')->nullable();
            $table->string('avatar_path')->after('telephone')->nullable();

            $table->renameColumn('provider_name', 'provider');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('connected_accounts', function (Blueprint $table) {
            // Revert...
        });
    }
}

```

#### Current Connected Account Context

When you login using a social provider, Socialstream will now set the context for the most-recent, or "current" provider being used. To do this, a new `current_connected_account_id` column will need adding to your users table.

Generate a new `users` migration:

```sh
php artisan make:migration update_users_table --table=users
```

The migration should be popuplated with the following content:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('current_connected_account_id')->after('current_team_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
```

#### Create Connected Account Action

Socialstream 2.x adds a new action for creating connected accounts on registration with a provider. You should copy the new [CreateConnectedAccount](https://github.com/joelbutcher/socialstream/blob/2.x/stubs/app/Actions/Socialstream/CreateConnectedAccount.php) action to the `App/Actions/Socialstream` directory in your project.

You should then add the register the action with Socialstream by placing the following code into the `boot` method of your application's `SocialstreamServiceProvider`:

```php
use App\Actions\Socialstream\CreateConnectedAccount;

Socialstream::createConnectedAccountsUsing(CreateConnectedAccount::class);
```

#### Generate Provider Redirect Action

Socialstream 2.x includes a new action to generate the redirects URI's used to authenticate with providers. 

You should then register this action with Socialstream by placing the following code into the `boot` method of your application's `SocialstreamServiceProvider`:

```php
use JoelButcher\Socialstream\Actions\GenerateRedirectForProvider;

Socialstream::generatesProvidersRedirectsUsing(GenerateRedirectForProvider::class);
```

If you wish, you may override this action by writing your own. This may allow you to define `scopes` or additional parameters, such as a `response_type` for explicit grants. See below for an example.

> Note: the action **MUST** implement the `JoelButcher\Socialstream\Contracts\GeneratesProviderRedirect` contract.

```php
<?php

namespace App\Actions\Socialstream;

use JoelButcher\Socialstream\Contracts\GeneratesProviderRedirect;
use Laravel\Socialite\Facades\Socialite;

class GenerateRedirectForProvider implements GeneratesProviderRedirect
{
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
}
```

### Connected Account Credentials

For convenience, Socialstream now also provides a `Credentials` helper class which can be used for authenticating with additional provider API's (e.g. Facebook's Graph API).

You may retrieve an instance of this class from a connected account:

```php
$connectedAccount = \App\Models\ConnectedAccount::first();

$credentials = $connectedAccount->getCredentials();
```

### Inertia Stack


#### Authentication Views

To upgrade your application's authentication views to use the new Vue files from Jetstream 2.x, you should copy the [Jetstream auth files](https://github.com/laravel/jetstream/tree/2.x/stubs/inertia/resources/js/Pages/Auth), then the [Socialstream auth files](https://github.com/joelbutcher/socialstream/tree/2.x/stubs/inertia/resources/js/Pages/Auth) to the `resources/js/Pages/Auth` folder location. 

You will also need to copy the [Providers.vue](https://github.com/joelbutcher/socialstream/blob/2.x/stubs/inertia/resources/js/Socialstream/Providers.vue) file to the `resources/js/Socialstream` directory.

However, if you wish to continue to render the Blade based authentication views, you should add the following code to the `boot` method of your application's `JetstreamServiceProvider` class:

```php
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Fortify;

Fortify::loginView(function () {
    return view('auth/login', [
        'canResetPassword' => Route::has('password.request'),
        'status' => session('status'),
    ]);
});

Fortify::requestPasswordResetLinkView(function () {
    return view('auth/forgot-password', [
        'status' => session('status'),
    ]);
});

Fortify::resetPasswordView(function (Request $request) {
    return view('auth/reset-password', [
        'email' => $request->input('email'),
        'token' => $request->route('token'),
    ]);
});

Fortify::registerView(function () {
    return view('auth/register');
});

Fortify::verifyEmailView(function () {
    return view('auth/verify-email', [
        'status' => session('status'),
    ]);
});

Fortify::twoFactorChallengeView(function () {
    return view('auth/two-factor-challenge');
});

Fortify::confirmPasswordView(function () {
    return view('auth/confirm-password');
});
```

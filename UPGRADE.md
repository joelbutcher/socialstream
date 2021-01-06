# Upgrade Guide

## Upgrading From Socialstream 1.x To Socialstream 2.x

### Changes

#### Connected Account Details

Version 2.x of Socialstream now captures more user data from a provider and saves them to your `connected_accounts` table. In order to correctly save this data, you will need to create a new migration to make the appropriate changes.

> Note: Socialstream now requires the `doctrine/dbal` package for renaming columns.

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

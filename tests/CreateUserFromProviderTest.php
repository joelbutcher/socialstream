<?php

namespace JoelButcher\Socialstream\Tests;

use App\Actions\Socialstream\CreateConnectedAccount;
use App\Actions\Socialstream\CreateUserFromProvider;
use App\Models\ConnectedAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\One\User as OAuth1User;
use Laravel\Socialite\Two\User as OAuth2User;

class CreateUserFromProviderTest extends TestCase
{
    public function test_user_can_be_created_from_o_auth_1_provider()
    {
        $this->migrate();

        $providerUser = new OAuth1User;
        $providerUser->id = '1234567890';
        $providerUser->name = 'Joel Butcher';
        $providerUser->email = 'joel@socialstream.com';
        $providerUser->token = Str::random(64);

        $action = new CreateUserFromProvider(new CreateConnectedAccount);

        $user = $action->create('github', $providerUser);

        $this->assertEquals($providerUser->email, $user->email);
        $this->assertInstanceOf(ConnectedAccount::class, $user->currentConnectedAccount);
    }

    public function test_user_can_be_created_from_o_auth_2_provider()
    {
        $this->migrate();

        $providerUser = new OAuth2User;
        $providerUser->id = '1234567890';
        $providerUser->name = 'Joel Butcher';
        $providerUser->email = 'joel@socialstream.com';
        $providerUser->token = Str::random(64);

        $action = new CreateUserFromProvider(new CreateConnectedAccount);

        $user = $action->create('github', $providerUser);
        $user->fresh();

        $this->assertEquals($providerUser->email, $user->email);
        $this->assertInstanceOf(ConnectedAccount::class, $user->currentConnectedAccount);
    }

    protected function migrate()
    {
        $this->artisan('migrate', ['--database' => 'testbench'])->run();
    }
}

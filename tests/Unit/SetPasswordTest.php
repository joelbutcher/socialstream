<?php

namespace JoelButcher\Socialstream\Tests\Unit;

use App\Actions\Socialstream\SetUserPassword;
use Illuminate\Support\Facades\Hash;
use JoelButcher\Socialstream\Tests\Fixtures\User;
use JoelButcher\Socialstream\Tests\TestCase;

class SetPasswordTest extends TestCase
{
    public function test_users_password_can_be_set(): void
    {
        $this->migrate();

        $user = $this->createUser();

        $action = new SetUserPassword;

        $action->set($user, [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $this->assertTrue(Hash::check('new-password', $user->password));
    }

    public function createUser()
    {
        return User::forceCreate([
            'name' => 'Joel Butcher',
            'email' => 'joel@socialstream.com',
            'password' => 'secret',
        ]);
    }

    protected function migrate()
    {
        $this->artisan('migrate', ['--database' => 'testbench'])->run();
    }
}

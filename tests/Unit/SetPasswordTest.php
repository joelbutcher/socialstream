<?php

namespace JoelButcher\Socialstream\Tests\Unit;

use App\Actions\Socialstream\SetUserPassword;
use Illuminate\Support\Facades\Hash;
use JoelButcher\Socialstream\Tests\Fixtures\User;
use JoelButcher\Socialstream\Tests\TestCase;

test('users password can be set', function (): void {
    $this->migrate();

    $user = User::forceCreate([
        'name' => 'Joel Butcher',
        'email' => 'joel@socialstream.com',
        'password' => 'secret',
    ]);

    $action = new SetUserPassword;

    $action->set($user, [
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $this->assertTrue(Hash::check('new-password', $user->password));
});

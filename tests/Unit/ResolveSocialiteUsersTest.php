<?php

namespace JoelButcher\Socialstream\Tests\Unit;

use JoelButcher\Socialstream\Actions\ResolveSocialiteUser;
use JoelButcher\Socialstream\Contracts\ResolvesSocialiteUsers;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Socialite\Contracts\User;

test('the action can be overridden', function (): void {
    expect($this->app->make(ResolvesSocialiteUsers::class))
        ->toBeInstanceOf(ResolveSocialiteUser::class);

    Socialstream::resolvesSocialiteUsersUsing(ResolverOverride::class);

    expect($this->app->make(ResolvesSocialiteUsers::class))
        ->toBeInstanceOf(ResolverOverride::class);
});

class ResolverOverride implements ResolvesSocialiteUsers
{
    public function resolve(string $provider): User
    {
        // TODO: Implement resolve() method.
    }
}

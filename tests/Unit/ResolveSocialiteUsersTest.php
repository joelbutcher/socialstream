<?php

namespace JoelButcher\Socialstream\Tests\Unit;

use JoelButcher\Socialstream\Contracts\ResolvesSocialiteUsers;
use JoelButcher\Socialstream\Socialstream;
use JoelButcher\Socialstream\Tests\Fixtures\ResolveUser;

beforeEach(fn () => Socialstream::resolvesSocialiteUsersUsing(ResolveUser::class));

test('the action can be overridden', function (): void {
    $action = app(ResolvesSocialiteUsers::class);

    $this->assertInstanceOf(ResolveUser::class, $action);
});

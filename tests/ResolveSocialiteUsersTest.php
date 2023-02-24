<?php

namespace JoelButcher\Socialstream\Tests;

use JoelButcher\Socialstream\Contracts\ResolvesSocialiteUsers;
use JoelButcher\Socialstream\Socialstream;
use JoelButcher\Socialstream\Tests\Fixtures\ResolveUser;

class ResolveSocialiteUsersTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Socialstream::resolvesSocialiteUsersUsing(ResolveUser::class);
    }

    public function test_action_can_be_overridden()
    {
        $action = app(ResolvesSocialiteUsers::class);

        $this->assertInstanceOf(ResolveUser::class, $action);
    }
}

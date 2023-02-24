<?php

namespace JoelButcher\Socialstream\Tests\Unit;

use JoelButcher\Socialstream\Contracts\ResolvesSocialiteUsers;
use JoelButcher\Socialstream\Socialstream;
use JoelButcher\Socialstream\Tests\Fixtures\ResolveUser;
use JoelButcher\Socialstream\Tests\TestCase;

class ResolveSocialiteUsersTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Socialstream::resolvesSocialiteUsersUsing(ResolveUser::class);
    }

    public function test_action_can_be_overridden(): void
    {
        $action = app(ResolvesSocialiteUsers::class);

        $this->assertInstanceOf(ResolveUser::class, $action);
    }
}

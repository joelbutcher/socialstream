<?php

namespace JoelButcher\Socialstream\Tests\Fixtures;

use Illuminate\Foundation\Auth\User as Authenticatable;
use JoelButcher\Socialstream\HasConnectedAccounts;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasTeams, HasConnectedAccounts;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
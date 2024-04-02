<?php

namespace JoelButcher\Socialstream\Tests\Fixtures;

use App\Models\User as BaseUser;
use JoelButcher\Socialstream\HasConnectedAccounts;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;

class User extends BaseUser
{
    use HasApiTokens;
    use HasConnectedAccounts;
    use HasTeams;
    use HasProfilePhoto;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}

<?php

namespace JoelButcher\Socialstream\Tests\Fixtures;

use App\Models\User as BaseUser;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;

class User extends BaseUser
{
    use HasApiTokens, HasProfilePhoto, HasTeams;

    protected $guarded = [];

    protected $fillable = [];
}

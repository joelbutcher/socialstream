<?php

namespace JoelButcher\Socialstream\Tests\Fixtures;

use App\Models\User as BaseUser;
use Laravel\Sanctum\HasApiTokens;

class User extends BaseUser
{
    use HasApiTokens;

    protected $guarded = [];

    protected $fillable = [];
}

<?php

declare(strict_types=1);

namespace JoelButcher\Socialstream\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

class SocialstreamUserProvider extends EloquentUserProvider
{
    public function validateCredentials(UserContract $user, #[\SensitiveParameter] array $credentials): bool
    {
        if (is_null($user->getAuthPassword())) {
            return false;
        }

        return parent::validateCredentials($user, $credentials);
    }
}

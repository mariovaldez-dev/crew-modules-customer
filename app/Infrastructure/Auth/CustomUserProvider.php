<?php

namespace App\Infrastructure\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

class CustomUserProvider implements UserProvider
{
    public function retrieveById($identifier)
    {
        return session()->get('authenticated_user');
    }

    public function retrieveByToken($identifier, $token)
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token) {}

    public function retrieveByCredentials(array $credentials)
    {
        return null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return false;
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
        // No local password hashing to rehash
    }
}

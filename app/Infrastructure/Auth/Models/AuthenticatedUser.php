<?php

namespace App\Infrastructure\Auth\Models;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $rol
 * @property string|null $sessionId
 */
class AuthenticatedUser implements Authenticatable
{
    public function __construct(
        private array $attributes
    ) {}

    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return $this->attributes['id'];
    }

    public function getAuthPassword()
    {
        return '';
    }

    public function getAuthPasswordName()
    {
        return 'password';
    }

    public function getRememberToken()
    {
        return '';
    }

    public function setRememberToken($value) {}

    public function getRememberTokenName()
    {
        return '';
    }

    public function __get($name)
    {
        return $this->attributes[$name] ?? null;
    }
}

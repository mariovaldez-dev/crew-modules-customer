<?php

namespace App\Domain\Auth\Repositories;

use App\Domain\Auth\Entities\User;

interface IAuthRepository
{
    public function authenticate(string $username, string $password, string $platform = 'WEB'): ?User;

    /**
     * Cerrar sesión en API externa
     */
    public function logout(string $sessionId): bool;

    /**
     * Registrar intento de login (RN-05)
     */
    public function logAttempt(string $username, bool $success, string $ip, string $platform = 'WEB'): void;
}

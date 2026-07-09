<?php

namespace App\Domain\Auth\Entities;

final class User
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $rol,
        public readonly bool $activo = true,
        public readonly ?string $sessionId = null
    ) {}

    public function isAsesorAgronomo(): bool
    {
        return $this->rol === '2';
    }
}

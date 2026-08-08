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
        public readonly ?string $sessionId = null,
        public readonly ?string $zona = null,
        public readonly ?string $zonaNombre = null
    ) {}

    public function isAsesorAgronomo(): bool
    {
        return $this->rol === '2';
    }
}

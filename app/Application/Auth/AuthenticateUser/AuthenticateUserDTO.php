<?php

namespace App\Application\Auth\AuthenticateUser;

final class AuthenticateUserDTO
{
    public function __construct(
        public readonly string $username,
        public readonly string $password,
        public readonly string $ip,
        public readonly string $platform = 'WEB'
    ) {}

    public static function fromRequest(array $data, string $ip, string $platform = 'WEB'): self
    {
        return new self(
            username: $data['username'],
            password: $data['password'],
            ip: $ip,
            platform: $platform
        );
    }
}

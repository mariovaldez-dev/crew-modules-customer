<?php

namespace App\Application\Auth\AuthenticateUser;

use App\Domain\Auth\Entities\User;
use App\Domain\Auth\Repositories\IAuthRepository;
use Exception;
use Illuminate\Support\Facades\Log;

final class AuthenticateUserUseCase
{
    public function __construct(
        private readonly IAuthRepository $repository
    ) {}

    /**
     * @throws Exception
     */
    public function execute(AuthenticateUserDTO $dto): ?User
    {
        try {
            $user = $this->repository->authenticate($dto->username, $dto->password, $dto->platform);

            $success = $user !== null;
            $this->repository->logAttempt($dto->username, $success, $dto->ip, $dto->platform);

            Log::withContext([
                'usuario' => $dto->username,
                'accion' => 'AuthenticateUser',
                'modulo' => 'auth',
                'resultado' => $success ? 'success' : 'failed',
                'plataforma' => $dto->platform,
            ])->info($success ? 'Login exitoso' : 'Login fallido');

            return $user;

        } catch (Exception $e) {
            Log::error('Error en AuthenticateUserUseCase', [
                'exception' => $e->getMessage(),
                'username' => $dto->username,
            ]);
            throw $e;
        }
    }
}

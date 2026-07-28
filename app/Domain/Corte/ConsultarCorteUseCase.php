<?php

namespace App\Domain\Corte;

class ConsultarCorteUseCase
{
    public function __construct(private CorteRepositoryInterface $repository)
    {
    }

    public function execute(int $corteId): ?array
    {
        $result = $this->repository->consultarCortePorId($corteId);
        if ($result['estatus'] === 0 && !empty($result['resultado'])) {
            return $result['resultado'];
        }
        
        return null;
    }
}

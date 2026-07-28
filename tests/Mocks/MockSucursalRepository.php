<?php

namespace Tests\Mocks;

use App\Domain\Shared\Repositories\SucursalRepositoryInterface;

class MockSucursalRepository implements SucursalRepositoryInterface
{
    public function listaPuntosDeVentaPorZona(string $zona): array
    {
        return match (strtoupper($zona)) {
            'FA' => [
                'ANGOS01' => 'Angostura 01',
                'ANGOS02' => 'Angostura 02',
                'ANGOS03' => 'Angostura 03',
            ],
            'ZONA-NORTE' => [
                '101' => 'PV Norte Principal',
                '103' => 'PV Norte Auxiliar',
            ],
            'ZONA-SUR' => [
                '102' => 'PV Sur Principal',
                '104' => 'PV Sur Auxiliar',
            ],
            default => [
                'ANGOS01' => 'Angostura 01',
                'ANGOS02' => 'Angostura 02',
            ],
        };
    }

    public function listaLideresPorZona(string $zona): array
    {
        return [
            'Líder 1' => 'Líder 1',
            'Líder 2' => 'Líder 2',
        ];
    }
}

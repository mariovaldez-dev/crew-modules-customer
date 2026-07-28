<?php

namespace Tests\Mocks;

use App\Domain\Cuadrilla\TarifaAuditRepositoryInterface;
use App\Domain\Cuadrilla\TarifaCambioDTO;
use Illuminate\Support\Facades\Cache;

class MockTarifaAuditRepository implements TarifaAuditRepositoryInterface
{
    public function record(TarifaCambioDTO $cambio): void
    {
        $log = Cache::get('mock_tarifa_audit', []);
        
        $log[] = [
            'cuadrillaId' => $cambio->cuadrillaId,
            'concepto' => $cambio->concepto,
            'precioAnterior' => $cambio->precioAnterior,
            'precioNuevo' => $cambio->precioNuevo,
            'usuario' => $cambio->usuario,
            'fecha' => $cambio->fecha->format('Y-m-d H:i:s')
        ];

        Cache::put('mock_tarifa_audit', $log, 3600);
    }
}

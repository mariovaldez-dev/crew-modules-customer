<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Cuadrilla\TarifaAuditRepositoryInterface;
use App\Domain\Cuadrilla\TarifaCambioDTO;
use Illuminate\Support\Facades\Log;

class TarifaAuditRepository implements TarifaAuditRepositoryInterface
{
    public function record(TarifaCambioDTO $cambio): void
    {
        Log::info('[TarifaAuditRepository] Auditoría de cambio de tarifa', [
            'cuadrillaId' => $cambio->cuadrillaId,
            'concepto' => $cambio->concepto,
            'precioAnterior' => $cambio->precioAnterior,
            'precioNuevo' => $cambio->precioNuevo,
            'usuario' => $cambio->usuario,
            'fecha' => $cambio->fecha->format('Y-m-d H:i:s')
        ]);
    }
}

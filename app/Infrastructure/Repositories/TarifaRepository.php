<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Tarifa\TarifaRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TarifaRepository implements TarifaRepositoryInterface
{
    public function consultarTarifas(?string $claveZona): array
    {
        $cacheKey = "tarifas_zona_" . ($claveZona ?: 'TODAS');

        return Cache::remember($cacheKey, 3600, function () use ($claveZona) {
            try {
                Log::info("CONSULTA REAL A BD (SP): proc_pdm_consultar_tarifas", ['zona' => $claveZona]);
                
                DB::connection('maniobras')->statement("SET ANSI_NULLS ON");
                DB::connection('maniobras')->statement("SET ANSI_WARNINGS ON");

                if ($claveZona) {
                    $resultados = DB::connection('maniobras')->select('EXEC proc_pdm_consultar_tarifas ?', [$claveZona]);
                } else {
                    $resultados = DB::connection('maniobras')->select('EXEC proc_pdm_consultar_tarifas');
                }

                if (empty($resultados)) {
                    return [];
                }

                $response = $resultados[0];

                if (!isset($response->estado) || (int) $response->estado !== 0) {
                    return [];
                }

                $rawLista = $response->listaCuadrillas ?? $response->listacuadrillas ?? $response->LISTACUADRILLAS ?? null;

                if (empty($rawLista)) {
                    return [];
                }

                $datos = is_string($rawLista) ? json_decode($rawLista, true) : $rawLista;
                return is_array($datos) ? $datos : [];
            } catch (\Throwable $e) {
                Log::error('[TarifaRepository@consultarTarifas] Error SQL: ' . $e->getMessage());
                return [];
            }
        });
    }
}

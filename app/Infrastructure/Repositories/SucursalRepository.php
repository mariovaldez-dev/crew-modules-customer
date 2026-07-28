<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Shared\Repositories\SucursalRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class SucursalRepository implements SucursalRepositoryInterface
{
    public function listaPuntosDeVentaPorZona(string $zona): array
    {
        Log::debug('[SucursalRepo::listaPuntosDeVentaPorZona] INICIO', ['zona' => $zona]);

        try {
            DB::connection('maniobras')->statement("SET ANSI_NULLS ON");
            DB::connection('maniobras')->statement("SET ANSI_WARNINGS ON");
            $paramZona = ($zona === 'TODAS') ? '' : $zona;
            $response = DB::connection('maniobras')->select(
                "EXEC proc_pdm_cosultar_combos 1, ?",
                [$paramZona]
            );

            if (empty($response)) {
                return [];
            }

            $result = $response[0];

            if ($result->estado !== 0) {
                throw new Exception($result->mensaje);
            }

            $pvsList = $result->combo ?? null;

            if (is_string($pvsList)) {
                $pvsList = json_decode($pvsList, true);
            }

            if (empty($pvsList)) {
                $pvsList = $response;
            }

            Log::debug('[SucursalRepo::listaPuntosDeVentaPorZona] Resultados crudos del SP', [
                'zona'        => $zona,
                'total_filas' => count($pvsList),
                'muestra'     => array_slice(array_map(fn($r) => (array) $r, $pvsList), 0, 3),
            ]);

            $mapped = [];
            foreach ($pvsList as $item) {
                $item   = is_array($item) ? $item : (array) $item;
                $codigo = trim($item['codigo'] ?? '');
                $nombre = trim($item['nombre'] ?? '');

                if ($codigo !== '') {
                    $mapped[$codigo] = $nombre;
                }
            }

            Log::debug('[SucursalRepo::listaPuntosDeVentaPorZona] Mapeado final', [
                'zona'  => $zona,
                'count' => count($mapped),
                'pvs'   => $mapped,
            ]);

            return $mapped;
        } catch (\Throwable $e) {
            Log::error('Error en SucursalRepository@listaPuntosDeVentaPorZona', [
                'zona'  => $zona,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    public function listaLideresPorZona(string $zona): array
    {
        Log::debug('[SucursalRepo::listaLideresPorZona] INICIO', ['zona' => $zona]);

        try {
            DB::connection('maniobras')->statement("SET ANSI_NULLS ON");
            DB::connection('maniobras')->statement("SET ANSI_WARNINGS ON");

            $query = "EXEC proc_pdm_cosultar_combos 4";
            $bindings = [];
            
            if ($zona !== '' && $zona !== 'TODAS') {
                $query .= ", ?";
                $bindings[] = $zona;
            }

            $response = DB::connection('maniobras')->select($query, $bindings);

            if (empty($response)) {
                return [];
            }

            $result = $response[0];

            if (isset($result->estado) && $result->estado !== 0) {
                throw new Exception($result->mensaje ?? 'Error desconocido del SP');
            }

            $comboData = $result->combo ?? null;

            if (is_string($comboData)) {
                $comboData = json_decode($comboData, true);
            }

            if (empty($comboData)) {
                $comboData = $response;
            }

            $mapped = [];
            foreach ($comboData as $item) {
                $item   = is_array($item) ? $item : (array) $item;
                $nombre = trim($item['nombre'] ?? '');

                if ($nombre !== '') {
                    $mapped[$nombre] = $nombre;
                }
            }

            return $mapped;
        } catch (\Throwable $e) {
            Log::error('Error en SucursalRepository@listaLideresPorZona', [
                'zona'  => $zona,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }
}

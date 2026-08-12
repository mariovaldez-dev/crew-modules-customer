<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Shared\Repositories\SucursalRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class SucursalRepository implements SucursalRepositoryInterface
{
    public function listaPuntosDeVentaPorZona(string $zona): array
    {
        return Cache::remember("pvs_zona_{$zona}", 300, function () use ($zona) {
            Log::debug('[SucursalRepo::listaPuntosDeVentaPorZona] INICIO CONSULTA SP', ['zona' => $zona]);

            try {
                $paramZona = ($zona === 'TODAS') ? '' : $zona;
                $response = DB::connection('maniobras')->select(
                    "SET NOCOUNT ON; SET ANSI_NULLS ON; SET ANSI_WARNINGS ON; EXEC proc_pdm_cosultar_combos 1, ?",
                    [$paramZona]
                );

                if (empty($response)) {
                    return [];
                }

                $result = $response[0];

                if (isset($result->estado) && (int) $result->estado !== 0) {
                    throw new Exception($result->mensaje ?? 'Error del SP');
                }

                $pvsList = $result->combo ?? null;

                if (is_string($pvsList)) {
                    $pvsList = json_decode($pvsList, true);
                }

                if (empty($pvsList)) {
                    $pvsList = $response;
                }

                $mapped = [];
                foreach ($pvsList as $item) {
                    $item   = is_array($item) ? $item : (array) $item;
                    $codigo = trim($item['codigo'] ?? '');
                    $nombre = trim($item['nombre'] ?? '');

                    if ($codigo !== '') {
                        $mapped[$codigo] = $nombre;
                    }
                }

                return $mapped;
            } catch (\Throwable $e) {
                Log::error('Error en SucursalRepository@listaPuntosDeVentaPorZona', [
                    'zona'  => $zona,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return [];
            }
        });
    }

    public function listaLideresPorZona(string $zona): array
    {
        return Cache::remember("lideres_zona_{$zona}", 300, function () use ($zona) {
            Log::debug('[SucursalRepo::listaLideresPorZona] INICIO CONSULTA SP', ['zona' => $zona]);

            try {
                $query = "SET NOCOUNT ON; SET ANSI_NULLS ON; SET ANSI_WARNINGS ON; EXEC proc_pdm_cosultar_combos 4";
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

                if (isset($result->estado) && (int) $result->estado !== 0) {
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
        });
    }
}

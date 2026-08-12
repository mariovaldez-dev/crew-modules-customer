<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Shared\Repositories\SucursalRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SucursalRepository implements SucursalRepositoryInterface
{
    public function listaPuntosDeVentaPorZona(string $zona): array
    {
        return Cache::remember("pvs_zona_{$zona}", 3600, function () use ($zona) {
            Log::debug('[SucursalRepo::listaPuntosDeVentaPorZona] INICIO CONSULTA SP', ['zona' => $zona]);

            try {
                $paramZona = ($zona === 'TODAS') ? '' : $zona;

                DB::connection('maniobras')->statement("SET ANSI_NULLS ON");
                DB::connection('maniobras')->statement("SET ANSI_WARNINGS ON");

                $results = DB::connection('maniobras')->select(
                    "EXEC proc_pdm_cosultar_combos 1, ?",
                    [$paramZona]
                );

                if (empty($results)) {
                    return [];
                }

                $response = $results[0];

                if (!isset($response->estado) || (int) $response->estado !== 0) {
                    return [];
                }

                if (empty($response->combo)) {
                    return [];
                }

                $comboData = is_string($response->combo) ? json_decode($response->combo, true) : $response->combo;
                if (!is_array($comboData)) {
                    return [];
                }

                $mapped = [];
                foreach ($comboData as $item) {
                    $item   = (array) $item;
                    $codigo = trim($item['codigo'] ?? $item['CODIGO'] ?? '');
                    $nombre = trim($item['nombre'] ?? $item['NOMBRE'] ?? '');

                    if ($codigo !== '') {
                        $mapped[$codigo] = $nombre;
                    }
                }

                return $mapped;
            } catch (\Throwable $e) {
                Log::error('Error en SucursalRepository@listaPuntosDeVentaPorZona', [
                    'zona'  => $zona,
                    'error' => $e->getMessage(),
                ]);
                return [];
            }
        });
    }

    public function listaLideresPorZona(string $zona): array
    {
        return Cache::remember("lideres_zona_{$zona}", 3600, function () use ($zona) {
            Log::debug('[SucursalRepo::listaLideresPorZona] INICIO CONSULTA SP', ['zona' => $zona]);

            try {
                $paramZona = ($zona === 'TODAS') ? '' : $zona;

                DB::connection('maniobras')->statement("SET ANSI_NULLS ON");
                DB::connection('maniobras')->statement("SET ANSI_WARNINGS ON");

                $results = DB::connection('maniobras')->select(
                    "EXEC proc_pdm_cosultar_combos 4, ?",
                    [$paramZona]
                );

                if (empty($results)) {
                    return [];
                }

                $response = $results[0];

                if (!isset($response->estado) || (int) $response->estado !== 0) {
                    return [];
                }

                if (empty($response->combo)) {
                    return [];
                }

                $comboData = is_string($response->combo) ? json_decode($response->combo, true) : $response->combo;
                if (!is_array($comboData)) {
                    return [];
                }

                $mapped = [];
                foreach ($comboData as $item) {
                    $item   = (array) $item;
                    $nombre = trim($item['nombre'] ?? $item['NOMBRE'] ?? '');

                    if ($nombre !== '') {
                        $mapped[$nombre] = $nombre;
                    }
                }

                return $mapped;
            } catch (\Throwable $e) {
                Log::error('Error en SucursalRepository@listaLideresPorZona', [
                    'zona'  => $zona,
                    'error' => $e->getMessage(),
                ]);
                return [];
            }
        });
    }
}

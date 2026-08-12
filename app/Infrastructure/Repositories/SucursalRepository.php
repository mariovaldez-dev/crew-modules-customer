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
                    Log::warning('[SucursalRepo::listaPuntosDeVentaPorZona] Respuesta vacía de DB');
                    return [];
                }

                Log::info('[SucursalRepo::listaPuntosDeVentaPorZona] Respuesta cruda del SP', ['response' => $response]);

                $rawFirst = (array) $response[0];
                $first = [];
                foreach ($rawFirst as $k => $v) {
                    $first[strtolower($k)] = $v;
                }

                if (isset($first['estado']) && (int) $first['estado'] !== 0) {
                    Log::warning('[SucursalRepo::listaPuntosDeVentaPorZona] SP devolvió estado distinto de 0', ['row' => $first]);
                    return [];
                }

                $comboData = $first['combo'] ?? null;
                if (is_string($comboData)) {
                    $comboData = json_decode($comboData, true);
                }

                if (empty($comboData) || !is_array($comboData)) {
                    $comboData = $response;
                }

                $mapped = [];
                foreach ($comboData as $item) {
                    $item   = is_array($item) ? $item : (array) $item;
                    $codigo = trim($item['codigo'] ?? $item['CODIGO'] ?? '');
                    $nombre = trim($item['nombre'] ?? $item['NOMBRE'] ?? '');

                    if ($codigo !== '') {
                        $mapped[$codigo] = $nombre;
                    }
                }

                Log::info('[SucursalRepo::listaPuntosDeVentaPorZona] Puntos de venta mapeados exitosamente', ['count' => count($mapped)]);
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
                    Log::warning('[SucursalRepo::listaLideresPorZona] Respuesta vacía de DB');
                    return [];
                }

                Log::info('[SucursalRepo::listaLideresPorZona] Respuesta cruda del SP', ['response' => $response]);

                $rawFirst = (array) $response[0];
                $first = [];
                foreach ($rawFirst as $k => $v) {
                    $first[strtolower($k)] = $v;
                }

                if (isset($first['estado']) && (int) $first['estado'] !== 0) {
                    Log::warning('[SucursalRepo::listaLideresPorZona] SP devolvió estado distinto de 0', ['row' => $first]);
                    return [];
                }

                $comboData = $first['combo'] ?? null;
                if (is_string($comboData)) {
                    $comboData = json_decode($comboData, true);
                }

                if (empty($comboData) || !is_array($comboData)) {
                    $comboData = $response;
                }

                $mapped = [];
                foreach ($comboData as $item) {
                    $item   = is_array($item) ? $item : (array) $item;
                    $nombre = trim($item['nombre'] ?? $item['NOMBRE'] ?? '');

                    if ($nombre !== '') {
                        $mapped[$nombre] = $nombre;
                    }
                }

                Log::info('[SucursalRepo::listaLideresPorZona] Líderes mapeados exitosamente', ['count' => count($mapped)]);
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

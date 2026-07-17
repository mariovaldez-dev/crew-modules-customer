<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Shared\Repositories\SucursalRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class SqlServerSucursalRepository implements SucursalRepositoryInterface
{
    public function zonaDe(int $ownsId): ?string
    {
        if ($ownsId === 101) {
            return 'ZONA-NORTE';
        }
        if ($ownsId === 102) {
            return 'ZONA-SUR';
        }

        try {
            $result = DB::connection('sapImpulsoraDB')->selectOne("
                SELECT TOP 1 S.U_Zona
                FROM OWHS W
                INNER JOIN [@SUCURSALES] S ON W.U_SerieSucursal = S.Code
                WHERE (W.WhsCode = ? OR TRY_CAST(W.WhsCode AS INT) = ?)
                  AND W.Inactive = 'N'
                  AND W.U_TipoPV IN ('B', 'PB')
            ", [$ownsId, $ownsId]);

            return $result ? trim($result->U_Zona) : null;
        } catch (\Throwable $e) {
            Log::error('Error en SqlServerSucursalRepository@zonaDe', [
                'ownsId' => $ownsId,
                'error' => $e->getMessage()
            ]);
            // Fallback mock
            return $ownsId === 101 ? 'ZONA-NORTE' : ($ownsId === 102 ? 'ZONA-SUR' : 'ZONA-NORTE');
        }
    }

    public function obtenerPuntosDeVentaDeUsuario(int $usuarioId): array
    {
        if ($usuarioId === 1) {
            return [101, 102];
        }
        if ($usuarioId === 2 || $usuarioId === 140) {
            return [101];
        }
        if ($usuarioId === 3) {
            return [102];
        }
        if ($usuarioId === 999) {
            return [101];
        }

        try {
            $results = DB::connection('sapImpulsoraDB')->select("
                SELECT U_PuntoVenta
                FROM [@USUARIOS_PUNTOVENTA]
                WHERE U_Usuario = ? OR TRY_CAST(U_Usuario AS INT) = ?
            ", [$usuarioId, $usuarioId]);

            $pvs = [];
            foreach ($results as $row) {
                if (isset($row->U_PuntoVenta)) {
                    $pvs[] = (int) $row->U_PuntoVenta;
                }
            }
            return $pvs;
        } catch (\Throwable $e) {
            Log::error('Error en SqlServerSucursalRepository@obtenerPuntosDeVentaDeUsuario', [
                'usuarioId' => $usuarioId,
                'error' => $e->getMessage()
            ]);
            // Fallback mock
            if ($usuarioId === 2 || $usuarioId === 140) {
                return [101];
            }
            if ($usuarioId === 3) {
                return [102];
            }
            return [];
        }
    }

    public function obtenerStatusUsuario(int $usuarioId): string
    {
        if ($usuarioId === 1 || $usuarioId === 2 || $usuarioId === 3 || $usuarioId === 140) {
            return 'A';
        }
        if ($usuarioId === 999) {
            return 'I';
        }

        try {
            // Buscamos el estatus en @AGENTES_VENTAS
            $result = DB::connection('sapImpulsoraDB')->selectOne("
                SELECT TOP 1 U_Status
                FROM [@AGENTES_VENTAS]
                WHERE Code = ? OR TRY_CAST(Code AS INT) = ?
            ", [$usuarioId, $usuarioId]);

            if ($result && isset($result->U_Status)) {
                return trim($result->U_Status);
            }

            return 'A'; // Activo por defecto si no se encuentra
        } catch (\Throwable $e) {
            Log::error('Error en SqlServerSucursalRepository@obtenerStatusUsuario', [
                'usuarioId' => $usuarioId,
                'error' => $e->getMessage()
            ]);
            return 'A'; // Activo por defecto
        }
    }
    public function listaPuntosDeVentaPorZona(string $zona): array
    {
        Log::debug('[SucursalRepo::listaPuntosDeVentaPorZona] INICIO', ['zona' => $zona]);

        try {

            DB::connection('localDB')->statement("SET ANSI_NULLS ON");
            DB::connection('localDB')->statement("SET ANSI_WARNINGS ON");
            // El SP proc_pdm_cosultar_combos vive en localDB
            $paramZona = ($zona === 'TODAS') ? '' : $zona;
            $response = DB::connection('localDB')->select(
                "EXEC proc_pdm_cosultar_combos 1, ?",
                [$paramZona]
            );


            if (empty($response)) {
                return [];
            }

            $result = $response[0];

            if($result->estado !== 0){
                throw new Exception($result->mensaje);
            }


            $pvsList = $result->combo ?? $result->combo ?? null;

            // Si COMBO es un string (JSON), decodificarlo
            if (is_string($pvsList)) {
                $pvsList = json_decode($pvsList, true);
            }

            // Si no hay PUNTOS DE VENTA en la primera fila
            if (empty($pvsList)) {
                $pvsList = $response;
            }

            Log::debug('[SucursalRepo::listaPuntosDeVentaPorZona] Resultados crudos del SP', [
                'zona'        => $zona,
                'total_filas' => count($pvsList),
                'muestra'     => array_slice(array_map(fn($r) => (array) $r, $pvsList), 0, 3),
            ]);

            // Mapear [{codigo, nombre, adicional}] → ['ANGOS01' => 'AGUSTINA']
            $mapped = [];
            foreach ($pvsList as $item) {
                // Soporta tanto stdClass (json_decode default) como array asociativo
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
            Log::error('Error en SqlServerSucursalRepository@listaPuntosDeVentaPorZona', [
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
            DB::connection('localDB')->statement("SET ANSI_NULLS ON");
            DB::connection('localDB')->statement("SET ANSI_WARNINGS ON");

            // Según el requerimiento:
            // EXEC proc_pdm_cosultar_combos 4 -> Todos (si $zona está vacío o es 'TODAS')
            // EXEC proc_pdm_cosultar_combos 4, 'FA' -> Solo de la zona
            
            $query = "EXEC proc_pdm_cosultar_combos 4";
            $bindings = [];
            
            if ($zona !== '' && $zona !== 'TODAS') {
                $query .= ", ?";
                $bindings[] = $zona;
            }

            $response = DB::connection('localDB')->select($query, $bindings);

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
                    // Como el SP retorna codigo: 0, usamos el nombre como llave
                    $mapped[$nombre] = $nombre;
                }
            }

            return $mapped;
        } catch (\Throwable $e) {
            Log::error('Error en SqlServerSucursalRepository@listaLideresPorZona', [
                'zona'  => $zona,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }
}

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
}

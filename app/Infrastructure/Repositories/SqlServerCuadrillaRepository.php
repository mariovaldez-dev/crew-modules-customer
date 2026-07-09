<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Cuadrilla\CuadrillaDTO;
use App\Domain\Cuadrilla\CuadrillaRepositoryInterface;
use App\Domain\Cuadrilla\TarifasManiobra;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class SqlServerCuadrillaRepository implements CuadrillaRepositoryInterface
{
    public function list(array $filtros, string $zonaUsuario): array
    {
        try {
            $results = DB::connection('localDB')->select(
                "EXEC proc_pdm_consultar_cuadrillas @Busqueda = ?, @PuntoVentaId = ?, @Zona = ?",
                [
                    $filtros['search'] ?? null,
                    $filtros['puntoVentaId'] ?? null,
                    $zonaUsuario
                ]
            );

            if (empty($results)) {
                return [];
            }

            $row = $results[0];
            if ((int) $row->estado !== 0) {
                return [];
            }

            $cuadrillasJson = json_decode($row->data, true);
            if (!is_array($cuadrillasJson)) {
                return [];
            }

            $cuadrillas = [];
            foreach ($cuadrillasJson as $item) {
                $cuadrillas[] = new CuadrillaDTO(
                    id: (int) $item['codigoCuadrilla'],
                    nombre: $item['nombreCuadrilla'],
                    lider: $item['liderCuadrilla'],
                    miembros: (int) $item['miembrosCuadrilla'],
                    puntoVentaId: (int) $item['puntoVentaId'],
                    zona: $item['zonaCuadrilla'],
                    tarifas: new TarifasManiobra(
                        carga25: isset($item['carga25kg']) ? (float) $item['carga25kg'] : null,
                        carga50: isset($item['carga50kg']) ? (float) $item['carga50kg'] : null,
                        descarga25: isset($item['descarga25kg']) ? (float) $item['descarga25kg'] : null,
                        descarga50: isset($item['descarga50kg']) ? (float) $item['descarga50kg'] : null,
                        traslado: isset($item['traslado']) ? (float) $item['traslado'] : null,
                        apaleo: isset($item['apaleo']) ? (float) $item['apaleo'] : null
                    )
                );
            }

            return $cuadrillas;
        } catch (Exception $e) {
            Log::error('Error en SqlServerCuadrillaRepository@list', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function findById(int $id): ?CuadrillaDTO
    {
        try {
            $results = DB::connection('localDB')->select(
                "EXEC proc_pdm_consultar_cuadrillas @Busqueda = NULL, @PuntoVentaId = NULL, @Zona = 'TODAS'"
            );

            if (empty($results)) {
                return null;
            }

            $row = $results[0];
            if ((int) $row->estado !== 0) {
                return null;
            }

            $cuadrillasJson = json_decode($row->data, true);
            if (!is_array($cuadrillasJson)) {
                return null;
            }

            foreach ($cuadrillasJson as $item) {
                if ((int) $item['codigoCuadrilla'] === $id) {
                    return new CuadrillaDTO(
                        id: (int) $item['codigoCuadrilla'],
                        nombre: $item['nombreCuadrilla'],
                        lider: $item['liderCuadrilla'],
                        miembros: (int) $item['miembrosCuadrilla'],
                        puntoVentaId: (int) $item['puntoVentaId'],
                        zona: $item['zonaCuadrilla'],
                        tarifas: new TarifasManiobra(
                            carga25: isset($item['carga25kg']) ? (float) $item['carga25kg'] : null,
                            carga50: isset($item['carga50kg']) ? (float) $item['carga50kg'] : null,
                            descarga25: isset($item['descarga25kg']) ? (float) $item['descarga25kg'] : null,
                            descarga50: isset($item['descarga50kg']) ? (float) $item['descarga50kg'] : null,
                            traslado: isset($item['traslado']) ? (float) $item['traslado'] : null,
                            apaleo: isset($item['apaleo']) ? (float) $item['apaleo'] : null
                        )
                    );
                }
            }

            return null;
        } catch (Exception $e) {
            Log::error('Error en SqlServerCuadrillaRepository@findById', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function create(CuadrillaDTO $cuadrilla): CuadrillaDTO
    {
        try {
            $usuarioId = auth()->user()?->id ?? 'sa';

            $results = DB::connection('localDB')->select(
                "EXEC proc_pdm_crear_cuadrilla 
                    @Nombre = ?, 
                    @Lider = ?, 
                    @Miembros = ?, 
                    @PuntoVentaId = ?, 
                    @Zona = ?, 
                    @CreadoPor = ?,
                    @Carga25kg = ?, 
                    @Carga50kg = ?, 
                    @Descarga25kg = ?, 
                    @Descarga50kg = ?, 
                    @Traslado = ?, 
                    @Apaleo = ?",
                [
                    $cuadrilla->nombre,
                    $cuadrilla->lider,
                    $cuadrilla->miembros,
                    $cuadrilla->puntoVentaId,
                    $cuadrilla->zona,
                    $usuarioId,
                    $cuadrilla->tarifas->carga25,
                    $cuadrilla->tarifas->carga50,
                    $cuadrilla->tarifas->descarga25,
                    $cuadrilla->tarifas->descarga50,
                    $cuadrilla->tarifas->traslado,
                    $cuadrilla->tarifas->apaleo
                ]
            );

            if (empty($results)) {
                throw new Exception("No se recibió respuesta de la base de datos.");
            }

            $row = $results[0];
            if ((int) $row->estado !== 0) {
                throw new Exception($row->mensaje);
            }

            $data = json_decode($row->data, true);
            if (!is_array($data)) {
                throw new Exception("Error al decodificar la respuesta JSON de la cuadrilla creada.");
            }

            return new CuadrillaDTO(
                id: (int) $data['codigoCuadrilla'],
                nombre: $data['nombreCuadrilla'],
                lider: $data['liderCuadrilla'],
                miembros: (int) $data['miembrosCuadrilla'],
                puntoVentaId: (int) $data['puntoVentaId'],
                zona: $data['zonaCuadrilla'],
                tarifas: new TarifasManiobra(
                    carga25: isset($data['carga25kg']) ? (float) $data['carga25kg'] : null,
                    carga50: isset($data['carga50kg']) ? (float) $data['carga50kg'] : null,
                    descarga25: isset($data['descarga25kg']) ? (float) $data['descarga25kg'] : null,
                    descarga50: isset($data['descarga50kg']) ? (float) $data['descarga50kg'] : null,
                    traslado: isset($data['traslado']) ? (float) $data['traslado'] : null,
                    apaleo: isset($data['apaleo']) ? (float) $data['apaleo'] : null
                )
            );
        } catch (Exception $e) {
            Log::error('Error en SqlServerCuadrillaRepository@create', ['error' => $e->getMessage()]);
            throw new Exception($e->getMessage());
        }
    }

    public function update(int $id, CuadrillaDTO $cuadrilla): CuadrillaDTO
    {
        try {
            $usuarioId = auth()->user()?->id ?? 'sa';

            $results = DB::connection('localDB')->select(
                "EXEC proc_pdm_modificar_cuadrilla 
                    @DocEntry = ?,
                    @Nombre = ?, 
                    @Lider = ?, 
                    @Miembros = ?, 
                    @PuntoVentaId = ?, 
                    @Zona = ?, 
                    @ModificadoPor = ?,
                    @Carga25kg = ?, 
                    @Carga50kg = ?, 
                    @Descarga25kg = ?, 
                    @Descarga50kg = ?, 
                    @Traslado = ?, 
                    @Apaleo = ?",
                [
                    $id,
                    $cuadrilla->nombre,
                    $cuadrilla->lider,
                    $cuadrilla->miembros,
                    $cuadrilla->puntoVentaId,
                    $cuadrilla->zona,
                    $usuarioId,
                    $cuadrilla->tarifas->carga25,
                    $cuadrilla->tarifas->carga50,
                    $cuadrilla->tarifas->descarga25,
                    $cuadrilla->tarifas->descarga50,
                    $cuadrilla->tarifas->traslado,
                    $cuadrilla->tarifas->apaleo
                ]
            );

            if (empty($results)) {
                throw new Exception("No se recibió respuesta de la base de datos.");
            }

            $row = $results[0];
            if ((int) $row->estado !== 0) {
                throw new Exception($row->mensaje);
            }

            $data = json_decode($row->data, true);
            if (!is_array($data)) {
                throw new Exception("Error al decodificar la respuesta JSON de la cuadrilla modificada.");
            }

            return new CuadrillaDTO(
                id: (int) $data['codigoCuadrilla'],
                nombre: $data['nombreCuadrilla'],
                lider: $data['liderCuadrilla'],
                miembros: (int) $data['miembrosCuadrilla'],
                puntoVentaId: (int) $data['puntoVentaId'],
                zona: $data['zonaCuadrilla'],
                tarifas: new TarifasManiobra(
                    carga25: isset($data['carga25kg']) ? (float) $data['carga25kg'] : null,
                    carga50: isset($data['carga50kg']) ? (float) $data['carga50kg'] : null,
                    descarga25: isset($data['descarga25kg']) ? (float) $data['descarga25kg'] : null,
                    descarga50: isset($data['descarga50kg']) ? (float) $data['descarga50kg'] : null,
                    traslado: isset($data['traslado']) ? (float) $data['traslado'] : null,
                    apaleo: isset($data['apaleo']) ? (float) $data['apaleo'] : null
                )
            );
        } catch (Exception $e) {
            Log::error('Error en SqlServerCuadrillaRepository@update', ['error' => $e->getMessage()]);
            throw new Exception($e->getMessage());
        }
    }

    public function delete(int $id): bool
    {
        try {
            $results = DB::connection('localDB')->select(
                "EXEC proc_pdm_eliminar_cuadrilla @DocEntry = ?",
                [$id]
            );

            if (empty($results)) {
                return false;
            }

            $row = $results[0];
            if ((int) $row->estado !== 0) {
                throw new Exception($row->mensaje);
            }

            return true;
        } catch (Exception $e) {
            Log::error('Error en SqlServerCuadrillaRepository@delete', ['error' => $e->getMessage()]);
            throw new Exception($e->getMessage());
        }
    }

    public function hasLiquidacionesEnProceso(int $cuadrillaId): bool
    {
        try {
            $sql = "
                SELECT COUNT(*) as total 
                FROM dbo.mov_pdm_registro_maniobras M
                INNER JOIN dbo.mov_pdm_cortes_liquidacion C ON M.U_CorteId = C.DocEntry
                WHERE M.U_CuadrillaId = ? AND C.U_Estado = 'borrador'
            ";
            $result = DB::connection('localDB')->selectOne($sql, [$cuadrillaId]);
            return $result && $result->total > 0;
        } catch (Exception $e) {
            Log::error('Error en SqlServerCuadrillaRepository@hasLiquidacionesEnProceso', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function hasManiobrasEnProceso(int $cuadrillaId): bool
    {
        try {
            $sql = "
                SELECT COUNT(*) as total 
                FROM dbo.mov_pdm_registro_maniobras 
                WHERE U_CuadrillaId = ? AND U_CorteId IS NULL
            ";
            $result = DB::connection('localDB')->selectOne($sql, [$cuadrillaId]);
            return $result && $result->total > 0;
        } catch (Exception $e) {
            Log::error('Error en SqlServerCuadrillaRepository@hasManiobrasEnProceso', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function exists(string $nombre, string $lider, int $puntoVentaId, ?int $excludeId = null): bool
    {
        try {
            $sql = "
                SELECT COUNT(*) as total 
                FROM dbo.mae_pdm_cuadrillas 
                WHERE LTRIM(RTRIM(UPPER(U_Nombre))) = LTRIM(RTRIM(UPPER(?)))
                  AND U_PuntoVentaId = ?
            ";
            $params = [$nombre, $puntoVentaId];

            if ($excludeId !== null) {
                $sql .= " AND DocEntry <> ?";
                $params[] = $excludeId;
            }

            $result = DB::connection('localDB')->selectOne($sql, $params);
            return $result && $result->total > 0;
        } catch (Exception $e) {
            Log::error('Error en SqlServerCuadrillaRepository@exists', ['error' => $e->getMessage()]);
            return false;
        }
    }
}

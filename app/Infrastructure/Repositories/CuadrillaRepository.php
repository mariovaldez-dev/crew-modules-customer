<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Cuadrilla\CuadrillaDTO;
use App\Domain\Cuadrilla\CuadrillaRepositoryInterface;
use App\Domain\Cuadrilla\TarifasManiobra;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class CuadrillaRepository implements CuadrillaRepositoryInterface
{
    /** @return CuadrillaDTO[] */
    public function list(array $filtros, string $zonaUsuario): array
    {
        try {
            $claveZona = ($zonaUsuario === 'TODAS') ? '' : $zonaUsuario;

            Log::debug('[CuadrillaRepo::list] INICIO', [
                'zonaUsuario' => $zonaUsuario,
                'claveZona'   => $claveZona,
                'filtros'     => $filtros,
            ]);

            DB::connection('localDB')->statement("SET ANSI_NULLS ON");
            DB::connection('localDB')->statement("SET ANSI_WARNINGS ON");

            $results = DB::connection('localDB')->select(
                "EXEC proc_consultar_cuadrillas @ClaveZona = ?",
                [$claveZona]
            );

            Log::debug('[CuadrillaRepo::list] Respuesta cruda del SP', [
                'total_rows'   => count($results),
                'primera_fila' => !empty($results) ? (array) $results[0] : null,
            ]);

            if (empty($results)) {
                Log::warning('[CuadrillaRepo::list] SP no devolvió filas');
                return [];
            }

            $row = $results[0];

            Log::debug('[CuadrillaRepo::list] Estado del SP', [
                'estado'  => $row->estado ?? 'N/A',
                'mensaje' => $row->mensaje ?? 'N/A',
            ]);

            if ((int) $row->estado !== 0) {
                Log::warning('[CuadrillaRepo::list] SP devolvió estado de error', [
                    'estado'  => $row->estado,
                    'mensaje' => $row->mensaje ?? 'sin mensaje',
                ]);
                return [];
            }

            $cuadrillasJson = json_decode($row->listaCuadrillas, true);

            Log::debug('[CuadrillaRepo::list] JSON decodificado', [
                'json_error'    => json_last_error_msg(),
                'total_items'   => is_array($cuadrillasJson) ? count($cuadrillasJson) : 'NO ES ARRAY',
                'muestra'       => is_array($cuadrillasJson) ? array_slice($cuadrillasJson, 0, 2) : null,
            ]);

            if (!is_array($cuadrillasJson)) {
                Log::warning('[CuadrillaRepo::list] listaCuadrillas no es un array válido');
                return [];
            }

            $search   = trim($filtros['search']       ?? '');
            $pvFiltro = trim($filtros['puntoVentaId'] ?? '');

            $cuadrillas = [];
            $descartados = ['search' => 0, 'pv' => 0];

            foreach ($cuadrillasJson as $item) {
                if ($search !== '' &&
                    stripos($item['nombreCuadrilla'], $search) === false &&
                    stripos($item['liderCuadrilla'],  $search) === false
                ) {
                    $descartados['search']++;
                    continue;
                }

                if ($pvFiltro !== '' && $item['puntoVenta'] !== $pvFiltro) {
                    $descartados['pv']++;
                    continue;
                }

                $cuadrillas[] = $this->mapRowToDTO($item);
            }

            Log::debug('[CuadrillaRepo::list] Resultado final', [
                'total_devueltas' => count($cuadrillas),
                'descartados'     => $descartados,
            ]);

            return $cuadrillas;
        } catch (Exception $e) {
            Log::error('[CuadrillaRepo::list] Excepción', [
                'zonaUsuario' => $zonaUsuario,
                'error'       => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

    public function findById(int $id): ?CuadrillaDTO
    {
        try {
            Log::debug('[CuadrillaRepo::findById] INICIO', ['id_buscado' => $id]);

            $context = session()->get('usuario_contexto');
            $claveZona = '';
            if ($context instanceof \App\Domain\Shared\UsuarioContexto && $context->tipo !== 'AM') {
                $claveZona = $context->zona;
            }

            Log::debug('[CuadrillaRepo::findById] Parámetro de zona', ['claveZona' => $claveZona]);

            DB::connection('localDB')->statement("SET ANSI_NULLS ON");
            DB::connection('localDB')->statement("SET ANSI_WARNINGS ON");

            $results = DB::connection('localDB')->select(
                "EXEC proc_consultar_cuadrillas @ClaveZona = ?",
                [$claveZona]
            );

            Log::debug('[CuadrillaRepo::findById] Respuesta cruda del SP', [
                'total_rows'   => count($results),
                'primera_fila' => !empty($results) ? (array) $results[0] : null,
            ]);

            if (empty($results)) {
                Log::warning('[CuadrillaRepo::findById] SP no devolvió filas');
                return null;
            }

            $row = $results[0];
            Log::debug('[CuadrillaRepo::findById] Estado del SP', [
                'estado'  => $row->estado ?? 'N/A',
                'mensaje' => $row->mensaje ?? 'N/A',
            ]);

            if ((int) $row->estado !== 0) {
                Log::warning('[CuadrillaRepo::findById] SP devolvió estado de error', [
                    'estado'  => $row->estado,
                    'mensaje' => $row->mensaje ?? 'sin mensaje',
                ]);
                return null;
            }

            $cuadrillasJson = json_decode($row->listaCuadrillas, true);
            if (!is_array($cuadrillasJson)) {
                Log::warning('[CuadrillaRepo::findById] listaCuadrillas no es un array válido');
                return null;
            }

            foreach ($cuadrillasJson as $item) {
                if ((int) $item['idCuadrilla'] === $id) {
                    Log::debug('[CuadrillaRepo::findById] Cuadrilla encontrada', ['id' => $id]);
                    return $this->mapRowToDTO($item);
                }
            }

            Log::warning('[CuadrillaRepo::findById] Cuadrilla no encontrada en la lista', ['id_buscado' => $id]);
            return null;
        } catch (Exception $e) {
            Log::error('[CuadrillaRepo::findById] Excepción', [
                'id'    => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    public function create(CuadrillaDTO $cuadrilla): CuadrillaDTO
    {
        try {
            $usuarioId  = (int) (auth()->user()?->id ?? 0);
            $listaTarifas = $this->tarifasToJson($cuadrilla->tarifas);

            $context = session()->get('usuario_contexto');
            $claveZona = '';
            if ($context instanceof \App\Domain\Shared\UsuarioContexto && $context->tipo !== 'AM') {
                $claveZona = $context->zona;
            }

            $results = DB::connection('localDB')->select(
                "EXEC proc_pdm_administrar_cuadrillas
                    @Opcion          = 1,
                    @zona            = ?,
                    @nombreCuadrilla = ?,
                    @liderCuadrilla  = ?,
                    @miembros        = ?,
                    @puntoVenta      = ?,
                    @listaTarifas    = ?,
                    @usuario         = ?",
                [
                    $claveZona,
                    $cuadrilla->nombre,
                    $cuadrilla->lider,
                    $cuadrilla->miembros,
                    $cuadrilla->puntoVentaId,
                    $listaTarifas,
                    $usuarioId,
                ]
            );

            if (empty($results)) {
                throw new Exception('No se recibió respuesta de la base de datos.');
            }

            $row = $results[0];
            if ((int) $row->estado !== 0) {
                throw new Exception($row->mensaje);
            }
            $idGenerado = isset($row->idCuadrilla) ? (int) $row->idCuadrilla : (isset($row->id) ? (int) $row->id : null);

            return new CuadrillaDTO(
                id:          $idGenerado,
                nombre:      $cuadrilla->nombre,
                lider:       $cuadrilla->lider,
                miembros:    $cuadrilla->miembros,
                puntoVentaId: $cuadrilla->puntoVentaId,
                zona:        $cuadrilla->zona,
                tarifas:     $cuadrilla->tarifas
            );
        } catch (Exception $e) {
            Log::error('Error en CuadrillaRepository@create', ['error' => $e->getMessage()]);
            throw new Exception($e->getMessage());
        }
    }

    public function update(int $id, CuadrillaDTO $cuadrilla): CuadrillaDTO
    {
        try {
            $usuarioId    = (int) (auth()->user()?->id ?? 0);
            $listaTarifas = $this->tarifasToJson($cuadrilla->tarifas);

            $context = session()->get('usuario_contexto');
            $claveZona = '';
            if ($context instanceof \App\Domain\Shared\UsuarioContexto && $context->tipo !== 'AM') {
                $claveZona = $context->zona;
            }

            $results = DB::connection('localDB')->select(
                "EXEC proc_pdm_administrar_cuadrillas
                    @Opcion          = 2,
                    @zona            = ?,
                    @idCuadrilla     = ?,
                    @nombreCuadrilla = ?,
                    @liderCuadrilla  = ?,
                    @miembros        = ?,
                    @puntoVenta      = ?,
                    @listaTarifas    = ?,
                    @usuario         = ?",
                [
                    $claveZona,
                    $id,
                    $cuadrilla->nombre,
                    $cuadrilla->lider,
                    $cuadrilla->miembros,
                    $cuadrilla->puntoVentaId,
                    $listaTarifas,
                    $usuarioId,
                ]
            );

            if (empty($results)) {
                throw new Exception('No se recibió respuesta de la base de datos.');
            }

            $row = $results[0];
            if ((int) $row->estado !== 0) {
                throw new Exception($row->mensaje);
            }

            return new CuadrillaDTO(
                id:          $id,
                nombre:      $cuadrilla->nombre,
                lider:       $cuadrilla->lider,
                miembros:    $cuadrilla->miembros,
                puntoVentaId: $cuadrilla->puntoVentaId,
                zona:        $cuadrilla->zona,
                tarifas:     $cuadrilla->tarifas
            );
        } catch (Exception $e) {
            Log::error('Error en CuadrillaRepository@update', ['error' => $e->getMessage()]);
            throw new Exception($e->getMessage());
        }
    }

    public function delete(int $id): bool
    {
        try {
            $usuarioId = (int) (auth()->user()?->id ?? 0);

            $results = DB::connection('localDB')->select(
                "EXEC proc_pdm_administrar_cuadrillas
                    @Opcion      = 3,
                    @idCuadrilla = ?,
                    @usuario     = ?",
                [$id, $usuarioId]
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
            Log::error('Error en CuadrillaRepository@delete', ['error' => $e->getMessage()]);
            throw new Exception($e->getMessage());
        }
    }

    public function hasLiquidacionesEnProceso(int $cuadrillaId): bool
    {
        // La validación de eliminación la procesa el SP proc_pdm_administrar_cuadrillas @Opcion = 3
        return false;
    }

    public function hasManiobrasEnProceso(int $cuadrillaId): bool
    {
        // La validación de eliminación la procesa el SP proc_pdm_administrar_cuadrillas @Opcion = 3
        return false;
    }

    public function exists(string $nombre, string $lider, string $puntoVentaId, ?int $excludeId = null): bool
    {
        try {
            $cuadrillas = $this->list(['search' => $nombre, 'puntoVentaId' => $puntoVentaId], 'TODAS');
            foreach ($cuadrillas as $c) {
                if ($excludeId !== null && $c->id === $excludeId) {
                    continue;
                }
                if (strtolower(trim($c->nombre)) === strtolower(trim($nombre)) &&
                    (string)$c->puntoVentaId === (string)$puntoVentaId
                ) {
                    return true;
                }
            }
            return false;
        } catch (Exception $e) {
            Log::error('Error en CuadrillaRepository@exists', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function tarifasToJson(TarifasManiobra $tarifas): string
    {
        $lista = [];
        foreach ($tarifas->dynamic as $tipoId => $valor) {
            if ($valor !== null && $valor !== '' && (float) $valor > 0) {
                $lista[] = [
                    'tipoManiobra' => (int) $tipoId,
                    'tarifa'       => round((float) $valor, 2),
                ];
            }
        }
        return json_encode($lista, JSON_UNESCAPED_UNICODE);
    }

    private function mapRowToDTO(array $item): CuadrillaDTO
    {
        $tarifasArray = is_string($item['listaTarifas'] ?? null)
            ? json_decode($item['listaTarifas'], true)
            : ($item['listaTarifas'] ?? []);

        $tarifasMap = [];
        foreach ((array) $tarifasArray as $t) {
            $tarifasMap[(int) $t['idTipoManiobra']] = (float) $t['tarifa'];
        }

        return new CuadrillaDTO(
            id:           (int) $item['idCuadrilla'],
            nombre:       $item['nombreCuadrilla'],
            lider:        $item['liderCuadrilla'],
            miembros:     (int) $item['miembros'],
            puntoVentaId: (string) $item['puntoVenta'],
            zona:         '',
            tarifas:      TarifasManiobra::fromDynamic($tarifasMap)
        );
    }
}

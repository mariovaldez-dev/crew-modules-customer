<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Cuadrilla\CuadrillaDTO;
use App\Domain\Cuadrilla\CuadrillaRepositoryInterface;
use App\Domain\Cuadrilla\TarifasManiobra;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CuadrillaRepository implements CuadrillaRepositoryInterface
{
    /** @return CuadrillaDTO[] */
    public function list(array $filtros, string $zonaUsuario): array
    {
        try {
            $context = session()->get('usuario_contexto');
            $isAm = ($context instanceof \App\Domain\Shared\UsuarioContexto && ($context->isAdministrador() || $context->tipo === 'AM'))
                || $zonaUsuario === 'TODAS' || $zonaUsuario === 'AM' || $zonaUsuario === '';

            $claveZona = $isAm ? '' : $zonaUsuario;

            Log::info("CONSULTA REAL A BD (SP): proc_consultar_cuadrillas", ['claveZona' => $claveZona, 'isAm' => $isAm, 'zonaUsuario' => $zonaUsuario]);

            DB::connection('maniobras')->statement("SET ANSI_NULLS ON");
            DB::connection('maniobras')->statement("SET ANSI_WARNINGS ON");

            $results = DB::connection('maniobras')->select(
                "EXEC proc_consultar_cuadrillas @ClaveZona = ?",
                [$claveZona]
            );

            if (empty($results)) {
                return [];
            }

            $response = $results[0];

            if (!isset($response->estado) || (int) $response->estado !== 0) {
                return [];
            }

            $rawLista = $response->listaCuadrillas ?? $response->listacuadrillas ?? $response->LISTACUADRILLAS ?? null;

            if (empty($rawLista)) {
                return [];
            }

            $cuadrillasData = is_string($rawLista) ? json_decode($rawLista, true) : $rawLista;

            if (!is_array($cuadrillasData)) {
                return [];
            }

            $search   = trim($filtros['search']       ?? '');
            $pvFiltro = trim($filtros['puntoVentaId'] ?? '');

            $cuadrillas = [];
            foreach ($cuadrillasData as $item) {
                $itemArray = (array) $item;
                $nombre = $itemArray['nombreCuadrilla'] ?? $itemArray['NOMBRECUADRILLA'] ?? '';
                $lider  = $itemArray['liderCuadrilla']  ?? $itemArray['LIDERCUADRILLA']  ?? '';
                $pv     = $itemArray['puntoVenta']       ?? $itemArray['PUNTOVENTA']       ?? '';

                if ($search !== '' &&
                    stripos($nombre, $search) === false &&
                    stripos($lider,  $search) === false
                ) {
                    continue;
                }

                if ($pvFiltro !== '' && $pv !== $pvFiltro) {
                    continue;
                }

                $cuadrillas[] = $this->mapRowToDTO($itemArray);
            }

            return $cuadrillas;
        } catch (\Throwable $e) {
            Log::error('Error en CuadrillaRepository@list', [
                'zona'  => $zonaUsuario,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    public function findById(int $id): ?CuadrillaDTO
    {
        try {
            $context = session()->get('usuario_contexto');
            $claveZona = '';
            if ($context instanceof \App\Domain\Shared\UsuarioContexto && $context->tipo !== 'AM') {
                $claveZona = $context->zona;
            }

            Log::info("CONSULTA REAL A BD (SP): proc_consultar_cuadrillas (findById)", ['id' => $id, 'claveZona' => $claveZona]);

            DB::connection('maniobras')->statement("SET ANSI_NULLS ON");
            DB::connection('maniobras')->statement("SET ANSI_WARNINGS ON");

            $results = DB::connection('maniobras')->select(
                "EXEC proc_consultar_cuadrillas @ClaveZona = ?",
                [$claveZona]
            );

            if (empty($results)) {
                return null;
            }

            $response = $results[0];

            if (!isset($response->estado) || (int) $response->estado !== 0) {
                return null;
            }

            $rawLista = $response->listaCuadrillas ?? $response->listacuadrillas ?? $response->LISTACUADRILLAS ?? null;

            if (empty($rawLista)) {
                return null;
            }

            $cuadrillasData = is_string($rawLista) ? json_decode($rawLista, true) : $rawLista;

            if (!is_array($cuadrillasData)) {
                return null;
            }

            foreach ($cuadrillasData as $item) {
                $itemArray = (array) $item;
                $itemId = (int) ($itemArray['idCuadrilla'] ?? $itemArray['IDCUADRILLA'] ?? 0);
                if ($itemId === $id) {
                    return $this->mapRowToDTO($itemArray);
                }
            }

            return null;
        } catch (\Throwable $e) {
            Log::error('Error en CuadrillaRepository@findById', [
                'id'    => $id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function create(CuadrillaDTO $cuadrilla): CuadrillaDTO
    {
        try {
            $usuarioId = (int) (auth()->user()?->id ?? 0);
            $context = session()->get('usuario_contexto');
            $zona = ($context instanceof \App\Domain\Shared\UsuarioContexto) ? $context->zona : '';

            Log::info("CONSULTA REAL A BD (SP): proc_pdm_administrar_cuadrillas (Opcion = 1)", ['nombre' => $cuadrilla->nombre, 'zona' => $zona]);

            DB::connection('maniobras')->statement("SET ANSI_NULLS ON");
            DB::connection('maniobras')->statement("SET ANSI_WARNINGS ON");

            $results = DB::connection('maniobras')->select(
                "EXEC proc_pdm_administrar_cuadrillas 
                    @Opcion = 1, 
                    @zona = ?, 
                    @nombreCuadrilla = ?, 
                    @liderCuadrilla = ?, 
                    @miembros = ?, 
                    @puntoVenta = ?, 
                    @listaTarifas = ?, 
                    @usuario = ?",
                [
                    $zona,
                    $cuadrilla->nombre,
                    $cuadrilla->lider,
                    $cuadrilla->miembros,
                    $cuadrilla->puntoVentaId,
                    $this->tarifasToJson($cuadrilla->tarifas),
                    $usuarioId
                ]
            );

            if (empty($results)) {
                throw new Exception('No se recibió respuesta de la base de datos.');
            }

            $response = $results[0];

            if (!isset($response->estado) || (int) $response->estado !== 0) {
                throw new Exception($response->mensaje ?? 'Error al crear cuadrilla');
            }

            return $cuadrilla;
        } catch (\Throwable $e) {
            Log::error('Error en CuadrillaRepository@create', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function update(int $id, CuadrillaDTO $cuadrilla): CuadrillaDTO
    {
        try {
            $usuarioId = (int) (auth()->user()?->id ?? 0);
            $context = session()->get('usuario_contexto');
            $zona = ($context instanceof \App\Domain\Shared\UsuarioContexto) ? $context->zona : '';

            Log::info("CONSULTA REAL A BD (SP): proc_pdm_administrar_cuadrillas (Opcion = 2)", ['id' => $id, 'nombre' => $cuadrilla->nombre]);

            DB::connection('maniobras')->statement("SET ANSI_NULLS ON");
            DB::connection('maniobras')->statement("SET ANSI_WARNINGS ON");

            $results = DB::connection('maniobras')->select(
                "EXEC proc_pdm_administrar_cuadrillas 
                    @Opcion = 2, 
                    @zona = ?, 
                    @idCuadrilla = ?, 
                    @nombreCuadrilla = ?, 
                    @liderCuadrilla = ?, 
                    @miembros = ?, 
                    @puntoVenta = ?, 
                    @listaTarifas = ?, 
                    @usuario = ?",
                [
                    $zona,
                    $id,
                    $cuadrilla->nombre,
                    $cuadrilla->lider,
                    $cuadrilla->miembros,
                    $cuadrilla->puntoVentaId,
                    $this->tarifasToJson($cuadrilla->tarifas),
                    $usuarioId
                ]
            );

            if (empty($results)) {
                throw new Exception('No se recibió respuesta de la base de datos.');
            }

            $response = $results[0];

            if (!isset($response->estado) || (int) $response->estado !== 0) {
                throw new Exception($response->mensaje ?? 'Error al actualizar cuadrilla');
            }

            return $cuadrilla;
        } catch (\Throwable $e) {
            Log::error('Error en CuadrillaRepository@update', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $usuarioId = (int) (auth()->user()?->id ?? 0);

            Log::info("CONSULTA REAL A BD (SP): proc_pdm_administrar_cuadrillas (Opcion = 3)", ['id' => $id]);

            DB::connection('maniobras')->statement("SET ANSI_NULLS ON");
            DB::connection('maniobras')->statement("SET ANSI_WARNINGS ON");

            $results = DB::connection('maniobras')->select(
                "EXEC proc_pdm_administrar_cuadrillas 
                    @Opcion = 3, 
                    @idCuadrilla = ?, 
                    @usuario = ?",
                [
                    $id,
                    $usuarioId
                ]
            );

            if (empty($results)) {
                throw new Exception('No se recibió respuesta de la base de datos.');
            }

            $response = $results[0];

            if (!isset($response->estado) || (int) $response->estado !== 0) {
                throw new Exception($response->mensaje ?? 'Error al eliminar cuadrilla');
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Error en CuadrillaRepository@delete', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function hasLiquidacionesEnProceso(int $cuadrillaId): bool
    {
        return false;
    }

    public function hasManiobrasEnProceso(int $cuadrillaId): bool
    {
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
        } catch (\Throwable $e) {
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
        $rawTarifas = $item['listaTarifas'] ?? $item['LISTATARIFAS'] ?? $item['listatarifas'] ?? [];
        $tarifasArray = is_string($rawTarifas) ? json_decode($rawTarifas, true) : $rawTarifas;

        $tarifasMap = [];
        foreach ((array) $tarifasArray as $t) {
            $tArr = (array) $t;
            $tipoId = (int) ($tArr['idTipoManiobra'] ?? $tArr['IDTIPOMANIOBRA'] ?? 0);
            $tarifaVal = (float) ($tArr['tarifa'] ?? $tArr['TARIFA'] ?? 0);
            if ($tipoId > 0) {
                $tarifasMap[$tipoId] = $tarifaVal;
            }
        }

        return new CuadrillaDTO(
            id:           (int) ($item['idCuadrilla'] ?? $item['IDCUADRILLAS'] ?? $item['IDCUADRILLA'] ?? 0),
            nombre:       (string) ($item['nombreCuadrilla'] ?? $item['NOMBRECUADRILLA'] ?? ''),
            lider:        (string) ($item['liderCuadrilla'] ?? $item['LIDERCUADRILLA'] ?? ''),
            miembros:     (int) ($item['miembros'] ?? $item['MIEMBROS'] ?? 0),
            puntoVentaId: (string) ($item['puntoVenta'] ?? $item['PUNTOVENTA'] ?? ''),
            zona:         '',
            tarifas:      TarifasManiobra::fromDynamic($tarifasMap)
        );
    }
}

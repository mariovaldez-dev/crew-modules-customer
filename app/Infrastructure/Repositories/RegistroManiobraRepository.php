<?php

namespace App\Infrastructure\Repositories;

use App\Domain\RegistroManiobra\ManiobraManualDTO;
use App\Domain\RegistroManiobra\RegistroManiobraDTO;
use App\Domain\RegistroManiobra\RegistroManiobraRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class RegistroManiobraRepository implements RegistroManiobraRepositoryInterface
{
    public function list(array $filtros, string $zonaUsuario, string $rolUsuario): array
    {
        try {
            $claveZona = ($zonaUsuario === 'TODAS') ? '' : $zonaUsuario;

            DB::connection('localDB')->statement("SET ANSI_NULLS ON");
            DB::connection('localDB')->statement("SET ANSI_WARNINGS ON");
            $results = DB::connection('localDB')->select(
                "EXEC proc_pdm_obtener_maniobras_ejecutadas @ClaveZona = ?, @FechaInicio = ?, @FechaFin = ?",
                [
                    $claveZona,
                    $filtros['fechaInicio'] ?? null,
                    $filtros['fechaFin'] ?? null
                ]
            );

            if (empty($results)) {
                return [];
            }

            $row = $results[0];
            if ((int) $row->estado !== 0) {
                return [];
            }

            $maniobrasJson = json_decode($row->listaManiobras, true);
            if (!is_array($maniobrasJson)) {
                return [];
            }

            $sucursalRepo = app(\App\Domain\Shared\Repositories\SucursalRepositoryInterface::class);
            $zonaFiltro = ($rolUsuario === 'AM') ? 'TODAS' : $zonaUsuario;
            $almacenesMap = $sucursalRepo->listaPuntosDeVentaPorZona($zonaFiltro);

            Log::debug('[RegistroManiobraRepository@list] Inicia mapeo', [
                'total_json' => count($maniobrasJson),
                'filtros' => $filtros,
                'data' => $maniobrasJson
            ]);

            $maniobras = [];
            foreach ($maniobrasJson as $item) {
                $fechaStr = $item['fecha'] ?? $item['fec_registro'] ?? date('Y-m-d');
                $fecha = new \DateTimeImmutable($fechaStr);
                
                $almacenId = $item['idPuntoVenta'] ?? '';

                if (!empty($filtros['almacenId'])) {
                    if ((string)$almacenId !== (string)$filtros['almacenId']) {
                        continue;
                    }
                }

                if (!empty($filtros['search'])) {
                    $search = mb_strtolower(trim($filtros['search']));
                    $match = false;
                    
                    if (mb_strpos(mb_strtolower($item['nombreManiobra'] ?? ''), $search) !== false) $match = true;
                    if (mb_strpos(mb_strtolower($item['nombreCuadrilla'] ?? ''), $search) !== false) $match = true;
                    if (mb_strpos(mb_strtolower($item['nombreLiderCuadrilla'] ?? ''), $search) !== false) $match = true;
                    
                    if (!$match) continue;
                }

                $estadoId = (int) ($item['estatus'] ?? 1);
                $corteId = isset($item['idCorte']) ? (int) $item['idCorte'] : null;

                $dto = new RegistroManiobraDTO(
                    id: (int) ($item['idManiobra'] ?? 0),
                    folio: $item['folio'] ?? 'S/F',
                    fecha: $fecha,
                    almacenId: $almacenId,
                    almacenNombre: $almacenesMap[$almacenId] ?? $almacenId,
                    cuadrillaId: (int) ($item['idCuadrilla'] ?? 0),
                    cuadrillaNombre: $item['nombreCuadrilla'] ?? '',
                    tipoManiobraId: (int) ($item['idTipoManiobra'] ?? 0),
                    tipoManiobraNombre: $item['nombreManiobra'] ?? '',
                    toneladas: (float) ($item['numeroToneladas'] ?? 0),
                    corteId: $corteId,
                    origen: $item['origen'] ?? 'APP',
                    estado: 'En proceso',
                    documentoSap: $item['numeroDocumentoSAP'] ?? null
                );

                if (!empty($filtros['estado'])) {
                    $estadoCalculado = $corteId ? 'Liquidada' : 'En proceso';
                    if (mb_strtolower($estadoCalculado) !== mb_strtolower($filtros['estado'])) {
                        continue;
                    }
                }

                $maniobras[] = $dto;
            }
            
            Log::debug('[RegistroManiobraRepository@list] Fin mapeo', [
                'total_maniobras_filtradas' => count($maniobras)
            ]);

            return $maniobras;
        } catch (Exception $e) {
            Log::error('Error en RegistroManiobraRepository@list', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function create(ManiobraManualDTO $maniobra): string
    {
        try {
            $usuarioId = auth()->user()?->id ?? 0;

            $results = DB::connection('localDB')->select(
                "EXEC dbo.proc_pdm_administrar_maniobras_ejecutadas
                    @idTipoManiobra = ?,
                    @idPuntoVenta = ?,
                    @idCuadrilla = ?,
                    @serieDocumento = ?,
                    @documentoSap = ?,
                    @toneladas = ?,
                    @usuario = ?",
                [
                    $maniobra->tipoManiobraId,
                    $maniobra->almacenId,
                    $maniobra->cuadrillaId,
                    0,
                    (int) $maniobra->documentoSap,
                    $maniobra->toneladas,
                    (int) $usuarioId
                ]
            );

            if (empty($results)) {
                throw new Exception("No se recibió respuesta de la base de datos.");
            }

            $row = $results[0];
            if ((int) $row->estado !== 0) {
                throw new Exception($row->mensaje);
            }

            return $row->mensaje ?? 'Maniobra registrada correctamente.';
        } catch (Exception $e) {
            Log::error('Error en RegistroManiobraRepository@create', ['error' => $e->getMessage()]);
            throw new Exception($e->getMessage());
        }
    }

    public function cuadrillasPorAlmacen(string $almacenId): array
    {
        try {
            DB::connection('localDB')->statement("SET ANSI_NULLS ON");
            DB::connection('localDB')->statement("SET ANSI_WARNINGS ON");

            $results = DB::connection('localDB')->select(
                "EXEC proc_pdm_cosultar_combos 2, ?",
                [$almacenId]
            );

            if (empty($results)) {
                return [];
            }
            $row = $results[0];
            if ((int) $row->estado !== 0) {
                return [];
            }

            $combo = $row->combo ?? null;
            if (is_string($combo)) {
                $combo = json_decode($combo, true);
            }
            if (empty($combo)) {
                return [];
            }

            $cuadrillas = [];
            foreach ($combo as $item) {
                $item = (array) $item;
                $codigo = (int) ($item['codigo'] ?? 0);
                $nombre = trim($item['nombre'] ?? '');
                if ($codigo > 0) {
                    $cuadrillas[$codigo] = $nombre;
                }
            }
            return $cuadrillas;
        } catch (\Throwable $e) {
            Log::error('Error en RegistroManiobraRepository@cuadrillasPorAlmacen', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function tiposManiobra(): array
    {
        try {
            DB::connection('localDB')->statement("SET ANSI_NULLS ON");
            DB::connection('localDB')->statement("SET ANSI_WARNINGS ON");

            $results = DB::connection('localDB')->select(
                "EXEC proc_pdm_cosultar_combos 3"
            );

            if (empty($results)) {
                return [];
            }
            $row = $results[0];
            if ((int) $row->estado !== 0) {
                return [];
            }

            $combo = $row->combo ?? null;
            if (is_string($combo)) {
                $combo = json_decode($combo, true);
            }
            if (empty($combo)) {
                return [];
            }

            $tipos = [];
            foreach ($combo as $item) {
                $item = (array) $item;
                $codigo = (int) ($item['codigo'] ?? 0);
                $nombre = trim($item['nombre'] ?? '');
                if ($codigo > 0) {
                    $tipos[$codigo] = $nombre;
                }
            }
            return $tipos;
        } catch (\Throwable $e) {
            Log::error('Error en RegistroManiobraRepository@tiposManiobra', ['error' => $e->getMessage()]);
            return [];
        }
    }
}

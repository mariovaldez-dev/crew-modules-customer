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
        $claveZona = ($zonaUsuario === 'TODAS') ? '' : $zonaUsuario;

        Log::info("[REGISTRO-MANIOBRAS] Ejecutando proc_pdm_obtener_maniobras_ejecutadas", [
            'claveZona' => $claveZona,
            'fechaInicio' => $filtros['fechaInicio'] ?? null,
            'fechaFin' => $filtros['fechaFin'] ?? null,
            'rolUsuario' => $rolUsuario
        ]);

        try {
            $results = DB::connection('maniobras')->select(
                "SET NOCOUNT ON; SET ANSI_NULLS ON; SET ANSI_WARNINGS ON; EXEC proc_pdm_obtener_maniobras_ejecutadas @ClaveZona = ?, @FechaInicio = ?, @FechaFin = ?",
                [
                    $claveZona,
                    $filtros['fechaInicio'] ?? null,
                    $filtros['fechaFin'] ?? null
                ]
            );

            if (empty($results)) {
                Log::warning("[REGISTRO-MANIOBRAS] proc_pdm_obtener_maniobras_ejecutadas no devolvió ningún resultado.");
                return [];
            }

            $row = $results[0];
            Log::info("[REGISTRO-MANIOBRAS] Respuesta raw de proc_pdm_obtener_maniobras_ejecutadas", [
                'estado' => $row->estado ?? null,
                'mensaje' => $row->mensaje ?? null,
                'listaManiobras_raw' => $row->listaManiobras ?? null
            ]);

            if ((int) $row->estado !== 0) {
                Log::warning("[REGISTRO-MANIOBRAS] proc_pdm_obtener_maniobras_ejecutadas devolvió estado diferente de 0: " . ($row->mensaje ?? 'Sin mensaje'));
                return [];
            }

            $maniobrasJson = json_decode($row->listaManiobras, true);
            if (!is_array($maniobrasJson)) {
                Log::error("[REGISTRO-MANIOBRAS] Error decodificando listaManiobras JSON.");
                return [];
            }

            $sucursalRepo = app(\App\Domain\Shared\Repositories\SucursalRepositoryInterface::class);
            $zonaFiltro = ($rolUsuario === 'AM') ? 'TODAS' : $zonaUsuario;
            $almacenesMap = $sucursalRepo->listaPuntosDeVentaPorZona($zonaFiltro);

            $maniobras = [];
            foreach ($maniobrasJson as $item) {
                $fechaRaw = $item['fecha'] ?? $item['fec_registro'] ?? date('Y-m-d');
                $fechaStr = substr($fechaRaw, 0, 10);
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
                $idManiobra = (int) ($item['idManiobra'] ?? 0);
                $folioFormatted = !empty($item['folio']) ? $item['folio'] : ($idManiobra > 0 ? 'MAN-' . str_pad((string)$idManiobra, 6, '0', STR_PAD_LEFT) : 'S/F');

                $dto = new RegistroManiobraDTO(
                    id: $idManiobra,
                    folio: $folioFormatted,
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
            
            Log::info("[REGISTRO-MANIOBRAS] Total maniobras procesadas exitosamente", [
                'total_recibidas' => count($maniobrasJson),
                'total_filtradas' => count($maniobras)
            ]);

            return $maniobras;
        } catch (Exception $e) {
            Log::error("[REGISTRO-MANIOBRAS] Error en RegistroManiobraRepository@list: " . $e->getMessage(), ['exception' => $e]);
            return [];
        }
    }

    public function create(ManiobraManualDTO $maniobra): string
    {
        $usuarioId = auth()->user()?->id ?? 0;
        $fechaString = $maniobra->fecha->format('Y-m-d H:i:s');

        Log::info("[REGISTRO-MANIOBRAS] Ejecutando proc_pdm_administrar_maniobras_ejecutadas (Alta Manual)", [
            'tipoManiobraId' => $maniobra->tipoManiobraId,
            'almacenId' => $maniobra->almacenId,
            'cuadrillaId' => $maniobra->cuadrillaId,
            'documentoSap' => $maniobra->documentoSap,
            'toneladas' => $maniobra->toneladas,
            'usuarioId' => $usuarioId,
            'fecha' => $fechaString
        ]);

        try {
            $results = DB::connection('maniobras')->select(
                "EXEC dbo.proc_pdm_administrar_maniobras_ejecutadas
                    @idTipoManiobra = ?,
                    @idPuntoVenta = ?,
                    @idCuadrilla = ?,
                    @serieDocumento = ?,
                    @documentoSap = ?,
                    @toneladas = ?,
                    @usuario = ?,
                    @fecha = ?",
                [
                    $maniobra->tipoManiobraId,
                    $maniobra->almacenId,
                    $maniobra->cuadrillaId,
                    0,
                    (int) $maniobra->documentoSap,
                    $maniobra->toneladas,
                    (int) $usuarioId,
                    $fechaString
                ]
            );

            if (empty($results)) {
                Log::error("[REGISTRO-MANIOBRAS] No se recibió respuesta de proc_pdm_administrar_maniobras_ejecutadas.");
                throw new Exception("No se recibió respuesta de la base de datos.");
            }

            $row = $results[0];
            Log::info("[REGISTRO-MANIOBRAS] Respuesta raw de proc_pdm_administrar_maniobras_ejecutadas", [
                'estado' => $row->estado ?? null,
                'mensaje' => $row->mensaje ?? null
            ]);

            if ((int) $row->estado !== 0) {
                Log::warning("[REGISTRO-MANIOBRAS] Error devuelto por proc_pdm_administrar_maniobras_ejecutadas: " . ($row->mensaje ?? 'Error desconocido'));
                throw new Exception($row->mensaje);
            }

            return $row->mensaje ?? 'Maniobra registrada correctamente.';
        } catch (Exception $e) {
            Log::error("[REGISTRO-MANIOBRAS] Exception en RegistroManiobraRepository@create: " . $e->getMessage(), ['exception' => $e]);
            throw new Exception($e->getMessage());
        }
    }

    public function cuadrillasPorAlmacen(string $almacenId): array
    {
        Log::info("[REGISTRO-MANIOBRAS] Consultando cuadrillas por almacén (proc_pdm_cosultar_combos 2)", ['almacenId' => $almacenId]);

        try {
            $results = DB::connection('maniobras')->select(
                "EXEC proc_pdm_cosultar_combos 2, ?",
                [$almacenId]
            );

            if (empty($results)) {
                Log::warning("[REGISTRO-MANIOBRAS] proc_pdm_cosultar_combos 2 no devolvió ningún resultado para almacén: {$almacenId}");
                return [];
            }

            $row = $results[0];
            Log::info("[REGISTRO-MANIOBRAS] Respuesta raw de proc_pdm_cosultar_combos 2", ['row' => $row]);

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

            Log::info("[REGISTRO-MANIOBRAS] Cuadrillas por almacén obtenidas correctamente. Total: " . count($cuadrillas));
            return $cuadrillas;
        } catch (\Throwable $e) {
            Log::error("[REGISTRO-MANIOBRAS] Error en cuadrillasPorAlmacen: " . $e->getMessage(), ['exception' => $e]);
            return [];
        }
    }

    public function tiposManiobra(): array
    {
        Log::info("[REGISTRO-MANIOBRAS] Consultando tipos de maniobra (proc_pdm_cosultar_combos 3)");

        try {
            $results = DB::connection('maniobras')->select(
                "EXEC proc_pdm_cosultar_combos 3"
            );

            if (empty($results)) {
                Log::warning("[REGISTRO-MANIOBRAS] proc_pdm_cosultar_combos 3 no devolvió ningún resultado.");
                return [];
            }

            $row = $results[0];
            Log::info("[REGISTRO-MANIOBRAS] Respuesta raw de proc_pdm_cosultar_combos 3", ['row' => $row]);

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

            Log::info("[REGISTRO-MANIOBRAS] Tipos de maniobra obtenidos correctamente. Total: " . count($tipos));
            return $tipos;
        } catch (\Throwable $e) {
            Log::error("[REGISTRO-MANIOBRAS] Error en tiposManiobra: " . $e->getMessage(), ['exception' => $e]);
            return [];
        }
    }
}

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
        $context = session()->get('usuario_contexto');
        $isAm = ($context instanceof \App\Domain\Shared\UsuarioContexto && ($context->isAdministrador() || $context->tipo === 'AM'))
            || $rolUsuario === 'AM' || $zonaUsuario === 'TODAS' || $zonaUsuario === 'AM' || $zonaUsuario === '';

        $claveZona = $isAm ? '' : $zonaUsuario;

        Log::info("CONSULTA REAL A BD (SP): proc_pdm_obtener_maniobras_ejecutadas", [
            'claveZona' => $claveZona,
            'isAm' => $isAm,
            'fechaInicio' => $filtros['fechaInicio'] ?? null,
            'fechaFin' => $filtros['fechaFin'] ?? null,
            'rolUsuario' => $rolUsuario
        ]);

        try {
            DB::connection('maniobras')->statement("SET ANSI_NULLS ON");
            DB::connection('maniobras')->statement("SET ANSI_WARNINGS ON");

            $results = DB::connection('maniobras')->select(
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

            $response = $results[0];

            if (!isset($response->estado) || (int) $response->estado !== 0) {
                return [];
            }

            $rawLista = $response->listaManiobras ?? $response->listamaniobras ?? $response->LISTAMANIOBRAS ?? null;

            if (empty($rawLista)) {
                return [];
            }

            $maniobrasJson = is_string($rawLista) ? json_decode($rawLista, true) : $rawLista;
            if (!is_array($maniobrasJson)) {
                return [];
            }

            $sucursalRepo = app(\App\Domain\Shared\Repositories\SucursalRepositoryInterface::class);
            $zonaFiltro = $isAm ? 'TODAS' : $zonaUsuario;
            $almacenesMap = $sucursalRepo->listaPuntosDeVentaPorZona($zonaFiltro);

            $maniobras = [];
            foreach ($maniobrasJson as $item) {
                $item = (array) $item;
                $fechaRaw = $item['fecha'] ?? $item['fec_registro'] ?? date('Y-m-d');
                $fechaStr = substr($fechaRaw, 0, 10);
                $fecha = new \DateTimeImmutable($fechaStr);
                
                $almacenId = $item['idPuntoVenta'] ?? $item['IDPUNTOVENTA'] ?? '';

                if (!empty($filtros['almacenId'])) {
                    if ((string)$almacenId !== (string)$filtros['almacenId']) {
                        continue;
                    }
                }

                if (!empty($filtros['search'])) {
                    $search = mb_strtolower(trim($filtros['search']));
                    $match = false;
                    
                    if (mb_strpos(mb_strtolower($item['nombreManiobra'] ?? $item['NOMBREMANIOBRA'] ?? ''), $search) !== false) $match = true;
                    if (mb_strpos(mb_strtolower($item['nombreCuadrilla'] ?? $item['NOMBRECUADRILLA'] ?? ''), $search) !== false) $match = true;
                    if (mb_strpos(mb_strtolower($item['nombreLiderCuadrilla'] ?? $item['NOMBRELIDERCUADRILLA'] ?? ''), $search) !== false) $match = true;
                    
                    if (!$match) continue;
                }

                $estadoId = (int) ($item['estatus'] ?? $item['ESTATUS'] ?? 1);
                $corteId = isset($item['idCorte']) ? (int) $item['idCorte'] : (isset($item['IDCORTE']) ? (int) $item['IDCORTE'] : null);
                $idManiobra = (int) ($item['idManiobra'] ?? $item['IDMANIOBRA'] ?? 0);
                $folioFormatted = !empty($item['folio']) ? $item['folio'] : ($idManiobra > 0 ? 'MAN-' . str_pad((string)$idManiobra, 6, '0', STR_PAD_LEFT) : 'S/F');

                $nombrePuntoVenta = $item['nombrePuntoVenta'] ?? $item['NOMBREPUNTOVENTA'] ?? $item['nombreAlmacen'] ?? $item['NOMBREALMACEN'] ?? null;

                $dto = new RegistroManiobraDTO(
                    id: $idManiobra,
                    folio: $folioFormatted,
                    fecha: $fecha,
                    almacenId: $almacenId,
                    almacenNombre: $nombrePuntoVenta ?? ($almacenesMap[$almacenId] ?? $almacenId),
                    cuadrillaId: (int) ($item['idCuadrilla'] ?? $item['IDCUADRILLA'] ?? 0),
                    cuadrillaNombre: $item['nombreCuadrilla'] ?? $item['NOMBRECUADRILLA'] ?? '',
                    tipoManiobraId: (int) ($item['idTipoManiobra'] ?? $item['IDTIPOMANIOBRA'] ?? 0),
                    tipoManiobraNombre: $item['nombreManiobra'] ?? $item['NOMBREMANIOBRA'] ?? '',
                    toneladas: (float) ($item['numeroToneladas'] ?? $item['NUMEROTONELADAS'] ?? 0),
                    corteId: $corteId,
                    origen: $item['origen'] ?? $item['ORIGEN'] ?? 'APP',
                    estado: 'En proceso',
                    documentoSap: $item['numeroDocumentoSAP'] ?? $item['NUMERODOCUMENTOSAP'] ?? null
                );

                if (!empty($filtros['estado'])) {
                    $estadoCalculado = $corteId ? 'Liquidada' : 'En proceso';
                    if (mb_strtolower($estadoCalculado) !== mb_strtolower($filtros['estado'])) {
                        continue;
                    }
                }

                $maniobras[] = $dto;
            }

            return $maniobras;
        } catch (\Throwable $e) {
            Log::error("Error en RegistroManiobraRepository@list: " . $e->getMessage(), ['exception' => $e]);
            return [];
        }
    }

    public function create(ManiobraManualDTO $maniobra): string
    {
        $usuarioId = auth()->user()?->id ?? 0;
        $fechaString = $maniobra->fecha->format('Y-m-d H:i:s');

        Log::info("CONSULTA REAL A BD (SP): proc_pdm_administrar_maniobras_ejecutadas (Alta Manual)", [
            'tipoManiobraId' => $maniobra->tipoManiobraId,
            'almacenId' => $maniobra->almacenId,
            'cuadrillaId' => $maniobra->cuadrillaId,
            'documentoSap' => $maniobra->documentoSap,
            'toneladas' => $maniobra->toneladas,
            'usuarioId' => $usuarioId,
            'fecha' => $fechaString
        ]);

        try {
            DB::connection('maniobras')->statement("SET ANSI_NULLS ON");
            DB::connection('maniobras')->statement("SET ANSI_WARNINGS ON");

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
                throw new Exception("No se recibió respuesta de la base de datos.");
            }

            $response = $results[0];

            if (!isset($response->estado) || (int) $response->estado !== 0) {
                throw new Exception($response->mensaje ?? 'Error al registrar maniobra');
            }

            return $response->mensaje ?? 'Maniobra registrada correctamente.';
        } catch (\Throwable $e) {
            Log::error("Exception en RegistroManiobraRepository@create: " . $e->getMessage(), ['exception' => $e]);
            throw new Exception($e->getMessage());
        }
    }

    public function cuadrillasPorAlmacen(string $almacenId): array
    {
        try {
            DB::connection('maniobras')->statement("SET ANSI_NULLS ON");
            DB::connection('maniobras')->statement("SET ANSI_WARNINGS ON");

            $results = DB::connection('maniobras')->select(
                "EXEC proc_pdm_cosultar_combos 2, ?",
                [$almacenId]
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

            $cuadrillas = [];
            foreach ($comboData as $item) {
                $item = (array) $item;
                $codigo = (int) ($item['codigo'] ?? $item['CODIGO'] ?? 0);
                $nombre = trim($item['nombre'] ?? $item['NOMBRE'] ?? '');
                if ($codigo > 0) {
                    $cuadrillas[$codigo] = $nombre;
                }
            }

            return $cuadrillas;
        } catch (\Throwable $e) {
            Log::error("Error en RegistroManiobraRepository@cuadrillasPorAlmacen: " . $e->getMessage());
            return [];
        }
    }

    public function tiposManiobra(): array
    {
        try {
            DB::connection('maniobras')->statement("SET ANSI_NULLS ON");
            DB::connection('maniobras')->statement("SET ANSI_WARNINGS ON");

            $results = DB::connection('maniobras')->select(
                "EXEC proc_pdm_cosultar_combos 3"
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

            $tipos = [];
            foreach ($comboData as $item) {
                $item = (array) $item;
                $codigo = (int) ($item['codigo'] ?? $item['CODIGO'] ?? 0);
                $nombre = trim($item['nombre'] ?? $item['NOMBRE'] ?? '');
                if ($codigo > 0) {
                    $tipos[$codigo] = $nombre;
                }
            }

            return $tipos;
        } catch (\Throwable $e) {
            Log::error("Error en RegistroManiobraRepository@tiposManiobra: " . $e->getMessage());
            return [];
        }
    }
}

<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Corte\CorteRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CorteRepository implements CorteRepositoryInterface
{
    public function generar(string $zona, string $fechaInicio, string $fechaFin, bool $reemplazarBorrador = false): array
    {
        DB::connection('maniobras')->statement("SET ANSI_NULLS ON");
        DB::connection('maniobras')->statement("SET ANSI_WARNINGS ON");

        $results = DB::connection('maniobras')->select(
            "EXEC proc_pdm_corte_generar @Zona = ?, @FechaInicio = ?, @FechaFin = ?, @ReemplazarBorrador = ?",
            [$zona, $fechaInicio, $fechaFin, $reemplazarBorrador ? 1 : 0]
        );

        return $this->parsePdoResult($results, 'proc_pdm_corte_generar');
    }

    public function consultar(string $zona): ?array
    {
        DB::connection('maniobras')->statement("SET ANSI_NULLS ON");
        DB::connection('maniobras')->statement("SET ANSI_WARNINGS ON");

        $results = DB::connection('maniobras')->select(
            "EXEC proc_pdm_corte_consultar @Zona = ?",
            [$zona]
        );

        if (empty($results)) {
            Log::warning("[CORTE-LIQUIDACION] proc_pdm_corte_consultar did not return any row for zona: {$zona}");
            return null;
        }

        $row = (array) $results[0];

        if (isset($row['estatus']) && $row['estatus'] == 0 && !empty($row['resultado']) && $row['resultado'] !== '{}' && $row['resultado'] !== '[]') {
            $data = json_decode($row['resultado'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("[CORTE-LIQUIDACION] JSON decode error in proc_pdm_corte_consultar: " . json_last_error_msg());
            } else {
                Log::info("[CORTE-LIQUIDACION] proc_pdm_corte_consultar JSON decoded successfully", ['data_parsed' => $data]);
            }
            return $data;
        }

        return null;
    }

    public function confirmarCuadrilla(int $corteId, int $cuadrillaId): array
    {
        DB::connection('maniobras')->statement("SET ANSI_NULLS ON");
        DB::connection('maniobras')->statement("SET ANSI_WARNINGS ON");

        $results = DB::connection('maniobras')->select(
            "EXEC proc_pdm_corte_confirmar_cuadrilla @CorteID = ?, @CuadrillaID = ?",
            [$corteId, $cuadrillaId]
        );

        return $this->parsePdoResult($results, 'proc_pdm_corte_confirmar_cuadrilla');
    }

    public function confirmarGeneral(int $corteId): array
    {
        DB::connection('maniobras')->statement("SET ANSI_NULLS ON");
        DB::connection('maniobras')->statement("SET ANSI_WARNINGS ON");

        $results = DB::connection('maniobras')->select(
            "EXEC proc_pdm_corte_confirmar_general @CorteID = ?",
            [$corteId]
        );

        return $this->parsePdoResult($results, 'proc_pdm_corte_confirmar_general');
    }

    public function regenerar(int $corteId): array
    {
        DB::connection('maniobras')->statement("SET ANSI_NULLS ON");
        DB::connection('maniobras')->statement("SET ANSI_WARNINGS ON");

        $results = DB::connection('maniobras')->select(
            "EXEC proc_pdm_corte_regenerar @CorteID = ?",
            [$corteId]
        );

        return $this->parsePdoResult($results, 'proc_pdm_corte_regenerar');
    }

    public function eliminarBorrador(int $corteId): array
    {
        DB::connection('maniobras')->statement("SET ANSI_NULLS ON");
        DB::connection('maniobras')->statement("SET ANSI_WARNINGS ON");

        $results = DB::connection('maniobras')->select(
            "EXEC proc_pdm_corte_eliminar @CorteID = ?",
            [$corteId]
        );

        return $this->parsePdoResult($results, 'proc_pdm_corte_eliminar');
    }

    public function consultarCortePorId(int $corteId): array
    {
        try {
            DB::connection('maniobras')->statement("SET ANSI_NULLS ON");
            DB::connection('maniobras')->statement("SET ANSI_WARNINGS ON");

            $results = DB::connection('maniobras')->select(
                "EXEC proc_pdm_corte_consultar_por_id @CorteID = ?",
                [$corteId]
            );

            $parsed = $this->parsePdoResult($results, 'proc_pdm_corte_consultar_por_id');
            if (isset($parsed['estatus']) && $parsed['estatus'] === 0 && !empty($parsed['resultado'])) {
                return $parsed;
            }
        } catch (\Throwable $e) {
            Log::warning("[CORTE-LIQUIDACION] proc_pdm_corte_consultar_por_id error o 404: " . $e->getMessage());
        }

        // Fallback: Si proc_pdm_corte_consultar_por_id falla o no devuelve datos,
        // buscar el corte en la lista general de la zona del usuario.
        $context = session()->get('usuario_contexto');
        $zona = $context->zona ?? '';

        if ($zona) {
            Log::info("[CORTE-LIQUIDACION] Fallback a proc_pdm_corte_consultar para corteId: {$corteId} en zona: {$zona}");
            $cortes = $this->listarCortes($zona);
            foreach ($cortes as $c) {
                $idVal = (int) ($c['corteId'] ?? $c['id'] ?? 0);
                if ($idVal === $corteId) {
                    return [
                        'estatus' => 0,
                        'mensaje' => 'Consulta exitosa (vía fallback)',
                        'resultado' => $c
                    ];
                }
            }
        }

        return [
            'estatus' => 404,
            'mensaje' => 'No se encontró el corte solicitado.',
            'resultado' => []
        ];
    }

    private function parsePdoResult($results, string $spNombre = 'SP'): array
    {
        if (empty($results)) {
            Log::error("[CORTE-LIQUIDACION] [$spNombre] Stored Procedure returned no results.");
            throw new Exception("[$spNombre] El Stored Procedure no devolvió ningún resultado.");
        }

        $row = (array) $results[0];
        $normalized = [];
        foreach ($row as $k => $v) {
            $normalized[strtolower($k)] = $v;
        }

        Log::info("[CORTE-LIQUIDACION] [$spNombre] Parsing result: Estatus=" . ($normalized['estatus'] ?? 'NULL') . ", Mensaje=" . ($normalized['mensaje'] ?? 'NULL'));

        if (!isset($normalized['estatus'])) {
            Log::error("[CORTE-LIQUIDACION] [$spNombre] Malformed SP response: missing estatus.", ['row' => $normalized]);
            throw new Exception("[$spNombre] Respuesta malformada del SP: no contiene estatus.");
        }

        if ($normalized['estatus'] != 0 && $normalized['estatus'] != 409 && $normalized['estatus'] != 404) {
            Log::warning("[CORTE-LIQUIDACION] [$spNombre] SP returned business error: " . ($normalized['mensaje'] ?? 'Sin mensaje'));
            throw new Exception($normalized['mensaje'] ?? 'Error desconocido en la base de datos.');
        }

        $payload = [];
        if (!empty($normalized['resultado']) && $normalized['resultado'] !== '{}') {
            $payload = json_decode($normalized['resultado'], true) ?? [];
        }

        Log::info("[CORTE-LIQUIDACION] [$spNombre] Successfully processed.", [
            'estatus' => $normalized['estatus'],
            'mensaje' => $normalized['mensaje'],
            'payload' => $payload
        ]);

        return [
            'estatus' => $normalized['estatus'],
            'mensaje' => $normalized['mensaje'],
            'resultado' => $payload
        ];
    }

    public function listarCortes(string $zona): array
    {
        Log::info("[CORTE-LIQUIDACION] Listando cortes para zona: {$zona}");
        $corteData = $this->consultar($zona);
        if (!$corteData) {
            Log::info("[CORTE-LIQUIDACION] listarCortes: consultar returned null.");
            return [];
        }

        // Si el resultado contiene la llave 'cortes'
        if (isset($corteData['cortes']) && is_array($corteData['cortes'])) {
            Log::info("[CORTE-LIQUIDACION] listarCortes: found 'cortes' key array.", ['count' => count($corteData['cortes'])]);
            return $corteData['cortes'];
        }

        // Si ya es un array de cortes (lista indexada numéricamente)
        if (is_array($corteData) && isset($corteData[0])) {
            Log::info("[CORTE-LIQUIDACION] listarCortes: found indexed array of cortes.", ['count' => count($corteData)]);
            return $corteData;
        }

        // Si el resultado es un solo objeto de corte (contiene 'corteId' o 'id' o 'folio')
        if (is_array($corteData) && (isset($corteData['corteId']) || isset($corteData['id']) || isset($corteData['folio']))) {
            Log::info("[CORTE-LIQUIDACION] listarCortes: found single corte object, wrapping in array.", ['corte' => $corteData]);
            return [$corteData];
        }

        Log::warning("[CORTE-LIQUIDACION] listarCortes: corteData format unrecognized.", ['corteData' => $corteData]);
        return [];
    }

    public function obtenerUltimaFechaFinConfirmada(string $zona): ?string
    {
        Log::info("[CORTE-LIQUIDACION] Obtener última fecha fin confirmada para zona: {$zona}");
        $cortes = $this->listarCortes($zona);
        foreach ($cortes as $corte) {
            if (($corte['estado'] ?? '') === 'Confirmado' || ($corte['opc_estatus'] ?? 0) == 2) {
                $fechaFin = $corte['fechaFin'] ?? $corte['fec_fin'] ?? null;
                Log::info("[CORTE-LIQUIDACION] Última fecha fin confirmada encontrada: {$fechaFin}");
                return $fechaFin;
            }
        }

        Log::info("[CORTE-LIQUIDACION] No se encontró ninguna fecha fin confirmada para zona: {$zona}");
        return null;
    }
}

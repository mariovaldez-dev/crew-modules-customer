<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Corte\CorteRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CorteRepository implements CorteRepositoryInterface
{
    private function executeSp(string $spCall, array $params = [], string $spNombre = 'SP'): ?array
    {
        Log::info("[CORTE-LIQUIDACION] Executing $spNombre", ['call' => $spCall, 'params' => $params]);
        try {
            DB::connection('maniobras')->statement("SET ANSI_NULLS ON");
            DB::connection('maniobras')->statement("SET ANSI_WARNINGS ON");

            $pdo = DB::connection('maniobras')->getPdo();
            $stmt = $pdo->prepare($spCall);
            $stmt->execute($params);

            $row = null;
            do {
                $rawRow = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($rawRow) {
                    $normalized = [];
                    foreach ($rawRow as $k => $v) {
                        $normalized[strtolower($k)] = $v;
                    }
                    if (array_key_exists('estatus', $normalized) || array_key_exists('resultado', $normalized)) {
                        $row = $normalized;
                        break;
                    }
                }
            } while ($stmt->nextRowset());

            Log::info("[CORTE-LIQUIDACION] Raw row from $spNombre", ['row' => $row]);
            return $row;
        } catch (\Throwable $e) {
            Log::error("[CORTE-LIQUIDACION] Exception executing $spNombre: " . $e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }

    public function generar(string $zona, string $fechaInicio, string $fechaFin): array
    {
        $row = $this->executeSp(
            "EXEC proc_pdm_corte_generar @Zona = :zona, @FechaInicio = :inicio, @FechaFin = :fin",
            ['zona' => $zona, 'inicio' => $fechaInicio, 'fin' => $fechaFin],
            'proc_pdm_corte_generar'
        );

        return $this->parsePdoResult($row, 'proc_pdm_corte_generar');
    }

    public function consultar(string $zona): ?array
    {
        $row = $this->executeSp(
            "EXEC proc_pdm_corte_consultar @Zona = :zona",
            ['zona' => $zona],
            'proc_pdm_corte_consultar'
        );

        if (!$row) {
            Log::warning("[CORTE-LIQUIDACION] proc_pdm_corte_consultar did not return any row for zona: {$zona}");
            return null;
        }

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
        $row = $this->executeSp(
            "EXEC proc_pdm_corte_confirmar_cuadrilla @CorteID = :corte, @CuadrillaID = :cuadrilla",
            ['corte' => $corteId, 'cuadrilla' => $cuadrillaId],
            'proc_pdm_corte_confirmar_cuadrilla'
        );

        return $this->parsePdoResult($row, 'proc_pdm_corte_confirmar_cuadrilla');
    }

    public function confirmarGeneral(int $corteId): array
    {
        $row = $this->executeSp(
            "EXEC proc_pdm_corte_confirmar_general @CorteID = :corte",
            ['corte' => $corteId],
            'proc_pdm_corte_confirmar_general'
        );

        return $this->parsePdoResult($row, 'proc_pdm_corte_confirmar_general');
    }

    public function regenerar(int $corteId): array
    {
        $row = $this->executeSp(
            "EXEC proc_pdm_corte_regenerar @CorteID = :corte",
            ['corte' => $corteId],
            'proc_pdm_corte_regenerar'
        );

        return $this->parsePdoResult($row, 'proc_pdm_corte_regenerar');
    }

    public function consultarCortePorId(int $corteId): array
    {
        $row = $this->executeSp(
            "EXEC proc_pdm_corte_consultar_por_id @CorteID = :corteId",
            ['corteId' => $corteId],
            'proc_pdm_corte_consultar_por_id'
        );

        return $this->parsePdoResult($row, 'proc_pdm_corte_consultar_por_id');
    }

    private function parsePdoResult($row, string $spNombre = 'SP'): array
    {
        if (!$row) {
            Log::error("[CORTE-LIQUIDACION] [$spNombre] Stored Procedure returned no results.");
            throw new Exception("[$spNombre] El Stored Procedure no devolvió ningún resultado.");
        }

        Log::info("[CORTE-LIQUIDACION] [$spNombre] Parsing result: Estatus=" . ($row['estatus'] ?? 'NULL') . ", Mensaje=" . ($row['mensaje'] ?? 'NULL'));

        if (!isset($row['estatus'])) {
            Log::error("[CORTE-LIQUIDACION] [$spNombre] Malformed SP response: missing estatus.", ['row' => $row]);
            throw new Exception("[$spNombre] Respuesta malformada del SP: no contiene estatus.");
        }

        if ($row['estatus'] != 0) {
            Log::warning("[CORTE-LIQUIDACION] [$spNombre] SP returned business error: " . ($row['mensaje'] ?? 'Sin mensaje'));
            throw new Exception($row['mensaje'] ?? 'Error desconocido en la base de datos.');
        }

        $payload = [];
        if (!empty($row['resultado']) && $row['resultado'] !== '{}') {
            $payload = json_decode($row['resultado'], true) ?? [];
        }

        Log::info("[CORTE-LIQUIDACION] [$spNombre] Successfully processed.", [
            'estatus' => $row['estatus'],
            'mensaje' => $row['mensaje'],
            'payload' => $payload
        ]);

        return [
            'estatus' => $row['estatus'],
            'mensaje' => $row['mensaje'],
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

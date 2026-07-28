<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Corte\CorteRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Exception;

class CorteRepository implements CorteRepositoryInterface
{
    public function generar(string $zona, string $fechaInicio, string $fechaFin): array
    {
        $pdo = DB::connection('maniobras')->getPdo();
        $pdo->exec("SET ANSI_NULLS ON; SET ANSI_WARNINGS ON;");

        $stmt = $pdo->prepare("EXEC proc_pdm_corte_generar @Zona = :zona, @FechaInicio = :inicio, @FechaFin = :fin");
        $stmt->execute(['zona' => $zona, 'inicio' => $fechaInicio, 'fin' => $fechaFin]);
        
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $this->parsePdoResult($row);
    }

    public function consultar(string $zona): ?array
    {
        \Illuminate\Support\Facades\Log::info("Iniciando consultar corte para zona: {$zona}");
        
        try {
            $pdo = DB::connection('maniobras')->getPdo();
            $pdo->exec("SET ANSI_NULLS ON; SET ANSI_WARNINGS ON;");

            $stmt = $pdo->prepare("EXEC proc_pdm_corte_consultar @Zona = :zona");
            $stmt->execute(['zona' => $zona]);
            
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$row) {
                \Illuminate\Support\Facades\Log::warning("El SP no devolvió ningún resultado (PDO) para zona: {$zona}");
                return null;
            }

            \Illuminate\Support\Facades\Log::info("Resultado SP (PDO): estatus = " . ($row['estatus'] ?? 'NULL') . ", mensaje = " . ($row['mensaje'] ?? 'NULL'));
            
            if (isset($row['estatus']) && $row['estatus'] == 0 && !empty($row['resultado'])) {
                $data = json_decode($row['resultado'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    \Illuminate\Support\Facades\Log::error("Error decodificando JSON del SP: " . json_last_error_msg());
                } else {
                    \Illuminate\Support\Facades\Log::info("JSON decodificado correctamente.");
                }
                return $data;
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Excepción en CorteRepository::consultar: " . $e->getMessage());
        }

        return null;
    }

    public function confirmarCuadrilla(int $corteId, int $cuadrillaId): array
    {
        $pdo = DB::connection('maniobras')->getPdo();
        $pdo->exec("SET ANSI_NULLS ON; SET ANSI_WARNINGS ON;");

        $stmt = $pdo->prepare("EXEC proc_pdm_corte_confirmar_cuadrilla @CorteID = :corte, @CuadrillaID = :cuadrilla");
        $stmt->execute(['corte' => $corteId, 'cuadrilla' => $cuadrillaId]);
        
        return $this->parsePdoResult($stmt->fetch(\PDO::FETCH_ASSOC));
    }

    public function confirmarGeneral(int $corteId): array
    {
        $pdo = DB::connection('maniobras')->getPdo();
        $pdo->exec("SET ANSI_NULLS ON; SET ANSI_WARNINGS ON;");

        $stmt = $pdo->prepare("EXEC proc_pdm_corte_confirmar_general @CorteID = :corte");
        $stmt->execute(['corte' => $corteId]);
        
        return $this->parsePdoResult($stmt->fetch(\PDO::FETCH_ASSOC));
    }

    public function regenerar(int $corteId): array
    {
        $pdo = DB::connection('maniobras')->getPdo();
        $pdo->exec("SET ANSI_NULLS ON; SET ANSI_WARNINGS ON;");

        $stmt = $pdo->prepare("EXEC proc_pdm_corte_regenerar @CorteID = :corte");
        $stmt->execute(['corte' => $corteId]);
        
        return $this->parsePdoResult($stmt->fetch(\PDO::FETCH_ASSOC));
    }

    private function parsePdoResult($row): array
    {
        if (!$row) {
            throw new Exception("El Stored Procedure no devolvió ningún resultado.");
        }

        if (!isset($row['estatus'])) {
            throw new Exception("Respuesta malformada del SP: no contiene estatus.");
        }

        if ($row['estatus'] != 0) {
            throw new Exception($row['mensaje'] ?? 'Error desconocido en la base de datos.');
        }

        $payload = [];
        if (!empty($row['resultado']) && $row['resultado'] !== '{}') {
            $payload = json_decode($row['resultado'], true) ?? [];
        }

        return [
            'estatus' => $row['estatus'],
            'mensaje' => $row['mensaje'],
            'resultado' => $payload
        ];
    }

    public function listarCortes(string $zona): array
    {
        $corteData = $this->consultar($zona);
        if (!$corteData) {
            return [];
        }

        if (isset($corteData['cortes']) && is_array($corteData['cortes'])) {
            return $corteData['cortes'];
        }

        return is_array($corteData) && isset($corteData[0]) ? $corteData : [];
    }

    public function consultarCortePorId(int $corteId): array
    {
        $pdo = DB::connection('maniobras')->getPdo();
        $pdo->exec("SET NOCOUNT ON; SET ANSI_NULLS ON; SET ANSI_WARNINGS OFF;");

        $query = "EXEC proc_pdm_corte_consultar_por_id @CorteID = :corteId";

        $stmt = $pdo->prepare($query);
        $stmt->execute(['corteId' => $corteId]);
        
        $row = null;
        do {
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row) break;
        } while ($stmt->nextRowset());

        if (!$row) {
            throw new \Exception("Error al consultar el corte por ID.");
        }

        if ($row['estatus'] != 0) {
            throw new \Exception($row['mensaje']);
        }

        $payload = json_decode($row['resultado'], true) ?? [];

        \Illuminate\Support\Facades\Log::info("ConsultarCortePorId(Corte: $corteId) => Resultado DB: " . $row['resultado']);

        return [
            'estatus' => $row['estatus'],
            'mensaje' => $row['mensaje'],
            'resultado' => $payload
        ];
    }

    public function obtenerUltimaFechaFinConfirmada(string $zona): ?string
    {
        $cortes = $this->listarCortes($zona);
        foreach ($cortes as $corte) {
            if (($corte['estado'] ?? '') === 'Confirmado' || ($corte['opc_estatus'] ?? 0) == 2) {
                return $corte['fechaFin'] ?? $corte['fec_fin'] ?? null;
            }
        }

        return null;
    }
}

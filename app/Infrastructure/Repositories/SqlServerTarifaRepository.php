<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Tarifa\TarifaRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class SqlServerTarifaRepository implements TarifaRepositoryInterface
{
    public function consultarTarifas(?string $claveZona): array
    {
        try {
            // El SP proc_pdm_consultar_tarifas soporta parámetro para zona, 
            // si es Administrador se debe enviar vacío o no enviar el parámetro.
            // Para coordinadores (claveZona != null) se manda la clave de zona.
            
            Log::debug('[SqlServerTarifaRepository@consultarTarifas] Ejecutando SP', ['zona' => $claveZona]);
            
            // PDO requiere setear ANSI_NULLS y ANSI_WARNINGS antes del query
            DB::connection('localDB')->getPdo()->exec("SET ANSI_NULLS ON; SET ANSI_WARNINGS ON;");
            
            if ($claveZona) {
                // Coordinador
                $resultados = DB::connection('localDB')->select('EXEC proc_pdm_consultar_tarifas ?', [$claveZona]);
            } else {
                // Administrador
                $resultados = DB::connection('localDB')->select('EXEC proc_pdm_consultar_tarifas');
            }

            Log::debug('[SqlServerTarifaRepository@consultarTarifas] Resultados devueltos por DB::select', ['count' => count($resultados)]);

            if (empty($resultados)) {
                return [];
            }

            $row = (array) $resultados[0];
            
            if (isset($row['estado']) && (int)$row['estado'] !== 0) {
                $mensaje = $row['mensaje'] ?? 'Error desconocido devuelto por el SP';
                Log::error('[SqlServerTarifaRepository] Error en SP', ['estado' => $row['estado'], 'mensaje' => $mensaje]);
                throw new Exception($mensaje);
            }
            
            $jsonString = $row['listaCuadrillas'] ?? null;
            
            if (!$jsonString) {
                return [];
            }
            
            $datos = json_decode($jsonString, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('[SqlServerTarifaRepository@consultarTarifas] Error decodificando JSON', ['error' => json_last_error_msg(), 'json' => $jsonString]);
                throw new Exception("Error al procesar el formato de tarifas devuelto por el servidor.");
            }

            return $datos;

        } catch (Exception $e) {
            Log::error('[SqlServerTarifaRepository@consultarTarifas] Error SQL: ' . $e->getMessage());
            throw new Exception("Ocurrió un error al consultar las tarifas: " . $e->getMessage());
        }
    }
}

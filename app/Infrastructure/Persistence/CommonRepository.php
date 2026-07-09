<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Shared\Repositories\ICommonRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommonRepository implements ICommonRepository
{
    public function getCategoriasCultivo(): array
    {
        return $this->consultarCombo(1);
    }

    public function getTiposCicloCultivo(): array
    {
        return $this->consultarCombo(2);
    }

    public function getZonas(): array
    {
        return $this->consultarCombo(3);
    }

    public function getTerritorios(string $zonaCodigo): array
    {
        // El SP espera el código (ej. 'FN') como segundo parámetro (filtro)
        return $this->consultarCombo(4, $zonaCodigo);
    }

    public function getProductos(): array
    {
        return $this->consultarCombo(5);
    }

    private function consultarCombo(int $tipo, ?string $filtro = null, ?string $adicional = null): array
    {
        $cacheKey = "combo_{$tipo}_" . ($filtro ?? 'none') . "_" . ($adicional ?? 'none');

        // once() asegura que si se llama varias veces en la misma petición (ej. Index + Create + Edit), 
        // solo se ejecute la lógica una vez.
        return once(function () use ($tipo, $filtro, $adicional, $cacheKey) {
            try {
                // Cache por 1 hora para evitar golpes innecesarios a la BD de SAP/Linked Server
                return \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($tipo, $filtro, $adicional) {
                    Log::info("CONSULTA REAL A BD (SP): proc_bdc_cosultar_combos", ['tipo' => $tipo]);
                    
                    DB::statement("SET ANSI_NULLS ON");
                    DB::statement("SET ANSI_WARNINGS ON");

                    $results = DB::select("EXEC proc_bdc_cosultar_combos ?, ?, ?", [
                        $tipo, 
                        $filtro, 
                        $adicional
                    ]);

                    if (empty($results)) {
                        return [];
                    }

                    $response = $results[0];
                    
                    if (!isset($response->estado) || (int)$response->estado !== 0) {
                        return [];
                    }

                    if (empty($response->combo)) {
                        return [];
                    }

                    $comboData = json_decode($response->combo, true);
                    return is_array($comboData) ? $comboData : [];
                });
            } catch (Exception $e) {
                Log::error("Excepción en consultarCombo (tipo $tipo): " . $e->getMessage());
                return [];
            }
        });
    }
}

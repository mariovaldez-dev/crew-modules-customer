<?php

namespace App\Domain\Tarifa;

class ListTarifasUseCase
{
    public function __construct(
        private readonly TarifaRepositoryInterface $repository
    ) {}

    public function execute(string $zonaUsuario, bool $esAdministrador): array
    {
        if ($esAdministrador) {
            // El AM no envía zona y el SP le devuelve todas las zonas agrupadas.
            $resultados = $this->repository->consultarTarifas(null);
            
            $agrupadas = [];
            foreach ($resultados as $zonaData) {
                $nombreZona = $zonaData['nombreZona'] ?? $zonaData['claveZona'] ?? 'Desconocida';
                $agrupadas[$nombreZona] = $zonaData['listaCuadrillas'] ?? [];
            }
            
            return $agrupadas; // [ 'Zona Angostura' => [ ...cuadrillas ], 'Zona Guasave' => ... ]
        } else {
            // El CO solo ve su zona
            $resultados = $this->repository->consultarTarifas($zonaUsuario);
            
            if (!empty($resultados)) {
                return [
                    'claveZona' => $resultados[0]['claveZona'] ?? $zonaUsuario,
                    'nombreZona' => $resultados[0]['nombreZona'] ?? 'Zona ' . $zonaUsuario,
                    'cuadrillas' => $resultados[0]['listaCuadrillas'] ?? []
                ];
            }
            
            return [
                'claveZona' => $zonaUsuario,
                'nombreZona' => 'Zona ' . $zonaUsuario,
                'cuadrillas' => []
            ];
        }
    }
}

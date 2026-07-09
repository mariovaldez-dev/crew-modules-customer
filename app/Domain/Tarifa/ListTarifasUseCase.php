<?php

namespace App\Domain\Tarifa;

use App\Domain\Cuadrilla\CuadrillaRepositoryInterface;

class ListTarifasUseCase
{
    public function __construct(
        private readonly CuadrillaRepositoryInterface $repository
    ) {}

    public function execute(string $zonaUsuario, bool $esAdministrador): array
    {
        // En este Use Case, reutilizamos CuadrillaRepository para obtener las cuadrillas y sus tarifas.
        // Como mock, usaremos la lógica actual del MockCuadrillaRepository que de por sí filtra por zona si se lo pedimos.
        
        if ($esAdministrador) {
            // El administrador ve todas las zonas. Simularemos que pasamos null o pedimos todas.
            // Para el mock actual, $zonaUsuario se requiere en list(), así que temporalmente 
            // leeremos todas si el repositorio lo permite. En nuestro mock actual, el filtro de zona es estricto, 
            // así que para el AM mandaremos 'TODAS' y ajustaremos el mock.
            $cuadrillas = $this->repository->list([], 'TODAS');
            
            // Agrupar por zona
            $agrupadas = [];
            foreach ($cuadrillas as $cuadrilla) {
                $zona = $cuadrilla->zona;
                if (!isset($agrupadas[$zona])) {
                    $agrupadas[$zona] = [];
                }
                $agrupadas[$zona][] = $cuadrilla;
            }
            
            return $agrupadas; // [ 'ZONA-NORTE' => [ CuadrillaDTO... ], 'ZONA-SUR' => ... ]
        } else {
            // El CO solo ve su zona
            $cuadrillas = $this->repository->list([], $zonaUsuario);
            return $cuadrillas; // Array plano de CuadrillaDTO
        }
    }
}

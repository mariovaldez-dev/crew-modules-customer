<?php

namespace App\Domain\RegistroManiobra;

use Exception;

class ExportManiobrasUseCase
{
    public function __construct(
        private readonly RegistroManiobraRepositoryInterface $repository
    ) {}

    public function execute(array $filtros, string $zonaUsuario, string $rolUsuario): string
    {
        // En una implementación real, esto usaría Maatwebsite\Excel\Facades\Excel 
        // y retornaría la ruta o objeto de descarga.
        // Aquí simulamos que obtenemos la data y retornamos un mensaje o path ficticio.

        // Validar que exista al menos un filtro activo (fechas se aplican por defecto)
        if (empty($filtros['fechaInicio']) && empty($filtros['fechaFin']) && empty($filtros['almacenId']) && empty($filtros['estado']) && empty($filtros['search'])) {
            throw new Exception("Debe aplicar al menos un filtro para exportar.");
        }

        $maniobras = $this->repository->list($filtros, $zonaUsuario, $rolUsuario);

        if (count($maniobras) === 0) {
            throw new Exception("No existe información para exportar.");
        }

        // Simular éxito
        return "export_dummy_path.xlsx";
    }
}

<?php

namespace App\Domain\Dashboard;

use Carbon\Carbon;

class GetDashboardDataUseCase
{
    public function __construct(
        private readonly DashboardRepositoryInterface $repository
    ) {}

    public function execute(string $zonaUsuario, string $rolUsuario): array
    {
        // Regla de negocio: Si es Administrador de Maniobras (AM), se manda null para traer el global.
        // Si es Coordinador (CO), se manda su zona.
        $zonaParam = $rolUsuario === 'AM' ? null : $zonaUsuario;
        
        $data = $this->repository->getDashboardData($zonaParam);

        if (empty($data)) {
            return [];
        }

        // Fix para SQL Server: Si los sub-JSONs vienen como strings, los decodificamos
        if (isset($data['corteActual']) && is_string($data['corteActual'])) {
            $data['corteActual'] = json_decode($data['corteActual'], true);
        }
        if (isset($data['recientesRegistros']) && is_string($data['recientesRegistros'])) {
            $data['recientesRegistros'] = json_decode($data['recientesRegistros'], true);
        }

        // Mapear objetos anidados para que la capa de presentación los consuma más fácil
        if (!empty($data['corteActual']) && is_array($data['corteActual']) && isset($data['corteActual']['folio'])) {
            $corteActual = (object) $data['corteActual'];
            // Ajustamos las propiedades para compatibilidad con la vista
            $corteActual->cuadrillas = array_fill(0, $corteActual->totalCuadrillas ?? 0, 1);
            $data['corteActual'] = $corteActual;
        } else {
            // Si no hay folio o está vacío, lo forzamos a null para que la vista muestre "Sin borrador"
            $data['corteActual'] = null;
        }

        if (!empty($data['recientesRegistros']) && is_array($data['recientesRegistros'])) {
            $data['recientesRegistros'] = array_map(function($item) {
                $obj = (object)$item;
                $obj->fecha = Carbon::parse($obj->fecha ?? now());
                return $obj;
            }, $data['recientesRegistros']);
        } else {
            $data['recientesRegistros'] = [];
        }

        return $data;
    }
}

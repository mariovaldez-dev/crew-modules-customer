<?php

namespace App\Domain\Tarifa;

interface TarifaRepositoryInterface
{
    /**
     * Retorna las tarifas obtenidas desde el SP.
     * Si $claveZona es null, retorna de todas las zonas (para el Administrador).
     * Si se provee, retorna solo esa zona (para Coordinadora).
     *
     * @return array Estructura en crudo del JSON decodificado.
     */
    public function consultarTarifas(?string $claveZona): array;
}

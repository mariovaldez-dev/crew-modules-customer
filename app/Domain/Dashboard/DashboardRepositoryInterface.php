<?php

namespace App\Domain\Dashboard;

interface DashboardRepositoryInterface
{
    /**
     * Devuelve toda la información requerida por el Dashboard de un solo golpe.
     * 
     * @param string|null $zonaUsuario La clave de la zona (si es CO) o null/vacío (si es AM).
     * @return array
     */
    public function getDashboardData(?string $zonaUsuario): array;
}

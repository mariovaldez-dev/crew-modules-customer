<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Dashboard\DashboardRepositoryInterface;
use Illuminate\Support\Facades\DB;
use PDO;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function getDashboardData(?string $zonaUsuario): array
    {
        $zonaParam = ($zonaUsuario === 'TODAS' || $zonaUsuario === '') ? null : $zonaUsuario;

        $pdo = DB::connection('maniobras')->getPdo();
        $pdo->exec("SET ANSI_NULLS ON; SET ANSI_WARNINGS ON;");

        $stmt = $pdo->prepare("EXEC proc_pdm_dashboard_inicio :zona");
        $stmt->execute(['zona' => $zonaParam]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row && isset($row['estatus']) && $row['estatus'] == 0) {
            $json = $row['resultado'] ?? '{}';
            return json_decode($json, true) ?? [];
        }

        return [];
    }
}

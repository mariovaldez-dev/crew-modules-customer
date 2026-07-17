<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Dashboard\DashboardRepositoryInterface;
use Illuminate\Support\Facades\DB;
use PDO;

class SqlServerDashboardRepository implements DashboardRepositoryInterface
{
    public function getDashboardData(?string $zonaUsuario): array
    {
        $pdo = DB::connection('localDB')->getPdo();
        
        // Configurar los manejos de nulos por si acaso (como en Tarifas)
        $pdo->exec("SET ANSI_NULLS ON; SET ANSI_WARNINGS ON;");

        $stmt = $pdo->prepare("EXEC proc_pdm_dashboard_inicio :zona");
        $stmt->execute(['zona' => $zonaUsuario]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row && isset($row['estatus']) && $row['estatus'] == 0) {
            $json = $row['resultado'] ?? '{}';
            return json_decode($json, true) ?? [];
        }

        // En caso de error o que no venga el formato esperado
        return [];
    }
}

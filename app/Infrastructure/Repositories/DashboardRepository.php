<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Dashboard\DashboardRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PDO;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function getDashboardData(?string $zonaUsuario): array
    {
        $zonaKey = ($zonaUsuario === 'TODAS' || empty($zonaUsuario)) ? 'TODAS' : $zonaUsuario;

        return Cache::remember("dashboard_data_{$zonaKey}", 60, function () use ($zonaUsuario) {
            $zonaParam = ($zonaUsuario === 'TODAS' || empty($zonaUsuario)) ? null : $zonaUsuario;

            $pdo = DB::connection('maniobras')->getPdo();
            $stmt = $pdo->prepare("SET NOCOUNT ON; SET ANSI_NULLS ON; SET ANSI_WARNINGS ON; EXEC proc_pdm_dashboard_inicio :zona");
            $stmt->execute(['zona' => $zonaParam]);
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row && isset($row['estatus']) && $row['estatus'] == 0) {
                $json = $row['resultado'] ?? '{}';
                return json_decode($json, true) ?? [];
            }

            return [];
        });
    }
}

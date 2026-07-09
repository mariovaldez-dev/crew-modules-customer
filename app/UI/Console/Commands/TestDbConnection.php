<?php

namespace App\UI\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class TestDbConnection extends Command
{
    protected $signature = 'db:test';
    protected $description = 'Verifica la conexión a la base de datos';

    public function handle()
    {
        $this->info('Iniciando prueba de conexión...');
        try {
            DB::connection()->getPdo();
            $this->info('✅ Conexión exitosa.');

            $version = DB::select('SELECT @@VERSION as version');
            $this->line('Versión SQL: ' . $version[0]->version);
        } catch (\Exception $e) {
            Log::error($e->getTraceAsString());
            $this->error('❌ Error de conexión: ' . $e->getMessage());
        }
    }
}

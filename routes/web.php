<?php

use App\Domain\Corte\ConsultarCorteUseCase;
use App\Domain\Corte\GenerarPdfCorteUseCase;
use App\UI\Livewire\Auth\LoginForm;
use App\UI\Livewire\Corte\CorteDetalle;
use App\UI\Livewire\Corte\CorteIndex;
use App\UI\Livewire\Cuadrillas\CuadrillaIndex;
use App\UI\Livewire\Dashboard\DashboardIndex;
use App\UI\Livewire\Maniobras\ManiobraIndex;
use App\UI\Livewire\RegistroManiobras\RegistroIndex;
use App\UI\Livewire\Tarifas\TarifasIndex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas Módulo de Operaciones - Grupo Impulsora
|--------------------------------------------------------------------------
*/

// Rutas Públicas (Autenticación)
Route::get('/login', LoginForm::class)->name('login')->middleware('guest');

// Rutas Protegidas (Módulo de Operaciones: AM / CO)
Route::middleware(['auth', 'role.operaciones'])->group(function () {

    // Dashboard e Inicio
    Route::get('/dashboard', DashboardIndex::class)->name('dashboard');

    // Catálogo de Maniobras (Rol Administrador Maniobras)
    Route::get('/maniobras', ManiobraIndex::class)->name('maniobras.index');

    // Gestión de Cuadrillas (Filtro por Zona / PV)
    Route::get('/cuadrillas', CuadrillaIndex::class)->name('cuadrillas.index');

    // Registro e Histórico de Maniobras
    Route::get('/registro-maniobras', RegistroIndex::class)->name('registro-maniobras.index');

    // Consulta de Tarifas por Cuadrilla
    Route::get('/tarifas', TarifasIndex::class)->name('tarifas.index');

    // Cortes de Liquidación
    Route::get('/corte-liquidacion', CorteIndex::class)->name('corte-liquidacion.index');

    // Descarga / Impresión de Reporte PDF del Corte
    Route::get('/corte-liquidacion/pdf/{id}.pdf', function (int $id, Request $request, GenerarPdfCorteUseCase $useCase, ConsultarCorteUseCase $consultarUseCase) {
        try {
            $corte = $consultarUseCase->execute($id);
            if (!$corte) {
                abort(404, 'Corte de liquidación no encontrado');
            }

            $folio = $corte['folio'] ?? 'Borrador';
            $fechaInicio = \Carbon\Carbon::parse($corte['fechaInicio'])->format('d-m-Y');
            $fechaFin = \Carbon\Carbon::parse($corte['fechaFin'])->format('d-m-Y');
            $cleanFolio = preg_replace('/[^A-Za-z0-9_\-]/', '_', $folio);
            $filename = "corte_{$cleanFolio}_{$fechaInicio}_al_{$fechaFin}.pdf";

            $pdfContent = $useCase->execute($id);

            $dispositionType = $request->boolean('download') ? 'attachment' : 'inline';

            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => $dispositionType . '; filename="' . $filename . '"',
                'Content-Length' => (string) strlen($pdfContent),
                'Cache-Control' => 'private, must-revalidate, max-age=0',
                'Pragma' => 'public',
            ]);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }
    })->where('id', '[0-9]+')->name('corte-liquidacion.pdf');

    Route::get('/corte-liquidacion/{id}', CorteDetalle::class)->where('id', '[0-9]+')->name('corte-liquidacion.detalle');

});

// Cierre de Sesión
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->name('logout')->middleware('auth');

// Redirección Raíz
Route::get('/', function () {
    return redirect()->route('login');
});

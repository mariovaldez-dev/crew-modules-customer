<?php

use App\UI\Livewire\Auth\LoginForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Pública
Route::get('/login', LoginForm::class)->name('login')->middleware('guest');

// Protegidas (Solo Módulo Operaciones)
Route::middleware(['auth', 'role.operaciones'])->group(function () {
    // Menú Principal (Tarjetas)
    Route::get('/dashboard', \App\UI\Livewire\Dashboard\DashboardIndex::class)->name('dashboard');

    // Catálogo de Maniobras
    Route::get('/maniobras', \App\UI\Livewire\Maniobras\ManiobraIndex::class)->name('maniobras.index');

    // Gestión de Cuadrillas
    Route::get('/cuadrillas', \App\UI\Livewire\Cuadrillas\CuadrillaIndex::class)->name('cuadrillas.index');

    // Registro de Maniobras
    Route::get('/registro-maniobras', \App\UI\Livewire\RegistroManiobras\RegistroIndex::class)->name('registro-maniobras.index');

    // Tarifas
    Route::get('/tarifas', \App\UI\Livewire\Tarifas\TarifasIndex::class)->name('tarifas.index');

    // Corte de Liquidación
    Route::get('/corte-liquidacion', \App\UI\Livewire\Corte\CorteIndex::class)->name('corte-liquidacion.index');
    Route::get('/corte-liquidacion/{id}', \App\UI\Livewire\Corte\CorteDetalle::class)->name('corte-liquidacion.detalle');
    
    Route::get('/corte-liquidacion/pdf/{id}', function (int $id, \App\Domain\Corte\GenerarPdfCorteUseCase $useCase, \App\Domain\Corte\ConsultarCorteUseCase $consultarUseCase) {
        try {
            $corte = $consultarUseCase->execute($id);
            if (!$corte) {
                abort(404, 'Corte no encontrado');
            }
            
            $folio = $corte['folio'] ?? 'Borrador';
            $fechaInicio = \Carbon\Carbon::parse($corte['fechaInicio'])->format('d-m-Y');
            $fechaFin = \Carbon\Carbon::parse($corte['fechaFin'])->format('d-m-Y');
            $filename = "{$folio}_{$fechaInicio}_al_{$fechaFin}.pdf";
            
            $pdfContent = $useCase->execute($id);
            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$filename.'"'
            ]);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }
    })->name('corte-liquidacion.pdf');
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->name('logout')->middleware('auth');

Route::get('/', function () {
    return redirect()->route('login');
});

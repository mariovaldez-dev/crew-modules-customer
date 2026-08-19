<?php

namespace Tests\Feature\RegistroManiobras;

use App\Domain\Cuadrilla\CuadrillaDTO;
use App\Domain\Cuadrilla\CuadrillaRepositoryInterface;
use App\Domain\Cuadrilla\TarifasManiobra;
use App\Domain\RegistroManiobra\CreateManiobraManualUseCase;
use App\Domain\RegistroManiobra\RegistroManiobraRepositoryInterface;
use App\UI\Livewire\RegistroManiobras\NuevaManiobraForm;
use Livewire\Livewire;
use Tests\TestCase;

class NuevaManiobraFormTest extends TestCase
{
    public function test_muestra_alerta_cuando_tarifa_es_cero()
    {
        $cuadrillaRepo = $this->createMock(CuadrillaRepositoryInterface::class);
        $cuadrillaRepo->method('findById')->willReturn(
            new CuadrillaDTO(
                id: 1,
                nombre: 'Cuadrilla Test',
                lider: 'Líder Test',
                miembros: 5,
                puntoVentaId: 'PV01',
                zona: 'ZONA1',
                tarifas: TarifasManiobra::fromDynamic([1 => 0.0])
            )
        );
        $this->app->instance(CuadrillaRepositoryInterface::class, $cuadrillaRepo);

        Livewire::test(NuevaManiobraForm::class)
            ->set('fecha', date('Y-m-d'))
            ->set('almacenId', 'PV01')
            ->set('cuadrillaId', '1')
            ->set('tipoManiobraId', '1')
            ->set('toneladas', '10.5')
            ->call('save')
            ->assertDispatched('open-modal', 'confirmar-maniobra-cero-modal');
    }

    public function test_permite_guardar_maniobra_con_tarifa_cero_tras_confirmacion()
    {
        $cuadrillaRepo = $this->createMock(CuadrillaRepositoryInterface::class);
        $cuadrillaRepo->method('findById')->willReturn(
            new CuadrillaDTO(
                id: 1,
                nombre: 'Cuadrilla Test',
                lider: 'Líder Test',
                miembros: 5,
                puntoVentaId: 'PV01',
                zona: 'ZONA1',
                tarifas: TarifasManiobra::fromDynamic([1 => 0.0])
            )
        );
        $this->app->instance(CuadrillaRepositoryInterface::class, $cuadrillaRepo);

        $registroRepo = $this->createMock(RegistroManiobraRepositoryInterface::class);
        $registroRepo->method('cuadrillasPorAlmacen')->willReturn([1 => 'Cuadrilla Test']);
        $registroRepo->expects($this->once())
            ->method('create')
            ->willReturn('Maniobra registrada correctamente.');
        $this->app->instance(RegistroManiobraRepositoryInterface::class, $registroRepo);

        Livewire::test(NuevaManiobraForm::class)
            ->set('fecha', date('Y-m-d'))
            ->set('almacenId', 'PV01')
            ->set('cuadrillaId', '1')
            ->set('tipoManiobraId', '1')
            ->set('toneladas', '10.5')
            ->call('confirmarSaveZero')
            ->assertDispatched('notify')
            ->assertDispatched('maniobra-registrada');
    }
}

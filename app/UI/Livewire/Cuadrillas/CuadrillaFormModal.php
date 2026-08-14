<?php

namespace App\UI\Livewire\Cuadrillas;

use App\Domain\Cuadrilla\CreateCuadrillaUseCase;
use App\Domain\Cuadrilla\TarifasManiobra;
use App\Domain\Cuadrilla\UpdateCuadrillaUseCase;
use App\Domain\Maniobra\ManiobraRepositoryInterface;
use App\Domain\Shared\Repositories\SucursalRepositoryInterface;
use App\UI\Livewire\Traits\WithZonaScope;
use Exception;
use Livewire\Component;

class CuadrillaFormModal extends Component
{
    use WithZonaScope;

    public bool $isOpen = false;

    public ?int $cuadrillaId = null;
    public string $nombre = '';
    public string $lider = '';
    public int $miembros = 1;
    public string $puntoVentaId = '';   // WhsCode (ej. 'ANGOS02')

    // Puntos de venta (cargados de forma lazy via wire:init)
    public array $puntosVenta = [];
    public bool $pvLoaded = false;

    // Maniobras dinámicas cargadas del SP [id => nombre]
    public array $maniobras = [];
    // Tarifas dinámicas [idTipoManiobra => valorString]
    public array $tarifas = [];
    public array $tarifasOriginales = [];
    public bool $maniobrasLoaded = false;

    protected $listeners = [
        'open-cuadrilla-modal' => 'openModal'
    ];

    public function openModal($cuadrilla = null): void
    {
        $this->resetValidation();
        $this->reset([
            'cuadrillaId', 'nombre', 'lider', 'miembros', 'puntoVentaId', 'tarifasOriginales',
        ]);

        // Si el wire:init no ha corrido, forzamos la carga síncrona para tener las maniobras listas antes de mapear
        if (!$this->maniobrasLoaded || !$this->pvLoaded) {
            $this->loadData(
                app(SucursalRepositoryInterface::class),
                app(ManiobraRepositoryInterface::class)
            );
        }

        // Inicializar tarifas vacías respetando las maniobras ya cargadas
        $this->tarifas = $this->buildEmptyTarifas();

        if ($cuadrilla) {
            $this->cuadrillaId  = $cuadrilla['id'] ?? null;
            $this->nombre       = $cuadrilla['nombre'] ?? '';
            $this->lider        = $cuadrilla['lider'] ?? '';
            $this->miembros     = $cuadrilla['miembros'] ?? 1;
            $this->puntoVentaId = $cuadrilla['puntoVentaId'] ?? '';

            // Rellenar las tarifas existentes sobre el mapa vacío y registrar las originales
            $tarifasExistentes = $cuadrilla['tarifas'] ?? [];
            foreach ($tarifasExistentes as $key => $valor) {
                // Si el modal ya cargó maniobras y la clave es un id numérico
                if (is_numeric($key) && array_key_exists($key, $this->tarifas)) {
                    $this->tarifas[$key] = $valor !== null ? (string) $valor : '';
                    $this->tarifasOriginales[(int) $key] = $valor !== null ? (float) $valor : null;
                }
            }
        }

        $this->dispatch('open-modal', 'cuadrilla-modal');
    }

    /**
     * Carga PV y maniobras en un solo wire:init.
     */
    public function loadData(
        SucursalRepositoryInterface $sucursalRepo,
        ManiobraRepositoryInterface $maniobraRepo
    ): void {
        if (!$this->pvLoaded) {
            $this->puntosVenta = $sucursalRepo->listaPuntosDeVentaPorZona($this->zonaUsuario);
            $this->pvLoaded    = true;
        }

        if (!$this->maniobrasLoaded) {
            // Obtener maniobras activas y construir [id => nombre]
            $dtos = $maniobraRepo->list();
            $this->maniobras = [];
            foreach ($dtos as $dto) {
                // Solo maniobras activas
                if (strtolower($dto->estatus) === 'activo') {
                    $this->maniobras[$dto->id] = $dto->nombre;
                }
            }
            // Inicializar el mapa de tarifas vacío con todos los ids
            $this->tarifas          = $this->buildEmptyTarifas();
            $this->maniobrasLoaded  = true;
        }
    }

    private function parseTarifa($val): float
    {
        if ($val === '' || $val === null) return 0.0;
        return (float) $val;
    }

    /**
     * Construye el mapa de tarifas vacío usando las maniobras ya cargadas.
     */
    private function buildEmptyTarifas(): array
    {
        $empty = [];
        foreach (array_keys($this->maniobras) as $id) {
            $empty[$id] = '';
        }
        return $empty;
    }

    public function save(CreateCuadrillaUseCase $createUseCase, UpdateCuadrillaUseCase $updateUseCase): void
    {
        // Reglas dinámicas de validación para cada tarifa
        $tarifaRules = [];
        foreach (array_keys($this->maniobras) as $id) {
            $tarifaRules["tarifas.$id"] = 'nullable|numeric|min:0|max:9999999999.99|regex:/^\d{1,10}(\.\d{1,2})?$/';
        }

        $this->validate(array_merge([
            'nombre'       => 'required|max:100',
            'lider'        => 'required|max:100',
            'miembros'     => 'required|integer|min:1',
            'puntoVentaId' => 'required|string|max:20',
        ], $tarifaRules), $this->messages());

        try {
            // Construir el mapa dinámico [idTipo => float|null]
            $dynamicMap = [];
            foreach ($this->tarifas as $id => $valor) {
                $valorFloat = $this->parseTarifa($valor);

                if ($this->cuadrillaId) {
                    // Si es edición, solo se colocan las que se modificaron
                    $valorOriginal = $this->tarifasOriginales[(int) $id] ?? null;
                    if ($valorFloat !== $valorOriginal) {
                        $dynamicMap[(int) $id] = $valorFloat;
                    }
                } else {
                    // Si es nueva, solo las que tengan algún valor asignado
                    if ($valorFloat !== null) {
                        $dynamicMap[(int) $id] = $valorFloat;
                    }
                }
            }

            $tarifasVO = TarifasManiobra::fromDynamic($dynamicMap);

            if ($this->cuadrillaId) {
                $usuarioId = (string) (auth()->user()?->id ?? 0);

                $updateUseCase->execute(
                    id:          $this->cuadrillaId,
                    nombre:      $this->nombre,
                    lider:       $this->lider,
                    miembros:    $this->miembros,
                    puntoVentaId: $this->puntoVentaId,
                    zona:        $this->zonaUsuario,
                    tarifas:     $tarifasVO,
                    usuarioId:   $usuarioId
                );
                $this->dispatch('notify', ['message' => 'Cuadrilla actualizada correctamente', 'type' => 'success']);
            } else {
                $createUseCase->execute(
                    nombre:      $this->nombre,
                    lider:       $this->lider,
                    miembros:    $this->miembros,
                    puntoVentaId: $this->puntoVentaId,
                    zona:        $this->zonaUsuario,
                    tarifas:     $tarifasVO
                );
                $this->dispatch('notify', ['message' => 'Cuadrilla creada correctamente', 'type' => 'success']);
            }

            $this->dispatch('close-modal', 'cuadrilla-modal');
            $this->dispatch('cuadrilla-saved');

        } catch (Exception $e) {
            $this->addError('form', $e->getMessage());
        }
    }

    protected function messages(): array
    {
        $messages = [
            'nombre.required'       => 'El nombre de la cuadrilla es obligatorio.',
            'nombre.max'            => 'El nombre no debe exceder 100 caracteres.',
            'lider.required'        => 'El nombre del líder es obligatorio.',
            'lider.max'             => 'El nombre del líder no debe exceder 100 caracteres.',
            'miembros.required'     => 'El número de miembros es obligatorio.',
            'miembros.integer'      => 'El número de miembros debe ser un número entero.',
            'miembros.min'          => 'La cuadrilla debe tener al menos 1 miembro.',
            'puntoVentaId.required' => 'El punto de venta es obligatorio.',
            'puntoVentaId.max'      => 'El punto de venta no es válido.',
        ];

        foreach (array_keys($this->maniobras) as $id) {
            $messages["tarifas.$id.numeric"] = 'La tarifa debe ser un número válido.';
            $messages["tarifas.$id.min"]     = 'La tarifa no puede ser negativa.';
            $messages["tarifas.$id.regex"]   = 'La tarifa solo acepta hasta 10 enteros y 2 decimales.';
            $messages["tarifas.$id.max"]     = 'La tarifa excede el valor máximo permitido.';
        }

        return $messages;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.cuadrillas.cuadrilla-form-modal', [
            'puntosVenta' => $this->puntosVenta,
            'maniobras'   => $this->maniobras,
            'tarifas'     => $this->tarifas,
        ]);
    }
}

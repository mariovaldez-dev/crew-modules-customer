<?php

namespace App\Domain\RegistroManiobra;

class ListRegistroManiobrasUseCase
{
    public function __construct(
        private readonly RegistroManiobraRepositoryInterface $repository
    ) {}

    /**
     * @return RegistroManiobraDTO[]
     */
    public function execute(array $filtros, string $zonaUsuario, string $rolUsuario): array
    {
        $maniobras = $this->repository->list($filtros, $zonaUsuario, $rolUsuario);

        // El estado se calcula dinámicamente:
        // Si la maniobra pertenece a un corte confirmado (corteId no nulo), su estado es 'Liquidada'.
        // De lo contrario es 'En proceso'.
        // Ya que el DTO es inmutable en su mayoría, podemos modificar la propiedad 'estado' si no la hicimos readonly
        // o crear un clon. La hice mutable en el DTO para este propósito.

        foreach ($maniobras as $maniobra) {
            $maniobra->estado = ($maniobra->corteId > 0 && $maniobra->estatusCorte === 1) ? 'Liquidada' : 'En proceso';
        }

        return $maniobras;
    }
}

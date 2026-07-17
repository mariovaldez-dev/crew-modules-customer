<?php

namespace App\Domain\Cuadrilla;

/**
 * Value Object que representa las tarifas por tipo de maniobra de una cuadrilla.
 * El mapa es dinámico: [idTipoManiobra (int) => tarifa (?float)]
 */
class TarifasManiobra
{
    /**
     * @param array<int, float|null> $dynamic  [idTipoManiobra => tarifa]
     */
    public function __construct(
        public readonly array $dynamic = []
    ) {
        $this->validateNoNegativos();
    }

    /**
     * Named constructor semántico para mayor claridad en los call sites.
     *
     * @param array<int, float|null> $tarifasMap
     */
    public static function fromDynamic(array $tarifasMap): self
    {
        return new self(dynamic: $tarifasMap);
    }

    private function validateNoNegativos(): void
    {
        foreach ($this->dynamic as $id => $valor) {
            if ($valor !== null && (float) $valor < 0) {
                throw new \InvalidArgumentException(
                    "La tarifa para tipo de maniobra #$id no puede ser negativa."
                );
            }
        }
    }

    public function toArray(): array
    {
        return $this->dynamic;
    }
}

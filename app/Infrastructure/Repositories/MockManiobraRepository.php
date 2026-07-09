<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Maniobra\ManiobraDTO;
use App\Domain\Maniobra\ManiobraRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class MockManiobraRepository implements ManiobraRepositoryInterface
{
    private function getData(): array
    {
        return Cache::remember('mock_maniobras', 3600, function () {
            return [
                1 => new ManiobraDTO(1, 'Carga 25kg', 'Maniobra estándar de carga manual', 'A'),
                2 => new ManiobraDTO(2, 'Descarga 50kg', 'Descarga pesada', 'A'),
                3 => new ManiobraDTO(3, 'Traslado Interno', 'Movimiento entre bodegas', 'A'),
                4 => new ManiobraDTO(4, 'Apaleo', 'Acomodo de grano', 'I'),
            ];
        });
    }

    private function saveData(array $data): void
    {
        Cache::put('mock_maniobras', $data, 3600);
    }

    public function list(?string $search = null): array
    {
        $data = $this->getData();

        if (!$search) {
            return array_values($data);
        }

        $filtered = array_filter($data, function (ManiobraDTO $item) use ($search) {
            return stripos($item->nombre, $search) !== false;
        });

        return array_values($filtered);
    }

    public function create(ManiobraDTO $maniobra): ManiobraDTO
    {
        $data = $this->getData();
        $newId = count($data) > 0 ? max(array_keys($data)) + 1 : 1;
        
        $newDto = new ManiobraDTO(
            id: $newId,
            nombre: $maniobra->nombre,
            descripcion: $maniobra->descripcion,
            estatus: $maniobra->estatus
        );

        $data[$newId] = $newDto;
        $this->saveData($data);

        return $newDto;
    }

    public function update(int $id, ManiobraDTO $maniobra): ManiobraDTO
    {
        $data = $this->getData();
        $data[$id] = new ManiobraDTO(
            id: $id,
            nombre: $maniobra->nombre,
            descripcion: $maniobra->descripcion,
            estatus: $maniobra->estatus
        );
        $this->saveData($data);

        return $data[$id];
    }

    public function existsByNombre(string $nombre, ?int $excludeId = null): bool
    {
        foreach ($this->getData() as $id => $item) {
            if ($excludeId !== null && $id === $excludeId) {
                continue;
            }
            if (strtolower($item->nombre) === strtolower($nombre)) {
                return true;
            }
        }
        return false;
    }
}

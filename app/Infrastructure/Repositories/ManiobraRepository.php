<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Maniobra\ManiobraDTO;
use App\Domain\Maniobra\ManiobraRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class ManiobraRepository implements ManiobraRepositoryInterface
{
    /**
     * @return ManiobraDTO[]
     */
    public function list(?string $search = null): array
    {
        try {
            Log::info("CONSULTA REAL A BD (SP): proc_consultar_tipos_maniobras");

            DB::connection('maniobras')->statement("SET ANSI_NULLS ON");
            DB::connection('maniobras')->statement("SET ANSI_WARNINGS ON");

            $results = DB::connection('maniobras')->select(
                "EXEC proc_consultar_tipos_maniobras"
            );

            if (empty($results)) {
                return [];
            }

            $response = $results[0];

            if (!isset($response->estado) || (int) $response->estado !== 0) {
                return [];
            }

            $rawLista = $response->listaTipoManiobras ?? $response->listatipomaniobras ?? $response->LISTATIPOMANIOBRAS ?? null;

            if (empty($rawLista)) {
                return [];
            }

            $maniobrasJson = is_string($rawLista) ? json_decode($rawLista, true) : $rawLista;
            if (!is_array($maniobrasJson)) {
                return [];
            }

            $maniobras = [];
            foreach ($maniobrasJson as $item) {
                $itemArray = (array) $item;
                $dto = new ManiobraDTO(
                    id:          (int) ($itemArray['idTipoManiobra'] ?? $itemArray['IDTIPOMANIOBRA'] ?? 0),
                    nombre:      (string) ($itemArray['nombreTipoManiobra'] ?? $itemArray['NOMBRETIPOMANIOBRA'] ?? ''),
                    descripcion: $itemArray['descripcionTipoManiobra'] ?? $itemArray['DESCRIPCIONTIPOMANIOBRA'] ?? null,
                    estatus:     (string) ($itemArray['estatus'] ?? $itemArray['ESTATUS'] ?? 'Activo')
                );

                if ($search !== null && $search !== '') {
                    if (stripos($dto->nombre, $search) === false &&
                        stripos($dto->descripcion ?? '', $search) === false
                    ) {
                        continue;
                    }
                }

                $maniobras[] = $dto;
            }

            return $maniobras;
        } catch (\Throwable $e) {
            Log::error('Error en ManiobraRepository@list', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function create(ManiobraDTO $maniobra): ManiobraDTO
    {
        try {
            $usuarioId = (int) (auth()->user()?->id ?? 0);

            DB::connection('maniobras')->statement("SET ANSI_NULLS ON");
            DB::connection('maniobras')->statement("SET ANSI_WARNINGS ON");

            $results = DB::connection('maniobras')->select(
                "EXEC proc_pdm_administrar_tipos_maniobras
                    @Opcion = 1,
                    @NombreManiobra = ?,
                    @DescripcionManiobra = ?,
                    @Usuario = ?",
                [
                    $maniobra->nombre,
                    $maniobra->descripcion ?? '',
                    $usuarioId,
                ]
            );

            if (empty($results)) {
                throw new Exception('No se recibió respuesta de la base de datos.');
            }

            $response = $results[0];
            if (!isset($response->estado) || (int) $response->estado !== 0) {
                throw new Exception($response->mensaje ?? 'Error al crear la maniobra');
            }

            $idGenerado = isset($response->idTipoManiobra) ? (int) $response->idTipoManiobra : (isset($response->id) ? (int) $response->id : null);

            return new ManiobraDTO(
                id:          $idGenerado,
                nombre:      $maniobra->nombre,
                descripcion: $maniobra->descripcion,
                estatus:     'Activo'
            );
        } catch (\Throwable $e) {
            Log::error('Error en ManiobraRepository@create', ['error' => $e->getMessage()]);
            throw new Exception('Error al crear la maniobra: ' . $e->getMessage());
        }
    }

    public function update(int $id, ManiobraDTO $maniobra): ManiobraDTO
    {
        try {
            $usuarioId = (int) (auth()->user()?->id ?? 0);
            $estatusBit = $this->estatusToBit($maniobra->estatus);

            DB::connection('maniobras')->statement("SET ANSI_NULLS ON");
            DB::connection('maniobras')->statement("SET ANSI_WARNINGS ON");

            $results = DB::connection('maniobras')->select(
                "EXEC proc_pdm_administrar_tipos_maniobras
                    @Opcion = 2,
                    @IdTipoManiobra = ?,
                    @NombreManiobra = ?,
                    @DescripcionManiobra = ?,
                    @Estatus = ?,
                    @Usuario = ?",
                [
                    $id,
                    $maniobra->nombre,
                    $maniobra->descripcion ?? '',
                    $estatusBit,
                    $usuarioId,
                ]
            );

            if (empty($results)) {
                throw new Exception('No se recibió respuesta de la base de datos.');
            }

            $response = $results[0];
            if (!isset($response->estado) || (int) $response->estado !== 0) {
                throw new Exception($response->mensaje ?? 'Error al actualizar maniobra');
            }

            return new ManiobraDTO(
                id:          $id,
                nombre:      $maniobra->nombre,
                descripcion: $maniobra->descripcion,
                estatus:     $maniobra->estatus
            );
        } catch (\Throwable $e) {
            Log::error('Error en ManiobraRepository@update', ['error' => $e->getMessage()]);
            throw new Exception('Error al actualizar la maniobra: ' . $e->getMessage());
        }
    }

    private function estatusToBit(string $estatus): int
    {
        return strtolower($estatus) === 'activo' ? 1 : 0;
    }

    public function existsByNombre(string $nombre, ?int $excludeId = null): bool
    {
        try {
            $maniobras = $this->list($nombre);
            foreach ($maniobras as $m) {
                if ($excludeId !== null && $m->id === $excludeId) {
                    continue;
                }
                if (strtolower(trim($m->nombre)) === strtolower(trim($nombre))) {
                    return true;
                }
            }
            return false;
        } catch (\Throwable $e) {
            Log::error('Error en ManiobraRepository@existsByNombre', ['error' => $e->getMessage()]);
            return false;
        }
    }
}

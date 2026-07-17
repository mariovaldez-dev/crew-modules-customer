<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Maniobra\ManiobraDTO;
use App\Domain\Maniobra\ManiobraRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class SqlServerManiobraRepository implements ManiobraRepositoryInterface
{
    /**
     * @return ManiobraDTO[]
     */
    public function list(?string $search = null): array
    {
        try {
            // El SP no acepta parámetros; el filtro de búsqueda se aplica en PHP
            $results = DB::connection('localDB')->select(
                "EXEC proc_consultar_tipos_maniobras"
            );

            if (empty($results)) {
                return [];
            }

            $row = $results[0];
            if ((int) $row->estado !== 0) {
                // -100 = sin registros, cualquier otro valor = error
                return [];
            }

            $maniobrasJson = json_decode($row->listaTipoManiobras, true);
            if (!is_array($maniobrasJson)) {
                return [];
            }

            $maniobras = [];
            foreach ($maniobrasJson as $item) {
                $dto = new ManiobraDTO(
                    id:          (int) $item['idTipoManiobra'],
                    nombre:      $item['nombreTipoManiobra'],
                    descripcion: $item['descripcionTipoManiobra'] ?? null,
                    estatus:     $item['estatus']  // "Activo" | "inactivo" tal como devuelve el SP
                );

                // Filtro de búsqueda en memoria (nombre o descripción)
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
        } catch (Exception $e) {
            Log::error('Error en SqlServerManiobraRepository@list', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function create(ManiobraDTO $maniobra): ManiobraDTO
    {
        try {
            $usuarioId = (int) (auth()->user()?->id ?? 0);

            $results = DB::connection('localDB')->select(
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

            $row = $results[0];
            if ((int) $row->estado !== 0) {
                throw new Exception($row->mensaje);
            }

            // El SP no devuelve el ID generado; lo recuperamos por nombre
            $nuevo = DB::connection('localDB')->selectOne(
                "SELECT TOP 1 idu_tipomaniobra FROM cat_pdm_tipos_maniobras
                  WHERE nom_maniobra = ? ORDER BY idu_tipomaniobra DESC",
                [$maniobra->nombre]
            );

            return new ManiobraDTO(
                id:          $nuevo ? (int) $nuevo->idu_tipomaniobra : null,
                nombre:      $maniobra->nombre,
                descripcion: $maniobra->descripcion,
                estatus:     'Activo'
            );
        } catch (Exception $e) {
            Log::error('Error en SqlServerManiobraRepository@create', ['error' => $e->getMessage()]);
            throw new Exception('Error al crear la maniobra: ' . $e->getMessage());
        }
    }

    public function update(int $id, ManiobraDTO $maniobra): ManiobraDTO
    {
        try {
            $usuarioId = (int) (auth()->user()?->id ?? 0);

            // El SP recibe @Estatus como BIT: 1 = Activo, 0 = Inactivo
            $estatusBit = $this->estatusToBit($maniobra->estatus);

            $results = DB::connection('localDB')->select(
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

            $row = $results[0];
            if ((int) $row->estado !== 0) {
                throw new Exception($row->mensaje);
            }

            // El SP no devuelve el registro; reconstruimos el DTO con los datos enviados
            return new ManiobraDTO(
                id:          $id,
                nombre:      $maniobra->nombre,
                descripcion: $maniobra->descripcion,
                estatus:     $maniobra->estatus
            );
        } catch (Exception $e) {
            Log::error('Error en SqlServerManiobraRepository@update', ['error' => $e->getMessage()]);
            throw new Exception('Error al actualizar la maniobra: ' . $e->getMessage());
        }
    }

    /**
     * Convierte el estatus textual del dominio a BIT para el SP.
     * 'Activo' → 1  |  cualquier otro valor → 0
     */
    private function estatusToBit(string $estatus): int
    {
        return strtolower($estatus) === 'activo' ? 1 : 0;
    }

    public function existsByNombre(string $nombre, ?int $excludeId = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM cat_pdm_tipos_maniobras WHERE LTRIM(RTRIM(UPPER(nom_maniobra))) = LTRIM(RTRIM(UPPER(?)))";
            $params = [$nombre];

            if ($excludeId !== null) {
                $sql .= " AND idu_tipomaniobra <> ?";
                $params[] = $excludeId;
            }

            $result = DB::connection('localDB')->selectOne($sql, $params);
            
            return $result && $result->total > 0;
        } catch (Exception $e) {
            Log::error('Error en SqlServerManiobraRepository@existsByNombre', ['error' => $e->getMessage()]);
            return false;
        }
    }
}

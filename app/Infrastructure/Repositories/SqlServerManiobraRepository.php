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
            $results = DB::connection('localDB')->select(
                "EXEC proc_pdm_consultar_maniobras @Busqueda = ?",
                [$search ?: '']
            );

            if (empty($results)) {
                return [];
            }

            $row = $results[0];
            if ((int) $row->estado !== 0) {
                // Si retorna -100 u otro estado de "sin información", devolvemos arreglo vacío
                return [];
            }

            $maniobrasJson = json_decode($row->data, true);
            if (!is_array($maniobrasJson)) {
                return [];
            }

            $maniobras = [];
            foreach ($maniobrasJson as $item) {
                $maniobras[] = new ManiobraDTO(
                    id: (int) $item['codigoManiobra'],
                    nombre: $item['nombreManiobra'],
                    descripcion: $item['descripcionManiobra'] ?? null,
                    estatus: $item['estatusManiobra'] ?? 'A'
                );
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
            $usuarioId = auth()->user()?->id ?? 'sa';

            $results = DB::connection('localDB')->select(
                "EXEC proc_pdm_crear_maniobra @Nombre = ?, @Descripcion = ?, @CreadoPor = ?",
                [$maniobra->nombre, $maniobra->descripcion, $usuarioId]
            );

            if (empty($results)) {
                throw new Exception("No se recibió respuesta de la base de datos.");
            }

            $row = $results[0];
            if ((int) $row->estado !== 0) {
                throw new Exception($row->mensaje);
            }

            $data = json_decode($row->data, true);
            if (!is_array($data)) {
                throw new Exception("Error al decodificar la respuesta JSON de la maniobra creada.");
            }

            return new ManiobraDTO(
                id: (int) $data['codigoManiobra'],
                nombre: $data['nombreManiobra'],
                descripcion: $data['descripcionManiobra'] ?? null,
                estatus: $data['estatusManiobra'] ?? 'A'
            );
        } catch (Exception $e) {
            Log::error('Error en SqlServerManiobraRepository@create', ['error' => $e->getMessage()]);
            throw new Exception("Error al crear la maniobra: " . $e->getMessage());
        }
    }

    public function update(int $id, ManiobraDTO $maniobra): ManiobraDTO
    {
        try {
            $results = DB::connection('localDB')->select(
                "EXEC proc_pdm_modificar_maniobra @DocEntry = ?, @Nombre = ?, @Descripcion = ?, @Estatus = ?",
                [$id, $maniobra->nombre, $maniobra->descripcion, $maniobra->estatus]
            );

            if (empty($results)) {
                throw new Exception("No se recibió respuesta de la base de datos.");
            }

            $row = $results[0];
            if ((int) $row->estado !== 0) {
                throw new Exception($row->mensaje);
            }

            $data = json_decode($row->data, true);
            if (!is_array($data)) {
                throw new Exception("Error al decodificar la respuesta JSON de la maniobra modificada.");
            }

            return new ManiobraDTO(
                id: (int) $data['codigoManiobra'],
                nombre: $data['nombreManiobra'],
                descripcion: $data['descripcionManiobra'] ?? null,
                estatus: $data['estatusManiobra'] ?? 'A'
            );
        } catch (Exception $e) {
            Log::error('Error en SqlServerManiobraRepository@update', ['error' => $e->getMessage()]);
            throw new Exception("Error al actualizar la maniobra: " . $e->getMessage());
        }
    }

    public function existsByNombre(string $nombre, ?int $excludeId = null): bool
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM cat_pdm_tipos_maniobras WHERE LTRIM(RTRIM(UPPER(U_Nombre))) = LTRIM(RTRIM(UPPER(?)))";
            $params = [$nombre];

            if ($excludeId !== null) {
                $sql .= " AND DocEntry <> ?";
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

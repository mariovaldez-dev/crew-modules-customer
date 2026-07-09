# Stored Procedures — Contratos y Mapeos

> **Estado:** ⏳ Pendiente de recibir documentación oficial del cliente.
> Actualizar este archivo en cuanto se compartan las estructuras reales.
> **Regla:** No implementar ningún Repository hasta tener el contrato del SP documentado aquí.

---

## ⚠️ SPs Pendientes de Documentar

| SP | Módulo | Estado |
|---|---|---|
| `sp_auth_login` | RQM-01 | ⏳ Pendiente |
| `sp_cultivos_list` | RQM-03 | ⏳ Pendiente |
| `sp_cultivos_insert` | RQM-04 | ⏳ Pendiente |
| `sp_etapas_insert` | RQM-04 | ⏳ Pendiente |
| `sp_cultivos_update` | RQM-05 | ⏳ Pendiente |
| `sp_etapas_sync` | RQM-05 | ⏳ Pendiente |
| `sp_cultivos_delete` | RQM-06 | ⏳ Pendiente |
| `sp_zonas_list` | RQM-07 | ⏳ Pendiente |
| `sp_territorios_by_zona` | RQM-07 | ⏳ Pendiente |
| `sp_config_zona_list` | RQM-07 | ⏳ Pendiente |
| `sp_config_zona_upsert` | RQM-08 | ⏳ Pendiente |
| `sp_categorias_list` | Catálogos | ⏳ Pendiente |
| `sp_tipos_ciclo_list` | Catálogos | ⏳ Pendiente |
| `sp_productos_list` | Catálogos | ⏳ Pendiente |

---

## Formato de documentación por SP

Cuando el cliente comparta las estructuras, documentar cada SP con este formato:

```
## sp_nombre_del_sp

**Módulo:** RQM-XX
**Operación:** SELECT / INSERT / UPDATE / DELETE
**Estado:** ✅ Documentado | ⏳ Pendiente | 🔴 Faltante

### Parámetros de entrada
| Parámetro | Tipo | Nullable | Descripción |
|---|---|---|---|
| @param1 | VARCHAR(50) | NO | Descripción |

### Estructura de respuesta
| Columna BD | Tipo | Nullable | Mapea a |
|---|---|---|---|
| columna_bd | VARCHAR | NO | Entity::$propiedad |

### Ejemplo de llamada
```php
DB::select('CALL sp_nombre(?, ?)', [$param1, $param2]);
```

### Notas / Comportamiento especial
- Observaciones sobre el SP
```

---

## Plantilla de mapeo — Repository

Cuando tengas el contrato del SP, el mapeo en el repositorio sigue este patrón:

```php
// Infrastructure/Persistence/CultivoRepository.php

private function mapToDomain(object $row): Cultivo
{
    return new Cultivo(
        id:       $row->columna_id,       // ← nombre real de columna del SP
        nombre:   $row->columna_nombre,   // ← nombre real de columna del SP
        categoria: $row->columna_cat,     // ← nombre real de columna del SP
        activo:   (bool) $row->activo,
    );
}
```

> **Importante:** Los nombres de columna (`$row->columna_x`) deben coincidir **exactamente**
> con lo que devuelve el SP. No asumir — verificar con el cliente.

---

## Catálogos — Notas generales

Los siguientes catálogos ya existen en BD del cliente y se consumen vía SP o vista directa:
- **Categorías** de cultivo
- **Tipos de ciclo**
- **Zonas**
- **Territorios** (dependientes de zona)
- **Productos recomendados**

Confirmar con el cliente si se consumen vía SP o si es permitido hacer `DB::select('SELECT...')` directo sobre las tablas/vistas de catálogo.

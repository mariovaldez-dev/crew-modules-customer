# Requerimientos — Administrador Biblioteca de Cultivos

> Documento de referencia completo. Cargar con `@docs/requerimientos.md` cuando trabajes en un módulo específico.

---

## RQM-01 — Autenticación de Usuario

**Descripción:** El sistema permite que usuarios registrados inicien sesión con usuario/correo + contraseña.

### Reglas de Negocio
| ID | Regla |
|---|---|
| RN-01 | Campo Usuario/Correo obligatorio, alfanumérico, máx. 50 caracteres |
| RN-02 | Campo Contraseña obligatorio, alfanumérico, mín. 8 y máx. 15 caracteres |
| RN-03 | El usuario/correo debe existir en BD y estar activo |
| RN-04 | La contraseña debe coincidir con la registrada (encriptada) |
| RN-05 | Registrar cada intento: usuario, fecha/hora, IP/IMEI, resultado (exitoso/fallido) |
| RN-06 | En caso de error mostrar: "Usuario o contraseña incorrectos." |

### Criterios de Aceptación
1. Login exitoso con credenciales válidas → redirige al menú principal
2. Login fallido → mensaje "Usuario o contraseña incorrectos."
3. Campos vacíos → mensajes de validación por campo
4. Cada intento queda registrado en auditoría (exitoso o fallido)

---

## RQM-02 — Opciones Nuevas en el Menú Principal

**Descripción:** El menú principal muestra opciones adicionales exclusivas para usuarios con rol de asesor agronómico.

### Opciones a agregar
- `Administrar biblioteca de cultivos` → módulo catálogo
- `Configuración por zona-territorio` → módulo configuración

### Reglas de Negocio
| ID | Regla |
|---|---|
| RN-01 | Opciones visibles **solo** para rol `asesor_agronomo` |
| RN-02 | Usuarios sin rol no acceden ni por URL directa |
| RN-03 | Cada opción redirige correctamente a su módulo |

### Criterios de Aceptación
1. Usuario con rol asesor → ve ambas opciones en menú
2. Usuario sin rol → no ve las opciones
3. Acceso por URL directa sin rol → redirige o retorna 403
4. Click en opción → redirige al módulo correcto

---

## RQM-03 — Consulta del Catálogo de Cultivos

**Descripción:** Listado de cultivos activos con filtros y detalle de etapas fenológicas.

### Campos — Primer nivel (listado)
| Campo | Tipo |
|---|---|
| Nombre del cultivo | Texto |
| Categoría | Texto (catálogo) |
| Tipo de ciclo | Texto (catálogo) |

### Campos — Segundo nivel (detalle etapas)
| Campo | Tipo |
|---|---|
| Nombre de la etapa | Texto |
| Rango en días (inicio - fin) | Numérico |

### Filtros disponibles
| Filtro | Control |
|---|---|
| Nombre del cultivo | Texto libre (coincidencia parcial) |
| Categoría | Lista desplegable (catálogo) |
| Tipo de ciclo | Lista desplegable (catálogo) |

### Reglas de Negocio
| ID | Regla |
|---|---|
| RN-01 | Solo mostrar cultivos activos |
| RN-02 | Cada cultivo debe tener al menos una etapa fenológica |
| RN-03 | Filtro por nombre: coincidencias parciales (LIKE) |
| RN-04 | Valores de combos provienen de catálogos predefinidos |
| RN-05 | Sin resultados → mensaje informativo |
| RN-06 | Ordenamiento por defecto: alfabético por nombre |

### Criterios de Aceptación
1. Al acceder → listado de cultivos activos con nombre, categoría y ciclo
2. Filtro nombre parcial → muestra coincidencias
3. Filtro categoría → muestra solo esa categoría
4. Filtro tipo de ciclo → muestra solo ese ciclo
5. Múltiples filtros → intersección (AND)
6. Sin resultados → mensaje informativo visible

---

## RQM-04 — Agregar Nuevo Cultivo

**Descripción:** Formulario modal para registrar nuevos cultivos con sus etapas fenológicas.

### Campos del formulario
| Campo | Tipo | Validación |
|---|---|---|
| Nombre del cultivo | Texto | Obligatorio, máx. 50 chars, único en catálogo |
| Categoría | Lista desplegable | Obligatorio, desde catálogo |
| Tipo de ciclo | Lista desplegable | Obligatorio, desde catálogo |

### Campos por etapa
| Campo | Tipo | Validación |
|---|---|---|
| Nombre de etapa | Texto | Obligatorio, máx. 50 chars, único en el cultivo |
| Día inicio | Numérico | Obligatorio (positivo o negativo). Desde etapa 2: precargado automático = día fin anterior + 1, **no editable** |
| Día fin | Numérico | Obligatorio, mayor a día inicio, mostrar en **rojo** |

### Reglas de Negocio
| ID | Regla |
|---|---|
| RN-01 | Todos los campos obligatorios |
| RN-02 | Nombre del cultivo único en catálogo |
| RN-03 | Mínimo una etapa por cultivo |
| RN-04 | Nombre de etapa único dentro del mismo cultivo |
| RN-05 | Longitud máxima 50 chars para nombre cultivo y etapas |
| RN-06 | Categoría y tipo de ciclo desde catálogos predefinidos |
| RN-07 | Mantener orden de captura de etapas |
| RN-08 | Validar todos los campos antes de guardar |

### Criterios de Aceptación
1. Click "Nuevo cultivo" → modal con formulario
2. Guardar sin campos obligatorios → mensajes de validación
3. Campos completos + mínimo 1 etapa → registro exitoso
4. "+ Nueva etapa" → agrega fila de etapa al listado
5. Eliminar etapa → solo permite eliminar la **última**
6. Guardar sin etapas → error "se requiere al menos una etapa"
7. Cancelar → cierra modal sin guardar

---

## RQM-05 — Actualizar Cultivo

**Descripción:** Edición de cultivo existente reutilizando la estructura del formulario de alta.

### Campos editables
- Nombre del cultivo
- Categoría
- Tipo de ciclo
- Etapas (agregar, editar nombre/días, eliminar solo última)

### Reglas de Negocio
| ID | Regla |
|---|---|
| RN-01 | Respetar todas las reglas de RQM-04 |
| RN-02 | Nombre único excluyendo el propio cultivo (no self-duplicate) |
| RN-03 | Mantener al menos una etapa después de la edición |
| RN-04 | Conservar orden de etapas (por día inicio) |
| RN-05 | Cambios no aplicados hasta confirmar con "Guardar cambios" |

### Criterios de Aceptación
1. Click "Editar" → modal con datos precargados del cultivo
2. Modificar campos → respeta validaciones
3. Guardar cambios válidos → actualiza catálogo
4. Datos inválidos → mensajes de validación, no actualiza
5. Cambios en etapas → se reflejan en listado antes de guardar
6. Guardar sin etapas → error bloqueante
7. Cancelar → cierra sin guardar cambios

---

## RQM-06 — Eliminar Cultivo

**Descripción:** Eliminación controlada con confirmación previa mostrando el detalle del cultivo.

### Información en modal de confirmación
- Nombre del cultivo
- Categoría
- Tipo de ciclo
- Listado de etapas asociadas

### Reglas de Negocio
| ID | Regla |
|---|---|
| RN-01 | Confirmación explícita requerida antes de eliminar |
| RN-02 | Eliminación incluye todas las etapas asociadas |
| RN-03 | Eliminación lógica (inactivación) — no borrado físico |

### Criterios de Aceptación
1. Click "Eliminar" → modal con datos del cultivo + etapas
2. Confirmar → elimina (lógicamente) + actualiza listado sin reload
3. Cancelar → cierra modal sin eliminar
4. Tras eliminación → listado actualizado inmediatamente

---

## RQM-07 — Mostrar Configuración de Cultivos por Zona-Territorio

**Descripción:** Consulta de cultivos configurados para una zona-territorio específica.

### Selección requerida
1. Zona (lista desplegable) → obligatoria
2. Territorio (lista desplegable, dependiente de zona) → obligatorio

### Campos — Primer nivel (tarjetas de cultivo)
| Campo | Tipo |
|---|---|
| Nombre del cultivo | Texto |
| Categoría | Texto |
| Tipo de ciclo | Texto |

### Campos — Segundo nivel (detalle etapas por cultivo)
| Campo | Tipo |
|---|---|
| Nombre de la etapa | Texto |
| Día de inicio | Numérico |
| Día de fin | Numérico |
| Indicador fertilización | Sí / No |
| Dosis | Numérico |
| Productos recomendados | Lista |

### Reglas de Negocio
| ID | Regla |
|---|---|
| RN-01 | Zona obligatoria para consultar |
| RN-02 | Territorio obligatorio para consultar |
| RN-03 | Mostrar cultivos y configuración de la zona-territorio seleccionada |
| RN-04 | Tarjeta en **rojo** cuando el cultivo no tiene configuración para esa zona |
| RN-05 | Ordenar por nombre de cultivo y por orden de etapas (día inicio) |

### Criterios de Aceptación
1. Zona + territorio seleccionados → listado de cultivos con info general
2. Ver cultivo → detalle de etapas con configuración
3. Etapas muestran: días, fertilización, dosis, productos
4. Cultivo sin configuración → tarjeta en rojo

---

## RQM-08 — Actualizar Configuración de Cultivos por Zona-Territorio

**Descripción:** Edición de la configuración de etapas por zona-territorio previamente consultada.

### Campos editables por etapa
| Campo | Tipo | Condición |
|---|---|---|
| Fertilización | Sí / No | Obligatorio siempre |
| Dosis | Numérico | Obligatorio **solo** si Fertilización = Sí |
| Productos recomendados | Multi-select | Opcional |

### Reglas de Negocio
| ID | Regla |
|---|---|
| RN-01 | Validar campos obligatorios antes de actualizar |
| RN-02 | Fertilización obligatoria para todas las etapas |
| RN-03 | Dosis obligatoria solo si Fertilización = Sí |
| RN-04 | Productos recomendados: multi-select opcional |
| RN-05 | Etapas ordenadas por día inicio durante la edición |

### Mensajes del sistema
- Éxito: `"Configuración actualizada correctamente"`
- Error: mensaje indicando la regla de negocio que no se cumplió

### Criterios de Aceptación
1. Ver config cargada → campos editables por etapa
2. Actualizar sin obligatorios → mensajes de validación
3. Fertilización = Sí sin Dosis → bloquear actualización + mensaje
4. Datos válidos + "Actualizar Configuración" → guarda + mensaje éxito
5. Error de validación → indica claramente qué regla falló

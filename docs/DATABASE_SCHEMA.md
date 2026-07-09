# Propuesta de Esquema de Base de Datos (SQL Server) — Módulo PDM (Nomenclatura SAP)

Esta propuesta detalla las tablas de base de datos requeridas para soportar las operaciones del sistema, utilizando la nomenclatura solicitada para las tablas y la nomenclatura estándar de **SAP Business One** para los campos:
* Tablas: `cat_pdm_...`, `mae_pdm_...`, `ctl_pdm_...`, `mov_pdm_...`
* Llave Primaria del Objeto / Documento: `DocEntry` (Standard de SAP B1)
* Campos definidos por el usuario (UDFs): Prefijo `U_` (Standard de SAP B1)

---

## 1. Tablas de Catálogo (`cat_`)

### 1.1 `cat_pdm_tipos_maniobras`
Almacena el catálogo de tipos de maniobra autorizados (RQM-01).

| Campo | Tipo de Datos SQL Server | Nulidad | Descripción / Restricciones |
|---|---|---|---|
| `DocEntry` | `INT IDENTITY(1,1)` | `NOT NULL` | Llave Primaria (PK) |
| `U_Nombre` | `VARCHAR(100)` | `NOT NULL` | Nombre de la maniobra (Único) |
| `U_Descripcion` | `VARCHAR(100)` | `NULL` | Breve descripción de la maniobra |
| `U_Estatus` | `CHAR(1)` | `NOT NULL` | `'A'` = Activa, `'I'` = Inactiva (Por defecto `'A'`) |
| `U_CreadoPor` | `VARCHAR(50)` | `NOT NULL` | Usuario que registró el tipo |
| `U_FechaCrea` | `DATETIME` | `NOT NULL` | Fecha de creación (`DEFAULT GETDATE()`) |

---

## 2. Tablas Maestras (`mae_`)

### 2.1 `mae_pdm_cuadrillas`
Almacena el maestro de cuadrillas (RQM-02). Los almacenes y Puntos de Venta (PV) se validan contra las vistas de SAP B1 (`OWHS` y `@SUCURSALES`).

| Campo | Tipo de Datos SQL Server | Nulidad | Descripción / Restricciones |
|---|---|---|---|
| `DocEntry` | `INT IDENTITY(1,1)` | `NOT NULL` | Llave Primaria (PK) |
| `U_Nombre` | `VARCHAR(100)` | `NOT NULL` | Nombre de la cuadrilla |
| `U_Lider` | `VARCHAR(100)` | `NOT NULL` | Nombre completo del líder |
| `U_Miembros` | `INT` | `NOT NULL` | Cantidad de integrantes (Restricción: `>= 1`) |
| `U_PuntoVentaId` | `INT` | `NOT NULL` | ID del Punto de Venta (FK lógica hacia SAP B1) |
| `U_Zona` | `VARCHAR(50)` | `NOT NULL` | Zona del Punto de Venta (Norte/Sur/etc.) |
| `U_CreadoPor` | `VARCHAR(50)` | `NOT NULL` | Usuario que creó el registro |
| `U_FechaCrea` | `DATETIME` | `NOT NULL` | Fecha de registro |

> Se recomienda una restricción única compuesta `UQ_mae_pdm_cuadrillas_U_Nombre_pv` sobre los campos (`U_Nombre`, `U_PuntoVentaId`) para permitir repetir nombres de cuadrilla solo si pertenecen a distintos puntos de venta.

### 2.2 `mae_pdm_tarifas_cuadrillas`
Almacena las 6 tarifas monetarias asignadas a cada cuadrilla.

| Campo | Tipo de Datos SQL Server | Nulidad | Descripción / Restricciones |
|---|---|---|---|
| `U_CuadrillaId` | `INT` | `NOT NULL` | PK, FK hacia `mae_pdm_cuadrillas(DocEntry)` |
| `U_Carga25kg` | `DECIMAL(18,2)` | `NULL` | Tarifa de carga 25kg (Opcional, `>= 0`) |
| `U_Carga50kg` | `DECIMAL(18,2)` | `NULL` | Tarifa de carga 50kg (Opcional, `>= 0`) |
| `U_Descarga25kg` | `DECIMAL(18,2)` | `NULL` | Tarifa de descarga 25kg (Opcional, `>= 0`) |
| `U_Descarga50kg` | `DECIMAL(18,2)` | `NULL` | Tarifa de descarga 50kg (Opcional, `>= 0`) |
| `U_Traslado` | `DECIMAL(18,2)` | `NULL` | Tarifa de traslado interno (Opcional, `>= 0`) |
| `U_Apaleo` | `DECIMAL(18,2)` | `NULL` | Tarifa de apaleo (Opcional, `>= 0`) |
| `U_UltAct` | `DATETIME` | `NOT NULL` | Fecha de la última modificación (`DEFAULT GETDATE()`) |

---

## 3. Tablas de Control (`ctl_`)

### 3.1 `ctl_pdm_auditoria_tarifas`
Bitácora histórica para la auditoría de cambios de tarifa por cuadrilla (RQM-02).

| Campo | Tipo de Datos SQL Server | Nulidad | Descripción / Restricciones |
|---|---|---|---|
| `DocEntry` | `BIGINT IDENTITY(1,1)` | `NOT NULL` | Llave Primaria (PK) |
| `U_CuadrillaId` | `INT` | `NOT NULL` | FK hacia `mae_pdm_cuadrillas(DocEntry)` |
| `U_ConceptoTarifa` | `VARCHAR(50)` | `NOT NULL` | Ej. `'carga_25kg'`, `'apaleo'` |
| `U_PrecioAnterior` | `DECIMAL(18,2)` | `NULL` | Tarifa previa |
| `U_PrecioNuevo` | `DECIMAL(18,2)` | `NULL` | Nueva tarifa registrada |
| `U_UsuarioId` | `VARCHAR(50)` | `NOT NULL` | Usuario que realizó la edición |
| `U_FechaCambio` | `DATETIME` | `NOT NULL` | Fecha y hora del cambio (`DEFAULT GETDATE()`) |

---

## 4. Tablas de Movimientos (`mov_`)

### 4.1 `mov_pdm_registro_maniobras`
Bitácora principal de maniobras registradas en el sistema (RQM-03).

| Campo | Tipo de Datos SQL Server | Nulidad | Descripción / Restricciones |
|---|---|---|---|
| `DocEntry` | `INT IDENTITY(1,1)` | `NOT NULL` | Llave Primaria (PK) |
| `U_Folio` | `VARCHAR(20)` | `NOT NULL` | Folio auto-generado (Único, ej. `MAN-0001`) |
| `U_Fecha` | `DATE` | `NOT NULL` | Fecha de realización de la maniobra (No futura) |
| `U_AlmacenId` | `INT` | `NOT NULL` | ID del almacén (FK lógica hacia SAP B1) |
| `U_CuadrillaId` | `INT` | `NOT NULL` | FK hacia `mae_pdm_cuadrillas(DocEntry)` |
| `U_TipoManiobraId` | `INT` | `NOT NULL` | FK hacia `cat_pdm_tipos_maniobras(DocEntry)` |
| `U_Toneladas` | `DECIMAL(18,3)` | `NOT NULL` | Peso en toneladas (Guardado a 3 decimales, `> 0`) |
| `U_DocumentoSap` | `VARCHAR(50)` | `NULL` | Folio de documento opcional de SAP B1 |
| `U_Origen` | `VARCHAR(10)` | `NOT NULL` | `'APP'` = Dispositivo, `'MANUAL'` = Alta Web |
| `U_CorteId` | `INT` | `NULL` | FK hacia `mov_pdm_cortes_liquidacion(DocEntry)` |
| `U_CreadoPor` | `VARCHAR(50)` | `NOT NULL` | Usuario de creación |
| `U_FechaReg` | `DATETIME` | `NOT NULL` | Fecha y hora de guardado |

### 4.2 `mov_pdm_cortes_liquidacion`
Encabezado de los cortes de liquidación generados para pago de cuadrillas (RQM-05).

| Campo | Tipo de Datos SQL Server | Nulidad | Descripción / Restricciones |
|---|---|---|---|
| `DocEntry` | `INT IDENTITY(1,1)` | `NOT NULL` | Llave Primaria (PK) |
| `U_Folio` | `VARCHAR(20)` | `NOT NULL` | Folio auto-generado (Único, ej. `COR-202607-001`) |
| `U_FechaInicio` | `DATE` | `NOT NULL` | Inicio de rango del corte |
| `U_FechaFin` | `DATE` | `NOT NULL` | Fin de rango del corte |
| `U_Zona` | `VARCHAR(50)` | `NOT NULL` | Zona a la que aplica el corte |
| `U_Estado` | `VARCHAR(20)` | `NOT NULL` | `'borrador'`, `'confirmado'` |
| `U_CreadoPor` | `VARCHAR(50)` | `NOT NULL` | Usuario que generó el borrador |
| `U_FechaCrea` | `DATETIME` | `NOT NULL` | Fecha del borrador |
| `U_ConfirmadoPor` | `VARCHAR(50)` | `NULL` | Usuario que aprobó y bloqueó el corte |
| `U_FechaConf` | `DATETIME` | `NULL` | Fecha de confirmación general |

### 4.3 `mov_pdm_cortes_cuadrillas`
Detalle acumulado de montos y toneladas por cuadrilla incluidos en un corte.

| Campo | Tipo de Datos SQL Server | Nulidad | Descripción / Restricciones |
|---|---|---|---|
| `U_CorteId` | `INT` | `NOT NULL` | PK, FK hacia `mov_pdm_cortes_liquidacion(DocEntry)` |
| `U_CuadrillaId` | `INT` | `NOT NULL` | PK, FK hacia `mae_pdm_cuadrillas(DocEntry)` |
| `U_TotalToneladas` | `DECIMAL(18,3)` | `NOT NULL` | Suma de toneladas del periodo |
| `U_TotalMonto` | `DECIMAL(18,2)` | `NOT NULL` | Suma total a pagar según tarifas vigentes |
| `U_Confirmada` | `BIT` | `NOT NULL` | `1` = Confirmado por coordinador, `0` = Pendiente |
| `U_ConfirmadoPor` | `VARCHAR(50)` | `NULL` | Usuario que validó la cuadrilla |
| `U_FechaConf` | `DATETIME` | `NULL` | Fecha de la validación individual |

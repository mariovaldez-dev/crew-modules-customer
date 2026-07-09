# Consulta y Stored Procedure — Definición de Zonas y Puntos de Venta

A continuación se presentan las soluciones en **SQL Server (T-SQL)** para obtener el mapeo de Puntos de Venta (Almacenes `OWHS`) y Zonas (tabla de usuario `[@SUCURSALES]`) de acuerdo con las condiciones solicitadas.

---

## 1. Consulta SQL Directa (Formato Relacional)

Esta consulta realiza la relación y los filtros solicitados:
* Filtra almacenes activos (`OWHS.Inactive = 'N'`).
* Filtra por tipos de Punto de Venta autorizados (`OWHS.U_TipoPV IN ('B', 'PB')`).
* Obtiene el nombre de la zona desde `[@SUCURSALES].Name`.

```sql
SELECT 
    w.WhsCode AS [PuntoVentaCodigo],
    w.WhsName AS [PuntoVentaNombre],
    w.U_SerieSucursal AS [ZonaCodigo],
    s.Name AS [ZonaNombre]
FROM 
    OWHS w
INNER JOIN 
    [@SUCURSALES] s ON w.U_SerieSucursal = s.Code
WHERE 
    w.Inactive = 'N'
    AND w.U_TipoPV IN ('B', 'PB')
ORDER BY 
    s.Name, 
    w.WhsName;
```

---

## 2. Stored Procedure que Retorna un JSON (T-SQL)

Para retornar los resultados en formato JSON directamente desde SQL Server (compatible con PHP `json_decode`), utilizamos la cláusula `FOR JSON PATH`.

El Stored Procedure almacena la estructura JSON en una variable `NVARCHAR(MAX)` y la devuelve como un único registro con la columna `JsonData`.

```sql
CREATE PROCEDURE dbo.sp_ObtenerPuntosVentaPorZonaJson
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @JsonResult NVARCHAR(MAX);

    -- Generar el JSON de forma directa
    SET @JsonResult = (
        SELECT 
            w.WhsCode AS [PuntoVentaCodigo],
            w.WhsName AS [PuntoVentaNombre],
            w.U_SerieSucursal AS [ZonaCodigo],
            s.Name AS [ZonaNombre]
        FROM 
            OWHS w
        INNER JOIN 
            [@SUCURSALES] s ON w.U_SerieSucursal = s.Code
        WHERE 
            w.Inactive = 'N'
            AND w.U_TipoPV IN ('B', 'PB')
        ORDER BY 
            s.Name, 
            w.WhsName
        FOR JSON PATH
    );

    -- Si no hay registros, asegurar que devuelva un arreglo vacío en lugar de NULL
    IF @JsonResult IS NULL
    BEGIN
        SET @JsonResult = '[]';
    END

    -- Retornar el JSON
    SELECT @JsonResult AS [JsonData];
END;
GO
```

### Ejemplo del JSON devuelto:
```json
[
  {
    "PuntoVentaCodigo": "PV001",
    "PuntoVentaNombre": "Punto de Venta Norte Principal",
    "ZonaCodigo": "ZN",
    "ZonaNombre": "ZONA-NORTE"
  },
  {
    "PuntoVentaCodigo": "PV002",
    "PuntoVentaNombre": "Punto de Venta Sur Auxiliar",
    "ZonaCodigo": "ZS",
    "ZonaNombre": "ZONA-SUR"
  }
]
```

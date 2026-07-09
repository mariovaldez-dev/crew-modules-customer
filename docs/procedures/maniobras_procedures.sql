-- =========================================================================
-- STORED PROCEDURES (SQL SERVER) — MÓDULO DE MANIOBRAS (CATÁLOGO)
-- =========================================================================

-- =========================================================================
-- 1. CONSULTAR TIPOS DE MANIOBRAS
-- =========================================================================
CREATE OR ALTER PROCEDURE proc_pdm_consultar_maniobras(
    @Busqueda VARCHAR(100) = NULL
)
AS
BEGIN
    /************************************************************************************
    Ejemplos de ejecución:
    -- EXEC proc_pdm_consultar_maniobras
    -- EXEC proc_pdm_consultar_maniobras 'Carga'
    ************************************************************************************/
    SET NOCOUNT ON;
    
    BEGIN TRY
        DECLARE @json NVARCHAR(MAX);
        DECLARE @estado INT = 0;
        DECLARE @mensaje VARCHAR(500) = 'OK';

        SET @json = (
            SELECT 
                DocEntry AS [codigoManiobra],
                U_Nombre AS [nombreManiobra],
                U_Descripcion AS [descripcionManiobra],
                U_Estatus AS [estatusManiobra],
                U_CreadoPor AS [creadoPor],
                U_FechaCrea AS [fechaCreacion]
            FROM 
                dbo.cat_pdm_tipos_maniobras
            WHERE 
                (@Busqueda IS NULL OR @Busqueda = '' OR U_Nombre LIKE '%' + @Busqueda + '%' OR U_Descripcion LIKE '%' + @Busqueda + '%')
            ORDER BY 
                U_Nombre
            FOR JSON PATH
        );

        IF @json IS NULL  
        BEGIN
            SET @estado = -100;
            SET @mensaje = 'Sin información de maniobras';
            SET @json = '[]';
        END
    END TRY 
    BEGIN CATCH
        SET @estado = ERROR_NUMBER();
        SET @mensaje = ERROR_PROCEDURE() + ' - ' + ERROR_MESSAGE();
        SET @json = '[]';
    END CATCH

    SELECT @estado AS estado, @mensaje AS mensaje, @json AS data;
END;
GO

-- =========================================================================
-- 2. CREAR NUEVA MANIOBRA
-- =========================================================================
CREATE OR ALTER PROCEDURE proc_pdm_crear_maniobra(
    @Nombre VARCHAR(100),
    @Descripcion VARCHAR(100) = NULL,
    @CreadoPor VARCHAR(50)
)
AS
BEGIN
    /************************************************************************************
    Ejemplos de ejecución:
    -- EXEC proc_pdm_crear_maniobra 'Carga 25kg', 'Carga de sacos de 25 kilos', 'sa'
    ************************************************************************************/
    SET NOCOUNT ON;

    BEGIN TRY
        DECLARE @json NVARCHAR(MAX);
        DECLARE @estado INT = 0;
        DECLARE @mensaje VARCHAR(500) = 'OK';
        DECLARE @NuevoDocEntry INT;

        -- Validar duplicidad (Case-Insensitive por defecto en SQL Server)
        IF EXISTS (SELECT 1 FROM dbo.cat_pdm_tipos_maniobras WHERE LTRIM(RTRIM(U_Nombre)) = LTRIM(RTRIM(@Nombre)))
        BEGIN
            SET @estado = -1;
            SET @mensaje = 'El nombre de la maniobra ya existe.';
            SET @json = '{}';
        END
        ELSE
        BEGIN
            -- Registrar maniobra
            INSERT INTO dbo.cat_pdm_tipos_maniobras (U_Nombre, U_Descripcion, U_CreadoPor)
            VALUES (@Nombre, @Descripcion, @CreadoPor);

            SET @NuevoDocEntry = SCOPE_IDENTITY();

            -- Devolver el registro insertado en JSON
            SET @json = (
                SELECT 
                    DocEntry AS [codigoManiobra],
                    U_Nombre AS [nombreManiobra],
                    U_Descripcion AS [descripcionManiobra],
                    U_Estatus AS [estatusManiobra],
                    U_CreadoPor AS [creadoPor],
                    U_FechaCrea AS [fechaCreacion]
                FROM 
                    dbo.cat_pdm_tipos_maniobras
                WHERE 
                    DocEntry = @NuevoDocEntry
                FOR JSON PATH, WITHOUT_ARRAY_WRAPPER
            );
        END
    END TRY 
    BEGIN CATCH
        SET @estado = ERROR_NUMBER();
        SET @mensaje = ERROR_PROCEDURE() + ' - ' + ERROR_MESSAGE();
        SET @json = '{}';
    END CATCH

    SELECT @estado AS estado, @mensaje AS mensaje, @json AS data;
END;
GO

-- =========================================================================
-- 3. MODIFICAR MANIOBRA
-- =========================================================================
CREATE OR ALTER PROCEDURE proc_pdm_modificar_maniobra(
    @DocEntry INT,
    @Nombre VARCHAR(100),
    @Descripcion VARCHAR(100) = NULL,
    @Estatus CHAR(1)
)
AS
BEGIN
    /************************************************************************************
    Ejemplos de ejecución:
    -- EXEC proc_pdm_modificar_maniobra 1, 'Carga 25kg Modificada', 'Descripción nueva', 'A'
    ************************************************************************************/
    SET NOCOUNT ON;

    BEGIN TRY
        DECLARE @json NVARCHAR(MAX);
        DECLARE @estado INT = 0;
        DECLARE @mensaje VARCHAR(500) = 'OK';

        -- Validar existencia
        IF NOT EXISTS (SELECT 1 FROM dbo.cat_pdm_tipos_maniobras WHERE DocEntry = @DocEntry)
        BEGIN
            SET @estado = -2;
            SET @mensaje = 'La maniobra seleccionada no existe.';
            SET @json = '{}';
        END
        -- Validar duplicidad del nombre excluyendo el registro actual
        ELSE IF EXISTS (SELECT 1 FROM dbo.cat_pdm_tipos_maniobras WHERE LTRIM(RTRIM(U_Nombre)) = LTRIM(RTRIM(@Nombre)) AND DocEntry <> @DocEntry)
        BEGIN
            SET @estado = -1;
            SET @mensaje = 'El nombre de la maniobra ya existe.';
            SET @json = '{}';
        END
        ELSE
        BEGIN
            -- Actualizar registro
            UPDATE dbo.cat_pdm_tipos_maniobras
            SET 
                U_Nombre = @Nombre,
                U_Descripcion = @Descripcion,
                U_Estatus = @Estatus
            WHERE 
                DocEntry = @DocEntry;

            -- Devolver el registro modificado en JSON
            SET @json = (
                SELECT 
                    DocEntry AS [codigoManiobra],
                    U_Nombre AS [nombreManiobra],
                    U_Descripcion AS [descripcionManiobra],
                    U_Estatus AS [estatusManiobra],
                    U_CreadoPor AS [creadoPor],
                    U_FechaCrea AS [fechaCreacion]
                FROM 
                    dbo.cat_pdm_tipos_maniobras
                WHERE 
                    DocEntry = @DocEntry
                FOR JSON PATH, WITHOUT_ARRAY_WRAPPER
            );
        END
    END TRY 
    BEGIN CATCH
        SET @estado = ERROR_NUMBER();
        SET @mensaje = ERROR_PROCEDURE() + ' - ' + ERROR_MESSAGE();
        SET @json = '{}';
    END CATCH

    SELECT @estado AS estado, @mensaje AS mensaje, @json AS data;
END;
GO

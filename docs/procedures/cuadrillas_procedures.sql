-- =========================================================================
-- STORED PROCEDURES PARA CUADRILLAS Y TARIFAS — MÓDULO PDM (Nomenclatura SAP)
-- =========================================================================

-- 1. proc_pdm_consultar_cuadrillas
-- Consulta el catálogo de cuadrillas y sus tarifas asociadas, con soporte de búsqueda y scoping
CREATE OR ALTER PROCEDURE proc_pdm_consultar_cuadrillas
    @Busqueda VARCHAR(100) = NULL,
    @PuntoVentaId INT = NULL,
    @Zona VARCHAR(50) = NULL
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRY
        DECLARE @json NVARCHAR(MAX);
        DECLARE @estado INT = 0;
        DECLARE @mensaje VARCHAR(500) = 'OK';

        -- Si la búsqueda es vacía, tratar como NULL
        IF LTRIM(RTRIM(@Busqueda)) = '' SET @Busqueda = NULL;

        SET @json = (
            SELECT 
                C.DocEntry AS [codigoCuadrilla],
                C.U_Nombre AS [nombreCuadrilla],
                C.U_Lider AS [liderCuadrilla],
                C.U_Miembros AS [miembrosCuadrilla],
                C.U_PuntoVentaId AS [puntoVentaId],
                C.U_Zona AS [zonaCuadrilla],
                T.U_Carga25kg AS [carga25kg],
                T.U_Carga50kg AS [carga50kg],
                T.U_Descarga25kg AS [descarga25kg],
                T.U_Descarga50kg AS [descarga50kg],
                T.U_Traslado AS [traslado],
                T.U_Apaleo AS [apaleo]
            FROM dbo.mae_pdm_cuadrillas C
            LEFT JOIN dbo.mae_pdm_tarifas_cuadrillas T ON C.DocEntry = T.U_CuadrillaId
            WHERE (@Zona IS NULL OR @Zona = 'TODAS' OR C.U_Zona = @Zona)
              AND (@PuntoVentaId IS NULL OR C.U_PuntoVentaId = @PuntoVentaId)
              AND (@Busqueda IS NULL OR C.U_Nombre LIKE '%' + @Busqueda + '%' OR C.U_Lider LIKE '%' + @Busqueda + '%')
            ORDER BY C.U_Nombre ASC
            FOR JSON PATH
        );

        IF @json IS NULL
        BEGIN
            SET @estado = -100;
            SET @mensaje = 'Sin información de cuadrillas';
        END

        SELECT @estado AS estado, @mensaje AS mensaje, @json AS data;
    END TRY
    BEGIN CATCH
        SELECT ERROR_NUMBER() AS estado, ERROR_MESSAGE() AS mensaje, NULL AS data;
    END CATCH
END;
GO

-- 2. proc_pdm_crear_cuadrilla
-- Registra una nueva cuadrilla y sus tarifas monetarias en una sola transacción
CREATE OR ALTER PROCEDURE proc_pdm_crear_cuadrilla
    @Nombre VARCHAR(100),
    @Lider VARCHAR(100),
    @Miembros INT,
    @PuntoVentaId INT,
    @Zona VARCHAR(50),
    @CreadoPor VARCHAR(50),
    @Carga25kg DECIMAL(18,2) = NULL,
    @Carga50kg DECIMAL(18,2) = NULL,
    @Descarga25kg DECIMAL(18,2) = NULL,
    @Descarga50kg DECIMAL(18,2) = NULL,
    @Traslado DECIMAL(18,2) = NULL,
    @Apaleo DECIMAL(18,2) = NULL
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRY
        DECLARE @estado INT = 0;
        DECLARE @mensaje VARCHAR(500) = 'OK';
        DECLARE @json NVARCHAR(MAX);
        DECLARE @NewDocEntry INT;

        -- Validar nombre único en el mismo PV
        IF EXISTS (
            SELECT 1 FROM dbo.mae_pdm_cuadrillas 
            WHERE LTRIM(RTRIM(UPPER(U_Nombre))) = LTRIM(RTRIM(UPPER(@Nombre)))
              AND U_PuntoVentaId = @PuntoVentaId
        )
        BEGIN
            SET @estado = -1;
            SET @mensaje = 'Ya existe una cuadrilla con el mismo nombre en este punto de venta.';
            SELECT @estado AS estado, @mensaje AS mensaje, NULL AS data;
            RETURN;
        END;

        BEGIN TRANSACTION;

        -- Insertar datos generales
        INSERT INTO dbo.mae_pdm_cuadrillas (U_Nombre, U_Lider, U_Miembros, U_PuntoVentaId, U_Zona, U_CreadoPor, U_FechaCrea)
        VALUES (@Nombre, @Lider, @Miembros, @PuntoVentaId, @Zona, @CreadoPor, GETDATE());

        SET @NewDocEntry = SCOPE_IDENTITY();

        -- Insertar tarifas iniciales
        INSERT INTO dbo.mae_pdm_tarifas_cuadrillas (U_CuadrillaId, U_Carga25kg, U_Carga50kg, U_Descarga25kg, U_Descarga50kg, U_Traslado, U_Apaleo, U_UltAct)
        VALUES (@NewDocEntry, @Carga25kg, @Carga50kg, @Descarga25kg, @Descarga50kg, @Traslado, @Apaleo, GETDATE());

        COMMIT TRANSACTION;

        -- Obtener objeto creado
        SET @json = (
            SELECT 
                C.DocEntry AS [codigoCuadrilla],
                C.U_Nombre AS [nombreCuadrilla],
                C.U_Lider AS [liderCuadrilla],
                C.U_Miembros AS [miembrosCuadrilla],
                C.U_PuntoVentaId AS [puntoVentaId],
                C.U_Zona AS [zonaCuadrilla],
                T.U_Carga25kg AS [carga25kg],
                T.U_Carga50kg AS [carga50kg],
                T.U_Descarga25kg AS [descarga25kg],
                T.U_Descarga50kg AS [descarga50kg],
                T.U_Traslado AS [traslado],
                T.U_Apaleo AS [apaleo]
            FROM dbo.mae_pdm_cuadrillas C
            LEFT JOIN dbo.mae_pdm_tarifas_cuadrillas T ON C.DocEntry = T.U_CuadrillaId
            WHERE C.DocEntry = @NewDocEntry
            FOR JSON PATH, WITHOUT_ARRAY_WRAPPER
        );

        SELECT @estado AS estado, @mensaje AS mensaje, @json AS data;
    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0 ROLLBACK TRANSACTION;
        SELECT ERROR_NUMBER() AS estado, ERROR_MESSAGE() AS mensaje, NULL AS data;
    END CATCH
END;
GO

-- 3. proc_pdm_modificar_cuadrilla
-- Modifica cuadrillas, audita cambios de tarifas e implementa bloqueo por liquidaciones en proceso
CREATE OR ALTER PROCEDURE proc_pdm_modificar_cuadrilla
    @DocEntry INT,
    @Nombre VARCHAR(100),
    @Lider VARCHAR(100),
    @Miembros INT,
    @PuntoVentaId INT,
    @Zona VARCHAR(50),
    @ModificadoPor VARCHAR(50),
    @Carga25kg DECIMAL(18,2) = NULL,
    @Carga50kg DECIMAL(18,2) = NULL,
    @Descarga25kg DECIMAL(18,2) = NULL,
    @Descarga50kg DECIMAL(18,2) = NULL,
    @Traslado DECIMAL(18,2) = NULL,
    @Apaleo DECIMAL(18,2) = NULL
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRY
        DECLARE @estado INT = 0;
        DECLARE @mensaje VARCHAR(500) = 'OK';
        DECLARE @json NVARCHAR(MAX);

        -- Validar nombre único en el mismo PV excluyéndose a sí misma
        IF EXISTS (
            SELECT 1 FROM dbo.mae_pdm_cuadrillas 
            WHERE LTRIM(RTRIM(UPPER(U_Nombre))) = LTRIM(RTRIM(UPPER(@Nombre)))
              AND U_PuntoVentaId = @PuntoVentaId
              AND DocEntry <> @DocEntry
        )
        BEGIN
            SET @estado = -1;
            SET @mensaje = 'Ya existe otra cuadrilla con el mismo nombre en este punto de venta.';
            SELECT @estado AS estado, @mensaje AS mensaje, NULL AS data;
            RETURN;
        END;

        -- Validar si se cambia Nombre o PV y si hay liquidaciones en proceso (en estado 'borrador')
        DECLARE @OldNombre VARCHAR(100);
        DECLARE @OldPV INT;
        SELECT @OldNombre = U_Nombre, @OldPV = U_PuntoVentaId FROM dbo.mae_pdm_cuadrillas WHERE DocEntry = @DocEntry;

        IF (@OldNombre <> @Nombre OR @OldPV <> @PuntoVentaId)
        BEGIN
            IF EXISTS (
                SELECT 1 FROM dbo.mov_pdm_registro_maniobras M
                INNER JOIN dbo.mov_pdm_cortes_liquidacion C ON M.U_CorteId = C.DocEntry
                WHERE M.U_CuadrillaId = @DocEntry AND C.U_Estado = 'borrador'
            )
            BEGIN
                SET @estado = -2;
                SET @mensaje = 'No se puede modificar el nombre ni el Punto de Venta porque esta cuadrilla tiene liquidaciones en proceso (corte en estado borrador).';
                SELECT @estado AS estado, @mensaje AS mensaje, NULL AS data;
                RETURN;
            END;
        END;

        BEGIN TRANSACTION;

        -- Auditoría de Tarifas
        DECLARE @PrevCarga25 DECIMAL(18,2), @PrevCarga50 DECIMAL(18,2), @PrevDescarga25 DECIMAL(18,2);
        DECLARE @PrevDescarga50 DECIMAL(18,2), @PrevTraslado DECIMAL(18,2), @PrevApaleo DECIMAL(18,2);

        SELECT 
            @PrevCarga25 = U_Carga25kg, @PrevCarga50 = U_Carga50kg, @PrevDescarga25 = U_Descarga25kg,
            @PrevDescarga50 = U_Descarga50kg, @PrevTraslado = U_Traslado, @PrevApaleo = U_Apaleo
        FROM dbo.mae_pdm_tarifas_cuadrillas WHERE U_CuadrillaId = @DocEntry;

        -- Comparar y escribir en bitácora histórica por tarifa cambiada
        IF ISNULL(@PrevCarga25, -1) <> ISNULL(@Carga25kg, -1)
            INSERT INTO dbo.ctl_pdm_auditoria_tarifas (U_CuadrillaId, U_ConceptoTarifa, U_PrecioAnterior, U_PrecioNuevo, U_UsuarioId, U_FechaCambio)
            VALUES (@DocEntry, 'carga_25kg', @PrevCarga25, @Carga25kg, @ModificadoPor, GETDATE());

        IF ISNULL(@PrevCarga50, -1) <> ISNULL(@Carga50kg, -1)
            INSERT INTO dbo.ctl_pdm_auditoria_tarifas (U_CuadrillaId, U_ConceptoTarifa, U_PrecioAnterior, U_PrecioNuevo, U_UsuarioId, U_FechaCambio)
            VALUES (@DocEntry, 'carga_50kg', @PrevCarga50, @Carga50kg, @ModificadoPor, GETDATE());

        IF ISNULL(@PrevDescarga25, -1) <> ISNULL(@Descarga25kg, -1)
            INSERT INTO dbo.ctl_pdm_auditoria_tarifas (U_CuadrillaId, U_ConceptoTarifa, U_PrecioAnterior, U_PrecioNuevo, U_UsuarioId, U_FechaCambio)
            VALUES (@DocEntry, 'descarga_25kg', @PrevDescarga25, @Descarga25kg, @ModificadoPor, GETDATE());

        IF ISNULL(@PrevDescarga50, -1) <> ISNULL(@Descarga50kg, -1)
            INSERT INTO dbo.ctl_pdm_auditoria_tarifas (U_CuadrillaId, U_ConceptoTarifa, U_PrecioAnterior, U_PrecioNuevo, U_UsuarioId, U_FechaCambio)
            VALUES (@DocEntry, 'descarga_50kg', @PrevDescarga50, @Descarga50kg, @ModificadoPor, GETDATE());

        IF ISNULL(@PrevTraslado, -1) <> ISNULL(@Traslado, -1)
            INSERT INTO dbo.ctl_pdm_auditoria_tarifas (U_CuadrillaId, U_ConceptoTarifa, U_PrecioAnterior, U_PrecioNuevo, U_UsuarioId, U_FechaCambio)
            VALUES (@DocEntry, 'traslado', @PrevTraslado, @Traslado, @ModificadoPor, GETDATE());

        IF ISNULL(@PrevApaleo, -1) <> ISNULL(@Apaleo, -1)
            INSERT INTO dbo.ctl_pdm_auditoria_tarifas (U_CuadrillaId, U_ConceptoTarifa, U_PrecioAnterior, U_PrecioNuevo, U_UsuarioId, U_FechaCambio)
            VALUES (@DocEntry, 'apaleo', @PrevApaleo, @Apaleo, @ModificadoPor, GETDATE());

        -- Actualizar datos generales
        UPDATE dbo.mae_pdm_cuadrillas
        SET U_Nombre = @Nombre, U_Lider = @Lider, U_Miembros = @Miembros, U_PuntoVentaId = @PuntoVentaId, U_Zona = @Zona
        WHERE DocEntry = @DocEntry;

        -- Actualizar tarifas
        UPDATE dbo.mae_pdm_tarifas_cuadrillas
        SET U_Carga25kg = @Carga25kg, U_Carga50kg = @Carga50kg, U_Descarga25kg = @Descarga25kg, U_Descarga50kg = @Descarga50kg, U_Traslado = @Traslado, U_Apaleo = @Apaleo, U_UltAct = GETDATE()
        WHERE U_CuadrillaId = @DocEntry;

        COMMIT TRANSACTION;

        -- Obtener objeto actualizado
        SET @json = (
            SELECT 
                C.DocEntry AS [codigoCuadrilla],
                C.U_Nombre AS [nombreCuadrilla],
                C.U_Lider AS [liderCuadrilla],
                C.U_Miembros AS [miembrosCuadrilla],
                C.U_PuntoVentaId AS [puntoVentaId],
                C.U_Zona AS [zonaCuadrilla],
                T.U_Carga25kg AS [carga25kg],
                T.U_Carga50kg AS [carga50kg],
                T.U_Descarga25kg AS [descarga25kg],
                T.U_Descarga50kg AS [descarga50kg],
                T.U_Traslado AS [traslado],
                T.U_Apaleo AS [apaleo]
            FROM dbo.mae_pdm_cuadrillas C
            LEFT JOIN dbo.mae_pdm_tarifas_cuadrillas T ON C.DocEntry = T.U_CuadrillaId
            WHERE C.DocEntry = @DocEntry
            FOR JSON PATH, WITHOUT_ARRAY_WRAPPER
        );

        SELECT @estado AS estado, @mensaje AS mensaje, @json AS data;
    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0 ROLLBACK TRANSACTION;
        SELECT ERROR_NUMBER() AS estado, ERROR_MESSAGE() AS mensaje, NULL AS data;
    END CATCH
END;
GO

-- 4. proc_pdm_eliminar_cuadrilla
-- Elimina una cuadrilla y sus tarifas, validando que no tenga maniobras en proceso
CREATE OR ALTER PROCEDURE proc_pdm_eliminar_cuadrilla
    @DocEntry INT
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRY
        DECLARE @estado INT = 0;
        DECLARE @mensaje VARCHAR(500) = 'OK';

        -- Validar si tiene maniobras registradas en proceso (U_CorteId NULL)
        IF EXISTS (
            SELECT 1 FROM dbo.mov_pdm_registro_maniobras
            WHERE U_CuadrillaId = @DocEntry AND U_CorteId IS NULL
        )
        BEGIN
            SET @estado = -1;
            SET @mensaje = 'No se puede eliminar la cuadrilla porque tiene maniobras registradas en proceso (pendientes de liquidar).';
            SELECT @estado AS estado, @mensaje AS mensaje, NULL AS data;
            RETURN;
        END;

        BEGIN TRANSACTION;

        -- Eliminar tarifas
        DELETE FROM dbo.mae_pdm_tarifas_cuadrillas WHERE U_CuadrillaId = @DocEntry;

        -- Eliminar cuadrilla
        DELETE FROM dbo.mae_pdm_cuadrillas WHERE DocEntry = @DocEntry;

        COMMIT TRANSACTION;

        SELECT @estado AS estado, @mensaje AS mensaje, NULL AS data;
    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0 ROLLBACK TRANSACTION;
        SELECT ERROR_NUMBER() AS estado, ERROR_MESSAGE() AS mensaje, NULL AS data;
    END CATCH
END;
GO

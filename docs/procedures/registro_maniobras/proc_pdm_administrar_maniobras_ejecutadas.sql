CREATE OR ALTER PROCEDURE proc_pdm_administrar_maniobras_ejecutadas
(
    @Opcion             INT = 1, -- 1: Insertar, 2: Actualizar, 3: Eliminar lógico
    @IdManiobra         INT = 0,
    @idTipoManiobra     INT = 0,
    @idPuntoVenta       VARCHAR(20) = '',
    @idCuadrilla        INT = 0,
    @serieDocumento     SMALLINT = 0,
    @documentoSap       VARCHAR(50) = '',
    @toneladas          NUMERIC(10,2) = 0,
    @usuario            INT = 0,
    @fecha              DATETIME = NULL,
    @numLineaDocumento  INT = -1
)
AS
/**********************************************************************************
-- 1. Alta Manual
EXEC dbo.proc_pdm_administrar_maniobras_ejecutadas
    @Opcion = 1,
    @idTipoManiobra = 2,
    @idPuntoVenta = 'ANGOS01',
    @idCuadrilla = 3,
    @serieDocumento = 0,
    @documentoSap = '0',
    @toneladas = 24.50,
    @usuario = 15,
    @fecha = '2026-08-10',
    @numLineaDocumento = 1;

-- 2. Editar Maniobra
EXEC dbo.proc_pdm_administrar_maniobras_ejecutadas
    @Opcion = 2,
    @IdManiobra = 105,
    @idTipoManiobra = 2,
    @idPuntoVenta = 'ANGOS01',
    @idCuadrilla = 3,
    @documentoSap = 'SAP-12345',
    @toneladas = 30.00,
    @usuario = 15;

-- 3. Eliminar Lógico (Soft Delete)
EXEC dbo.proc_pdm_administrar_maniobras_ejecutadas
    @Opcion = 3,
    @IdManiobra = 105,
    @usuario = 15;

*********************************************************************************/
BEGIN
    SET NOCOUNT ON;

    DECLARE @estado INT = 0;
    DECLARE @mensaje VARCHAR(500) = '';
    DECLARE @tarifas NUMERIC(10,2) = 0.00;
    DECLARE @idCorteAsignado INT = 0;

    BEGIN TRY

        /* =========================================================================
           OPCION 1: INSERTAR (Alta Manual)
        ========================================================================= */
        IF @Opcion = 1
        BEGIN
            SET @tarifas = COALESCE((SELECT num_tarifa 
                            FROM ctl_pdm_tarifas_cuadrillas 
                            WHERE idu_cuadrilla = @idCuadrilla 
                            AND idu_tipomaniobra = @idTipoManiobra), 0);

            IF @tarifas >= 0
            BEGIN
                INSERT INTO dbo.mov_pdm_maniobras_ejecutadas
                (
                    idu_tipomaniobra,
                    num_tarifa_maniobra,
                    idu_punto_venta,
                    idu_cuadrilla,
                    num_tipodocumento,
                    num_documentosap,
                    num_toneladas,
                    opc_estatus,
                    num_usuario_registro,
                    num_usuario_modifico,
                    fec_registro,
                    fec_actualizacion,
                    num_linea_documento
                )
                VALUES
                (
                    @idTipoManiobra,
                    @tarifas,
                    @idPuntoVenta,
                    @idCuadrilla,
                    @serieDocumento,
                    @documentoSap,
                    @toneladas,
                    1,
                    @usuario,
                    @usuario,
                    COALESCE(@fecha, GETDATE()),
                    GETDATE(),
                    @numLineaDocumento
                );

                SET @mensaje = 'Maniobra registrada correctamente.';
                SET @estado = 0;
            END
            ELSE
            BEGIN
                SET @mensaje = 'No existe tarifa para esa maniobra, favor de registrar la tarifa dentro de la cuadrilla.';
                SET @estado = -100;
            END
        END

        /* =========================================================================
           OPCION 2: ACTUALIZAR (Edición)
        ========================================================================= */
        ELSE IF @Opcion = 2
        BEGIN
            -- Validar existencia y que no esté liquidada
            IF NOT EXISTS (SELECT 1 FROM dbo.mov_pdm_maniobras_ejecutadas WHERE idu_maniobra = @IdManiobra AND opc_estatus = 1)
            BEGIN
                SET @mensaje = 'La maniobra no existe o ya ha sido eliminada.';
                SET @estado = -101;
            END
            ELSE
            BEGIN
                -- Verificar si está liquidada en un corte confirmado (opc_estatus = 1 en mae_pdm_cortes_liquidacion)
                SELECT @idCorteAsignado = M.idu_corte
                FROM dbo.mov_pdm_maniobras_ejecutadas M
                LEFT JOIN dbo.mae_pdm_cortes_liquidacion CL ON M.idu_corte = CL.idu_corte
                WHERE M.idu_maniobra = @IdManiobra AND ISNULL(CL.opc_estatus, 0) = 1;

                IF @idCorteAsignado > 0
                BEGIN
                    SET @mensaje = 'No se puede editar una maniobra que ya ha sido liquidada.';
                    SET @estado = -102;
                END
                ELSE
                BEGIN
                    -- Obtener tarifa vigente de la cuadrilla y tipo
                    SET @tarifas = COALESCE((SELECT num_tarifa 
                                    FROM ctl_pdm_tarifas_cuadrillas 
                                    WHERE idu_cuadrilla = @idCuadrilla 
                                    AND idu_tipomaniobra = @idTipoManiobra), 0);

                    -- Obtener id_corte actual si está en un borrador
                    SELECT @idCorteAsignado = idu_corte 
                    FROM dbo.mov_pdm_maniobras_ejecutadas 
                    WHERE idu_maniobra = @IdManiobra;

                    UPDATE dbo.mov_pdm_maniobras_ejecutadas
                    SET
                        idu_tipomaniobra = @idTipoManiobra,
                        num_tarifa_maniobra = @tarifas,
                        idu_punto_venta = @idPuntoVenta,
                        idu_cuadrilla = @idCuadrilla,
                        num_documentosap = @documentoSap,
                        num_toneladas = @toneladas,
                        num_usuario_modifico = @usuario,
                        fec_actualizacion = GETDATE(),
                        fec_registro = COALESCE(@fecha, fec_registro)
                    WHERE idu_maniobra = @IdManiobra;

                    -- Si la maniobra pertenecía a un corte borrador, recalcular totales del corte
                    IF @idCorteAsignado > 0
                    BEGIN
                        DECLARE @TotalTon NUMERIC(10,2) = 0;
                        DECLARE @TotalMon NUMERIC(10,2) = 0;

                        SELECT 
                            @TotalTon = ISNULL(SUM(m.num_toneladas), 0),
                            @TotalMon = ISNULL(SUM(m.num_toneladas * m.num_tarifa_maniobra), 0)
                        FROM dbo.mov_pdm_maniobras_ejecutadas m
                        WHERE m.idu_corte = @idCorteAsignado AND m.opc_estatus = 1;

                        UPDATE dbo.mae_pdm_cortes_liquidacion
                        SET num_toneladas_total = @TotalTon,
                            monto_total = @TotalMon
                        WHERE idu_corte = @idCorteAsignado;
                    END

                    SET @mensaje = 'Maniobra actualizada correctamente.';
                    SET @estado = 0;
                END
            END
        END

        /* =========================================================================
           OPCION 3: ELIMINAR LÓGICO (Soft Delete)
        ========================================================================= */
        ELSE IF @Opcion = 3
        BEGIN
            -- Validar existencia
            IF NOT EXISTS (SELECT 1 FROM dbo.mov_pdm_maniobras_ejecutadas WHERE idu_maniobra = @IdManiobra AND opc_estatus = 1)
            BEGIN
                SET @mensaje = 'La maniobra no existe o ya ha sido eliminada.';
                SET @estado = -101;
            END
            ELSE
            BEGIN
                -- Verificar si está liquidada en un corte confirmado (opc_estatus = 1 en mae_pdm_cortes_liquidacion)
                DECLARE @corteLiquidado INT = 0;
                SELECT @corteLiquidado = ISNULL(CL.opc_estatus, 0), @idCorteAsignado = M.idu_corte
                FROM dbo.mov_pdm_maniobras_ejecutadas M
                LEFT JOIN dbo.mae_pdm_cortes_liquidacion CL ON M.idu_corte = CL.idu_corte
                WHERE M.idu_maniobra = @IdManiobra;

                IF @corteLiquidado = 1
                BEGIN
                    SET @mensaje = 'No se puede eliminar una maniobra que ya ha sido liquidada.';
                    SET @estado = -102;
                END
                ELSE
                BEGIN
                    -- Soft delete: opc_estatus = 0, desvincular de corte
                    UPDATE dbo.mov_pdm_maniobras_ejecutadas
                    SET
                        opc_estatus = 0,
                        idu_corte = 0,
                        num_usuario_modifico = @usuario,
                        fec_actualizacion = GETDATE()
                    WHERE idu_maniobra = @IdManiobra;

                    -- Si estaba en un corte borrador en proceso, recalcular totales del corte
                    IF @idCorteAsignado > 0
                    BEGIN
                        DECLARE @TotalTonDel NUMERIC(10,2) = 0;
                        DECLARE @TotalMonDel NUMERIC(10,2) = 0;

                        SELECT 
                            @TotalTonDel = ISNULL(SUM(m.num_toneladas), 0),
                            @TotalMonDel = ISNULL(SUM(m.num_toneladas * m.num_tarifa_maniobra), 0)
                        FROM dbo.mov_pdm_maniobras_ejecutadas m
                        WHERE m.idu_corte = @idCorteAsignado AND m.opc_estatus = 1;

                        UPDATE dbo.mae_pdm_cortes_liquidacion
                        SET num_toneladas_total = @TotalTonDel,
                            monto_total = @TotalMonDel
                        WHERE idu_corte = @idCorteAsignado;
                    END

                    SET @mensaje = 'Maniobra eliminada correctamente.';
                    SET @estado = 0;
                END
            END
        END

        SELECT
            @estado AS estado,
            @mensaje AS mensaje;

    END TRY
    BEGIN CATCH

        SELECT
            ERROR_NUMBER() AS estado,
            ERROR_MESSAGE() AS mensaje;

    END CATCH
END
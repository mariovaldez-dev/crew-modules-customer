-- ==============================================================================
-- 7. SP: ELIMINAR / DESOLVER BORRADOR DE CORTE
-- ==============================================================================
CREATE OR ALTER PROCEDURE proc_pdm_corte_eliminar
    @CorteID INT
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRY
        -- 1. Validar si el corte existe y su estatus
        DECLARE @Estatus INT;

        SELECT @Estatus = opc_estatus
        FROM mae_pdm_cortes_liquidacion
        WHERE idu_corte = @CorteID;

        IF @Estatus IS NULL
        BEGIN
            SELECT 400 AS estatus, 'El corte especificado no existe.' AS mensaje, '{}' AS resultado;
            RETURN;
        END

        IF @Estatus = 1
        BEGIN
            SELECT 400 AS estatus, 'Un corte ya verificado/confirmado no puede ser eliminado.' AS mensaje, '{}' AS resultado;
            RETURN;
        END

        -- 2. Desvincular las maniobras atadas a este corte (regresar idu_corte = 0)
        UPDATE mov_pdm_maniobras_ejecutadas
        SET idu_corte = 0
        WHERE idu_corte = @CorteID;

        -- 3. Eliminar confirmaciones de cuadrillas asociadas al corte
        DELETE FROM mov_pdm_cortes_cuadrillas_confirmacion
        WHERE idu_corte = @CorteID;

        -- 4. Eliminar el encabezado del corte en borrador
        DELETE FROM mae_pdm_cortes_liquidacion
        WHERE idu_corte = @CorteID;

        -- 5. Retornar respuesta de éxito
        SELECT 0 AS estatus, 'El borrador de corte fue eliminado exitosamente.' AS mensaje, '{}' AS resultado;

    END TRY
    BEGIN CATCH
        SELECT 500 AS estatus, ERROR_MESSAGE() AS mensaje, '{}' AS resultado;
    END CATCH
END
GO

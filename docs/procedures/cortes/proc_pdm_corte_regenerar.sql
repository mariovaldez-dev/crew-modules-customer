-- ==============================================================================
-- 6. SP: REGENERAR CORTE
-- ==============================================================================
CREATE OR ALTER PROCEDURE proc_pdm_corte_regenerar
    @CorteID INT
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRY
        -- 1. Validar que siga en borrador
        DECLARE @Zona VARCHAR(20);
        DECLARE @FechaInicio DATE;
        DECLARE @FechaFin DATE;

        SELECT @Zona = clv_zona, @FechaInicio = fec_inicio, @FechaFin = fec_fin 
        FROM mae_pdm_cortes_liquidacion 
        WHERE idu_corte = @CorteID AND opc_estatus = 0;

        IF @Zona IS NULL
        BEGIN
            SELECT 400 AS estatus, 'El corte ya fue confirmado y no puede ser regenerado.' AS mensaje, '{}' AS resultado;
            RETURN;
        END

        -- 2. Soltar las maniobras atadas al corte (regresar su idu_corte a 0)
        UPDATE mov_pdm_maniobras_ejecutadas
        SET idu_corte = 0
        WHERE idu_corte = @CorteID;

        -- 3. Eliminar confirmaciones de cuadrillas
        DELETE FROM mov_pdm_cortes_cuadrillas_confirmacion WHERE idu_corte = @CorteID;

        -- 4. Eliminar el corte actual (borrador)
        DELETE FROM mae_pdm_cortes_liquidacion WHERE idu_corte = @CorteID;

        -- 5. Mandar llamar al Generar de nuevo usando las fechas originales
        -- (No retornamos directamente desde este scope el resultado del execute, lo capturamos o reescribimos el EXEC)
        -- Para mantener simple el estatus de retorno, devolvemos OK y el front debe llamar a Generar() después.
        
        SELECT 0 AS estatus, 'Borrador cancelado correctamente. Listo para regenerar.' AS mensaje, '{}' AS resultado;
    END TRY
    BEGIN CATCH
        SELECT 500 AS estatus, ERROR_MESSAGE() AS mensaje, '{}' AS resultado;
    END CATCH
END
GO

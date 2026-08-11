-- ==============================================================================
-- 6. SP: REGENERAR CORTE
-- ==============================================================================
CREATE OR ALTER PROCEDURE proc_pdm_corte_regenerar
    @CorteID INT
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRY
        -- 1. Validar que siga en borrador y obtener parámetros originales
        DECLARE @Zona VARCHAR(20);
        DECLARE @FechaInicio DATE;
        DECLARE @FechaFin DATE;

        SELECT @Zona = clv_zona, @FechaInicio = fec_inicio, @FechaFin = fec_fin 
        FROM mae_pdm_cortes_liquidacion 
        WHERE idu_corte = @CorteID AND opc_estatus = 0;

        IF @Zona IS NULL
        BEGIN
            SELECT 400 AS estatus, 'El corte ya fue confirmado o no existe, no puede ser regenerado.' AS mensaje, '{}' AS resultado;
            RETURN;
        END

        -- 2. Soltar las maniobras atadas al corte (regresar su idu_corte a 0)
        UPDATE mov_pdm_maniobras_ejecutadas
        SET idu_corte = 0
        WHERE idu_corte = @CorteID;

        -- 3. Eliminar confirmaciones de cuadrillas para este corte
        DELETE FROM mov_pdm_cortes_cuadrillas_confirmacion WHERE idu_corte = @CorteID;

        -- 4. Eliminar el corte actual (borrador antiguo)
        DELETE FROM mae_pdm_cortes_liquidacion WHERE idu_corte = @CorteID;

        -- 5. Generar el nuevo borrador de corte automáticamente con las mismas fechas
        EXEC proc_pdm_corte_generar @Zona = @Zona, @FechaInicio = @FechaInicio, @FechaFin = @FechaFin;

    END TRY
    BEGIN CATCH
        SELECT 500 AS estatus, ERROR_MESSAGE() AS mensaje, '{}' AS resultado;
    END CATCH
END

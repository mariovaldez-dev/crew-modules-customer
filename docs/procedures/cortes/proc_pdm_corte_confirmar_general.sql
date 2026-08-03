-- ==============================================================================
-- 5. SP: CONFIRMAR GENERAL
-- ==============================================================================
CREATE OR ALTER PROCEDURE proc_pdm_corte_confirmar_general
    @CorteID INT
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRY
        -- Validar estado
        IF NOT EXISTS (SELECT 1 FROM mae_pdm_cortes_liquidacion WHERE idu_corte = @CorteID AND opc_estatus = 0)
        BEGIN
            SELECT 400 AS estatus, 'El corte no existe o ya está confirmado.' AS mensaje, '{}' AS resultado;
            RETURN;
        END

        -- Validar que TODAS las cuadrillas involucradas en las maniobras del corte estén confirmadas
        DECLARE @CuadrillasInvolucradas INT = (SELECT COUNT(DISTINCT idu_cuadrilla) FROM mov_pdm_maniobras_ejecutadas WHERE idu_corte = @CorteID);
        DECLARE @CuadrillasConfirmadas INT = (SELECT COUNT(*) FROM mov_pdm_cortes_cuadrillas_confirmacion WHERE idu_corte = @CorteID);

        IF @CuadrillasInvolucradas > @CuadrillasConfirmadas
        BEGIN
            SELECT 400 AS estatus, 'Faltan cuadrillas por confirmar su desglose.' AS mensaje, '{}' AS resultado;
            RETURN;
        END

        -- Todo OK. Generar folio LIQ-00X
        DECLARE @NuevoFolio VARCHAR(20) = 'LIQ-' + RIGHT('000' + CAST(@CorteID AS VARCHAR), 3);

        UPDATE mae_pdm_cortes_liquidacion
        SET opc_estatus = 1,
            folio_corte = @NuevoFolio
        WHERE idu_corte = @CorteID;

        SELECT 0 AS estatus, 'Corte general confirmado exitosamente.' AS mensaje, 
        (SELECT @NuevoFolio AS folio_corte FOR JSON PATH, WITHOUT_ARRAY_WRAPPER) AS resultado;
    END TRY
    BEGIN CATCH
        SELECT 500 AS estatus, ERROR_MESSAGE() AS mensaje, '{}' AS resultado;
    END CATCH
END
GO

-- ==============================================================================
-- 4. SP: CONFIRMAR CUADRILLA
-- ==============================================================================
CREATE OR ALTER PROCEDURE proc_pdm_corte_confirmar_cuadrilla
    @CorteID INT,
    @CuadrillaID INT
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRY
        IF NOT EXISTS (SELECT 1 FROM mae_pdm_cortes_liquidacion WHERE idu_corte = @CorteID AND opc_estatus = 0)
        BEGIN
            SELECT 400 AS estatus, 'El corte no existe o ya está confirmado general.' AS mensaje, '{}' AS resultado;
            RETURN;
        END

        IF NOT EXISTS (SELECT 1 FROM mov_pdm_cortes_cuadrillas_confirmacion WHERE idu_corte = @CorteID AND idu_cuadrilla = @CuadrillaID)
        BEGIN
            INSERT INTO mov_pdm_cortes_cuadrillas_confirmacion (idu_corte, idu_cuadrilla)
            VALUES (@CorteID, @CuadrillaID);
        END

        SELECT 0 AS estatus, 'Cuadrilla confirmada exitosamente.' AS mensaje, '{}' AS resultado;
    END TRY
    BEGIN CATCH
        SELECT 500 AS estatus, ERROR_MESSAGE() AS mensaje, '{}' AS resultado;
    END CATCH
END
GO

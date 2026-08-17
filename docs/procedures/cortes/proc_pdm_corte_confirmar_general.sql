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

        -- Validar que al menos una cuadrilla esté confirmada
        DECLARE @CuadrillasConfirmadas INT = (SELECT COUNT(*) FROM mov_pdm_cortes_cuadrillas_confirmacion WHERE idu_corte = @CorteID);

        IF @CuadrillasConfirmadas = 0
        BEGIN
            SELECT 400 AS estatus, 'Debe confirmar al menos una cuadrilla antes de confirmar el corte.' AS mensaje, '{}' AS resultado;
            RETURN;
        END

        -- Desvincular las maniobras de las cuadrillas que NO fueron confirmadas en este corte
        UPDATE mov_pdm_maniobras_ejecutadas
        SET idu_corte = 0
        WHERE idu_corte = @CorteID
          AND idu_cuadrilla NOT IN (
              SELECT idu_cuadrilla 
              FROM mov_pdm_cortes_cuadrillas_confirmacion 
              WHERE idu_corte = @CorteID
          );

        -- Recalcular los totales del corte considerando únicamente las maniobras confirmadas
        DECLARE @TotalToneladas NUMERIC(10,2) = 0;
        DECLARE @TotalMonto NUMERIC(10,2) = 0;

        SELECT 
            @TotalToneladas = ISNULL(SUM(m.num_toneladas), 0),
            @TotalMonto = ISNULL(SUM(m.num_toneladas * ISNULL(t.num_tarifa, 0)), 0)
        FROM mov_pdm_maniobras_ejecutadas m
        LEFT JOIN ctl_pdm_tarifas_cuadrillas t 
            ON m.idu_cuadrilla = t.idu_cuadrilla AND m.idu_tipomaniobra = t.idu_tipomaniobra
        WHERE m.idu_corte = @CorteID;

        -- Generar folio LIQ-00X y confirmar el encabezado del corte con los totales actualizados
        DECLARE @NuevoFolio VARCHAR(20) = 'LIQ-' + RIGHT('000' + CAST(@CorteID AS VARCHAR), 3);

        UPDATE mae_pdm_cortes_liquidacion
        SET opc_estatus = 1,
            folio_corte = @NuevoFolio,
            num_toneladas_total = @TotalToneladas,
            monto_total = @TotalMonto
        WHERE idu_corte = @CorteID;

        SELECT 0 AS estatus, 'Corte general confirmado exitosamente.' AS mensaje, 
        (SELECT @NuevoFolio AS folio_corte FOR JSON PATH, WITHOUT_ARRAY_WRAPPER) AS resultado;
    END TRY
    BEGIN CATCH
        SELECT 500 AS estatus, ERROR_MESSAGE() AS mensaje, '{}' AS resultado;
    END CATCH
END
GO

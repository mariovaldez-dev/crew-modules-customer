-- ==============================================================================
-- 7. SP: CONSULTAR CORTE POR ID
-- ==============================================================================
CREATE OR ALTER PROCEDURE proc_pdm_corte_consultar_por_id
    @CorteID INT
AS
BEGIN
    SET NOCOUNT ON;
    SET ANSI_WARNINGS OFF;
    BEGIN TRY
        IF NOT EXISTS (SELECT 1 FROM mae_pdm_cortes_liquidacion WHERE idu_corte = @CorteID)
        BEGIN
            SELECT 404 AS estatus, 'No se encontró el corte solicitado.' AS mensaje, '{}' AS resultado;
            RETURN;
        END

        DECLARE @JSONResult NVARCHAR(MAX) = (
            SELECT 
                corte.idu_corte AS corteId,
                corte.folio_corte AS folio,
                CONVERT(VARCHAR, corte.fec_inicio, 120) AS fechaInicio,
                CONVERT(VARCHAR, corte.fec_fin, 120) AS fechaFin,
                corte.num_toneladas_total AS toneladasTotal,
                corte.monto_total AS montoTotal,
                CASE WHEN corte.opc_estatus = 0 THEN 'Borrador' ELSE 'Confirmado' END AS estado,
                (
                    SELECT 
                        c.idu_cuadrilla AS cuadrillaId,
                        c.nom_cuadrilla AS nombreCuadrilla,
                        c.idu_punto_venta AS almacenId,
                        COUNT(m.idu_maniobra) AS totalManiobras,
                        SUM(m.num_toneladas) AS totalToneladas,
                        SUM(m.num_toneladas * ISNULL(t.num_tarifa, 0)) AS montoCuadrilla,
                        CASE WHEN conf.idu_corte IS NOT NULL THEN 1 ELSE 0 END AS estaConfirmada,
                        (
                            SELECT 
                                subM.idu_maniobra AS maniobraId,
                                subM.fec_registro AS fecha,
                                tipo.nom_maniobra AS concepto,
                                subM.num_toneladas AS toneladas,
                                ISNULL(subT.num_tarifa, 0) AS tarifaAplicada,
                                (subM.num_toneladas * ISNULL(subT.num_tarifa, 0)) AS montoTotal
                            FROM mov_pdm_maniobras_ejecutadas subM
                            INNER JOIN cat_pdm_tipos_maniobras tipo ON subM.idu_tipomaniobra = tipo.idu_tipomaniobra
                            LEFT JOIN ctl_pdm_tarifas_cuadrillas subT ON subM.idu_cuadrilla = subT.idu_cuadrilla AND subM.idu_tipomaniobra = subT.idu_tipomaniobra
                            WHERE subM.idu_corte = corte.idu_corte AND subM.idu_cuadrilla = c.idu_cuadrilla
                            FOR JSON PATH
                        ) AS maniobrasDetalle
                    FROM mov_pdm_maniobras_ejecutadas m
                    INNER JOIN mae_pdm_cuadrillas c ON m.idu_cuadrilla = c.idu_cuadrilla
                    LEFT JOIN ctl_pdm_tarifas_cuadrillas t ON m.idu_cuadrilla = t.idu_cuadrilla AND m.idu_tipomaniobra = t.idu_tipomaniobra
                    LEFT JOIN mov_pdm_cortes_cuadrillas_confirmacion conf ON conf.idu_corte = corte.idu_corte AND conf.idu_cuadrilla = c.idu_cuadrilla
                    WHERE m.idu_corte = corte.idu_corte
                    GROUP BY c.idu_cuadrilla, c.nom_cuadrilla, c.idu_punto_venta, conf.idu_corte
                    FOR JSON PATH
                ) AS cuadrillas
            FROM mae_pdm_cortes_liquidacion corte
            WHERE corte.idu_corte = @CorteID
            FOR JSON PATH, WITHOUT_ARRAY_WRAPPER
        );

        SELECT 0 AS estatus, 'Consulta exitosa' AS mensaje, ISNULL(@JSONResult, '{}') AS resultado;

    END TRY
    BEGIN CATCH
        SELECT 500 AS estatus, ERROR_MESSAGE() AS mensaje, '{}' AS resultado;
    END CATCH
END
GO

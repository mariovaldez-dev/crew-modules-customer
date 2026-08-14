-- ==============================================================================
-- 3. SP: CONSULTAR CORTE
-- ==============================================================================
CREATE OR ALTER PROCEDURE proc_pdm_corte_consultar
    @Zona VARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRY
        IF NOT EXISTS (SELECT 1 FROM mae_pdm_cortes_liquidacion WHERE clv_zona = @Zona)
        BEGIN
            SELECT 404 AS estatus, 'No hay cortes para esta zona.' AS mensaje, '[]' AS resultado;
            RETURN;
        END

        DECLARE @JSONResult NVARCHAR(MAX) = (
            SELECT 
                corte.idu_corte AS corteId,
                corte.folio_corte AS folio,
                corte.clv_zona AS zonaId,
                ISNULL(z.SlpName, corte.clv_zona) AS zonaNombre,
                CONVERT(VARCHAR, corte.fec_inicio, 120) AS fechaInicio,
                CONVERT(VARCHAR, corte.fec_fin, 120) AS fechaFin,
                corte.num_toneladas_total AS toneladasTotal,
                corte.monto_total AS montoTotal,
                CASE WHEN corte.opc_estatus = 0 THEN 'Borrador' ELSE 'Confirmado' END AS estado,
                (
                    -- Agrupación por cuadrilla para el frontend
                    SELECT 
                        c.idu_cuadrilla AS cuadrillaId,
                        c.nom_cuadrilla AS nombreCuadrilla,
                        c.idu_punto_venta AS almacenId,
                        ISNULL(pun.WhsName, c.idu_punto_venta) AS almacenNombre,
                        COUNT(m.idu_maniobra) AS totalManiobras,
                        SUM(m.num_toneladas) AS totalToneladas,
                        SUM(m.num_toneladas * ISNULL(t.num_tarifa, 0)) AS montoCuadrilla,
                        -- Si existe en la tabla de confirmaciones, está confirmada
                        CASE WHEN conf.idu_corte IS NOT NULL THEN 1 ELSE 0 END AS estaConfirmada,
                        -- Detalle de maniobras por cuadrilla
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
                    LEFT JOIN SAP.SBO_Impulsora_PROD.dbo.OWHS pun ON pun.WhsCode = c.idu_punto_venta COLLATE SQL_Latin1_General_CP850_CI_AS
                    LEFT JOIN ctl_pdm_tarifas_cuadrillas t ON m.idu_cuadrilla = t.idu_cuadrilla AND m.idu_tipomaniobra = t.idu_tipomaniobra
                    LEFT JOIN mov_pdm_cortes_cuadrillas_confirmacion conf ON conf.idu_corte = corte.idu_corte AND conf.idu_cuadrilla = c.idu_cuadrilla
                    WHERE m.idu_corte = corte.idu_corte
                    GROUP BY c.idu_cuadrilla, c.nom_cuadrilla, c.idu_punto_venta, pun.WhsName, conf.idu_corte
                    FOR JSON PATH
                ) AS cuadrillas
            FROM mae_pdm_cortes_liquidacion corte
            LEFT JOIN SAP.SBO_Impulsora_PROD.dbo.OSLP z ON z.Memo = corte.clv_zona COLLATE SQL_Latin1_General_CP850_CI_AS AND z.Active = 'Y'
            WHERE corte.clv_zona = @Zona
            ORDER BY corte.fec_registro DESC
            FOR JSON PATH
        );

        SELECT 0 AS estatus, 'Cortes consultados correctamente' AS mensaje, ISNULL(@JSONResult, '[]') AS resultado;

    END TRY
    BEGIN CATCH
        SELECT 500 AS estatus, ERROR_MESSAGE() AS mensaje, '[]' AS resultado;
    END CATCH
END

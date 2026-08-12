CREATE OR ALTER PROCEDURE proc_pdm_dashboard_inicio
    @Zona VARCHAR(20) = NULL
    /*
     EXEC proc_pdm_dashboard_inicio
     EXEC proc_pdm_dashboard_inicio 'FA'
     */
AS
BEGIN
    SET NOCOUNT ON;

    BEGIN TRY
        -- 1. Normalizar la zona (si viene vacía, tratarla como NULL)
        IF LTRIM(RTRIM(@Zona)) = '' SET @Zona = NULL;

        -- 2. Variables para contadores estadísticos
        DECLARE @TotalManiobras INT = 0;
        DECLARE @TotalCuadrillas INT = 0;
        DECLARE @ToneladasTotales DECIMAL(10,2) = 0.0;
        DECLARE @ManiobrasLiquidadas INT = 0;
        DECLARE @ManiobrasEnProceso INT = 0;
        DECLARE @FolioCorteActual VARCHAR(50) = NULL;
        DECLARE @CuadrillasEnCorte INT = 0;

        -- 3. Calcular Catálogo de Maniobras Activas
        SELECT @TotalManiobras = COUNT(*) 
        FROM cat_pdm_tipos_maniobras 
        WHERE opc_estatus = 1;

        -- 4. Calcular Cuadrillas Activas de la zona
        SELECT @TotalCuadrillas = COUNT(*) 
        FROM mae_pdm_cuadrillas 
        WHERE (@Zona IS NULL OR clv_zona = @Zona) AND opc_estatus = 1;

        -- 5. Calcular Estadísticas de Maniobras Registradas
        --    Liquidada   = corte general confirmado (opc_estatus = 1)
        --    Confirmada  = cuadrilla confirmada en corte borrador
        --    En proceso  = sin corte o en borrador sin confirmación de cuadrilla
        SELECT 
            @ToneladasTotales    = ISNULL(SUM(m.num_toneladas), 0),
            @ManiobrasLiquidadas = SUM(CASE WHEN ISNULL(CL.opc_estatus, 0) = 1 THEN 1 ELSE 0 END),
            @ManiobrasEnProceso  = SUM(CASE WHEN ISNULL(CL.opc_estatus, 0) = 1 THEN 0 ELSE 1 END)
            
        FROM mov_pdm_maniobras_ejecutadas m
        INNER JOIN mae_pdm_cuadrillas c ON m.idu_cuadrilla = c.idu_cuadrilla
        LEFT JOIN dbo.mae_pdm_cortes_liquidacion CL ON m.idu_corte = CL.idu_corte
        WHERE (@Zona IS NULL OR c.clv_zona = @Zona) 
          AND m.opc_estatus = 1;

        -- 6. Construir el JSON final
        DECLARE @JSONResult NVARCHAR(MAX) = (
            SELECT 
                @TotalManiobras AS totalManiobras,
                @TotalCuadrillas AS totalCuadrillas,
                @ToneladasTotales AS toneladasTotales,
                @ManiobrasLiquidadas AS maniobrasLiquidadas,
                @ManiobrasEnProceso AS maniobrasEnProceso,
                (
                    -- Objeto del corte (NULL para Admin)
                    SELECT @FolioCorteActual AS folio, @CuadrillasEnCorte AS totalCuadrillas
                    FOR JSON PATH, WITHOUT_ARRAY_WRAPPER
                ) AS corteActual,
                (
                    -- Arreglo de los 5 últimos registros
                    -- estatusCiclo: 0 = En proceso | 1 = Confirmada | 2 = Liquidada
                    SELECT TOP 5 
                        'MAN-' + RIGHT('000000' + CAST(m.idu_maniobra AS VARCHAR(20)), 6) AS folio, 
                        m.fec_registro AS fecha,
                        c.nom_cuadrilla AS cuadrillaNombre,
                        m.idu_punto_venta AS almacenNombre,
                        t.nom_maniobra AS tipoManiobraNombre,
                        m.num_toneladas AS toneladas,
                        m.idu_corte AS idCorte,
                        CASE
                            WHEN ISNULL(CL2.opc_estatus, 0) = 1 THEN 'Liquidada'
                            WHEN CC.idu_corte IS NOT NULL         THEN 'Confirmada'
                            ELSE                                       'En proceso'
                        END AS estado
                    FROM mov_pdm_maniobras_ejecutadas m
                    INNER JOIN mae_pdm_cuadrillas c ON m.idu_cuadrilla = c.idu_cuadrilla
                    INNER JOIN cat_pdm_tipos_maniobras t ON m.idu_tipomaniobra = t.idu_tipomaniobra
                    LEFT JOIN dbo.mae_pdm_cortes_liquidacion CL2 ON m.idu_corte = CL2.idu_corte
                    LEFT JOIN dbo.mov_pdm_cortes_cuadrillas_confirmacion CC
                        ON m.idu_corte = CC.idu_corte AND m.idu_cuadrilla = CC.idu_cuadrilla
                    WHERE (@Zona IS NULL OR c.clv_zona = @Zona)
                      AND m.opc_estatus = 1
                    ORDER BY m.fec_registro DESC, m.idu_maniobra DESC
                    FOR JSON PATH
                ) AS recientesRegistros
            FOR JSON PATH, WITHOUT_ARRAY_WRAPPER
        );

        -- 7. Retornar el estándar de la empresa
        SELECT 
            0 AS estatus, 
            'Información recuperada con éxito' AS mensaje, 
            @JSONResult AS resultado;

    END TRY
    BEGIN CATCH
        SELECT 
            -100 AS estatus, 
            ERROR_MESSAGE() AS mensaje, 
            '{}' AS resultado;
    END CATCH
END

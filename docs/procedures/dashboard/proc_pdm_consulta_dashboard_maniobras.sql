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
        SELECT 
            @ToneladasTotales = ISNULL(SUM(m.num_toneladas), 0),
            -- Si idu_corte > 0 está asignada a un corte (Liquidada)
            @ManiobrasLiquidadas = SUM(CASE WHEN ISNULL(m.idu_corte, 0) > 0 THEN 1 ELSE 0 END),
            -- Si idu_corte == 0 está pendiente (En proceso)
            @ManiobrasEnProceso = SUM(CASE WHEN ISNULL(m.idu_corte, 0) = 0 THEN 1 ELSE 0 END)
            
        FROM mov_pdm_maniobras_ejecutadas m
        INNER JOIN mae_pdm_cuadrillas c ON m.idu_cuadrilla = c.idu_cuadrilla
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
                    SELECT TOP 5 
                        'MAN-' + RIGHT('000000' + CAST(m.idu_maniobra AS VARCHAR(20)), 6) AS folio, 
                        m.fec_registro AS fecha,
                        c.nom_cuadrilla AS cuadrillaNombre,
                        m.idu_punto_venta AS almacenNombre,
                        t.nom_maniobra AS tipoManiobraNombre,
                        m.num_toneladas AS toneladas,
                        m.idu_corte AS idCorte,
                        CASE WHEN ISNULL(m.idu_corte, 0) > 0 THEN 'Liquidada' ELSE 'En proceso' END AS estado
                    FROM mov_pdm_maniobras_ejecutadas m
                    INNER JOIN mae_pdm_cuadrillas c ON m.idu_cuadrilla = c.idu_cuadrilla
                    INNER JOIN cat_pdm_tipos_maniobras t ON m.idu_tipomaniobra = t.idu_tipomaniobra
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

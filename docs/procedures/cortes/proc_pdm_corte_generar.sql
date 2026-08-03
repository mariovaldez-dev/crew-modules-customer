-- ==============================================================================
-- 2. SP: GENERAR BORRADOR
-- ==============================================================================
CREATE OR ALTER PROCEDURE proc_pdm_corte_generar
    @Zona VARCHAR(20),
    @FechaInicio DATE,
    @FechaFin DATE
AS
BEGIN
    SET NOCOUNT ON;
    BEGIN TRY
        -- 1. Validar si ya hay un borrador activo para la zona
        IF EXISTS (SELECT 1 FROM mae_pdm_cortes_liquidacion WHERE clv_zona = @Zona AND opc_estatus = 0)
        BEGIN
            SELECT 400 AS estatus, 'Ya existe un corte en borrador para esta zona.' AS mensaje, '{}' AS resultado;
            RETURN;
        END

        -- 2. Insertar encabezado de corte en borrador
        DECLARE @NuevoCorteID INT;
        
        INSERT INTO mae_pdm_cortes_liquidacion (clv_zona, fec_inicio, fec_fin, opc_estatus)
        VALUES (@Zona, @FechaInicio, @FechaFin, 0);

        SET @NuevoCorteID = SCOPE_IDENTITY();

        -- 3. Asignar el corte a las maniobras de la zona que no tengan corte (idu_corte = 0) y estén activas (opc_estatus = 1)
        -- y cuyo fec_registro caiga entre @FechaInicio y @FechaFin.
        UPDATE m
        SET m.idu_corte = @NuevoCorteID
        FROM mov_pdm_maniobras_ejecutadas m
        INNER JOIN mae_pdm_cuadrillas c ON m.idu_cuadrilla = c.idu_cuadrilla
        WHERE c.clv_zona = @Zona
          AND m.idu_corte = 0
          AND m.opc_estatus = 1
          AND CAST(m.fec_registro AS DATE) BETWEEN @FechaInicio AND @FechaFin;

        -- 4. Calcular el monto leyendo las tarifas vigentes (ctl_pdm_tarifas_cuadrillas)
        -- Hacemos la suma total y actualizamos el encabezado
        DECLARE @TotalToneladas NUMERIC(10,2) = 0;
        DECLARE @TotalMonto NUMERIC(10,2) = 0;

        SELECT 
            @TotalToneladas = ISNULL(SUM(m.num_toneladas), 0),
            @TotalMonto = ISNULL(SUM(m.num_toneladas * t.num_tarifa), 0)
        FROM mov_pdm_maniobras_ejecutadas m
        LEFT JOIN ctl_pdm_tarifas_cuadrillas t 
            ON m.idu_cuadrilla = t.idu_cuadrilla AND m.idu_tipomaniobra = t.idu_tipomaniobra
        WHERE m.idu_corte = @NuevoCorteID;

        UPDATE mae_pdm_cortes_liquidacion
        SET num_toneladas_total = @TotalToneladas,
            monto_total = @TotalMonto
        WHERE idu_corte = @NuevoCorteID;

        -- Retornar OK
        SELECT 0 AS estatus, 'Corte en borrador generado correctamente.' AS mensaje, 
        (SELECT @NuevoCorteID AS idu_corte FOR JSON PATH, WITHOUT_ARRAY_WRAPPER) AS resultado;

    END TRY
    BEGIN CATCH
        SELECT 500 AS estatus, ERROR_MESSAGE() AS mensaje, '{}' AS resultado;
    END CATCH
END
GO

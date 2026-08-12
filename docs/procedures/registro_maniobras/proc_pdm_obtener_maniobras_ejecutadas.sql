
CREATE OR ALTER PROCEDURE dbo.proc_pdm_obtener_maniobras_ejecutadas
(
    @ClaveZona VARCHAR(10) = '',
    @FechaInicio DATE = NULL,
    @FechaFin DATE = NULL
)
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @estado INT = -100;
    DECLARE @mensaje VARCHAR(500) = '';
    DECLARE @listaManiobras NVARCHAR(MAX);

    BEGIN TRY
		SET @listaManiobras =
		(
			SELECT
				M.idu_maniobra as idManiobra,
				M.idu_tipomaniobra as idTipoManiobra,
				TM.nom_maniobra as nombreManiobra,
				M.num_tarifa_maniobra as numeroTarifaManiobra,
				M.idu_punto_venta as idPuntoVenta,
				pun.WhsName as nombrePuntoVenta,
				M.idu_cuadrilla as idCuadrilla,
				C.nom_cuadrilla as nombreCuadrilla,
				C.nom_lider_cuadrilla as nombreLiderCuadrilla,
				M.num_tipodocumento as numeroTipoDocumento,
				M.num_documentosap as numeroDocumentoSAP,
				M.num_toneladas as numeroToneladas,
				M.opc_estatus as estatus,
				M.idu_corte as idCorte,
				ISNULL(CL.opc_estatus, 0) as estatusCorte,
				CASE WHEN CC.idu_corte IS NOT NULL THEN 1 ELSE 0 END as estaConfirmada,
                M.fec_registro as fecha
			FROM dbo.mov_pdm_maniobras_ejecutadas M
			INNER JOIN dbo.cat_pdm_tipos_maniobras TM
				ON M.idu_tipomaniobra = TM.idu_tipomaniobra
			INNER JOIN dbo.mae_pdm_cuadrillas C
				ON M.idu_cuadrilla = C.idu_cuadrilla
			INNER JOIN SAP.SBO_Impulsora_PROD.dbo.OWHS AS pun 
				ON pun.WhsCode = C.idu_punto_venta COLLATE SQL_Latin1_General_CP850_CI_AS
			LEFT JOIN dbo.mae_pdm_cortes_liquidacion CL
				ON M.idu_corte = CL.idu_corte
			LEFT JOIN dbo.mov_pdm_cortes_cuadrillas_confirmacion CC
				ON M.idu_corte = CC.idu_corte AND M.idu_cuadrilla = CC.idu_cuadrilla
			WHERE
				M.opc_estatus = 1
				AND C.opc_estatus = 1 
				AND pun.U_SerieSucursal = (CASE WHEN @ClaveZona = '' THEN pun.U_SerieSucursal ELSE @ClaveZona END)
                AND (@FechaInicio IS NULL OR CAST(M.fec_registro AS DATE) >= @FechaInicio)
                AND (@FechaFin IS NULL OR CAST(M.fec_registro AS DATE) <= @FechaFin)
			ORDER BY
				M.fec_registro DESC
			FOR JSON PATH
		);
			
		IF @listaManiobras IS NOT NULL
		BEGIN
			SET @estado = 0;
			SET @mensaje = 'Información obtenida correctamente.';
		END
		ELSE
		BEGIN 
			SET @estado = -100;
			SET @mensaje = 'No hay registros de maniobras para la zona y fechas';
		END

    END TRY
    BEGIN CATCH
		SET @estado =  ERROR_NUMBER()
		SET @mensaje = ERROR_MESSAGE()
    END CATCH

	SELECT
            @estado AS estado,
            @mensaje AS mensaje,
            ISNULL(@listaManiobras, '[]') AS listaManiobras;
END

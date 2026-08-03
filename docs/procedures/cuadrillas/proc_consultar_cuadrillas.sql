CREATE OR ALTER PROCEDURE dbo.proc_consultar_cuadrillas(
@ClaveZona VARCHAR(10) = '')
AS

/*******************************************************************************************
EXEC proc_consultar_cuadrillas
EXEC proc_consultar_cuadrillas 'FA'
********************************************************************************************/
BEGIN
    SET NOCOUNT ON;

    DECLARE @estado INT = 0;
    DECLARE @mensaje VARCHAR(500) = '';
    DECLARE @listaCuadrillas NVARCHAR(MAX);

    BEGIN TRY

		IF EXISTS (SELECT  1 FROM mae_pdm_cuadrillas WHERE opc_estatus = 1)
		BEGIN
			SET @listaCuadrillas =
			(
				SELECT
					C.idu_cuadrilla AS idCuadrilla,
					C.nom_cuadrilla AS nombreCuadrilla,
					C.nom_lider_cuadrilla AS liderCuadrilla,
					C.num_miembros AS miembros,
					C.idu_punto_venta AS puntoVenta,					
					JSON_QUERY
					(
						(
							SELECT
								T.idu_tipomaniobra AS idTipoManiobra,
								M.nom_maniobra AS nombreTipoManiobra,
								T.num_tarifa AS tarifa								
							FROM ctl_pdm_tarifas_cuadrillas T
							INNER JOIN cat_pdm_tipos_maniobras M
								ON T.idu_tipomaniobra = M.idu_tipomaniobra
							WHERE T.idu_cuadrilla = C.idu_cuadrilla
								AND M.opc_estatus = 1
							FOR JSON PATH
						)
					) AS listaTarifas

				FROM mae_pdm_cuadrillas C
				INNER JOIN SAP.SBO_Impulsora_PROD.dbo.OWHS AS pun ON pun.WhsCode = C.idu_punto_venta  COLLATE SQL_Latin1_General_CP850_CI_AS
				WHERE C.opc_estatus = 1 
					AND pun.U_SerieSucursal = (CASE WHEN @ClaveZona = '' THEN pun.U_SerieSucursal ELSE @ClaveZona END)
				ORDER BY C.nom_cuadrilla
				FOR JSON PATH
			);

			SET @mensaje = 'Información obtenida correctamente.';
			SET @estado = 0;
			
		END
		ELSE
		BEGIN
			SET @mensaje = 'Sin registro de cuadrillas.';
			SET @estado = -100;
		END
    END TRY
    BEGIN CATCH
        
            SET @estado = ERROR_NUMBER();
            SET @mensaje = ERROR_MESSAGE();
            SET @listaCuadrillas = '[]';

    END CATCH

	SELECT
		@estado AS estado,
		@mensaje AS mensaje,
		ISNULL(@listaCuadrillas,'[]') AS listaCuadrillas;


END
GO
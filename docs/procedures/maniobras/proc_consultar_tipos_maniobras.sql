CREATE OR ALTER PROCEDURE dbo.proc_consultar_tipos_maniobras
AS
/*******************************************************************************
EXEC proc_consultar_tipos_maniobras

********************************************************************************/
BEGIN
    SET NOCOUNT ON;

    DECLARE @estado INT = 0;
    DECLARE @mensaje VARCHAR(500) = '';
    DECLARE @listaTipoManiobras NVARCHAR(MAX);

    BEGIN TRY
		IF EXISTS (SELECT TOP 1 1FROM cat_pdm_tipos_maniobras WHERE opc_estatus = 1)
		BEGIN
			SET @listaTipoManiobras =
			(
				SELECT
					idu_tipomaniobra AS idTipoManiobra,
					nom_maniobra AS nombreTipoManiobra,
					des_maniobra AS descripcionTipoManiobra,
					CASE WHEN opc_estatus = 1 THEN 'Activo' ELSE 'inactivo' END AS estatus
				FROM cat_pdm_tipos_maniobras
				
				ORDER BY nom_maniobra
				FOR JSON PATH
			);

			SET @estado = 0;
			SET @mensaje = 'Información obtenida correctamente.';
		END
		ELSE
		BEGIN
			SET @estado = -100;
			SET @mensaje = 'Sin registros que  mostrar';
		END
    END TRY
    BEGIN CATCH

        SET @estado = ERROR_NUMBER();
        SET @mensaje = ERROR_MESSAGE();
        SET @listaTipoManiobras = '[]';

    END CATCH

    SELECT
        @estado AS estado,
        @mensaje AS mensaje,
        @listaTipoManiobras AS listaTipoManiobras;
END
GO
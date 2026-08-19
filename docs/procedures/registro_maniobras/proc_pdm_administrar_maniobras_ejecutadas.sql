CREATE OR ALTER PROCEDURE proc_pdm_administrar_maniobras_ejecutadas
(
    @idTipoManiobra       INT = 0,
    @idPuntoVenta        VARCHAR(20) = '',
    @idCuadrilla          INT = 0,
    @serieDocumento     SMALLINT = 0,
    @documentoSap       INT = 0,
    @toneladas          NUMERIC(10,2) = 0,
    @usuario            INT = 0,
    @fecha              DATETIME = NULL,
    @numLineaDocumento  INT = -1
)
AS
/**********************************************************************************
EXEC dbo.proc_pdm_administrar_maniobras_ejecutadas
    @idTipoManiobra = 2,
    @idPuntoVenta = 'ANGOS01',
    @idCuadrilla = 3,
    @serieDocumento = 0,
    @documentoSap = '0',
    @toneladas = 24.50,
    @usuario = 15,
    @fecha = '2026-08-10',
    @numLineaDocumento = 1;

*********************************************************************************/
BEGIN
    SET NOCOUNT ON;

    DECLARE @estado INT = 0;
    DECLARE @mensaje VARCHAR(500) = '';
	DECLARE @tarifas NUMERIC(10,2) = 0.00;

    BEGIN TRY
		
		SET @tarifas = COALESCE((SELECT num_tarifa 
						FROM ctl_pdm_tarifas_cuadrillas 
						WHERE idu_cuadrilla = @idCuadrilla 
						AND idu_tipomaniobra = @idTipoManiobra),0)

		IF @tarifas >= 0
		BEGIN
			INSERT INTO dbo.mov_pdm_maniobras_ejecutadas
			(
				idu_tipomaniobra,
				num_tarifa_maniobra,
				idu_punto_venta,
				idu_cuadrilla,
				num_tipodocumento,
				num_documentosap,
				num_toneladas,
				opc_estatus,
				num_usuario_registro,
				num_usuario_modifico,
				fec_registro,
				fec_actualizacion,
				num_linea_documento
			)
			VALUES
			(
				@idTipoManiobra,
				@tarifas,
				@idPuntoVenta,
				@idCuadrilla,
				@serieDocumento,
				@documentoSap,
				@toneladas,
				1,
				@usuario,
				@usuario,
				COALESCE(@fecha, GETDATE()),
				GETDATE(),
				@numLineaDocumento
			);

			SET @mensaje = 'Maniobra registrada correctamente.';
			SET @estado = 0;
		END
		ELSE
		BEGIN
			SET @mensaje = 'No existe tarifa para esa maniobra, favor de registrar la tarifa dentro de la cuadrilla';
			SET @estado = -100;
		END
        SELECT
            @estado AS estado,
            @mensaje AS mensaje;

    END TRY
    BEGIN CATCH

        SELECT
            ERROR_NUMBER() AS estado,
            ERROR_MESSAGE() AS mensaje;

    END CATCH
END
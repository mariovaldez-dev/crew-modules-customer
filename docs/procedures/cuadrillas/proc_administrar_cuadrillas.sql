CREATE OR ALTER PROCEDURE dbo.proc_pdm_administrar_cuadrillas
(
    @Opcion INT,
	@zona VARCHAR(10)='',
    @idCuadrilla INT = 0,
    @nombreCuadrilla VARCHAR(100) = '',
    @liderCuadrilla VARCHAR(100) = '',
    @miembros INT = 0,
    @puntoVenta VARCHAR(20) = '',
    @listaTarifas VARCHAR(MAX) = '[]',
    @usuario INT = 0
)
AS
/***********************************************************************************************************
--SELECT * FROM cat_pdm_tipos_maniobras
--SELECT * FROM SAP.SBO_Impulsora_PROD.dbo.OWHS
SELECT * FROM mae_pdm_cuadrillas WHERE idu_cuadrilla = 5
SELECT * FROM ctl_pdm_tarifas_cuadrillas WHERE idu_cuadrilla = 5
SELECT * FROM his_pdm_tarifas_cuadrillas WHERE idu_cuadrilla = 5
DECLARE @json NVARCHAR(MAX) =
'
[
    {
        "tipoManiobra":2,
        "tarifa":200
    },
    {
        "tipoManiobra":3,
        "tarifa":100
    }
]
';
------------------- Nueva Cuadrilla ----------------------
EXEC proc_pdm_administrar_cuadrillas
    @Opcion = 1,
    @nombreCuadrilla = 'Cuadrilla Mario',
    @liderCuadrilla = 'Juan MM',
    @miembros = 8,
    @puntoVenta = 'ANGOS02',
    @listaTarifas = @json,
    @usuario = 5;

-------------------- Actualizar Cuadrilla-----------------

DECLARE @json NVARCHAR(MAX) =
'
[
    {
        "tipoManiobra":2,
        "tarifa":350
    },
    {
        "tipoManiobra":3,
        "tarifa":280
    },
	 {
        "tipoManiobra":4,
        "tarifa":100
    }
]
';

EXEC proc_pdm_administrar_cuadrillas
    @Opcion = 2,
	@idCuadrilla = 5,
   @nombreCuadrilla = 'Cuadrilla Owner - Mario Modificada 2',
    @liderCuadrilla = 'Juan MMM',
    @miembros = 10,
    @puntoVenta = 'ANGOS03',
    @listaTarifas = @json,
    @usuario = 7;

************************************************************************************************************/
BEGIN
    SET NOCOUNT ON;

    DECLARE @estado INT = 0;
    DECLARE @mensaje VARCHAR(500) = '';

    BEGIN TRY

       -- BEGIN TRANSACTION;

        ---------------------------------------------------------
        -- OPCION 1 - NUEVA CUADRILLA
        ---------------------------------------------------------
        IF @Opcion = 1
        BEGIN

            IF EXISTS(SELECT 1 FROM mae_pdm_cuadrillas WHERE nom_cuadrilla = @nombreCuadrilla AND idu_punto_venta = @puntoVenta AND opc_estatus = 1)
            BEGIN
                SET @mensaje = 'Ya existe una cuadrilla con el mismo nombre en este punto de venta.'
				SET @estado = -100;
            END
			ELSE
			BEGIN
				INSERT INTO mae_pdm_cuadrillas
				(
					clv_zona,
					nom_cuadrilla,
					nom_lider_cuadrilla,
					num_miembros,
					idu_punto_venta,
					num_usuario_registro,
					num_usuario_modifico,
					fec_registro,
					fec_actualizacion
				)
				VALUES
				(
					@zona,
					@nombreCuadrilla,
					@liderCuadrilla,
					@miembros,
					@puntoVenta,
					@usuario,
					@usuario,
					GETDATE(),
					GETDATE()
				);

				SET @idCuadrilla = SCOPE_IDENTITY();

				INSERT INTO ctl_pdm_tarifas_cuadrillas
				(
					idu_cuadrilla,
					idu_tipomaniobra,
					num_tarifa,
					num_usuario_registro,
					num_usuario_modifico,
					fec_registro,
					fec_actualizacion
				)
				SELECT
					@idCuadrilla,
					tipoManiobra,
					tarifa,
					@usuario,
					@usuario,
					GETDATE(),
					GETDATE()
				FROM OPENJSON(@listaTarifas)
				WITH
				(
					tipoManiobra INT,
					tarifa NUMERIC(10,2)
				);

				SET @mensaje = 'Cuadrilla registrada correctamente.';
				SET @estado = 0;
			END
        END

        ---------------------------------------------------------
        -- OPCION 2 - ACTUALIZAR
        ---------------------------------------------------------
        ELSE IF @Opcion = 2
        BEGIN

            IF EXISTS(SELECT 1 FROM mae_pdm_cuadrillas WHERE nom_cuadrilla = @nombreCuadrilla AND idu_punto_venta = @puntoVenta AND opc_estatus = 1 AND idu_cuadrilla <> @idCuadrilla)
            BEGIN
                SET @mensaje = 'Ya existe otra cuadrilla con el mismo nombre en este punto de venta.'
				SET @estado = -101;
            END
			ELSE
			BEGIN

				UPDATE mae_pdm_cuadrillas
				SET
					clv_zona = @zona,
					nom_cuadrilla = @nombreCuadrilla,
					nom_lider_cuadrilla = @liderCuadrilla,
					num_miembros = @miembros,
					idu_punto_venta = @puntoVenta,
					num_usuario_modifico = @usuario,
					fec_actualizacion = GETDATE()
				WHERE idu_cuadrilla = @idCuadrilla;

				--
				-- Creamso tabla con las nuevas tarifas
				-- SELECT * FROM #tmpTarifas
				--
			
				DROP TABLE IF EXISTS #tmpTarifas;
				SELECT
					@idCuadrilla AS idCuadrilla,
					tipoManiobra AS tipoManiobra,
					tarifa AS tarifa
				INTO #tmpTarifas
				FROM OPENJSON(@listaTarifas)
				WITH
				(
					tipoManiobra INT,
					tarifa NUMERIC(10,2)
				);
				
				--
				-- Respaldamos las tarifas modificadas
				--

				INSERT INTO his_pdm_tarifas_cuadrillas
				(
					idu_cuadrilla,
					idu_tipomaniobra,
					num_tarifa,
					num_usuario_registro,
					num_usuario_modifico,
					fec_registro,
					fec_actualizacion
				)
				SELECT
					ctl.idu_cuadrilla,
					ctl.idu_tipomaniobra,
					ctl.num_tarifa,
					ctl.num_usuario_registro,
					@usuario,
					ctl.fec_registro,
					GETDATE()
				FROM ctl_pdm_tarifas_cuadrillas AS ctl
				WHERE idu_cuadrilla = @idCuadrilla
					AND EXISTS (SELECT 1 FROM #tmpTarifas AS tmp WHERE tmp.tipoManiobra = ctl.idu_tipomaniobra AND tmp.tarifa <> ctl.num_tarifa)
				
				--
				-- Actualizamos los registros de las maniobras donde se modifico la tarifa
				--
				
				UPDATE ctl
				SET
					ctl.num_tarifa = tmp.tarifa,
					ctl.num_usuario_modifico = @usuario,
					ctl.fec_actualizacion = GETDATE()
				FROM ctl_pdm_tarifas_cuadrillas AS ctl
				INNER JOIN #tmpTarifas AS tmp
					ON ctl.idu_cuadrilla = tmp.idCuadrilla
				   AND ctl.idu_tipomaniobra = tmp.tipoManiobra
				WHERE ctl.num_tarifa <> tmp.tarifa;

				--
				-- Insertamos las tarifas de las nuevas maniobras, que anteriormente no tenian tarifa
				--

				INSERT INTO ctl_pdm_tarifas_cuadrillas
				(
					idu_cuadrilla,
					idu_tipomaniobra,
					num_tarifa,
					num_usuario_registro,
					num_usuario_modifico,
					fec_registro,
					fec_actualizacion
				)
				SELECT
					@idCuadrilla,
					tmp.tipoManiobra,
					tmp.tarifa,
					@usuario,
					@usuario,
					GETDATE(),
					GETDATE()
				FROM #tmpTarifas AS tmp
				WHERE NOT EXISTS (SELECT 1 FROM ctl_pdm_tarifas_cuadrillas AS ctl 
									WHERE ctl.idu_cuadrilla = @idCuadrilla AND ctl.idu_tipomaniobra = tmp.tipoManiobra )
				

				SET @mensaje = 'Cuadrilla actualizada correctamente.';
				SET @estado = 0;
			END
        END

        ---------------------------------------------------------
        -- OPCION 3 - INHABILITAR / ELIMINAR CUADRILLA
        ---------------------------------------------------------
        ELSE IF @Opcion = 3
        BEGIN
            IF EXISTS (SELECT 1 FROM mov_pdm_maniobras_ejecutadas WHERE idu_cuadrilla = @idCuadrilla AND opc_estatus = 1)
            BEGIN
                SET @mensaje = 'No se puede eliminar la cuadrilla porque tiene maniobras registradas o cortes liquidados asociados.';
                SET @estado = -103;
            END
            ELSE
            BEGIN
                UPDATE mae_pdm_cuadrillas
                SET
                    opc_estatus = 0,
                    num_usuario_modifico = @usuario,
                    fec_actualizacion = GETDATE()
                WHERE idu_cuadrilla = @idCuadrilla;

                SET @mensaje = 'Cuadrilla inhabilitada correctamente.';
                SET @estado = 0;
            END
        END
        ---------------------------------------------------------
        -- OPCION 4 - VALIDAR SI TIENE MANIOBRAS O LIQUIDACIONES ASOCIADAS
        ---------------------------------------------------------
        ELSE IF @Opcion = 4
        BEGIN
            IF EXISTS (SELECT 1 FROM mov_pdm_maniobras_ejecutadas WHERE idu_cuadrilla = @idCuadrilla AND opc_estatus = 1)
            BEGIN
                SET @estado = 1; -- Tiene maniobras o liquidaciones
                SET @mensaje = 'La cuadrilla tiene maniobras registradas o cortes liquidados asociados.';
            END
            ELSE
            BEGIN
                SET @estado = 0; -- No tiene
                SET @mensaje = 'Sin maniobras asociadas.';
            END
        END
        ELSE
        BEGIN
        
			SET @mensaje = 'La opción especificada no es válida.';
			SET @estado = -102;
        END

        --COMMIT TRANSACTION;

    END TRY
    BEGIN CATCH

        --IF @@TRANCOUNT > 0
        --    ROLLBACK TRANSACTION;


        SET @estado =  ERROR_NUMBER();
        SET @mensaje = ERROR_MESSAGE();

    END CATCH

	SELECT @estado AS estado, @mensaje AS mensaje;
END
GO
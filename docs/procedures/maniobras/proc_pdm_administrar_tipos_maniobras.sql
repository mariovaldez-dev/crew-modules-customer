CREATE OR ALTER PROCEDURE dbo.proc_pdm_administrar_tipos_maniobras
(
    @Opcion                INT,
    @IdTipoManiobra        INT = 0,
    @NombreManiobra        VARCHAR(100) = '',
    @DescripcionManiobra   VARCHAR(100) = '',
	@Estatus BIT = 1,
    @Usuario               INT = 0
)
/********************************************************************************************
-- Nuevo tipo Maniobra 
EXEC proc_pdm_administrar_tipos_maniobras
    @Opcion = 1,
    @NombreManiobra = 'Carga Costales 100KG',
    @DescripcionManiobra = 'Descarga de producto en unidad de cotal de 100KG',
    @Usuario = 1;


-- Editar tipo maniobra
EXEC proc_pdm_administrar_tipos_maniobras
    @Opcion = 2,
    @IdTipoManiobra = 6,
    @NombreManiobra = 'Carga Costales 100KG',
    @DescripcionManiobra = 'Carga de producto en unidad de cotal de 100 KILogramos',
	@estatus = 1,
    @Usuario = 5;

*********************************************************************************************/



AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @estado INT = 0;
    DECLARE @mensaje VARCHAR(500) = '';

    BEGIN TRY

        /* ==========================================
           OPCION 1 - INSERTAR
        ========================================== */
        IF @Opcion = 1
        BEGIN
            IF EXISTS(SELECT 1 FROM cat_pdm_tipos_maniobras WHERE nom_maniobra = @NombreManiobra)
            BEGIN
                SET @mensaje = 'Ya existe un tipo de maniobra con el mismo nombre.'
				SET @estado = -100;
            END
			ELSE
			BEGIN
				INSERT INTO cat_pdm_tipos_maniobras
				(
					nom_maniobra,
					des_maniobra,
					num_usuario_registro,
					num_usuario_modifico,
					fec_registro,
					fec_actualizacion
				)
				VALUES
				(
					@NombreManiobra,
					@DescripcionManiobra,
					@Usuario,
					@Usuario,
					GETDATE(),
					GETDATE()
				);

				SET @Mensaje = 'Tipo de maniobra registrado correctamente.';
				SET @estado = 0;
			END
        END

        /* ==========================================
           OPCION 2 - ACTUALIZAR
        ========================================== */

        ELSE IF @Opcion = 2
        BEGIN
           

            IF EXISTS(SELECT 1 FROM cat_pdm_tipos_maniobras WHERE nom_maniobra = @NombreManiobra AND idu_tipomaniobra <> @IdTipoManiobra)
            BEGIN
				SET @mensaje = 'Ya existe un tipo de maniobra con el mismo nombre.'
				SET @estado = -101;
            END
			ELSE
			BEGIN
				UPDATE cat_pdm_tipos_maniobras
				SET
					nom_maniobra = @NombreManiobra,
					des_maniobra = @DescripcionManiobra,
					num_usuario_modifico = @Usuario,
					opc_estatus = @Estatus,
					fec_actualizacion = GETDATE()
				WHERE idu_tipomaniobra = @IdTipoManiobra;

				SET @mensaje = 'Tipo de maniobra actualizado correctamente.';
				SET @estado = 0;
			END
        END

    END TRY
    BEGIN CATCH
	
		SET @estado = ERROR_NUMBER()
		SET @mensaje = ERROR_MESSAGE()

    END CATCH

	 SELECT @estado AS estado, @mensaje AS mensaje;
END
GO
CREATE OR ALTER PROCEDURE proc_pdm_login  
    @Usuario     VARCHAR(100),       
    @Contrasena  VARCHAR(100)        
AS  
BEGIN  
	--EXEC proc_pdm_login 'lcarrillo@grupoimpulsora.com', 'lcarrillo1'
    SET NOCOUNT ON;  

	DECLARE @estado INT = 0;
	DECLARE @mensaje VARCHAR(500) = 'OK';
	DECLARE @codigoAsesor INT = 0;
	DECLARE @nombreAsesor VARCHAR(500) = '';
	DECLARE @zonaAsesor VARCHAR(500) = '';	
	DECLARE @rol SMALLINT
    SELECT  
        @codigoAsesor = age.Code,  
        @nombreAsesor = age.Name,
		@zonaAsesor = age.U_serieSucursal,
		@rol = (CASE WHEN age.U_Tipo = 'AM' THEN 1 ELSE 2 END)
    FROM SAP.SBO_Impulsora_PROD.dbo.[@USUARIOS_PUNTOVENTA] AS age  
	WHERE          
        UPPER(age.U_usuario) = UPPER(@Usuario) 
        AND UPPER(age.U_passwd) = UPPER(@Contrasena)  
		AND U_Tipo IN ('CA', 'CO', 'AM') 
		AND U_status = 'A'  ;  

		IF COALESCE(@codigoAsesor, 0) = 0 
		BEGIN
			SET @estado = -100 
			SET @mensaje = 'No fue posible iniciar sesi�n. El usuario o la contrase�a proporcionados no son v�lidos.'
		END

		SELECT	@estado AS [estado], 
				@mensaje AS [mensaje], 
				@codigoAsesor AS [codigoAsesor], 
				@nombreAsesor AS [nombreAsesor],
				@zonaAsesor AS [zonaAsesor],
				@rol AS [rolAsesor]


END  
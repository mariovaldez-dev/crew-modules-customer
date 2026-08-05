CREATE TABLE ctl_pdm_tarifas_cuadrillas
(
	idu_cuadrilla           INT NOT NULL DEFAULT 0,
	idu_tipomaniobra            INT NOT NULL DEFAULT 0,
	num_tarifa              NUMERIC(10,2) NOT NULL DEFAULT 0,
	num_usuario_registro    INT NOT NULL DEFAULT 0,
	num_usuario_modifico    INT NOT NULL DEFAULT 0,
	fec_registro            DATETIME NOT NULL DEFAULT GETDATE(),
	fec_actualizacion       DATETIME NOT NULL DEFAULT GETDATE(),

	CONSTRAINT PK_ctl_pdm_tarifas_cuadrillas
		PRIMARY KEY CLUSTERED (idu_cuadrilla, idu_tipomaniobra),

	CONSTRAINT FK_ctl_pdm_tarifas_cuadrillas_mae_pdm_cuadrillas
		FOREIGN KEY (idu_cuadrilla)
		REFERENCES dbo.mae_pdm_cuadrillas(idu_cuadrilla),

	CONSTRAINT FK_ctl_pdm_tarifas_cuadrillas_cat_pdm_tipos_maniobras
		FOREIGN KEY (idu_tipomaniobra)
		REFERENCES dbo.cat_pdm_tipos_maniobras(idu_tipomaniobra)
);


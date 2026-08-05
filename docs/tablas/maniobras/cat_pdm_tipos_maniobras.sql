DROP TABLE IF EXISTS cat_pdm_tipos_maniobras;
CREATE TABLE cat_pdm_tipos_maniobras
(
	idu_tipomaniobra       INT IDENTITY(1,1) NOT NULL,
	nom_maniobra           VARCHAR(100) NOT NULL DEFAULT '',
	des_maniobra           VARCHAR(100) NOT NULL DEFAULT '',
	opc_estatus            BIT NOT NULL DEFAULT 1,
	num_usuario_registro   INT NOT NULL DEFAULT 0,
	num_usuario_modifico   INT NOT NULL DEFAULT 0,
	fec_registro           DATETIME NOT NULL DEFAULT GETDATE(),
	fec_actualizacion      DATETIME NOT NULL DEFAULT GETDATE(),

	CONSTRAINT PK_cat_pdm_tipos_maniobras
		PRIMARY KEY CLUSTERED (idu_tipomaniobra)
);

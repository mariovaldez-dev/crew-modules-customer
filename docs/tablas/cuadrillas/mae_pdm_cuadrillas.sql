CREATE TABLE mae_pdm_cuadrillas
(
	idu_cuadrilla           INT IDENTITY(1,1) NOT NULL,
	nom_cuadrilla           VARCHAR(100) NOT NULL DEFAULT '',
	nom_lider_cuadrilla     VARCHAR(100) NOT NULL DEFAULT '',
	num_miembros            INT NOT NULL DEFAULT 0,
	clv_zona		        VARCHAR(20) NOT NULL DEFAULT '',
	idu_punto_venta         VARCHAR(20) NOT NULL DEFAULT '',
	opc_estatus				bit NOT NULL DEFAULT 1,
	num_usuario_registro    INT NOT NULL DEFAULT 0,
	num_usuario_modifico    INT NOT NULL DEFAULT 0,
	fec_registro            DATETIME NOT NULL DEFAULT GETDATE(),
	fec_actualizacion       DATETIME NOT NULL DEFAULT GETDATE(),

	CONSTRAINT PK_mae_pdm_cuadrillas
		PRIMARY KEY CLUSTERED (idu_cuadrilla),

	CONSTRAINT UQ_mae_pdm_cuadrillas_nom_cuadrilla_puntoventa
		UNIQUE (nom_cuadrilla, idu_punto_venta)
);

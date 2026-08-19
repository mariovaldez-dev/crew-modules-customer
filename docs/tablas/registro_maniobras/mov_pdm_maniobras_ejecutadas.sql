DROP TABLE IF EXISTS dbo.mov_pdm_maniobras_ejecutadas;
CREATE TABLE dbo.mov_pdm_maniobras_ejecutadas
(
    idu_maniobra            INT IDENTITY(1,1) NOT NULL,
    idu_tipomaniobra        INT NOT NULL DEFAULT 0,
	num_tarifa_maniobra		NUMERIC(10,2) NOT NULL DEFAULT 0.00, 
    idu_punto_venta         VARCHAR(20) NOT NULL DEFAULT '',
    idu_cuadrilla           INT NOT NULL DEFAULT 0,
    num_tipodocumento       SMALLINT NOT NULL DEFAULT 0,
    num_documentosap        VARCHAR(10) NOT NULL DEFAULT '',
    num_linea_documento     INT NULL DEFAULT -1,
    num_toneladas           NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    opc_estatus             SMALLINT NOT NULL DEFAULT 0,
    idu_corte               INT NOT NULL DEFAULT 0,
    num_usuario_registro    INT NOT NULL DEFAULT 0,
    num_usuario_modifico    INT NOT NULL DEFAULT 0,
    fec_registro            DATETIME NOT NULL DEFAULT GETDATE(),
    fec_actualizacion       DATETIME NOT NULL DEFAULT GETDATE(),

    CONSTRAINT PK_mov_maniobras_ejecutadas
        PRIMARY KEY CLUSTERED (idu_maniobra),

    CONSTRAINT FK_mov_pdm_maniobras_ejecutadas_cat_pdm_tipos_maniobras
        FOREIGN KEY (idu_tipomaniobra)
        REFERENCES dbo.cat_pdm_tipos_maniobras(idu_tipomaniobra),

    CONSTRAINT FK_mov_pdm_maniobras_ejecutadas_mae_pdm_cuadrillas
        FOREIGN KEY (idu_cuadrilla)
        REFERENCES dbo.mae_pdm_cuadrillas(idu_cuadrilla)
);


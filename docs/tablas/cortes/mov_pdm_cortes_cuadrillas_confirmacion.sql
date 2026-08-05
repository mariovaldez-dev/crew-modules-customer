DROP TABLE IF EXISTS mov_pdm_cortes_cuadrillas_confirmacion;
CREATE TABLE dbo.mov_pdm_cortes_cuadrillas_confirmacion (
    idu_corte int NOT NULL,
    idu_cuadrilla int NOT NULL,
    fec_confirmacion datetime DEFAULT getdate() NOT NULL,
    CONSTRAINT PK_mov_cortes_cuadrillas PRIMARY KEY (idu_corte, idu_cuadrilla)
);
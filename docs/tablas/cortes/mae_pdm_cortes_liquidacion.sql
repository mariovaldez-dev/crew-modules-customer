DROP TABLE IF EXISTS mae_pdm_cortes_liquidacion;
CREATE TABLE dbo.mae_pdm_cortes_liquidacion (
    idu_corte int IDENTITY(1,1) NOT NULL,
    folio_corte varchar(20) NULL, -- LIQ-00X
    clv_zona varchar(20) NOT NULL,
    fec_inicio date NOT NULL,
    fec_fin date NOT NULL,
    num_toneladas_total numeric(10,2) DEFAULT 0.00,
    monto_total numeric(10,2) DEFAULT 0.00,
    opc_estatus smallint DEFAULT 0 NOT NULL, -- 0 = Borrador, 1 = Confirmado
    fec_registro datetime DEFAULT getdate() NOT NULL,
    CONSTRAINT PK_mae_pdm_cortes PRIMARY KEY (idu_corte)
);

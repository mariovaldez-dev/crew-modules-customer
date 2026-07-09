-- =========================================================================
-- SCRIPT DE CREACIÓN DE TABLAS (SQL SERVER) — MÓDULO PDM (Nomenclatura SAP)
-- =========================================================================

-- 1. cat_pdm_tipos_maniobras (Catálogo de Tipos de Maniobras)
IF OBJECT_ID('dbo.cat_pdm_tipos_maniobras', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.cat_pdm_tipos_maniobras (
        DocEntry INT IDENTITY(1,1) NOT NULL,
        U_Nombre VARCHAR(100) NOT NULL,
        U_Descripcion VARCHAR(100) NULL,
        U_Estatus CHAR(1) NOT NULL CONSTRAINT DF_cat_pdm_tipos_maniobras_U_Estatus DEFAULT 'A',
        U_CreadoPor VARCHAR(50) NOT NULL,
        U_FechaCrea DATETIME NOT NULL CONSTRAINT DF_cat_pdm_tipos_maniobras_U_FechaCrea DEFAULT GETDATE(),
        CONSTRAINT PK_cat_pdm_tipos_maniobras PRIMARY KEY CLUSTERED (DocEntry),
        CONSTRAINT UQ_cat_pdm_tipos_maniobras_U_Nombre UNIQUE (U_Nombre)
    );
END;

-- 2. mae_pdm_cuadrillas (Maestro de Cuadrillas)
IF OBJECT_ID('dbo.mae_pdm_cuadrillas', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.mae_pdm_cuadrillas (
        DocEntry INT IDENTITY(1,1) NOT NULL,
        U_Nombre VARCHAR(100) NOT NULL,
        U_Lider VARCHAR(100) NOT NULL,
        U_Miembros INT NOT NULL CONSTRAINT CK_mae_pdm_cuadrillas_U_Miembros CHECK (U_Miembros >= 1),
        U_PuntoVentaId INT NOT NULL,
        U_Zona VARCHAR(50) NOT NULL,
        U_CreadoPor VARCHAR(50) NOT NULL,
        U_FechaCrea DATETIME NOT NULL CONSTRAINT DF_mae_pdm_cuadrillas_U_FechaCrea DEFAULT GETDATE(),
        CONSTRAINT PK_mae_pdm_cuadrillas PRIMARY KEY CLUSTERED (DocEntry),
        CONSTRAINT UQ_mae_pdm_cuadrillas_U_Nombre_pv UNIQUE (U_Nombre, U_PuntoVentaId)
    );
END;

-- 3. mae_pdm_tarifas_cuadrillas (Tarifas de Maniobras por Cuadrilla)
IF OBJECT_ID('dbo.mae_pdm_tarifas_cuadrillas', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.mae_pdm_tarifas_cuadrillas (
        U_CuadrillaId INT NOT NULL,
        U_Carga25kg DECIMAL(18,2) NULL CONSTRAINT CK_mae_pdm_tarifas_cuadrillas_U_Carga25kg CHECK (U_Carga25kg >= 0),
        U_Carga50kg DECIMAL(18,2) NULL CONSTRAINT CK_mae_pdm_tarifas_cuadrillas_U_Carga50kg CHECK (U_Carga50kg >= 0),
        U_Descarga25kg DECIMAL(18,2) NULL CONSTRAINT CK_mae_pdm_tarifas_cuadrillas_U_Descarga25kg CHECK (U_Descarga25kg >= 0),
        U_Descarga50kg DECIMAL(18,2) NULL CONSTRAINT CK_mae_pdm_tarifas_cuadrillas_U_Descarga50kg CHECK (U_Descarga50kg >= 0),
        U_Traslado DECIMAL(18,2) NULL CONSTRAINT CK_mae_pdm_tarifas_cuadrillas_U_Traslado CHECK (U_Traslado >= 0),
        U_Apaleo DECIMAL(18,2) NULL CONSTRAINT CK_mae_pdm_tarifas_cuadrillas_U_Apaleo CHECK (U_Apaleo >= 0),
        U_UltAct DATETIME NOT NULL CONSTRAINT DF_mae_pdm_tarifas_cuadrillas_U_UltAct DEFAULT GETDATE(),
        CONSTRAINT PK_mae_pdm_tarifas_cuadrillas PRIMARY KEY CLUSTERED (U_CuadrillaId),
        CONSTRAINT FK_mae_pdm_tarifas_cuadrillas_U_Cuadrilla FOREIGN KEY (U_CuadrillaId) REFERENCES dbo.mae_pdm_cuadrillas(DocEntry) ON DELETE CASCADE
    );
END;

-- 4. ctl_pdm_auditoria_tarifas (Bitácora de Auditoría de Tarifas)
IF OBJECT_ID('dbo.ctl_pdm_auditoria_tarifas', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.ctl_pdm_auditoria_tarifas (
        DocEntry BIGINT IDENTITY(1,1) NOT NULL,
        U_CuadrillaId INT NOT NULL,
        U_ConceptoTarifa VARCHAR(50) NOT NULL,
        U_PrecioAnterior DECIMAL(18,2) NULL,
        U_PrecioNuevo DECIMAL(18,2) NULL,
        U_UsuarioId VARCHAR(50) NOT NULL,
        U_FechaCambio DATETIME NOT NULL CONSTRAINT DF_ctl_pdm_auditoria_tarifas_U_FechaCambio DEFAULT GETDATE(),
        CONSTRAINT PK_ctl_pdm_auditoria_tarifas PRIMARY KEY CLUSTERED (DocEntry),
        CONSTRAINT FK_ctl_pdm_auditoria_tarifas_U_Cuadrilla FOREIGN KEY (U_CuadrillaId) REFERENCES dbo.mae_pdm_cuadrillas(DocEntry) ON DELETE CASCADE
    );
END;

-- 5. mov_pdm_cortes_liquidacion (Cortes de Liquidación Generales)
IF OBJECT_ID('dbo.mov_pdm_cortes_liquidacion', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.mov_pdm_cortes_liquidacion (
        DocEntry INT IDENTITY(1,1) NOT NULL,
        U_Folio VARCHAR(20) NOT NULL,
        U_FechaInicio DATE NOT NULL,
        U_FechaFin DATE NOT NULL,
        U_Zona VARCHAR(50) NOT NULL,
        U_Estado VARCHAR(20) NOT NULL CONSTRAINT DF_mov_pdm_cortes_liquidacion_U_Estado DEFAULT 'borrador',
        U_CreadoPor VARCHAR(50) NOT NULL,
        U_FechaCrea DATETIME NOT NULL CONSTRAINT DF_mov_pdm_cortes_liquidacion_U_FechaCrea DEFAULT GETDATE(),
        U_ConfirmadoPor VARCHAR(50) NULL,
        U_FechaConf DATETIME NULL,
        CONSTRAINT PK_mov_pdm_cortes_liquidacion PRIMARY KEY CLUSTERED (DocEntry),
        CONSTRAINT UQ_mov_pdm_cortes_liquidacion_U_Folio UNIQUE (U_Folio)
    );
END;

-- 6. mov_pdm_registro_maniobras (Bitácora de Maniobras Registradas)
IF OBJECT_ID('dbo.mov_pdm_registro_maniobras', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.mov_pdm_registro_maniobras (
        DocEntry INT IDENTITY(1,1) NOT NULL,
        U_Folio VARCHAR(20) NOT NULL,
        U_Fecha DATE NOT NULL,
        U_AlmacenId INT NOT NULL,
        U_CuadrillaId INT NOT NULL,
        U_TipoManiobraId INT NOT NULL,
        U_Toneladas DECIMAL(18,3) NOT NULL CONSTRAINT CK_mov_pdm_registro_maniobras_U_Toneladas CHECK (U_Toneladas > 0),
        U_DocumentoSap VARCHAR(50) NULL,
        U_Origen VARCHAR(10) NOT NULL,
        U_CorteId INT NULL,
        U_CreadoPor VARCHAR(50) NOT NULL,
        U_FechaReg DATETIME NOT NULL CONSTRAINT DF_mov_pdm_registro_maniobras_U_FechaReg DEFAULT GETDATE(),
        CONSTRAINT PK_mov_pdm_registro_maniobras PRIMARY KEY CLUSTERED (DocEntry),
        CONSTRAINT UQ_mov_pdm_registro_maniobras_U_Folio UNIQUE (U_Folio),
        CONSTRAINT FK_mov_pdm_registro_maniobras_U_Cuadrilla FOREIGN KEY (U_CuadrillaId) REFERENCES dbo.mae_pdm_cuadrillas(DocEntry),
        CONSTRAINT FK_mov_pdm_registro_maniobras_U_Tipo FOREIGN KEY (U_TipoManiobraId) REFERENCES dbo.cat_pdm_tipos_maniobras(DocEntry),
        CONSTRAINT FK_mov_pdm_registro_maniobras_U_Corte FOREIGN KEY (U_CorteId) REFERENCES dbo.mov_pdm_cortes_liquidacion(DocEntry)
    );
END;

-- 7. mov_pdm_cortes_cuadrillas (Acumulados de Cortes por Cuadrilla)
IF OBJECT_ID('dbo.mov_pdm_cortes_cuadrillas', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.mov_pdm_cortes_cuadrillas (
        U_CorteId INT NOT NULL,
        U_CuadrillaId INT NOT NULL,
        U_TotalToneladas DECIMAL(18,3) NOT NULL,
        U_TotalMonto DECIMAL(18,2) NOT NULL,
        U_Confirmada BIT NOT NULL CONSTRAINT DF_mov_pdm_cortes_cuadrillas_U_Confirmada DEFAULT 0,
        U_ConfirmadoPor VARCHAR(50) NULL,
        U_FechaConf DATETIME NULL,
        CONSTRAINT PK_mov_pdm_cortes_cuadrillas PRIMARY KEY CLUSTERED (U_CorteId, U_CuadrillaId),
        CONSTRAINT FK_mov_pdm_cortes_cuadrillas_U_Corte FOREIGN KEY (U_CorteId) REFERENCES dbo.mov_pdm_cortes_liquidacion(DocEntry) ON DELETE CASCADE,
        CONSTRAINT FK_mov_pdm_cortes_cuadrillas_U_Cuadrilla FOREIGN KEY (U_CuadrillaId) REFERENCES dbo.mae_pdm_cuadrillas(DocEntry)
    );
END;

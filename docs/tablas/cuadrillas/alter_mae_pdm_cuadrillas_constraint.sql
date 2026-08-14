-- ==============================================================================
-- SCRIPT DE MIGRACIÓN: Actualización de CONSTRAINT de unicidad en mae_pdm_cuadrillas
-- Permite registrar el mismo nombre de cuadrilla en diferentes Puntos de Venta.
-- ==============================================================================

-- 1. Eliminar la restricción de unicidad simple sobre nom_cuadrilla si existe:
IF EXISTS (
    SELECT 1 
    FROM sys.objects 
    WHERE name = 'UQ_mae_pdm_cuadrillas_nom_cuadrilla' 
      AND type = 'UQ'
)
BEGIN
    ALTER TABLE dbo.mae_pdm_cuadrillas 
    DROP CONSTRAINT UQ_mae_pdm_cuadrillas_nom_cuadrilla;
END
GO

-- 2. Crear la nueva restricción de unicidad compuesta (nom_cuadrilla + idu_punto_venta):
IF NOT EXISTS (
    SELECT 1 
    FROM sys.objects 
    WHERE name = 'UQ_mae_pdm_cuadrillas_nom_cuadrilla_puntoventa' 
      AND type = 'UQ'
)
BEGIN
    ALTER TABLE dbo.mae_pdm_cuadrillas
    ADD CONSTRAINT UQ_mae_pdm_cuadrillas_nom_cuadrilla_puntoventa 
    UNIQUE (nom_cuadrilla, idu_punto_venta);
END
GO

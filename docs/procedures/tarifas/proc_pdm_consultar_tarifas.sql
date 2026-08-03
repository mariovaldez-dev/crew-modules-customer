CREATE OR ALTER PROCEDURE dbo.proc_pdm_consultar_tarifas(
    @ClaveZona VARCHAR(10) = '')
AS

/*******************************************************************************************
EXEC proc_pdm_consultar_tarifas              -- todas las zonas
EXEC proc_pdm_consultar_tarifas 'FA'         -- solo esa zona (igual agrupado)
********************************************************************************************/
BEGIN
    SET NOCOUNT ON;

    DECLARE @estado INT = 0;
    DECLARE @mensaje VARCHAR(500) = '';
    DECLARE @listaCuadrillas NVARCHAR(MAX);

    BEGIN TRY

        IF EXISTS (SELECT 1 FROM mae_pdm_cuadrillas WHERE opc_estatus = 1)
        BEGIN

            SET @listaCuadrillas =
            (
                SELECT
                    zona.Memo AS claveZona,
                    zona.SlpName AS nombreZona,
                    JSON_QUERY
                    (
                        (
                            SELECT
                                C.idu_cuadrilla AS idCuadrilla,
                                C.nom_cuadrilla AS nombreCuadrilla,
                                C.nom_lider_cuadrilla AS liderCuadrilla,
                                C.num_miembros AS miembros,
                                C.idu_punto_venta AS idPuntoVenta,
                                pun.WhsName AS nombrePuntoVenta,
                                JSON_QUERY
                                (
                                    (
                                        SELECT
                                            M.idu_tipomaniobra AS idTipoManiobra,
                                            M.nom_maniobra AS nombreTipoManiobra,
                                            COALESCE(T.num_tarifa,0) AS tarifa
                                        FROM cat_pdm_tipos_maniobras M
                                        FULL JOIN ctl_pdm_tarifas_cuadrillas T
                                            ON T.idu_tipomaniobra = M.idu_tipomaniobra AND T.idu_cuadrilla = C.idu_cuadrilla
                                        WHERE M.opc_estatus = 1
                                        FOR JSON PATH
                                    )
                                ) AS listaTarifas
                            FROM mae_pdm_cuadrillas C
                            INNER JOIN SAP.SBO_Impulsora_PROD.dbo.OWHS AS pun
                                ON pun.WhsCode = C.idu_punto_venta COLLATE SQL_Latin1_General_CP850_CI_AS
                            WHERE C.opc_estatus = 1
                                AND pun.U_SerieSucursal = zona.Memo
                            ORDER BY C.nom_cuadrilla
                            FOR JSON PATH
                        )
                    ) AS listaCuadrillas
                FROM (SELECT SlpCode, SlpName, Memo
                      FROM SAP.SBO_Impulsora_PROD.dbo.OSLP
                      WHERE Active = 'Y'
                        AND (Memo IS NOT NULL AND Memo != '')) AS zona
                WHERE zona.Memo = (CASE WHEN @ClaveZona = '' THEN zona.Memo ELSE @ClaveZona END)
                    AND EXISTS
                    (
                        SELECT 1
                        FROM mae_pdm_cuadrillas C2
                        INNER JOIN SAP.SBO_Impulsora_PROD.dbo.OWHS AS pun2
                            ON pun2.WhsCode = C2.idu_punto_venta COLLATE SQL_Latin1_General_CP850_CI_AS
                        WHERE C2.opc_estatus = 1
                            AND pun2.U_SerieSucursal = zona.Memo
                    )
                ORDER BY zona.SlpName
                FOR JSON PATH
            );

            SET @mensaje = 'Información obtenida correctamente.';
            SET @estado = 0;

        END
        ELSE
        BEGIN
            SET @mensaje = 'Sin registro de tarifas.';
            SET @estado = -100;
        END
    END TRY
    BEGIN CATCH

        SET @estado = ERROR_NUMBER();
        SET @mensaje = ERROR_MESSAGE();
        SET @listaCuadrillas = '[]';

    END CATCH

    SELECT
        @estado AS estado,
        @mensaje AS mensaje,
        ISNULL(@listaCuadrillas,'[]') AS listaCuadrillas;

END:
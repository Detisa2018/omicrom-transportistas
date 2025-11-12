<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$request = utils\HTTPUtils::getRequest();

if (!$request->hasAttribute("FechaI")) {
    $FechaI = $Anio . "-" . $Mes . "-01";
    $FechaF = $Anio . "-" . $Mes . "-" . lastDayPerMonth($Anio, $Mes);
}

$Titulo = "Reporte de archivos del SAT del $FechaI al $FechaF ";

$selectLogs = "
        SELECT fecha, IF(reporte = 'M', 'MENSUAL' , 'DIARIO') reporte,
        etiqueta concepto, producto, SUM(valor) valor
        FROM resumen_reporte_sat
        WHERE TRUE
        AND fecha BETWEEN DATE('$FechaI') AND DATE('$FechaF')
        GROUP BY reporte, etiqueta, producto
        ORDER BY etiqueta, producto DESC
        ;";

$registros = utils\IConnection::getRowsFromQuery($selectLogs);

$SqlVc_ = "SELECT valor FROM variables_corporativo WHERE llave = 'quitaResumenSistema';";
$rsVc = utils\IConnection::execSql($SqlVc_);
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#Mes").val("<?= $Mes ?>");
                $("#Anio").val("<?= $Anio ?>");
            });
        </script>
    </head>

    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo); ?>
            <div id="Reportes">
                <table aria-hidden="true">
                    <thead>
                        <tr>
                            <td></td>
                            <td>Reporte</td>
                            <td>Concepto</td>
                            <td>Producto</td>
                            <td>Valor</td>
                            <?php if ($rsVc["valor"] != 1) { ?>
                                <td>Sistema</td>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 0;
                        $subtotal = $subtotalOmi = 0;
                        foreach ($registros as $rg) {
                            if ($rsVc["valor"] != 1) {
                                $Rs = getDataForTxt($rg["concepto"], $rg["producto"], $rg["reporte"], $FechaI, $FechaI, $FechaF);
                            }
                            ?>
                            <tr class="texto_tablas">
                                <td><?= $i + 1 ?></td>
                                <td><?= $rg["reporte"] ?></td>
                                <td><?= $rg["concepto"] ?></td>
                                <td><?= $rg["producto"] ?></td>
                                <td class="numero"><?= number_format($rg["valor"], 0, ".", ",") ?></td>
                                <?php if ($rsVc["valor"] != 1) { ?>
                                    <td class="numero"><?= number_format($Rs, 0, ".", ",") ?></td>
                                <?php } ?>
                            </tr>
                            <?php
                            $subtotal += $rg["valor"];
                            $subtotalOmi += number_format($Rs, 0, ".", "");
                            if ($registros[$i + 1]["concepto"] !== $rg["concepto"]) {
                                ?>
                                <tr class="subtotal">
                                    <td colspan="4">Total</td>
                                    <td><?= number_format($subtotal, 2, ".", ",") ?></td>                                
                                    <?php if ($rsVc["valor"] != 1) { ?>
                                        <td><?= number_format($subtotalOmi, 2, ".", ",") ?></td>
                                    <?php } ?>
                                </tr>
                                <?php
                                $subtotal = $subtotalOmi = 0;
                            }
                            $i++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="footer">
            <form name="formActions" method="post" action="" id="form" class="oculto">
                <div id="Controles">
                    <table aria-hidden="true">
                        <tr style="height: 40px;">
                            <td style="width: 30%;">
                                <table aria-hidden="true">
                                    <tr>
                                        <td>Mes:</td>
                                        <td>
                                            <select name="Mes" id="Mes">
                                                <?php
                                                foreach (getMonts() as $key => $value) {
                                                    echo "<option value='$key'>$value</option>";
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <table aria-hidden="true">
                                    <tr>
                                        <td>Año:</td>
                                        <td>
                                            <select name="Anio" id="Anio">
                                                <?php
                                                foreach (getYears() as $key => $value) {
                                                    echo "<option value='$key'>$value</option>";
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <?php
                                if ($request->getAttribute("return") === "resumen.php") {
                                    ?>
                                    <a href="<?= $request->getAttribute("return") ?>">
                                        <i class="fa fa-reply fa-2x" aria-hidden="true"></i>
                                    </a>
                                    <?php
                                }
                                ?>
                                <span><input type="submit" name="Boton" value="Enviar"></span>
                                <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
            <?php topePagina(); ?>
        </div>
    </body>
</html>

<?php

function getDataForTxt($Descripcion, $Producto, $Tipo, $Fecha, $FechaI, $FechaF) {
    $Sql = $F_Ini = $F_Fin = "";
    if ($Tipo === "MENSUAL") {
        $F_Ini = date('Ym01', strtotime($Fecha));
        $F_Fin = date('Ymt', strtotime($Fecha));
    } else {
        $F_Ini = date('Ymd', strtotime($Fecha));
        $F_Fin = date('Ymd', strtotime($Fecha));
    }

    switch ($Descripcion) {
        case "COMPRAS: (IMPORTE NETO PESOS)":
            $Sql = " SELECT round(sum((select sum(IF(STRCMP('E',me.tipocomprobante) = 0, -1, 1) * precio*cantidad) preciounitario from med a where id = me.id and clave in (1,2,3,4,5,10))),2)  resultado 
                        FROM cargas c LEFT JOIN me ON c.id = me.carga 
                        WHERE 
                        c.producto='$Producto'  AND c.tipo = 0 
                        AND me.tipo !=  'Jarreo' 
                        AND YEAR(DATE(fecha_fin)) = YEAR('$F_Ini')
                        AND MONTH(DATE(fecha_fin)) = MONTH('$F_Fin')
                        AND me.volumenfac > 0  
                        GROUP BY me.producto";
            break;
        case "COMPRAS: (NUMERO CARGAS)":
            $Sql = "SELECT  count(*)  resultado FROM cargas c WHERE c.producto='$Producto'
                        AND c.tipo = 0 AND YEAR(DATE(fecha_fin)) = YEAR('$F_Ini')
                        AND MONTH(DATE(fecha_fin)) = MONTH('$F_Fin')";
            break;
        case "COMPRAS: (NUMERO FACTURAS)":
            $Sql = " SELECT count(*)   resultado 
                        FROM cargas c LEFT JOIN me ON c.id = me.carga 
                        WHERE 
                        c.producto='$Producto'  AND c.tipo = 0 
                        AND me.tipo !=  'Jarreo' 
                        AND YEAR(DATE(fecha_fin)) = YEAR('$F_Ini')
                        AND MONTH(DATE(fecha_fin)) = MONTH('$F_Fin')
                        AND me.volumenfac > 0  
                        GROUP BY me.producto";
            break;
        case "COMPRAS: (VOLUMEN FACTURAS LITROS)":

            $Sql = "SELECT ROUND( sum(IF(STRCMP('E',me.tipocomprobante) = 0, -1, 1) * me.volumenfac*1000) , 2 ) resultado 
                        FROM cargas c LEFT JOIN me ON c.id = me.carga 
                        WHERE 
                        c.producto='$Producto' AND c.tipo = 0 
                        AND me.tipo !=  'Jarreo' 
                        AND YEAR(DATE(fecha_fin)) = YEAR('$F_Ini')
                        AND MONTH(DATE(fecha_fin)) = MONTH('$F_Fin')
                        AND me.volumenfac > 0  
                        GROUP BY me.producto";
            break;
        case "COMPRAS: (VOLUMEN SENSOR TANQUES LITROS)":
            $Sql2 = "SELECT clave_producto FROM cargas WHERE producto  ='$Producto' limit 1";
            $Clave = utils\IConnection::execSql($Sql2);
            $Vc1 = "SELECT valor FROM variables_corporativo WHERE llave = 'REPORTA_VOLUMEN_BRUTO'";
            $Vc_r01 = utils\IConnection::execSql($Vc1);
            $Vc2 = "SELECT valor FROM variables_corporativo WHERE llave = 'CV_SUMA_VTA_DESCARGA'";
            $Vc_r02 = utils\IConnection::execSql($Vc2);
            if ($Vc_r01["valor"] == 1 && $Vc_r01["valor"] == 1) {
                $primerDiaMes = date("Y-m-01", strtotime($FechaI));
                $AddSql = "
                        CASE 
                        WHEN IFNULL(valor_variable('REPORTA_VOLUMEN_BRUTO'),'0') = '0' THEN
                            IFNULL(SUM(CASE WHEN aumento_merma != 0 THEN aumento_merma ELSE c.tcaumento END ),0)
                        ELSE
                        IFNULL(SUM(c.aumento),0) + 
                        CASE WHEN IFNULL(valor_variable('CV_SUMA_VTA_DESCARGA'),'0')= 1 AND IFNULL(SUM(c.aumento),0) > 0 THEN
                         getVolumenDescarga('" . $Clave["clave_producto"] . "','$primerDiaMes','$FechaF') 
                        ELSE 0 END
                        END resultado";
            } else {
                $AddSql = "
                        CASE 
                        WHEN IFNULL(valor_variable('REPORTA_VOLUMEN_BRUTO'),'0') = '0' THEN
                            IFNULL(SUM(CASE WHEN aumento_merma != 0 THEN aumento_merma ELSE c.tcaumento END ),0)
                        ELSE
                        IFNULL(SUM(c.aumento),0) + 
                        CASE WHEN IFNULL(valor_variable('CV_SUMA_VTA_DESCARGA'),'0')= 1 AND IFNULL(SUM(c.aumento),0) > 0 THEN
                         getVolumenDescarga('" . $Clave["clave_producto"] . "','$FechaI','$Fecha') 
                        ELSE 0 END
                        END resultado";
            }

            $Sql = "  SELECT
                        $AddSql
                        FROM
                        cargas c
                        WHERE c.producto='$Producto'
                        AND c.tipo = 0
                        AND YEAR(DATE(fecha_fin)) = YEAR('$F_Ini')
                        AND MONTH(DATE(fecha_fin)) = MONTH('$F_Fin')";
            break;
        case "INVENTARIO FIN DE MES":
            $Sql = "SELECT
                            CASE  
                            WHEN IFNULL(valor_variable('REPORTA_VOLUMEN_BRUTO'),'0') = '0' THEN
                                SUM(volumen_compensado) 
                            ELSE 
                                SUM(cantidad) 
                            END resultado 
                        FROM (
                        SELECT * FROM (
                        SELECT IFNULL(
                        ROUND(volumen_actual , 3), 0) cantidad
                        ,DATE ( fecha_hora_s )
                        fecha
                        , tanque
                        , IFNULL(ROUND(volumen_compensado , 3), 0) volumen_compensado
                        FROM tanques_h
                        WHERE producto LIKE '$Producto'
                                AND DATE ( fecha_hora_s ) = DATE_ADD(DATE('$F_Fin'),INTERVAL 1 DAY)
                                ORDER BY fecha_hora_s DESC
                        ) t
                        GROUP BY DATE ( t.fecha ),t.tanque
                        ) t GROUP BY DATE ( fecha )";
            break;
        case "VENTAS: (IMPORTE FACTURAS EMITIDAS)":
            $Sql = "SELECT SUM(rm.importe) resultado FROM fc INNER JOIN cli ON cli.id = fc.cliente "
                    . "INNER JOIN fcd ON fc.id = fcd.id INNER JOIN rm ON rm.id = fcd.ticket "
                    . "INNER JOIN com ON com.clavei=rm.producto  "
                    . "WHERE com.descripcion = '$Producto' AND "
                    . "YEAR(DATE(fc.fecha)) = YEAR(DATE('$F_Ini')) AND MONTH(DATE(fc.fecha)) = MONTH(DATE('$F_Fin')) "
                    . "AND fcd.producto <=4 AND fc.status = 1";
            break;
        case "VENTAS: (IMPORTE PESOS) ":
            $Sql = "SELECT IFNULL(SUM(rm.importe),0) resultado FROM rm "
                    . "LEFT JOIN com ON com.clavei = rm.producto "
                    . "WHERE com.descripcion='$Producto'  AND tipo_venta = 'D' "
                    . "AND DATE(rm.fecha_venta) BETWEEN DATE('$F_Ini') AND DATE('$F_Fin')";

            break;
        case "VENTAS: (NUMERO DE VENTAS) ":
            $Sql = "SELECT IFNULL(COUNT(1),0) resultado FROM rm "
                    . "LEFT JOIN com ON com.clavei = rm.producto "
                    . "WHERE com.descripcion='$Producto'  AND tipo_venta = 'D' "
                    . "AND DATE(fecha_venta) BETWEEN DATE('$F_Ini') AND DATE('$F_Fin')";
            break;
        case "VENTAS: (VOLUMEN LITROS) ":
            $Sql = "SELECT IFNULL(SUM(rm.importe/rm.precio),0) resultado FROM rm "
                    . "LEFT JOIN com ON com.clavei = rm.producto "
                    . "WHERE com.descripcion='$Producto'  AND tipo_venta = 'D' "
                    . "AND DATE(fecha_venta) BETWEEN DATE('$F_Ini') AND DATE('$F_Fin')";
            break;
    }
    $restultado = utils\IConnection::execSql($Sql);
    return $restultado["resultado"];
}

<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
include_once ("services/ReportesVentasService.php");
$serie = $request->getAttribute("serie");
$mes = $Mes;
$anio = $Anio;
$piezas = $request->getAttribute("piezas");
$sigmes = $mes + 1;
$fechas = array("01" => "Enero", "02" => "Febrero", "03" => "Marzo", "04" => "Abril",
    "05" => "Mayo", "06" => "Junio", "07" => "Julio", "08" => "Agosto", "09" => "Septiembre",
    "10" => "Octubre", "11" => "Noviembre", "12" => "Diciembre");
//echo $selectFacturas;
$registros = utils\IConnection::getRowsFromQuery($selectFacturas);

$Titulo = "Detallado en el siguiente mes del mes: " . $mes . " del año" . $anio;

$Id = 200;
$data = array("Nombre" => $Titulo, "Reporte" => $Id,
    "mes" => $mes, "anio" => $anio, "mesig" => $sigmes
);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <title><?= $Gcia ?></title>
    </head>
    <script>
        $(document).ready(function () {
            $("#FechaI").val("<?= $FechaI ?>");
        });
    </script>
    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo); ?>
            <div id="Reportes">
                <?php
                $fecha_original = $FechaI;
                $fecha_formateada = DateTime::createFromFormat("Y-m-d", $fecha_original)->format("Ymd");

                $CntVentas = "SELECT COUNT(*) r 
                FROM rm 
                WHERE rm.fecha_venta = $fecha_formateada
                AND dis_mang > 0 AND tipo_venta IN ('D','J','N','A')
                AND importe > 0.05
                AND procesado = 1";
                $Vts = utils\IConnection::execSql($CntVentas);
                $ConfigMan = "SELECT dispensario ,posicion,dis_mang manguera,com.clave,manguera manguera_l, 
                    com.claveProducto, com.claveSubproducto, com.GasConEtanol, 
                    com.comDeEtanolEnGasolina, com.otros, com.marca, com.ComOctanajeGas 
                    FROM com , man_pro   
                    WHERE  man_pro.producto = com.clavei AND man_pro.activo = 'Si' AND com.activo = 'Si' 
                    ORDER BY dispensario , dis_mang";
                $Cm = utils\IConnection::getRowsFromQuery($ConfigMan);
                ?>
                Conteo de ventas <?= $Vts["r"] ?>
                <?php
                $imp = 0;
                $i = 0;
                foreach ($Cm as $rg) {
                    $SqlG = "SELECT v.id, v.dispensario dispensario, v.manguera, 
                            ROUND(v.importe/v.precio,3) volumen
                            , v.precio , c.clave
                             , v.tipo_venta tipoventa 
                            , TRUNCATE(v.importe,2) importe 
                             , v.fin_venta finventa 
                             , c.claveProducto, c.claveSubproducto, c.GasConEtanol 
                             , c.comDeEtanolEnGasolina, c.otros, c.marca, c.ComOctanajeGas 
                             FROM rm v 
                             LEFT JOIN com c ON v.producto = c.clavei AND c.activo = 'Si' 
                             WHERE v.fecha_venta = $fecha_formateada
                             AND v.dispensario = '" . $rg["dispensario"] . "' AND v.posicion = '" . $rg["posicion"] . "' AND v.manguera = '" . $rg["manguera_l"] . "'
                             AND v.dis_mang > 0  AND v.tipo_venta IN ('D','J','N','A') 
                             AND v.procesado = 1 
                             AND importe > 0.05
                             ORDER BY v.id";
                    $Gg = utils\IConnection::getRowsFromQuery($SqlG);
                    ?>
                    <table aria-hidden="true">
                        <tbody>
                            <?php
                            $e = 0;
                            foreach ($Gg as $rgg) {
                                $e++;
                                $i++;
                            }

                            $CntVentas1 = "SELECT COUNT(*) r 
                FROM rm 
                WHERE rm.fecha_venta = $fecha_formateada
                AND dis_mang > 0 AND tipo_venta IN ('D','J','N','A')
                AND importe > 0.05 AND dispensario = '" . $rg["dispensario"] . "' AND posicion = '" . $rg["posicion"] . "' AND manguera = '" . $rg["manguera_l"] . "'
                AND procesado = 1";
                            $Vts1 = utils\IConnection::execSql($CntVentas1);
                            $CntVentas2 = "SELECT COUNT(*) r 
                FROM rm 
                WHERE rm.fecha_venta = $fecha_formateada
                AND dis_mang > 0 AND tipo_venta IN ('D','J','N','A')
                AND importe > 0.05 AND dispensario = '" . $rg["dispensario"] . "' AND posicion = '" . $rg["posicion"] . "'
                AND procesado = 1";
                            $Vts2 = utils\IConnection::execSql($CntVentas2);
                            ?> 
                            <tr>
                                <td colspan="100%" style="font-size: 17px;">
                                    Conteo por venta <?= $e ?> <br> 
                                    Conteo por disp,pos,man <?= $Vts1["r"] ?> <br> 
                                    Conteo por disp,pos <?= $Vts2["r"] ?><br>
                                    Dispensario : <?= $rg["dispensario"] ?> <br> 
                                    Posicion : <?= $rg["posicion"] ?> <br>
                                    Manguera : <?= $rg["manguera_l"] ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <?php
                    $imp += $rg["importe"];
                }
                ?>
                Total <?= $i ?>
            </div>
        </div>
        <div id="footer">
            <form name="formActions" method="post" action="" id="form" class="oculto">
                <div id="Controles">
                    <table aria-hidden="true">
                        <tr style="height: 40px;">
                            <td>
                                <input type="date" name="FechaI" id="FechaI">
                                <span class="ButtonExcel"><a href="report_excel_reports.php?<?= http_build_query($data) ?>"><i class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true"></i></a></span>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
            <?php topePagina(); ?>
        </div>
    </body>
</html>


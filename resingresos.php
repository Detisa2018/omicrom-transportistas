<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";
$Titulo = "Seguimiento de anticipos del $FechaI al $FechaF ";

if ($request->hasAttribute("criteria")) {
    utils\HTTPUtils::setSessionValue("StatusCancelada", false);
    utils\HTTPUtils::setSessionValue("StatusAbierta", false);
    utils\HTTPUtils::setSessionValue("StatusCerrada", false);
    utils\HTTPUtils::setSessionValue("StatusTodos", true);
} else if ($request->hasAttribute("Cancelada") || $request->hasAttribute("Abierta") || $request->hasAttribute("Cerrada") || $request->hasAttribute("Todos")) {
    utils\HTTPUtils::setSessionValue("StatusCancelada", $request->hasAttribute("Cancelada"));
    utils\HTTPUtils::setSessionValue("StatusAbierta", $request->hasAttribute("Abierta"));
    utils\HTTPUtils::setSessionValue("StatusCerrada", $request->hasAttribute("Cerrada"));
    utils\HTTPUtils::setSessionValue("StatusTodos", $request->hasAttribute("Todos"));
}

if (!$request->hasAttribute("criteria")) {
    $Sql = "SELECT p.id, p.serie,p.cliente, cli.nombre, p.fecha, p.status, p.uuid, p.importe,p.concepto FROM pagos p LEFT JOIN cli ON cli.id = p.cliente "
            . " WHERE DATE(p.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF') AND cli.tipodepago = 'Prepago';";
    $Sql2 = "";
}

$registros = utils\IConnection::getRowsFromQuery($Sql);
$registros2 = utils\IConnection::getRowsFromQuery($Sql2);
$Id = 32; /* Número de en el orden de la tabla submenus */
$data = array("Nombre" => $Titulo, "Reporte" => $Id,
    "FechaI" => $FechaI, "FechaF" => $FechaF,
    "Detallado" => $Detallado, "Desglose" => $Desglose,
    "Turno" => $Turno, "Textos" => "Subtotal", "Filtro" => "1");
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>

        <?php require "./config_omicrom_reports.php"; ?>         

        <script type="text/javascript" src="https://unpkg.com/xlsx@0.15.1/dist/xlsx.full.min.js"></script>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#FechaI").val("<?= $FechaI ?>").attr("size", "10");
                $("#FechaF").val("<?= $FechaF ?>").attr("size", "10");
                $("#cFechaI").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaI")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#cFechaF").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaF")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#Todos").prop("checked",<?= utils\HTTPUtils::getSessionValue("StatusTodos") ?>);
                $("#Abierta").prop("checked",<?= utils\HTTPUtils::getSessionValue("StatusAbierta") ?>);
                $("#Cerrada").prop("checked",<?= utils\HTTPUtils::getSessionValue("StatusCerrada") ?>);
                $("#Cancelada").prop("checked",<?= utils\HTTPUtils::getSessionValue("StatusCancelada") ?>);

                $(".botonAnimatedMin").on("click", function () {
                    $(".botonAnimatedMin").prop("checked", false);
                    $(this).prop("checked", true);
                });

            });
            function ExportToExcel(type, fn, dl) {
                var elt = document.getElementById('tbl_exporttable_to_xls');
                var wb = XLSX.utils.table_to_book(elt, {sheet: "sheet1"});
                return dl ?
                        XLSX.write(wb, {bookType: type, bookSST: true, type: 'base64'}) :
                        XLSX.writeFile(wb, fn || ('ReporteGerencia.' + (type || 'xlsx')));
            }
        </script>
    </head>

    <body>
        <div id="tbl_exporttable_to_xls">
            <div id="container">
                <?php nuevoEncabezado($Titulo); ?>
                <div id="Reportes" style="min-height: 200px;width: 100%;"> 
                    <?php
                    $SumTotal = 0;
                    $Nombre = "-";
                    foreach ($registros as $rg) {
                        ?>  
                        <table aria-hidden="true" style="width: 100%;margin-bottom: 18px;">
                            <thead>
                                <tr style="font-size: 14px;">
                                    <td style="width: 8%;">Id Pago</td>
                                    <td style="width: 11%;">Fecha</td>
                                    <td style="width: 16%;">Cliente</td>
                                    <td style="width: 25%;">Concepto</td>
                                    <td style="width: 22%;">Uuid</td>
                                    <td  style="width: 6%;">Status</td>
                                    <td style="width: 10%">Importe</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="background-color: #E7E5E4 !important;">
                                    <td style="text-align: center;"><?= $rg["serie"] ?> - <?= $rg["id"] ?></td>
                                    <td><?= $rg["fecha"] ?></td>
                                    <td><?= mb_convert_case($rg["nombre"], MB_CASE_TITLE, "UTF-8") ?></td>
                                    <td><?= $rg["concepto"] ?></td>
                                    <td><?= $rg["uuid"] ?></td>
                                    <td><?= $rg["status"] ?></td>
                                    <td style="text-align: right">$<?= number_format($rg["importe"], 2) ?></td>
                                </tr>
                                <?php
                                $SqlSub = "SELECT rc.uuid,rc.serie,rc.folio_factura,rc.importe,fc.total impFactura,
                                    nc.id,nc.serie serienc,nc.uuid uuidnc,nc.total impNc,fc.fecha fechaFc, nc.fecha fechaNc
                                            FROM omicrom.relacion_cfdi rc 
                                            LEFT JOIN nc ON nc.id = rc.id_nc 
                                            LEFT JOIN fc ON fc.id = rc.id_fc
                                            WHERE rc.uuid_relacionado='" . $rg["uuid"] . "';";
                                $rsS = utils\IConnection::getRowsFromQuery($SqlSub);
                                $SqlSub2 = "SELECT fc.folio,fc.serie,fc.uuid,fc.total importe,nc.id,nc.serie serienc,nc.total importenc ,nc.uuid uuidnc,"
                                        . "fc.fecha fechaFc, nc.fecha fechaNc "
                                        . "FROM omicrom.fc "
                                        . "LEFT JOIN nc ON fc.id = nc.relacioncfdi "
                                        . "WHERE fc.relacioncfdi = " . $rg["id"];
                                $Vv = utils\IConnection::execSql($SqlSub2);
                                $ImpFactura = $ImpNc = $importe = 0;
                                if ($rsS[0][0] <> '' || $Vv["folio"] > 0) {
                                    ?>
                                    <tr  style="background-color: white !important;">
                                        <td colspan="7">
                                            <div id="Reportes" style="min-height: 50px; width: 94%;margin-left: 3%;"> 
                                                <table style="width: 100%">
                                                    <thead>
                                                        <tr style="font-weight: bold;">
                                                            <td>Factura</td>
                                                            <td>Fecha</td>
                                                            <td>Uuid</td>
                                                            <td>Factura</td>
                                                            <td>Relacionado</td>
                                                            <td>Nta.Cred.</td>
                                                            <td>Fecha</td>
                                                            <td>Uuid</td>
                                                            <td>Importe N.C.</td>
                                                        </tr>
                                                    </thead>
                                                    <?php
                                                    $SeconValue = true;
                                                    if ($Vv["folio"] > 0) {
                                                        $SeconValue = false;
                                                        ?>
                                                        <tr style="background-color: white !important;">
                                                            <td><?= $Vv["serie"] ?> - <?= $Vv["folio"] ?></td>
                                                            <td><?= $Vv["fechaFc"] ?></td>
                                                            <td><?= $Vv["uuid"] ?></td>
                                                            <td style="text-align: right;">$<?= number_format($Vv["importe"], 2) ?></td>
                                                            <td style="text-align: right;">$<?= number_format($Vv["importe"], 2) ?></td>
                                                            <td><?= $Vv["serienc"] ?> - <?= $Vv["id"] ?></td>
                                                            <td><?= $Vv["fechaNc"] ?></td>
                                                            <td><?= $Vv["uuidnc"] ?></td>
                                                            <td style="text-align: right;">$<?= number_format($Vv["importenc"], 2) ?></td>
                                                        </tr>
                                                        <?php
                                                        $ImpFactura += $Vv["importe"];
                                                        $importe += $Vv["importe"];
                                                        $ImpNc += $Vv["importenc"];
                                                    }

                                                    $s = 0;
                                                    if ($SeconValue) {
                                                        foreach ($rsS as $rss) {
                                                            $addColor = $s % 2 == 0 ? "white" : "rgb(218, 218, 218)";
                                                            ?>
                                                            <tr style="background-color: white !important;">
                                                                <td><?= $rss["serie"] ?> - <?= $rss["folio_factura"] ?></td>
                                                                <td><?= $rss["fechaFc"] ?></td>
                                                                <td><?= $rss["uuid"] ?></td>
                                                                <td style="text-align: right;padding-right: 15px;">$<?= number_format($rss["impFactura"], 2) ?></td>
                                                                <td style="text-align: right;padding-right: 15px;">$<?= number_format($rss["importe"], 2) ?></td>
                                                                <td><?= $rss["serienc"] ?> - <?= $rss["id"] ?></td>
                                                                <td><?= $rss["fechaNc"] ?></td>
                                                                <td><?= $rss["uuidnc"] ?></td>
                                                                <td style="text-align: right;padding-right: 15px;">$<?= number_format($rss["impNc"], 2) ?></td>
                                                            </tr>
                                                            <?php
                                                            $ImpFactura += $rss["importe"];
                                                            $importe += $rss["impFactura"];
                                                            $ImpNc += $rss["impNc"];
                                                            $s++;
                                                        }
                                                    }
                                                    ?>
                                                    <tr style="background-color: white;">
                                                        <td colspan="3" style="text-align: right;width: 40%;">
                                                            Total
                                                        </td>
                                                        <td style="text-align: right;padding-right: 15px;width: 6%;">
                                                            $<?= number_format($importe, 2) ?>
                                                        </td>
                                                        <td style="text-align: right;padding-right: 15px;">$<?= number_format($ImpFactura, 2) ?></td>
                                                        <td colspan="3"></td>
                                                        <td style="text-align: right;padding-right: 15px;">$<?= number_format($ImpNc, 2) ?></td>
                                                    </tr> 
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                                <?php
                            }
                            $SumTotal += $rg["importe"];
                        }
                        ?>
                    </table>
                </div>
            </div>
        </div>
        <div id="footer">
            <form name="formActions" method="post" action="" id="form" class="oculto">
                <div id="Controles">
                    <table aria-hidden="true">
                        <tr style="height: 40px;">
                            <td>
                                <table aria-hidden="true" style="width: 100%;">
                                    <tr>
                                        <td style="width: 15%;text-align: right;">F.inicial:</td>
                                        <td style="text-align: left; width: 50px;"><input type="text" id="FechaI" name="FechaI"></td>
                                        <td class="calendario"  style="width: 100px !important;text-align: left; "><i id="cFechaI" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                                        <td style="width: 15%;text-align: right;">F.final:</td>
                                        <td style="text-align: left; width: 50px;"><input type="text" id="FechaF" name="FechaF"></td>
                                        <td class="calendario" style="width: 100px !important;text-align: left; "><i id="cFechaF" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                                        <td style="text-align: right;">
                                            <span><input type="submit" name="Boton" value="Enviar"></span>
                                        </td>
                                        <td>
                                            <?php
                                            if ($usuarioSesion->getTeam() !== "Operador") {
                                                ?>                                                                                                                                                                       <!--<span class="ButtonExcel"><a href="report_excel_reports.php?<?= http_build_query($data) ?>"><i class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true"></i></a></span>-->
                                                <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                                                        <?php
                                                    }
                                                    ?>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
            <?php topePagina(); ?>
        </div>
    </body>
</html>

<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";
$Titulo = "Compra de aceites y otros del $FechaI al $FechaF ";

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
if (utils\HTTPUtils::getSessionValue("StatusCancelada")) {
    $Sts = "Canceladas";
    $AddSqlSts = " AND et.status = 'Cancelado'";
} else if (utils\HTTPUtils::getSessionValue("StatusAbierta")) {
    $Sts = "Abiertas";
    $AddSqlSts = " AND et.status = 'Abierta'";
} else if (utils\HTTPUtils::getSessionValue("StatusCerrada")) {
    $Sts = "Cerradas";
    $AddSqlSts = " AND et.status = 'Cerrada'";
} else {
    $Sts = "Todos";
}
$Titulo = $Titulo . " Estatus : " . $Sts;

if (!$request->hasAttribute("criteria")) {
    $Sql = "SELECT et.id,et.uuid,DATE(et.fecha) fecha,prv.id idprv,prv.nombre,cantidad,importesin , et.iva ,et.status,et.documento FROM omicrom.et LEFT JOIN prv ON et.proveedor=prv.id "
            . " WHERE DATE(fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF') $AddSqlSts;";
    $Sql2 = "SELECT inv.id, inv.descripcion descripcion ,SUM(etd.cantidad) cnt,etd.costo costo, SUM(etd.descuento) descuento , SUM((etd.cantidad * etd.costo)) total FROM omicrom.et 
                LEFT JOIN etd ON et.id = etd.id LEFT JOIN inv ON inv.id = etd.producto 
                WHERE DATE(et.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF') $AddSqlSts GROUP BY inv.id,etd.costo,etd.descuento ORDER BY inv.id ASC;";
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
                <div id="Reportes" style="min-height: 200px;"> 


                    <table aria-hidden="true">
                        <thead>
                            <tr>
                                <td colspan="11" style="font-size: 18px;">Compras por factura</td>
                            </tr>
                            <tr>
                                <td>Id</td>
                                <td>Fecha</td>
                                <td>Prv</td>
                                <td>Nombre</td>
                                <td>No.Factura</td>
                                <td>Uuid</td>
                                <td>Status</td>
                                <td>Cantidad</td>
                                <td>Importe</td>
                                <td>Iva</td>
                                <td>Total</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $Vts = $impAce = 0;
                            foreach ($registros as $rg) {
                                ?>
                                <tr>
                                    <td><?= $rg["id"] ?></td>
                                    <td><?= $rg["fecha"] ?></td>
                                    <td style="text-align: right;margin-right: 15px;"><?= $rg["idprv"] ?></td>
                                    <td><?= mb_convert_case($rg["nombre"], MB_CASE_TITLE, "UTF-8") ?></td>
                                    <td style="text-align: right;"><?= $rg["documento"] ?></td>
                                    <td><?= $rg["uuid"] ?></td>
                                    <td><?= $rg["status"] ?></td>
                                    <td style="text-align: right"><?= $rg["cantidad"] ?></td>
                                    <td style="text-align: right">$<?= number_format($rg["importesin"], 2) ?></td>
                                    <td style="text-align: right">$<?= number_format($rg["iva"], 2) ?></td>
                                    <td style="text-align: right">$<?= number_format($rg["importesin"] + $rg["iva"], 2) ?></td>
                                </tr>
                                <?php
                                $SumCantidad += $rg["cantidad"];
                                $SumImporte += ($rg["importesin"] );
                                $SumIVA += $rg["iva"];
                                $SumTotal += $rg["importesin"] + $rg["iva"];
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7"> Total -></td>
                                <td><?= number_format($SumCantidad, 2) ?></td>
                                <td>$<?= number_format($SumImporte, 2) ?></td>
                                <td>$<?= number_format($SumIVA, 2) ?></td>
                                <td>$<?= number_format($SumTotal, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                    <table aria-hidden="true" style="width: 90%;margin-left: 5%;">
                        <thead>
                            <tr>
                                <td colspan="9" style="font-size: 16px;">Detalle de compras por producto</td>
                            </tr>
                            <tr>
                                <td>Clave</td>
                                <td>Descripcion</td>
                                <td>Cantidad</td>
                                <td>Costo</td>
                                <td>Descuento</td>
                                <td>Cto. c/ Desc.</td>
                                <td>Importe</td>
                                <td>Iva</td>
                                <td>Total</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($registros2 as $r2) {
                                $CostoConDescuento = ($r2["costo"] * (1 - $r2["descuento"])) * $r2["cnt"];
                                $IvaCostoConDescuento = $CostoConDescuento * 0.16;
                                ?>
                                <tr>
                                    <td style="text-align: right;margin-right: 15px;"><?= $r2["id"] ?></td>
                                    <td><?= mb_convert_case($r2["descripcion"], MB_CASE_TITLE, "UTF-8") ?></td>
                                    <td style="text-align: right;"><?= number_format($r2["cnt"], 0) ?></td>
                                    <td style="text-align: right;">$<?= number_format($r2["costo"], 2) ?></td>
                                    <td style="text-align: right;"><?= number_format($r2["descuento"] * 100, 0) ?> %</td>
                                    <td style="text-align: right;">$<?= number_format($r2["costo"] * (1 - $r2["descuento"]), 2) ?></td>
                                    <td style="text-align: right;">$<?= number_format($CostoConDescuento, 2) ?></td>
                                    <td style="text-align: right;">$<?= number_format($IvaCostoConDescuento, 2) ?></td>
                                    <td style="text-align: right;">$<?= number_format($CostoConDescuento + $IvaCostoConDescuento, 2) ?></td>
                                </tr>
                                <?php
                                $SumDCnt += $r2["cnt"];
                                $SumDCosto += $r2["costo"];
                                $SumDCostoCDesc += $r2["costo"] * (1 - $r2["descuento"]);
                                $SumDDesc += $r2["descuento"];
                                $SumDImporte += $CostoConDescuento;
                                $SumDIVA += $IvaCostoConDescuento;
                                $SumDTotal += $CostoConDescuento + $IvaCostoConDescuento;
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" style="text-align: right;">Total -></td>
                                <td style="text-align: right"><?= number_format($SumDCnt, 0) ?></td>
                                <td style="text-align: right"></td>
                                <td style="text-align: right"></td>
                                <td style="text-align: right"></td>
                                <td style="text-align: right">$<?= number_format($SumDImporte, 2) ?></td>
                                <td style="text-align: right">$<?= number_format($SumDIVA, 2) ?></td>
                                <td style="text-align: right">$<?= number_format($SumDTotal, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
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
                                        <td>F.inicial:</td>
                                        <td><input type="text" id="FechaI" name="FechaI"></td>
                                        <td class="calendario"><i id="cFechaI" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                                    </tr>
                                    <tr>
                                        <td>F.final:</td>
                                        <td><input type="text" id="FechaF" name="FechaF"></td>
                                        <td class="calendario"><i id="cFechaF" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                                    </tr>
                                </table>
                            </td>
                            <td><div style="font-size: 15px;font-weight: bold;">Status:</div>
                                <input type="checkbox" name="Todos" id="Todos" class="botonAnimatedMin"> Todos
                                <input type="checkbox" name="Abierta" id="Abierta" class="botonAnimatedMin"> Abiertas
                                <input type="checkbox" name="Cerrada" id="Cerrada" class="botonAnimatedMin"> Cerrada
                                <input type="checkbox" name="Cancelada" id="Cancelada" class="botonAnimatedMin"> Cancelada
                            </td>
                            <td>
                                <span><input type="submit" name="Boton" value="Enviar"></span>
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
                </div>
            </form>
            <?php topePagina(); ?>
        </div>
    </body>
</html>

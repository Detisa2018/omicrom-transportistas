<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$Titulo = "Facturas realizadas a publico en General de $FechaI al $FechaF ";
$registros = utils\IConnection::getRowsFromQuery($SelectFPG);
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
                $("#FormaPago").val("<?= $FormaPago ?>");
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
        <?php nuevoEncabezado($Titulo); ?> 
        <div id="tbl_exporttable_to_xls">
            <div id="container">
                <div id="Reportes" style="min-height: 200px;"> 
                            <table aria-hidden="true" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <td>Serie</td>
                                        <td>Folio</td>
                                        <td>Clave RFC</td>
                                        <td>Entidad</td>
                                        <td>Fecha Emisión</td>
                                        <td>Subtotal</td>
                                        <td>Descuento</td>
                                        <td>Iva</td>
                                        <td>Total</td>
                                        <td>Estado</td>
                                        <td>Tipo</td>
                                        <td>Usuario</td>
                                        <td>Tipo Doc.</td>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php
                                    $fcdFact = array();
                                    foreach ($registros as $rg) {
                                        ?>
                                        <tr>
                                            <td><?= $rg["serie"] ?></td>
                                            <td><?= $rg["folio"] ?></td>
                                            <td><?= $rg["rfc"] ?></td>
                                            <td><?= $rg["nombre"] ?></td>
                                            <td><?= $rg["fecha"] ?></td>
                                            <td><?= number_format($rg["importe"],2) ?></td>
                                            <td><?= number_format($rg["descuento"],2) ?></td>
                                            <td><?= number_format($rg["iva"],2) ?></td>
                                            <td><?= number_format($rg["total"],2) ?></td>
                                            <td><?= $rg["status"] ?></td>
                                            <td><?= $rg["tipo"] ?></td>
                                            <td><?= $rg["usr"] ?></td>
                                            <td><?= $rg["factura"] ?></td>
                                        </tr>
                                        <?php
                                        array_push($fcdFact,$rg["id"]);
                                        $Subtotal += $rg["importe"];
                                        $Descuento += $rg["descuento"];
                                        $Iva += $rg["iva"];
                                        $Total += $rg["total"];
                                    }
                                        ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td>Total</td>
                                        <td class="moneda"><?= number_format($Subtotal, 2) ?></td>
                                        <td class="moneda"><?= number_format($Descuento, 2) ?></td>
                                        <td class="moneda"><?= number_format($Iva, 2) ?></td>
                                        <td class="moneda"><?= number_format($Total, 2) ?></td>
                                        <td ></td>
                                        <td ></td>
                                        <td ></td>
                                        <td ></td>
                                    </tr>
                                </tfoot>
                            </table>
                </div>
            </div>
        </div>
        <div id="TablaDatosReporte">
            <div style="width: 60%;padding-top: 10px;min-height: 200px;margin-left: auto;margin-right: auto;">
                <div><h3>PRODUCTOS   FACTURADOS</h3></div>
                    <table aria-hidden="true">
                        <thead>
                            <tr class="tableexport-ignore">
                                <td>Clave</td>
                                <td>Producto</td>
                                <td>Cantidad</td>
                                <td>Volumen</td>
                                <td>Subtotal</td>
                                <td>Descuento</td>
                                <td>Iva</td>
                                <td>Total</td>

                            </tr>
                        </thead>
                            <tbody>
                                <?php
                                $placeholders = implode(',', $fcdFact);
                                $SELECTFACT = "SELECT inv.clave_producto ,inv.descripcion, 
                                            sum(if(producto>5,fcd.cantidad,0)) piezas,
                                            sum(if(producto<5,fcd.cantidad,0)) volumen, 
                                            sum( round((fcd.cantidad * fcd.precio),3)) importe,
                                            sum( round((fcd.cantidad * fcd.precio) * fcd.iva,3)) iva, 
                                            sum( round( (fcd.cantidad * fcd.ieps) ,3)) ieps, 
                                            sum( round( (fcd.descuento) ,2) ) descueto,
                                            sum(fcd.importe) total 
                                        FROM fc 
                                            left join fcd on fc.id = fcd.id 
                                            left join inv on fcd.producto = inv.id 
                                        WHERE fc.id in ($placeholders)                 
                                        group by producto";

                                $registrosT = utils\IConnection::getRowsFromQuery($SELECTFACT);
                                    foreach ($registrosT as $rg) {
                                ?>
                                    <tr class="tableexport-ignore">                 
                                        <td><?= $rg["clave_producto"] ?></td>
                                        <td><?= $rg["descripcion"] ?></td>
                                        <td class="numero"><?= number_format($rg["piezas"], 0) ?></td>
                                        <td class="numero"><?= number_format($rg["volumen"], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["importe"] + $rg["ieps"], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["descuento"], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["iva"], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["total"], 2) ?></td>
                                    </tr>
                                <?php

                                    $PiezasT += $rg["piezas"];
                                    $VolumenT += $rg["volumen"];
                                    $ImporteT += $rg["importe"] + $rg["ieps"];

                                    $DescuentoT += $rg["descuento"];
                                    $IvaT += $rg["iva"];
                                    $TotalT += $rg["total"];
                                }
                                ?>
                            </tbody>
                                    <tfoot>
                                        <tr class="tableexport-ignore">
                                            <td colspan="2">Gran Total</td>
                                            <td><?= number_format($PiezasT, 0) ?></td>
                                            <td><?= number_format($VolumenT, 2) ?></td>
                                            <td><?= number_format($ImporteT, 2) ?></td>
                                            <td><?= number_format($DescuentoT, 2) ?></td>
                                            <td><?= number_format($IvaT, 2) ?></td>
                                            <td><?= number_format($TotalT, 2) ?></td>

                                        </tr>
                                    </tfoot>
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
                            <td>
                                <table style="width: 100%" aria-hidden="true">
                                    <tr>
                                        <td>&nbsp;Forma Pago:</td>
                                        <td style="text-align: left;padding-left: 5px">
                                            <select id="FormaPago" name="FormaPago">
                                                <option value="*">Todos</option>
                                                <option value="Contado">Contado</option>
                                                <option value="Tarjeta">Tarjeta</option>
                                                <option value="Monedero">Monedero</option>
                                                <option value="Aditivos">Aditivos</option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <span><input type="submit" name="Boton" value="Enviar"></span>                                                                                                                                                                                                                                              <!--<span class="ButtonExcel"><a href="report_excel_reports.php?<?= http_build_query($data) ?>"><i class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true"></i></a></span>-->
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
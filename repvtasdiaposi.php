<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$Titulo = "Ventas por día del $FechaI al $FechaF $DetalleTexto [Reporte Contable]";
$registros = utils\IConnection::getRowsFromQuery($SelectByDiaPosicion);
$registrosT = utils\IConnection::getRowsFromQuery($SelectByDiaPosicionT);

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
                $("#Detallado").val("<?= $Detallado ?>");
                $("#Desglose").val("<?= $Desglose ?>");
                $("#Turno");
                comboTurno();

                $("#Detallado").change(function () {
                    comboTurno();
                });

                $("#Desglose").change(function () {
                    comboTurno();
                });

                $("#FechaI").focus();

                function comboTurno() {
                    if ($("#Detallado").val() === "Si" && $("#Desglose").val() === "Cortes") {
                        $("#Turno").val("<?= $Turno ?>");
                        $("#showTurno").show();
                    } else {
                        $("#showTurno").hide();
                    }
                }
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
                    <table aria-hidden="true">
                        <thead>
                            <tr>
                                <td>Dispensario</td>
                                <td>Posicion</td>
                                <td>Producto</td>
                                <td>No.de ventas</td>
                                <td>Litros</td>
                                <td>Importe</td>
                                <td>Descuento</td>
                                <td>Litros C.</td>
                                <td>Importe C.</td>
                                <td>Total</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $nImpAce = $nVts = $nImp = $nLts = 0;
                            foreach ($registros as $rg) {
                                ?>
                                <tr>
                                    <td><?= $rg["dispensario"] ?></td>
                                    <td><?= $rg["posicion"] ?></td>
                                    <td><?= $rg["descripcion"] ?></td>
                                    <td class="numero"><?= number_format($rg["ventas"], 0) ?></td>
                                    <td class="numero"><?= number_format($rg["volumen"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["pesos"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["descuento"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["volumenN"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["pesosN"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["pesos"] - $rg["descuento"], 2) ?></td>
                                </tr>
                                <?php
                                $nVts += $rg["ventas"];
                                $nImp += $rg["pesos"];
                                $nLts += $rg["volumen"];
                                $nImpN += $rg["pesosN"];
                                $nLtsN += $rg["volumenN"];
                                $nDesc += $rg["descuento"];
                            }
                            ?>

                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2"></td>
                                <td>Total</td>
                                <td><?= number_format($nVts, 0) ?></td>
                                <td><?= number_format($nLts, 3) ?></td>
                                <td class="moneda"><?= number_format($nImp, 2) ?></td>
                                <td><?= $nDesc ?></td>
                                <td><?= number_format($nLtsN, 3) ?></td>
                                <td class="moneda"><?= number_format($nImpN, 2) ?></td>
                                <td class="moneda"><?= number_format($nImp, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <div id="TablaDatosReporte">
            <div style="width: 60%;padding-top: 10px;min-height: 200px;margin-left: auto;margin-right: auto;">
                <div><h3>G E N E R A L</h3></div>
                    <table aria-hidden="true">
                        <thead>
                            <tr class="tableexport-ignore">
                                <td>Producto</td>
                                <td>No.Ventas</td>
                                <td>Litros</td>
                                <td>Importe</td>
                                <td>Descuento</td>
                                <td>Litros C.</td>
                                <td>Importe C.</td>
                                <td>Total</td>

                            </tr>
                        </thead>
                            <tbody>
                                <?php
                                    foreach ($registrosT as $rg) {
                                ?>
                                    <tr class="tableexport-ignore">                 
                                        <td><?= $rg["descripcion"] ?></td>
                                        <td><?= $rg["ventas"] ?></td>
                                        <td class="numero"><?= number_format($rg["volumen"], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["pesos"], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["descuento"], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["VolumenN"], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["pesosN"], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["pesos"] + $rg["pesosN"], 2) ?></td>
                                    </tr>
                                <?php

                                    $Volumnen += $rg["volumen"];
                                    $Pesos += $rg["pesos"];
                                    $Descuento += $rg["descuento"];

                                    $VolumneN += $rg["volumenN"];
                                    $PesosN += $rg["pesosN"];
                                    $Total += $rg["pesos"] + $rg["pesosN"];




                                }
                                ?>
                            </tbody>
                                    <tfoot>
                                        <tr class="tableexport-ignore">
                                            <td colspan="2">Gran Total</td>
                                            <td><?= number_format($Volumnen, 2) ?></td>
                                            <td><?= number_format($Pesos, 2) ?></td>
                                            <td><?= number_format($Descuento, 2) ?></td>
                                            <td><?= number_format($VolumneN , 2) ?></td>
                                            <td><?= number_format($PesosN, 2) ?></td>
                                            <td><?= number_format($Total, 2) ?></td>

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
                            <td id="showTurno">
                                <table style="width: 100%" aria-hidden="true">
                                    <tr>
                                        <td>Por Turno:</td>
                                        <td style="text-align: left;">
                                            <select id="Turno" name="Turno">
                                                <option value="No">No</option>
                                                <option value="Si">Si</option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <span><input type="submit" name="Boton" value="Enviar"></span>
                                <?php
                                if ($usuarioSesion->getTeam() !== "Operador") {
                                    ?>
                                                                                                                                                                                                                                                                                                                            <!--<span class="ButtonExcel"><a href="report_excel_reports.php?<?= http_build_query($data) ?>"><i class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true"></i></a></span>-->
                                    <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                                    <span><button name="Reporte"><a href="reptransac.php"><i class="icon fa fa-address-card" aria-hidden="true"></i></a></button></span>
                                    <span><button onclick="ExportToExcel('xlsx')"><i class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true">v2</i></button></span>
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

<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$Titulo = "Venta de traslados  del $FechaI al $FechaF";

$registros = utils\IConnection::getRowsFromQuery($SelectTraslados);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports.php'; ?> 
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
                $("#Corte").attr("size", "10");
                $("#SCliente").activeComboBox(
                        $("[name='form1']"),
                        "SELECT data, value FROM (SELECT id as data, CONCAT(id, ' | ', tipodepago, ' | ', nombre) value FROM cli " +
                        "WHERE TRUE AND cli.tipodepago NOT REGEXP 'Puntos') sub WHERE TRUE",
                        "value"
                        );
                $('#SCliente').focus();
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
                            <td>Id</td>
                            <td>Folio</td>
                            <td>Cliente</td>
                            <td>Tipo</td>
                            <td>UUID</td>
                            <td>Fecha</td>
                            <td>Importe</td>
                            <td>Iva</td>
                            <td>Retencion</td>
                            <td>Total</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $nCnt = $nImp = $nPrecio = $nIva = $nRetencion = 0;
                        foreach ($registros as $rg) {
                            ?>
                            <tr>
                                <td><?= $rg["id"] ?></td>
                                <td><?= $rg["folio"] ?></td>
                                <td><?= ucwords(strtolower($rg["nombre"])) ?></td>
                                <td><?= $rg["tipodepago"] ?></td>
                                <td><?= $rg["uuid"] ?></td>
                                <td><?= $rg["fecha"] ?></td>
                                <td class="numero"><?= number_format($rg["precio"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["importeIva"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["retencion"], 2) ?></td>
                                <td class="numero">$<?= number_format($rg["importe"], 2) ?></td>
                            </tr>
                            <?php
                            $nPrecio += $rg["precio"];
                            $nIva += $rg["importeIva"];
                            $nRetencion += $rg["retencion"];
                            $nImp += $rg["importe"];
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6">Total</td>
                            <td>$<?= number_format($nPrecio, 2) ?></td>
                            <td>$<?= number_format($nIva, 2) ?></td>
                            <td>$<?= number_format($nRetencion, 2) ?></td>
                            <td>$<?= number_format($nImp, 2) ?></td>
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
                            <td align="left">
                                Cliente:
                                <div style="position: relative;">
                                    <input style="width: 100%;" type="search" id="SCliente" name="ClienteS" <?= $busca == 2 ? "required" : "" ?>>
                                </div>
                                <div id="autocomplete-suggestions"></div>
                            </td>
                            <td>
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
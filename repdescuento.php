<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$Titulo = "Reporte de Descuento por periodo de $FechaHI a $FechaHF ";
$registros = utils\IConnection::getRowsFromQuery($selectDescuento);

?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <script type="text/javascript" src="https://unpkg.com/xlsx@0.15.1/dist/xlsx.full.min.js"></script>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#FechaHI").val("<?= $FechaHI ?>").attr("size", "18");
                $("#FechaHF").val("<?= $FechaHF ?>").attr("size", "18");
                /*$("#cFechaI").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaHI")[0], "yyyy-mm-dd HH:mm:ss", $(this)[0]);
                });
                $("#cFechaF").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaHF")[0], "yyyy-mm-dd HH:mm:ss", $(this)[0]);
                });
                */
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

            <div id="container">
                <?php nuevoEncabezado($Titulo); ?>

                <div id="tbl_exporttable_to_xls">
                    <div id="Reportes">
                        <table aria-hidden="true">
                            <thead>
                                <tr class="titulo">
                                    <td>Folio</td>
                                    <td>Corte</td>
                                    <td>Posicion</td>
                                    <td>Producto</td>
                                    <td>Fin Venta</td>
                                    <td>Producto</td>
                                    <td>Volumen</td>
                                    <td>Importe</td>
                                    <td>Descuento</td>
                                    <td>Nombre</td>
                                    <td>Codigo</td>
                                    <td>Despachador</td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($registros as $rg) {
                                    
                                        
                                            ?>
                                            <tr>
                                                <td><?= $rg["folio"] ?></td>
                                                <td><?= $rg["corte"] ?></td>
                                                <td><?= $rg["posicion"] ?></td>
                                                <td><?= $rg["descripcion"] ?></td>
                                                <td><?= $rg["fin_venta"] ?></td>
                                                <td><?= $rg["descripcion"] ?></td>
                                                <td><?= number_format($rg["volumen"], 2) ?></td>
                                                <td><?= number_format($rg["importe"], 2) ?></td>
                                                <td><?= number_format($rg["descuento"], 2) ?></td>
                                                <td><?= $rg["nombre"] ?></td>
                                                <td><?= $rg["codigo"] ?></td>
                                                <td><?= $rg["despachador"] ?></td>
                                            </tr>
                                            <?php
                                    $volumen += $rg["volumen"];
                                    $importe += $rg["importe"];
                                    $descuento += $rg["descuento"];
                                }
                                ?>
                            </tbody>

                            <tfoot>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td>Total</td>
                                    <td><?= number_format($volumen, 2) ?></td>
                                    <td><?= number_format($importe, 2) ?></td>
                                    <td><?= number_format($descuento, 2) ?></td>
                                    <td></td>
                                    <td></td>
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
                                        <td><input type="text" id="FechaHI" name="FechaHI"></td>
                                        <td class="calendario"><i id="cFechaI" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                                    </tr>
                                    <tr>
                                        <td>F.final:</td>
                                        <td><input type="text" id="FechaHF" name="FechaHF"></td>
                                        <td class="calendario"><i id="cFechaF" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <span><input type="submit" name="Boton" value="Enviar"></span>
                                <span><button onclick="ExportToExcel('xlsx')"><i class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true">v2</i></button></span>
                                <?php
                                if ($usuarioSesion->getTeam() !== "Operador") {
                                    ?>
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


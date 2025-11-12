<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$Titulo = "Reporte de Ventas por periodo de $FechaI a $FechaF ";

$selectVtaYena = "SELECT rm.id noTicket, rm.fin_venta fechaVenta, rm.importe , rm.posicion , SESSION  sessionID, auth codigoAutorizacion,
	account noTargeta, rm.monto, cuser Cliente,rm.previousMoney saldoInicial,rm.currentMoney saldoFinal
FROM (
	SELECT 
		rm.id, rm.posicion, rm.manguera, rm.pesos, rm.volumen, rm.importe , rm.fin_venta ,rm.fecha_venta,
		JSON_UNQUOTE( JSON_EXTRACT( rt.transaccion, '$.SessionID' ) ) session,
	    JSON_UNQUOTE( JSON_EXTRACT( rt.transaccion, '$.AuthCode' ) ) auth,
	    JSON_UNQUOTE( JSON_EXTRACT( rt.transaccion, '$.CardNumber' ) ) account,
	    JSON_UNQUOTE( JSON_EXTRACT( rt.transaccion, '$.PreviousMoney' ) ) previousMoney,
	    JSON_UNQUOTE( JSON_EXTRACT( rt.transaccion, '$.CurrentMoney' ) ) currentMoney,
	    rt.monto
	FROM rm left JOIN rm_transacciones rt ON rm.id = rt.id JOIN cli ON cli.id = rm.cliente 
	WHERE cli.nombre REGEXP 'YENA'
    AND fecha_venta BETWEEN " . str_replace("-", "", $FechaI) . " and " . str_replace("-", "", $FechaF) . "
	ORDER BY rm.id DESC
) rm  
LEFT JOIN (
	SELECT
			JSON_UNQUOTE( JSON_EXTRACT( br.response, '$.CustomerName' ) ) cuser, 
			JSON_UNQUOTE( JSON_EXTRACT(bi.response, '$.SessionID' ) ) session, bi.account
	FROM bitacora_integraciones bi 
	JOIN bitacora_integraciones br 
			ON br.idintegracion = bi.idintegracion 
			AND br.account = bi.account 
			AND ABS( TIME_TO_SEC( TIMEDIFF( bi.fecha, br.fecha ) ) ) <= 2 
			AND br.accion LIKE 'REQUEST'
	WHERE bi.idintegracion = 'YENA' AND bi.accion LIKE 'ORDER CAPTURE'
) btr USING( session, account ) 
ORDER BY rm.fecha_venta";

$registros = utils\IConnection::getRowsFromQuery($selectVtaYena);

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
                                    <td>FechaVenta</td>
                                    <td>Importe</td>
                                    <td>Posicion</td>
                                    <td>SessionID</td>
                                    <td>CodigoAutorizacion</td>
                                    <td>No. Targeta</td>
                                    <td>Monto</td>
                                    <td>Cliente</td>
                                    <td>Saldo Inicial</td>
                                    <td>Saldo Final</td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($registros as $rg) {
                                    
                                        
                                            ?>
                                            <tr>
                                                <td><?= $rg["noTicket"] ?></td>
                                                <td><?= $rg["fechaVenta"] ?></td>
                                                <td><?= number_format($rg["importe"], 2) ?></td>
                                                <td><?= $rg["posicion"] ?></td>
                                                <td><?= $rg["sessionID"] ?></td>
                                                <td><?= $rg["codigoAutorizacion"] ?></td>
                                                <td><?= $rg["noTargeta"] ?></td>
                                                <td><?= number_format($rg["monto"], 2) ?></td>
                                                <td><?= $rg["Cliente"] ?></td>
                                                <td><?= number_format($rg["saldoInicial"], 2) ?></td>
                                                <td><?= number_format($rg["saldoFinal"], 2) ?></td>
                                            </tr>
                                            <?php
                                    $monto += $rg["monto"];
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
                                    <td></td>
                                    <td>Total</td>
                                    <td><?= number_format($monto, 2) ?></td>
                                    <td></td>
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

<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$Titulo = "Pagos Realizados del $FechaI al $FechaF";

if ($Desglose == '*'){
    $selectNotas = "
    select  n.id,n.serie serieN,n.uuid uuidnota,n.fecha fechaN,n.cliente ,cli.nombre,0 ticket,n.cantidad,n.importe,n.iva,n.ieps,n.total,
    case WHEN n.status = 0 THEN 
    'Abierto'
    WHEN n.status = 1 THEN     
    'Timbrado' 
    WHEN n.status = 2 THEN     
    'Cancelado' 
    END status,
	fc.serie serieF,fc.folio folioF,fc.uuid uuidfac,fc.fecha fechafact,fc.total totalF
	from nc n 
    inner join cli on n.cliente = cli.id
    inner join fc on n.relacioncfdi = fc.id
    where date(n.fecha) between date(' $FechaI') and date('$FechaF')  
    ";

}else {
    $selectNotas ="
    select  n.id,n.serie serieN,n.uuid uuidnota,n.fecha fechaN,n.cliente ,cli.nombre,ncd.id_ticket ticket,
    ncd.cantidad,
    ROUND(((ncd.importe-(ncd.ieps*ncd.cantidad))/(1.16)),2) importe,
    ROUND(((ncd.importe-(ncd.ieps*ncd.cantidad))/(1.16))*(ncd.iva),2) iva,ROUND(ncd.ieps*ncd.cantidad,2) ieps,
    ROUND(ncd.importe, 2) total,
    case WHEN n.status = 0 THEN 'Abierto'
    WHEN n.status = 1 THEN 'Timbrado' 
    WHEN n.status = 2 THEN 'Cancelado' 
    END status,
	fc.serie serieF,fc.folio folioF,fc.uuid uuidfac,fc.fecha fechafact,fc.total totalF
	from nc n 
    inner join ncd on ncd.id = n.id
    inner join cli on n.cliente = cli.id
    inner join fc on n.relacioncfdi = fc.id
     where date(n.fecha) between date(' $FechaI') and date('$FechaF')";
}


$registros = utils\IConnection::getRowsFromQuery($selectNotas);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css" type="text/css">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css" type="text/css">
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
                $('#RpAceites').DataTable({
                    dom: 'Bfrtip',
                    paging: false,
                    buttons: [
                        'copyHtml5',
                        'excelHtml5',
                        'csvHtml5',
                        'pdfHtml5'
                    ]
                });
            });
        </script>
    </head>

    <body>

        <div id="container">
            <?php nuevoEncabezado($Titulo); ?>

            <div id="Reportes">
                    <table id="RpAceites" aria-hidden="true" class="display" style="width: 100%;">
                        <thead>
                            <tr class = "titulo">
                                <td colspan="13" style="text-align: center;">Notas de Credito</td>
                            </tr>
                            <tr>
                                <td>No.nota </td>
                                <td>Serie </td>
                                <td>UUID Nota</td>
                                <td>Fecha Nota</td>
                                <td>Cliente </td>
                                <td>Nombre </td>
                                <td>Ticket </td>
                                <td>Cantidad </td>
                                <td>Importe </td>
                                <td>Iva </td>
                                <td>Ieps </td>
                                <td>Total </td>
                                <td>Status </td>
                                <td>Serie Factura </td>
                                <td>Folio Factura </td>
                                <td>UUID Factura </td>
                                <td>Fecha Factura </td>
                                <td>Total Factura </td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($registros as $rg) {
                                echo "<tr>";
                                echo "<td>" . $rg["id"] . "</td>";
                                echo "<td>" . $rg["serieN"] . "</td>";
                                echo "<td>" . $rg["uuidnota"] . "</td>";
                                echo "<td>" . $rg["fechaN"] . "</td>";
                                echo "<td>" . $rg["cliente"] . "</td>";
                                echo "<td>" . $rg["nombre"] . "</td>";
                                echo "<td>" . $rg["ticket"] . "</td>";
                                echo "<td>" . $rg["cantidad"] . "</td>";
                                echo "<td>" . $rg["importe"] . "</td>";
                                echo "<td>" . $rg["iva"] . "</td>";
                                echo "<td>" . $rg["ieps"] . "</td>";
                                echo "<td>" . $rg["total"] . "</td>";
                                echo "<td>" . $rg["status"] . "</td>";
                                echo "<td>" . $rg["serieF"] . "</td>";
                                echo "<td>" . $rg["folioF"] . "</td>";
                                echo "<td>" . $rg["uuidfac"] . "</td>";
                                echo "<td>" . $rg["fechafact"] . "</td>";
                                echo "<td>" . $rg["totalF"] . "</td>";
                                
                            }
                            ?>
                        </tbody>
                    </table>
            </div> 
        </div>
    </body>
</html>


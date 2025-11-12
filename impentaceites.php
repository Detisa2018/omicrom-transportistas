<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("importeletras.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$usuarioSesion = getSessionUsuario();

if ($request->hasAttribute("busca")) {
    utils\HTTPUtils::setSessionValue("buscaCompra", $request->getAttribute("busca"));
}

$busca = utils\HTTPUtils::getSessionValue("buscaCompra");

$sqlHe = "SELECT et.fecha,et.concepto,et.cantidad,et.iva,et.proveedor,prv.alias,
        et.status,et.importe,et.importesin,et.documento            
        FROM et LEFT JOIN prv ON et.proveedor=prv.id 
        WHERE et.id='$busca'";

$He = $mysqli->query($sqlHe)->fetch_array();

$selectVales = "
        SELECT etd.producto,inv.descripcion,etd.cantidad,etd.costo,
        (etd.descuento * 100) descuento,
        (etd.adicional * 100) adicional,
        (etd.cantidad * etd.costo) importe,
        (etd.costo * (1 - etd.descuento) * (1 - etd.adicional)) c_real,
        etd.cantidad * etd.costo * (1 - etd.descuento) * (1 - etd.adicional) importe_r,
        etd.idnvo 
        FROM etd LEFT JOIN inv ON etd.producto=inv.id 
        WHERE etd.id='$busca' AND etd.cantidad > 0";
$result = $mysqli->query($selectVales);

$Titulo = "Entrada de aceites No:[$busca]";
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports.php'; ?> 
        <title><?= $Gcia ?></title>
        <script type="text/javascript">
            $(document).ready(function () {

            });
        </script>

    </head>
    <body>
        <div class="sheet padding-10mm">
            <?php nuevoEncabezado($Titulo); ?>
            <table aria-hidden="true" class="HeaderBasic">
                <thead>
                    <tr>
                        <td><strong>Id:</strong>  <?= $busca ?></td>
                        <td><strong>No.entrada:</strong> <?= $busca ?></td>
                        <td><strong>Fecha:</strong> <?= $He["fecha"] ?></td>
                        <td><strong>Docto:</strong> <?= $He["documento"] ?></td>
                    </tr>
                    <tr>
                        <td><strong>Proveedor:</strong> <?= $He["alias"] ?></td>
                        <td colspan="2"><strong>Concepto:</strong> <?= $He["concepto"] ?></td>
                        <td><strong>Total:</strong> <?= number_format($He["importe"] + $He["iva"], 2) ?> </td>
                    </tr>
                </thead>                                     
            </table>
            <div id="tbl_exporttable_to_xls">
                <div id="container">
                    <div id="Reportes" style="min-height: 200px;"> 
                        <table aria-hidden="true">
                            <thead>
                                <tr>
                                    <td colspan="11" style="font-size: 18px;">Compras por factura</td>
                                </tr>
                                <tr>
                                    <td>Clave</td>
                                    <td>Descripcion</td>
                                    <td>Cantidad</td>
                                    <td>Costo</td>
                                    <td>Descuento</td>
                                    <td>Costo real</td>
                                    <td>Importe</td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while ($rg = $result->fetch_array()) {
                                    ?>
                                    <tr>
                                        <td><?= $rg["producto"] ?></td>
                                        <td><?= $rg["descripcion"] ?></td>
                                        <td class="numero"><?= number_format($rg["cantidad"]) ?></td>
                                        <td class="numero"><?= number_format($rg["costo"], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["descuento"], 2) . ($rg["adicional"] > 0 ? " + " . number_format($rg["adicional"], 2) : "" ) ?></td>
                                        <td class="numero"><?= number_format($rg[c_real], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                                    </tr>
                                    <?php
                                    $nCnt += $rg["cantidad"];
                                    $nDes += ($rg["importe"] - $rg[importe_r]);
                                    $nImp += $rg["importe"];
                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td></td>
                                    <td>Descuento: <?= number_format($nDes, 2) ?></td>
                                    <td><?= number_format($nCnt) ?></td>
                                    <td></td>
                                    <td colspan="2">Subtotal</td>
                                    <td><?= number_format($He["importesin"], 2) ?></td>
                                </tr>
                                <tr>
                                    <td colspan="5"></td>
                                    <td style="border-bottom: solid 2px gray;">Iva</td>
                                    <td  style="border-bottom: solid 2px gray;"><?= number_format($He["iva"], 2) ?></td>
                                </tr>
                                <tr>
                                    <td colspan="5"></td>
                                    <td style="border-bottom: solid 2px gray;">Total</td>
                                    <td style="border-bottom: solid 2px gray;"><?= number_format($He["importesin"] + $He["iva"], 2) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <form name="formActions" method="post" action="" id="form" class="oculto">
            <div id="Controles">
                <table aria-hidden="true">
                    <tr style="height: 40px;">
                        <td style="text-align: right;margin-right: 25px;padding: 5px;">
                            <?php
                            if ($usuarioSesion->getTeam() !== "Operador") {
                                ?>                                                                                                                                                                       <!--<span class="ButtonExcel"><a href="report_excel_reports.php?<?= http_build_query($data) ?>"><i class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true"></i></a></span>-->
                                <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i><br>Imprimir</button></span>
                                        <?php
                                    }
                                    ?>
                        </td>
                    </tr>
                </table>
            </div>
        </form>
    </body>
</html>     


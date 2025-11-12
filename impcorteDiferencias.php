<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("data/CtDAO.php");

use com\softcoatl\utils as utils;

//require './services/ReportesVentasService.php';

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$usuarioSesion = getSessionUsuario();

if ($request->hasAttribute("busca")) {
    header("location: impcorte.php?Corte=" . $request->getAttribute("busca"));
}

$ctDAO = new CtDAO();
$ctVO = $ctDAO->retrieve($Corte);
$Isla = $ctVO->getIsla();

$Sql1 = "SELECT corte,
                   posicion,
                   producto,
                   fecha_venta,
                   precio,
                   SUM(volumen) VolumenDeDispensario,
                   MIN(totalizadorvi) T_I ,MAX(totalizadorvf) T_F,
                   SUM(ROUND(totalizadorvf - totalizadorvi,3)) Total_Venta_Totalizadores,
                   ROUND(SUM(volumen) - SUM(ROUND(totalizadorvf - totalizadorvi,3)),3) Diferencia,
                   ROUND(((ROUND(SUM(volumen) - SUM(ROUND(totalizadorvf - totalizadorvi,3)),3) *100)/ SUM(volumen)),2) porcentajeError,
                   ROUND(SUM(pesos),2) pesos,ROUND(SUM( precio * ROUND(totalizadorvf - totalizadorvi,3)),2) pesosTotalizadores,
                   ROUND(SUM(pesos)-SUM(precio* ROUND(totalizadorvf - totalizadorvi,3)  ),2) DiferenciaImporte
               FROM
                   rm
                   WHERE corte = " . $request->getAttribute("Corte") . "
                   group by posicion,producto;";
$Rs1 = utils\IConnection::getRowsFromQuery($Sql1);
//$Sql2 = "SELECT corte,
//                   posicion,
//                   producto,
//                   fecha_venta,
//                   precio,
//                   SUM(volumen) VolumenDeDispensario,
//                   MIN(totalizadorvi) T_I ,MAX(totalizadorvf) T_F,
//                   SUM(ROUND(totalizadorvf - totalizadorvi,4)) Total_Venta_Totalizadores,
//                   ROUND(volumen - ROUND(totalizadorvf - totalizadorvi,4),4) Diferencia,
//                   ROUND(((ROUND(volumen - ROUND(totalizadorvf - totalizadorvi,4),4) *100)/ volumen),2) porcentajeError,
//                   ROUND(pesos,2) pesos,ROUND(precio * ROUND(totalizadorvf - totalizadorvi,4),2) pesosTotalizadores,
//                   ROUND(pesos-precio* ROUND(totalizadorvf - totalizadorvi,4)  ),2) DiferenciaImporte
//               FROM
//                   rm
//                   WHERE corte = " . $request->getAttribute("Corte") . "
//                   group by id;";
//$Rs2 = utils\IConnection::getRowsFromQuery($Sql2);

$Titulo = " Corte de turno: Id[$Corte]  Fecha: " . $ctVO->getFecha() . " Isla: " . $Isla . " Turno: " . $ctVO->getTurno();
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#Corte").val("<?= $Corte ?>");
            });
        </script>
    </head>

    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo); ?>
            <div id="Reportes" style="min-height: 200px;">
                <div id="Reportes">
                    <table aria-hidden="true">
                        <thead>
                            <tr>
                                <td colspan="100%">
                                    <h3> Análisis por manguera </h3>
                                </td>
                            </tr>
                            <tr>
                                <td>Posicion</td>
                                <td>Producto</td>
                                <td>Volumen Dispensario</td>
                                <td>Totalizador Inicial</td>
                                <td>Totalizador Final</td>
                                <td>Volumen Totalizadores</td>
                                <td>Dif. Volumen</td>
                                <td>Importe Dispensario</td>
                                <td>Importe Totalizadores</td>
                                <td>Dif. Importe</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $CountV = 0;
                            foreach ($Rs1 as $rg) {
                                ?>
                                <tr>
                                    <td><?= $rg["posicion"] ?></td>
                                    <td><?= $rg["producto"] ?></td>
                                    <td class="numero"><?= number_format($rg["VolumenDeDispensario"], 2) ?></td>
                                    <td class="numero"><?= $rg["T_I"] ?></td>
                                    <td class="numero"><?= $rg["T_F"] ?></td>
                                    <td class="numero"><?= number_format($rg["Total_Venta_Totalizadores"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["VolumenDeDispensario"] - $rg["Total_Venta_Totalizadores"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["pesos"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["pesosTotalizadores"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["DiferenciaImporte"], 2) ?></td>
                                </tr>
                                <?php
                                $VolumenDispensario += $rg["VolumenDeDispensario"];
                                $TotalVentaTotalizadores += $rg["Total_Venta_Totalizadores"];
                                $DiferenciaVolumen += number_format($rg["VolumenDeDispensario"] - $rg["Total_Venta_Totalizadores"], 2);
                                $ImporteDispensario += $rg["pesos"];
                                $TotalImporteTotalizadores += $rg["pesosTotalizadores"];
                                $DiferenciasImporte += $rg["DiferenciaImporte"];
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2"></td>
                                <td class="numero"><?= number_format($TotalVentaTotalizadores, 2) ?> L.</td>            
                                <td colspan="2"></td>
                                <td class="numero"><?= number_format($TotalVentaTotalizadores, 2) ?> L.</td>
                                <td class="numero"><?= number_format($DiferenciaVolumen, 2) ?> L.</td>
                                <td class="numero">$<?= number_format($ImporteDispensario, 2) ?></td>
                                <td class="numero">$<?= number_format($TotalImporteTotalizadores, 2) ?></td>
                                <td class="numero">$<?= number_format($DiferenciasImporte, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                    <?php
                    $VolumenDispensario = $TotalVentaTotalizadores = $DiferenciaVolumen = $ImporteDispensario = $TotalImporteTotalizadores = $DiferenciasImporte = 0;
                    ?>
<!--                    <table aria-hidden="true">
                        <thead>
                            <tr>
                                <td colspan="100%">
                                    <h3> Análisis por Desácho </h3>
                                </td>
                            </tr>
                            <tr>
                                <td>Posicion</td>
                                <td>Producto</td>
                                <td>Volumen Dispensario</td>
                                <td>Totalizador Inicial</td>
                                <td>Totalizador Final</td>
                                <td>Volumen Totalizadores</td>
                                <td>Dif. Volumen</td>
                                <td>Importe Dispensario</td>
                                <td>Importe Totalizadores</td>
                                <td>Dif. Importe</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $CountV = 0;
                            foreach ($Rs2 as $rg) {
                                ?>
                                <tr>
                                    <td><?= $rg["posicion"] ?></td>
                                    <td><?= $rg["producto"] ?></td>
                                    <td class="numero"><?= number_format($rg["VolumenDeDispensario"], 3) ?></td>
                                    <td class="numero"><?= $rg["T_I"] ?></td>
                                    <td class="numero"><?= $rg["T_F"] ?></td>
                                    <td class="numero"><?= number_format($rg["Total_Venta_Totalizadores"], 3) ?></td>
                                    <td class="numero"><?= number_format($rg["VolumenDeDispensario"] - $rg["Total_Venta_Totalizadores"], 3) ?></td>
                                    <td class="numero"><?= number_format($rg["pesos"], 3) ?></td>
                                    <td class="numero"><?= number_format($rg["pesosTotalizadores"], 3) ?></td>
                                    <td class="numero"><?= number_format($rg["DiferenciaImporte"], 3) ?></td>
                                </tr>
                                <?php
                                $VolumenDispensario += $rg["VolumenDeDispensario"];
                                $TotalVentaTotalizadores += $rg["Total_Venta_Totalizadores"];
                                $DiferenciaVolumen += number_format($rg["VolumenDeDispensario"] - $rg["Total_Venta_Totalizadores"], 2);
                                $ImporteDispensario += $rg["pesos"];
                                $TotalImporteTotalizadores += $rg["pesosTotalizadores"];
                                $DiferenciasImporte += $rg["DiferenciaImporte"];
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2"></td>
                                <td class="numero"><?= number_format($TotalVentaTotalizadores, 2) ?> L.</td>            
                                <td colspan="2"></td>
                                <td class="numero"><?= number_format($TotalVentaTotalizadores, 2) ?> L.</td>
                                <td class="numero"><?= number_format($DiferenciaVolumen, 2) ?> L.</td>
                                <td class="numero">$<?= number_format($ImporteDispensario, 2) ?></td>
                                <td class="numero">$<?= number_format($TotalImporteTotalizadores, 2) ?></td>
                                <td class="numero">$<?= number_format($DiferenciasImporte, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>-->
                </div>
            </div>
        </div>
        <div id="footer">
            <form name="formActions" method="post" action="" id="form" class="oculto">
                <div id="Controles">
                    <table aria-hidden="true">
                        <tr style="height: 40px;">
                            <td>
                                <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                            </td>

                        </tr>
                    </table>
                </div>
                <input type="hidden" name="Corte" id="Corte">
            </form>
            <?php topePagina(); ?>
        </div>
    </body>
</html>


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

$busca = $request->getAttribute("busca");
$ciaDAO = new CiaDAO();
$ciaVO = $ciaDAO->retrieve(1);

if ($request->hasAttribute("Fecha")) {
    $FechaHra = $request->getAttribute("Fecha") . " " . $request->getAttribute("Hora") . ":00";
    $sql = "SELECT tanques_h.tanque, tanques_h.producto, tanques_h.volumen_actual, tanques_h.fecha_hora_s, 
                tanques.capacidad_total, tanques_h.temperatura, tanques_h.agua, tanques.volumen_operativo,
                tanques.volumen_fondaje,tanques.clave_producto,tanques_h.altura,tanques_h.volumen_compensado
                FROM tanques, tanques_h
                WHERE tanques_h.fecha_hora_s <= '$FechaHra'           
                AND tanques_h.tanque = '" . $request->getAttribute("Producto") . "'
                AND tanques_h.tanque = tanques.tanque
                ORDER BY tanques_h.id DESC LIMIT 10";
} else {
    $sql = "SELECT * FROM tanques WHERE tanque = '" . $request->getAttribute("Producto") . "'  LIMIT 10";
}
$Cpo = utils\IConnection::execSql($sql);

$months = array();
setlocale(LC_TIME, 'es_MX.UTF-8');
for ($m = 1; $m <= 12; $m++) {
    $months[cZeros($m, 2, "LEFT")] = strftime("%B", mktime(0, 0, 0, $m, 12));
}
$cFecha = $ciaVO->getColonia() . " " . $ciaVO->getCiudad() . " a " . date("d") . " de " . $months[date("m")] . " de " . date("Y");

$Titulo = "Reporte de tanques";
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports_print.php'; ?> 
        <title><?= $Gcia ?></title>
        <style>
            @page { 
                size: A4 /*landscape*/; 
            }
        </style>
        <script type="text/javascript">
            $(document).ready(function () {

            });
        </script>

    </head>

    <!-- Set "A5", "A4" or "A3" for class name -->
    <!-- Set also "landscape" if you need -->
    <body class="A4">
        <div class="iconos">
             <table aria-hidden="true">
                <tr>
                    <td style="text-align: left"><?= $Titulo ?></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="text-align: center"><i onclick="print();" title="Imprimir" class='icon fa fa-lg fa-print' aria-hidden="true"></i></td>
                </tr>
            </table>
        </div>
        <!-- Each sheet element should have the class "sheet" -->
        <!-- "padding-**mm" is optional: you can set 10, 15, 20 or 25 -->
        <div class="sheet padding-10mm">

            <div id="acuse">
                 <table aria-hidden="true">
                    <thead>
                        <tr><td  style="font-size: 15px;"><?= $cFecha ?></tr>
                    </thead>
                </table>

                <table aria-hidden="true">
                    <tbody>
                        <tr><td colspan="2" style="text-align: center;font-size: 25px;">No.estacion:<?= $ciaVO->getNumestacion() ?></td></tr>
                        <tr><td colspan="2" style="text-align: center;font-size: 23px;"><?= $ciaVO->getCia() ?></td></tr>
                        <tr><td colspan="2" style="text-align: center;font-size: 25px;"><?= $ciaVO->getRfc() ?></td></tr>
                        <tr><td colspan="2" style="text-align: center;font-size: 25px;"><?= $ciaVO->getDireccion() ?>&nbsp;<?= $ciaVO->getNumeroext() ?></td></tr>
                        <tr><td colspan="2" style="text-align: center;font-size: 25px;"><?= $ciaVO->getCiudad() ?>&nbsp;<?= $ciaVO->getEstado() ?></td></tr>
                        <tr><td colspan="2" style="text-align: center;font-size: 25px;">Fecha de impresion: &nbsp;<?= date("Y-m-d H:i:s") ?></td></tr>
                        <tr><td colspan="2" style="text-align: center;font-size: 25px;">Fecha de lectura del tanque:&nbsp; <?= $Cpo[fecha_hora_s] ?></td></tr>
                    </tbody>
                </table>

                 <table aria-hidden="true">
                    <tbody>
                        <tr><td style="font-size: 25px;">No.tanque: </td><td style="font-size: 25px;"><?= $Cpo["tanque"] ?></td></tr>
                        <tr><td style="font-size: 25px;">Descripcion: </td><td style="font-size: 25px;"><?= $Cpo["producto"] ?></td></tr>
                        <tr><td style="font-size: 25px;">Clave del producto: </td><td style="font-size: 25px;"><?= $Cpo[clave_producto] ?></td></tr>
                        <tr><td style="font-size: 25px;">Existencia: </td><td style="font-size: 25px;"><?= number_format($Cpo[volumen_actual], 0) ?>Lts.</td></tr>
                        <tr><td style="font-size: 25px;">Volumen Compensado: </td><td style="font-size: 25px;"><?= number_format($Cpo[volumen_compensado], 0) ?>Lts.</td></tr>
                        <tr><td style="font-size: 25px;">Por llenar: </td><td style="font-size: 25px;"><?= number_format($Cpo[volumen_operativo] - $Cpo[volumen_actual], 0) ?>Lts.</td></tr>
                        <tr><td style="font-size: 25px;">Volumen operativo: </td><td style="font-size: 25px;"><?= number_format($Cpo[volumen_operativo], 0) ?>Lts.</td></tr>
                        <tr><td style="font-size: 25px;">Volumen fondaje: </td><td style="font-size: 25px;"><?= number_format($Cpo[volumen_fondaje], 0) ?>Lts.</td></tr>
                        <tr><td style="font-size: 25px;">Temperatura: </td><td style="font-size: 25px;"><?= number_format($Cpo["temperatura"], 2) ?> °C</td></tr>
                        <tr><td style="font-size: 25px;">Altura: </td><td style="font-size: 25px;"><?= number_format($Cpo["altura"], 0) ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </body>
</html>     
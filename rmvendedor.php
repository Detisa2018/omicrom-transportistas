<?php
#Librerias
session_start();

include_once ("check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$mysqli = iconnect();

if ($request->hasAttribute("criteria")) {
    utils\HTTPUtils::setSessionValue("Busca", $request->getAttribute("busca"));
    utils\HTTPUtils::setSessionValue("Corte", $request->getAttribute("Corte"));
    utils\HTTPUtils::setSessionValue("FechaI", "");
    utils\HTTPUtils::setSessionValue("FechaF", "");
}

if ($request->getAttribute("FechaI") !== "" && $request->getAttribute("FechaF") !== "") {
    utils\HTTPUtils::setSessionValue("FechaI", $request->getAttribute("FechaI"));
    utils\HTTPUtils::setSessionValue("FechaF", $request->getAttribute("FechaF"));
    utils\HTTPUtils::setSessionValue("FechaIFormat", $request->getAttribute("FechaI"));
    utils\HTTPUtils::setSessionValue("FechaFFormat", $request->getAttribute("FechaF"));
}
$busca = utils\HTTPUtils::getSessionValue("Busca");
$Corte = utils\HTTPUtils::getSessionValue("Corte");
$Titulo = "Ventas de corte: $Corte Vendedor: $busca";
if (utils\HTTPUtils::getSessionValue("FechaI") !== "" && utils\HTTPUtils::getSessionValue("FechaF") !== "") {

    $Fecha_Inicial = date('Ymd', strtotime($request->getAttribute("FechaI")));
    $Fecha_Final = date('Ymd', strtotime($request->getAttribute("FechaF")));
    utils\HTTPUtils::setSessionValue("FechaI", $Fecha_Inicial);
    utils\HTTPUtils::setSessionValue("FechaF", $Fecha_Final);
    $Titulo = "Fecha Inicial " . $request->getAttribute("FechaI") . " Fecha Final " . $request->getAttribute("FechaF");
    $AddSql = "AND rm.fecha_venta BETWEEN $Fecha_Inicial AND $Fecha_Final ";
} else {
    $AddSql = "AND rm.corte = '$Corte' ";
}
$Fecha_Inicial = utils\HTTPUtils::getSessionValue("FechaI");
$Fecha_Final = utils\HTTPUtils::getSessionValue("FechaF");

$cSql = "SELECT rm.corte,rm.id,rm.fin_venta fecha,rm.pesos,rm.volumen,
            com.descripcion producto, rm.posicion, rm.kilometraje,rm.placas, rm.codigo, 
            IF(unidades.impreso IS NULL,'-----',unidades.impreso) impreso, 
            IF(unidades.descripcion IS NULL,'-----',unidades.descripcion) descripcion
        FROM com,rm 
        LEFT JOIN unidades ON rm.codigo = unidades.codigo AND unidades.cliente > 0
        WHERE com.clavei = rm.producto $AddSql AND rm.vendedor = '$busca' AND tipo_venta != 'J'
        ORDER BY rm.id";
$Vta = utils\IConnection::getRowsFromQuery($cSql);
$cSqlJ = "SELECT rm.corte,rm.id,rm.fin_venta fecha,rm.pesos,rm.volumen,
            com.descripcion producto, rm.posicion, rm.kilometraje,rm.placas, rm.codigo, 
            IF(unidades.impreso IS NULL,'-----',unidades.impreso) impreso, 
            IF(unidades.descripcion IS NULL,'-----',unidades.descripcion) descripcion
        FROM com,rm 
        LEFT JOIN unidades ON rm.codigo = unidades.codigo AND unidades.cliente > 0
        WHERE com.clavei = rm.producto AND  tipo_venta = 'J' $AddSql AND rm.vendedor = '$busca' 
        ORDER BY rm.id";

$jr = utils\IConnection::getRowsFromQuery($cSqlJ);
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports.php'; ?> 
        <title><?= $Gcia ?></title>
    </head>
    <script>
        $(document).ready(function () {
            $("#FechaI").val("<?= utils\HTTPUtils::getSessionValue("FechaIFormat") ?>");
            $("#FechaF").val("<?= utils\HTTPUtils::getSessionValue("FechaFFormat") ?>");
        });
    </script>
    <body>

        <div id='container'>
            <?php nuevoEncabezado($Titulo); ?>

            <div id="TablaDatosReporte">
                <table aria-hidden="true">
                    <tr><td colspan="100%">Consumos</td></tr>
                    <tr>
                        <td class="downTitles"></td>
                        <td class="downTitles">No.ticket</td>
                        <td class="downTitles">Corte</td>
                        <td class="downTitles">No.tarjeta</td>
                        <td class="downTitles">Fecha</td>
                        <td class="downTitles">No.placas</td>
                        <td class="downTitles">Kilometraje</td>
                        <td class="downTitles">Descripcion</td>
                        <td class="downTitles">Producto</td>
                        <td class="downTitles">Litros</td>
                        <td class="downTitles">Importe</td>
                    </tr>
                    <?php
                    $cont = 1;
                    $Imp = $nLtsC = 0;
                    foreach ($Vta as $rg) {
                        ?>

                        <tr>
                            <td align="right"><?= $cont ?></td>
                            <td align="right"><?= $rg["id"] ?></td>
                            <td align="right"><?= $rg["corte"] ?></td>
                            <td align="left"><?= $rg["impreso"] ?></td>
                            <td align="left"><?= $rg["fecha"] ?></td>
                            <td align="left"><?= ucwords(strtoupper($rg["placas"])) ?></td>
                            <td align="right"><?= $rg["kilometraje"] ?></td>
                            <td align="left"><?= ucwords(strtolower($rg["descripcion"])) ?></td>
                            <td align="left"><?= $rg["producto"] ?></td>
                            <td align="right"><?= number_format($rg["volumen"], 2) ?></td>
                            <td align="right"><?= number_format($rg["pesos"], 2) ?></td>
                        </tr>

                        <?php
                        $cont++;
                        $nLtsC += $rg["volumen"];
                        $nImpC += $rg["pesos"];
                    }
                    ?>
                    <tr>
                        <td class="upTitles" colspan="9"></td>
                        <td class="upTitles"><?= number_format($nLtsC, 2) ?></td>
                        <td class="upTitles"><?= number_format($nImpC, 2) ?></td>
                    </tr>
                </table>
                <table aria-hidden="true">
                    <tr><td colspan="100%">Jarreos</td></tr>
                    <tr>
                        <td class="downTitles"></td>
                        <td class="downTitles">No.ticket</td>
                        <td class="downTitles">Corte</td>
                        <td class="downTitles">No.tarjeta</td>
                        <td class="downTitles">Fecha</td>
                        <td class="downTitles">No.placas</td>
                        <td class="downTitles">Kilometraje</td>
                        <td class="downTitles">Descripcion</td>
                        <td class="downTitles">Producto</td>
                        <td class="downTitles">Litros</td>
                        <td class="downTitles">Importe</td>
                    </tr>
                    <?php
                    $cont = 1;
                    $Imp = $nLtsC = $nImpC = 0;
                    foreach ($jr as $rg) {
                        ?>

                        <tr>
                            <td align="right"><?= $cont ?></td>
                            <td align="right"><?= $rg["id"] ?></td>
                            <td align="right"><?= $rg["corte"] ?></td>
                            <td align="left"><?= $rg["impreso"] ?></td>
                            <td align="left"><?= $rg["fecha"] ?></td>
                            <td align="left"><?= ucwords(strtoupper($rg["placas"])) ?></td>
                            <td align="right"><?= $rg["kilometraje"] ?></td>
                            <td align="left"><?= ucwords(strtolower($rg["descripcion"])) ?></td>
                            <td align="left"><?= $rg["producto"] ?></td>
                            <td align="right"><?= number_format($rg["volumen"], 2) ?></td>
                            <td align="right"><?= number_format($rg["pesos"], 2) ?></td>
                        </tr>

                        <?php
                        $cont++;
                        $nLtsC += $rg["volumen"];
                        $nImpC += $rg["pesos"];
                    }
                    ?>
                    <tr>
                        <td class="upTitles" colspan="9"></td>
                        <td class="upTitles"><?= number_format($nLtsC, 2) ?></td>
                        <td class="upTitles"><?= number_format($nImpC, 2) ?></td>
                    </tr>
                </table>
            </div>

            <div id="footer">
                <form name="formActions" method="post" action="" id="form" class="oculto">
                    <div id="Controles">
                        <table aria-hidden="true">
                            <tr style="height: 40px;">
                                <td>
                                    Fecha Inicial <input type="date" name="FechaI" id="FechaI">
                                </td>
                                <td>
                                    Fecha Final <input type="date" name="FechaF" id="FechaF">
                                </td>
                                <td><input type="submit" name="Boton" value="Genera"></td>
                                <td>
                                    <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </form>
                <?php topePagina() ?>
            </div>
    </body>
</html>
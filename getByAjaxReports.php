<?php

header("Cache-Control: no-cache,no-store");
$wsdl = 'http://localhost:9080/DetiPOS/detisa/services/DetiPOS?wsdl';

include_once ("libnvo/lib.php");
include_once ("services/ConsultasReportes.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();

$dt = $request->getAttribute("Var");
$mysqli = iconnect();
$jsonString = Array();
$display = Array();
$jsonString["success"] = false;
$jsonString["message"] = "";
$FechaIni = $request->getAttribute("FechaInicial");
$FechaFin = $request->getAttribute("FechaFinal");
if ($request->getAttribute("Origen") === "GeneraBalance") {
    $selectBalanceCreate = "CALL omicrom.balance_productos('$FechaIni', '$FechaFin');";
    error_log($selectBalanceCreate);
    if ($mysqli->query($selectBalanceCreate)) {
        $Tot = utils\IConnection::execSql("SELECT valor FROM variables_corporativo WHERE llave='OrdenReportes'");
        $FiltroSql = $Tot["valor"] != "" ? "ORDER BY clave ASC" : "";
        $selectBalance = "SELECT b.*,getUMedida(com.cve_producto_sat,com.cve_sub_producto_sat) um "
                . "FROM balance_productos b inner join com on b.clave = com.clave " . $FiltroSql;
        $registros = utils\IConnection::getRowsFromQuery($selectBalance, $mysqli);
        $Html .= '<table aria-hidden="true" style="margin-bottom: 35px;border: 1px solid #434343;border-radius: 5px;width:95%;margin-left:2%;">
            <tbody>';
        $Informacion = 1;
        $nFac = 1000;
        $n = 0;
        foreach ($registros as $rg) {
            if (!empty($clave) && $clave !== $rg["clavei"]) {
                if ($Informacion == TipoInformacion::OMICROM || $Informacion == TipoInformacion::COMPARATIVO) {
                    $Html .= '<tr class="subtotal">';
                    $Html .= '<td>Resumen</td>';
                    $Html .= '<td>' . number_format($InventarioI / $nFac, 3) . '</td>';
                    $Html .= '<td>' . number_format($Cargas / $nFac, 3) . '</td>';
                    $Html .= '<td>' . number_format($Jarreos / $nFac, 3) . '</td>';
                    if ($balance["valor"] == 1 && $Informacion === TipoInformacion::OMICROM) {
                        $Html .= '<td>' . number_format($Bruto / $nFac, 3) . '</td>';
                        $Html .= '<td>' . number_format($Diferencia / $nFac, 3) . '</td>';
                    }
                    if ($Informacion == TipoInformacion::COMPARATIVO) {
                        $Html .= '<td>' . number_format($VentasCV / $nFac, 3) . '</td>';
                    }
                    $Html .= '<td>' . number_format($Ventas / $nFac, 3) . '</td>';
                    if ($incluir) {
                        $Html .= '<td>' . number_format($VtaExtra / $nFac, 3) . '</td>';
                    }
                    if ($Informacion == TipoInformacion::COMPARATIVO) {
                        $Html .= '<td>' . number_format($CargasCV / $nFac, 3) . '</td>';
                    }
                    $Html .= '<td>' . number_format($InvTeorico / $nFac, 3) . '</td>';
                    $Html .= '<td>' . number_format($InventarioF / $nFac, 3) . '</td>';
                    $Html .= '<td>' . number_format(($InventarioF - $InventarioI + $Ventas - $Cargas - $VtaExtra) / $nFac, 3) . '</td>';
                    $Html .= '</tr>';
                    $InvTeorico = $Ventas = $Cargas = $VtaExtra = $InvFinal = $VentasCV = $CargasCV = $Bruto = $Diferencia = $Jarreos = 0;
                } else {
                    $Html .= '<tr class="subtotal">';
                    $Html .= '<td>Resumen</td>';
                    $Html .= '<td>' . number_format(0, 3) . '</td>';
                    $Html .= '<td>' . number_format($Cargas / $nFac, 3) . '</td>';
                    $Html .= '<td>' . number_format(0, 3) . '</td>';
                    $Html .= '<td>' . number_format($Ventas / $nFac, 3) . '</td>';
                    $Html .= '<td>' . number_format(0, 3) . '</td>';
                    $Html .= '<td>' . number_format(0, 3) . '</td>';
                    $Html .= '</tr>';
                    $InvTeorico = $Ventas = $Cargas = $InvFinal = $VentasCV = $CargasCV = 0;
                }
            }
            if (empty($clave) || $clave !== $rg["clavei"]) {
                $HtmlColor = "SELECT color FROM omicrom.com WHERE clave = '" . $rg["clave"] . "'";
                $Cr = utils\IConnection::execSql($HtmlColor);
                $Html .= '<tr class="titulo" >';
                $Html .= '<td colspan="10" style="border-top: 2px solid black;background:' . $Cr . '">' . $rg["clave"] . ' ' . $rg["descripcion"] . ' ' . $rg["um"] . '</td>';
                $Html .= '</tr>';
                $Html .= '<tr class="titulos">';
                $Html .= '<td width="15%">Fecha</td>';
                $Html .= '<td>Inv.inicial</td>';
                $Html .= '<td>Compras</td>';
                $Html .= '<td>Jarreos</td>';
                if ($balance["valor"] == 1 && $Informacion === TipoInformacion::OMICROM) {
                    $Html .= '<td>Bruto</td>';
                    $Html .= '<td>Dif.</td>';
                }
                if ($Informacion == TipoInformacion::COMPARATIVO) {
                    $Html .= '<td>Ventas CV</td>';
                }
                $Html .= '<td>Ventas</td>';
                if ($Informacion == TipoInformacion::COMPARATIVO) {
                    $Html .= '<td>Compras CV</td>';
                }
                $Html .= '<td>Inv.Teorico</td>';
                $Html .= '<td>Inv.Final</td>';
                $Html .= '<td>Diferencia</td>';
                $Html .= '</tr>';
                $InventarioI = $rg["inicial"];
            }

            $clave = $rg["clavei"];

            if ($Informacion == TipoInformacion::OMICROM || $Informacion == TipoInformacion::COMPARATIVO) {

                $FechaLF = "DATE('" . $rg["fecha"] . "') ORDER BY fecha_hora_s  DESC LIMIT " . $rg["limite"] . "";
                if ($rg["fecha"] !== date("Y-m-d")) {
                    $FechaLF = "DATE_ADD('" . $rg["fecha"] . "',INTERVAL 1 DAY) ORDER BY fecha_hora_s  ASC LIMIT " . $rg["limite"] . "";
                }

                $selectLecturaFinal = " 
                                    SELECT SUM(cantidad) cantidad,fecha,fecha_hora_s 
                                    FROM (
                                        SELECT IFNULL(volumen_actual, 0) cantidad,DATE (fecha_hora_s) fecha, fecha_hora_s
                                        FROM tanques_h
                                        WHERE TRUE AND tanque IN (" . $rg["tanques"] . ") AND DATE( fecha_hora_s ) = $FechaLF
                                    ) t ";

                $Ifin = utils\IConnection::execSql($selectLecturaFinal);
                $Iinicial = $rg["inicial"];
                $Ifinal = $Ifin["cantidad"];
                $Compras = $busca === "1" ? $rg["compras"] : $rg["volumen_docto"];

                if ($Informacion == TipoInformacion::COMPARATIVO) {
                    $data = leer_archivo_zip_to_xml($rg["nombrearchivo"], $rg["claveProducto"], $rg["claveSubProducto"]);
                }
                $InvTeorico = $Iinicial - $rg["venta"] + $Compras + ($incluir ? $Rmd["cantidad"] : 0);

                $date1 = new DateTime($rg["fecha"] . " 23:59:59");
                $date2 = new DateTime($Ifin["fecha_hora_s"]);
                $diff = $date1->diff($date2);
                $difereciaFechas = ( ($diff->days * 24 ) * 60 ) + ( $diff->i ) . " minutos";
                $style = "";
                $n++;
                $style = $n % 2 == 0 ? ";background-color:#D5D8DC" : ";background-color:#D7DBDD";
                if ($diff->i > 5) {
                    $style = "background-color: #F7FF7C";
                }
                $Html .= '<tr style="' . $style . '" title="Fin de muestra: ' . $Ifin["fecha_hora_s"] . ' Dif: ' . $difereciaFechas . '">';
                $Html .= '<td>' . $rg["fecha"] . '</td>';
                $Html .= '<td class="numero">' . number_format($Iinicial / $nFac, 3) . '</td>';
                $Html .= '<td class="numero">' . number_format($Compras / $nFac, 3) . '</td>';
                $Html .= '<td class="numero">' . number_format($rg["jarreos"] / $nFac, 3) . '</td>';
                if ($balance["valor"] == 1 && $Informacion === TipoInformacion::OMICROM) {
                    $Html .= '<td class="numero">' . number_format($rg["bruto"] / $nFacc, 3) . '</td>';
                    $Html .= '<td class="numero">' . number_format($rg["diferencia"] / $nFac, 3) . '</td>';
                }
                if ($Informacion == TipoInformacion::COMPARATIVO) {
                    $Html .= '<td class="numero">' . number_format($data["venta"] / $nFac, 3) . '</td>';
                }
                $Html .= '<td class="numero">' . number_format($rg["venta"] / $nFac, 3) . '</td>';
                if ($Informacion == TipoInformacion::COMPARATIVO) {
                    $Html .= '<td class="numero">' . number_format($data["compras"] / $nFac, 3) . '</td>';
                }
                $Html .= '<td class="numero">' . number_format($InvTeorico / $nFac, 3) . '</td>';
                $Html .= '<td class="numero">' . number_format($Ifinal / $nFac, 3) . '</td>';
                $Html .= '<td class="numero">' . number_format(($Ifinal - $InvTeorico) / $nFac, 3) . '</td>';
                $Html .= '</tr>';
                $Ventas += $rg["venta"];
                $Jarreos += $rg["jarreos"];
                $Cargas += $Compras;
                $VtaExtra += ($incluir ? $Rmd["cantidad"] : 0);
                $Bruto += $rg["bruto"];
                $Diferencia += $rg["diferencia"];

                $InvFinal = $Ifinal;
                $InventarioF = $Ifinal;

                $Tot_Bruto += $rg["bruto"];
                $Tot_Dif += $rg["diferencia"];
                $T_Ventas += $rg["venta"];
                $T_Jarreos += $rg["jarreos"];
                $T_Cargas += $Compras;
                $T_VtaExtra += ($incluir ? $Rmd["cantidad"] : 0);

                if ($Informacion == TipoInformacion::COMPARATIVO) {
                    $VentasCV += $data["venta"];
                    $CargasCV += $data["compras"];

                    $T_VentasCV += $data["venta"];
                    $T_CargasCV += $data["compras"];
                }
            } elseif ($Informacion == TipoInformacion::ARCHIVOS) {

                $data = leer_archivo_zip_to_xml($rg["nombrearchivo"], $rg["claveProducto"], $rg["claveSubProducto"]);

                $Iinicial = $data["disponible"] + $data["extraccion"] - $data["compras"];
                $Ifinal = $data["disponible"];
                $Teorico = $Iinicial - $data["venta"] + $data["compras"];
                $Html .= '<tr>';
                $Html .= '<td class="numero">' . $rg["fecha"] . '</td>';
                $Html .= '<td class="numero">' . number_format($Iinicial / $nFac, 3) . '</td>';
                $Html .= '<td class="numero">' . number_format($data["compras"] / $nFac, 3) . '</td>';
                $Html .= '<td class="numero">' . number_format(0, 3) . '</td>';
                $Html .= '<td class="numero">' . number_format($data["venta"] / $nFac, 3) . '</td>';
                $Html .= '<td class="numero">' . number_format($Teorico / $nFac, 3) . '</td>';
                $Html .= '<td class="numero">' . number_format($Ifinal / $nFac, 3) . '</td>';
                $Html .= '<td class="numero">' . number_format(($Ifinal - $Teorico) / $nFac, 3) . '</td>';
                $Html .= '</tr>';
                $InvTeorico = $Iinicial;
                $Ventas += $data["venta"];
                $Cargas += $data["compras"];
                $InvFinal = $Ifinal;

                $T_Ventas += $data["venta"];
                $T_Cargas += $data["compras"];
            }
        }
        $Html .= '<tr class="subtotal">';
        $Html .= '<td>Resumen</td>';
        $Html .= '<td>' . number_format($InventarioI / $nFac, 3) . '</td>';
        $Html .= '<td>' . number_format($Cargas / $nFac, 3) . '</td>';
        $Html .= '<td>' . number_format($Jarreos / $nFac, 3) . '</td>';
        if ($balance["valor"] == 1 && $Informacion === TipoInformacion::OMICROM) {
            $Html .= '<td>' . number_format($Bruto / $nFac, 3) . '</td>';
            $Html .= '<td>' . number_format($Diferencia / $nFac, 3) . '</td>';
        }
        if ($Informacion == TipoInformacion::COMPARATIVO) {
            $Html .= '<td>' . number_format($VentasCV / $nFac, 3) . '</td>';
        }
        $Html .= '<td>' . number_format($Ventas / $nFac, 3) . '</td>';
        if ($Informacion == TipoInformacion::COMPARATIVO) {
            $Html .= '<td>' . number_format($CargasCV / $nFac, 3) . '</td>';
        }
        $Html .= '<td>' . number_format($InvTeorico / $nFac, 3) . '</td>';
        $Html .= '<td>' . number_format($InventarioF / $nFac, 3) . '</td>';
        $Html .= '<td>' . number_format(($InventarioF - $InventarioI + $Ventas - $Cargas - $VtaExtra) / $nFac, 3) . '</td>';
        $Html .= '</tr>';
        $Html .= '</tbody>';
        $Html .= '</table>';
        $display["html"] = $Html;
    }
} else if ($request->getAttribute("Origen") === "FacturaAditivos") {

    $cSqlA = "SELECT inv.clave_producto, inv.descripcion, detalle.*,(factMost+factPublico+porFacturar) Piezas
                    FROM inv 
                    LEFT JOIN (
                    SELECT  inv.clave_producto, vta.descripcion,vta.cantidad,
                        ifnull(sum(case when fc.status = 1 or vta.uuid = '-----'  then vta.total end),0) total,
                        ifnull(sum(case when vta.uuid != '-----' and cli.rfc != 'XAXX010101000' OR (cli.rfc = 'XAXX010101000' and cli.nombre not like '%PUB%') then vta.cantidad end),0) factMost,
                        ifnull(sum(case when vta.uuid != '-----' and cli.rfc = 'XAXX010101000' and cli.nombre like '%PUB%' then vta.cantidad end),0) factPublico,
                        ifnull(sum(case when vta.uuid = '-----'  then vta.cantidad end),0) porFacturar
                      FROM vtaditivos vta 
                           inner join inv on vta.clave = inv.id
                           left join fcd on vta.id = fcd.ticket and producto > 5
                           left join fc on fcd.id = fc.id and fc.status = 1
                           LEFT JOIN cli ON fc.cliente=cli.id
                      WHERE DATE(vta.fecha) BETWEEN DATE('$FechaIni') AND DATE('$FechaFin')  
                      AND vta.tm = 'C' AND vta.cantidad > 0 
                      GROUP BY vta.descripcion  ORDER BY cast(vta.clave as decimal ) ASC
                    ) AS detalle ON detalle.clave_producto = inv.clave_producto 
                    WHERE rubro = 'Aceites'
                ";
    $queryA = utils\IConnection::getRowsFromQuery($cSqlA);
    $Html .= '<table  style="margin-bottom: 5px;border: 1px solid #434343;border-radius: 5px; width:99%;" summary="Inventario por periodo de fechas">';
    $Html .= '<tr style="font-weight: bold;">';
    $Html .= '<th>Clave</th>';
    $Html .= '<th >Descripcion</th>';
    $Html .= '<th>Piezas</th>';
    $Html .= '<th>Fact.Mostrador</th>';
    $Html .= '<th>Fact.General</th>';
    $Html .= '<th>Por Facturar</th>';
    $Html .= '<th>Total</th>';
    $Html .= '</tr>';
    $tol = $pza = $fm = $fp = $pf = $s = 0;
    foreach ($queryA as $rs) {
        if ($rs["Piezas"] >= 1) {
            $s++;
            $Bck = $s % 2 == 0 ? "background-color:#D5D8DC" : "background-color:#D7DBDD";
            $Html .= '<tr style="border-top:1px solid black;' . $Bck . '">';
            $Html .= '<td>' . $rs["clave_producto"] . '</td>';
            $Html .= '<td>' . $rs["descripcion"] . '</td>';
            $Html .= '<td>' . $rs["Piezas"] . '</td>';
            $Html .= '<td>' . $rs["factMost"] . '</td>';
            $Html .= '<td>' . $rs["factPublico"] . '</td>';
            $Html .= '<td>' . $rs["porFacturar"] . '</td>';
            $Html .= '<td>' . $rs["total"] . '</td>';
            $Html .= '</tr>';
            $pza += $rs["Piezas"];
            $fm += $rs["factMost"];
            $fp += $rs["factPublico"];
            $pf += $rs["porFacturar"];
            $tol += $rs["total"];
        }
    }
    $Html .= '<tr>';
    $Html .= '<td></td>';
    $Html .= '<td>Total</td>';
    $Html .= '<td class="numero">' . number_format($pza, 0) . '</td>';
    $Html .= '<td class="numero">' . number_format($fm, 0) . '</td>';
    $Html .= '<td class="numero">' . number_format($fp, 0) . '</td>';
    $Html .= '<td class="numero">' . number_format($pf, 0) . '</td>';
    $Html .= '<td class="numero">' . number_format($tol, 2) . '</td>';
    $Html .= '</tr>';
    $Html .= '</table>';
    $display["html"] = $Html;
}
echo json_encode($display);

<?php

include_once ('data/CxcDAO.php');
include_once ('data/RmDAO.php');
include_once ('data/FcDAO.php');
include_once ('data/FcdDAO.php');
include_once ("libnvo/lib.php");

use com\softcoatl\cfdi\v33\schema\Comprobante as Comprobante;
use com\softcoatl\utils as utils;

$mysqli = iconnect();

if (move_uploaded_file($_FILES["file"]["tmp_name"][0], "/home/omicrom/xml/" . $_FILES["file"]["name"][0])) {

    $nombreA = "/home/omicrom/xml/" . $_FILES["file"]["name"][0];

    $carga_xml = simplexml_load_file($nombreA); //Obtenemos los datos del xml agregados

    if (!$carga_xml) {
        $location = "/home/omicrom/xml/archivo.xml";
        error_log("XML INCORRECTO POR ALGUN CARACTER ESPECIAL SE DA NOMBRE DE archivo.xml");
        unlink($location);
        $fh = fopen($nombreA, 'r+') or die("Ocurrio un error al abrir el archivo");
        $texto = fgets($fh);
        $archivo = fopen($location, 'a');
        $string = mb_substr($texto, 0, 15);
        $cadena = utf8_decode($texto);
        fputs($archivo, $cadena);
        fclose($archivo);
        $carga_xml = simplexml_load_file($location);
    }

    $ns = $carga_xml->getNamespaces(true);
    $carga_xml->registerXPathNamespace('c', $ns['cfdi']);
    $carga_xml->registerXPathNamespace('t', $ns['tfd']);

    foreach ($carga_xml->xpath('//cfdi:Comprobante') as $cfdiComprobante) {
        $Folio = $cfdiComprobante['Folio'];
        $Importe = $cfdiComprobante['Total'];
        $FechaXml = $cfdiComprobante['Fecha'];
        $TipoDeComprobante = $cfdiComprobante['TipoDeComprobante'];
    }
    $i = 0;
    $Unidad[] = array();
    $Cantidad[] = array();
    $ValorUnitario[] = array();
    $ImporteC[] = array();
    $Descuento[] = array();
    $datetime = new DateTime($FechaXml);
    $FechaGeneral = $datetime->format("Y-m-d H:i:s");
    $FechaComercializadores = $datetime->format("Y-m-d");
    $HoraComercializadores = $datetime->format("H:i:s");
    $FechaHoraComer = $FechaComercializadores . " " . $HoraComercializadores;
    $Uuid = "";
    foreach ($carga_xml->xpath('//t:TimbreFiscalDigital') as $tfd) {
        $Uuid = strtoupper($tfd['UUID']);
    }

    $idCliSearch = generaBusquedaCliente($Uuid);
    $IdCliXml = 0;
    if (!($idCliSearch > 0)) {
        foreach ($carga_xml->xpath('//cfdi:Comprobante//cfdi:Receptor') as $Receptor) {
            $StringCliente = $Receptor["Nombre"];
            $SqlCliente = "SELECT id FROM cli WHERE nombre = '$StringCliente';";
            $RsCli = utils\IConnection::execSql($SqlCliente);
            $IdCliXml = $RsCli["id"];
        }
    } else if ($idCliSearch > 0) {
        $IdCliXml = $idCliSearch;
    }
    $rmDAO = new RmDAO();
    $rmVO = new RmVO();
    $rmVO = $rmDAO->retrieve($Uuid, "uuid");
    if (!($rmVO->getId() > 0)) {
        $idCli = explode("|", $_REQUEST["Cliente"]);
        if ($idCli[0] > 0) {
            $id_Cliente = $idCli[0];
        } else {
            $id_Cliente = $IdCliXml;
        }
        $IdCli = utils\HTTPUtils::getCookieValue("Cliente");
        foreach ($carga_xml->xpath('//cfdi:Comprobante//cfdi:Conceptos//cfdi:Concepto') as $Concepto) {
            $Clave_producto_servicio[$i] = $Concepto["ClaveProdServ"];
            $Unidad[$i] = $Concepto['ClaveUnidad'];
            $Cantidad[$i] = $Concepto['Cantidad'];
            $ImporteC[$i] = $Concepto["Importe"];
            $ValorUnitario[$i] = $Concepto["ValorUnitario"];
            $Descuento[$i] = $Concepto["Descuento"];
            $Descripcion[$i] = $Concepto["Descripcion"];
            $i++;
            $SumIVA = 0;
            $DatosCalculado = $Concepto["Importe"] - $Concepto["Cantidad"];
            foreach ($carga_xml->xpath('//cfdi:Comprobante//cfdi:Conceptos//cfdi:Concepto//cfdi:Impuestos//cfdi:Traslados//cfdi:Traslado') as $ImpuestosTras) {
                if ($DatosCalculado < $ImpuestosTras["Base"] && $DatosCalculado > 0) {
                    $SumIVA = $ImpuestosTras["Importe"];
                    $CalculoIeps = $ImpuestosTras["Base"];
                } else if ($DatosCalculado < 0) {
                    $SumIVA = $ImpuestosTras["Importe"];
                }
            }
            $IEPSCalculado = ($Concepto["Importe"] - $CalculoIeps) / $Concepto['Cantidad'];
            $Concepto["Importe"] = $Concepto["Importe"] + $SumIVA;

            $VcSc = utils\IConnection::execSql("SELECT valor FROM variables_corporativo WHERE llave = 'ServicioComercial';");
            if ($VcSc["valor"] == 1 && ($Concepto['ClaveProdServ'] == 15101515 || $Concepto['ClaveProdServ'] == 15101514 || $Concepto['ClaveProdServ'] == 15101505)) {
                error_log("Entramos en el apartado de Comercialización");
                $Tcom = "SELECT com.clavei,com.ieps FROM inv LEFT JOIN com ON com.descripcion=inv.descripcion WHERE inv_cproducto='" . $Concepto['ClaveProdServ'] . "';";
                $RsTcom = utils\IConnection::execSql($Tcom);
                $SqlIva = "SELECT iva FROM cia;";
                $Iva = utils\IConnection::execSql($SqlIva);

                if ($Concepto['ClaveProdServ'] == 15101514) {
                    $man = 1;
                } else if ($Concepto['ClaveProdServ'] == 15101515) {
                    $man = 2;
                } elseif ($Concepto['ClaveProdServ'] == 15101505) {
                    $man = 3;
                }
                /* Agregamos despacho de venta */
                $RmDAO = new RmDAO();
                $RmVO = new RmVO();
                $RmVO->setTurno("1");
                $RmVO->setCorte("1");
                $RmVO->setDispensario("1");
                $RmVO->setPosicion("1");
                $RmVO->setManguera($man);
                $RmVO->setDis_mang($man);
                $RmVO->setProducto($RsTcom["clavei"]);
                $PrecioXL = ($Concepto["Importe"] ) / $Concepto["Cantidad"];
                $RmVO->setPrecio($PrecioXL);
                $RmVO->setUuid($Uuid);
                $RmVO->setInicio_venta($FechaGeneral);
                $RmVO->setFin_venta($FechaGeneral);
                $RmVO->setPesos($Concepto["Importe"]);
                $RmVO->setPesosp($Concepto["Importe"]);
                $RmVO->setVolumen($Concepto['Cantidad']);
                $RmVO->setVolumenp($Concepto['Cantidad']);
                $RmVO->setImporte($Concepto["Importe"]);
                $RmVO->setIva($Iva["iva"] / 100);
//                $RmVO->setIeps($RsTcom["ieps"]);
                $RmVO->setIeps($IEPSCalculado);
                $RmVO->setVendedor(0);
                $RmVO->setComprobante(0);
                $RmVO->setFactor(0);
                $RmVO->setCliente($id_Cliente);
                $RmVO->setIdcxc(0);
                $idRmNvo = $RmDAO->create($RmVO);
                $RmVO = $RmDAO->retrieve($idRmNvo);
                $idRmNvo = 0;
                /* Agregamos registros en fc y fcd */

                $FcDAO = new FcDAO();
                $FcVO = new FcVO();
                $SqlFolio = "SELECT MAX(folio) + 1 nvoFolio FROM omicrom.fc WHERE serie = 'EST';";
                $Nvo = utils\IConnection::execSql($SqlFolio);
                $IepsCom = "SELECT (com.ieps * " . $RmVO->getVolumen() . ") iepsTotal, cia.iva, com.descripcion, com.precio, com.ieps "
                        . " FROM com LEFT JOIN cia ON TRUE WHERE clavei = '" . $RmVO->getProducto() . "' AND activo = 'Si';";
                $Ieps = utils\IConnection::execSql($IepsCom);

                $ImporteSinImpuestos = ($RmVO->getImporte() - $Ieps["iepsTotal"]) / (1 + ($Ieps["iva"] / 100));
                $IvaTotal = ($RmVO->getImporte() - $Ieps["iepsTotal"]) / (1 + ($Ieps["iva"] / 100)) * ($Ieps["iva"] / 100);
                $FcVO->setSerie("EST");
                $FcVO->setFolio($Nvo["nvoFolio"]);
                $FcVO->setFecha($RmVO->getFin_venta());
                $FcVO->setCliente($RmVO->getCliente());
                $FcVO->setCantidad($RmVO->getVolumen());
                $FcVO->setImporte($ImporteSinImpuestos);
                $FcVO->setIva($IvaTotal);
                $FcVO->setTotal($RmVO->getImporte());
                $FcVO->setIeps($Ieps["iepsTotal"]);
                $FcVO->setStatus(1);
                $FcVO->setTicket($RmVO->getId());
                $FcVO->setObservaciones("Se carga manualmente la información");
                $FcVO->setUsr("System");
                $FcVO->setOrigen(1);
                $FcVO->setStCancelacion(0);
                $FcVO->setRelacioncfdi(0);
                $FcVO->setUsocfdi("G03");
                $FcVO->setFormadepago("01");
                $FcVO->setMetododepago("PUE");
                $FcVO->setPeriodo("00");
                $FcVO->setMeses("00");
                $FcVO->setAno("0000");
                $FcVO->setDescuento(0);
                $FcVO->setDocumentoRelacion(0);
                $FcVO->setCancelacion("-");
                $IdNvoFc = $FcDAO->create($FcVO);

                $FcVO = $FcDAO->retrieve($IdNvoFc);
                $FcVO->setUuid($Uuid);
                $FcDAO->update($FcVO);

                $FcdDAO = new FcdDAO();
                $FcdVO = new FcdVO();
                $SelectInv = "SELECT id FROM inv WHERE descripcion = '" . $Ieps["descripcion"] . "'";
                $RsInvId = utils\IConnection::execSql($SelectInv);

                $FcdVO->setId($IdNvoFc);
                $FcdVO->setProducto($RsInvId["id"]);
                $FcdVO->setCantidad($RmVO->getVolumen());
                $FcdVO->setPreciob($PrecioXL);
                $FcdVO->setIva($Ieps["iva"] / 100);
                $FcdVO->setPrecio(($PrecioXL - $IEPSCalculado) / 1.16);
                $FcdVO->setIva_retenido(0.00);
                $FcdVO->setIeps($IEPSCalculado);
                $FcdVO->setImporte($RmVO->getImporte());
                $FcdVO->setTicket($RmVO->getId());
                $FcdVO->setTipoc("C");
                $FcdVO->setDescuento(0);
                $FcdDAO->create($FcdVO);

                /* Agregamos movimiento al estado de cuenta */
                $CxcDAO = new CxcDAO();
                $CxcVO = new CxcVO();
                $CxcVO->setPlacas("-");
                $CxcVO->setCliente($id_Cliente);
                $CxcVO->setImporte($Importe);
                $CxcVO->setCantidad($Concepto['Cantidad']);
                $CxcVO->setReferencia($RmVO->getId());
                $CxcVO->setFecha($FechaComercializadores);
                $CxcVO->setHora($HoraComercializadores);
                $CxcVO->setTm("H");
                $CxcVO->setConcepto("Venta de pipa, medio XML");
                $CxcVO->setRecibo($RmVO->getId());
                $CxcVO->setProducto($RsTcom["clavei"]);
                $CxcDAO->create($CxcVO);
            } else if ($Concepto['ClaveProdServ'] == 81141601) {
                $SqlProducto = "SELECT id FROM omicrom.inv where inv_cproducto = " . $Concepto['ClaveProdServ'];
                $IdComer = utils\IConnection::execSql($SqlProducto);
                $FcdVO->setId($IdNvoFc);
                $FcdVO->setProducto($IdComer["id"]);
                $FcdVO->setCantidad($Concepto["Cantidad"]);
                $FcdVO->setPreciob($Concepto["ValorUnitario"]);
                $FcdVO->setIva($Ieps["iva"] / 100);
                $FcdVO->setPrecio($Concepto["ValorUnitario"] / (1 + ($Ieps["iva"] / 100)));
                $FcdVO->setIva_retenido(0.00);
                $FcdVO->setIeps(0);
                $FcdVO->setImporte($Concepto["Importe"]);
                $FcdVO->setTicket(0);
                $FcdVO->setTipoc("C");
                $FcdVO->setDescuento(0);
                $FcdDAO->create($FcdVO);
            }
        }

        $cSQL = "
        SELECT 
        cantidad, total, importe, iva, total-importe-iva ieps, descuento 
        FROM (
        SELECT 
           ROUND( sum( cantidad ), 3) cantidad,
           ROUND( sum( total ) - sum(retenido), 2) total,
           ROUND( sum( cantidad * ( preciob - factorieps ) / (1 + factoriva) ), 2) importe,
           ROUND( sum(fiva), 2) iva,
           ROUND(SUM( descuento),2) descuento
        FROM (
           SELECT 
              iva factoriva,
              ieps factorieps,
              cantidad,
              importe total,
              preciob,
              descuento,
              (cantidad*(preciob - ieps)/(1+iva))*iva fiva,
              if(iva_retenido * importe / (1 + iva)=0,0,iva_retenido * importe / (1 + iva)) retenido
           FROM fcd WHERE id = '$IdNvoFc') as SUB
        ) SUBQ
    ";

        $Msj = "Procesos agregado con exito";
        $Ddd = utils\IConnection::execSql($cSQL);
        $FcVO = $FcDAO->retrieve($IdNvoFc);
        $FcVO->setCantidad($Ddd["cantidad"]);
        $FcVO->setImporte($Ddd['importe']);
        $FcVO->setIva($Ddd['iva']);
        $FcVO->setIeps($Ddd['ieps']);
        $FcVO->setTotal($Ddd['total']);
        $FcVO->setDescuento($Ddd['descuento']);
        if (!$FcDAO->update($FcVO)) {
            $Msj = "ERROR : Error en el proceso de carga";
        }
    } else {
        $Msj = "ERROR : UUID agregado con anterioridad";
    }
    SetExternalMessage($Msj);
}

function generaBusquedaCliente($Uuid) {
    $SqlGeneraCliente = "SELECT rue.uuid_compra,me.carga  FROM relaciones_uuid_ext rue LEFT JOIN me ON me.uuid = rue.uuid_compra WHERE rue.uuid_venta = '$Uuid';";
    $rsMe = utils\IConnection::execSql($SqlGeneraCliente);
    $SqlCxcCli = "SELECT cliente FROM cxc  WHERE tm = 'C' AND referencia = " . $rsMe["carga"] . " AND producto != 'A';";
    $CliId = utils\IConnection::execSql($SqlCxcCli);
    return $CliId["cliente"];
}

?>
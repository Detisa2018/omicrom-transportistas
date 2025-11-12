<?php

include_once ('data/IngresosDAO.php');
include_once ('data/CartaPorteDAO.php');
include_once ('data/Ingresos_detalleDAO.php');
include_once ('data/TrasladosDAO.php');
include_once ('data/TrasladosDetalleDAO.php');
include_once ("libnvo/lib.php");

use com\softcoatl\cfdi\v33\schema\Comprobante as Comprobante;
use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$mysqli = iconnect();
error_log(print_r($request, true));
$IdCli = explode("|", $request->getAttribute("Cliente"));
$IdPrv = $request->getAttribute("Terminal");
$IdCli = $IdCli[0];

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
    $carga_xml->registerXPathNamespace('pm', "http://pemex.com/facturaelectronica/addenda/v2");
    $carga_xml->registerXPathNamespace('cartaporte20', 'http://www.sat.gob.mx/CartaPorte20');
    $carga_xml->registerXPathNamespace('cartaporte30', 'http://www.sat.gob.mx/CartaPorte30');
    $carga_xml->registerXPathNamespace('cartaporte31', 'http://www.sat.gob.mx/CartaPorte31');
    $carga_xml->registerXPathNamespace('t', $ns['tfd']);
    $FormaPago = $MetodoPago = $UsoCfdi = $FechaXml = $Serie = $Folio = $TipoDeComprobante = NULL;
    foreach ($carga_xml->xpath('//cfdi:Comprobante') as $cfdiComprobante) {
        $Serie = $cfdiComprobante['Serie'];
        $Folio = $cfdiComprobante['Folio'];
        $FechaXml = $cfdiComprobante['Fecha'];
        $FormaPago = $cfdiComprobante["FormaPago"];
        $MetodoPago = $cfdiComprobante["MetodoPago"];
        $TotalTraslado = $cfdiComprobante["Total"];
        $SubTotalTraslado = $cfdiComprobante["SubTotal"];
        $TipoDeComprobante = $cfdiComprobante['TipoDeComprobante'];
    }
    $i = 0;
    $Unidad[] = array();
    $Cantidad[] = array();
    $ValorUnitario[] = array();
    $ImporteC[] = array();
    $Descuento[] = array();
    foreach ($carga_xml->xpath('//cfdi:Comprobante//cfdi:Receptor') as $Receptor) {
        $UsoCfdi = $Receptor["UsoCFDI"];
    }
    foreach ($carga_xml->xpath('//cfdi:Comprobante//cfdi:Conceptos//cfdi:Concepto') as $Concepto) {
        $Clave_producto_servicio[$i] = $Concepto["ClaveProdServ"];
        $i++;
    }
    $u = 0;
    // RETENCIONES ;
    foreach ($carga_xml->xpath('//cfdi:Comprobante//cfdi:Conceptos//cfdi:Concepto//cfdi:Impuestos//cfdi:Retenciones//cfdi:Retencion') as $Retenciones) {
        $u++;
        $BaseRetencion[$u] = $Retenciones["Base"];
        $ImpuestoRetencion[$u] = $Retenciones['Impuesto'];
        $TasaRetencion[$u] = $Retenciones['TasaOCuota'];
        $ImporteRetencion[$u] = -$Retenciones["Importe"];
    }
    foreach ($carga_xml->xpath('//cfdi:Comprobante//cfdi:Impuestos') as $Impuestos) {
        $ImpuestosT = $Impuestos["TotalImpuestosTrasladados"];
        $ImpuestosR = $Impuestos["TotalImpuestosRetenidos"];
    }
    $Uuid = null;
    foreach ($carga_xml->xpath('//t:TimbreFiscalDigital') as $tfd) {
        $Uuid = strtoupper($tfd['UUID']);
    }
    $IngresosDAO = new IngresosDAO();
    $IngresosVO = new IngresosVO();
    $IngresosVO = $IngresosDAO->retrieve($Uuid, "t.uuid");
    $V_CartaPorte = respondeVersionCartaPorte($carga_xml);
    error_log("VERSION  " . $V_CartaPorte);

    if (!is_numeric($IngresosVO->getId())) {
        $IdNvo = 0;
        foreach ($carga_xml->xpath('//cfdi:Comprobante//cfdi:Complemento//cartaporte' . $V_CartaPorte . ':CartaPorte//cartaporte' . $V_CartaPorte . ':Mercancias//cartaporte' . $V_CartaPorte . ':Mercancia') as $CPMercancia) {
            $CantidadCartaPorte = $CPMercancia["Cantidad"];
            $ClaveProductoServ = $Clave_producto_servicio[0];
            $BienesTransp = $CPMercancia["BienesTransp"];
            $Embalaje = $CPMercancia["Embalaje"];
            $SqlIdProducto = "SELECT id FROM inv WHERE inv_cproducto = $BienesTransp";
            $IdInv = utils\IConnection::execSql($SqlIdProducto);
            if ($TipoDeComprobante == "T") {
                $IdNvo = insertIntoTraslado($Serie, $Folio, $FechaXml, $CantidadCartaPorte, $Uuid, $IdCli, $ClaveProductoServ, $FormaPago, $UsoCfdi, $MetodoPago, $IdPrv);
                insertIntoTrasladoDetalle($IdNvo, $IdInv["id"], $CantidadCartaPorte, $SubTotalTraslado, $TotalTraslado, $TasaRetencion[1]);
                insertIntoCartaPorte($IdNvo, $Serie, $FechaXml, $Embalaje);
            } else {
                $IdNvo = insertIntoIngreso($Serie, $Folio, $FechaXml, $CantidadCartaPorte, $Uuid, $IdCli, $ClaveProductoServ, $FormaPago, $UsoCfdi, $MetodoPago, $IdPrv);
                insertIntoIngresoDetalle($IdNvo, $IdInv["id"], $CantidadCartaPorte, $SubTotalTraslado, $TotalTraslado, $TasaRetencion[1]);
                insertIntoCartaPorte($IdNvo, $Serie, $FechaXml, $Embalaje);
            }
        }
        foreach ($carga_xml->xpath('//cfdi:Comprobante//cfdi:Complemento//cartaporte' . $V_CartaPorte . ':CartaPorte//cartaporte' . $V_CartaPorte . ':Ubicaciones//cartaporte' . $V_CartaPorte . ':Ubicacion') as $Ubic) {
            $TipoDestino = $Ubic["TipoUbicacion"];
            $FechaTime = $Ubic["FechaHoraSalidaLlegada"];
            $DistanciaRecorrida = $Ubic["DistanciaRecorrida"];
            $IdCartaPorteFk = "SELECT id id_carta_porte_fk FROM carta_porte WHERE id_origen=$IdNvo;";
            $id_carta_porte_fk = utils\IConnection::execSql($IdCartaPorteFk);
            $TipoComp = $TipoDeComprobante == "T" ? "TRA" : "ING";
            foreach ($Ubic->xpath('./cartaporte20:Domicilio') as $Domicilio) {
                switch ($TipoDestino) {
                    case "Origen":
                        insertOrigenDestino($id_carta_porte_fk["id_carta_porte_fk"], $Domicilio["Estado"], $Domicilio["Localidad"], $Domicilio["Municipio"], $Domicilio["CodigoPostal"], 'P', $FechaTime, $DistanciaRecorrida,$TipoComp);
                        break;
                    case "Destino":
                        insertOrigenDestino($id_carta_porte_fk["id_carta_porte_fk"], $Domicilio["Estado"], $Domicilio["Localidad"], $Domicilio["Municipio"], $Domicilio["CodigoPostal"], 'C', $FechaTime, $DistanciaRecorrida,$TipoComp);
                        break;
                }
            }
        }

        foreach ($carga_xml->xpath('//cfdi:Comprobante//cfdi:Complemento//cartaporte' . $V_CartaPorte . ':CartaPorte//cartaporte' . $V_CartaPorte . ':Mercancias//cartaporte' . $V_CartaPorte . ':Autotransporte') as $AutoTransp) {
            $NumeroSCT = $AutoTransp["NumPermisoSCT"];
            $IdAutoTransporte = "SELECT id FROM catalogo_vehiculos WHERE numero_sct ='$NumeroSCT';";
            $IdAuthT = utils\IConnection::execSql($IdAutoTransporte);
            $IdAutg = $IdAuthT["id"];

            $IdCartaPorteFk = "SELECT id id_carta_porte_fk FROM carta_porte WHERE id_origen=$IdNvo;";
            $id_carta_porte_fk = utils\IConnection::execSql($IdCartaPorteFk);
            $UpdateCp = "UPDATE carta_porte SET id_vehiculo = $IdAutg WHERE id = " . $id_carta_porte_fk["id_carta_porte_fk"];
            utils\IConnection::execSql($UpdateCp);
        }

        foreach ($carga_xml->xpath('//cfdi:Comprobante//cfdi:Complemento//cartaporte' . $V_CartaPorte . ':CartaPorte//cartaporte' . $V_CartaPorte . ':FiguraTransporte//cartaporte' . $V_CartaPorte . ':TiposFigura') as $Tf) {
            $value = $Tf["TipoFigura"] === "01";
            if (strlen($Tf["NumLicencia"]) > 6) {
                $IdOp = "SELECT id FROM catalogo_operadores WHERE rfc_operador='" . $Tf["RFCFigura"] . "';";
                $idop = utils\IConnection::execSql($IdOp);
                $IdOperador = $idop["id"];

                $IdCartaPorteFk = "SELECT id id_carta_porte_fk FROM carta_porte WHERE id_origen=$IdNvo;";
                $id_carta_porte_fk = utils\IConnection::execSql($IdCartaPorteFk);
                $UpdateCp = "UPDATE carta_porte SET id_operador = $IdOperador WHERE id = " . $id_carta_porte_fk["id_carta_porte_fk"];
                utils\IConnection::execSql($UpdateCp);
            }
        }
        SetExternalMessage("Registro ingresado con exito");
    } else {
        error_log("REGISTRO INGRESADO CON ANTERIORIDAD, FAVOR DE VERIFICAR !");
        SetExternalMessage("ERROR : El UUID se encuentra registrado en la base de datos");
    }
} else {
    echo "Error en guardado";
}

function insertOrigenDestino($id_carta_porte_fk, $Estado, $Localidad, $Municipio, $CodigoPostal, $Origen, $FechaTime, $DistanciaRecorrida,$TipoComp) {
    $IdDestino = "SELECT id FROM catalogo_direcciones WHERE estado = '$Estado' AND "
            . "municipio = '$Municipio' AND codigo_postal = '$CodigoPostal' AND tabla_origen = '$Origen' AND id > 0 LIMIT 1;";
    $IdDst = utils\IConnection::execSql($IdDestino);
    $TipoDestinoOrigen = $Origen === 'P' ? "Origen" : "Destino";
    $Insert = "INSERT INTO carta_porte_destino (id_destino_fk, id_carta_porte_fk, fecha, distancia, tipo, origen) "
            . "VALUES ('" . $IdDst["id"] . "','$id_carta_porte_fk','$FechaTime','$DistanciaRecorrida','$TipoDestinoOrigen','$TipoComp');";
    utils\IConnection::execSql($Insert);
}

function insertIntoCartaPorte($idIngreso, $Serie, $FechaXml, $Embalaje) {
    $Insert = "INSERT INTO carta_porte ("
            . "id_origen,"
            . "origen,"
            . "transpInternac,"
            . "rfcRemitenteDestinatario,"
            . "fechaHoraSalidaLlegada,"
            . "moneda, "
            . "embalaje, "
            . "idOrigen, "
            . "idDestino, "
            . "id_operador, "
            . "id_vehiculo, "
            . "id_direccion) "
            . "VALUES ($idIngreso,'$Serie','No',(SELECT rfc_representante_legal FROM cia),'$FechaXml','MXN','$Embalaje','00','00',1,1,1)";
    utils\IConnection::execSql($Insert);
}

function insertIntoIngreso($Serie, $Folio, $FechaXml, $CantidadCartaPorte, $Uuid, $Cliente, $ClaveProductoServ, $FormaPago, $UsoCfdi, $MetodoPago, $IdPrv) {
    $IngresoDAO = new IngresosDAO();
    $IngresoVO = new IngresosVO();

    $IngresoVO->setSerie($Serie);
    $IngresoVO->setFolio($Folio);
    $IngresoVO->setFecha($FechaXml);
    $IngresoVO->setCantidad($CantidadCartaPorte);
    $IngresoVO->setImporte(0);
    $IngresoVO->setIva(0);
    $IngresoVO->setIeps(0);
    $IngresoVO->setTotal(0);
    $IngresoVO->setStatus(1);
    $IngresoVO->setObservaciones("Creación por medio de XML Dia " . date("Y-m-d H:i:s"));
    $IngresoVO->setUuid($Uuid);
    $IngresoVO->setUsr("SistemXML");
    $IngresoVO->setClaveProdServ($ClaveProductoServ);
    $IngresoVO->setId_cli($Cliente);
    $IngresoVO->setMetodopago($MetodoPago);
    $IngresoVO->setFormadepago($FormaPago);
    $IngresoVO->setUsocfdi($UsoCfdi);
    $IngresoVO->setId_prv($IdPrv);
    return $IngresoDAO->create($IngresoVO);
}

function insertIntoIngresoDetalle($idIngreso, $IdProducto, $CantidadTras, $SubTotalTraslado, $TotalTraslado, $Retencion) {
    $IngresoDetalleDAO = new Ingresos_detalleDAO();
    $IngresoDetalleVO = new Ingresos_detalleVO();
    $IngresoDetalleVO->setId($idIngreso);
    $IngresoDetalleVO->setProducto($IdProducto);
    $IngresoDetalleVO->setCantidad($CantidadTras);
    $IngresoDetalleVO->setPreciob(0);
    $IngresoDetalleVO->setPrecio(0);
    $IngresoDetalleVO->setIva(0.16);
    $IngresoDetalleVO->setIeps(0);
    $IngresoDetalleVO->setImporte(0);
    $IngresoDetalleVO->setId_rm(-1);
    $IngresoDetalleDAO->create($IngresoDetalleVO);

    $IngresoDetalleVO = new Ingresos_detalleVO();
    $IngresoDetalleVO->setId($idIngreso);
    $IngresoDetalleVO->setProducto(0);
    $IngresoDetalleVO->setCantidad(1);
    $IngresoDetalleVO->setImporte($TotalTraslado);
    $IngresoDetalleVO->setPreciob($TotalTraslado);
    $IngresoDetalleVO->setPrecio($TotalTraslado / (1.16 - $Retencion));
    $IngresoDetalleVO->setIeps($Retencion * 100);
    $IngresoDetalleVO->setIva(0.16);
    $IngresoDetalleVO->setId_rm(0);
    $IngresoDetalleDAO->create($IngresoDetalleVO);
}

function insertIntoTraslado($Serie, $Folio, $FechaXml, $CantidadCartaPorte, $Uuid, $Cliente, $ClaveProductoServ, $FormaPago, $UsoCfdi, $MetodoPago, $IdPrv) {
    error_log("TRASLADO INSERTAs");
    $TrasladosDAO = new TrasladosDAO();
    $TrasladosVO = new TrasladosVO();
    $TrasladosVO->setMetodoPago("-");
    $TrasladosVO->setSerie($Serie);
    $TrasladosVO->setFolio($Folio);
    $TrasladosVO->setFecha($FechaXml);
    $TrasladosVO->setCantidad($CantidadCartaPorte);
    $TrasladosVO->setImporte(0);
    $TrasladosVO->setIva(0);
    $TrasladosVO->setIeps(0);
    $TrasladosVO->setTotal(0);
    $TrasladosVO->setStatus(1);
    $TrasladosVO->setObservaciones("Creación por medio de XML Dia " . date("Y-m-d H:i:s"));
    $TrasladosVO->setUuid($Uuid);
    $TrasladosVO->setUsr("SistemXML");
    $TrasladosVO->setClaveProductoServicio("000000");
    $TrasladosVO->setStCancelacion(0);
    $TrasladosVO->setId_cli($Cliente);
    $TrasladosVO->setMetodopago($MetodoPago);
    $TrasladosVO->setFormapago("-");
    $TrasladosVO->setUsocfdi($UsoCfdi);
    $TrasladosVO->setMotivoCan("00");
    $TrasladosVO->setMetodoPago("-");
    $TrasladosVO->setIdprv($IdPrv);
    return $TrasladosDAO->create($TrasladosVO);
}

function insertIntoTrasladoDetalle($idIngreso, $IdProducto, $CantidadTras, $SubTotalTraslado, $TotalTraslado, $Retencion) {
    $TrasladosDetalleDAO = new TrasladosDetalleDAO();
    $TrasladosDetalleVO = new TrasladosDetalleVO();
    $TrasladosDetalleVO->setId($idIngreso);
    $TrasladosDetalleVO->setProducto($IdProducto);
    $TrasladosDetalleVO->setCantidad($CantidadTras);
    $TrasladosDetalleVO->setPreciob(0);
    $TrasladosDetalleVO->setPrecio(0);
    $TrasladosDetalleVO->setIva(0.16);
    $TrasladosDetalleVO->setIeps(0);
    $TrasladosDetalleVO->setImporte(0);
    $TrasladosDetalleDAO->create($TrasladosDetalleVO);
}

function respondeVersionCartaPorte($carga_xml) {
    if ($carga_xml->xpath('//cfdi:Complemento//cartaporte20:CartaPorte')) {
        $version = '20';
    } elseif ($carga_xml->xpath('//cfdi:Complemento//cartaporte30:CartaPorte')) {
        $version = '30';
    } elseif ($carga_xml->xpath('//cfdi:Complemento//cartaporte31:CartaPorte')) {
        $version = '31';
    } else {
        $version = 'Desconocida';
    }
    return $version;
}

?>
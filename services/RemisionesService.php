<?php

#Librerias
include_once ('data/RmDAO.php');
include_once ('data/ClientesDAO.php');
include_once ('data/CombustiblesDAO.php');
include_once ('data/TarjetaDAO.php');
include_once ('data/CxcDAO.php');
include_once ('data/IslaDAO.php');
include_once ('data/FcDAO.php');
include_once ('data/FcdDAO.php');
include_once ('data/CombustibleDAO.php');
include_once ('services/VentasConCodigo.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "remisiones.php?";

$rmDAO = new RmDAO();
$clientesDAO = new ClientesDAO();
$comDAO = new CombustiblesDAO();
$tarjetaDAO = new TarjetaDAO();
$cxcDAO = new CxcDAO();
$islaDAO = new IslaDAO();

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    $rmVO = new RmVO();
    $rmVO->setId($sanitize->sanitizeInt("busca"));
    if (is_numeric($rmVO->getId())) {
        $rmVO = $rmDAO->retrieve($rmVO->getId());
    }
    $clienteVO = $clientesDAO->retrieve($rmVO->getCliente());

    //error_log(print_r($request, TRUE));
    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {

            $Posicion = $sanitize->sanitizeInt("Posicion");
            $Producto = $sanitize->sanitizeString("Producto");
            $Importe = $sanitize->sanitizeFloat("Importe");
            $Volumen = $sanitize->sanitizeFloat("Volumen");

            $islaVO = $islaDAO->retrieve(1, "isla");
            $comVO = $comDAO->retrieve($Producto, "clavei");

            $man_sql = "SELECT m.dispensario,m.manguera,m.dis_mang,m.isla,m.posicion,m.factor,m.enable,man.despachador 
                        FROM man_pro m,man
                        WHERE m.posicion = man.posicion AND m.posicion='$Posicion' AND m.producto = '$Producto' AND m.activo = 'Si'";

            $Man = utils\IConnection::execSql($man_sql);

            if ($Importe > 0) {
                $Volumen = round($Importe / $comVO->getPrecio(), 4);
            } elseif ($Volumen > 0) {
                $Importe = round($Volumen * $comVO->getPrecio(), 4);
            }

            $VolumenBase = 50000;

            do {
                if ($Volumen > $VolumenBase) {
                    $auxV = $VolumenBase;
                    $Volumen -= $auxV;
                } else {
                    $auxV = $Volumen;
                    $Volumen = 0;
                }

                $auxI = round($auxV * $comVO->getPrecio(), 4);

                $Importep = $auxI / ( 1 + $Man["factor"] * $Man["enable"] / 100 );
                $Volumenp = $auxV / ( 1 + $Man["factor"] * $Man["enable"] / 100 );

                $rmVO->setDispensario($Man["dispensario"]);
                $rmVO->setPosicion($Posicion);
                $rmVO->setManguera($Man["manguera"]);
                $rmVO->setDis_mang($Man["dis_mang"]);
                $rmVO->setProducto($comVO->getClavei());
                $rmVO->setPrecio($comVO->getPrecio());
                $rmVO->setInicio_venta(date("Y-m-d H:i:s"));
                $rmVO->setFin_venta(date("Y-m-d H:i:s"));
                $rmVO->setPesos($auxI);
                $rmVO->setVolumen($auxV);
                $rmVO->setPesosp($Importep);
                $rmVO->setVolumenp($Volumenp);
                $rmVO->setTurno($islaVO->getTurno());
                $rmVO->setCorte($islaVO->getCorte());
                $rmVO->setVendedor($Man["despachador"]);
                $rmVO->setIva($comVO->getIva());
                $rmVO->setIeps($comVO->getIeps());
                $rmVO->setFactor($Man["factor"]);
                $rmVO->setUuid("-----");
                //error_log(print_r($rmVO, TRUE));
                if ($rmDAO->create($rmVO) > 0) {
                    $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                    BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "VENTA MANUAL POR [" . $auxI . "]");
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } while ($Volumen > 0);
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {

            if ($rmDAO->update($rmVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === "Cambiar tipo de despacho") {
            if (date("Y-m-d", strtotime($rmVO->getFin_venta())) == date("Y-m-d") || date("Y-m-d", strtotime($rmVO->getFin_venta())) == date("Y-m-d", strtotime(date("Y-m-d") . " -1 day")) && ($rmVO->getImporte() + 1 > $rmVO->getPesos() && $rmVO->getImporte() - 1 < $rmVO->getPesos())) {
                $rmVO->setEnviado(0);
                $rmVO->setTipo_venta($sanitize->sanitizeString("Tipo_venta"));
                if ($rmDAO->update($rmVO)) {
                    $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                    BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "CAMBIA VENTA " . $rmVO->getId() . " A [" . $rmVO->getTipo_venta() . "]");
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } else {
                $Msj = "Lo siento tu transaccion ha sido procesada, por lo tanto no fue posible realizar el cambio";
            }
        } elseif ($request->getAttribute("Boton") === "Guardar") {
            $Msj = relacionPorUnidad($sanitize->sanitizeInt("Cliente"), $sanitize->sanitizeString("Codigo"), $rmVO->getId(), $sanitize->sanitizeString("Kilometraje"), $sanitize->sanitizeString("Placas"));
        } elseif ($request->getAttribute("Boton") === "Reasignar Cliente") {
            $partes = explode('|', $request->getAttribute("ClienteS"));
            $idCliente = $partes[0];
            $ActualizaCxc = "UPDATE cxc SET cliente = $idCliente WHERE referencia IN (" . $rmVO->getId() . ") AND tm = 'H' LIMIT 1";
            utils\IConnection::execSql($ActualizaCxc);
            $ActualizaRm = "UPDATE rm  SET cliente = $idCliente  WHERE id IN (" . $rmVO->getId() . ") LIMIT 1";
            utils\IConnection::execSql($ActualizaRm);
            $ActualizaFc = "UPDATE fc SET cliente = $idCliente  WHERE id in (SELECT fc.id FROM omicrom.fc "
                    . "LEFT JOIN fcd ON fc.id=fcd.id WHERE fcd.ticket = " . $rmVO->getId() . ") LIMIT 1;";
            utils\IConnection::execSql($ActualizaFc);
            $Msj = "Registros actualizados con exito";
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en pagos: " . $ex);
    } finally {
        header("Location: $Return");
    }
}


if ($request->hasAttribute("op")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $cId = $sanitize->sanitizeInt("cId");

    try {
        if ($request->getAttribute("op") === utils\Messages::OP_DELETE) {
            $Msj = utils\Messages::RESPONSE_ERROR;
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en pagos: " . $ex);
    } finally {
        header("Location: $Return");
    }
}

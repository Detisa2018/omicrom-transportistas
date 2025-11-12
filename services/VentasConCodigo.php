<?php

include_once ('data/CxcDAO.php');
include_once ('data/IslaDAO.php');
include_once ('data/FcDAO.php');
include_once ('data/ClientesDAO.php');
include_once ('data/TarjetaDAO.php');
include_once ('data/BitacoraDAO.php');

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();

function relacionPorUnidad($Cliente, $Codigo, $Rm, $Kilometraje, $Placas) {
    $mysqli = iconnect();
    $usuarioSesion = getSessionUsuario();
    $clientesDAO = new ClientesDAO();
    $clientesVO_ = new ClientesVO();
    $clienteVO = new ClientesVO();

    $rmDAO = new RmDAO();
    $rmVO = new RmVO();

    $tarjetaDAO = new TarjetaDAO();
    $tarjetaVO = new TarjetaVO();

    $BitacoraDAO = new BitacoraDAO();
    $BitacoraVO = new BitacoraVO();

    $rmVO = $rmDAO->retrieve($Rm);
    $clienteVO_ = $clientesDAO->retrieve($Cliente);
    $clienteVO = $clientesDAO->retrieve($rmVO->getCliente());
    /* Validamos si unidad es tipo balance o no */
    $rsUB = validaTipoUnidad($Codigo, $clienteVO_, $rmVO);
    if ($rsUB["no"] == 0 || $rsUB["no"] == null) {
        if ($clienteVO_->getActivo() === "Si") {
            if (!empty($Codigo)) {
                if (empty($rmVO->getCodigo()) || $clienteVO->getTipodepago() === TiposCliente::CONTADO || $clienteVO->getTipodepago() === TiposCliente::PUNTOS) {
                    if (($clienteVO->getTipodepago() === TiposCliente::CONTADO || $clienteVO->getTipodepago() === TiposCliente::PUNTOS) && $rmVO->getUuid() === "-----") {
                        $Codigo = explode("|", $Codigo);
                        $tarjetaVO = $tarjetaDAO->retrieve(trim($Codigo[0]), "id");
                        if (strtolower($tarjetaVO->getEstado()) === StatusUnidad::ACTIVA) {
                            $Kilometraje = empty($Kilometraje) ? $rmVO->getKilometraje() : $Kilometraje;
                            $rmVO->setCliente($Cliente);
                            if (strlen($Placas) > 1 && !empty($Placas)) {
                                $rmVO->setPlacas($Placas);
                            } else {
                                $rmVO->setPlacas($tarjetaVO->getPlacas());
                            }
                            $rmVO->setPuntos($clienteVO_->getPuntos());
                            $rmVO->setCodigo($tarjetaVO->getCodigo());
                            $rmVO->setKilometraje($Kilometraje);
                            if ($clienteVO_->getTipodepago() === TiposCliente::CONSIGNACION) {
                                $rmVO->setTipo_venta("N");
                            }
                            if ($rmDAO->update($rmVO)) {
                                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                                $BitacoraDAO->saveLog($usuarioSesion->getNombre(), "ADM", "REASIGNA VENTA " . $rmVO->getId() . " A CLIENTE " . $rmVO->getCliente());
                                if (isClienteCreditPrepagoTarjeta($clienteVO)) {
                                    $sqlUpdateCxcLogic = "UPDATE cxc SET placas = '" . $tarjetaVO->getPlacas() . "' 
                                      WHERE cliente =  " . $rmVO->getCliente() . " AND referencia = '" . $rmVO->getId() . "' AND producto = '" . $rmVO->getProducto() . "' AND tm = 'C' LIMIT 1;";
                                    if (!$mysqli->query($sqlUpdateCxcLogic)) {
                                        error_log($mysqli->error);
                                        $Msj = utils\Messages::RESPONSE_ERROR;
                                    }
                                } else {
                                    $clienteVO = $clientesDAO->retrieve($rmVO->getCliente());
                                    //error_log(print_r($clientevO, true));
                                    if ($clienteVO->getTipodepago() === TiposCliente::CREDITO || $clienteVO->getTipodepago() === TiposCliente::PREPAGO || $clienteVO->getTipodepago() === TiposCliente::TARJETA) {
                                        verificaCxc($rmVO, $tarjetaVO);
                                    }
                                }
                            } else {
                                $Msj = utils\Messages::RESPONSE_ERROR;
                            }
                        } else {
                            $Msj = "Error : La unidad esta desactivada, favor de verificarlo.";
                        }
                    } else {
                        $Msj = "Error : No es posible realizar esta operacion, el ticket ya esta facturado.";
                    }
                } else {
                    $Msj = "Error : La venta ya tiene un código asignado";
                }
            } else {
                $Msj = "Error : El código ingresado es invalido";
            }
        } else {
            $Msj = "Error : No es posible realizar esta operacion, el cliente [" . $clienteVO_->getNombre() . "] esta inactivo";
        }
    } else {
        $Codigo = explode("|", $Codigo);
        $tarjetaVO = $tarjetaDAO->retrieve(trim($Codigo[0]), "id");

        actualizaImporteUnidadB($rmVO->getImporte(), $clienteVO_->getId(), trim($Codigo[1]));

        $Kilometraje = empty($Kilometraje) ? $rmVO->getKilometraje() : $Kilometraje;

        $rmVO->setCliente($Cliente);
        if (strlen($Placas) > 1 && !empty($Placas)) {
            $rmVO->setPlacas($Placas);
        } else {
            $rmVO->setPlacas($tarjetaVO->getPlacas());
        }
        $rmVO->setCodigo($tarjetaVO->getCodigo());
        $rmVO->setKilometraje($Kilometraje);
        if ($rmDAO->update($rmVO)) {
            $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
            verificaCxc($rmVO, $tarjetaVO);
        }
    }
    $mysqli->error;
    return $Msj;
}

function validaTipoUnidad($Codigo, $clienteVO_, $rmVO) {
    $Codigo = explode("|", $Codigo);
    $BuscaUnidadB = "SELECT *,count(1) no FROM unidades WHERE cliente = " . $clienteVO_->getId() . " AND periodo='B' AND codigo = '" . trim($Codigo[1]) . "'";
    $rsUB = utils\IConnection::execSql($BuscaUnidadB);
    if ($rsUB["periodo"] === "B") {
        insertaBitacoraBalance($rsUB, $rmVO);
    }
    return $rsUB;
}

function insertaBitacoraBalance($rsUB, $rmVO) {
    $usuarioSesion = getSessionUsuario();
    $Residuo = $rsUB["importe"] - $rmVO->getImporte();
    $Insrt = "INSERT INTO unidades_log (noPago,importeAnt,importe,importeDelPago,idUnidad,usr) 
                      VALUES ('" . $rmVO->getId() . "'," . "'" . $rsUB["importe"] . "'," . "'" . $Residuo . "'," . "'" . -$rmVO->getImporte() . "'," . "'" . $rsUB["id"] . "'," . "'" . $usuarioSesion->getNombre() . "');";
    utils\IConnection::execSql($Insrt);
}

function isClienteCreditPrepagoTarjeta($clienteVO) {
    if ($clienteVO->getTipodepago() === TiposCliente::CREDITO || $clienteVO->getTipodepago() === TiposCliente::PREPAGO || $clienteVO->getTipodepago() === TiposCliente::TARJETA) {
        return true;
    } else {
        return false;
    }
}

function actualizaImporteUnidadB($Importe, $Cliente, $Codigo) {
    $mysqli2 = iconnect();

    $UpdateTarjeta = "UPDATE unidades SET importe = importe - " . $Importe . " WHERE cliente = " . $Cliente
            . " AND periodo = 'B' AND codigo = '" . $Codigo . "'";
    if (!$mysqli2->query($UpdateTarjeta)) {
        error_log($mysqli2->error);
        $Msj = utils\Messages::RESPONSE_ERROR;
    }
    $mysqli2->close();
}

function verificaCxc($rmVO, $tarjetaVO) {
    $cxcDAO = new CxcDAO();
    $cxcVO = new CxcVO();
    $rmDAO = new RmDAO();

    $IdCxc = "SELECT id FROM cxc WHERE referencia = " . $rmVO->getId() . " AND cliente = " . $rmVO->getCliente() . " AND tm='C'";
    $Idcxc = utils\IConnection::execSql($IdCxc);
    if ($Idcxc["id"] > 0) {
        $cxcVO = $cxcDAO->retrieve($Idcxc["id"]);
        $cxcVO->setPlacas($rmVO->getPlacas());
        if ($cxcDAO->update($cxcVO) < 0) {
            $Msj = utils\Messages::RESPONSE_ERROR;
        } else {
            $rmVO = $rmDAO->retrieve($rmVO->getId());
            $rmVO->setEnviado(0);
            $rmDAO->update($rmVO);
        }
    } else {
        $cxcVO->setCliente($rmVO->getCliente());
        $cxcVO->setPlacas($tarjetaVO->getPlacas());
        $cxcVO->setReferencia($rmVO->getId());
        $cxcVO->setFecha($rmVO->getFin_venta());
        $cxcVO->setHora($rmVO->getFin_venta());
        $cxcVO->setTm("C");
        $cxcVO->setConcepto("Venta de combustible");
        $cxcVO->setCantidad($rmVO->getVolumen());
        $cxcVO->setImporte($rmVO->getPesos());
        $cxcVO->setRecibo(0);
        $cxcVO->setCorte($rmVO->getCorte());
        $cxcVO->setRubro("-----");
        $cxcVO->setProducto($rmVO->getProducto());
        if ($cxcDAO->create($cxcVO) < 0) {
            error_log($mysqli->error);
            $Msj = utils\Messages::RESPONSE_ERROR;
        } else {
            $rmVO = $rmDAO->retrieve($rmVO->getId());
            $rmVO->setEnviado(0);
            $rmDAO->update($rmVO);
        }
    }
}

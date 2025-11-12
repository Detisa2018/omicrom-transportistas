<?php

#Librerias
include_once ('data/ServiciosTraDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "servicios.php?";

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    $ServiciosTraDAO = new ServiciosTraDAO();
    $ServicioTraVO = new ServiciosTraVO();

    $ServicioTraVO = $ServiciosTraDAO->retrieve($busca);

    $ServicioTraVO->setNombre($request->getAttribute("Nombre"));
    $ServicioTraVO->setClave_producto($request->getAttribute("ClaveProducto"));
    $ServicioTraVO->setClave_unidad($request->getAttribute("ClaveUnidad"));
    $ServicioTraVO->setPrecio($request->getAttribute("Precio"));
    $ServicioTraVO->setIdentificador($request->getAttribute("Identificador"));

    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            if ($ServiciosTraDAO->create($ServicioTraVO) > 0) {
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
            if ($ServiciosTraDAO->update($ServicioTraVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
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
    $vehiculoDAO = new VehiculoDAO();
    $cId = $sanitize->sanitizeInt("cId");

    try {
        if ($request->getAttribute("op") === utils\Messages::OP_DELETE) {

            if ($vehiculoDAO->remove($cId)) {
                $Msj = utils\Messages::RESPONSE_VALID_DELETE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en vehiculos: " . $ex);
    } finally {
        header("Location: $Return");
    }
}
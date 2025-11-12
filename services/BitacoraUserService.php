<?php

#Librerias
include_once ('data/UsuarioDAO.php');
include_once ('data/AuthSemestralDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "bitacoraUsers.php?";

$AuthBitacoraDAO = new AuthSemestralDAO();
$BitacoraDAO = new BitacoraDAO();
$BitacoraVO = new BitacoraVO();
if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    $AuthBitacoraVO = new AuthSemestralVO();
    if ($request->getAttribute("Boton") !== utils\Messages::OP_UPDATE) {
        $AuthBitacoraVO->setId_authuser($usuarioSesion->getId());
    } else {
        $AuthBitacoraVO = $AuthBitacoraDAO->retrieve($busca);
    }
    $AuthBitacoraVO->setFecha($request->getAttribute("Fecha"));
    $AuthBitacoraVO->setDescripcion($request->getAttribute("Descripcion"));
    $AuthBitacoraVO->setStatus($request->getAttribute("Status"));

    //error_log(print_r($request, TRUE));
    try {

        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            if ($AuthBitacoraDAO->create($AuthBitacoraVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "CREACION BITACORA PARA USUARIOS: " . $AuthBitacoraVO->getId());
            } else {
                $Msj = $response;
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
            if ($AuthBitacoraDAO->update($AuthBitacoraVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE . " " . $Msj;
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "ACTUALIZACION  BITACORA PARA USUARIOS: " . $AuthBitacoraVO->getId());
            } else {
                $Msj = $response . " " . $Msj;
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en usuarios: " . $ex);
    } finally {
        header("Location: $Return");
    }
}
<?php

#Librerias
include_once ('data/PercepcionesDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "percepciones.php?";

$nameVariableSession = "CatalogoDePercepciones"; /* Utilizado en clientesService */

if ($request->hasAttribute("cVarVal")) {
    utils\HTTPUtils::setSessionBiValue($nameVariableSession, "cVarVal", $request->getAttribute("cVarVal"));
}
$ObjectVO = new PercepcionesVO();
$ObjectDAO = new PercepcionesDAO();

$cVarVal = utils\HTTPUtils::getSessionBiValue($nameVariableSession, "cVarVal");

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    try {
        if (is_numeric($request->getAttribute("busca"))) {
            $ObjectVO = $ObjectDAO->retrieve($request->getAttribute("busca"));
        }

        $ObjectVO->setEmpleado_id($request->getAttribute("Empleado"));
        $ObjectVO->setTipo_percepcion_id($request->getAttribute("T_Percepcion"));
        $ObjectVO->setMonto($request->getAttribute("Monto"));
        $ObjectVO->setFecha($request->getAttribute("Fecha"));
        $ObjectVO->setObservaciones($request->getAttribute("Observaciones"));

        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            $NvoId = $ObjectDAO->create($ObjectVO);
            if ($NvoId > 0) {
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                header("Location: " . $Return . "criteria=ini&Msj=$Msj");
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
            if ($ObjectDAO->update($ObjectVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                header("Location: percepcionese.php?Msj=$Msj");
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en unidades: " . $ex);
    } finally {
        header("Location: $Return");
    }
}
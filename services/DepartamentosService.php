<?php

#Librerias
include_once ('data/DepartamentosDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "departamentos.php?";

$nameVariableSession = "CatalogoCodigosPuestosDetalle"; /* Utilizado en clientesService */

if ($request->hasAttribute("cVarVal")) {
    utils\HTTPUtils::setSessionBiValue($nameVariableSession, "cVarVal", $request->getAttribute("cVarVal"));
}
$ObjectVO = new DepartamentosVO();
$ObjectDAO = new DepartamentosDAO();

$cVarVal = utils\HTTPUtils::getSessionBiValue($nameVariableSession, "cVarVal");

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    try {
        if (is_numeric($request->getAttribute("busca"))) {
            $ObjectVO = $ObjectDAO->retrieve($request->getAttribute("busca"));
        }

        $ObjectVO->setNombre($request->getAttribute("Nombre"));
        $ObjectVO->setDescripcion($request->getAttribute("Descripcion"));
        $ObjectVO->setId_superior($request->getAttribute("Departamento_Sup"));
        $ObjectVO->setId_responsable($request->getAttribute("Encargado"));
        $ObjectVO->setUbicacion($request->getAttribute("Ubicacion"));
        $ObjectVO->setEstatus($request->getAttribute("Estatus"));

        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            $NvoId = $ObjectDAO->create($ObjectVO);
            if ($NvoId > 0) {
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                header("Location: " . $Return . "criteria=ini&Msj=$Msj");
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
            if ($ObjectDAO->update($ObjectVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                header("Location: departamentose.php?Msj=$Msj");
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en unidades: " . $ex);
    } finally {
        header("Location: $Return");
    }
}
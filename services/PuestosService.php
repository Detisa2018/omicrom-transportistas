<?php

#Librerias
include_once ('data/PuestosDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "puestos.php?";

$nameVariableSession = "CatalogoCodigosPuestosDetalle"; /* Utilizado en clientesService */

if ($request->hasAttribute("cVarVal")) {
    utils\HTTPUtils::setSessionBiValue($nameVariableSession, "cVarVal", $request->getAttribute("cVarVal"));
}
$ObjectVO = new PuestosVO();
$ObjectDAO = new PuestosDAO();

$cVarVal = utils\HTTPUtils::getSessionBiValue($nameVariableSession, "cVarVal");

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    try {
        error_log(print_r($request, true));
        if (is_numeric($request->getAttribute("busca"))) {
            $ObjectVO = $ObjectDAO->retrieve($request->getAttribute("busca"));
            error_log("ENTRA Y " . print_r($ObjectVO, true));
        }
        $ObjectVO->setPuesto($request->getAttribute("Puesto"));
        $ObjectVO->setDescripcion($request->getAttribute("Descripcion"));
        $ObjectVO->setId_departamento($request->getAttribute("Departamento"));
        $ObjectVO->setSueldo_base($request->getAttribute("SueldoBase"));
        $ObjectVO->setNivel_salarial($request->getAttribute("NivelSalarial"));
        $ObjectVO->setTipo_contrato($request->getAttribute("TipoContrato"));
        $ObjectVO->setHorario_laboral_entrada($request->getAttribute("Horario_Laboral_Entrada"));
        $ObjectVO->setHorario_laboral_salida($request->getAttribute("Horario_Laboral_Salida"));
        $ObjectVO->setEstatus($request->getAttribute("Status"));
        error_log(print_r($ObjectVO, true));
        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            $NvoId = $ObjectDAO->create($ObjectVO);
            if ($NvoId > 0) {
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                header("Location: " . $Return . "criteria=ini&Msj=$Msj");
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
            if ($ObjectDAO->update($ObjectVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                header("Location: puestose.php?Msj=$Msj");
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en unidades: " . $ex);
    } finally {
        header("Location: $Return");
    }
}
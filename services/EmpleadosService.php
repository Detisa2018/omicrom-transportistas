<?php

#Librerias
include_once ('data/Empleados_nomDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "empleados.php?";

$nameVariableSession = "CatalogoCodigosEmpleadosDetalle"; /* Utilizado en clientesService */

if ($request->hasAttribute("cVarVal")) {
    utils\HTTPUtils::setSessionBiValue($nameVariableSession, "cVarVal", $request->getAttribute("cVarVal"));
}
$EmpleadosVO = new Empleados_nomVO();
$EmpleadosDAO = new Empleados_nomDAO();

$cVarVal = utils\HTTPUtils::getSessionBiValue($nameVariableSession, "cVarVal");

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    try {
        if (is_numeric($request->getAttribute("busca"))) {
            $EmpleadosVO = $EmpleadosDAO->retrieve($request->getAttribute("busca"));
        }

        $EmpleadosVO->setRfc($request->getAttribute("Rfc"));
        $EmpleadosVO->setCurp($request->getAttribute("Curp"));
        $EmpleadosVO->setNombre($request->getAttribute("Nombre"));
        $EmpleadosVO->setImss($request->getAttribute("Imss"));
        $EmpleadosVO->setCuenta_bancaria($request->getAttribute("CuentaBancaria"));
        $EmpleadosVO->setFecha_ingreso($request->getAttribute("FechaIngreso"));
        $EmpleadosVO->setTipo_nomina($request->getAttribute("TipoNomina"));
        $EmpleadosVO->setId_departamento($request->getAttribute("Departamento"));
        $EmpleadosVO->setNo_credencial($request->getAttribute("NoCredencial"));
        $EmpleadosVO->setStatus($request->getAttribute("Status"));
        $EmpleadosVO->setSueldo_diario($request->getAttribute("SueldoDiario"));
        $EmpleadosVO->setSueldo_integrado($request->getAttribute("SueldoIntegrado"));
        $EmpleadosVO->setBaja($request->getAttribute("Baja"));
        $EmpleadosVO->setObservaciones($request->getAttribute("Observaciones"));

        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            $NvoId = $EmpleadosDAO->create($EmpleadosVO);
            if ($NvoId > 0) {
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                header("Location: empleados.php?criteria=ini&Msj=$Msj");
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
            if ($EmpleadosDAO->update($EmpleadosVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                header("Location: empleadose.php?Msj=$Msj");
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en unidades: " . $ex);
    } finally {
        header("Location: $Return");
    }
}
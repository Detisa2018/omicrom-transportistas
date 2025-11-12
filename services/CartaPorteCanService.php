<?php

#Librerias

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "cancartaporte.php?";

if ($request->hasAttribute("busca")) {
    utils\HTTPUtils::setSessionValue("busca", $request->getAttribute("busca"));
    if ($request->hasAttribute("Tipo")) {
        utils\HTTPUtils::setSessionValue("TipoCan", $request->getAttribute("Tipo"));
    }
} 
$TipoCan = utils\HTTPUtils::getSessionValue("TipoCan");

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {

    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_CANCEL) {
            $busca = $sanitize->sanitizeInt("busca");
            $table = $TipoCan == 2 ? "ingresos" : "traslados";
            $ActualizaIng = "UPDATE $table SET stCancelacion = 2,  status = IF(uuid ='-----',3,2) , motivoCan = '02' WHERE id = $busca;";
            error_log($ActualizaIng);
            utils\IConnection::execSql($ActualizaIng);

            $wsdl = FACTENDPOINT;
            error_log($wsdl);
            $client = new nusoap_client($wsdl, true);
            $client->timeout = 180;
            $client->soap_defencoding = 'UTF-8';
            $client->namespaces = array("SOAP-ENV" => "http://schemas.xmlsoap.org/soap/envelope/");
            error_log(print_r($request, true));
            $Ss = "SELECT $table FROM ingresos WHERE id = " . $request->getAttribute("busca");
            $rSs = utils\IConnection::execSql($Ss);

            $parm = "|" . $rSs["uuid"] . "|02||";

            $params = array(
                "uuid" => array($parm)
            );
            $result = $client->call("cancelacion", $params, false, '', '');

            if ($client->fault) {
                error_log(print_r($result, TRUE));
                $Msj = utils\Messages::RESPONSE_ERROR;
            } else {
                $err = $client->getError();
                if ($err) {
                    error_log(print_r($err, TRUE));
                    $Msj = utils\Messages::RESPONSE_ERROR;
                } else {
                    if ($result['return']['canceled'] == "true") {
                        $Msj = "Comprobante Cancelado Exitosamente";
                    } else {
                        $Msj = "Error Cancelando el Comprobante " . $result['return']['error'];
                    }
                }
            }
        } else {
            $Msj = utils\Messages::RESPONSE_PASSWORD_INCORRECT;
        }
        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en facturas: " . $ex);
    } finally {
//        header("Location: $Return");
    }
}

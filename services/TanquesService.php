<?php

#Librerias
include_once ('data/TanqueDAO.php');
include_once ('data/MedidoresDAO.php');
include_once ('data/CombustiblesDAO.php');
include_once ('data/VariablesDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "tanquese.php?";

$tanqueDAO = new TanqueDAO();
$combustibleDAO = new CombustiblesDAO();
$ciaDAO = new CiaDAO();

$MedidoresDAO = new MedidoresDAO();
$MedidoresVO = new MedidoresVO();

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    $objectVO = new TanqueVO();
    $combustibleVO = $combustibleDAO->retrieve($sanitize->sanitizeString("Producto"), "clave");

    $objectVO->setId($sanitize->sanitizeInt("busca"));
    if (is_numeric($objectVO->getId())) {
        $objectVO = $tanqueDAO->retrieve($objectVO->getId());
    }
    $objectVO->setTanque($sanitize->sanitizeInt("Tanque"));
    $objectVO->setProducto($combustibleVO->getDescripcion());
    $objectVO->setClave_producto($combustibleVO->getClave());
    $objectVO->setEstado($sanitize->sanitizeInt("Estado"));
    $objectVO->setCapacidad_total($sanitize->sanitizeString("CapacidadTotal"));
    $objectVO->setVolumen_fondaje($sanitize->sanitizeFloat("Volumen_fondaje"));
    $objectVO->setVolumen_minimo($sanitize->sanitizeFloat("Volumen_minimo"));
    $objectVO->setDescripcion($sanitize->sanitizeString("Descripcion"));

    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
            $Clave_admin = VariablesDAO::getVariable("clave_admin");
            if ($Clave_admin === md5($sanitize->sanitizeString("Clave_Admin"))) {
                if ($tanqueDAO->update($objectVO)) {
                    $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                    BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "ACTUALIZACION DE TANQUE " . $objectVO->getTanque());
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE . "SAT") {
            $objectVO = $tanqueDAO->retrieve($objectVO->getId());
            $objectVO->setPrefijo_sat($sanitize->sanitizeString("Prefijo_sat"));
            $objectVO->setSistema_medicion($sanitize->sanitizeString("Sistema_medicion"));
            $objectVO->setSensor($sanitize->sanitizeString("Sensor"));
            $objectVO->setCapacidad_total($sanitize->sanitizeString("CapacidadTotal"));
            $objectVO->setDescripcion($sanitize->sanitizeString("Descripcion"));
            $objectVO->setIdProveedor($sanitize->sanitizeString("Proveedor"));
            $objectVO->setIdProveedorSesor($sanitize->sanitizeString("ProveedorSensor"));
            $objectVO->setVigencia_calibracion($sanitize->sanitizeString("Calibracion"));
            $Insertidumbre = $sanitize->sanitizeString("Incertidumbre_sensor") / 100;
            $objectVO->setIncertidumbre_sensor($Insertidumbre);
            $objectVO->setVolumen_operativo($sanitize->sanitizeFloat("CapacidadOperativa"));
            if ($tanqueDAO->update($objectVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "ACTUALIZACION DE TANQUE " . $objectVO->getTanque());
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE . "Medidor") {
            $MedidoresVO = new MedidoresVO();
            $MedidoresVO = $MedidoresDAO->fillObject($res);
            $MedidoresVO->setDisp_asociado("TANQ");
            $MedidoresVO->setTipo_medidor($request->getAttribute("tipo_medidor"));
            $MedidoresVO->setVigencia_calibracion($request->getAttribute("vigencia_calibracion"));
            $MedidoresVO->setModelo_medidor($request->getAttribute("modelo_medidor"));
            $IdMedidor = CreamosOEditamos($request->getAttribute("busca"));
            if (is_numeric($IdMedidor)) {
                $MedidoresVO->setId($IdMedidor);
                $MedidoresDAO->updateTanques($MedidoresVO);
            } else {
                $MedidoresVO->setNum_dispensario($request->getAttribute("busca"));
                $MedidoresDAO->create($MedidoresVO);
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en tanques: " . $ex);
    } finally {
        header("Location: $Return");
    }
}
if ($request->getAttribute("Op") === "Download") {
    $archivo = $request->getAttribute("Archivo"); // Ruta relativa o absoluta
    if (file_exists($archivo)) {
        // Forzar cabeceras para descarga
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($archivo) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($archivo));

        // Limpiar el búfer de salida y leer el archivo
        flush();
        readfile($archivo);
        exit;
    } else {
        echo "El archivo no existe.";
    }
} else if ($request->getAttribute("Op") === "Delete") {
    $Update = "UPDATE dictamenes SET id = -id WHERE id = " . $request->getAttribute("IdOrigen");
    utils\IConnection::execSql($Update);
    $Msj = utils\Messages::RESPONSE_VALID_DELETE;
    header("location: tanquese.php?Msj=");
}

function CreamosOEditamos($busca) {
    $Sql = "SELECT id FROM medidores WHERE disp_asociado='TANQ' AND num_dispensario=$busca LIMIT 1;";
    error_log($Sql);
    $vvl = utils\IConnection::execSql($Sql);
    error_log(print_r($vvl, true));
    return $vvl["id"];
}

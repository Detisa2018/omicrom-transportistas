<?php

set_time_limit(720);

include_once ("libnvo/lib.php");
include_once ('com/softcoatl/cfdi/ComprobanteResolver.php');

use com\softcoatl\utils as utils;

$request = utils\Request::instance();
$tipo = $request->get("type");
$id = $request->get("id");
$formato = $request->has("formato") ? $request->get("formato") : "0";

$condition = is_numeric($id) ? "id = {$id}" : "uuid = '{$id}'";
$sql = <<< EOQ
    SELECT 1 tabla, fc.id, fc.uuid FROM fc WHERE fc.{$condition}
    UNION ALL
    SELECT 2 tabla, nc.id, nc.uuid FROM nc WHERE nc.{$condition}
    UNION ALL
    SELECT 3 tabla, p.id, p.uuid FROM pagos p WHERE p.{$condition}
    UNION ALL
    SELECT 5 tabla, t.id, t.uuid FROM traslados t WHERE t.{$condition}
EOQ;
$connection = utils\IConnection::getConnection();
$result = $connection->query($sql);
$myrowsel = $result->fetch_array();
$uuid = $myrowsel["uuid"];

$fsql = "SELECT id_fc_fk id, cfdi_xml xml FROM facturas WHERE uuid = '" . $uuid . "'";
error_log($fsql);
$fresult = $connection->query($fsql);
$frow = $fresult->fetch_array();
error_log(print_r($frow, true));

if (empty($frow["xml"])) { // Si no existe el registro en facturas

    // Carga los archivos de disco duro
    error_log("No existe el registro, carga archivo {$uuid}.xml de HD");
    if (!file_exists("/var/www/html/omicrom/fae/archivos/" . $uuid . ".xml")) {
        error_log("No existe archivo XML");
        echo "Error : No existe archivo XML";
        exit();
    }

    $xml = file_get_contents("/var/www/html/omicrom/fae/archivos/" . $uuid . ".xml");
    error_log($xml);
    if (empty($frow["id"])) {
        $insert = "INSERT INTO facturas (id_fc_fk, version, cfdi_xml, fecha_emision, fecha_timbrado, clave_pac, emisor, receptor, uuid, tabla) "
                  . "SELECT fc.id, '4.0', '', fc.fecha, fc.fecha, 'SIFEI', cia.rfc, cli.rfc, fc.uuid, ? "
                  . "FROM fc "
                  . "JOIN cia ON TRUE "
                  . "JOIN cli ON cli.id = fc.cliente "
                  . "WHERE fc.uuid = ?";
        error_log("Creando registro en facturas");
        if (($stmt = $connection->prepare($insert))) {
            $stmt->bind_param("is", $myrowsel["tabla"], $uuid);
            $stmt->execute();
            error_log($stmt->error);
        }
    }

    error_log("Cargando xml de HD");
    $update = "UPDATE facturas SET cfdi_xml = LOAD_FILE( '/var/www/html/omicrom/fae/archivos/" . $uuid . ".xml' ) WHERE uuid = '" . $uuid . "'";
    error_log($update);
    $connection->query($update);
    error_log($connection->error);
} else {
    error_log("Existe el registro. Carga archivos de BD");
    $xml = $frow['xml'];
}

if ($tipo === 'pdf') {

    $wsdl = FACTENDPOINT;
    $client = new nusoap_client($wsdl, true);
    $client->timeout = 720;
    $client->response_timeout = 720;
    $client->soap_defencoding = 'UTF-8';
    $client->namespaces = array("SOAP-ENV" => "http://schemas.xmlsoap.org/soap/envelope/");

    $formato = $formato == 1 ? "TC" : "A1";
    $params = array(
        "uuid" => $uuid,
        "formato" => $formato
    );

    $message = "generaPDFFile";
    try {
        $response = $client->call($message, $params);
        if (empty($client->getError())) {
            $pdf = base64_decode($response["return"]);
            error_log("****************** Se ha generado el PDF con el nuevo método*****************");
        } else {
            throw new Exception("Sin acceso al servidor de facturación.");
        }
        error_log("Enviando PDF");
        header("Content-Description: File Transfer");
        header("Content-Type: application/pdf");
        header("Content-Disposition: inline; filename=" . $uuid . ".pdf");
        header("Content-Length: " . strlen(bin2hex($pdf)) / 2);
        header("Expires: 0");
        header("Cache-Control: must-revalidate");
        header("Pragma: public");
        ob_clean();
        echo $pdf;
        exit();
    } catch (Exception $e) {
        echo "Error : " . $e->getMessage();
        exit();
    }
} else {

    error_log("Enviando XML");
    header("Content-Description: File Transfer");
    header("Content-Type: application/xml");
    header("Content-Disposition: attachment; filename=" . $uuid . ".xml");
    header("Content-Length: " . strlen($xml));
    header("Expires: 0");
    header("Cache-Control: must-revalidate");
    header("Pragma: public");
    ob_clean();
    echo $xml;
    exit();
}


<?php

include_once ("libnvo/lib.php");

use com\softcoatl\cfdi\v33\schema\Comprobante as Comprobante;
use com\softcoatl\utils as utils;

$mysqli = iconnect();

if (move_uploaded_file($_FILES["file"]["tmp_name"][0], "/home/omicrom/xml/" . $_FILES["file"]["name"][0])) {

    $nombreA = "/home/omicrom/xml/" . $_FILES["file"]["name"][0] . "";

    $carga_xml = simplexml_load_file($nombreA); //Obtenemos los datos del xml agregados

    $InsertInto = "INSERT INTO dictamenes (direccion,tabla,id_tabla) VALUES ('$nombreA','" . $_REQUEST["Origen"] . "'," . $_REQUEST["busca"] . ");";
    utils\IConnection::execSql($InsertInto);
    if (!$carga_xml) {
        $location = "/home/omicrom/xml/archivo.pdf";
        unlink($location);
        $fh = fopen($nombreA, 'r+') or die("Ocurrio un error al abrir el archivo");
        $texto = fgets($fh);
        $archivo = fopen($location, 'a');
        $string = mb_substr($texto, 0, 15);
        $cadena = utf8_decode($texto);
        fputs($archivo, $cadena);
        fclose($archivo);
        $carga_xml = simplexml_load_file($location);
    }
}
?>
<?php
session_start();

include_once ("check_report.php");
include_once ("libnvo/lib.php");
include_once ("phpqrcode/qrlib.php");
include_once ("data/SysFilesDAO.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$mysqli = iconnect();

$busca = $request->getAttribute("busca");

$ciaDAO = new CiaDAO();
$sysFilesDAO = new SysFilesDAO();
$sysFilesVO = $sysFilesDAO->retrieve("fc_img");
$usuarioSesion = getSessionUsuario();

$ciaVO = $ciaDAO->retrieve(1);
$logo = $sysFilesVO->getFile();
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports_print.php'; ?> 
        <!--        <link rel="stylesheet" href="js/jquery-ui.css">
                <script type="text/14javascript" src="js/jquery-ui.js"></script>-->
        <link rel="stylesheet" href="js/jquery-ui.css">
        <script src="js/jquery-ui.js"></script>
        <title><?= $Gcia ?></title>         
    </head>
    <body>
        <input type="submit" name="Boton" class="nombre_cliente" value="Imprimir" id="Imprimir" onclick="print()">
        <div align="center" class="text" style="align-items: flex-start">
            <img src="phpbarcode/barcode.php?f=svg&s=code128a&d=<?= $request->getAttribute("busca"); ?>&w=1120&h=200&pt=25&pb=40&ts=20&ls=1" alt=""/>
        </div>
    </body>
</html>
<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("importeletras.php");
include_once ("data/AuthSemestralDAO.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$usuarioSesion = getSessionUsuario();

if ($request->hasAttribute("buscaNv")) {
    utils\HTTPUtils::setSessionValue("buscaNv", $request->getAttribute("buscaNv"));
}

$busca = utils\HTTPUtils::getSessionValue("buscaNv");

$AuthSemestralDAO = new AuthSemestralDAO();
$AuthSemestralVO = new AuthSemestralVO();

$AuthSemestralVO = $AuthSemestralDAO->retrieve($busca);
$AuthU = "SELECT name FROM authuser WHERE id = " . $AuthSemestralVO->getId_authuser();
$RsName = utils\IConnection::execSql($AuthU);
$ciaDAO = new CiaDAO();
$ciaVO = $ciaDAO->retrieve(1);

$Titulo = "Anailisis de usuarios ";
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports_print.php'; ?> 
        <title><?= $Gcia ?></title>
        <style>
            @page {
                size: A4 /*landscape*/;
            }
        </style>
        <script type="text/javascript">
            function Export2Doc(element, filename = '') {

                var preHtml = "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'><head><meta charset='utf-8'><title>Export HTML To Doc</title></head><body>";
                var postHtml = "</body></html>";

                var html = preHtml + document.getElementById(element).innerHTML + postHtml;

                var blob = new Blob(['\ufeff', html], {
                    type: 'application/msword'
                });

                var url = 'data:application/vnd.ms-word;charset=utf-8,' + encodeURIComponent(html);


                filename = filename ? filename + '.doc' : 'document.doc';


                var downloadLink = document.createElement("a");

                document.body.appendChild(downloadLink);

                if (navigator.msSaveOrOpenBlob) {
                    navigator.msSaveOrOpenBlob(blob, filename);
                } else {

                    downloadLink.href = url;
                    downloadLink.download = filename;
                    downloadLink.click();
                }

                document.body.removeChild(downloadLink);
            }
        </script>

    </head>

    <!-- Set "A5", "A4" or "A3" for class name -->
    <!-- Set also "landscape" if you need -->
    <body class="A5">
        <div class="iconos">
            <table aria-hidden="true">
                <tr>
                    <td style="text-align: left"><?= $Titulo ?></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="text-align: center">
                        <em onclick="print();" class='icon fa fa-lg fa-print' aria-hidden="true" title="Descargar tipo PDF"></em>
                    </td>
                </tr>
            </table>
        </div>
        <!-- Each sheet element should have the class "sheet" -->
        <!-- "padding-**mm" is optional: you can set 10, 15, 20 or 25 -->
        <div class="sheet padding-10mm">

            <?php nuevoEncabezadoPrint(null) ?>
            <div id="exportContent">
                <?php
                $output = "/home/omicrom/xml/respuesta.txt";
                $command = "sudo dmidecode -t system | grep Serial | cut -d' ' -f3 > $output";
                exec($command);
                $txt_file = fopen('/home/omicrom/xml/respuesta.txt', 'r');
                while ($line = fgets($txt_file)) {
                    $Cervidor = $line;
                }
                fclose($txt_file);
                ?>
                <table style="width: 100%;" aria-hidden="true">
                    <tr><td style="font-weight: bold;text-align: right;"><em>Asunto: Aviso de sistema de monitoreo y control a distancia</em></td></tr>
                    <tr><td style="font-weight: bold;"><em>Revisión semestral</em></td></tr>
                    <tr>
                        <td>
                            <table style="font-size: 20px;width: 100%;margin-top: 20px;margin-bottom: 20px;">
                                <tr>
                                    <td><strong>Id :</strong> <?= $AuthSemestralVO->getId() ?></td>
                                    <td><strong>Fecha :</strong> <?= $AuthSemestralVO->getFecha() ?></td>
                                    <td><strong>Descripcion :</strong> <?= $AuthSemestralVO->getDescripcion() ?></td>
                                </tr>
                                <tr>
                                    <td colspan="2"><strong>Usuario :</strong> <?= $RsName["name"] ?></td>
                                    <td><strong>Status :</strong><?= $AuthSemestralVO->getStatus() ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php
                            $AuthSemestral = "SELECT a.id,a.name,IF(asr.status_anterior = 'false','Inactivo','Activo') anterior,IF(asr.status_actual='false','Inactivo','Activo') Actual "
                                    . "FROM auth_semestral_resultados asr LEFT JOIN authuser a ON a.id=asr.id_authuser WHERE id_auth_semestral = " . $busca;
                            $rsAuth = utils\IConnection::getRowsFromQuery($AuthSemestral);
                            ?>
                            <div id="TablaDatos" style="min-height: 80px !important;">
                                <table class="paginador CtShow" aria-hidden="true"  style="max-height: 100px !important;width: 50%;margin-left: 25%;border: 1px solid black;border-radius: 15px;padding: 15px;">
                                    <thead>
                                        <tr>
                                            <th style="font-size: 20px; border-radius: 10px;" colspan="3" title="Solo se muestran cortes sin cerrar">Modificaciónes</th>
                                        </tr>
                                        <tr>
                                            <th style="width: 30%;">Nombre</th>
                                            <th style="width: 30%">Status Anterior</th>
                                            <th style="width: 20%;">Status Actual</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $e = 0;
                                        foreach ($rsAuth as $rs) {
                                            $e++;
                                            $HtmlColor = $e % 2 == 0 ? " style='background-color:#CCD1D1'" : "";
                                            ?>
                                            <tr <?= $HtmlColor ?>>
                                                <td><?= $rs["name"] ?></td>
                                                <td><?= $rs["anterior"] ?></td>
                                                <td><?= $rs["Actual"] ?></td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                    </tbody>
                                </table>
                                <table class="paginador CtShow" aria-hidden="true"  style="max-height: 100px !important;width: 50%;margin-left: 25%;border: 1px solid black;border-radius: 15px;margin-top: 20px;padding: 15px;">
                                    <thead>
                                        <tr>
                                            <th style="font-size: 20px; border-radius: 10px;" colspan="3" title="Usuarios con los mismos status">Sin modificaciónes</th>
                                        </tr>
                                        <tr>
                                            <th style="width: 30%;">Nombre</th>
                                            <th style="width: 30%">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $SqlAuthuser = "SELECT id,name,uname,team,status FROM authuser WHERE team <= 8 AND groupwork=0 AND "
                                                . "id NOT IN (SELECT asr.id_authuser "
                                                . "FROM auth_semestral_resultados asr WHERE id_auth_semestral = " . $busca . ");";
                                        $RsAuth = utils\IConnection::getRowsFromQuery($SqlAuthuser);
                                        foreach ($RsAuth as $rs) {
                                            $e++;
                                            $HtmlColor = $e % 2 == 0 ? " style='background-color:#CCD1D1'" : "";
                                            ?>
                                            <tr <?= $HtmlColor ?>>
                                                <td><?= $rs["name"] ?></td>
                                                <td><?= $rs["status"] ?></td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                    </tbody>
                                </table>
                        </td>
                    </tr>
                </table>

            </div>
        </div>
    </body>
    <style>
        .StyleTable{
            width: 100%;
            border: 1px solid #606c84;
            border-radius: 0px 0px 20px 20px;
        }
        .StyleTable tr:nth-child(2){
            background-color: #CCD1D1;
        }
        .StyleTable tr:nth-child(1){
            background-color: #CCD1D1;
        }

    </style>
</html>     


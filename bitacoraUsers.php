<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$nameSession = "catalogoBitacoraAuthuser";
$arrayFilter = array();
$session = new OmicromSession("ase.id", "ase.id", $nameSession, $arrayFilter, "locked");

foreach ($arrayFilter as $key => $value) {
    ${$key} = utils\HTTPUtils::getSessionBiValue($nameSession, $key);
}

$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Id = 165;
$Titulo = "Catálogo de bitacora semestral";

$paginador = new Paginador($Id,
        "",
        "",
        "",
        $conditions,
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "REGEXP",
        "");

$self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
$cLink = substr($self, 0, strrpos($self, ".")) . 'e.php';
$cLinkd = substr($self, 0, strrpos($self, ".")) . 'd.php';

require_once './services/UsuariosService.php';
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#autocomplete").focus();
            });
        </script>
        <?php $paginador->script(); ?>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <div id="TablaDatos">
            <table class="paginador" aria-hidden="true">
                <?php
                echo $paginador->headers(array("Detalle", "Movimientos", "PDF"), array());
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>
                        <td style="text-align: center;"><a href="<?= $cLink ?>?busca=<?= $row["id"] ?>"><i class="icon fa fa-lg fa-edit" aria-hidden="true"></i></a></td>   
                        <td style="text-align: center;"><a href="<?= $cLinkd ?>?busca=<?= $row["id"] ?>"><i class="fa-solid fa-file-lines"></i></a></td>
                        <td style="text-align: center;"><a href=javascript:winuni("resultadoAnalisisUsuarios.php?buscaNv=<?= $row["id"] ?>")><i class="fa-solid fa-file"></i></a></td>
                                <?php echo $paginador->formatRow(); ?>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>
        <?php
        $nLink = array("<i class=\"icon fa fa-lg fa-arrow-circle-left\" aria-hidden=\"true\"></i> Regresar" => $self . "?criteria=ini", "<i class='fa-regular fa-file'></i> Reporte " => "javascript:winuni('reporteUsuarios.php');");
        $reload = false;
        echo $paginador->footer($usuarioSesion->getTeam() === UsuarioPerfilDAO::PERFIL_ADMIN && $locked == 0, $nLink, false, $reload);
        echo $paginador->filter();
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>

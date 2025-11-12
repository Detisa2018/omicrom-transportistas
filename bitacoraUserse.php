<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("data/AuthSemestralDAO.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$self = utils\HTTPUtils::self();

$Titulo = "Detalle de usuario";
if ($request->hasAttribute("busca")) {
    utils\HTTPUtils::setSessionValue("busca", $request->getAttribute("busca"));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue("busca", $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue("busca");

require_once './services/BitacoraUserService.php';
$AuthSemestralDAO = new AuthSemestralDAO();
$AuthSemestralVO = new AuthSemestralVO();

if (is_numeric($busca)) {
    $AuthSemestralVO = $AuthSemestralDAO->retrieve($busca);
} else {
    $AuthSemestralVO->setId(0);
    $AuthSemestralVO->setStatus("Abierta");
    $AuthSemestralVO->setFecha(date("Y-m-d"));
}
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script type="text/javascript">
            $(document).ready(function () {
                $("#Id").val("<?= $AuthSemestralVO->getId() ?>");
                $("#Fecha").val("<?= $AuthSemestralVO->getFecha() ?>");
                $("#Descripcion").val("<?= $AuthSemestralVO->getDescripcion() ?>");
                $("#Status").val("<?= $AuthSemestralVO->getStatus() ?>");
            });
        </script>
        <script type="text/javascript" src="js/js-usuarios.js"></script>
    </head>
    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="bitacoraUsers.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">
                    <div id="FormulariosBoots">
                        <div class="container no-margin">
                            <div class="row no-padding">
                                <div class="col-12 background container no-margin">
                                    <form name="formulario1" id="formulario1" method="post" action="">
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Id: </div>
                                            <div class="col-1">
                                                <input type="number" name="Id" id="Id" class="clase-<?= $clase1 ?>" disabled/>
                                            </div>
                                            <div class="col-5"></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Fecha: </div>
                                            <div class="col-3">
                                                <input type="date" name="Fecha" id="Fecha" class="clase-<?= $clase1 ?>" />
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Descripcion: </div>
                                            <div class="col-5">
                                                <input type="text" name="Descripcion" id="Descripcion" class="clase-<?= $clase1 ?>"/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Activo: </div>
                                            <div class="col-3">
                                                <select name='Status' id='Status' class='texto_tablas'>
                                                    <option value='Abierta'>Abierta</option>
                                                    <option value='Cerrada'>Cerrada</option>
                                                    <option value='Cancelada'>Cancelada</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right"></div>
                                            <div class="col-4">                                               
                                                <?php
                                                if ($AuthSemestralVO->getStatus() !== "Cerrada") {
                                                    ?>
                                                    <input type="submit" name="Boton" id="Boton" value="<?= $ValButton = $busca > 0 ? utils\Messages::OP_UPDATE : utils\Messages::OP_ADD ?>" class="clase-<?= $clase1 ?>"/>
                                                    <?php
                                                }
                                                ?>
                                            </div>
                                            <div class="col-3 align-right"></div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
    </body>
</html> 

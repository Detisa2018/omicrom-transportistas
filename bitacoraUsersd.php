<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("data/MensajesDAO.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$usuarioSesion = getSessionUsuario();
if ($request->hasAttribute("busca")) {
    utils\HTTPUtils::setSessionValue("busca", $request->getAttribute("busca"));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue("busca", $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue("busca");

$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));
$Titulo = "Detalle del analisis";
$Id = 5;

if ($request->getAttribute("Boton") === "Cerrar Analisis") {
    $UpdateSts = "UPDATE auth_semestral SET status = 'Cerrada' WHERE id = $busca";
    utils\IConnection::execSql($UpdateSts);
}

$sqlSts = "SELECT status FROM omicrom.auth_semestral WHERE id = $busca;";
$RsS = utils\IConnection::execSql($sqlSts);

$SqlAuthuser = "SELECT id,name,uname,team,status FROM authuser WHERE team <= 8 AND groupwork=0;";
$RsAuth = utils\IConnection::getRowsFromQuery($SqlAuthuser);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php include './config_omicrom.php'; ?> 
        <title><?= $Gcia ?></title>
        <script type="text/javascript">
            $(document).ready(function () {
                $(".botonAnimatedGreen").click(function () {
                    var idUser = $(this).data('idauth');
                    var statusCheck = $(this).prop("checked");
                    console.log("Sts " + statusCheck);
                    jQuery.ajax({
                        type: "POST",
                        url: "getByAjax.php",
                        dataType: "json",
                        cache: false,
                        data: {"Op": "RegistraBitacoraSemestral", "id_Bitacora": "<?= $busca ?>", "id_authuser": idUser, "Usr": "<?= $usuarioSesion->getId() ?>", "Status": statusCheck},
                        success: function (data) {

                        }
                    });
                });
            });
        </script>
    </head>

    <body>
        <?php BordeSuperior(); ?>
        <table style="width: 100%;">
            <tr>
                <td style="width: 10%;vertical-align: top;padding-top: 300px;">
                    <a href="bitacoraUsers.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>Regresar
                </td>
                <td>
                    <?php
                    if ($RsS["status"] === "Abierta") {
                        ?>
                        <form>
                            <div style="width: 100%;text-align: right;height: 35px;padding-right: 15px;" class="nombre_cliente">
                                <input type="submit" name="Boton" value="Cerrar Analisis">
                            </div>
                        </form>
                        <?php
                    }
                    ?>

                    <div id="TablaDatos" style="min-height: 80px !important;">
                        <table class="paginador CtShow" aria-hidden="true"  style="max-height: 100px !important;min-width: 100%;">
                            <thead>
                                <tr>
                                    <th style="font-size: 20px; border-radius: 10px;" colspan="4" title="Solo se muestran cortes sin cerrar">Detalle de Analisis id <?= $busca ?></th>
                                </tr>
                                <tr>
                                    <th style="width: 30%;">Nombre</th>
                                    <th style="width: 30%">Usuario</th>
                                    <th style="width: 20%;">Equipo</th>
                                    <?php
                                    if ($RsS["status"] === "Abierta") {
                                        ?>
                                        <th style="width: 10%"></th>
                                        <?php
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($RsAuth as $ct) {
                                    $Sts = $ct["status"] === "active" ? "checked='true'" : "";
                                    ?>
                                    <tr>
                                        <td style="padding-left: 15px;"><?= $ct["name"] ?></td>
                                        <td style="padding-left: 15px;"><?= $ct["uname"] ?></td>
                                        <td style="padding-left: 15px;"><?= $ct["team"] ?></td>
                                        <?php
                                        if ($RsS["status"] === "Abierta") {
                                            ?>
                                            <td style="text-align: center;"><input type="checkbox" <?= $Sts ?> name="Status" data-idauth="<?= $ct["id"] ?>" class="botonAnimatedGreen"></td>
                                                <?php
                                            }
                                            ?>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
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

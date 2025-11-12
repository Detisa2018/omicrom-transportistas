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

$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));
$Titulo = "Captura de pipas, vía XML";
$Id = 5;
if ($request->getAttribute("creacion") === "Automatica") {
    sleep(4);
    $SqlCarga = "SELECT carga FROM me_tmp WHERE usuario = " . $usuarioSesion->getId() . " order by id desc limit 1";
    $Sc = utils\IConnection::execSql($SqlCarga);
    header("location: entradased.php?carga=" . $Sc["carga"]);
}
if ($request->hasAttribute("op")) {
    if ($request->getAttribute("op") === "st") {
        $sql = "SELECT * FROM msj WHERE tipo = '" . TipoMensaje::SIN_LEER . "' AND DATE_ADD(fecha,INTERVAL vigencia DAY) >= CURRENT_DATE()";
        $registros = utils\IConnection::getRowsFromQuery($sql);
        $numRegistros = count($registros);
        if ($numRegistros == 0) {
            header("Location: servicio.php");
        } else {
            header("Location: servicio.php?pop=1");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php include './config_omicrom.php'; ?> 
        <title><?= $Gcia ?></title>      
        <script src="dropzone/min/dropzone.min.js"></script>
        <script type="text/javascript">
            $(document).ready(function () {
                Dropzone.prototype.defaultOptions.dictDefaultMessage = "Arrastrar o dar click para subir archivo XML";
                Dropzone.options.myDrop = {
                    url: "uploadTotal.php?busca=<?= $carga ?>&Cliente=<?= $usuarioSesion->getId() ?>&Location=" + $("#CheckLocation").val(),
                    uploadMultiple: true,
                    maxFileSize: 3,
                    acceptedFiles: ".xml",
                    init: function init() {
                        this.on("addedfile", function () {
                            setTimeout(function () {
                                window.location.href = 'pipasporxml.php?creacion=Automatica';
                            }, 800);
                        });
                    }
                }
            });
        </script>
    </head>

    <body>
        <?php BordeSuperior(); ?>

        <div class="container show-dropzone">
            <div class="row no-padding">
                <div class="col-lg-12">
                    <div class="btn-group w-100">
                        <form class="dropzone" id="myDrop" enctype="multipart/form-data">
                            <div class="fallback">
                                <input type="file" name="file" id="myId" multiple>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
    </body>
</html>

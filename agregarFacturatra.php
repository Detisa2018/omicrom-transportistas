<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("data/MensajesDAO.php");
include_once ('./comboBoxes.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$usuarioSesion = getSessionUsuario();

$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));
$Titulo = "Lectura XML Facturación Traslado";
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php include './config_omicrom.php'; ?> 
        <title><?= $Gcia ?></title>
        <script type="text/javascript">

        </script>
    </head>

    <body>
        <?php BordeSuperior(); ?>

        <div class="texto_tablas" align="center"><?= $Msj ?></div>
        <table style="width: 100%;border: 1px solid black;min-height: 400px;border-radius: 15px;background-color: #E5E5E5">
            <tr>
                <td style="text-align: center; width: 80px;" class="nombre_cliente">
                    <a href="traslados.php"><div class="RegresarCss " alt="Flecha regresar" style="">Regresar</div></a>
                </td>
                <td>
                    <table style="width: 80%;margin-left: 10%;border: 2px solid #099;padding: 0px 0px 20px 0px;border-radius: 10px;background-color: #ECFEFF;">
                        <tr><td class="styleBotonSubmit" colspan="4">Carga de Facturas con Carta Porte, para generar información hacia el XML mensual</td></tr>
                        <tr style="height: 10px;">
                            <td style="font-size: 18px; font-family: sans-serif; width: 60%;padding-left: 15px;vertical-align: top;padding-top: 10px;">
                                Terminal de Almacenamiento :<sup style="color: #A1A1A1">Origen</sup>
                                <br><?php ComboboxCatalogoUniversal::generate("Terminal", "TERMINALES_ALMACENAMIENTO", "450px;", "", "SELECCIONE UNA TERMINAL DE ALMACENAMIENTO"); ?>
                                <br>
                                <br>
                                Cliente :<sup style="color: #A1A1A1;">Destino</sup> <br>
                                <div style="position: relative;">
                                    <input type="search" style="width: 450px;" class="texto_tablas" name="ClienteS" id="autocomplete" placeholder="Buscar cliente" required>
                                </div>
                                <div id="autocomplete-suggestions"></div><br>
                            </td>
                            </td>
                            <td>
                                <table style="width: 90%;margin-left: 5%;height: 30px !important;">
                                    <tr>
                                        <td>
                                            <div class="container show-dropzone">
                                                <div class="row no-padding">
                                                    <div class="col-lg-12">
                                                        <div class="btn-group w-100">
                                                            <form class="dropzone" id="myDrop" enctype="multipart/form-data" style="width: 100% !important;height: 40px !important;border-radius: 10px;">
                                                                <div class="fallback">
                                                                    <input type="file" name="file" id="myId" multiple>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
    </body>
</html>
<script src="dropzone/min/dropzone.min.js"></script>
<script>
            $(document).ready(function () {
                Dropzone.prototype.defaultOptions.dictDefaultMessage = "Subir archivo XML " + '<i class="fa-solid fa-download"></i>';
                Dropzone.options.myDrop = {
                    url: "uploadTra.php",
                    uploadMultiple: true,
                    maxFileSize: 3,
                    acceptedFiles: ".xml",
                    params: function () {
                        return {
                            Cliente: $("#autocomplete").val(),
                            Terminal: $("#Terminal").val()
                        };
                    },
                    init: function init() {
                        this.on("addedfile", function () {
                            setTimeout(function () {
                                window.location.href = "traslados.php?criteria=ini&tipo=" + <?= utils\HTTPUtils::getSessionObject("Tipo") ?> + "&Iniciamos=Si";
                            }, 800);
                        });
                    }
                }
                $("#autocomplete").activeComboBox(
                        $("[name='form1']"),
                        "SELECT data, value FROM (SELECT id as data, CONCAT(id, ' | ', tipodepago, ' | ', nombre) value FROM cli " +
                        "WHERE TRUE AND cli.tipodepago NOT REGEXP 'Puntos' AND id >= 10) sub WHERE TRUE",
                        "value"
                        );
                $('#autocomplete').focus();
            });
</script>

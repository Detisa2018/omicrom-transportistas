<?php
#Librerias
session_start();
set_time_limit(300);

include_once ("auth.php");
include_once ("authconfig.php");
include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$mysqli = iconnect();

$arrayFilter = array("Fecha" => date("Y-m-d"), "Disponible" => "N", "Corte" => "",
    "Turno" => "*", "Posicion" => "*", "Producto" => "*");
$nameSession = "catalogoRemisiones";
$session = new OmicromSession("i.id", "i.id", $nameSession, $arrayFilter, "Filtros");
$usuarioSesion = getSessionUsuario();

/**
 * Valida las busquedas desde el visor de posiciones    
 */
if ($request->hasAttribute("Servicio")) {
    utils\HTTPUtils::setSessionBiValue($nameSession, "Posicion", $request->getAttribute("Posicion"));
}

foreach ($arrayFilter as $key => $value) {
    ${$key} = utils\HTTPUtils::getSessionBiValue($nameSession, $key);
}

$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Id = 165;
$Titulo = "Traslados";

$conditions = "";
$paginador = new Paginador($Id,
        "i.uuid ",
        "",
        "",
        $conditions,
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "REGEXP",
        "",
        "");

$self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
$cLink = substr($self, 0, strrpos($self, ".")) . 'e.php';
$cLinkd = substr($self, 0, strrpos($self, ".")) . 'd.php';

$VC1 = utils\IConnection::execSql("SELECT valor FROM variables_corporativo WHERE llave = 'url_fact_online';");
$VC2 = utils\IConnection::execSql("SELECT valor FROM variables_corporativo WHERE llave = 'fact_online_omicrom';");
$VC3 = utils\IConnection::execSql("SELECT valor FROM variables_corporativo WHERE llave = 'uso_corporativo';");

include_once './services/RemisionesService.php';
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {

                $("#Fecha").val("<?= $Fecha ?>").attr("size", "8").addClass("texto_tablas");
                $("#cFecha").css("cursor", "hand").click(function () {
                    displayCalendar($("#Fecha")[0], "yyyy-mm-dd", $(this)[0]);
                    $("#Corte").val("");
                });

                $("#Posicion").val("<?= $Posicion ?>").addClass("texto_tablas");
                $("#Producto").val("<?= $Producto ?>").addClass("texto_tablas");
                $("#Turno").val("<?= $Turno ?>").addClass("texto_tablas");
                $("#Corte").val("<?= $Corte ?>").addClass("texto_tablas");

                var Disponible = "<?= $Disponible ?>";
                if (Disponible === "S") {
                    $("#DisponibleS").prop("checked", true);
                    $("#DisponibleN").prop("checked", false);
                } else {
                    $("#DisponibleN").prop("checked", true);
                    $("#DisponibleS").prop("checked", false);
                }

                $("#Fecha").focus(function () {
                    $("#Corte").val("");
                });

                $("#Corte").focus(function () {
                    $("#Fecha").val("");
                });

                $("#form1").submit(function (event) {
                    if ($("#Fecha").val() !== "" || $("#Corte").val() !== "") {
                        //$("#message").text("Validated...").show();
                        return;
                    }

                    if ($("#Fecha").val() === "" && $("#Corte").val() === "") {
                        $("#message").text("Seleccione un corte o asigne una fecha!").show().fadeOut(3000);
                    }

                    event.preventDefault();
                });
            });
            function winminls(url) {
                windowMin = window.open(url, "miniwin", "width=460,height=500,left=200,top=120,location=no");
            }
            function winunils(url) {
                windowUni = window.open(url, "filtros", "status=no,tollbar=yes,scrollbars=yes,menubar=no,width=790,height=550,left=250,top=80");
            }
        </script>
        <?php $paginador->script(); ?>
    </head>

    <body>

        <?php BordeSuperior(); ?>
        <input  type="hidden" name="NameUser" id="NameUser" value="<?= $usuarioSesion->getNombre() ?>">
        <div id="TablaDatos">
            <table class="paginador" aria-hidden="true">
                <?php
                $VcSc = utils\IConnection::execSql("SELECT valor FROM variables_corporativo WHERE llave = 'ServicioComercial';");
                echo $paginador->headers(array(""), array(" "));
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>
                        <td style="text-align: center;"> 
                            <?php if ($row['uuid'] != "") { ?>
                                <a style="color: red;" href="javascript:winuni('enviafile.php?id=<?= $row['uuid'] ?>&type=pdf&formato=0')"><i class="icon fa fa-lg fa-file-pdf-o" title="Obtener PDF Tamaño Carta" aria-hidden="true"></i></a>
                            <?php } ?>
                        </td>
                        <?php echo $paginador->formatRow(); ?>
                        <td><?= $row["descuento"] ?></td>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>

        <?php
        $data = array("Nombre" => $Titulo, "Reporte" => 165,
            "Fecha" => $Fecha, "Corte" => $Corte,
            "Posicion" => $Posicion, "Producto" => $Producto,
            "Turno" => $Turno, "Disponible" => $Disponible,
            "busca" => $busca, "Criterio" => $session->getSessionAttribute("criteriaField"));
        $nLink = array("<i class=\"icon fa fa-lg fa-download\" aria-hidden=\"true\"></i> Exportar" => "report_excel.php?" . http_build_query($data));
        $GroupWork = utils\IConnection::execSql("SELECT groupwork FROM authuser WHERE id = " . $usuarioSesion->getIdUsuario());
        echo $paginador->footer(false, $nLink, false, true);
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
        <link rel="stylesheet" href="bootstrap/bootstrap-4.0.0/dist/css/bootstrap-modal.css" type="text/css">

        <?php include_once ("./bootstrap/modals/AjusteTicket.html"); ?>

        <script src="./bootstrap/controller/utils.js"></script>
        <script src="./bootstrap/controller/ajuste.js?var=<?= md5("bootstrap/controller/ajuste.js") ?>"></script>
    </body>
</html>

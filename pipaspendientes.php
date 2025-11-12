<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once('data/V_CorporativoDAO.php');
include_once('data/CargasDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();

$session = new OmicromSession("cargas.id", "cargas.id");
$cVarVal = 'day_pipe_capture';

$pipeDAO = new V_CorporativoDAO();
$pipeVO = $pipeDAO->retrieve($cVarVal)->getValor();

if (empty($pipeVO)) {
    $pipeVO = 45;
} else {
    $pipeVO;
}

$CiaDAO = new CiaDAO();
$CiaVO = new CiaVO();
$CiaVO = $CiaDAO->retrieve(true);
$Sc = 'SELECT valor FROM variables_corporativo where llave = "ServicioComercial"';
$Rssc = utils\IConnection::execSql($Sc);
if ($Rssc["valor"] == 1) {
    utils\IConnection::execSql("truncate table me_tmp;");
    utils\IConnection::execSql("truncate table med_tmp;");
}


$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Id = 41;
$Titulo = "Recepciones pendientes de documentar";

$paginador = new Paginador($Id,
        "cargas.clave_producto,getUMedida(com.cve_producto_sat,com.cve_sub_producto_sat) um,vol_doc ",
        "",
        "",
        "cargas.clave_producto = com.clave AND cargas.entrada = 0 ",
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "REGEXP",
        "");

$self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
$cLink = 'entradase.php';
$cLinkd = substr($self, 0, strrpos($self, ".")) . 'd.php';
$FechaActual = date('Y-m-d', strtotime('-' . $pipeVO . 'day', strtotime(date("Y-m-d"))));
if ($request->getAttribute("op") === "AddCarga") {
    error_log("Creando descarga");
    $CargasDAO = new CargasDAO();
    $CargasVO = new CargasVO();
    $FechaGeneral = date("Y-m-d H:i:s");
    $Com = "SELECT com.clave,com.clavei,com.ieps,t.tanque,com.descripcion FROM inv LEFT JOIN com ON com.descripcion=inv.descripcion LEFT JOIN tanques t
                ON t.producto=com.descripcion WHERE com.clavei = '" . $request->getAttribute('Producto') . "';";
    $ComDt = utils\IConnection::execSql($Com);
    $VolumenG = 0;
    $CargasVO->setTanque($ComDt["tanque"]);
    $CargasVO->setProducto($ComDt["descripcion"]);
    $CargasVO->setClave_producto($ComDt["clave"]);
    $CargasVO->setT_inicial(0);
    $CargasVO->setVol_inicial(0);
    $CargasVO->setFecha_inicio($FechaGeneral);
    $CargasVO->setT_final(0);
    $CargasVO->setFecha_fin($FechaGeneral);
    $CargasVO->setAumento($VolumenG);
    $CargasVO->setVol_final($VolumenG);
    $CargasVO->setEntrada(0);
    $CargasVO->setInicia_carga($FechaGeneral);
    $CargasVO->setFinaliza_carga($FechaGeneral);
    $CargasVO->setFecha_insercion($FechaGeneral);
    $CargasVO->setTipo(0);
    $CargasVO->setFolioenvios(0);
    $CargasVO->setEnviado(0);
    $CargasVO->setTcAumento($VolumenG);
    $CargasVO->setVol_doc($VolumenG);
    $CargasDAO->create($CargasVO);
    header("location: pipaspendientes.php");
}
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
                echo $paginador->headers(array(" "), array("V.D.", "U.M.", "Ventas", "Jarrear"));
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();

                    $Cnt = $row["vol_final"] - $row["vol_inicial"];
                    ?>
                    <tr>
                        <td style="text-align: center;">
                            <?php if (date($row["fecha"]) >= $FechaActual) { ?>
                                <a href="<?= $cLink ?>?carga=<?= $row["id"] ?>&step=1">capturar</a>
                            <?php } ?>
                        </td>
                        <?php echo $paginador->formatRow(); ?>
                        <td style="text-align: center;" class="VolumenDocumentado" data-aument='<?= $row["aumento"] ?>'>
                            <a href="#" title="Ingresa para modificar el volumen documentado"> <?= $row["vol_doc"] ?></a>
                        </td>
                        <td style="text-align: center;">
                            <?= $row["um"] ?>
                        </td>
                        <td style="text-align: center;">
                            <a href=javascript:winmin("cpayuda.php?busca=<?= $row["id"] ?>"); title="Ventas durante la descarga"><i class="icon fa fa-lg fa-file-text-o" aria-hidden="true"></i></a>
                        </td>
                        <td style="text-align: center;">
                            <a href="#" class="jarrearPipa" data-idcarga="<?= $row["id"] ?>">seleccionar</a>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>
        <?php
        if ($CiaVO->getTipo_permiso() === "COM") {
            echo $paginador->footer(false, array("<i id='UnirPipas' class='fa fa-wrench' aria-hidden='true'>Unir</i> " => "#", "<i id='AgregarRegistro' class='fa fa-add' aria-hidden='true'>Agregar</i> " => "#",
                "<i class='fa-solid fa-file-import'  data-target='#modal-carga-xml'>Agregar Xml</i>" => "pipasporxml.php"), false, true);
        } else {
            echo $paginador->footer(false, array("<i id='UnirPipas' class='fa fa-wrench' aria-hidden='true'>Unir</i> " => "#"), false, true);
        }
        echo $paginador->filter();
        echo "<div class='mensajes'>$Msj</div>";
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
    </body>
    <?php
    $Productos = "SELECT inv.descripcion,com.clavei FROM inv LEFT JOIN com ON com.descripcion= inv.descripcion WHERE inv.rubro = 'Combustible' AND inv.activo = 'Si'";
    $Pds = utils\IConnection::getRowsFromQuery($Productos);
    $HtmlAdd .= "<table style=\'width:100%;\'><tr><td valign=\'top\' style=\'text-align:right;\'>";
    $HtmlAdd .= "Producto:</td><td style=\'text-align:left;\'><select name =\'ProductoNv\' id=\'ProductoNv\'>";
    foreach ($Pds as $pd) {
        $HtmlAdd .= "<option value=\'" . $pd["clavei"] . "\'>" . $pd["descripcion"] . "</option>";
    }
    $HtmlAdd .= "</select><br><br></td></tr></tr></table>";
    ?>
    <script>
        $(document).ready(function () {

            $("#AgregarRegistro").click(function () {
                Swal.fire({
                    title: "Registro de pipas",
                    background: "#E9E9E9",
                    cancelButtonColor: '#FF5C5C',
                    html: '<?= $HtmlAdd ?>',
                    iconColor: "#8EA0FB",
                    showConfirmButton: true,
                    showCancelButton: true,
                    confirmButtonText: "Aceptar",
                    cancelButtonText: "Cancelar"
                }).then((result) => {
                    if (result.isConfirmed) {
//                        console.log('pipaspendientes.php?op=AddCarga&Producto=' + $("#ProductoNv").val() + '&Volumen=' + $("#VolumenPipa").val() + "&Fecha=" + $("#Fecha_Carga").val());
                        window.location.href = 'pipaspendientes.php?op=AddCarga&Producto=' + $("#ProductoNv").val() + '&Volumen=' + $("#VolumenPipa").val() + "&Fecha=" + $("#Fecha_Carga").val();
                    }
                });
            });

            $(".jarrearPipa").click(function () {
                Swal.fire({
                    icon: "question",
                    title: "¿Seguro que desea registrar la entrada no." + this.dataset.idcarga + " como jarreo?",
                    background: "#E9E9E9",
                    cancelButtonColor: '#FF5C5C',
                    iconColor: "#8EA0FB",
                    showConfirmButton: true,
                    showCancelButton: true,
                    confirmButtonText: "Aceptar",
                    cancelButtonText: "Cancelar"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'entradase.php?carga=' + this.dataset.idcarga + '&op=9';
                    }
                });
            });
            $("#UnirPipas").click(function () {
                Swal.fire({
                    title: "Unión de cargas",
                    background: "#E9E9E9",
                    showConfirmButton: true,
                    confirmButtonText: "Unir",
                    input: 'text',
                    inputLabel: 'Cargas :',
                    inputPlaceholder: 'Ejemplo: 187,188'

                }).then((result) => {
                    if (result.isConfirmed) {
                        jQuery.ajax({
                            type: "POST",
                            url: "bootstrap/ajax/getCargas.php",
                            dataType: "json",
                            cache: false,
                            data: {"op": 1, "capturas": result.value},
                            beforeSend: function (xhr) {
                                $("#Msj").hide();
                                $("#Fail").hide();
                                $("#myLoader").modal("toggle");
                            },
                            success: function (data) {
                                console.log(data);
                                Swal.fire({
                                    icon: 'success',
                                    iconColor: 'blue',
                                    title: data,
                                    background: "#ABEBC6"
                                }).then((result) => {
                                    setTimeout(GoToPipas(), 2500);
                                });
                            },
                            error: function (jqXHR, textStatus) {
                                console.log(jqXHR);
                                Swal.fire({
                                    icon: 'warning',
                                    iconColor: 'red',
                                    title: jqXHR.responseText,
                                    background: "#F5B7B1"
                                })
                            }
                        });
                        //setInterval(GoToPipas(), 3500);
                    }
//                    
                });
            });

            $(".VolumenDocumentado").click(function () {
                fila = $(this).closest("tr");
                var aument = this.dataset.aument;
                var limitSup = aument * 1.20;
                var limitInf = aument * 0.80;
                id = parseInt(fila.find('td:eq(1)').text()); //capturo el ID	
                Swal.fire({
                    title: "Volumen documentado de la carga no." + id,
                    background: "#E9E9E9",
                    showConfirmButton: true,
                    confirmButtonText: "Actualizar",
                    input: 'text',
                    inputLabel: 'Volumen :',
                    inputPlaceholder: ''

                }).then((result) => {
                    if (result.value >= limitInf && result.value <= limitSup) {
                        if (result.isConfirmed) {
                            jQuery.ajax({
                                type: "POST",
                                url: "bootstrap/ajax/getCargas.php",
                                dataType: "json",
                                cache: false,
                                data: {"op": 2, "Id": id, "VolumenDocumentado": result.value},
                                beforeSend: function (xhr) {
                                    $("#Msj").hide();
                                    $("#Fail").hide();
                                    $("#myLoader").modal("toggle");
                                },
                                success: function (data) {
                                    console.log(data);
                                    Swal.fire({
                                        icon: 'success',
                                        iconColor: 'green',
                                        title: data,
                                        background: "#ABEBC6"
                                    }).then((result) => {
                                        setTimeout(GoToPipas(), 2500);
                                    });
                                },
                                error: function (jqXHR, textStatus) {
                                    console.log(jqXHR);
                                    Swal.fire({
                                        icon: 'warning',
                                        iconColor: 'red',
                                        title: jqXHR.responseText,
                                        background: "#F5B7B1"
                                    })
                                }
                            });
                            //setInterval(GoToPipas(), 3500);
                        }
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            iconColor: 'red',
                            title: "Diferencia mayor al 20%",
                            html: "Limite mayor " + limitSup + " Limite inferior " + limitInf,
                            background: "#F5B7B1"
                        });
                    }
                });
            });

        });
        function GoToPipas() {
            window.location.href = 'pipaspendientes.php?criteria=ini';
        }
    </script>
</html>
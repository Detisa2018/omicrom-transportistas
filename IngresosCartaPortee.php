<?php
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("comboBoxes.php");
include_once ('data/CartaPorteIngresoDAO.php');
include_once('data/ProveedorPACDAO.php');

use com\softcoatl\utils as utils;

$Titulo = "Favor de confirmar sus datos";
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

require_once './services/IngresosCartaPorteService.php';

$Vc = "SELECT valor FROM omicrom.variables_corporativo WHERE llave = 'encrypt_fields';";
$Rvc = utils\IConnection::execSql($Vc);
$SqlAdd = $Rvc["valor"] == 1 ? "deencrypt_data(cli.correo) correo " : " cli.correo";
$SqlIngreso = "SELECT i.folio, cli.enviarcorreo, cli.nombre, cli.rfc, cli.codigo, i.usocfdi, i.fecha, cli.regimenfiscal, i.formadepago, i.metodopago, $SqlAdd FROM ingresos i 
LEFT JOIN carta_porte cp ON cp.id_origen = i.id 
LEFT JOIN cli ON cli.id = i.id_cli
WHERE i.id = $busca AND cp.tabla = 'Ingresos'";
$IngCp = utils\IConnection::execSql($SqlIngreso);
$request = utils\HTTPUtils::getRequest();
$pacDAO = new ProveedorPACDAO();
$ppac = $pacDAO->getActive();

$clienteVO = new ClientesVO();
$clientesDAO = new ClientesDAO();
if (is_numeric($busca)) {
    $clienteVO = $clientesDAO->retrieve($IngCp["id_cli"]);
}
$Cia = "SELECT codigo FROM cia;";
$Cp = utils\IConnection::execSql($Cia);

$VvlTeam = strpos($clienteVO->getNombre(), "'") !== false ? true : false;
$MovimientosIEPS = "SELECT valor FROM omicrom.variables_corporativo WHERE llave = 'SinMovmientosIEPS';";
$MovIEPS = utils\IConnection::execSql($MovimientosIEPS);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom.php"; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("input[name=Boton2]").click(function () {
                    var generico = 0;
                    if ($("#Metododepago").val() !== '' && $("#Formadepago").val() !== '' && $("#RegimenFiscal").val() !== '' && $("#CodigoPostal").val() !== '' && $("#cuso").val() !== "") {
                        if ($("#rfcGenerico").is(':checked')) {
                            generico = 1;
                        }
                        var locationDir = "genfactura331.php?Boton=" + $(this).val() + "&rfcGenerico=" + generico + "&CambioCantidad=" + $("#CambioCantidad").val();
                        console.log(locationDir);
                    } else {
                        alert("Faltan ingresar datos, favor de verificar");
                    }
                });
                $("body").on("shown.bs.modal", "#modal-de-carga", function (e) { });
                $("#ViewGenerico").hide();
                if ('<?= utils\HTTPUtils::getSessionValue("cGeneric") ?>' == '1') {
                    $("#rfcGenerico").attr("checked", true);
                    $("#ViewGenerico").show();
                }
                if ("<?= utils\HTTPUtils::getSessionValue("cGenericPerso") ?>" == "1") {
                    $("#rfcGenericoPersonal").attr("checked", true);
                }
                $("#rfcGenerico").click(function () {
                    if (!$(this).prop("checked")) {
                        $("#ViewGenerico").hide();
                    } else {
                        $("#ViewGenerico").show();
                    }
                });
                $("#rfcGenericoPersonal").click(function () {
                    if ($(this).prop("checked")) {
                        Swal.fire({
                            icon: 'question',
                            iconColor: '#EC7063',
                            title: 'La factura sera emitida con estos conceptos.',
                            showCancelButton: true,
                            cancelButtonColor: "#d33",
                            confirmButtonText: "Aceptar",
                            html: "Razon social : " + $("#Nombre").val() + "<br>RFC : XAXX010101000<br> Uso CFDI: S01 - Sin efectos fiscales<br> Regimen F.: 616.- Sin obligaciones fiscales <br> CP: <?= $Cp[codigo] ?>",
                            background: "#D6EAF8"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $("#rfcGenericoPersonal").prop("checked", true);
                            } else {
                                $("#rfcGenericoPersonal").prop("checked", false);
                            }
                        });
                    }
                });
                $("#Formadepago").attr("name", "Formadepago").attr("required", "true").val("<?= $IngCp["formadepago"] ?>");
                $("#Metododepago").attr("name", "Metododepago").attr("required", "true").val("<?= $IngCp["metodopago"] ?>");
                $("#Relacioncfdi").attr("name", "Relacioncfdi").val("<?= $IngCp["relacioncfdi"] ?>");
                $("#FolioRelacionado").attr("name", "FolioRelacionado").val("<?= $IngCp["relacionfolio"] ?>");
                $("#CorreoElectronico").val("<?= $IngCp["correo"] ?>");
                $("#cuso").val("<?= $IngCp["usocfdi"] ?>");
                $("#FechaG").val("<?= $IngCp["fecha"] ?>");
                $("#CodigoPostal").val("<?= $IngCp["codigo"] ?>");
                AjaxRegimenFiscal("<?= $IngCp["rfc"] ?>");
                $("#TipoCantidad").click(function () {
                    var tipo = false;
                    if ($("#TipoCantidad").prop("checked")) {
                        $("#CambioCantidad").val("EnTrue");
                    } else {
                        $("#CambioCantidad").val("EnFalse");
                    }
                });

                $(".TipoRelacion").change(function () {
                    var valor = $(this).val();
                    var idRegistro = this.dataset.idrg;
                    Swal.fire({
                        icon: 'question',
                        iconColor: '#EC7063',
                        title: 'Seguro de modificar el tipo de relación de la factura.',
                        showCancelButton: true,
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Aceptar",
                        background: "#D6EAF8"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            jQuery.ajax({
                                type: 'GET',
                                url: 'getByAjax.php',
                                dataType: 'json',
                                cache: false,
                                data: {"Op": "ActualizaRelacionMultiple", "IdRegistro": idRegistro, "TipoRelacion": valor},
                                success: function (data) {
                                    location.reload();
                                },
                                error: function (jqXHR) {
                                    console.log(jqXHR);
                                }
                            });
                        }
                    });
                });


                regresaFormaPago("<?= $IngCp["metododepago"] ?>", "<?= $IngCp["formadepago"] ?>");
                $("#Metododepago").change(function () {
                    regresaFormaPago($("#Metododepago").val(), "");
                });
            });

            function regresaFormaPago(metodopago, formapago) {
                jQuery.ajax({
                    type: 'GET',
                    url: 'getByAjax.php',
                    dataType: 'json',
                    cache: false,
                    data: {"Op": "FormaDePago", "MetodoPago": metodopago, "Formapago": formapago},
                    success: function (data) {
                        $("#AgregaFormaPago").html("");
                        $("#AgregaFormaPago").html(data.Html);
                    },
                    error: function (jqXHR) {
                        console.log(jqXHR);
                    }
                });
            }

            function AjaxRegimenFiscal(data) {
                jQuery.ajax({
                    type: 'GET',
                    url: 'getByAjax.php',
                    dataType: 'json',
                    cache: false,
                    data: {"Var": data, "Origen": "GetRegimenFiscales"},
                    beforeSend: function (xhr) {
                        $('#RegimenFiscal').empty();
                    },
                    success: function (data) {
                        for (var dt of data)
                        {
                            $('#RegimenFiscal').append($('<option>', {
                                value: dt["clave"],
                                text: dt["clave"] + ".- " + dt["descripcion"]
                            }));
                            $('#RegimenFiscal').val("<?= $IngCp["regimenfiscal"] ?>");
                        }
                    },
                    error: function (jqXHR) {
                        console.log(jqXHR);
                    }
                });
            }
        </script>
    </head>

    <body>

        <?php
        BordeSuperior();
        ?>


        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="IngresosCartaPorte.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;padding-top: 55px;">
                    <div id="FormulariosBoots">
                        <div class="container no-margin">
                            <div class="row no-padding" style="font-size: 15px;">
                                <div class="col-10 background container no-margin" style="border: 1px solid #007F7F;">
                                    <div style="background-color: #099; color: white; text-align:center; font-family: Helvetica, Arial, Verdana, Tahoma, sans-serif; font-size:25px; font-weight:bold;">
                                        CONFIRMACIÓN FINAL DE DATOS GENERALES
                                    </div>
                                    <div style="background-color: #007F7F; color: white; text-align:right; font-family: Helvetica, Arial, Verdana, Tahoma, sans-serif; font-size:14px; font-weight:bold;">
                                        Verifique que los datos generales sean correctos antes de continuar.
                                    </div>
                                    <form name="formulario1" id="formulario1" method="post" action="">
                                        <div class="row no-padding" style="height: 15px;"></div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Folio: </div>
                                            <div class="col-3">
                                                <?= $IngCp["folio"] ?>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Nombre: </div>
                                            <div class="col-3">
                                                <input type="text" name="Nombre" id="Nombre" value="<?= $IngCp["nombre"] ?>"/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">R.F.C: </div>
                                            <div class="col-3">
                                                <input type="text" name="Rfc" id="Rfc" maxlength="15" value="<?= $IngCp["rfc"] ?>"/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Codigo Postal: </div>
                                            <div class="col-1">
                                                <input type="text" name="CodigoPostal" id="CodigoPostal" maxlength="15"/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Regimen F: </div>
                                            <div class="col-3">
                                                <select name="RegimenFiscal"  class="texto_tablas" id="RegimenFiscal">
                                                    <option value=""/>Selecciona Regimen Fiscal</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Uso CFDI: </div>
                                            <div class="col-3">
                                                <?= ComboboxUsoCFDI::generateByTypeCli("cuso", strlen($IngCp["rfc"])); ?>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Forma de pago: </div>
                                            <div class="col-3">
                                                <?php ComboboxFormaDePago::generate("Formadepago", "250px"); ?>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Método de pago: </div>
                                            <div class="col-3">
                                                <?php ComboboxMetodoDePago::generate("Metododepago", "250px"); ?>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Fecha: </div>
                                            <div class="col-3">
                                                <input type="text" name="Fecha" id="Fecha" value="<?= $IngCp["fecha"] ?>"/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Correo electronico: </div>
                                            <div class="col-3">
                                                <input type="text" name="CorreoElectronico" id="CorreoElectronico"/>
                                            </div>
                                            <div class="col-3">
                                                <?php
                                                if ($IngCp["enviarcorreo"] == 'Si') {
                                                    echo "<input type='checkbox' class='botonAnimatedMin' name='Enviarcorreo' value='Si' checked>";
                                                } else {
                                                    echo "<input type='checkbox' class='botonAnimatedMin' name='Enviarcorreo' value='Si'>";
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-7 align-right"></div>
                                            <div class="col-5">
                                                <div style="width: 100%;text-align: right;font-size: 12px;">
                                                    *Recuerde que el timbrado generará su CFDI de Carta Porte final.*
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right"></div>
                                            <div class="col-3">
                                                <input type="submit" class="styleBotonSubmit" name="Boton" value="<?= utils\Messages::OP_UPDATE ?>" title="Guarda los datos del formulario">
                                            </div>
                                        </div>
                                        <div class="col-3 align-right"></div>
                                    </form>
                                    <form name="form2" id="form2" method="post" action="IngresosCartaPorte.php?op=Timbra">
                                        <div class="row no-padding">
                                            <div class="col-4 align-right"></div>
                                            <div class="col-4">
                                                <input type="hidden" name="op" value="Timbra"/>
                                                <input type="submit" aria-hidden="true" data-toggle="modal"  data-target="#modal-de-carga" name="Boton2" value="Timbrar C.P." class="styleBotonSubmit"/>
                                            </div>
                                            <div class="col-4">
                                                <?php if ($ppac->getPruebas() === "1") { ?>
                                                    <div style="color: red !important;width: 100%;text-align: left;font-size: 12px;">
                                                        Modo de demostración activo<br>
                                                        Este timbrado no será válido ante el SAT. <i class="fa-solid fa-file-circle-exclamation fa-lg" style="color: red;"></i>
                                                    </div>
                                                <?php } ?>     
                                            </div>
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
    <style>
        .fa-plus:hover {
            color: #ff6633;
        }
        .fa-plus {
            color: #066;
        }
    </style>
</html> 
<link rel="stylesheet" href="bootstrap/bootstrap-4.0.0/dist/css/bootstrap-modal.css" type="text/css">
<?php include_once ("bootstrap/modals/modal_carcss.html"); ?>

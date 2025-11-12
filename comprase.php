<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("comboBoxes.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$self = utils\HTTPUtils::self();

$Titulo = "Detalle de compra";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

require_once './services/ComprasService.php';

$proveedorDAO = new ProveedorDAO();

$comprasVO = new ComprasVO();
if (is_numeric($busca)) {
    $comprasVO = $comprasDAO->retrieve($busca);
} else {
    $comprasVO->setFecha(date("Y-m-d H:i:s"));
}
$proveedorVO = $proveedorDAO->retrieve($comprasVO->getProveedor());
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <style>
            #myDrop {
                min-height: 80px;
                padding: 5px;
            }
            .dz-button {
                font-size: 22px !important;
                font-weight: bold;
                color: #4F4F4F;
            }
            .dropzone{
                border-radius: 10px;
                border-color: #099;
                background: #EDFAF3;
            }
            .dropzone:hover{
                border:2px solid #EC7063;
                border-color: #EC7063 !important;
                background-color: #FAD7A0 !important;
                font-weight: bold;
                color: #273746;
            }
        </style>
        <script>
            $(document).ready(function () {
                $("#busca").val("<?= $busca ?>");
                $("#Proveedor").val("<?= $comprasVO->getProveedor() ?>");
                $("#Concepto").val("<?= $comprasVO->getConcepto() ?>");
                $("#Documento").val("<?= $comprasVO->getDocumento() ?>");
                $("#Importesin").val("<?= $comprasVO->getImportesin() ?>");
                $("#Uuid").val("<?= $comprasVO->getUuid() ?>");
                $("#Iva").val("<?= $comprasVO->getIva() ?>");
                $("#Observaciones").val("<?= $comprasVO->getObservaciones() ?>");
                $('#Fecha').val('<?= $comprasVO->getFecha() ?>').attr('size', '18').addClass('texto_tablas');
                $('#cFecha').css('cursor', 'hand').click(function () {
                    displayCalendar($('#Fecha')[0], 'yyyy-mm-dd', $(this)[0]);
                });

                $("#Proveedor").focus();
                Dropzone.prototype.defaultOptions.dictDefaultMessage = "<i class='fa-regular fa-hard-drive'></i> Importar Datos XML <i class='fa-regular fa-file-code'></i>";
                Dropzone.options.myDrop = {
                    url: "uploadAditivos.php?busca=<?= $carga ?>&Proveedor=" + $("#Proveedor").val(),
                    uploadMultiple: true,
                    maxFileSize: 3,
                    acceptedFiles: ".xml",
                    height: "10px",
                    init: function init() {
                        this.on("addedfile", function () {
                            setTimeout(function () {
                                window.location.href = "compras.php?criteria=ini";
                            }, 800);
                        });
                    }
                }
                $("#Importesin, #Iva").on("keypress", function (e) {
                    var charCode = e.which ? e.which : e.keyCode;
                    if (charCode == 8 || charCode == 9 || charCode == 46 || charCode == 13) {
                        return true;
                    }
                    if (charCode < 48 || charCode > 57) {
                        Swal.fire({
                            title: "⚠ Ingrese solo números o decimales",
                            icon: 'warning',
                            iconColor: '#C0392B',
                            background: "#E9E9E9",
                            cancelButtonColor: '#E74C3C',
                            showConfirmButton: true,
                            showCancelButton: false
                        });
                        return false;
                    }
                });
                $("#Iva").one("focus", function () {
                    var value = $("#Importesin").val() * 0.16;
                    $("#Iva").val(value.toFixed(2));
                });
            });

        </script>
        <script src="dropzone/min/dropzone.min.js"></script>
    </head>

    <body>

        <?php BordeSuperior(); ?>
        <table style="width: 90%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="compras.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">
                    <div id="FormulariosBoots">
                        <div class="container no-margin">
                            <div class="row no-padding">
                                <div class="col-9 background container no-margin">
                                    <form name="formulario1" id="formulario1" method="post" action="">
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Id : </div>
                                            <div class="col-3 align-left">
                                                <?= $busca ?>
                                            </div>
                                        </div> 
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Fecha: </div>
                                            <div class="col-2">
                                                <input type="datetime-local" name="Fecha" id="Fecha" maxlength="15" class="clase-<?= $clase1 ?>" disabled/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Proveedor : </div>
                                            <div class="col-6">
                                                <?= ComboboxProveedor::generate("Proveedor", "'Aceites'", "100%", " required='required'") ?>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Concepto : </div>
                                            <div class="col-4">
                                                <input type="text" name="Concepto" id="Concepto" maxlength="30" class="clase-<?= $clase1 ?>" required/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right ">Folio ó No. de factura : </div>
                                            <div class="col-3">
                                                <input type="text" name="Documento" id="Documento" maxlength="15" class="clase-<?= $clase1 ?>" required/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right ">Uuid : </div>
                                            <div class="col-5">
                                                <input type="text" name="Uuid" id="Uuid" maxlength="35" class="clase-<?= $clase1 ?>" required/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right ">Importe sin iva : </div>
                                            <div class="col-3">
                                                <input type="text" name="Importesin" id="Importesin" class="clase-<?= $clase1 ?>" required/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right ">Iva : </div>
                                            <div class="col-3">
                                                <input type="text" name="Iva" id="Iva" maxlength="15" class="clase-<?= $clase1 ?>" required/>
                                            </div>
                                        </div>
                                        <?php
                                        if ($busca > 0 && $proveedorVO->getTipodepago() === "Credito") {
                                            ?>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right ">Tipo de pago : </div>
                                                <div class="col-3">
                                                    <?= $proveedorVO->getTipodepago() ?>
                                                </div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right ">No. de dias de credito : </div>
                                                <div class="col-3">
                                                    <?= $proveedorVO->getDias_credito() ?>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right "></div>
                                            <div class="col-3 align-right ">
                                                <?php
                                                if (is_numeric($busca)) {
                                                    if ($comprasVO->getStatus() === StatusCompra::ABIERTO) {
                                                        echo "<input type='submit' class='nombre_cliente' name='Boton' value='Actualizar'>";
                                                    }
                                                } else {
                                                    echo "<input type='submit' class='nombre_cliente' name='Boton' value='Agregar'>";
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <input type='hidden' name='busca' id="busca">
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                    if ($busca === "NUEVO") {
                        ?>
                        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
                            <tr>
                                <td colspan="2" style="padding-top: 40px;">
                                    <div class="container show-dropzone">
                                        <div class="row no-padding">
                                            <div class="col-lg-3"></div>
                                            <div class="col-lg-4" title="Recuerda que para que el uso sea correcto, se necesita tener los productos con el mismo nombre a como los tiene el proveedor registrado en su factura o xml">
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
                                </td>
                            </tr>
                        </table>
                        <?php
                    }
                    ?>
                </td>
            </tr>
        </table>
        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
    </body>
</html>

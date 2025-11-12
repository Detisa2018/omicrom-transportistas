<?php
#Librerias
session_start();

include_once("check.php");
include_once("libnvo/lib.php");
include_once('./comboBoxes.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();

require_once './services/CapturaPipasService.php';

$Titulo = "Detalle de captura";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

$Return = "entradas.php";

$meVO = new MeVO();
if (is_numeric($busca)) {
    $meVO = $meDAO->retrieve($busca);
}
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">

    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>

            $(document).ready(function () {
                $("#Tanque").val("<?= $meVO->getTanque() ?>");
                $("#Tipo").val("<?= $meVO->getTipo() ?>");
                $("#Documento").val("<?= $meVO->getDocumento() ?>");
                $("#Proveedor").val("<?= $meVO->getProveedor() ?>");
                $("#Transporte").val("<?= $meVO->getProveedorTransporte() ?>");
                $("#Terminal").val("<?= $meVO->getTerminal() ?>");

                $('#Fechafac').val('<?= $meVO->getFechafac() ?>').attr('size', '12').addClass('texto_tablas');
                $('#cFecha').css('cursor', 'hand').click(function () {
                    displayCalendar($('#Fechafac')[0], 'yyyy-mm-dd', $(this)[0]);
                });

                $("#autocompleteGen").activeComboBox(
                        $("[name='form1']"),
                        "SELECT data, value FROM (SELECT id as data, CONCAT(id, ' | ', tipodepago, ' | ', nombre, ' | ', observaciones) value FROM cli " +
                        "WHERE TRUE AND cli.tipodepago NOT REGEXP 'Puntos') sub WHERE TRUE",
                        "value"
                        );
            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center; width: 90px;" class="nombre_cliente">
                    <a href="entradas.php"><div class="RegresarCss " alt="Flecha regresar" style="">Regresar</div></a>
                </td>
                <td style="vertical-align: top;">
                    <form name="form1" id="form1" method="post" action="">

                        <?php
                        cTable("99%", "0");

                        echo "<tr><td bgcolor=#e1e1e1 class='nombre_cliente' align='right'>Proveedor: &nbsp;</td><td>";
                        ComboboxProveedor::generate("Proveedor", "'Combustibles'", "300px", "");
                        echo "</td></tr>";

                        echo "<tr><td bgcolor=#e1e1e1 class='nombre_cliente' align='right'>Tanque: &nbsp;</td><td>";
                        ComboboxTanques::generate("Tanque", "300px");
                        echo "</td></tr>";

                        echo "<tr><td bgcolor=#e1e1e1 class='nombre_cliente' align='right'>Transportista: &nbsp;</td><td>";
                        ComboboxCatalogoUniversal::generate("Transporte", "PROVEEDORES_TRANSPORTE", "300px");
                        echo "</td></tr>";

                        echo "<tr><td bgcolor=#e1e1e1 class='nombre_cliente' align='right'>Terminal de almacenamiento:&nbsp;</td><td>";
                        ComboboxCatalogoUniversal::generate("Terminal", "TERMINALES_ALMACENAMIENTO", "300px", "", "");
                        echo "</td></tr>";

                        echo "<tr class='nombre_cliente'><td align='right' bgcolor ='#E1E1E1'>Tipo de carga: &nbsp; </td><td>";
                        echo "&nbsp;<select name='Tipo' class='texto_tablas' id='Tipo' style='width: 300px'>";
                        echo "<option value='Jarreo'>Jarreo</option>";
                        echo "<option value='Normal'>Normal</option>";
                        echo "<option value='Consignacion'>Consignacion</option>";
                        echo "<option value=''>N/A</option>";
                        echo "</select>";
                        echo "</td></tr>";

                        echo "<tr class='nombre_cliente'><td align='right' bgcolor ='#E1E1E1'>Tipo de documento: &nbsp; </td><td> ";
                        echo "&nbsp;<select name='Documento'  class='texto_tablas' id='Documento'  style='width: 300px'>";
                        echo "<option value='CP'>CP</option>";
                        echo "<option value='RP'>RP</option>";
                        echo "<option value=''>N/A</option>";
                        echo "</select>";
                        echo "</td></tr>";

                        cInput("Clave del vehiculo:", "Text", "10", "Clavevehiculo", "right", $meVO->getClavevehiculo(), "20", true, false, '', " required='required'");

                        cInput("UUID:", "Text", "36", "FolioFiscal", "right", $meVO->getUuid(), "20", true, false, '', " required='required'");

                        cInput("Num. Carga:", "Text", "10", "Carga", "right", $meVO->getCarga(), "20", true, false, '', " required='required'");
                        cInput("Fecha del la factura: ", "Text", "10", "Fechafac", "right", "", "10", true, false, "&nbsp <i class='fa-regular fa-calendar-plus fa-lg' style='color:#099' id='cFecha'></i>", " required='required'");
                        cInput("Folio de la Factura:", "Text", "10", "Foliofac", "right", $meVO->getFoliofac(), "20", true, false, '', " required='required'");
                        cInput("Volumen de la Factura:", "Text", "10", "Volumenfac", "right", $meVO->getVolumenfac(), "20", true, false, '', " required='required'");
                        cInput("Precio:", "Text", "10", "Preciou", "right", $meVO->getPreciou(), "20", true, false, '', " required='required'");
                        cInput("Importe de la Factura:", "Text", "10", "Importefac", "right", $meVO->getImportefac(), "20", true, false, '', " required='required'");

                        cTableCie();
                        ?>

                        <p align='center'>
                            <input type='submit' class='nombre_cliente' name='Boton' onclick="validate()" value='Actualizar'>
                            <input type='hidden' name='busca' value="<?= $busca ?>">
                        </p>
                    </form>

                </td>
            </tr>
            <?php
            $VcSc = utils\IConnection::execSql("SELECT valor FROM variables_corporativo WHERE llave = 'ServicioComercial';");
            if ($VcSc["valor"] == 1) {
                ?>
                <?php
                $Cliente = "SELECT CONCAT(cli.id,'.-',cli.nombre , ' | ', cli.observaciones) nombre FROM omicrom.cxc "
                        . "LEFT JOIN cli ON cli.id=cxc.cliente where referencia in (select carga from me where id = " . $meVO->getId() . ")  AND cxc.tm='C';";
                $DtCli = utils\IConnection::execSql($Cliente);
                ?>
                <tr>
                    <td colspan="100%">
                        <form name="ReAsignacion" id="ReAsignacion">
                            <div class="row background">               
                                <div class="col-5 align-right" style="font-weight: bold;text-align: right;font-size: 18px;margin-top: 50px;">Reasignación de cliente <?= $DtCli["nombre"] ?></div>
                            </div>
                            <div class="row background">               
                                <div class="col-2 align-right" style="font-weight: bold;text-align: right;">Cliente :</div>
                                <div class="col-4">
                                    <div style="position: relative;">
                                        <input type="search" style="width: 100%" class="texto_tablas" name="ClienteS" id="autocompleteGen" placeholder="Buscar cliente" required>
                                    </div>
                                    <div id="autocomplete-suggestions"></div>
                                </div>
                                <div class="col-2">
                                    <input type="submit" name="Boton" value="Reasignar Cliente" class="texto_tablas">
                                </div>
                            </div>
                            <input type='hidden' name='busca' value="<?= $busca ?>">
                        </form>
                    </td>
                </tr>
                <?php
            }
            ?>
        </table>
        <style>
            .autocomplete-suggestions {
                width: 800px !important;
            }
            .autocomplete-suggestion{
                width: 790px !important;
            }
        </style>
        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
    </body>

</html>
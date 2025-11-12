<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$self = utils\HTTPUtils::self();

$Titulo = "<i class='fa-brands fa-codepen fa-2x'></i> Detalle de Percepciones";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

require_once "./services/PercepcionesService.php";
$ObjectDAO = new PercepcionesDAO();
$ObjectVO = new PercepcionesVO();
if (is_numeric($busca)) {
    $ObjectVO = $ObjectDAO->retrieve($busca);
}
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                let busca = "<?= $busca ?>";
                $("#busca").val(busca);
                $("#Empleado").val("<?= $ObjectVO->getEmpleado_id() ?>");
                $("#T_Percepcion").val("<?= $ObjectVO->getTipo_percepcion_id() ?>");
                $("#Monto").val("<?= $ObjectVO->getMonto() ?>");
                $("#Fecha").val("<?= $ObjectVO->getFecha() ?>");
                $("#Observaciones").val("<?= $ObjectVO->getObservaciones() ?>");
            });
        </script>
    </head>

    <body>
        <?= print_r($ObjectVO, true) ?>
        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="<?= $Return ?>"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">
                    <div id="FormulariosBoots">
                        <div class="container no-margin">
                            <div class="row no-padding">
                                <div class="col-11 background no-margin">
                                    <form name="formulario1" id="formulario1" method="post" action="">
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Id: </div>
                                            <div class="col-2">
                                                <?= $ObjectVO->getId() ?>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Empleado: </div>
                                            <div class="col-3">
                                                <select name="Empleado" id="Empleado">
                                                    <?php
                                                    $SelectEmp = "SELECT id,nombre FROM empleados_nom WHERE status  = 1";
                                                    $rsEmp = utils\IConnection::getRowsFromQuery($SelectEmp);
                                                    foreach ($rsEmp as $emp) {
                                                        ?>
                                                        <option value="<?= $emp["id"] ?>"><?= $emp["nombre"] ?></option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Tipo de percepcion : </div>
                                            <div class="col-4">
                                                <select name="T_Percepcion" id="T_Percepcion">
                                                    <option value="">Ninguno</option>
                                                    <?php
                                                    $SelectPerc = "SELECT clave,CONCAT(clave,' - ', nombre) nombre FROM catalogo_percepciones";
                                                    $Per = utils\IConnection::getRowsFromQuery($SelectPerc);
                                                    foreach ($Per as $pe) {
                                                        ?>
                                                        <option value="<?= $pe["clave"] ?>"><?= $pe["nombre"] ?></option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Importe : </div>
                                            <div class="col-4">
                                                <input type="text" name="Monto" id="Monto"/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Fecha : </div>
                                            <div class="col-2">
                                                <input type="date" name="Fecha" id="Fecha"/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Observaciones : </div>
                                            <div class="col-2">
                                                <input type="text" name="Observaciones" id="Observaciones"/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-8 align-center">
                                                <?php
                                                crearBoton("Boton", is_numeric($busca) ? utils\Messages::OP_UPDATE : utils\Messages::OP_ADD);
                                                crearInputHidden("busca");
                                                ?>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-11 align-right mensajeInput">
                                                (<sup><i style="color: red;font-size: 8px;" class="fa fa-lg fa-asterisk" aria-hidden="true"></i></sup>) 
                                                Campos necesarios para control de venta
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
</html>
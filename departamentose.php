<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$self = utils\HTTPUtils::self();

$Titulo = "<i class='fa-brands fa-codepen fa-2x'></i> Detalle de Departamento";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

require_once "./services/DepartamentosService.php";
$ObjectDAO = new DepartamentosDAO();
$ObjectVO = new DepartamentosVO();
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
                $("#Nombre").val("<?= $ObjectVO->getNombre() ?>");
                $("#Descripcion").val("<?= $ObjectVO->getDescripcion() ?>");
                $("#Departamento_Sup").val("<?= $ObjectVO->getId_superior() ?>");
                $("#Encargado").val("<?= $ObjectVO->getId_responsable() ?>");
                $("#Ubicacion").val("<?= $ObjectVO->getUbicacion() ?>");
                $("#Estatus").val("<?= $ObjectVO->getEstatus() ?>");
            });
        </script>
    </head>

    <body>

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
                                            <div class="col-4 align-right required">Nombre: </div>
                                            <div class="col-3">
                                                <input type="text" name="Nombre" id="Nombre"/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Descripcion: </div>
                                            <div class="col-6">
                                                <input type="text" name="Descripcion" id="Descripcion"/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Departamento Superior : </div>
                                            <div class="col-4">
                                                <select name="Departamento_Sup" id="Departamento_Sup">
                                                    <option value="">Ninguno</option>
                                                    <?php
                                                    $SelectEmp = "SELECT id,nombre FROM departamentos";
                                                    $Enc = utils\IConnection::getRowsFromQuery($SelectEmp);
                                                    foreach ($Enc as $ec) {
                                                        ?>
                                                        <option value="<?= $ec["id"] ?>"><?= $ec["nombre"] ?></option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Encargado : </div>
                                            <div class="col-4">
                                                <select  name="Encargado" id="Encargado">
                                                    <?php
                                                    $SelectEmp = "SELECT id,nombre FROM empleados_nom";
                                                    $Enc = utils\IConnection::getRowsFromQuery($SelectEmp);
                                                    foreach ($Enc as $ec) {
                                                        ?>
                                                        <option value="<?= $ec["id"] ?>"><?= $ec["nombre"] ?></option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Ubicacion : </div>
                                            <div class="col-6">
                                                <input type="text" name="Ubicacion" id="Ubicacion"/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Estatus : </div>
                                            <div class="col-2">
                                                <select name="Estatus" id="Estatus">
                                                    <option value="0">Inactivo</option>
                                                    <option value="1">Activo</option>
                                                    <option value="2">Suspendido</option>
                                                    <option value="3">Baja</option>
                                                </select>
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
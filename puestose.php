<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$self = utils\HTTPUtils::self();

$Titulo = "<i class='fa-solid fa-location-dot fa-2x'></i> Detalle de Puestos";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

require_once "./services/PuestosService.php";
$ObjectDAO = new PuestosDAO();
$ObjectVO = new PuestosVO();
if (is_numeric($busca)) {
    $ObjectVO = $ObjectDAO->retrieve($busca);
}
echo print_r($ObjectVO, true);
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
                $("#Puesto").val("<?= $ObjectVO->getPuesto() ?>");
                $("#Descripcion").val("<?= $ObjectVO->getDescripcion() ?>");
                $("#Departamento").val("<?= $ObjectVO->getId_departamento() ?>");
                $("#SueldoBase").val("<?= $ObjectVO->getSueldo_base() ?>");
                $("#NivelSalarial").val("<?= $ObjectVO->getNivel_salarial() ?>");
                $("#TipoContrato").val("<?= $ObjectVO->getTipo_contrato() ?>");
                $("#Horario_Laboral_Entrada").val("<?= $ObjectVO->getHorario_laboral_entrada() ?>");
                $("#Horario_Laboral_Salida").val("<?= $ObjectVO->getHorario_laboral_salida() ?>");
                $("#Status").val("<?= $ObjectVO->getEstatus() ?>");
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
                                            <div class="col-4 align-right">Id: </div>
                                            <div class="col-2">
                                                <?= $ObjectVO->getId() ?>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Puesto: </div>
                                            <div class="col-3">
                                                <input type="text" name="Puesto" id="Puesto" required/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Descripcion: </div>
                                            <div class="col-6">
                                                <input type="text" name="Descripcion" id="Descripcion"  required/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Departamento : </div>
                                            <div class="col-2">
                                                <select name="Departamento" id="Departamento"  required>
                                                    <option value="1">Administración</option>
                                                    <option value="2">Limpiesa</option>
                                                    <option value="3">Seguridad</option>
                                                    <option value="4">Desarrollo</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Sueldo Base : </div>
                                            <div class="col-2">
                                                <input type="text" name="SueldoBase" id="SueldoBase" required/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Nivel Salarial : </div>
                                            <div class="col-2">
                                                <input type="text" name="NivelSalarial" id="NivelSalarial" required/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Tipo de Contrato : </div>
                                            <div class="col-2">
                                                <input type="text" name="TipoContrato" id="TipoContrato" required/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Horario de entrada : </div>
                                            <div class="col-2">
                                                <input type="time" name="Horario_Laboral_Entrada" id="Horario_Laboral_Entrada" required/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Horario de salida : </div>
                                            <div class="col-2">
                                                <input type="time" name="Horario_Laboral_Salida" id="Horario_Laboral_Salida"  required/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right ">Estatus : </div>
                                            <div class="col-2">
                                                <select name="Status" id="Status" required>
                                                    <option value="Inactivo">Inactivo</option>
                                                    <option value="Activo">Activo</option>
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
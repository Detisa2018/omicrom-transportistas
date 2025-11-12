<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$self = utils\HTTPUtils::self();

$Titulo = "<i class='fa-solid fa-id-badge fa-2x'></i> Detalle de empleado";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

require_once "./services/EmpleadosService.php";
$EmpleadosDAO = new Empleados_nomDAO();
$EmpleadosVO = new Empleados_nomVO();
if (is_numeric($busca)) {
    $EmpleadosVO = $EmpleadosDAO->retrieve($busca);
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
                $("#buscaShow").val(busca);
                $("#Rfc").val("<?= $EmpleadosVO->getRfc() ?>");
                $("#Nombre").val("<?= $EmpleadosVO->getNombre() ?>");
                $("#Imss").val("<?= $EmpleadosVO->getImss() ?>");
                $("#CuentaBancaria").val("<?= $EmpleadosVO->getCuenta_bancaria() ?>");
                $("#FechaIngreso").val("<?= $EmpleadosVO->getFecha_ingreso() ?>");
                $("#TipoNomina").val("<?= $EmpleadosVO->getTipo_nomina() ?>");
                $("#Departamento").val("<?= $EmpleadosVO->getId_departamento() ?>");
                $("#NoCredencial").val("<?= $EmpleadosVO->getNo_credencial() ?>");
                $("#Status").val("<?= $EmpleadosVO->getStatus() ?>");
                $("#SueldoDiario").val("<?= $EmpleadosVO->getSueldo_diario() ?>");
                $("#SueldoIntegrado").val("<?= $EmpleadosVO->getSueldo_integrado() ?>");
                $("#Baja").val("<?= $EmpleadosVO->getBaja() ?>");
                $("#Curp").val("<?= $EmpleadosVO->getCurp() ?>");
                $("#Observaciones").val("<?= $EmpleadosVO->getObservaciones() ?>");
                $("#Imss").change(function () {
                    let regex = /^[1-9]\d{10}$/;
                    if (!regex.test($("#Imss").val())) {
                        alertTextValidation("El campo debe de contener minimo 11 y maximo 11 caracteres", "", "Aceptar", "", false, "error");
                    }
                });
                $("#Rfc").change(function () {
                    let regex = /^([A-ZÑ&]{3,4})(\d{2})(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])[A-Z\d]{3}$/;
                    if (!regex.test($("#Rfc").val())) {
                        alertTextValidation("El dato no concuerta con la expreción regular: 4 letras (nombre y apellidos). 6 dígitos de fecha (AAMMDD). 3 caracteres alfanuméricos (homoclave). ", "", "Aceptar", "", false, "error");
                    }
                });

            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="empleados.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">
                    <div id="FormulariosBoots">
                        <div class="container no-margin">
                            <div class="row no-padding">
                                <div class="col-10 background no-margin">
                                    <form name="formulario1" id="formulario1" method="post" action="">
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Id: </div>
                                            <div class="col-1">
                                                <?= $busca ?>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Nombre: </div>
                                            <div class="col-5">
                                                <input type="text" name="Nombre" id="Nombre"/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">CURP: </div>
                                            <div class="col-3">
                                                <input type="text" name="Curp" id="Curp" maxlength="18"/>
                                            </div>
                                            <div class="col-2 align-right">RFC: </div>
                                            <div class="col-3">
                                                <input type="text" name="Rfc" id="Rfc" maxlength="13"/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">IMSS : </div>
                                            <div class="col-3">
                                                <input type="text" name="Imss" id="Imss" maxlength="13"/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Cuenta Bancaria : </div>
                                            <div class="col-3">
                                                <input type="text" name="CuentaBancaria" id="CuentaBancaria"/>
                                            </div>
                                            <div class="col-2 align-right">No. Credencial : </div>
                                            <div class="col-3">
                                                <input type="text" name="NoCredencial" id="NoCredencial"/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Fecha de ingreso : </div>
                                            <div class="col-3">
                                                <input type="date" name="FechaIngreso" id="FechaIngreso"/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Tipo Nomina : </div>
                                            <div class="col-3">
                                                <select name="TipoNomina" id="TipoNomina">
                                                    <option value="-">Sin Registro</option>
                                                    <option value="Semanal">Semanal</option>
                                                    <option value="Quincenal">Quincenal</option>
                                                    <option value="Mensual">Mensual</option>
                                                </select>
                                            </div>
                                            <div class="col-2 align-right">Departamento : </div>
                                            <div class="col-3">
                                                <?php
                                                $SQLDep = "SELECT id,nombre FROM departamentos";
                                                $RsDep = utils\IConnection::getRowsFromQuery($SQLDep);
                                                ?>
                                                <select name="Departamento" id="Departamento">
                                                    <?php
                                                    foreach ($RsDep as $dep) {
                                                        ?>
                                                        <option value="<?= $dep["id"] ?>"><?= $dep["nombre"] ?></option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Status : </div>
                                            <div class="col-3">
                                                <select name="Status" id="Status">
                                                    <option value="0">Inactivo</option>
                                                    <option value="1">Activo</option>
                                                </select>
                                            </div>
                                            <div class="col-2 align-right" >Baja : </div>
                                            <div class="col-3">
                                                <input type="date" name="Baja" id="Baja"/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Sueldo Diario : </div>
                                            <div class="col-3">
                                                <input type="text" name="SueldoDiario" id="SueldoDiario"/>
                                            </div>
                                            <div class="col-2 align-right">Sueldo Integrado : </div>
                                            <div class="col-3">
                                                <input type="text" name="SueldoIntegrado" id="SueldoIntegrado"/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Observaciones : </div>
                                            <div class="col-6">
                                                <textarea name="Observaciones" id="Observaciones" rows="2" cols="64"></textarea>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4"></div>
                                            <div class="col-3">
                                                <input class="styleBotonSubmit" type="submit" name="Boton" id="" value="<?= is_numeric($busca) ? utils\Messages::OP_UPDATE : utils\Messages::OP_ADD ?>"/>
                                            </div>
                                            <?php
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
<?php
#Librerias
session_start();

include_once ("check.php");
include_once ('comboBoxes.php');
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$self = utils\HTTPUtils::self();

$Titulo = "Detalle de Servicios";
$nameVarBusca = "buscaV";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

require_once "./services/ServiciosService.php";

$objectVO = new ServiciosTraVO();
$objectDAO = new ServiciosTraDAO();
if (is_numeric($busca)) {
    $objectVO = $objectDAO->retrieve($busca);
}
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>        
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">

                    <a href="<?= $request->hasAttribute("ReturnD") ? $request->getAttribute("ReturnD") : "servicios.php"; ?>"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">

                    <div id="FormulariosBoots">

                        <div class="container no-margin">
                            <div class="row no-padding">
                                <div class="col-12 background no-margin">
                                    <form name="formulario1" id="formulario1" method="post" action="">
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Id:</div>
                                            <div class="col-2"><?= $busca ?></div>
                                        </div>                                                                                          
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Nombre:</div>
                                            <div class="col-4"><input type="text" name="Nombre" id="Nombre" placeholder="" /></div>
                                        </div>                                                                                                
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Identificador:</div>
                                            <div class="col-2"><input type="text" name="Identificador" id="Identificador"/></div>
                                        </div>                                                                                   
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Clave Unidad:</div>
                                            <div class="col-2"><?= ComboboxUnidades::generate("ClaveUnidad"); ?></div>
                                        </div>                                                                                          
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Clave Producto:</div>
                                            <div class="col-2"><?= ComboboxCommonProductoServicio::generate("ClaveProducto"); ?></div>
                                        </div>                                                                                      
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Precio:</div>
                                            <div class="col-1"><input type="text" name="Precio" id="Precio"/></div>
                                        </div>                                                                                         
                                        <div class="row no-padding">
                                            <div class="col-3 align-right"></div>
                                            <div class="col-4"><input type="submit" name="Boton" id="Boton"/></div>
                                        </div>                                       
                                        <input type="hidden" name="busca" id="busca"/>
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
        <script>
            $(document).ready(function () {
                $("#busca").val("<?= $busca ?>");
                $("#Nombre").val("<?= $objectVO->getNombre() ?>");
                $("#ClaveUnidad").val("<?= $objectVO->getClave_unidad() ?>");
                $("#ClaveProducto").val("<?= $objectVO->getClave_producto() ?>");
                $("#Precio").val("<?= $objectVO->getPrecio() ?>");
                $("#Identificador").val("<?= $objectVO->getIdentificador() ?>");
                $("#Id").val("<?= $busca ?>");

                if ($("#busca").val() !== "NUEVO") {
                    $("#Boton").val("Actualizar");
                } else {
                    $("#Boton").val("Agregar");
                }
            });
        </script>
    </body>
</html>
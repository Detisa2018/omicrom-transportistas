<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("comboBoxes.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$self = utils\HTTPUtils::self();
$Return = "remisiones.php";

$Titulo = "Detalle de venta";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

require_once './services/RemisionesService.php';

$rmVO = new RmVO();
$clienteVO = new ClientesVO();
$comVO = new CombustiblesVO();
if (is_numeric($busca)) {
    $rmVO = $rmDAO->retrieve($busca);
    $clienteVO = $clientesDAO->retrieve($rmVO->getCliente());
    $comVO = $comDAO->retrieve($rmVO->getProducto(), "clavei");
    $Cliente = $rmVO->getCliente();
} else {
    $PosA = $mysqli->query("SELECT posicion FROM man WHERE activo='Si' ORDER BY posicion");
    $matrizPosicion = array("" => "SELECCIONAR");

    if ($request->hasAttribute("Posicion")) {
        $matrizPosicion = array();
        $matrizProducto = array();
        $Com = $mysqli->query("SELECT m.producto,c.descripcion FROM man_pro m,com c "
                . "WHERE m.producto = c.clavei AND m.activo='Si' AND m.posicion = '" . $request->getAttribute("Posicion") . "'");
        while ($rg = $Com->fetch_array()) {
            $matrizProducto[$rg["producto"]] = $rg["descripcion"];
        }
    }

    while ($rg = $PosA->fetch_array()) {
        $matrizPosicion[$rg["posicion"]] = $rg["posicion"];
    }
    $Titulo = "Agregar venta";
}

$SCliente = $clienteVO;
if ($request->hasAttribute("Cliente")) {
    $SeachCliente = $request->getAttribute("Cliente");
    $Cliente = strpos($SeachCliente, "|") > 0 ? trim(substr($SeachCliente, 0, strpos($SeachCliente, "|"))) : trim($SeachCliente);
    $SCliente = $clientesDAO->retrieve($Cliente);

    $selectCodigos = "SELECT id, CONCAT(id, ' | ', TRIM(impreso), ' | ', TRIM(numeco) , ' | ', TRIM(descripcion) , ' | ', TRIM(placas),IF(periodo = 'B',CONCAT(' | Saldo Disponible $', importe),'')) descripcion
                    FROM unidades WHERE cliente = '$Cliente' AND LOWER(estado) = 'a'
                    ORDER BY impreso";
    $Codigos = utils\IConnection::getRowsFromQuery($selectCodigos);
} elseif ($clienteVO->getId() > 0) {
    $selectCodigos = "SELECT id, CONCAT(id, ' | ', TRIM(impreso), ' | ', TRIM(descripcion) , ' | ', TRIM(placas)) descripcion
                    FROM unidades WHERE cliente = '" . $clienteVO->getId() . "' AND LOWER(estado) = 'a'
                    ORDER BY impreso";
    $Codigos = utils\IConnection::getRowsFromQuery($selectCodigos);
}
$SlCt = "SELECT status,statusctv FROM ct WHERE id = " . $rmVO->getCorte();
$CtRs = utils\IConnection::execSql($SlCt);

$matriz0 = array("D" => "Normal", "J" => "Jarreo", "A" => "Uvas/Pemex", "N" => "Consignacion");
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#busca").val("<?= $busca ?>");
            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>
        <div id="FormulariosBoots">
            <div class="container no-margin">
                <div class="row no-padding">
                    <div class="col-12 background container no-margin">
                        <form name="formulario2" id="formulario2" method="post" action="">
                            <div class="row no-padding">
                                <div class="col-3 align-right">Id : </div>
                                <div class="col-3">
                                    <?= $busca ?>
                                </div>
                            </div>
                            <div class="row no-padding">
                                <div class="col-3 align-right">Producto : </div>
                                <div class="col-4">
                                    <?= ComboboxCombustibles::generate("Productos") ?>
                                </div>
                            </div>
                            <div class="row no-padding">
                                <div class="col-3 align-right">Cantidad : </div>
                                <div class="col-1">
                                    <input type="text" name="Cantidad" id="Cantidad" placeholder="0.00">
                                </div>
                                <div class="col-1">Ó</div>
                                <div class="col-1 align-right">Importe : </div>
                                <div class="col-1">
                                    <input type="text" name="Importe" id="Importe"  placeholder="0.00">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
        <style>
            .autocomplete-suggestions {
                width: 800px !important;
            }
            .autocomplete-suggestion{
                width: 790px !important;
            }
        </style>
    </body>
</html>
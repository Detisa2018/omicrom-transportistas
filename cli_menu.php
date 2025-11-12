<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$usuarioSesion = getSessionUsuario();

$Titulo = "Servicio a clientes menu principal";
$Id = 1;
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php include './config_omicrom_clientes.php'; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {

            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(true); ?>
        <?php
        $Band = "SELECT tipo_permiso FROM cia;";
        $stB = utils\IConnection::execSql($Band);
        if ($stB["tipo_permiso"] === "TRA") {
            ?>
            <a href=javascript:winuni("calendarRm.php?busca=ini");><i class="fa fa-calendar-plus-o fa-lg" aria-hidden="true" style="color:#009080">Registro de pedidos</i></a>
        <?php } ?>
        <table aria-hidden="true" style="width: 100%;">
            <tr>
                <td>
                    <?php
                    $Cliente = explode(".", $usuarioSesion->getNombre());
                    $selectSaldoT = "SELECT IF(cli.tipodepago='Credito',cli.limite,0) limite,SUM(IF(tm = 'H',importe, -importe)) consumos, cli.limite - SUM(IF(tm = 'H',importe, -importe)) saldo_restante FROM cxc 
                                LEFT JOIN cli ON cli.id = cxc.cliente 
                                WHERE cliente = '" . $Cliente[0] . "' AND fecha < DATE(NOW())
                                AND tipodepago in ('Credito','Prepago');";

                    $saldoT = utils\IConnection::execSql($selectSaldoT);
                    if ($saldoT["saldo_restante"] <> 0) {
                        ?>
                        <div style="width: 76%;margin-left: 12%; height: 100px;background-color: #FDF2E9;margin-top: 45px;">
                            <div style="width: 100%;height: 40px;background-color: #066;text-align: center;padding-top: 10px;font-size: 20px;font-weight: bold;color: white;">
                                Saldo Actual
                            </div>
                            <div style="display: inline-block;width: 33%;border: 1px solid #D6DBDF;height: 60px;padding-top: 15px;padding-left: 5px;">
                                Limite : <?= number_format($saldoT["limite"], 2) ?>
                            </div>
                            <div style="display: inline-block;width: 32%;border: 1px solid #D6DBDF;height: 60px;padding-top: 15px;padding-left: 5px;">
                                Edo.Cuenta: <?= number_format($saldoT["consumos"], 2) ?>
                            </div>
                            <div style="display: inline-block;width: 33%;border: 1px solid #D6DBDF;height: 60px;padding-top: 15px;padding-left: 5px;">
                                Saldo : <?= number_format($saldoT["saldo_restante"], 2) ?>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </td>
            </tr>
        </table>

        <?php BordeSuperiorCerrar(); ?>
        <?php PieDePagina(); ?>

    </body>
</html>

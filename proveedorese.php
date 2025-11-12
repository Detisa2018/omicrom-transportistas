<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ('comboBoxes.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$self = utils\HTTPUtils::self();

$Titulo = "Detalle de proveedor";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

require_once "./services/ProveedoresService.php";

$objectVO = new ProveedorVO();
if (is_numeric($busca)) {
    $objectVO = $proveedorDAO->retrieve($busca);
}

$arrayTipoPago = Array("Credito" => "Credito", "Contado" => "Contado");
$arrayTipoProveedor = array("Combustibles" => "Combustibles", "Aceites" => "Aceites", "Dictamenes" => "Dictamenes", "Equipo" => "Equipo", "CV" => "Contro Volumetrico", "Otros" => "Otros");
$arrayProveedor = Array("Nacional" => "Nacional", "Extranjero" => "Extranjero");
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
                    <a href="proveedores.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">

                    <div id="FormulariosBoots">

                        <div class="container no-margin">
                            <div class="row background no-padding">
                                <div class="col-9 no-margin">
                                    <form name="formulario1" id="formulario1" method="post" action="">
                                        <div class="row no-padding">
                                            <div class="col-4 align-right  required">Id:</div>
                                            <div class="col-4"><input type="text" name="Id" id="Id" placeholder="" disabled=""/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right  required">Nombre:</div>
                                            <div class="col-8"><input type="text" name="Nombre" id="Nombre" placeholder="" required="" onkeyup="mayus(this);"/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right  required">Alias:</div>
                                            <div class="col-4"><input type="text" name="Alias" id="Alias" placeholder="" required="" onkeyup="mayus(this);"/></div>
                                            <div class="col-4">Nombre corto</div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right  required">R.f.c.:</div>
                                            <div class="col-4"><input type="text" name="Rfc" id="Rfc" placeholder="Ej: XAXX010101000" required="" onkeyup="mayus(this);"/></div>
                                            <div class="col-4">Sin espacios ni guiones</div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right ">Direccion:</div>
                                            <div class="col-8"><input type="text" name="Direccion" id="Direccion" placeholder="" onkeyup="mayus(this);"/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right ">No.exterior:</div>
                                            <div class="col-4"><input type="text" name="Numeroext" id="Numeroext" placeholder="" onkeyup="mayus(this);"/></div>
                                            <div class="col-2 align-right ">Depto:</div>
                                            <div class="col-2"><input type="text" name="Numeroint" id="Numeroint" placeholder=""/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right ">Colonia:</div>
                                            <div class="col-8"><input type="text" name="Colonia" id="Colonia" placeholder="" onkeyup="mayus(this);"/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right ">Municipio:</div>
                                            <div class="col-8"><input type="text" name="Municipio" id="Municipio" placeholder="" onkeyup="mayus(this);"/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right ">Telefono:</div>
                                            <div class="col-4"><input type="text" name="Telefono" id="Telefono" placeholder="" onkeyup="mayus(this);"/></div>
                                            <div class="col-2 align-right ">C.P.:</div>
                                            <div class="col-2"><input type="text" name="Codigo" id="Codigo" placeholder=""/></div>
                                        </div>                                       
                                        <div class="row no-padding">
                                            <div class="col-4 align-right ">Correo electronico:</div>
                                            <div class="col-8"><input type="email" name="Correo" id="Correo" placeholder=""/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right ">Contacto:</div>
                                            <div class="col-8"><input type="text" name="Contacto" id="Contacto" placeholder="" onkeyup="mayus(this);"/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right ">Numero de cuenta contable:</div>
                                            <div class="col-8"><input type="text" name="Ncc" id="Ncc" placeholder=""/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right ">Nombre del banco:</div>
                                            <div class="col-8"><input type="text" name="Banco" id="Banco" placeholder="" onkeyup="mayus(this);"/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right ">No.de cuenta:</div>
                                            <div class="col-8"><input type="text" name="Cuenta" id="Cuenta" placeholder=""/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right ">Clabe interbancaria:</div>
                                            <div class="col-8"><input type="text" name="Clabe" id="Clabe" placeholder=""/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right ">Proveedor de:</div>
                                            <div class="col-4">
                                                <select name="Proveedorde" id="Proveedorde">
                                                    <?php foreach ($arrayTipoProveedor as $key => $value) : ?>
                                                        <option value="<?= $key ?>"><?= $value ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-4">Se usa en distintos modulos</div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right ">Permiso de la CRE:</div>
                                            <div class="col-4"><input type="text" name="PermisoCRE" id="PermisoCRE" placeholder="Ej: PL/0000/COM/2020" onkeyup="mayus(this);"/></div>
                                            <div class="col-4">Para proveedores de combustible</div>
                                        </div>                                        
                                        <div class="row no-padding">
                                            <div class="col-4 align-right ">Tipo de proveedor:</div>
                                            <div class="col-4">
                                                <select name="Tipodepago" id="Tipodepago">
                                                    <?php foreach ($arrayTipoPago as $key => $value) : ?>
                                                        <option value="<?= $key ?>"><?= $value ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-2 align-right ">Dias/credito:</div>
                                            <div class="col-2"><input type="number" name="Dias_credito" id="Dias_credito" placeholder="" min="0"/></div>
                                        </div>   
                                        <div class="row no-padding">
                                            <div class="col-4 align-right ">Tipo:</div>
                                            <div class="col-4">
                                                <select name="TipoProveedor" id="TipoProveedor">
                                                    <?php foreach ($arrayProveedor as $key => $value) : ?>
                                                        <option value="<?= $key ?>"><?= $value ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-4">Se usará en la captura de pipas</div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right"></div>
                                            <div class="col-4"><input type="submit" name="Boton" id="Boton"/></div>
                                        </div>                                       
                                        <input type="hidden" name="busca" id="busca"/>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php
                        $ciaDAO = new CiaDAO();
                        $ciaVO = new CiaVO();
                        $ciaVO = $ciaDAO->retrieve("true");
                        if ($ciaVO->getTipo_permiso() === "TRA") {
                            $object2VO = new DireccionVO();
                            $direccionDAO = new DireccionDAO();
                            if (is_numeric($busca)) {
                                $object2VO = $direccionDAO->retrieve($busca, "id_origen", " AND tabla_origen = 'P'");
                            }
                            ?>


                            <div class="container no-margin" style="margin-top: 15px;">
                                <div class="row no-padding">
                                    <div class="col-12 background container no-margin">
                                        <div style="width: 100%; text-align: center;"><h3>DIRECCIONES ASOCIADAS AL PROVEEDOR</h3></div>
                                        <div style="width: 100%;text-align: right;padding-right:40px;height: 40px;font-size: 16px;">
                                            <div id="AddUbicacion">Agregar Ubicacion <i class="fa-solid fa-plus"></i></div>
                                        </div>
                                        <table style="width: 80%;margin-left: 10%;background-color: white;border-radius: 10px;padding: 10px;">
                                            <tr style="font-size: 15px;font-weight: bold;">
                                                <td style="border-bottom: 1px solid black;">Descripcion</td>
                                                <td style="border-bottom: 1px solid black;">Num. Ext.</td>
                                                <td style="border-bottom: 1px solid black;">Num. Int.</td>
                                                <td style="border-bottom: 1px solid black;">Colonia</td>
                                                <td style="border-bottom: 1px solid black;">Localidad</td>
                                                <td style="border-bottom: 1px solid black;">Municipio</td>
                                                <td style="border-bottom: 1px solid black;">Estado</td>
                                                <td style="border-bottom: 1px solid black;">C.P.</td>
                                                <td style="border-bottom: 1px solid black;">Editar</td>
                                                <td style="border-bottom: 1px solid black;">Eliminar</td>
                                            </tr>
                                            <?php
                                            $SqlDetalle = "SELECT * FROM catalogo_direcciones WHERE tabla_origen = 'P' AND id_origen = $busca AND id > 0";
                                            $Dt = utils\IConnection::getRowsFromQuery($SqlDetalle);
                                            $s = 0;
                                            foreach ($Dt as $t) {
                                                $s++;
                                                $ColorLine = $s % 2 == 0 ? "white" : "#D4D4D4";
                                                ?>
                                                <tr style="background-color: <?= $ColorLine ?>">
                                                    <td><?= $t["descripcion"] ?></td>
                                                    <td><?= $t["num_exterior"] ?></td>
                                                    <td><?= $t["num_interior"] ?></td>
                                                    <td><?= $t["colonia"] ?></td>
                                                    <td><?= $t["localidad"] ?></td>
                                                    <td><?= $t["municipio"] ?></td>
                                                    <td><?= $t["estado"] ?></td>
                                                    <td><?= $t["codigo_postal"] ?></td>
                                                    <td style="text-align: center;"><i data-iddireccion="<?= $t["id"] ?>" class="fa-solid fa-pencil"></i></td>
                                                    <td style="text-align: center;"><i data-iddireccion="<?= $t["id"] ?>" class="fa-solid fa-trash-can"></i></td>
                                                </tr>
                                                <?php
                                            }
                                            ?>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="container no-margin" style="margin-top: 15px;">
                                <div class="row no-padding">
                                    <div class="col-12 background container no-margin">
                                        <form name="formulario3" id="formulario3" method="post" action="">
                                            <input type="hidden"  name="busca" value="<?= $busca ?>">
                                            <div class="row no-padding">
                                                <div class="col-11 align-right mensajeInput">
                                                    (<sup><i style="color: red;font-size: 8px;" class="fa fa-lg fa-asterisk" aria-hidden="true"></i></sup>) 
                                                    <strong> Campos necesarios para la Carta Porte 2.0</strong>
                                                </div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right required">Descripcion:</div>
                                                <div class="col-6"><input type="text" name="DescripcionCP" id="DescripcionCP" placeholder=""></div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right required">Calle:</div>
                                                <div class="col-6"><input type="text" name="CalleCP" id="CalleCP" placeholder="" required="" onkeyup="mayus(this);"/></div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right required">Numero Ext.:</div>
                                                <div class="col-6"><input type="text" name="ExtCP" id="ExtCP" placeholder="" required=""/></div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right">Numero Int:</div>
                                                <div class="col-6"><input type="text" name="IntCP" id="IntCP" placeholder=""/></div>
                                            </div> 
                                            <div class="row no-padding">
                                                <div class="col-3 align-right required">Estado:</div>
                                                <div class="col-6">
                                                    <select name="EstadoCP" id="EstadoCP" required >
                                                        <?php
                                                        $arrayDatos = CatalogosSelectores::getEstado();
                                                        foreach ($arrayDatos as $key => $value) {
                                                            ?>
                                                            <option value="<?= $key ?>"/><?= $value ?></option>
                                                            <?php
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right required" > Municipio:</div>
                                                <div class="col-6">
                                                    <select name="MunicipioCP" id="MunicipioCP" required></select>                                              
                                                </div>
                                            </div>                                       
                                            <div class="row no-padding">
                                                <div class="col-3 align-right required">Localidad:</div>
                                                <div class="col-6">
                                                    <select name="LocalidadCP" id="LocalidadCP" required ></select>                                                           
                                                </div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right required">Codigo Postal:</div>
                                                <div class="col-6">
                                                    <input type="text" name="CodigoPostalCP" id="CodigoPostalCP" placeholder="" required=""/>
                                                </div>
                                            </div>                                       
                                            <div class="row no-padding">
                                                <div class="col-3 align-right required">Colonia:</div>
                                                <div class="col-6">
                                                    <select name="ColoniaCP" id="ColoniaCP" required ></select> 
                                                </div>
                                            </div>                             
                                            <div class="row no-padding">
                                                <div class="col-3 align-right "></div>
                                                <div class="col-3">
                                                    <input type="button" name="Boton2" id="ActualizaUbi" value="Actualizar">
                                                    <input type="button" name="Boton2" id="AgregaUbi" value="Agregar">
                                                </div>
                                            </div>
                                            <input type="hidden" id="IdProveedor" name="IdProveedor">
                                            <input type="hidden" id="IdUbicacion" name="IdUbicacion">
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <script>
                                $(document).ready(function () {
                                    /*Comenzamos con boton para poder agregar*/
                                    $("#ActualizaUbi").hide();
                                    
                                    /*Limpiamos apartado de Direcciónes para pdoer agregar una nueva*/
                                    $("#AddUbicacion").click(function () {
                                        $("#CodigoPostalCP").val("");
                                        $("#ExtCP").val("");
                                        $("#IntCP").val("");
                                        $("#DescripcionCP").val("");
                                        $("#CalleCP").val("");
                                        $("#EstadoCP").val("");
                                        $("#MunicipioCP").val("");
                                        $("#LocalidadCP").val("");
                                        $("#ColoniaCP").val("");
                                        $("#ActualizaUbi").hide();
                                        $("#AgregaUbi").show();
                                    });
                                    /*Aliminamos registros de Ubicación*/
                                    $(".fa-trash-can").click(function () {
                                        var idDireccion = this.dataset.iddireccion;
                                        Swal.fire({
                                            icon: 'warning',
                                            iconColor: "#9C2007",
                                            background: "#FFE2E2",
                                            showConfirmButton: true,
                                            title: '¿Está usted seguro de que desea eliminar su registro?'
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                jQuery.ajax({
                                                    type: 'GET',
                                                    url: 'getByAjax.php',
                                                    dataType: 'json',
                                                    cache: false,
                                                    data: {"Op": "EliminaUbicacion",
                                                        "IdUbicacion": idDireccion
                                                    },
                                                    success: function (data) {
                                                        location.reload();
                                                    },
                                                    error: function (jqXHR) {
                                                        console.log(jqXHR);
                                                    }
                                                });
                                            }
                                            //                    
                                        });
                                    });
                                    /*Actualizamos info de un registro de direcciónes*/
                                    $("#ActualizaUbi").click(function () {
                                        jQuery.ajax({
                                            type: 'GET',
                                            url: 'getByAjax.php',
                                            dataType: 'json',
                                            cache: false,
                                            data: {"Op": "ActualizarUbicacion",
                                                "Descripcion": $("#DescripcionCP").val(),
                                                "Calle": $("#CalleCP").val(),
                                                "NumExt": $("#ExtCP").val(),
                                                "NumInt": $("#IntCP").val(),
                                                "Colonia": $("#ColoniaCP").val(),
                                                "Localidad": $("#LocalidadCP").val(),
                                                "Municipio": $("#MunicipioCP").val(),
                                                "Estado": $("#EstadoCP").val(),
                                                "CodigoPostal": $("#CodigoPostalCP").val(),
                                                "IdUbicacion": $("#IdUbicacion").val()
                                            },
                                            success: function (data) {
                                                location.reload();
                                            },
                                            error: function (jqXHR) {
                                                console.log(jqXHR);
                                            }
                                        });
                                    });
                                    /*Agregamos un nuevo registro de direcciónes*/
                                    $("#AgregaUbi").click(function () {
                                        jQuery.ajax({
                                            type: 'GET',
                                            url: 'getByAjax.php',
                                            dataType: 'json',
                                            cache: false,
                                            data: {"Op": "AgregarUbicacion",
                                                "Descripcion": $("#DescripcionCP").val(),
                                                "Calle": $("#CalleCP").val(),
                                                "NumExt": $("#ExtCP").val(),
                                                "NumInt": $("#IntCP").val(),
                                                "Colonia": $("#ColoniaCP").val(),
                                                "Localidad": $("#LocalidadCP").val(),
                                                "Municipio": $("#MunicipioCP").val(),
                                                "TablaOrigen": "P",
                                                "Estado": $("#EstadoCP").val(),
                                                "CodigoPostal": $("#CodigoPostalCP").val(),
                                                "IdProveedor": "<?= $busca ?>"
                                            },
                                            success: function (data) {
                                                location.reload();
                                            },
                                            error: function (jqXHR) {
                                                console.log(jqXHR);
                                            }
                                        });
                                    });

                                    $(".fa-pencil").click(function () {
                                        var idDireccion = this.dataset.iddireccion;
                                        jQuery.ajax({
                                            type: 'GET',
                                            url: 'getByAjax.php',
                                            dataType: 'json',
                                            cache: false,
                                            data: {"Op": "ObtenDireccion", "idDireccion": idDireccion, "Origen" : "P"},
                                            success: function (data) {
                                                AjaxCodigoPostal(data.CodigoPostal, data.Colonia);
                                                AjaxEstado(data.Estado, data.Municipio, data.Localidad);
                                                $("#CodigoPostalCP").val(data.CodigoPostal);
                                                $("#ExtCP").val(data.NumExt);
                                                $("#IntCP").val(data.NumInt);
                                                $("#DescripcionCP").val(data.Descripcion);
                                                $("#CalleCP").val(data.Calle);
                                                $("#EstadoCP").val(data.Estado);
                                                $("#MunicipioCP").val(data.Municipio);
                                                $("#LocalidadCP").val(data.Localidad);
                                                $("#ColoniaCP").val(data.Colonia);
                                                $("#IdProveedor").val(idDireccion);
                                                $("#IdUbicacion").val(data.Id);
                                            },
                                            error: function (jqXHR) {
                                                console.log(jqXHR);
                                            }
                                        });
                                        $("#ActualizaUbi").show();
                                        $("#AgregaUbi").hide();
                                    });
                                    $("#EstadoCP").change(function () {
                                        AjaxEstado($("#EstadoCP").val(), "EstadoCP");
                                    });
                                    $("#CodigoPostalCP").change(function () {
                                        if ($("#CodigoPostalCP").val().length == 5) {
                                            AjaxCodigoPostal($("#CodigoPostalCP").val());
                                        }
                                    });
    //                                    AjaxEstado("<?= $object2VO->getEstado() ?>", "<?= $object2VO->getMunicipio() ?>", "<?= $object2VO->getLocalidad() ?>");
    //                                    AjaxCodigoPostal("<?= $object2VO->getCodigo_postal() ?>", "<?= $object2VO->getColonia() ?>");
                                });
                            </script>
                            <?php
                        }
                        ?>
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

                $("#Id").val("<?= $busca ?>");
                $("#Nombre").val("<?= $objectVO->getNombre() ?>");
                $("#Alias").val("<?= $objectVO->getAlias() ?>");
                $("#Rfc").val("<?= $objectVO->getRfc() ?>");
                $("#Direccion").val("<?= $objectVO->getDireccion() ?>");
                $("#Numeroext").val("<?= $objectVO->getNumeroext() ?>");
                $("#Numeroint").val("<?= $objectVO->getNumeroint() ?>");
                $("#Colonia").val("<?= $objectVO->getColonia() ?>");
                $("#Municipio").val("<?= $objectVO->getMunicipio() ?>");
                $("#Telefono").val("<?= $objectVO->getTelefono() ?>");
                $("#Codigo").val("<?= $objectVO->getCodigo() ?>");
                $("#Correo").val("<?= $objectVO->getCorreo() ?>");
                $("#Contacto").val("<?= $objectVO->getContacto() ?>");
                $("#Ncc").val("<?= $objectVO->getNcc() ?>");
                $("#Banco").val("<?= $objectVO->getBanco() ?>");
                $("#Cuenta").val("<?= $objectVO->getCuenta() ?>");
                $("#Clabe").val("<?= $objectVO->getClabe() ?>");
                $("#PermisoCRE").val("<?= $objectVO->getPermisoCRE() ?>");
                $("#Proveedorde").val("<?= $objectVO->getProveedorde() ?>");
                $("#Dias_credito").val("<?= $objectVO->getDias_credito() ?>");
                $("#Tipodepago").val("<?= $objectVO->getTipodepago() ?>");
                $("#TipoProveedor").val("<?= $objectVO->getTipoProveedor() ?>");

                if ($("#busca").val() !== "NUEVO") {
                    $("#Boton").val("Actualizar");
                } else {
                    $("#Boton").val("Agregar");
                }

                $("#Nombre").focus();

                $("#Boton").on("click", function (e) {
                    const  pattern = /^[A-ZÑ&]{3,4}[\d]{6}[A-ZÑ\d]{3}$/;
                    let rfc = $("#Rfc").val().trim();
                    if (!pattern.test(rfc)) {
                        e.preventDefault();
                        $("#Rfc").focus();
                        alert("El RFC [" + rfc + "] ingresado es invalido, favor de verificarlo.\nEstructura valida {ABCD}{YYMMDD}{123}");
                    }
                });


            });

            function AjaxEstado(dt, val1, val2) {
                jQuery.ajax({
                    type: 'GET',
                    url: 'getDirecciones.php',
                    dataType: 'json',
                    cache: false,
                    data: {"Var": dt, "Origen": "Estado"},
                    beforeSend: function (xhr) {
                        $('#LocalidadCP').empty();
                        $('#MunicipioCP').empty();
                    },
                    success: function (data) {
                        for (var dt of data)
                        {
                            for (var d of dt) {
                                if (typeof d["localidad"] != "string") {

                                    $('#MunicipioCP').append($('<option>', {
                                        value: d["clave"],
                                        text: d["clave"] + ".- " + d["descripcion"]
                                    }));
                                } else {
                                    $('#LocalidadCP').append($('<option>', {
                                        value: d["id"],
                                        text: d["localidad"] + ".- " + d["descripcion"]
                                    }));
                                }
                            }
                            $("#MunicipioCP").val(val1);
                            $("#LocalidadCP").val(val2);
                        }
                    },
                    error: function (jqXHR) {
                        console.log(jqXHR);
                    }
                });
            }

            function AjaxCodigoPostal(dt, val1) {
                jQuery.ajax({
                    type: 'GET',
                    url: 'getDirecciones.php',
                    dataType: 'json',
                    cache: false,
                    data: {"Var": dt, "Origen": "CodigoPostal"},
                    beforeSend: function (xhr) {
                        $('#Colonia').empty();
                    },
                    success: function (data) {
                        for (var dt of data)
                        {
                            $('#ColoniaCP').append($('<option>', {
                                value: dt["colonia"],
                                text: dt["codigo_postal"] + ".- " + dt["nombre"]
                            }));
                        }
                        $("#ColoniaCP").val(val1);
                    },
                    error: function (jqXHR) {
                        console.log(jqXHR);
                    }
                });
            }
        </script>

    </body>

</html>
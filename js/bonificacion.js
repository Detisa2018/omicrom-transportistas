$(document).ready(function () {
    $("#BotonBuscar").click(function () {
        var Ticket = $("#IdTicket").val();
        var cntPuntos = $("#ImporteDisponible").val();
        jQuery.ajax({
            type: "POST",
            url: "getPuntos.php",
            dataType: "json",
            cache: false,
            data: {"Op": "CalculaBonificacion", "Ticket": Ticket, "CntPuntos": cntPuntos},
            success: function (data) {
                $("#Respuesta_Ticket").html(data.Html);
                console.log("Puntos necesarios" + $("#PuntosNecesarios").val());
                console.log("Puntos del cliente" + $("#PuntosCliente").val());
                if (parseFloat($("#PuntosCliente").val()) >= parseFloat($("#PuntosNecesarios").val())) {
                    $("#BotonAceptar").show();
                } else {
                    alertTextValidation("Puntos insuficientes!", "", "Aceptar", "", false, "error", 10000, true);
                }
                $("#BotonBuscar").hide();
            }
        });
    });
    $("#BotonAceptar").click(function () {
        var Ticket = $("#IdTicket").val();
        var cntPuntos = $("#ImporteDisponible").val();
        jQuery.ajax({
            type: "POST",
            url: "getPuntos.php",
            dataType: "json",
            cache: false,
            beforeSend: function () {
                alertTextValidation("Cargando, favor de esperar ... ", "", "", "", false, "warning", 10000, false);
            },
            data: {"Op": "IngresaBonificacion", "Ticket": Ticket, "CntPuntos": cntPuntos, "IdUnidad": $("#IdUnidad").val()},
            success: function (data) {
                var typeIcon = data.Return ? "success" : "error";
                alertTextValidation(data.Html, "", "", "", false, typeIcon, 10000, true);
                data.Return ? location.reload() : false;
            }
        });
    });
    $("#Recompenza").click(function () {
        console.log($("#ProductosValue").val());
        jQuery.ajax({
            type: "POST",
            url: "getPuntos.php",
            dataType: "json",
            cache: false,
            beforeSend: function () {
                alertTextValidation("Cargando, favor de esperar ... ", "", "", "", false, "warning", 10000, false);
            },
            data: {"Op": "BonificaAditivo", "IdProducto": $("#ProductosValue").val(), "CntPuntos": $("#PuntosCliente").val(), "IdUnidad": $("#IdUnidad").val()},
            success: function (data) {
                var typeIcon = data.Return ? "success" : "error";
                alertTextValidation(data.Html, "", "", "", false, typeIcon, 10000, true);
                data.Return ? location.reload() : false;
            }
        });
    });
    $("#IniPuntos").click(function () {
        alertTextValidation("Ingresar cantidad de puntos para inicializar.", "number", "Inicializar", "", true, "question", 50000, true, "", $("#IdUnidad").val());
    });
});

function getResultado(val_Json) {
    console.log(val_Json);
    if (val_Json.Sucess) {
        jQuery.ajax({
            type: "POST",
            url: "getPuntos.php",
            dataType: "json",
            cache: false,
            data: {"Op": "InicializaPuntos", "Puntos": val_Json.Value, "IdUnidad": val_Json.IdOrigen},
            success: function (data) {
                location.reload();
            }
        });
    }
}
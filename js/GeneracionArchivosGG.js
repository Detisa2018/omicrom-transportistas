/* global Swal */
$(document).ready(function () {
    $("#CerrarCt").click(function () {
        Op = $("#OpGrupo").val();
        console.log(Op);
        switch (Op) {
            case "0":
                console.log("ENTA?");
                $(location).attr('href', "cambiotur.php?op=cr");
                break;
            case "1":
                var clientes = $("#dClientes").data("clientes");
                var tarjetas = $("#dTarjetas").data("tarjetas");
                var consignaciones = $("#dConsignaciones").data("consignaciones");
                var monederos = $("#dMonederos").data("monederos");
                var aceites = $("#dAceites").data("aceites");
                var dolares = $("#dDolares").data("dolares");
                var gastos = $("#dGastos").data("gastos");
                var efectivo = $("#dEfectivo").data("efectivo");
                var total = $("#dTotal").data("total");
                var depositos = $("#dDepositos").data("depositos");
                var corte = $("#IdCt").val();
                var idUsr = $("#IdUsr").val();
                jQuery.ajax({
                    type: "POST",
                    url: "getByAjax.php",
                    dataType: "json",
                    cache: false,
                    data: {"Op": "Log_Cortes", "id_usr": idUsr, "id_ct": corte, "clientes": clientes, "tarjetas": tarjetas, "consignaciones": consignaciones, "monederos": monederos,
                        "aceites": aceites, "dolares": dolares, "gastos": gastos, "efectivo": efectivo, "total": total, "despositos": depositos}
                });
                $(location).attr('href', "cambiotur.php?op=cr");
                break;
        }
    });
});

const inputOptions = new Promise((resolve) => {
    resolve({
        'ALL': 'Todo',
        'SALES': 'Ventas',
        'BALANCE': 'Balance',
        'TURNO': 'Turno'
    });
});
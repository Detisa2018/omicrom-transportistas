$(document).ready(function () {
    jQuery.ajax({
        type: "POST",
        url: "getByAjaxReports.php",
        beforeSend: function () {
            var e = 0;
            var txtCargando = "Cargando Balance de productos, favor de esperar";
            var pts2 = "";
            setInterval(function () {
                if (e < 4) {
                    pts2 = pts2 + ".";
                } else {
                    pts2 = ".";
                    e = 0;
                }
                e++;
                if ($("#MuestraBalance").text().length < 50) {
                    $("#MuestraBalance").html(txtCargando + pts2);
                }
            }, 800);
        },
        dataType: "json",
        cache: false,
        data: {"Origen": "GeneraBalance", "FechaInicial": $("#FechaIni").val(), "FechaFinal": $("#FechaFin").val()},
        success: function (data) {
            $("#MuestraBalance").html("");
            $("#MuestraBalance").html(data.html);
            jQuery.ajax({
                type: "POST",
                url: "getByAjaxReports.php",
                dataType: "json",
                cache: false,
                beforeSend: function () {
                    $("#FacturaAditivos").html("Cargando, favor de esperar ... ");
                },
                data: {"Origen": "FacturaAditivos", "FechaInicial": $("#FechaIni").val(), "FechaFinal": $("#FechaFin").val()},
                success: function (data) {
                    $("#FacturaAditivos").html("");
                    $("#FacturaAditivos").html(data.html);
                }
            });
        }
    });
});
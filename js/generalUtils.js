
$(document).ready(function () {
    $("#ShowAdult").click(function () {
        callVisorPedidos("Adulto");
        $("#ShowAdult").css({"background-color": "red"});
    });
    $("#ShowJoven").click(function () {
        callVisorPedidos("Joven");
    });
    $("#ShowNiño").click(function () {
        callVisorPedidos("Niño");
    });
});

function callVisorPedidos(tipoRopa) {
    $('#Contenedor').load('inventario.php?Tipo=' + tipoRopa, function (response, status, xhr) {
        if (status === "error") {
            var msg = "Sorry but there was an error: ";
            console.log(msg + xhr.status + " " + xhr.statusText);
        }
    });
}
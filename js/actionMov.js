$(document).ready(function () {
    $(".Desliga").hide();
    $(".fa-user-minus").click(function () {
        $(".Desliga").hide();
        var idCliente = this.dataset.idclienteg;
        var dirInput = 'input[type="checkbox"][data-idcliente="' + idCliente + '"]';
        $(dirInput).show();
    });
    $(".Desliga").click(function () {
        $('.botonAnimatedGreen:checked').each(function () {
            console.log($(this));
            var idcliente = $(this).data('idcliente');
            console.log('Checkbox con IDRG: ' + idcliente);
        });
    });
});


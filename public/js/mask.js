(function($) {
    "use strict";
    $.fn.modal.Constructor.prototype.enforceFocus = function() {};

    var ua = navigator.userAgent;
    var isAndroid = /Android/i.test(ua);
    var isChrome = /Chrome/i.test(ua);

    // Fix masking on Chrome for mobile devices
    if (isAndroid && isChrome) {
        $(".money, .cep, .cpf, .cnpj, .percentage, .quantity").attr(
            "type",
            "tel"
            );
    }
    alert('teste')
})

var cpfMascara = function(val) {
    return val.replace(/\D/g, "").length > 11
    ? "00.000.000/0000-00"
    : "000.000.000-009";
},
cpfOptions = {
    onKeyPress: function(val, e, field, options) {
        field.mask(cpfMascara.apply({}, arguments), options);
    }
};

$(".cpf_cnpj").mask(cpfMascara, cpfOptions);
$(".cnpj").mask("00.000.000/0000-00", {
    reverse: true
});
$(".cpf").mask("000.000.000-00", {
    reverse: true
});
$(".cep").mask("00000-000");

$('.money').mask('000000000,00', {reverse: true});
$('.input_number').mask('000000000,00', {reverse: true});


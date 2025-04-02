console.log('Lite Scheduler JS cargado');

jQuery(document).ready(function ($) {
    $('#perfil-form').on('submit', function (e) {
        console.log('Enviando datos:', datos);

        e.preventDefault();

        const datos = {
            action: 'ls_guardar_perfil',
            nonce: ls_ajax.nonce,
            nombre: $('#ls-nombre').val(),
            dias: $('input[name="dias[]"]:checked').map(function () {
                return $(this).val();
            }).get(),
            hora_inicio: $('#ls-inicio').val(),
            hora_fin: $('#ls-fin').val(),
            duracion: $('#ls-duracion').val(),
            buffer: $('#ls-buffer').val(),
        };

        $.post(ls_ajax.ajax_url, datos, function (response) {
            if (response.success) {
                $('#ls-mensaje').text(response.data).css('color', 'green');
            } else {
                $('#ls-mensaje').text('Error al guardar el perfil').css('color', 'red');
            }
        });
    });
});

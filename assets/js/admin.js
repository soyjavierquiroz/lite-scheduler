jQuery(document).ready(function ($) {
    // Guardar perfil
    $('#perfil-form').on('submit', function (e) {
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
            buffer: $('#ls-buffer').val()
        };

        $.post(ls_ajax.ajax_url, datos, function (response) {
            $('#ls-mensaje').html(response.success
                ? '<div class="notice notice-success">✅ Perfil guardado.</div>'
                : '<div class="notice notice-error">❌ Error al guardar perfil.</div>');
        });
    });

    // Eliminar reserva
    $('.ls-eliminar-reserva').on('click', function () {
        if (!confirm('¿Estás seguro de eliminar esta reserva?')) return;

        const $row = $(this).closest('tr');
        const id = $row.data('id');

        $.post(ls_ajax.ajax_url, {
            action: 'ls_eliminar_reserva',
            id: id,
            nonce: ls_ajax.nonce
        }, function (response) {
            if (response.success) {
                $row.fadeOut(300, function () { $(this).remove(); });
            } else {
                alert('Error al eliminar la reserva');
            }
        });
    });
});

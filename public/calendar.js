jQuery(document).ready(function ($) {
    const $calendario = $('#lite-scheduler-calendario');
    const perfil = $calendario.data('perfil');

    if (!perfil) {
        $calendario.html('<p>No hay horarios disponibles.</p>');
        return;
    }

    const diasActivos = perfil.dias;
    const fechaInputId = 'ls-fecha';

    const html = `
        <div class="ls-wrapper">
            <h3>Selecciona una fecha</h3>
            <input type="text" id="${fechaInputId}" class="ls-fecha" readonly />
            <div id="ls-horarios"></div>
            <div id="ls-confirmar-wrapper" style="text-align:center; margin-top:15px; display:none;">
                <button id="ls-confirmar" class="ls-btn-confirmar">Confirmar turno</button>
            </div>
            <div id="ls-confirmacion" class="ls-confirmacion"></div>
        </div>
    `;

    $calendario.html(html);

    flatpickr(`#${fechaInputId}`, {
        dateFormat: "Y-m-d",
        minDate: "today",
        disable: [
            function (date) {
                const dia = date.toLocaleDateString('es-ES', { weekday: 'long' });
                const diaCapitalizado = dia.charAt(0).toUpperCase() + dia.slice(1);
                return !diasActivos.includes(diaCapitalizado);
            }
        ],
        onChange: function (selectedDates, dateStr) {
            mostrarHorarios(dateStr);
        }
    });

    let turnoSeleccionado = null;

    function mostrarHorarios(fechaStr) {
        const horarios = generarHorarios(perfil.hora_inicio, perfil.hora_fin, parseInt(perfil.duracion), parseInt(perfil.buffer));
        let output = '<div class="ls-horarios">';

        horarios.forEach((hora) => {
            output += `<button class="ls-horario" data-hora="${hora}" data-fecha="${fechaStr}">${hora}</button>`;
        });

        output += '</div>';
        $('#ls-horarios').html(output);
        $('#ls-confirmacion').empty();
        $('#ls-confirmar-wrapper').hide();
        turnoSeleccionado = null;

        $('.ls-horario').on('click', function () {
            $('.ls-horario').removeClass('activo');
            $(this).addClass('activo');
            turnoSeleccionado = {
                hora: $(this).data('hora'),
                fecha: $(this).data('fecha')
            };
            $('#ls-confirmar-wrapper').show();
        });
    }

    $('#ls-confirmar').on('click', function () {
        if (!turnoSeleccionado) return;

        $.post(ls_ajax.ajax_url, {
            action: 'ls_guardar_reserva',
            fecha: turnoSeleccionado.fecha,
            hora: turnoSeleccionado.hora
        }, function (response) {
            if (response.success) {
                $('#ls-confirmacion').html(`✅ Turno confirmado para <strong>${turnoSeleccionado.fecha}</strong> a las <strong>${turnoSeleccionado.hora}</strong>.`);
                $('#ls-confirmar-wrapper').hide();
                $('#ls-horarios').html('');
            } else {
                $('#ls-confirmacion').html('❌ Hubo un error al guardar tu reserva.');
            }
        });
    });

    function generarHorarios(inicio, fin, duracion, buffer) {
        const result = [];
        let [h, m] = inicio.split(':').map(Number);
        const [hf, mf] = fin.split(':').map(Number);

        while (h < hf || (h === hf && m < mf)) {
            const horaActual = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
            result.push(horaActual);

            m += duracion + buffer;
            while (m >= 60) {
                h += 1;
                m -= 60;
            }
        }

        return result;
    }
});

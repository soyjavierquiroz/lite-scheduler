jQuery(document).ready(function ($) {
    const $calendario = $('#lite-scheduler-calendario');
    const perfil = $calendario.data('perfil');

    if (!perfil) return;

    const diasActivos = perfil.dias;
    let turnoSeleccionado = null;

    const html = `
        <div class="ls-wrapper">
            <h3>Selecciona una fecha</h3>
            <input type="text" id="ls-fecha" class="ls-fecha" readonly />
            <div id="ls-horarios"></div>
            <div id="ls-datos" style="display:none; margin-top:20px;">
                <input type="text" id="ls-nombre" placeholder="Tu nombre" required />
                <input type="email" id="ls-correo" placeholder="Correo electrónico" required />
                <input type="tel" id="ls-telefono" placeholder="Teléfono" />
                <button id="ls-confirmar" class="ls-btn-confirmar">Confirmar turno</button>
            </div>
            <div id="ls-confirmacion" class="ls-confirmacion"></div>
        </div>
    `;

    $calendario.html(html);

    flatpickr("#ls-fecha", {
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

    function mostrarHorarios(fechaStr) {
        const horarios = generarHorarios(perfil.hora_inicio, perfil.hora_fin, parseInt(perfil.duracion), parseInt(perfil.buffer));
        let output = '<div class="ls-horarios">';
        horarios.forEach((hora) => {
            output += `<button class="ls-horario" data-hora="${hora}" data-fecha="${fechaStr}">${hora}</button>`;
        });
        output += '</div>';
        $('#ls-horarios').html(output);
        $('#ls-confirmacion').empty();
        $('#ls-datos').hide();
        turnoSeleccionado = null;

        $('.ls-horario').on('click', function () {
            $('.ls-horario').removeClass('activo');
            $(this).addClass('activo');
            turnoSeleccionado = {
                fecha: $(this).data('fecha'),
                hora: $(this).data('hora')
            };
            $('#ls-datos').show();
        });
    }

    $('#ls-confirmar').on('click', function () {
        if (!turnoSeleccionado) return;

        const nombre = $('#ls-nombre').val();
        const correo = $('#ls-correo').val();
        const telefono = $('#ls-telefono').val();

        if (!nombre || !correo) {
            alert('Por favor, completa nombre y correo.');
            return;
        }

        $.post(ls_ajax.ajax_url, {
            action: 'ls_guardar_reserva',
            fecha: turnoSeleccionado.fecha,
            hora: turnoSeleccionado.hora,
            nombre,
            correo,
            telefono
        }, function (response) {
            if (response.success) {
                $('#ls-confirmacion').html(`✅ Turno confirmado para <strong>${turnoSeleccionado.fecha}</strong> a las <strong>${turnoSeleccionado.hora}</strong>.`);
                $('#ls-horarios').html('');
                $('#ls-datos').hide();
            } else {
                $('#ls-confirmacion').html('❌ Error al guardar la reserva.');
            }
        });
    });

    function generarHorarios(inicio, fin, duracion, buffer) {
        const result = [];
        let [h, m] = inicio.split(':').map(Number);
        const [hf, mf] = fin.split(':').map(Number);

        while (h < hf || (h === hf && m < mf)) {
            result.push(`${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`);
            m += duracion + buffer;
            while (m >= 60) {
                h += 1;
                m -= 60;
            }
        }

        return result;
    }
});

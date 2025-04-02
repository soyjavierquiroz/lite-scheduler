<?php
// includes/admin.php

if (!defined('ABSPATH')) exit;

add_action('admin_menu', function () {
    add_menu_page(
        'Lite Scheduler',
        'Lite Scheduler',
        'manage_options',
        'lite-scheduler',
        'ls_render_configuracion',
        'dashicons-calendar-alt',
        25
    );

    add_submenu_page(
        'lite-scheduler',
        'Reservas',
        'Reservas',
        'manage_options',
        'ls-reservas',
        'ls_render_reservas'
    );
});

add_action('admin_enqueue_scripts', function ($hook) {
    if (strpos($hook, 'lite-scheduler') === false) return;

    wp_enqueue_style('ls-admin-css', LS_URL . 'assets/css/admin.css');
    wp_enqueue_script('jquery');
    wp_enqueue_script('ls-admin-js', LS_URL . 'assets/js/admin.js', ['jquery'], null, true);

    wp_localize_script('ls-admin-js', 'ls_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('ls_admin_nonce')
    ]);
});

function ls_render_configuracion() {
    $perfil = get_option('ls_perfil');
    ?>
    <div class="wrap">
        <h1>Configuración del Perfil de Atención</h1>
        <form id="perfil-form" class="perfil-form">
            <label for="ls-nombre">Nombre del perfil</label>
            <input type="text" id="ls-nombre" name="nombre" value="<?= esc_attr($perfil['nombre'] ?? '') ?>" required>

            <label>Días activos</label>
            <div class="dias-checkboxes">
                <?php
                $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                foreach ($dias as $dia) {
                    $checked = in_array($dia, $perfil['dias'] ?? []) ? 'checked' : '';
                    echo "<label><input type='checkbox' name='dias[]' value='{$dia}' $checked> {$dia}</label>";
                }
                ?>
            </div>

            <label for="ls-inicio">Hora de inicio</label>
            <input type="time" id="ls-inicio" name="hora_inicio" value="<?= esc_attr($perfil['hora_inicio'] ?? '') ?>" required>

            <label for="ls-fin">Hora de fin</label>
            <input type="time" id="ls-fin" name="hora_fin" value="<?= esc_attr($perfil['hora_fin'] ?? '') ?>" required>

            <label for="ls-duracion">Duración de cita (min)</label>
            <input type="number" id="ls-duracion" name="duracion" value="<?= esc_attr($perfil['duracion'] ?? 30) ?>" required>

            <label for="ls-buffer">Buffer entre citas (min)</label>
            <input type="number" id="ls-buffer" name="buffer" value="<?= esc_attr($perfil['buffer'] ?? 10) ?>" required>

            <button type="submit">Guardar Perfil</button>
        </form>
        <div id="ls-mensaje"></div>
    </div>
    <?php
}

function ls_render_reservas() {
    global $wpdb;
    $tabla = $wpdb->prefix . 'ls_reservas';
    $reservas = $wpdb->get_results("SELECT * FROM $tabla ORDER BY fecha DESC, hora DESC");

    echo '<div class="wrap"><h1>Reservas</h1>';
    if (empty($reservas)) {
        echo '<p>No hay reservas aún.</p></div>';
        return;
    }

    echo '<table class="widefat striped"><thead><tr>';
    echo '<th>Fecha</th><th>Hora</th><th>Nombre</th><th>Correo</th><th>Teléfono</th><th>Acción</th>';
    echo '</tr></thead><tbody>';

    foreach ($reservas as $r) {
        echo '<tr data-id="' . esc_attr($r->id) . '">';
        echo '<td>' . esc_html($r->fecha) . '</td>';
        echo '<td>' . esc_html($r->hora) . '</td>';
        echo '<td>' . esc_html($r->nombre) . '</td>';
        echo '<td>' . esc_html($r->correo) . '</td>';
        echo '<td>' . esc_html($r->telefono) . '</td>';
        echo '<td><button class="button ls-eliminar-reserva">Eliminar</button></td>';
        echo '</tr>';
    }

    echo '</tbody></table></div>';
}

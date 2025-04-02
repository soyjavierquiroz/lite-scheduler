<?php
/**
 * Plugin Name: Lite Scheduler
 * Description: Plugin simple para agendar turnos y configurar perfiles de atención.
 * Version: 1.2.0
 * Author: Tu Nombre
 * Text Domain: lite-scheduler
 */

if (!defined('ABSPATH')) exit;

/**
 * Admin menu
 */
add_action('admin_menu', function () {
    add_menu_page(
        'Lite Scheduler',
        'Lite Scheduler',
        'manage_options',
        'lite-scheduler',
        'ls_render_admin_page',
        'dashicons-calendar-alt',
        25
    );

    // Submenú: Reservas
    add_submenu_page(
        'lite-scheduler',
        'Reservas',
        'Reservas',
        'manage_options',
        'ls-reservas',
        'ls_render_reservas_page'
    );
});

/**
 * Admin scripts
 */
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'toplevel_page_lite-scheduler') return;

    wp_enqueue_style('ls-admin-css', plugins_url('admin/admin.css', __FILE__));
    wp_enqueue_script('jquery');
    wp_enqueue_script('ls-admin-js', plugins_url('admin/admin.js', __FILE__), ['jquery'], null, true);

    wp_localize_script('ls-admin-js', 'ls_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('ls_guardar_perfil')
    ]);
});

/**
 * Guardar perfil vía AJAX
 */
add_action('wp_ajax_ls_guardar_perfil', function () {
    check_ajax_referer('ls_guardar_perfil', 'nonce');

    $datos = [
        'nombre'      => sanitize_text_field($_POST['nombre']),
        'dias'        => array_map('sanitize_text_field', $_POST['dias'] ?? []),
        'hora_inicio' => sanitize_text_field($_POST['hora_inicio']),
        'hora_fin'    => sanitize_text_field($_POST['hora_fin']),
        'duracion'    => intval($_POST['duracion']),
        'buffer'      => intval($_POST['buffer']),
    ];

    update_option('ls_perfil', $datos);
    wp_send_json_success('Perfil guardado correctamente');
});

/**
 * Página de configuración en el admin
 */
function ls_render_admin_page() {
    ?>
    <div class="wrap">
        <h1>Perfiles de Atención</h1>
        <form id="perfil-form" class="perfil-form">
            <label for="ls-nombre">Nombre del perfil</label>
            <input type="text" id="ls-nombre" name="nombre" required>

            <label>Días activos</label>
            <div class="dias-checkboxes">
                <?php
                $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                foreach ($dias as $dia) {
                    echo "<label><input type='checkbox' name='dias[]' value='{$dia}'> {$dia}</label>";
                }
                ?>
            </div>

            <label for="ls-inicio">Hora de inicio</label>
            <input type="time" id="ls-inicio" name="hora_inicio" required>

            <label for="ls-fin">Hora de fin</label>
            <input type="time" id="ls-fin" name="hora_fin" required>

            <label for="ls-duracion">Duración de cita (min)</label>
            <input type="number" id="ls-duracion" name="duracion" min="1" value="30" required>

            <label for="ls-buffer">Buffer entre citas (min)</label>
            <input type="number" id="ls-buffer" name="buffer" min="0" value="10" required>

            <button type="submit">Guardar Perfil</button>
        </form>
        <div id="ls-mensaje"></div>
    </div>
    <?php
}

/**
 * Shortcode frontend [lite_scheduler]
 */
add_shortcode('lite_scheduler', function () {
    $perfil = get_option('ls_perfil');
    if (!$perfil) return '<p>No hay disponibilidad configurada.</p>';

    ob_start();
    ?>
    <div id="lite-scheduler-calendario" data-perfil='<?php echo json_encode($perfil); ?>'></div>
    <?php
    return ob_get_clean();
});

/**
 * Scripts del frontend
 */
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('flatpickr-css', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');
    wp_enqueue_script('jquery');
    wp_enqueue_script('flatpickr-js', 'https://cdn.jsdelivr.net/npm/flatpickr', ['jquery'], null, true);

    wp_enqueue_style('ls-calendar-css', plugins_url('public/calendar.css', __FILE__));
    wp_enqueue_script('ls-calendar-js', plugins_url('public/calendar.js', __FILE__), ['jquery', 'flatpickr-js'], null, true);

    wp_localize_script('ls-calendar-js', 'ls_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
    ]);
});

/**
 * Crear tabla de reservas al activar plugin
 */
register_activation_hook(__FILE__, 'ls_crear_tabla_reservas');

function ls_crear_tabla_reservas() {
    global $wpdb;
    $tabla = $wpdb->prefix . 'ls_reservas';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $tabla (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        fecha DATE NOT NULL,
        hora TIME NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * AJAX: guardar reserva
 */
add_action('wp_ajax_ls_guardar_reserva', 'ls_guardar_reserva');
add_action('wp_ajax_nopriv_ls_guardar_reserva', 'ls_guardar_reserva');

function ls_guardar_reserva() {
    global $wpdb;

    $fecha = sanitize_text_field($_POST['fecha'] ?? '');
    $hora  = sanitize_text_field($_POST['hora'] ?? '');

    if (!$fecha || !$hora) {
        wp_send_json_error('Faltan datos');
    }

    $tabla = $wpdb->prefix . 'ls_reservas';
    $wpdb->insert($tabla, [
        'fecha' => $fecha,
        'hora'  => $hora,
    ]);

    wp_send_json_success('Reserva guardada');
}

/**
 * Página de administración de reservas
 */
function ls_render_reservas_page() {
    global $wpdb;
    $tabla = $wpdb->prefix . 'ls_reservas';
    $reservas = $wpdb->get_results("SELECT * FROM $tabla ORDER BY fecha DESC, hora DESC");

    echo '<div class="wrap"><h1>Reservas Agendadas</h1>';

    if (empty($reservas)) {
        echo '<p>No hay reservas aún.</p></div>';
        return;
    }

    echo '<table class="widefat fixed striped" id="ls-tabla-reservas"><thead><tr>';
    echo '<th>Fecha</th><th>Hora</th><th>Creado</th><th>Acciones</th>';
    echo '</tr></thead><tbody>';

    foreach ($reservas as $r) {
        echo '<tr data-id="' . esc_attr($r->id) . '">';
        echo '<td>' . esc_html($r->fecha) . '</td>';
        echo '<td>' . esc_html($r->hora) . '</td>';
        echo '<td>' . esc_html($r->created_at) . '</td>';
        echo '<td><button class="button button-small ls-eliminar-reserva">Eliminar</button></td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
    echo '<script>
    jQuery(document).ready(function($){
        $(".ls-eliminar-reserva").click(function(){
            if (!confirm("¿Seguro que querés eliminar esta reserva?")) return;
            const $row = $(this).closest("tr");
            const id = $row.data("id");

            $.post(ajaxurl, {
                action: "ls_eliminar_reserva",
                id: id,
                nonce: "' . wp_create_nonce('ls_eliminar_reserva') . '"
            }, function(response) {
                if (response.success) {
                    $row.fadeOut(300, function(){ $(this).remove(); });
                } else {
                    alert("Error al eliminar la reserva.");
                }
            });
        });
    });
    </script>';
    echo '</div>';
}

add_action('wp_ajax_ls_eliminar_reserva', function () {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Sin permisos');
    }

    if (!check_ajax_referer('ls_eliminar_reserva', 'nonce', false)) {
        wp_send_json_error('Nonce inválido');
    }

    global $wpdb;
    $id = intval($_POST['id'] ?? 0);
    if ($id > 0) {
        $tabla = $wpdb->prefix . 'ls_reservas';
        $wpdb->delete($tabla, ['id' => $id]);
        wp_send_json_success('Eliminado');
    }

    wp_send_json_error('ID inválido');
});

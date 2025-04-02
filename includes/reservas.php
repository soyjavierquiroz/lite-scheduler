<?php
// includes/reservas.php

if (!defined('ABSPATH')) exit;

/**
 * Guardar reserva desde frontend
 */
add_action('wp_ajax_ls_guardar_reserva', 'ls_guardar_reserva');
add_action('wp_ajax_nopriv_ls_guardar_reserva', 'ls_guardar_reserva');

function ls_guardar_reserva() {
    global $wpdb;

    $fecha    = sanitize_text_field($_POST['fecha'] ?? '');
    $hora     = sanitize_text_field($_POST['hora'] ?? '');
    $nombre   = sanitize_text_field($_POST['nombre'] ?? '');
    $correo   = sanitize_email($_POST['correo'] ?? '');
    $telefono = sanitize_text_field($_POST['telefono'] ?? '');

    if (!$fecha || !$hora) {
        wp_send_json_error('Faltan datos obligatorios.');
    }

    $tabla = $wpdb->prefix . 'ls_reservas';
    $wpdb->insert($tabla, [
        'fecha'    => $fecha,
        'hora'     => $hora,
        'nombre'   => $nombre,
        'correo'   => $correo,
        'telefono' => $telefono
    ]);

    wp_send_json_success('Reserva guardada con éxito.');
}

/**
 * Eliminar reserva desde el admin
 */
add_action('wp_ajax_ls_eliminar_reserva', function () {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Sin permisos');
    }

    if (!check_ajax_referer('ls_admin_nonce', 'nonce', false)) {
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

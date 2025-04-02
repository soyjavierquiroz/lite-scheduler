<?php
// includes/init.php

if (!defined('ABSPATH')) exit;

register_activation_hook(__FILE__, 'ls_crear_tabla_reservas');

function ls_crear_tabla_reservas() {
    global $wpdb;
    $tabla = $wpdb->prefix . 'ls_reservas';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $tabla (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        fecha DATE NOT NULL,
        hora TIME NOT NULL,
        nombre VARCHAR(255) DEFAULT '',
        correo VARCHAR(255) DEFAULT '',
        telefono VARCHAR(50) DEFAULT '',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

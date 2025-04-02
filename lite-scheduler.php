<?php
/**
 * Plugin Name: Lite Scheduler
 * Description: Plugin modular para agendar citas, con perfiles de atención, reservas, integración visual y extensiones futuras.
 * Version: 2.0.0
 * Author: Tu Nombre
 * Text Domain: lite-scheduler
 */

if (!defined('ABSPATH')) exit;

// Definir constantes
define('LS_DIR', plugin_dir_path(__FILE__));
define('LS_URL', plugin_dir_url(__FILE__));

// Cargar archivos del núcleo
require_once LS_DIR . 'includes/init.php';
require_once LS_DIR . 'includes/admin.php';
require_once LS_DIR . 'includes/reservas.php';
require_once LS_DIR . 'includes/calendar-render.php';

// 🚀 Listo para agregar más:
// require_once LS_DIR . 'includes/google-sync.php';
// require_once LS_DIR . 'includes/api-webhooks.php';
// require_once LS_DIR . 'includes/notifications.php';

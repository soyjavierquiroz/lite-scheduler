<?php
// includes/calendar-render.php

if (!defined('ABSPATH')) exit;

add_shortcode('lite_scheduler', function () {
    $perfil = get_option('ls_perfil');
    if (!$perfil) return '<p>No hay disponibilidad configurada.</p>';

    ob_start();
    ?>
    <div id="lite-scheduler-calendario" data-perfil='<?php echo esc_attr(json_encode($perfil)); ?>'></div>
    <?php
    return ob_get_clean();
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('flatpickr-css', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');
    wp_enqueue_script('jquery');
    wp_enqueue_script('flatpickr-js', 'https://cdn.jsdelivr.net/npm/flatpickr', ['jquery'], null, true);

    wp_enqueue_style('ls-calendar-css', LS_URL . 'assets/css/calendar.css');
    wp_enqueue_script('ls-calendar-js', LS_URL . 'assets/js/calendar.js', ['jquery', 'flatpickr-js'], null, true);

    wp_localize_script('ls-calendar-js', 'ls_ajax', [
        'ajax_url' => admin_url('admin-ajax.php')
    ]);
});

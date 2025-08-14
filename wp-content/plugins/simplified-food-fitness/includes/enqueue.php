<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function sff_enqueue_assets() {
    // ✅ Load jQuery
    wp_enqueue_script('jquery');

    // ✅ Main Script with jQuery dependency
    wp_enqueue_script(
        'sff-scripts',
        SFF_PLUGIN_URL . 'assets/js/sff-scripts.js',
        ['jquery'],
        '1.0.0',
        true
    );

    wp_enqueue_script(
        'sff-dashboard-menu',
        SFF_PLUGIN_URL . 'assets/js/dashboard-menu.js',
        [],
        '1.0.0',
        true
    );

    // ✅ Localize AJAX object
    wp_localize_script('sff-scripts', 'sff_ajax_obj', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('sff_scan_nonce')
    ]);

    // ✅ CSS
    wp_enqueue_style('sff-styles', SFF_PLUGIN_URL . 'assets/css/sff-styles.css', [], '1.0.2');
}
add_action('wp_enqueue_scripts', 'sff_enqueue_assets');

// ❌ REMOVE this if you’re not using the script in wp-admin:
// add_action('admin_enqueue_scripts', 'sff_enqueue_assets');

// ❌ Remove this duplication (you already enqueued it above):
// function sff_enqueue_global_styles() {...}
// add_action('wp_enqueue_scripts', 'sff_enqueue_global_styles');

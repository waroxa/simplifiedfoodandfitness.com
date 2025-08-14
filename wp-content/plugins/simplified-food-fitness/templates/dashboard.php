<?php
/* Template Name: SFF Dashboard */
if (!defined('ABSPATH')) {
    exit;
}

// Ensure plugin styles are loaded
echo '<link rel="stylesheet" href="' . SFF_PLUGIN_URL . 'assets/css/sff-styles.css?ver=1.0.2">';

if (!is_user_logged_in()) {
    if (function_exists('sff_custom_login_form')) {
        echo sff_custom_login_form();
    } else {
        wp_login_form();
    }
    return;
}

echo do_shortcode('[sff_meal_dashboard]');
?>

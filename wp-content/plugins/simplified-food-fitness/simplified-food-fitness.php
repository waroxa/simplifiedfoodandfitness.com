<?php
/*
Plugin Name: Simplified Food & Fitness Macro Tracker
Description: A custom plugin for managing client meal plans, macro targets, and grocery lists with a mobile-first design.
Version: 1.0.2
Author: Simplified Food & Fitness
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define constants for paths
define('SFF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SFF_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include all feature files
require_once SFF_PLUGIN_DIR . 'includes/post-types.php';
require_once SFF_PLUGIN_DIR . 'includes/meta-boxes.php';
require_once SFF_PLUGIN_DIR . 'includes/shortcodes.php';
require_once SFF_PLUGIN_DIR . 'includes/ajax.php';
require_once SFF_PLUGIN_DIR . 'includes/settings.php';
require_once SFF_PLUGIN_DIR . 'includes/enqueue.php';
require_once SFF_PLUGIN_DIR . 'includes/helpers.php';

// Enqueue custom styles for lead profiles
function sff_enqueue_custom_styles() {
    wp_enqueue_style('sff-custom-styles', SFF_PLUGIN_URL . 'assets/css/sff-styles.css', array(), '1.0.0', 'all');
}
add_action('wp_enqueue_scripts', 'sff_enqueue_custom_styles');

// Force WordPress to use custom single template for client_leads
function sff_load_client_leads_template($template) {
    if (is_singular('client_leads')) {
        return SFF_PLUGIN_DIR . 'templates/single-client_leads.php';
    }
    return $template;
}
add_filter('single_template', 'sff_load_client_leads_template');

// Add logo and header to client leads page
function sff_client_leads_logo_header() {
    $logo_url = "https://simplifiedfoodandfitness.com/wp-content/uploads/2024/10/3.png";
    echo '<div class="dashboard-container" style="max-width:1200px; margin:auto; padding:20px; font-family:Segoe UI, Arial, sans-serif;">';
    echo '<div style="display:flex; align-items:center; justify-content:space-between; gap:15px; flex-wrap:wrap; text-align:left; margin-bottom:30px;">';
    echo '<div style="flex-shrink:0;">';
    echo '<img src="' . esc_url($logo_url) . '" alt="Logo" style="height:70px; width:auto; max-width:200px;">';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
// add_action('wp_head', 'sff_client_leads_logo_header');

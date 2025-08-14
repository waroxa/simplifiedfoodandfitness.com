<?php
/*
Plugin Name: Simplified Food & Fitness Macro Tracker
Description: A custom plugin for managing client meal plans, macro targets, and grocery lists with a mobile-first design.
Version: 1.1.0
Author: Simplified Food & Fitness
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define constants for paths
define('SFF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SFF_PLUGIN_URL', plugin_dir_url(__FILE__));

define('SFF_PLUGIN_VERSION', '1.1.0');
if (!defined('SFF_MACRO_FIELDS')) {
    define('SFF_MACRO_FIELDS', [
        'calories', 'carbs', 'protein', 'fat',
        'saturated_fat', 'trans_fat', 'cholesterol', 'sodium',
        'fiber', 'sugars', 'added_sugars', 'vitamin_d', 'calcium',
        'iron', 'potassium', 'magnesium', 'vitamin_a', 'vitamin_c',
        'vitamin_e', 'zinc', 'folate', 'riboflavin', 'niacin',
        'vitamin_b6', 'vitamin_b12', 'thiamin'
    ]);
}

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

// Create profile page with client profile shortcode on activation
function sff_create_profile_page() {
    $slug = 'my-profile';
    $page = get_page_by_path($slug);
    if (!$page) {
        wp_insert_post([
            'post_title'   => 'My Profile',
            'post_name'    => $slug,
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => '[sff_client_profile]'
        ]);
    } elseif (strpos($page->post_content, '[sff_client_profile]') === false) {
        $page->post_content .= "\n[sff_client_profile]";
        wp_update_post([
            'ID' => $page->ID,
            'post_content' => $page->post_content,
        ]);
    }
}

function sff_install_tables() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'sff_ingredient_nutrition';
    $charset_collate = $wpdb->get_charset_collate();

    $columns = '';
    foreach (SFF_MACRO_FIELDS as $field) {
        $columns .= "$field FLOAT DEFAULT 0,\n";
    }

    $sql = "CREATE TABLE $table_name (
        ingredient_id BIGINT(20) UNSIGNED NOT NULL,
        $columns
        cost DECIMAL(10,2) DEFAULT 0,
        PRIMARY KEY  (ingredient_id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

function sff_migrate_macros_from_meta() {
    $ids = get_posts([
        'post_type' => 'ingredient',
        'numberposts' => -1,
        'fields' => 'ids'
    ]);
    if (empty($ids)) {
        return;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'sff_ingredient_nutrition';
    $formats = array_merge(['%d'], array_fill(0, count(SFF_MACRO_FIELDS), '%f'), ['%f']);

    foreach ($ids as $id) {
        $macros = get_post_meta($id, '_sff_macros', true);
        $data = array_merge(['ingredient_id' => $id], array_fill_keys(SFF_MACRO_FIELDS, 0));
        if (is_array($macros)) {
            foreach (SFF_MACRO_FIELDS as $field) {
                $data[$field] = isset($macros[$field]) ? floatval($macros[$field]) : 0;
            }
        }
        $data['cost'] = 0;
        $exists = $wpdb->get_var($wpdb->prepare("SELECT ingredient_id FROM $table WHERE ingredient_id = %d", $id));
        if ($exists) {
            $wpdb->update($table, $data, ['ingredient_id' => $id], $formats, ['%d']);
        } else {
            $wpdb->insert($table, $data, $formats);
        }
    }
}

function sff_plugin_activate() {
    sff_create_profile_page();
    sff_install_tables();
    sff_migrate_macros_from_meta();
    update_option('sff_plugin_version', SFF_PLUGIN_VERSION);
}
register_activation_hook(__FILE__, 'sff_plugin_activate');

function sff_plugin_update_check() {
    $installed = get_option('sff_plugin_version');
    if ($installed !== SFF_PLUGIN_VERSION) {
        sff_install_tables();
        sff_migrate_macros_from_meta();
        update_option('sff_plugin_version', SFF_PLUGIN_VERSION);
    }
}
add_action('init', 'sff_plugin_update_check', 20);

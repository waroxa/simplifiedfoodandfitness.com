<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function sff_add_settings_menu() {
    add_options_page(
        'Simplified Nutrition Label Settings',
        'Nutrition Label Settings',
        'manage_options',
        'sff-nutrition-settings',
        'sff_render_settings_page'
    );
}
add_action('admin_menu', 'sff_add_settings_menu');

function sff_render_settings_page() {
   ?>
    <div class="wrap">
        <h2>API Settings</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('sff_nutrition_settings');
            do_settings_sections('sff-nutrition-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function sff_register_settings() {
    register_setting('sff_nutrition_settings', 'sff_google_api_key');
    register_setting('sff_nutrition_settings', 'sff_usda_api_key');
    register_setting(
        'sff_nutrition_settings',
        'sff_day_type_macros',
        [
            'type'              => 'array',
            'sanitize_callback' => 'sff_sanitize_day_type_macros',
            'default'           => sff_get_day_type_macros(false),
        ]
    );

    add_settings_section('sff_api_settings_section', 'API Configuration', null, 'sff-nutrition-settings');
    add_settings_section('sff_day_types_section', __('Macro Day Types', 'simplified-food-fitness'), 'sff_render_day_types_intro', 'sff-nutrition-settings');

    add_settings_field(
        'sff_google_api_key',
        'Google Cloud Vision API Key',
        'sff_render_api_key_field',
        'sff-nutrition-settings',
        'sff_api_settings_section'
    );

    add_settings_field(
        'sff_usda_api_key',
        'USDA API Key',
        'sff_render_usda_api_key_field',
        'sff-nutrition-settings',
        'sff_api_settings_section'
    );

    add_settings_field(
        'sff_day_type_macros',
        __('Day Type Macro Percentages', 'simplified-food-fitness'),
        'sff_render_day_type_macros_field',
        'sff-nutrition-settings',
        'sff_day_types_section'
    );
}
add_action('admin_init', 'sff_register_settings');

function sff_render_day_types_intro() {
    echo '<p>' . esc_html__('Define the macro percentage splits that should be used when planning rest, active, and training days.', 'simplified-food-fitness') . '</p>';
}

function sff_render_api_key_field() {
    $api_key = get_option('sff_google_api_key', '');
    echo '<input type="text" name="sff_google_api_key" value="' . esc_attr($api_key) . '" style="width: 400px;">';
}

function sff_render_usda_api_key_field() {
    $api_key = get_option('sff_usda_api_key', '');
    echo '<input type="text" name="sff_usda_api_key" value="' . esc_attr($api_key) . '" style="width: 400px;">';
}

function sff_render_day_type_macros_field() {
    $configs = sff_get_day_type_macros();
    echo '<table class="widefat striped" style="max-width:640px">';
    echo '<thead><tr><th>' . esc_html__('Day Type', 'simplified-food-fitness') . '</th><th>' . esc_html__('Carbs %', 'simplified-food-fitness') . '</th><th>' . esc_html__('Protein %', 'simplified-food-fitness') . '</th><th>' . esc_html__('Fat %', 'simplified-food-fitness') . '</th></tr></thead><tbody>';
    foreach ($configs as $slug => $config) {
        echo '<tr>';
        echo '<th scope="row">' . esc_html($config['label']) . '</th>';
        echo '<td><input type="number" step="0.1" min="0" max="100" name="sff_day_type_macros[' . esc_attr($slug) . '][carbs]" value="' . esc_attr($config['carbs']) . '" /></td>';
        echo '<td><input type="number" step="0.1" min="0" max="100" name="sff_day_type_macros[' . esc_attr($slug) . '][protein]" value="' . esc_attr($config['protein']) . '" /></td>';
        echo '<td><input type="number" step="0.1" min="0" max="100" name="sff_day_type_macros[' . esc_attr($slug) . '][fat]" value="' . esc_attr($config['fat']) . '" /></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
    echo '<p class="description">' . esc_html__('Percentages are used to calculate daily macro goals for the weekly meal calendar.', 'simplified-food-fitness') . '</p>';
}

function sff_sanitize_day_type_macros($input) {
    $defaults = sff_get_default_day_type_macros();
    $output   = [];

    foreach ($defaults as $slug => $default) {
        $row = isset($input[$slug]) && is_array($input[$slug]) ? $input[$slug] : [];
        $output[$slug] = [
            'label'   => $default['label'],
            'carbs'   => isset($row['carbs']) ? floatval($row['carbs']) : $default['carbs'],
            'protein' => isset($row['protein']) ? floatval($row['protein']) : $default['protein'],
            'fat'     => isset($row['fat']) ? floatval($row['fat']) : $default['fat'],
        ];
    }

    return $output;
}

// function sff_client_leads_dashboard() {
//     add_menu_page(
//         'Client Leads',
//         'Client Leads',
//         'manage_options',
//         'sff-client-leads',
//         'sff_render_client_leads_page',
//         'dashicons-admin-users',
//         6
//     );
// }
// add_action('admin_menu', 'sff_client_leads_dashboard');

// function sff_render_client_leads_page() {
//     echo '<div class="wrap"><h1>Client Leads</h1>';
//     $args = [
//         'post_type' => 'client_leads',
//         'posts_per_page' => -1
//     ];
//     $leads = get_posts($args);

//     if (!$leads) {
//         echo '<p>No leads found.</p>';
//         return;
//     }

//     echo '<table class="wp-list-table widefat fixed striped">';
//     echo '<thead><tr><th>Name</th><th>Date</th><th>View</th></tr></thead><tbody>';
//     foreach ($leads as $lead) {
//         echo '<tr>';
//         echo '<td>' . esc_html($lead->post_title) . '</td>';
//         echo '<td>' . esc_html($lead->post_date) . '</td>';
//         echo '<td><a href="' . get_edit_post_link($lead->ID) . '">View</a></td>';
//         echo '</tr>';
//     }
//     echo '</tbody></table></div>';
// }

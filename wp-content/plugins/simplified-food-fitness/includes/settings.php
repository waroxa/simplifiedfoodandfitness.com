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
        <h2>Google Vision API Settings</h2>
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
    add_settings_section('sff_api_settings_section', 'API Configuration', null, 'sff-nutrition-settings');
    add_settings_field('sff_google_api_key', 'Google Cloud Vision API Key', 'sff_render_api_key_field', 'sff-nutrition-settings', 'sff_api_settings_section');
}
add_action('admin_init', 'sff_register_settings');

function sff_render_api_key_field() {
    $api_key = get_option('sff_google_api_key', '');
    echo '<input type="text" name="sff_google_api_key" value="' . esc_attr($api_key) . '" style="width: 400px;">';
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

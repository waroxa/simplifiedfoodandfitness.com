<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function sff_register_custom_post_types() {
    register_post_type('meal_plan', [
        'labels' => [
            'name' => __('Meal Plans'),
            'singular_name' => __('Meal Plan')
        ],
        'public' => false,
        'has_archive' => false,
        'show_ui' => true,
        'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
    ]);

    register_post_type('macro_target', [
    'labels' => [
        'name' => __('Macro Targets'),
        'singular_name' => __('Macro Target')
    ],
        'public' => false,
        'has_archive' => false,
        'show_ui' => true,
        'supports' => ['title', 'custom-fields'],
        'menu_icon' => 'dashicons-chart-bar',
    ]);

    register_post_type('ingredient', [
        'labels' => [
            'name' => __('Ingredients'),
            'singular_name' => __('Ingredient')
        ],
        'public' => false,
        'show_ui' => true,
        'supports' => ['title', 'custom-fields'],
        'menu_icon' => 'dashicons-carrot'
    ]);

    register_post_type('client_leads', [
    'labels' => [
        'name' => __('Leads'),
        'singular_name' => __('Client Lead')
    ],
    'public' => true,  // <-- Now leads have a URL
    'has_archive' => true, // <-- Leads can have an archive page
    'show_ui' => true,
    'menu_position' => 5,
    'supports' => ['title', 'custom-fields'],
    'menu_icon' => 'dashicons-admin-users'
]);

    register_post_type('clients', [
    'labels' => [
        'name' => __('Clients'),
        'singular_name' => __('Client'),
    ],
    'public' => true,
    'has_archive' => true,
    'show_ui' => true,
    'supports' => ['title', 'custom-fields'],
    'menu_icon' => 'dashicons-businessman', // WordPress icon
    'menu_position' => 6
]);


}
add_action('init', 'sff_register_custom_post_types');


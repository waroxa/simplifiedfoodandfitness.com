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
        // Removed default custom fields to keep the UI clean. Bespoke meta boxes handle data entry.
        'supports' => ['title'],
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

    register_post_type('recipe', [
        'labels' => [
            'name' => __('Recipes'),
            'singular_name' => __('Recipe')
        ],
        'public' => false,
        'show_ui' => true,
        'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
        'menu_icon' => 'dashicons-book-alt'
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

    register_taxonomy('ingredient_category', ['ingredient'], [
        'labels' => [
            'name' => __('Ingredient Categories'),
            'singular_name' => __('Ingredient Category')
        ],
        'public' => false,
        'show_ui' => true,
        'show_admin_column' => true,
        'hierarchical' => true,
    ]);

    $default_terms = ['Meals', 'Vegetables', 'Fruits', 'Proteins', 'Grains'];
    foreach ($default_terms as $term) {
        if (!term_exists($term, 'ingredient_category')) {
            wp_insert_term($term, 'ingredient_category');
        }
    }


}
add_action('init', 'sff_register_custom_post_types');

function sff_ingredient_category_admin_filter() {
    global $typenow;
    if ($typenow === 'ingredient') {
        $taxonomy = 'ingredient_category';
        $selected = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
        wp_dropdown_categories([
            'show_option_all' => __('All Categories'),
            'taxonomy' => $taxonomy,
            'name' => $taxonomy,
            'orderby' => 'name',
            'selected' => $selected,
            'hierarchical' => true,
            'hide_empty' => false,
        ]);
    }
}
add_action('restrict_manage_posts', 'sff_ingredient_category_admin_filter');

function sff_ingredient_category_filter_query($query) {
    global $pagenow;
    $taxonomy = 'ingredient_category';
    if ($pagenow === 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'ingredient' && !empty($_GET[$taxonomy])) {
        $term = intval($_GET[$taxonomy]);
        $query->query_vars[$taxonomy] = $term;
    }
}
add_filter('parse_query', 'sff_ingredient_category_filter_query');


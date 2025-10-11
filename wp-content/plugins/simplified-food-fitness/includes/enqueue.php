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
        'sff-recipe-nutrition',
        SFF_PLUGIN_URL . 'assets/js/recipe-nutrition.js',
        ['jquery'],
        '1.0.0',
        true
    );

    // ✅ Localize AJAX object
    $macro_group_macros = [
        'calories', 'carbs', 'protein', 'fat',
        'saturated_fat', 'trans_fat', 'cholesterol', 'sodium',
        'fiber', 'sugars', 'added_sugars',
    ];

    $macro_group_micros = array_values(array_diff(SFF_MACRO_FIELDS, $macro_group_macros));

    wp_localize_script('sff-scripts', 'sff_ajax_obj', [
        'ajax_url'          => admin_url('admin-ajax.php'),
        'nonce'             => wp_create_nonce('sff_scan_nonce'),
        'macro_fields'      => SFF_MACRO_FIELDS,
        'macro_groups'      => [
            'macros' => $macro_group_macros,
            'micros' => $macro_group_micros,
        ],
        'recipe_empty_text' => __('Select ingredients to see nutrition details.', 'simplified-food-fitness'),
        'recipe_labels'     => [
            'ingredient_single' => __('ingredient selected', 'simplified-food-fitness'),
            'ingredient_plural' => __('ingredients selected', 'simplified-food-fitness'),
        ],
        'show_usda_details' => current_user_can('manage_options'),
    ]);

    // Dashboard interactions
    wp_enqueue_script(
        'sff-dashboard',
        SFF_PLUGIN_URL . 'assets/js/dashboard.js',
        [],
        '1.0.0',
        true
    );

    wp_localize_script('sff-dashboard', 'sff_dashboard', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('sff_dashboard_nonce'),
    ]);

    // ✅ CSS
    wp_enqueue_style('sff-styles', SFF_PLUGIN_URL . 'assets/css/sff-styles.css', [], '1.0.3');

    if (is_admin() && function_exists('get_current_screen')) {
        $screen = get_current_screen();

        if ($screen && $screen->post_type === 'recipe') {
            wp_enqueue_media();
            wp_enqueue_script(
                'sff-recipe-media',
                SFF_PLUGIN_URL . 'assets/js/recipe-media.js',
                ['jquery'],
                '1.0.0',
                true
            );

            $post_id = 0;
            if (isset($_GET['post'])) {
                $post_id = absint($_GET['post']);
            } elseif (isset($_POST['post_ID'])) {
                $post_id = absint($_POST['post_ID']);
            }

            $cover_id    = $post_id ? (int) get_post_meta($post_id, '_sff_recipe_cover_id', true) : 0;
            $gallery_ids = $post_id ? get_post_meta($post_id, '_sff_recipe_gallery_ids', true) : [];
            if (!is_array($gallery_ids)) {
                $gallery_ids = array_filter(array_map('intval', (array) $gallery_ids));
            }

            $media_items = [];
            $ids_to_prepare = array_unique(array_merge($cover_id ? [$cover_id] : [], $gallery_ids));
            foreach ($ids_to_prepare as $media_id) {
                $prepared = wp_prepare_attachment_for_js($media_id);
                if ($prepared) {
                    $media_items[$media_id] = $prepared;
                }
            }

            wp_localize_script('sff-recipe-media', 'sffRecipeMediaData', [
                'coverId'    => $cover_id,
                'galleryIds' => $gallery_ids,
                'items'      => $media_items,
                'i18n'       => [
                    'coverTitle'   => __('Choose cover image', 'simplified-food-fitness'),
                    'coverButton'  => __('Use as cover', 'simplified-food-fitness'),
                    'galleryTitle' => __('Add gallery images', 'simplified-food-fitness'),
                    'galleryButton'=> __('Add images', 'simplified-food-fitness'),
                    'noCover'      => __('No cover image selected.', 'simplified-food-fitness'),
                    'noGallery'    => __('No gallery images selected.', 'simplified-food-fitness'),
                    'remove'       => __('Remove image', 'simplified-food-fitness'),
                    'imageFallback'=> __('Image', 'simplified-food-fitness'),
                ],
            ]);
        }
    }
}
add_action('wp_enqueue_scripts', 'sff_enqueue_assets');

add_action('admin_enqueue_scripts', 'sff_enqueue_assets');

// ❌ Remove this duplication (you already enqueued it above):
// function sff_enqueue_global_styles() {...}
// add_action('wp_enqueue_scripts', 'sff_enqueue_global_styles');

<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function sff_get_ingredient_owner_id($ingredient_id) {
    $ingredient_id = intval($ingredient_id);
    if (!$ingredient_id) {
        return 0;
    }

    $owner = get_post_meta($ingredient_id, '_sff_owner_id', true);
    if ($owner !== '' && $owner !== null) {
        return intval($owner);
    }

    $post = get_post($ingredient_id);
    if (!$post) {
        return 0;
    }

    if (user_can($post->post_author, 'manage_options')) {
        return 0;
    }

    return intval($post->post_author);
}

function sff_assign_ingredient_owner($ingredient_id, $user_id = null, $force = false) {
    $ingredient_id = intval($ingredient_id);
    if (!$ingredient_id) {
        return;
    }

    if ($user_id === null) {
        $user_id = get_current_user_id();
        if ($user_id && user_can($user_id, 'manage_options')) {
            $user_id = 0;
        }
    }

    if (!$force) {
        $current = get_post_meta($ingredient_id, '_sff_owner_id', true);
        if ($current !== '' && $current !== null) {
            return;
        }
    }

    update_post_meta($ingredient_id, '_sff_owner_id', intval($user_id));
}

function sff_user_can_access_ingredient($ingredient_id, $user_id = 0) {
    $ingredient_id = intval($ingredient_id);
    if (!$ingredient_id) {
        return false;
    }

    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if ($user_id && user_can($user_id, 'manage_options')) {
        return true;
    }

    $owner_id = sff_get_ingredient_owner_id($ingredient_id);
    if ($owner_id === 0) {
        return true;
    }

    return $owner_id === intval($user_id);
}

function sff_is_general_ingredient($ingredient_id) {
    return sff_get_ingredient_owner_id($ingredient_id) === 0;
}

function sff_prepare_ingredient_payload($ingredient_id, $user_id = 0) {
    $ingredient_id = intval($ingredient_id);
    if (!$ingredient_id) {
        return null;
    }

    $post = get_post($ingredient_id);
    if (!$post || $post->post_type !== 'ingredient') {
        return null;
    }

    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    $macros_raw = get_post_meta($ingredient_id, '_sff_macros', true);
    $macros = array_fill_keys(SFF_MACRO_FIELDS, 0);
    if (is_array($macros_raw)) {
        foreach (SFF_MACRO_FIELDS as $field) {
            if (isset($macros_raw[$field])) {
                $macros[$field] = floatval($macros_raw[$field]);
            }
        }
    }

    $terms = wp_get_post_terms($ingredient_id, 'ingredient_category', ['number' => 1]);
    $category_id = 0;
    $category_name = '';
    if (!is_wp_error($terms) && !empty($terms)) {
        $category_id = intval($terms[0]->term_id);
        $category_name = $terms[0]->name;
    }

    $owner_id = sff_get_ingredient_owner_id($ingredient_id);
    $is_personal = ($owner_id && $owner_id === intval($user_id));
    $is_general = ($owner_id === 0);

    $owner_badge = $is_personal ? __('My Ingredient', 'simplified-food-fitness') : __('General Database', 'simplified-food-fitness');
    $owner_badge_class = $is_personal ? 'personal' : ($is_general ? 'general' : 'restricted');

    return [
        'id' => $ingredient_id,
        'title' => get_the_title($ingredient_id),
        'brand_name' => get_post_meta($ingredient_id, '_sff_brand_name', true) ?: get_the_title($ingredient_id),
        'serving_size' => get_post_meta($ingredient_id, '_sff_serving_size', true),
        'servings' => get_post_meta($ingredient_id, '_sff_servings', true),
        'macros' => $macros,
        'macro_source' => get_post_meta($ingredient_id, '_sff_macro_source', true),
        'fdc_id' => get_post_meta($ingredient_id, '_sff_fdc_id', true),
        'category_id' => $category_id,
        'category_name' => $category_name,
        'owner_id' => $owner_id,
        'owner_type' => $is_personal ? 'personal' : ($is_general ? 'general' : 'restricted'),
        'is_personal' => $is_personal,
        'is_general' => $is_general,
        'owner_badge' => $owner_badge,
        'owner_badge_class' => $owner_badge_class,
        'price' => floatval(get_post_meta($ingredient_id, '_sff_price', true)),
    ];
}

function sff_generate_grocery_list($meal_plan) {
    if (empty($meal_plan) || !is_string($meal_plan)) {
        return '<p>No grocery list available.</p>';
    }

    $ingredients = [];

    // Use regex to extract potential ingredients
    preg_match_all('/\b(\d+\s\w+\s\w+)/', $meal_plan, $matches);

    if (!empty($matches[0])) {
        $ingredients = array_unique($matches[0]);
    }

    if (empty($ingredients)) {
        return '<p>No ingredients found in the meal plan.</p>';
    }

    $output = '<ul class="sff-grocery-list">';
    foreach ($ingredients as $ingredient) {
        $output .= '<li>' . esc_html($ingredient) . '</li>';
    }
    $output .= '</ul>';

    return $output;
}

function sff_generate_meal_cards($meal_plan) {
    $meals = json_decode($meal_plan, true);
    $output = '';

    if (is_array($meals)) {
        foreach ($meals as $meal) {
            $output .= '<div class="sff-meal-card">';
            $output .= '<img src="' . esc_url($meal['image']) . '" alt="' . esc_html($meal['title']) . '">';
            $output .= '<h3>' . esc_html($meal['time']) . ' - ' . esc_html($meal['title']) . '</h3>';
            $output .= '<p>' . esc_html($meal['description']) . '</p>';
            $output .= '<div class="sff-macro-info">';
            $output .= '<span>C: ' . esc_html($meal['carbs']) . 'g</span>';
            $output .= '<span>P: ' . esc_html($meal['protein']) . 'g</span>';
            $output .= '<span>F: ' . esc_html($meal['fat']) . 'g</span>';
            $output .= '<span>Cal: ' . esc_html($meal['calories']) . '</span>';
            $output .= '</div>';
            $output .= '<div class="sff-ingredients"><h4>Ingredients</h4><ul>';
            foreach (explode(',', $meal['ingredients']) as $ingredient) {
                $output .= '<li>' . esc_html(trim($ingredient)) . '</li>';
            }
            $output .= '</ul></div>';
            $output .= '<a href="#" class="sff-view-recipe">View Recipe</a>';
            $output .= '<button class="sff-change-meal" data-meal="' . esc_attr($meal['id']) . '">Change Meal</button>';
            $output .= '</div>'; 
        }
    } else {
        $output .= '<p>No meals found. Please check with your coach.</p>';
    }

    return $output;
}

function sff_fetch_usda_macros($fdc_id, &$raw_data = null) {
    $raw_data = null;
    $api_key = defined('SFF_USDA_API_KEY') ? SFF_USDA_API_KEY : '';
    if (empty($api_key) || empty($fdc_id)) {
        return [];
    }

    $endpoint = 'https://api.nal.usda.gov/fdc/v1/food/' . intval($fdc_id);
    $url = add_query_arg(
        [
            'api_key' => $api_key,
            'format'  => 'full',
        ],
        $endpoint
    );

    $response = wp_remote_get($url, [
        'timeout' => 20,
        'headers' => [
            'Accept' => 'application/json',
        ],
    ]);

    if (is_wp_error($response)) {
        return [];
    }

    $body = wp_remote_retrieve_body($response);
    if (empty($body)) {
        return [];
    }

    $raw_data = json_decode($body, true);
    if (empty($raw_data) || !is_array($raw_data)) {
        $raw_data = null;
        return [];
    }

    $macros = array_fill_keys(SFF_MACRO_FIELDS, 0);

    // Parse "labelNutrients" block first (available for many branded foods).
    if (!empty($raw_data['labelNutrients']) && is_array($raw_data['labelNutrients'])) {
        $label_map = [
            'calories'      => 'calories',
            'carbohydrates' => 'carbs',
            'protein'       => 'protein',
            'fat'           => 'fat',
            'saturatedFat'  => 'saturated_fat',
            'transFat'      => 'trans_fat',
            'cholesterol'   => 'cholesterol',
            'sodium'        => 'sodium',
            'fiber'         => 'fiber',
            'sugars'        => 'sugars',
            'addedSugars'   => 'added_sugars',
            'vitaminD'      => 'vitamin_d',
            'calcium'       => 'calcium',
            'iron'          => 'iron',
            'potassium'     => 'potassium',
        ];

        foreach ($label_map as $label_key => $macro_key) {
            if (!isset($raw_data['labelNutrients'][$label_key]) || !is_array($raw_data['labelNutrients'][$label_key])) {
                continue;
            }

            $entry = $raw_data['labelNutrients'][$label_key];
            $value = null;

            if (isset($entry['value'])) {
                $value = $entry['value'];
            } elseif (isset($entry['amount'])) {
                $value = $entry['amount'];
            }

            if ($value !== null && $value !== '') {
                $macros[$macro_key] = floatval($value);
            }
        }
    }

    // Additional nutrients may live in the broader foodNutrients array.
    if (!empty($raw_data['foodNutrients']) && is_array($raw_data['foodNutrients'])) {
        $number_map = [
            '208' => 'calories',
            '205' => 'carbs',
            '203' => 'protein',
            '204' => 'fat',
            '606' => 'saturated_fat',
            '605' => 'trans_fat',
            '601' => 'cholesterol',
            '307' => 'sodium',
            '291' => 'fiber',
            '269' => 'sugars',
            '539' => 'added_sugars',
            '328' => 'vitamin_d',
            '301' => 'calcium',
            '303' => 'iron',
            '306' => 'potassium',
            '304' => 'magnesium',
            '320' => 'vitamin_a',
            '401' => 'vitamin_c',
            '323' => 'vitamin_e',
            '309' => 'zinc',
            '417' => 'folate',
            '405' => 'riboflavin',
            '406' => 'niacin',
            '415' => 'vitamin_b6',
            '418' => 'vitamin_b12',
            '404' => 'thiamin',
        ];

        $name_map = [
            'Energy' => 'calories',
            'Carbohydrate, by difference' => 'carbs',
            'Protein' => 'protein',
            'Total lipid (fat)' => 'fat',
            'Fatty acids, total saturated' => 'saturated_fat',
            'Fatty acids, total trans' => 'trans_fat',
            'Cholesterol' => 'cholesterol',
            'Sodium, Na' => 'sodium',
            'Fiber, total dietary' => 'fiber',
            'Sugars, total including NLEA' => 'sugars',
            'Sugars, total' => 'sugars',
            'Sugars, added' => 'added_sugars',
            'Vitamin D' => 'vitamin_d',
            'Vitamin D (D2 + D3)' => 'vitamin_d',
            'Calcium, Ca' => 'calcium',
            'Iron, Fe' => 'iron',
            'Potassium, K' => 'potassium',
            'Magnesium, Mg' => 'magnesium',
            'Vitamin A, RAE' => 'vitamin_a',
            'Vitamin C, total ascorbic acid' => 'vitamin_c',
            'Vitamin E (alpha-tocopherol)' => 'vitamin_e',
            'Zinc, Zn' => 'zinc',
            'Folate, total' => 'folate',
            'Riboflavin' => 'riboflavin',
            'Niacin' => 'niacin',
            'Vitamin B-6' => 'vitamin_b6',
            'Vitamin B-12' => 'vitamin_b12',
            'Thiamin' => 'thiamin',
        ];

        foreach ($raw_data['foodNutrients'] as $nutrient) {
            $value = null;

            if (isset($nutrient['value'])) {
                $value = floatval($nutrient['value']);
            } elseif (isset($nutrient['amount'])) {
                $value = floatval($nutrient['amount']);
            }

            if ($value === null) {
                continue;
            }

            $number = isset($nutrient['nutrientNumber']) ? (string) $nutrient['nutrientNumber'] : '';
            if ($number && isset($number_map[$number])) {
                $macros[$number_map[$number]] = $value;
                continue;
            }

            $name = $nutrient['nutrientName'] ?? '';
            if ($name && isset($name_map[$name])) {
                $macros[$name_map[$name]] = $value;
            }
        }
    }

    // If every entry is still zero, treat the response as empty.
    if (!array_filter($macros, static function ($val) {
        return floatval($val) !== 0.0;
    })) {
        return [];
    }

    return $macros;
}
function sff_render_ingredient_form($post_id = null) {
   // Get existing ingredient data if editing
    $brand_name = $serving_size = $servings = '';
    $front_image = $nutrition_label_image = '';
    $fdc_id = '';
    $sku = $affiliate_link = '';
    $price = 0;
    $ingredient_category = 0;
    $macros = [
        'calories' => 0, 'carbs' => 0, 'protein' => 0, 'fat' => 0,
        'saturated_fat' => 0, 'trans_fat' => 0, 'cholesterol' => 0,
        'sodium' => 0, 'fiber' => 0, 'sugars' => 0, 'added_sugars' => 0,
        'vitamin_d' => 0, 'calcium' => 0, 'iron' => 0, 'potassium' => 0, 'magnesium' => 0,
        'vitamin_a' => 0, 'vitamin_c' => 0, 'vitamin_e' => 0, 'zinc' => 0, 'folate' => 0,
        'riboflavin' => 0, 'niacin' => 0, 'vitamin_b6' => 0, 'vitamin_b12' => 0, 'thiamin' => 0
    ];
    $macro_source = 'manual';

    $allow_usda_search = current_user_can('manage_options') && is_admin();

    if ($post_id) {
        $brand_name = get_post_meta($post_id, '_sff_brand_name', true);
        $serving_size = get_post_meta($post_id, '_sff_serving_size', true);
        $servings = get_post_meta($post_id, '_sff_servings', true);
        $macros = get_post_meta($post_id, '_sff_macros', true) ?: $macros;
        $macro_source = get_post_meta($post_id, '_sff_macro_source', true) ?: 'manual';
        $front_image = get_post_meta($post_id, '_sff_front_image', true);
        $nutrition_label_image = get_post_meta($post_id, '_sff_nutrition_label_image', true);
        $fdc_id = get_post_meta($post_id, '_sff_fdc_id', true);
        $sku = get_post_meta($post_id, '_sff_sku', true);
        $affiliate_link = get_post_meta($post_id, '_sff_affiliate_link', true);
        $price = get_post_meta($post_id, '_sff_price', true);
        $cat_terms = wp_get_post_terms($post_id, 'ingredient_category', ['fields' => 'ids']);
        if (!empty($cat_terms)) {
            $ingredient_category = $cat_terms[0];
        }
    }

    ob_start(); ?>
    <div class="sff-ingredient-form-card" style="background:#fff; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.1); padding:20px;">
        <h2 style="font-size:20px; color:#333; margin-bottom:15px;">Add Ingredient</h2>
        <?php if (current_user_can('manage_options')) : ?>
            <div style="background:#f2f9f3; border-left:4px solid #42b14c; padding:12px; border-radius:8px; margin-bottom:20px; color:#235d3a;">
                <strong><?php esc_html_e('Admin quick tip:', 'simplified-food-fitness'); ?></strong>
                <?php esc_html_e('Anything you submit here is saved to the shared ingredient database. Use the Ingredient Library in wp-admin to review client submissions and promote your favorites.', 'simplified-food-fitness'); ?>
            </div>
        <?php endif; ?>

        <!-- Step 1: Product Name Extraction -->
        <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
            <input type="hidden" name="action" value="sff_save_ingredient">
            <?php wp_nonce_field('sff_ingredient_nonce', 'sff_nonce_field'); ?>

            <div id="sff-wizard-step-1">
                <h3 style="font-size:18px; color:#333; margin-bottom:10px;">Step 1: Find Ingredient</h3>
                <p style="font-size:14px; color:#777;">Choose a source below to begin.</p>

                <div class="sff-option">
                    <h4 style="font-size:16px; color:#333; margin-bottom:8px;">Option 1: Search Ingredient Database</h4>
                    <p style="font-size:13px; color:#5f6f64; margin-top:0;">Browse shared ingredients from your dietitian and items you have already added.</p>
                    <div style="position:relative;">
                        <input type="text" name="sff_brand_name" id="sff_product_name" value="<?php echo esc_attr($brand_name); ?>" placeholder="Start typing e.g., Banana" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; margin-bottom:10px;">
                        <?php
                        $is_admin_user = current_user_can('manage_options');
                        $default_scope = $allow_usda_search && $is_admin_user ? 'all' : 'general';
                        $scope_options = [
                            'general'  => __('General Database', 'simplified-food-fitness'),
                            'personal' => __('My Ingredients', 'simplified-food-fitness'),
                        ];

                        if ($allow_usda_search && $is_admin_user) {
                            $scope_options = ['all' => __('All Ingredients', 'simplified-food-fitness')] + $scope_options;
                        }
                        ?>
                        <input type="hidden" id="sff-allow-usda" value="<?php echo esc_attr($allow_usda_search ? '1' : '0'); ?>">
                        <select id="sff-ingredient-scope" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; margin-bottom:10px;">
                            <?php foreach ($scope_options as $value => $label) : ?>
                                <option value="<?php echo esc_attr($value); ?>" <?php selected($value, $default_scope); ?>><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" id="sff-database-search-button" style="background:#42b14c; color:white; border:none; padding:10px; border-radius:6px; cursor:pointer; font-size:14px; width:100%; margin-bottom:10px;">🔍 Search Database</button>
                        <div id="sff-ingredient-suggestions" class="sff-ingredient-suggestions" style="display:none;"></div>
                    </div>
                </div>

                <div class="sff-divider"><span>OR</span></div>

                <div class="sff-option">
                    <h4 style="font-size:16px; color:#333; margin-bottom:8px;">Option 2: Upload Product Label</h4>
                    <?php if ($front_image) : ?>
                        <img src="<?php echo esc_url($front_image); ?>" alt="Front Image" style="width:100px; height:auto; border-radius:8px; margin-bottom:10px;">
                    <?php endif; ?>

                    <input type="file" id="sff_front_image_upload" accept="image/*" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                    <button type="button" id="scan_front_image_button" style="background:#42b14c; color:white; border:none; padding:10px; border-radius:6px; cursor:pointer; font-size:14px; width:100%; margin-top:10px;">
                        1️⃣ Scan Product Name
                    </button>
                    <div id="scan_front_results" style="margin-top:10px; padding:10px; background:#f8f8f8; border-radius:8px; font-size:0.9rem; text-align:center;"></div>
                </div>

                <button type="button" id="next_step_button" style="background:#E9FAB0; color:#023441; border:none; padding:12px; border-radius:8px; cursor:pointer; font-size:16px; width:100%; margin-top:20px;">
                    Next Step →
                </button>
            </div>

            <!-- Step 2: Nutrition Details -->
            <div id="sff-wizard-step-2" style="display:none;">
                <h3 style="font-size:18px; color:#333; margin-bottom:10px;">Step 2: Add Nutrition Details</h3>
                <p style="font-size:14px; color:#777;">Scan a nutrition label, use a USDA match, or enter macros manually.</p>

                <?php if ($nutrition_label_image) : ?>
                    <img src="<?php echo esc_url($nutrition_label_image); ?>" alt="Nutrition Label Image" style="width:100px; height:auto; border-radius:8px; margin-bottom:10px;">
                <?php endif; ?>

                <div id="sff-macro-summary" class="sff-macro-summary-card" style="display:none;">
                    <div class="sff-macro-summary-title">Nutrition Summary</div>
                    <div class="sff-macro-summary-grid"></div>
                </div>

                <input type="hidden" name="sff_fdc_id" id="sff_fdc_id" value="<?php echo esc_attr($fdc_id); ?>">
                <input type="hidden" name="sff_source_ingredient" id="sff_source_ingredient" value="">
                <input type="hidden" name="sff_selected_owner" id="sff_selected_owner" value="">

                <div id="sff-ingredient-selection-note" style="display:none; margin-bottom:15px; padding:10px; background:#f2f9f3; border-radius:8px; font-size:0.95rem; color:#235d3a;"></div>

                <?php
                $show_category_dropdown = apply_filters('sff_show_category_dropdown', empty($fdc_id));
                if ($show_category_dropdown) :
                    ?>
                    <label style="font-size:14px; color:#777;">Category:</label>
                    <?php
                    $dropdown = wp_dropdown_categories([
                        'taxonomy' => 'ingredient_category',
                        'hide_empty' => false,
                        'name'      => 'sff_ingredient_category',
                        'orderby'   => 'name',
                        'selected'  => $ingredient_category,
                        'show_option_none' => __('Select category'),
                        'echo'      => false,
                    ]);
                    echo str_replace('<select', '<select style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; margin-bottom:10px;"', $dropdown);
                endif;
                ?>

                <div id="sff_scan_fields">
                    <input type="file" id="sff_nutrition_label_upload" accept="image/*" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                    <button type="button" id="scan_nutrition_label_button" style="background:#42b14c; color:white; border:none; padding:10px; border-radius:6px; cursor:pointer; font-size:14px; width:100%; margin-top:10px;">
                        2️⃣ Scan Nutrition Label 🥗
                    </button>
                    <div id="scan_results" style="margin-top:10px; padding:10px; background:#f8f8f8; border-radius:8px; font-size:0.9rem; text-align:center;"></div>
                </div>

                <p style="font-size:14px; color:#777;">Macros source: <span id="macro_source_text"><?php echo ucfirst($macro_source); ?></span></p>
                <input type="hidden" name="sff_macro_source" id="sff_macro_source" value="<?php echo esc_attr($macro_source); ?>">

                <fieldset style="border:none; padding:0; margin-top:15px;">
                    <legend style="font-size:16px; font-weight:bold; color:#333;">Macros per Serving</legend>

                    <!-- Add Serving Size and Servings Per Container Fields -->
                    <label style="font-size:14px; color:#777;">Serving Size:</label>
                    <input type="text" name="sff_serving_size" id="sff_serving_size" value="<?php echo esc_attr($serving_size); ?>" placeholder="e.g., 1 cup (240ml)" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; margin-bottom:10px;">

                    <label style="font-size:14px; color:#777;">Servings Per Container:</label>
                    <input type="number" name="sff_servings" id="sff_servings" value="<?php echo esc_attr($servings); ?>" placeholder="e.g., 4" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; margin-bottom:10px;">

                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(120px, 1fr)); gap:15px;">
                        <?php foreach ($macros as $key => $value) : ?>
                            <div>
                                <label style="font-size:14px; color:#777;"><?php echo ucwords(str_replace('_', ' ', $key)); ?>:</label>
                                <input type="number" name="sff_macros[<?php echo $key; ?>]" value="<?php echo esc_attr($value); ?>" step="any" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </fieldset>

                <div id="usda-full-response" class="usda-response-box" style="display:none;">
                    <p class="usda-response-message">USDA API response will appear here after you select a food.</p>
                </div>

                <label style="font-size:14px; color:#777;">SKU:</label>
                <input type="text" name="sff_sku" value="<?php echo esc_attr($sku); ?>" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; margin-bottom:10px;">

                <label style="font-size:14px; color:#777;">Affiliate Link:</label>
                <input type="url" name="sff_affiliate_link" value="<?php echo esc_attr($affiliate_link); ?>" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; margin-bottom:10px;">

                <label style="font-size:14px; color:#777;">Price:</label>
                <input type="number" step="0.01" name="sff_price" value="<?php echo esc_attr($price); ?>" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; margin-bottom:10px;">

                <input type="submit" name="sff_submit_ingredient" value="Save Ingredient" style="background:#E9FAB0; color:#023441; border:none; padding:12px; border-radius:8px; cursor:pointer; font-size:16px; width:100%; margin-top:20px;">
            </div>
        </form>

        <!-- Step 3: Success Confirmation (NEW) -->
        <div id="sff-wizard-step-3" style="display:none; text-align:center; padding:20px;">
            <h2>✅ Ingredient Added!</h2>
            <p>Your ingredient has been successfully saved.</p>
            <button id="add_new_ingredient_button" style="background:#023441; color:#E9FAB0; padding:12px 20px; border:none; border-radius:8px; font-weight:bold; cursor:pointer;">
                ➕ Add a New Ingredient
            </button>
        </div>
    </div>

    <?php
    return ob_get_clean();
}

function sff_render_ingredient_meta_box($post) {
    echo sff_render_ingredient_form($post->ID);
}

function sff_save_ingredient_details($post_id) {
     if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    sff_assign_ingredient_owner($post_id);

    if (isset($_POST['sff_quantity'])) {
        update_post_meta($post_id, '_sff_quantity', sanitize_text_field($_POST['sff_quantity']));
    }

    if (isset($_POST['sff_unit_type'])) {
        update_post_meta($post_id, '_sff_unit_type', sanitize_text_field($_POST['sff_unit_type']));
    }

    if (isset($_POST['sff_brand_name'])) {
        update_post_meta($post_id, '_sff_brand_name', sanitize_text_field($_POST['sff_brand_name']));
    }

    if (isset($_POST['sff_ingredient_category'])) {
        $cat = intval($_POST['sff_ingredient_category']);
        if ($cat) {
            wp_set_object_terms($post_id, $cat, 'ingredient_category');
        } else {
            wp_set_object_terms($post_id, [], 'ingredient_category');
        }
    }

    $fdc_id = '';
    if (isset($_POST['sff_fdc_id'])) {
        $fdc_id = sanitize_text_field($_POST['sff_fdc_id']);
        update_post_meta($post_id, '_sff_fdc_id', $fdc_id);
    }

    if (isset($_POST['sff_measurements'])) {
        update_post_meta($post_id, '_sff_measurements', sanitize_textarea_field($_POST['sff_measurements']));
    }

    if (isset($_POST['sff_macros'])) {
        $macros = array_map('sanitize_text_field', $_POST['sff_macros']);
        $macro_source = isset($_POST['sff_macro_source']) ? sanitize_text_field($_POST['sff_macro_source']) : 'manual';
        if (array_sum(array_map('floatval', $macros)) === 0 && $macro_source === 'usda' && !empty($fdc_id)) {
            $api_macros = sff_fetch_usda_macros($fdc_id);
            foreach ($api_macros as $key => $value) {
                $macros[$key] = $value;
            }
        }
        update_post_meta($post_id, '_sff_macros', $macros);
        update_post_meta($post_id, '_sff_macro_source', $macro_source);

        global $wpdb;
        $table = $wpdb->prefix . 'sff_ingredient_nutrition';
        $data = array_merge(['ingredient_id' => $post_id], array_fill_keys(SFF_MACRO_FIELDS, 0));
        foreach (SFF_MACRO_FIELDS as $field) {
            $data[$field] = isset($macros[$field]) ? floatval($macros[$field]) : 0;
        }
        $data['cost'] = isset($_POST['sff_cost']) ? floatval($_POST['sff_cost']) : 0;

        $formats = array_merge(['%d'], array_fill(0, count(SFF_MACRO_FIELDS), '%f'), ['%f']);
        $exists = $wpdb->get_var($wpdb->prepare("SELECT ingredient_id FROM $table WHERE ingredient_id = %d", $post_id));
        if ($exists) {
            $wpdb->update($table, $data, ['ingredient_id' => $post_id], $formats, ['%d']);
        } else {
            $wpdb->insert($table, $data, $formats);
        }
    }

    if (isset($_POST['sff_source_ingredient'])) {
        update_post_meta($post_id, '_sff_source_ingredient', intval($_POST['sff_source_ingredient']));
    }

    if (isset($_POST['sff_selected_owner'])) {
        update_post_meta($post_id, '_sff_selected_owner', sanitize_text_field($_POST['sff_selected_owner']));
    }
}
add_action('save_post', 'sff_save_ingredient_details');

function sff_custom_login_form() {
   $args = array(
        'echo'           => false,
        'redirect'       => home_url('/dashboard/'),
        'label_username' => __('Username or Email'),
        'label_password' => __('Password'),
        'label_remember' => __('Remember Me'),
        'label_log_in'   => __('Log In'),
    );
    
    ob_start(); ?>
    
    <div class="sff-login-container" style="max-width: 400px; margin: 2rem auto; padding: 2rem; background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
        <div class="sff-login-header" style="text-align: center; margin-bottom: 1.5rem;">
            <img src="https://simplifiedfoodandfitness.com/wp-content/uploads/2024/10/3.png" 
                 alt="Logo" 
                 style="height: 60px; margin-bottom: 1rem;">
            <h2 style="color: #023441; margin: 0 0 0.5rem;">Welcome Back! 🌱</h2>
            <p style="color: #6c757d;">Log in to access your meal plans and macros</p>
        </div>
        
        <?php echo wp_login_form($args); ?>
        
        <div class="sff-login-links" style="margin-top: 1.5rem; text-align: center;">
            <a href="<?php echo wp_lostpassword_url(); ?>" 
               style="color: #023441; text-decoration: none; font-size: 0.9rem;">
                Forgot Password?
            </a>
        </div>
    </div>

   
    
    <?php
    return ob_get_clean();
}

function sff_create_recipe_from_modal($name, $ingredient_ids) {
    $recipe_id = wp_insert_post([
        'post_type'   => 'recipe',
        'post_title'  => sanitize_text_field($name),
        'post_status' => 'publish',
    ]);

    if (is_wp_error($recipe_id)) {
        return $recipe_id;
    }

    $ingredient_ids = array_map('intval', (array) $ingredient_ids);
    update_post_meta($recipe_id, '_sff_recipe_ingredients', $ingredient_ids);

    $ingredient_servings = [];
    foreach ($ingredient_ids as $id) {
        if ($id) {
            $ingredient_servings[$id] = 1.0;
        }
    }

    if (!empty($ingredient_servings)) {
        update_post_meta($recipe_id, '_sff_recipe_ingredient_servings', $ingredient_servings);
    }

    $total_macros = sff_get_recipe_macros_from_ids($ingredient_ids, $ingredient_servings);
    update_post_meta($recipe_id, '_sff_recipe_servings', 1);

    if (!empty($ingredient_ids)) {
        update_post_meta($recipe_id, '_sff_recipe_macros', $total_macros);
        update_post_meta($recipe_id, '_sff_recipe_macros_total', $total_macros);
        if (array_key_exists('cost', $total_macros)) {
            update_post_meta($recipe_id, '_sff_recipe_cost', $total_macros['cost']);
        }
    } else {
        delete_post_meta($recipe_id, '_sff_recipe_macros');
        delete_post_meta($recipe_id, '_sff_recipe_macros_total');
        delete_post_meta($recipe_id, '_sff_recipe_cost');
    }

    return $recipe_id;
}

function sff_get_recipe_macros_from_ids($ingredient_ids, $serving_map = []) {
    $fields = array_merge(SFF_MACRO_FIELDS, ['cost']);
    $totals = array_fill_keys($fields, 0.0);

    if (!is_array($ingredient_ids) || empty($ingredient_ids)) {
        return $totals;
    }

    $normalized = [];
    foreach ((array) $ingredient_ids as $id) {
        $id = intval($id);
        if (!$id) {
            continue;
        }

        $normalized[$id] = isset($serving_map[$id]) && floatval($serving_map[$id]) > 0
            ? floatval($serving_map[$id])
            : 1.0;
    }

    if (empty($normalized)) {
        return $totals;
    }

    global $wpdb;
    $table        = $wpdb->prefix . 'sff_ingredient_nutrition';
    $ingredient_ids = array_keys($normalized);
    $placeholders = implode(',', array_fill(0, count($ingredient_ids), '%d'));
    $select       = 'ingredient_id, ' . implode(', ', $fields);
    $query        = $wpdb->prepare("SELECT $select FROM $table WHERE ingredient_id IN ($placeholders)", $ingredient_ids);
    $results      = $wpdb->get_results($query, ARRAY_A);

    foreach ($results as $row) {
        $id = intval($row['ingredient_id']);
        if (!isset($normalized[$id])) {
            continue;
        }

        $multiplier = max(0, floatval($normalized[$id]));
        foreach ($fields as $field) {
            $totals[$field] += floatval($row[$field]) * $multiplier;
        }
    }

    return $totals;
}

function sff_get_recipe_macros($recipe_id, $per_serving = false) {
    $ingredient_ids   = get_post_meta($recipe_id, '_sff_recipe_ingredients', true);
    $ingredient_map   = get_post_meta($recipe_id, '_sff_recipe_ingredient_servings', true);
    if (!is_array($ingredient_map)) {
        $ingredient_map = [];
    }

    $base_totals = sff_get_recipe_macros_from_ids($ingredient_ids, $ingredient_map);
    $servings    = max(1, (int) get_post_meta($recipe_id, '_sff_recipe_servings', true));

    if ($per_serving) {
        $per_serving_totals = [];
        foreach ($base_totals as $key => $value) {
            $per_serving_totals[$key] = $value / $servings;
        }

        return $per_serving_totals;
    }

return $base_totals;
}

function sff_get_user_assigned_recipe_ids($user_id) {
    $user_id = intval($user_id);
    if (!$user_id) {
        return [];
    }

    $raw = get_user_meta($user_id, 'sff_assigned_recipes', true);
    if (!is_array($raw)) {
        $raw = [];
    }

    $ids = [];
    foreach ($raw as $id) {
        $id = intval($id);
        if ($id) {
            $ids[$id] = $id;
        }
    }

    if (!empty($ids)) {
        return array_values($ids);
    }

    $fallback_recipes = get_posts([
        'post_type'      => 'recipe',
        'post_status'    => 'any',
        'fields'         => 'ids',
        'nopaging'       => true,
        'no_found_rows'  => true,
        'meta_query'     => [
            [
                'key'     => '_sff_assigned_users',
                'value'   => sprintf('i:%d;', $user_id),
                'compare' => 'LIKE',
            ],
        ],
    ]);

    foreach ($fallback_recipes as $recipe_id) {
        $recipe_id = intval($recipe_id);
        if (!$recipe_id) {
            continue;
        }

        $assigned_users = get_post_meta($recipe_id, '_sff_assigned_users', true);
        if (!is_array($assigned_users)) {
            continue;
        }

        if (in_array($user_id, array_map('intval', $assigned_users), true)) {
            $ids[$recipe_id] = $recipe_id;
        }
    }

    if (!empty($ids)) {
        sff_save_user_assigned_recipe_ids($user_id, $ids);
    }

    return array_values($ids);
}

function sff_save_user_assigned_recipe_ids($user_id, $ids) {
    $user_id = intval($user_id);
    if (!$user_id) {
        return;
    }

    $normalized = [];
    foreach ((array) $ids as $id) {
        $id = intval($id);
        if ($id) {
            $normalized[$id] = $id;
        }
    }

    update_user_meta($user_id, 'sff_assigned_recipes', array_values($normalized));
}

function sff_get_recipe_assigned_users($recipe_id) {
    $recipe_id = intval($recipe_id);
    if (!$recipe_id) {
        return [];
    }

    $raw = get_post_meta($recipe_id, '_sff_assigned_users', true);
    if (!is_array($raw)) {
        $raw = [];
    }

    $ids = [];
    foreach ($raw as $id) {
        $id = intval($id);
        if ($id) {
            $ids[$id] = $id;
        }
    }

    return array_values($ids);
}

function sff_save_recipe_assigned_users($recipe_id, $user_ids) {
    $recipe_id = intval($recipe_id);
    if (!$recipe_id) {
        return;
    }

    $normalized = [];
    foreach ((array) $user_ids as $user_id) {
        $user_id = intval($user_id);
        if ($user_id) {
            $normalized[$user_id] = $user_id;
        }
    }

    update_post_meta($recipe_id, '_sff_assigned_users', array_values($normalized));
}

function sff_add_recipe_to_user_bank($recipe_id, $user_id) {
    $recipe_id = intval($recipe_id);
    $user_id   = intval($user_id);

    if (!$recipe_id || !$user_id) {
        return false;
    }

    $assigned_users = sff_get_recipe_assigned_users($recipe_id);
    if (!in_array($user_id, $assigned_users, true)) {
        $assigned_users[] = $user_id;
        sff_save_recipe_assigned_users($recipe_id, $assigned_users);
    }

    $user_recipes = sff_get_user_assigned_recipe_ids($user_id);
    if (!in_array($recipe_id, $user_recipes, true)) {
        $user_recipes[] = $recipe_id;
        sff_save_user_assigned_recipe_ids($user_id, $user_recipes);
    }

    return true;
}

function sff_remove_recipe_from_user_bank($recipe_id, $user_id) {
    $recipe_id = intval($recipe_id);
    $user_id   = intval($user_id);

    if (!$recipe_id || !$user_id) {
        return false;
    }

    $assigned_users = sff_get_recipe_assigned_users($recipe_id);
    if (in_array($user_id, $assigned_users, true)) {
        $assigned_users = array_values(array_diff($assigned_users, [$user_id]));
        sff_save_recipe_assigned_users($recipe_id, $assigned_users);
    }

    $user_recipes = sff_get_user_assigned_recipe_ids($user_id);
    if (in_array($recipe_id, $user_recipes, true)) {
        $user_recipes = array_values(array_diff($user_recipes, [$recipe_id]));
        sff_save_user_assigned_recipe_ids($user_id, $user_recipes);
    }

    return true;
}

function sff_get_user_recipe_customizations($user_id) {
    $user_id = intval($user_id);
    if (!$user_id) {
        return [];
    }

    $data = get_user_meta($user_id, 'sff_recipe_customizations', true);
    return is_array($data) ? $data : [];
}

function sff_get_user_recipe_customization($user_id, $recipe_id) {
    $user_id   = intval($user_id);
    $recipe_id = intval($recipe_id);
    if (!$user_id || !$recipe_id) {
        return [];
    }

    $customizations = sff_get_user_recipe_customizations($user_id);
    if (!isset($customizations[$recipe_id]) || !is_array($customizations[$recipe_id])) {
        return [];
    }

    return $customizations[$recipe_id];
}

function sff_save_user_recipe_customizations($user_id, $customizations) {
    $user_id = intval($user_id);
    if (!$user_id) {
        return;
    }

    $sanitized = [];
    foreach ((array) $customizations as $recipe_id => $config) {
        $recipe_id = intval($recipe_id);
        if (!$recipe_id || !is_array($config)) {
            continue;
        }

        $entry = [];
        if (isset($config['ingredients']) && is_array($config['ingredients'])) {
            foreach ($config['ingredients'] as $original_id => $replacement_id) {
                $original_id    = intval($original_id);
                $replacement_id = intval($replacement_id);
                if ($original_id && $replacement_id) {
                    $entry['ingredients'][$original_id] = $replacement_id;
                }
            }
        }

        if (!empty($entry)) {
            $sanitized[$recipe_id] = $entry;
        }
    }

    update_user_meta($user_id, 'sff_recipe_customizations', $sanitized);
}

function sff_clear_user_recipe_customization($user_id, $recipe_id) {
    $user_id   = intval($user_id);
    $recipe_id = intval($recipe_id);
    if (!$user_id || !$recipe_id) {
        return;
    }

    $customizations = sff_get_user_recipe_customizations($user_id);
    if (isset($customizations[$recipe_id])) {
        unset($customizations[$recipe_id]);
        sff_save_user_recipe_customizations($user_id, $customizations);
    }
}

function sff_get_user_personal_ingredients($user_id) {
    $user_id = intval($user_id);
    if (!$user_id) {
        return [];
    }

    $posts = get_posts([
        'post_type'      => 'ingredient',
        'numberposts'    => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'meta_query'     => [
            [
                'key'     => '_sff_owner_id',
                'value'   => $user_id,
                'compare' => '=',
                'type'    => 'NUMERIC',
            ],
        ],
        'suppress_filters' => false,
    ]);

    if (!$posts) {
        return [];
    }

    $items = [];
    foreach ($posts as $post) {
        $items[] = [
            'id'   => intval($post->ID),
            'name' => $post->post_title,
        ];
    }

    return $items;
}

function sff_get_general_ingredients() {
    $posts = get_posts([
        'post_type'      => 'ingredient',
        'numberposts'    => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'meta_query'     => [
            [
                'key'     => '_sff_owner_id',
                'value'   => 0,
                'compare' => '=',
                'type'    => 'NUMERIC',
            ],
        ],
        'suppress_filters' => false,
    ]);

    if (!$posts) {
        return [];
    }

    $items = [];
    foreach ($posts as $post) {
        $items[] = [
            'id'   => intval($post->ID),
            'name' => $post->post_title,
        ];
    }

    return $items;
}

function sff_get_client_preferences_for_user($user_id) {
    $user_id = intval($user_id);
    if (!$user_id) {
        return [
            'liked'    => [],
            'disliked' => [],
        ];
    }

    $preferences = [
        'liked'    => [],
        'disliked' => [],
    ];

    if (function_exists('sff_get_client_preference_items')) {
        $linked_clients = get_posts([
            'post_type'      => 'clients',
            'numberposts'    => 1,
            'fields'         => 'ids',
            'meta_key'       => 'linked_user_id',
            'meta_value'     => $user_id,
            'suppress_filters' => false,
        ]);

        if (!empty($linked_clients)) {
            $preferences = sff_get_client_preference_items(intval($linked_clients[0]));
        }
    }

    return $preferences;
}

function sff_extract_recipe_ids_from_plan($plan) {
    if (is_numeric($plan)) {
        $plan = get_post(intval($plan));
    }

    if (!$plan || $plan->post_type !== 'meal_plan') {
        return [];
    }

    $raw_schedule = get_post_meta($plan->ID, '_sff_meal_data', true);

    if (is_string($raw_schedule) && $raw_schedule !== '') {
        $decoded = json_decode($raw_schedule, true);
        $raw_schedule = is_array($decoded) ? $decoded : [];
    } elseif (!is_array($raw_schedule)) {
        $raw_schedule = [];
    }

    $recipe_ids = [];

    foreach ($raw_schedule as $entries) {
        if (!is_array($entries)) {
            continue;
        }

        foreach ($entries as $recipe_id) {
            $recipe_id = intval($recipe_id);
            if ($recipe_id) {
                $recipe_ids[$recipe_id] = $recipe_id;
            }
        }
    }

    return array_values($recipe_ids);
}

function sff_prune_user_recipe_customizations($user_id, $allowed_recipe_ids) {
    $user_id = intval($user_id);
    if (!$user_id) {
        return;
    }

    $allowed_map = [];
    foreach ((array) $allowed_recipe_ids as $recipe_id) {
        $recipe_id = intval($recipe_id);
        if ($recipe_id) {
            $allowed_map[$recipe_id] = true;
        }
    }

    $customizations = sff_get_user_recipe_customizations($user_id);
    if (empty($customizations)) {
        return;
    }

    $changed = false;

    foreach ($customizations as $recipe_id => $config) {
        $recipe_id = intval($recipe_id);
        if (!$recipe_id || !isset($allowed_map[$recipe_id])) {
            unset($customizations[$recipe_id]);
            $changed = true;
        }
    }

    if ($changed) {
        sff_save_user_recipe_customizations($user_id, $customizations);
    }
}

function sff_sync_user_recipes_from_assigned_plans($user_id) {
    $user_id = intval($user_id);
    if (!$user_id) {
        return;
    }

    $plans = get_posts([
        'post_type'      => 'meal_plan',
        'post_status'    => ['publish', 'private'],
        'numberposts'    => -1,
        'fields'         => 'ids',
        'meta_query'     => [
            [
                'key'     => '_sff_assigned_users',
                'value'   => '"' . $user_id . '"',
                'compare' => 'LIKE',
            ],
        ],
        'suppress_filters' => false,
    ]);

    $recipe_ids = [];

    foreach ($plans as $plan_id) {
        foreach (sff_extract_recipe_ids_from_plan($plan_id) as $recipe_id) {
            if ($recipe_id) {
                $recipe_ids[$recipe_id] = $recipe_id;
            }
        }
    }

    $recipe_ids = array_values($recipe_ids);

    $current = sff_get_user_assigned_recipe_ids($user_id);
    $to_add = array_diff($recipe_ids, $current);
    $to_remove = array_diff($current, $recipe_ids);

    foreach ($to_add as $recipe_id) {
        $assigned_users = sff_get_recipe_assigned_users($recipe_id);
        if (!in_array($user_id, $assigned_users, true)) {
            $assigned_users[] = $user_id;
            sff_save_recipe_assigned_users($recipe_id, $assigned_users);
        }
    }

    foreach ($to_remove as $recipe_id) {
        $assigned_users = sff_get_recipe_assigned_users($recipe_id);
        if (in_array($user_id, $assigned_users, true)) {
            $assigned_users = array_values(array_diff($assigned_users, [$user_id]));
            sff_save_recipe_assigned_users($recipe_id, $assigned_users);
        }
        sff_clear_user_recipe_customization($user_id, $recipe_id);
    }

    sff_save_user_assigned_recipe_ids($user_id, $recipe_ids);
    sff_prune_user_recipe_customizations($user_id, $recipe_ids);
}

function sff_get_page_allowed_users($page_id) {
    $page_id = intval($page_id);
    if (!$page_id) {
        return [];
    }

    $raw = get_post_meta($page_id, '_sff_allowed_users', true);
    if (!is_array($raw)) {
        return [];
    }

    $ids = [];
    foreach ($raw as $user_id) {
        $user_id = intval($user_id);
        if ($user_id) {
            $ids[$user_id] = $user_id;
        }
    }

    return array_values($ids);
}

function sff_save_page_allowed_users($page_id, $user_ids) {
    $page_id = intval($page_id);
    if (!$page_id) {
        return;
    }

    $normalized = [];
    foreach ((array) $user_ids as $user_id) {
        $user_id = intval($user_id);
        if ($user_id) {
            $normalized[$user_id] = $user_id;
        }
    }

    if (!empty($normalized)) {
        update_post_meta($page_id, '_sff_allowed_users', array_values($normalized));
    } else {
        delete_post_meta($page_id, '_sff_allowed_users');
    }
}

function sff_get_user_allowed_pages($user_id) {
    $user_id = intval($user_id);
    if (!$user_id) {
        return [];
    }

    $raw = get_user_meta($user_id, 'sff_allowed_pages', true);
    if (!is_array($raw)) {
        return [];
    }

    $ids = [];
    foreach ($raw as $page_id) {
        $page_id = intval($page_id);
        if ($page_id) {
            $ids[$page_id] = $page_id;
        }
    }

    return array_values($ids);
}

function sff_sync_user_page_access($user_id, $page_ids) {
    $user_id = intval($user_id);
    if (!$user_id) {
        return;
    }

    $page_ids = array_map('intval', (array) $page_ids);
    $page_ids = array_values(array_filter(array_unique($page_ids)));

    $current = sff_get_user_allowed_pages($user_id);
    $to_add = array_diff($page_ids, $current);
    $to_remove = array_diff($current, $page_ids);

    foreach ($to_add as $page_id) {
        $allowed_users = sff_get_page_allowed_users($page_id);
        if (!in_array($user_id, $allowed_users, true)) {
            $allowed_users[] = $user_id;
            sff_save_page_allowed_users($page_id, $allowed_users);
        }
    }

    foreach ($to_remove as $page_id) {
        $allowed_users = sff_get_page_allowed_users($page_id);
        if (in_array($user_id, $allowed_users, true)) {
            $allowed_users = array_values(array_diff($allowed_users, [$user_id]));
            sff_save_page_allowed_users($page_id, $allowed_users);
        }
    }

    if (!empty($page_ids)) {
        update_user_meta($user_id, 'sff_allowed_pages', $page_ids);
    } else {
        delete_user_meta($user_id, 'sff_allowed_pages');
    }
}

function sff_determine_preference_state_for_name($name, $preferences) {
    $name = is_string($name) ? trim($name) : '';
    if ($name === '' || !is_array($preferences)) {
        return '';
    }

    if (function_exists('sff_title_matches_preference_items')) {
        if (!empty($preferences['disliked']) && sff_title_matches_preference_items($name, (array) $preferences['disliked'])) {
            return 'disliked';
        }

        if (!empty($preferences['liked']) && sff_title_matches_preference_items($name, (array) $preferences['liked'])) {
            return 'liked';
        }
    } else {
        $normalized = strtolower(preg_replace('/\s+/', ' ', $name));
        if (!empty($preferences['disliked'])) {
            foreach ((array) $preferences['disliked'] as $item) {
                $item = strtolower(trim($item));
                if ($item !== '' && strpos($normalized, $item) !== false) {
                    return 'disliked';
                }
            }
        }
        if (!empty($preferences['liked'])) {
            foreach ((array) $preferences['liked'] as $item) {
                $item = strtolower(trim($item));
                if ($item !== '' && strpos($normalized, $item) !== false) {
                    return 'liked';
                }
            }
        }
    }

    return '';
}

function sff_render_preview_ingredient_list($ingredient_rows, $preferences) {
    if (empty($ingredient_rows) || !is_array($ingredient_rows)) {
        return '<p class="sff-preview-ingredients__empty">' . esc_html__('No ingredients have been added yet.', 'simplified-food-fitness') . '</p>';
    }

    ob_start();
    ?>
    <ul class="sff-preview-ingredients">
        <?php foreach ($ingredient_rows as $row) :
            $display_name   = isset($row['display_name']) && $row['display_name'] !== '' ? $row['display_name'] : ($row['original_name'] ?? '');
            $original_name  = $row['original_name'] ?? $display_name;
            $preference     = sff_determine_preference_state_for_name($display_name, $preferences);
            if ($preference === '' && $display_name !== $original_name) {
                $preference = sff_determine_preference_state_for_name($original_name, $preferences);
            }
            $classes = ['sff-preview-ingredient'];
            if (!empty($row['is_custom'])) {
                $classes[] = 'is-swapped';
            }
            if ($preference) {
                $classes[] = 'sff-preview-ingredient--' . $preference;
            }
            ?>
            <li class="<?php echo esc_attr(implode(' ', $classes)); ?>">
                <div class="sff-preview-ingredient__main">
                    <span class="sff-preview-ingredient__name"><?php echo esc_html($display_name ?: $original_name); ?></span>
                    <?php if ($preference === 'liked') : ?>
                        <span class="sff-preview-ingredient__badge sff-preview-ingredient__badge--liked"><?php esc_html_e('Liked', 'simplified-food-fitness'); ?></span>
                    <?php elseif ($preference === 'disliked') : ?>
                        <span class="sff-preview-ingredient__badge sff-preview-ingredient__badge--disliked"><?php esc_html_e('Disliked', 'simplified-food-fitness'); ?></span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($row['is_custom'])) : ?>
                    <div class="sff-preview-ingredient__note">
                        <?php
                        printf(
                            esc_html__('Original: %s', 'simplified-food-fitness'),
                            esc_html($original_name)
                        );
                        ?>
                    </div>
                <?php endif; ?>
                <?php
                $details = [];
                if (isset($row['servings']) && $row['servings'] !== '') {
                    $servings_value = floatval($row['servings']);
                    $details[] = sprintf(
                        esc_html__('%s servings', 'simplified-food-fitness'),
                        esc_html(number_format_i18n($servings_value, $servings_value === floor($servings_value) ? 0 : 2))
                    );
                }
                if (!empty($row['serving_size'])) {
                    $details[] = $row['serving_size'];
                }
                if (!empty($details)) :
                    ?>
                    <div class="sff-preview-ingredient__details"><?php echo esc_html(implode(' • ', $details)); ?></div>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php
    return ob_get_clean();
}

function sff_get_recipe_ingredient_details_with_overrides($recipe_id, $overrides = []) {
    $recipe_id = intval($recipe_id);
    if (!$recipe_id) {
        return [];
    }

    $ingredient_ids = get_post_meta($recipe_id, '_sff_recipe_ingredients', true);
    if (!is_array($ingredient_ids)) {
        return [];
    }

    $servings_map = get_post_meta($recipe_id, '_sff_recipe_ingredient_servings', true);
    if (!is_array($servings_map)) {
        $servings_map = [];
    }

    $details = [];

    foreach ($ingredient_ids as $id) {
        $id = intval($id);
        if (!$id) {
            continue;
        }

        $replacement_id = 0;
        if (isset($overrides['ingredients'][$id])) {
            $replacement_id = intval($overrides['ingredients'][$id]);
        }

        $display_id = $replacement_id ?: $id;

        $serving_amount = isset($servings_map[$id]) ? floatval($servings_map[$id]) : 1.0;
        $serving_amount = $serving_amount > 0 ? $serving_amount : 1.0;

        $original_name  = get_the_title($id);
        $display_name   = get_the_title($display_id);
        $replacement_name = $replacement_id ? get_the_title($replacement_id) : '';

        if ($original_name === '') {
            $original_name = sprintf(__('Ingredient #%d', 'simplified-food-fitness'), $id);
        }
        if ($display_name === '') {
            $display_name = $original_name;
        }
        if ($replacement_id && $replacement_name === '') {
            $replacement_name = sprintf(__('Ingredient #%d', 'simplified-food-fitness'), $replacement_id);
        }

        $details[] = [
            'original_id'      => $id,
            'original_name'    => $original_name,
            'display_id'       => $display_id,
            'display_name'     => $display_name,
            'serving_size'     => get_post_meta($display_id, '_sff_serving_size', true),
            'servings'         => $serving_amount,
            'is_custom'        => ($display_id !== $id),
            'replacement_id'   => $replacement_id,
            'replacement_name' => $replacement_name,
        ];
    }

    return $details;
}

function sff_get_recipe_macros_with_overrides($recipe_id, $overrides = [], $per_serving = false) {
    $recipe_id = intval($recipe_id);
    if (!$recipe_id) {
        return array_fill_keys(array_merge(SFF_MACRO_FIELDS, ['cost']), 0.0);
    }

    $ingredient_ids = get_post_meta($recipe_id, '_sff_recipe_ingredients', true);
    if (!is_array($ingredient_ids)) {
        $ingredient_ids = [];
    }

    $servings_map = get_post_meta($recipe_id, '_sff_recipe_ingredient_servings', true);
    if (!is_array($servings_map)) {
        $servings_map = [];
    }

    $custom_ids  = [];
    $custom_map  = [];

    foreach ($ingredient_ids as $id) {
        $id = intval($id);
        if (!$id) {
            continue;
        }

        $replacement = isset($overrides['ingredients'][$id]) ? intval($overrides['ingredients'][$id]) : 0;
        $display_id  = $replacement ?: $id;

        $serving_amount = isset($servings_map[$id]) ? floatval($servings_map[$id]) : 1.0;
        $serving_amount = $serving_amount > 0 ? $serving_amount : 1.0;

        $custom_ids[] = $display_id;
        if (isset($custom_map[$display_id])) {
            $custom_map[$display_id] += $serving_amount;
        } else {
            $custom_map[$display_id] = $serving_amount;
        }
    }

    $totals = sff_get_recipe_macros_from_ids($custom_ids, $custom_map);

    if ($per_serving) {
        $servings = max(1, (int) get_post_meta($recipe_id, '_sff_recipe_servings', true));
        foreach ($totals as $key => $value) {
            $totals[$key] = $servings > 0 ? $value / $servings : $value;
        }
    }

    return $totals;
}

function sff_get_recipe_rating_data($recipe_id) {
    $recipe_id = intval($recipe_id);
    if (!$recipe_id) {
        return sff_prepare_recipe_rating_response($recipe_id, 0, 0, 0, 0, []);
    }

    $comments = get_comments([
        'post_id' => $recipe_id,
        'status'  => 'approve',
        'type'    => 'sff_recipe_rating',
        'number'  => 0,
    ]);

    if (!$comments) {
        return sff_prepare_recipe_rating_response($recipe_id, 0, 0, 0, 0, []);
    }

    $total      = 0;
    $count      = 0;
    $thumbs_up  = 0;
    $thumbs_down = 0;
    $prepared   = [];

    foreach ($comments as $comment) {
        $rating = intval(get_comment_meta($comment->comment_ID, '_sff_rating', true));
        if ($rating < 1) {
            continue;
        }

        $preference = sanitize_key(get_comment_meta($comment->comment_ID, '_sff_preference', true));
        if ($preference === 'up') {
            $thumbs_up++;
        } elseif ($preference === 'down') {
            $thumbs_down++;
        }

        $total += $rating;
        $count++;

        $prepared[] = [
            'id'         => $comment->comment_ID,
            'rating'     => $rating,
            'preference' => in_array($preference, ['up', 'down'], true) ? $preference : '',
            'content'    => $comment->comment_content,
            'author'     => $comment->comment_author,
            'date'       => mysql2date(get_option('date_format'), $comment->comment_date),
        ];
    }

    $average = $count ? $total / $count : 0;

    return sff_prepare_recipe_rating_response($recipe_id, $average, $count, $thumbs_up, $thumbs_down, $prepared);
}

function sff_prepare_recipe_rating_response($recipe_id, $average, $count, $thumbs_up, $thumbs_down, $comments) {
    $response = [
        'average'     => $average,
        'count'       => $count,
        'thumbs_up'   => $thumbs_up,
        'thumbs_down' => $thumbs_down,
        'comments'    => $comments,
    ];

    if ($recipe_id) {
        update_post_meta($recipe_id, '_sff_rating_average', $average);
        update_post_meta($recipe_id, '_sff_rating_count', $count);
        update_post_meta($recipe_id, '_sff_rating_thumbs_up', $thumbs_up);
        update_post_meta($recipe_id, '_sff_rating_thumbs_down', $thumbs_down);
    }

    return $response;
}

function sff_update_recipe_feedback_meta($recipe_id) {
    $recipe_id = intval($recipe_id);
    if (!$recipe_id) {
        return;
    }

    sff_get_recipe_rating_data($recipe_id);
}

function sff_get_user_recipe_rating_comment($recipe_id, $user_id) {
    $recipe_id = intval($recipe_id);
    $user_id   = intval($user_id);
    if (!$recipe_id || !$user_id) {
        return null;
    }

    $comments = get_comments([
        'post_id' => $recipe_id,
        'user_id' => $user_id,
        'type'    => 'sff_recipe_rating',
        'number'  => 1,
        'status'  => 'approve',
    ]);

    return $comments ? $comments[0] : null;
}

function sff_render_star_display($rating) {
    $rating = max(0, min(5, floatval($rating)));

    $output = '<div class="sff-star-display" aria-label="' . esc_attr(sprintf(__('%1$s out of 5 stars', 'simplified-food-fitness'), number_format_i18n($rating, 1))) . '">';

    for ($i = 1; $i <= 5; $i++) {
        $class = 'sff-star-display__star';
        if ($rating >= $i) {
            $class .= ' is-filled';
        } elseif ($rating > $i - 1) {
            $class .= ' is-filled';
        }
        $output .= '<span class="' . esc_attr($class) . '">★</span>';
    }

    $output .= '</div>';

    return $output;
}

function sff_admin_notice() {
    if (isset($_GET['ingredient_saved']) && $_GET['ingredient_saved'] == 'true') {
        echo '<div class="updated notice is-dismissible"><p>✅ Ingredient has been saved successfully!</p></div>';
    }
}
add_action('admin_notices', 'sff_admin_notice');
function sff_get_default_day_type_macros() {
    return [
        'rest' => [
            'label' => __('Rest Day', 'simplified-food-fitness'),
            'carbs' => 40,
            'protein' => 30,
            'fat' => 30,
        ],
        'active' => [
            'label' => __('Active Day', 'simplified-food-fitness'),
            'carbs' => 45,
            'protein' => 30,
            'fat' => 25,
        ],
        'training' => [
            'label' => __('Training Day', 'simplified-food-fitness'),
            'carbs' => 50,
            'protein' => 30,
            'fat' => 20,
        ],
    ];
}

function sff_get_day_type_macros($include_saved = true) {
    $defaults = sff_get_default_day_type_macros();

    if (!$include_saved) {
        return $defaults;
    }

    $saved = get_option('sff_day_type_macros');
    if (!is_array($saved)) {
        return $defaults;
    }

    foreach ($defaults as $slug => $config) {
        if (!isset($saved[$slug]) || !is_array($saved[$slug])) {
            continue;
        }

        $defaults[$slug]['carbs'] = isset($saved[$slug]['carbs']) ? floatval($saved[$slug]['carbs']) : $config['carbs'];
        $defaults[$slug]['protein'] = isset($saved[$slug]['protein']) ? floatval($saved[$slug]['protein']) : $config['protein'];
        $defaults[$slug]['fat'] = isset($saved[$slug]['fat']) ? floatval($saved[$slug]['fat']) : $config['fat'];
    }

    return $defaults;
}

function sff_calculate_macro_targets_from_percentages($calories, array $percentages) {
    $calories = floatval($calories);
    if ($calories <= 0) {
        return [
            'calories' => 0,
            'carbs'    => 0,
            'protein'  => 0,
            'fat'      => 0,
        ];
    }

    $carb_percent    = isset($percentages['carb_percent']) ? floatval($percentages['carb_percent']) : 0;
    $protein_percent = isset($percentages['protein_percent']) ? floatval($percentages['protein_percent']) : 0;
    $fat_percent     = isset($percentages['fat_percent']) ? floatval($percentages['fat_percent']) : 0;

    $carbs   = ($calories * $carb_percent / 100) / 4;
    $protein = ($calories * $protein_percent / 100) / 4;
    $fat     = ($calories * $fat_percent / 100) / 9;

    return [
        'calories' => $calories,
        'carbs'    => $carbs,
        'protein'  => $protein,
        'fat'      => $fat,
    ];
}

function sff_get_user_macro_profile($user_id) {
    $user_id = intval($user_id);
    if (!$user_id) {
        return [
            'calories'     => 0,
            'macros'       => ['carbs' => 0, 'protein' => 0, 'fat' => 0, 'fats' => 0],
            'percentages'  => ['carbs' => 0, 'protein' => 0, 'fat' => 0],
            'source'       => 'none',
        ];
    }

    $targets      = get_user_meta($user_id, '_sff_macro_targets', true);
    $percentages  = get_user_meta($user_id, '_sff_macro_percentages', true);
    $calories     = 0;
    $macros       = ['carbs' => 0, 'protein' => 0, 'fat' => 0];
    $macro_source = 'user_meta';

    if (is_array($targets)) {
        $calories = isset($targets['calories']) ? floatval($targets['calories']) : 0;
        $macros['carbs']   = isset($targets['carbs']) ? floatval($targets['carbs']) : (isset($targets['carb']) ? floatval($targets['carb']) : 0);
        $macros['protein'] = isset($targets['protein']) ? floatval($targets['protein']) : 0;
        $macros['fat']     = isset($targets['fat']) ? floatval($targets['fat']) : (isset($targets['fats']) ? floatval($targets['fats']) : 0);
    }

    if (!is_array($percentages) || empty($percentages)) {
        if ($calories > 0 && ($macros['carbs'] || $macros['protein'] || $macros['fat'])) {
            $percentages = [
                'carbs'   => $macros['carbs'] * 4 / max($calories, 1) * 100,
                'protein' => $macros['protein'] * 4 / max($calories, 1) * 100,
                'fat'     => $macros['fat'] * 9 / max($calories, 1) * 100,
            ];
        } else {
            $percentages = [];
        }
    }

    if ($calories <= 0 || (!$macros['carbs'] && !$macros['protein'] && !$macros['fat'])) {
        $macro_posts = get_posts([
            'post_type'      => 'macro_target',
            'author'         => $user_id,
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'fields'         => 'ids',
        ]);

        if (!empty($macro_posts)) {
            $macro_source = 'macro_post';
            $post_id  = $macro_posts[0];
            $calories = floatval(get_post_meta($post_id, 'calories', true));
            $macros['carbs']   = floatval(get_post_meta($post_id, 'carbs', true));
            $macros['protein'] = floatval(get_post_meta($post_id, 'protein', true));
            $macros['fat']     = floatval(get_post_meta($post_id, 'fats', true));
            $percentages = [
                'carbs'   => floatval(get_post_meta($post_id, 'carb_percent', true)),
                'protein' => floatval(get_post_meta($post_id, 'protein_percent', true)),
                'fat'     => floatval(get_post_meta($post_id, 'fat_percent', true)),
            ];
        }
    }

    if (!is_array($percentages) || empty($percentages)) {
        $percentages = [
            'carbs'   => 0,
            'protein' => 0,
            'fat'     => 0,
        ];
    }

    $macros['carbs']   = round($macros['carbs'], 2);
    $macros['protein'] = round($macros['protein'], 2);
    $macros['fat']     = round($macros['fat'], 2);
    $macros['fats']    = $macros['fat'];

    return [
        'calories'    => round($calories),
        'macros'      => $macros,
        'percentages' => [
            'carbs'   => round(floatval($percentages['carbs'] ?? 0), 2),
            'protein' => round(floatval($percentages['protein'] ?? 0), 2),
            'fat'     => round(floatval($percentages['fat'] ?? 0), 2),
        ],
        'source'      => $macro_source,
    ];
}


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
                        <select id="sff-ingredient-scope" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; margin-bottom:10px;">
                            <option value="all">All Ingredients</option>
                            <option value="personal">My Ingredients</option>
                            <option value="general">General Database</option>
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

    $totals = sff_get_recipe_macros_from_ids($ingredient_ids);
    update_post_meta($recipe_id, '_sff_recipe_macros', $totals);

    $cost = 0;
    foreach ($ingredient_ids as $id) {
        $cost += floatval(get_post_meta($id, '_sff_unit_cost', true));
    }
    update_post_meta($recipe_id, '_sff_recipe_cost', $cost);

    return $recipe_id;
}

function sff_get_recipe_macros_from_ids($ingredient_ids) {
    $fields = array_merge(SFF_MACRO_FIELDS, ['cost']);
    $totals = array_fill_keys($fields, 0);
    if (!is_array($ingredient_ids) || empty($ingredient_ids)) {
        return $totals;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'sff_ingredient_nutrition';
    $placeholders = implode(',', array_fill(0, count($ingredient_ids), '%d'));
    $select = implode(', ', $fields);
    $query = $wpdb->prepare("SELECT $select FROM $table WHERE ingredient_id IN ($placeholders)", $ingredient_ids);
    $results = $wpdb->get_results($query, ARRAY_A);

    foreach ($results as $row) {
        foreach ($fields as $field) {
            $totals[$field] += floatval($row[$field]);
        }
    }

    return $totals;
}

function sff_get_recipe_macros($recipe_id, $per_serving = false) {
    $ingredient_ids = get_post_meta($recipe_id, '_sff_recipe_ingredients', true);
    $totals = sff_get_recipe_macros_from_ids($ingredient_ids);
    if ($per_serving) {
        $servings = (int) get_post_meta($recipe_id, '_sff_recipe_servings', true);
        if ($servings > 0) {
            foreach ($totals as $key => $value) {
                $totals[$key] = $value / $servings;
            }
        }
    }
    return $totals;
}

function sff_admin_notice() {
    if (isset($_GET['ingredient_saved']) && $_GET['ingredient_saved'] == 'true') {
        echo '<div class="updated notice is-dismissible"><p>✅ Ingredient has been saved successfully!</p></div>';
    }
}
add_action('admin_notices', 'sff_admin_notice');

<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
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

function sff_render_ingredient_form($post_id = null) {
   // Get existing ingredient data if editing
    $brand_name = $serving_size = $servings = '';
    $front_image = $nutrition_label_image = '';
    $macros = [
        'calories' => 0, 'carbs' => 0, 'protein' => 0, 'fat' => 0, 
        'saturated_fat' => 0, 'trans_fat' => 0, 'cholesterol' => 0, 
        'sodium' => 0, 'fiber' => 0, 'sugars' => 0, 'added_sugars' => 0,
        'vitamin_d' => 0, 'calcium' => 0, 'iron' => 0, 'potassium' => 0, 'magnesium' => 0,
        'vitamin_a' => 0, 'vitamin_c' => 0, 'vitamin_e' => 0, 'zinc' => 0, 'folate' => 0,
        'riboflavin' => 0, 'niacin' => 0, 'vitamin_b6' => 0, 'vitamin_b12' => 0, 'thiamin' => 0
    ];

    if ($post_id) {
        $brand_name = get_post_meta($post_id, '_sff_brand_name', true);
        $serving_size = get_post_meta($post_id, '_sff_serving_size', true);
        $servings = get_post_meta($post_id, '_sff_servings', true);
        $macros = get_post_meta($post_id, '_sff_macros', true) ?: $macros;
        $front_image = get_post_meta($post_id, '_sff_front_image', true);
        $nutrition_label_image = get_post_meta($post_id, '_sff_nutrition_label_image', true);
    }

    ob_start(); ?>

    <div class="dashboard-container" style="max-width:600px; margin:auto; padding:20px; font-family:'Segoe UI', Arial, sans-serif;">
        
        <div style="background:#fff; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.1); padding:20px;">
            <h2 style="font-size:20px; color:#333; margin-bottom:15px;">Add Ingredient</h2>

            <!-- Step 1: Product Name Extraction -->
            <div id="sff-wizard-step-1">
                <h3 style="font-size:18px; color:#333; margin-bottom:10px;">Step 1: Upload Front Image</h3>
                <p style="font-size:14px; color:#777;">Take a picture of the front of the product to extract the name.</p>
                
                <?php if ($front_image) : ?>
                    <img src="<?php echo esc_url($front_image); ?>" alt="Front Image" style="width:100px; height:auto; border-radius:8px; margin-bottom:10px;">
                <?php endif; ?>

                <input type="file" id="sff_front_image_upload" accept="image/*" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                <button type="button" id="scan_front_image_button" style="background:#42b14c; color:white; border:none; padding:10px; border-radius:6px; cursor:pointer; font-size:14px; width:100%; margin-top:10px;">
                    1️⃣ Scan Nutrition Label 
                </button>
                <div id="scan_front_results" style="margin-top:10px; padding:10px; background:#f8f8f8; border-radius:8px; font-size:0.9rem; text-align:center;"></div>

                <button type="button" id="next_step_button" style="display:none; background:#E9FAB0; color:#023441; border:none; padding:12px; border-radius:8px; cursor:pointer; font-size:16px; width:100%; margin-top:20px;">
                    Next Step →
                </button>
            </div>

            <!-- Step 2: Nutrition Label Extraction -->
<div id="sff-wizard-step-2" style="display:none;">
    <h3 style="font-size:18px; color:#333; margin-bottom:10px;">Step 2: Upload Nutrition Label</h3>
    <p style="font-size:14px; color:#777;">Take a picture of the nutrition label to extract macros.</p>
    
    <?php if ($nutrition_label_image) : ?>
        <img src="<?php echo esc_url($nutrition_label_image); ?>" alt="Nutrition Label Image" style="width:100px; height:auto; border-radius:8px; margin-bottom:10px;">
    <?php endif; ?>

    <form method="POST" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
        <input type="hidden" name="action" value="sff_save_ingredient">
        <?php wp_nonce_field('sff_ingredient_nonce', 'sff_nonce_field'); ?>

        <label style="font-size:14px; color:#777;">Product Name:</label>
        <input type="text" name="sff_brand_name" id="sff_product_name" value="<?php echo esc_attr($brand_name); ?>" placeholder="e.g., Brand X" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; margin-bottom:10px;">


        <input type="file" id="sff_nutrition_label_upload" accept="image/*" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
        <button type="button" id="scan_nutrition_label_button" style="background:#42b14c; color:white; border:none; padding:10px; border-radius:6px; cursor:pointer; font-size:14px; width:100%; margin-top:10px;">
            2️⃣ Scan Nutrition Label 🥗
        </button>
        <div id="scan_results" style="margin-top:10px; padding:10px; background:#f8f8f8; border-radius:8px; font-size:0.9rem; text-align:center;"></div>

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
                        <input type="number" name="sff_macros[<?php echo $key; ?>]" value="<?php echo esc_attr($value); ?>" step="0.1" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;">
                    </div>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <input type="submit" name="sff_submit_ingredient" value="Save Ingredient" style="background:#E9FAB0; color:#023441; border:none; padding:12px; border-radius:8px; cursor:pointer; font-size:16px; width:100%; margin-top:20px;">
    </form>
</div>

<!-- Step 3: Success Confirmation (NEW) -->
    <div id="sff-wizard-step-3" style="display:none; text-align:center; padding:20px;">
        <h2>✅ Ingredient Added!</h2>
        <p>Your ingredient has been successfully saved.</p>
        <button id="add_new_ingredient_button" style="background:#023441; color:#E9FAB0; padding:12px 20px; border:none; border-radius:8px; font-weight:bold; cursor:pointer;">
            ➕ Add a New Ingredient
        </button>
    </div>
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

    if (isset($_POST['sff_quantity'])) {
        update_post_meta($post_id, '_sff_quantity', sanitize_text_field($_POST['sff_quantity']));
    }

    if (isset($_POST['sff_unit_type'])) {
        update_post_meta($post_id, '_sff_unit_type', sanitize_text_field($_POST['sff_unit_type']));
    }

    if (isset($_POST['sff_brand_name'])) {
        update_post_meta($post_id, '_sff_brand_name', sanitize_text_field($_POST['sff_brand_name']));
    }

    if (isset($_POST['sff_measurements'])) {
        update_post_meta($post_id, '_sff_measurements', sanitize_textarea_field($_POST['sff_measurements']));
    }

    if (isset($_POST['sff_macros'])) {
        $macros = array_map('sanitize_text_field', $_POST['sff_macros']);
        update_post_meta($post_id, '_sff_macros', $macros);

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
}
add_action('save_post', 'sff_save_ingredient_details');

function sff_custom_login_form() {
   $args = array(
        'echo'           => false,
        'redirect'       => (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
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

function sff_get_recipe_macros_from_ids($ingredient_ids) {
    $totals = ['calories' => 0, 'carbs' => 0, 'protein' => 0, 'fat' => 0];
    if (!is_array($ingredient_ids) || empty($ingredient_ids)) {
        return $totals;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'sff_ingredient_nutrition';
    $placeholders = implode(',', array_fill(0, count($ingredient_ids), '%d'));
    $query = $wpdb->prepare("SELECT calories, carbs, protein, fat FROM $table WHERE ingredient_id IN ($placeholders)", $ingredient_ids);
    $results = $wpdb->get_results($query, ARRAY_A);

    foreach ($results as $row) {
        $totals['calories'] += floatval($row['calories']);
        $totals['carbs'] += floatval($row['carbs']);
        $totals['protein'] += floatval($row['protein']);
        $totals['fat'] += floatval($row['fat']);
    }

    return $totals;
}

function sff_get_recipe_macros($recipe_id) {
    $ingredient_ids = get_post_meta($recipe_id, '_sff_recipe_ingredients', true);
    return sff_get_recipe_macros_from_ids($ingredient_ids);
}

function sff_admin_notice() {
    if (isset($_GET['ingredient_saved']) && $_GET['ingredient_saved'] == 'true') {
        echo '<div class="updated notice is-dismissible"><p>✅ Ingredient has been saved successfully!</p></div>';
    }
}
add_action('admin_notices', 'sff_admin_notice');

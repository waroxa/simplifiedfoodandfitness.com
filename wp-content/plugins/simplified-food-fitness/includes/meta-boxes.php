<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Macro Targets Meta Box
function sff_add_macro_target_meta_boxes() {
    add_meta_box(
        'sff_macro_target_details',
        __('Macro & Micro Targets'),
        'sff_render_macro_target_meta_box',
        'macro_target',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'sff_add_macro_target_meta_boxes');



// Meal Plan Meta Boxes
function sff_add_meal_plan_meta_boxes() {
    add_meta_box(
    'sff_meal_plan_details',
    __('Meal Plan Details'),
    'sff_render_meal_plan_meta_box',
    'meal_plan',
    'normal',  // ✅ Correct position
    'high'
);
add_meta_box(
    'sff_meal_plan_assign_users',
    __('Assign Meal Plan'),
    'sff_meal_plan_assign_users_callback',
    'meal_plan',
    'normal', // Moves it to full width
    'high'
);

}
add_action('add_meta_boxes', 'sff_add_meal_plan_meta_boxes');

function sff_save_meal_plan_meta_box($post_id) {
    if (isset($_POST['assigned_user'])) {
        update_post_meta($post_id, '_assigned_user', sanitize_text_field($_POST['assigned_user']));
    }
}
add_action('save_post_meal_plan', 'sff_save_meal_plan_meta_box');

// function sff_meal_plan_assign_callback($post) {
//     $assigned_user = get_post_meta($post->ID, '_assigned_user', true);
//     $users = get_users(['role' => 'subscriber']); // Get all clients

//     echo '<div style="display:flex; flex-direction:column; gap:10px;">';
//     echo '<label for="assigned_user"><strong>Assign Meal Plan to:</strong></label>';
//     echo '<select name="assigned_user" id="assigned_user" style="padding:10px; width:100%; border-radius:6px; border:1px solid #ccc; background:#fff; font-size:16px;">';
//     echo '<option value="">-- Select a Client --</option>';

//     foreach ($users as $user) {
//         $selected = ($assigned_user == $user->ID) ? 'selected' : '';
//         echo '<option value="' . esc_attr($user->ID) . '" ' . $selected . '>';
//         echo esc_html($user->display_name);
//         echo '</option>';
//     }

//     echo '</select>';
//     echo '</div>';
// }



function sff_render_meal_plan_meta_box($post) {
    wp_nonce_field('sff_save_meal_plan_details', 'sff_meal_plan_nonce');
    $meal_data = get_post_meta($post->ID, '_sff_meal_data', true);
    ?>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; max-width:800px;">

        <div>
            <label><strong>Meal Time:</strong></label>
            <input type="text" name="sff_meal_data[time]" value="<?php echo esc_attr($meal_data['time'] ?? ''); ?>" style="width:100%; padding:8px;">
        </div>

        <div>
            <label><strong>Calories:</strong></label>
            <input type="number" name="sff_meal_data[calories]" value="<?php echo esc_attr($meal_data['calories'] ?? ''); ?>" style="width:100%; padding:8px;">
        </div>

        <div style="grid-column: span 2;">
            <label><strong>Meal Title:</strong></label>
            <input type="text" name="sff_meal_data[title]" value="<?php echo esc_attr($meal_data['title'] ?? ''); ?>" style="width:100%; padding:8px;">
        </div>

        <div style="grid-column: span 2;">
            <label><strong>Description:</strong></label>
            <textarea name="sff_meal_data[description]" style="width:100%; height:80px; padding:8px;"><?php echo esc_textarea($meal_data['description'] ?? ''); ?></textarea>
        </div>

        <div>
            <label><strong>Servings:</strong></label>
            <input type="number" name="sff_meal_data[servings]" value="<?php echo esc_attr($meal_data['servings'] ?? ''); ?>" style="width:100%; padding:8px;">
        </div>

        <div>
            <label><strong>Serving Size (g):</strong></label>
            <input type="number" name="sff_meal_data[serving_size]" value="<?php echo esc_attr($meal_data['serving_size'] ?? ''); ?>" style="width:100%; padding:8px;">
        </div>

        <div>
            <label><strong>Carbs (g):</strong></label>
            <input type="number" name="sff_meal_data[carbs]" value="<?php echo esc_attr($meal_data['carbs'] ?? ''); ?>" style="width:100%; padding:8px;">
        </div>

        <div>
            <label><strong>Protein (g):</strong></label>
            <input type="number" name="sff_meal_data[protein]" value="<?php echo esc_attr($meal_data['protein'] ?? ''); ?>" style="width:100%; padding:8px;">
        </div>

        <div>
            <label><strong>Fat (g):</strong></label>
            <input type="number" name="sff_meal_data[fat]" value="<?php echo esc_attr($meal_data['fat'] ?? ''); ?>" style="width:100%; padding:8px;">
        </div>

        <div style="grid-column: span 2;">
            <label><strong>Ingredients (comma-separated):</strong></label>
            <textarea name="sff_meal_data[ingredients]" style="width:100%; height:50px; padding:8px;"><?php echo esc_textarea($meal_data['ingredients'] ?? ''); ?></textarea>
        </div>

        <div style="grid-column: span 2;">
            <label><strong>Directions:</strong></label>
            <textarea name="sff_meal_data[directions]" style="width:100%; height:80px; padding:8px;"><?php echo esc_textarea($meal_data['directions'] ?? ''); ?></textarea>
        </div>

    </div>
    <?php
}


function sff_save_meal_plan_details($post_id) {
    if (!isset($_POST['sff_meal_plan_nonce']) || !wp_verify_nonce($_POST['sff_meal_plan_nonce'], 'sff_save_meal_plan_details')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['sff_meal_data'])) {
        update_post_meta($post_id, '_sff_meal_data', $_POST['sff_meal_data']);
    }
}
add_action('save_post', 'sff_save_meal_plan_details');



// function sff_render_user_assignment($post) {
//     wp_nonce_field('sff_save_user_assignment_nonce_action', 'sff_user_assignment_nonce');
//     $assigned_users = get_post_meta($post->ID, '_sff_assigned_users', true) ?: [];
//     $users = get_users();
//      // Add a nonce field for security
//     wp_nonce_field('sff_save_user_assignment_nonce_action', 'sff_user_assignment_nonce');

//     // Get previously assigned users
//     $assigned_users = get_post_meta($post->ID, '_sff_assigned_users', true);
//     if (!is_array($assigned_users)) {
//         $assigned_users = [];
//     }

//     // Fetch all users
//     $users = get_users();

//     echo '<label for="sff_assigned_users">Select Users:</label>';
//     echo '<select name="sff_assigned_users[]" multiple style="width:100%;">';
//     foreach ($users as $user) {
//         $selected = in_array($user->ID, $assigned_users) ? 'selected' : '';
//         echo '<option value="' . esc_attr($user->ID) . '" ' . $selected . '>' . esc_html($user->display_name . ' (' . $user->user_email . ')') . '</option>';
//     }
//     echo '</select>';
// }

function sff_meal_plan_assign_users_callback($post) {
    $assigned_user = get_post_meta($post->ID, '_assigned_user', true);
    $assigned_users = get_post_meta($post->ID, '_sff_assigned_users', true);
    if (!is_array($assigned_users)) {
        $assigned_users = [];
    }

    $users = get_users(); // Fetch ALL users, not just subscribers

    echo '<div style="display:grid; gap:15px;">';

    // Assign Single Client (Dropdown)
    echo '<div>';
    echo '<label for="assigned_user"><strong>Assign Meal Plan to:</strong></label>';
    echo '<select name="assigned_user" id="assigned_user" style="padding:10px; width:100%; border-radius:6px; border:1px solid #ccc; background:#fff; font-size:16px;">';
    echo '<option value="">-- Select a Client --</option>';

    foreach ($users as $user) {
        $selected = ($assigned_user == $user->ID) ? 'selected' : '';
        echo '<option value="' . esc_attr($user->ID) . '" ' . $selected . '>';
        echo esc_html($user->display_name);
        echo '</option>';
    }
    echo '</select>';
    echo '</div>';

    // Assign Multiple Users (Multi-Select)
    echo '<div>';
    echo '<label for="sff_assigned_users"><strong>Assign to Multiple Users:</strong></label>';
    echo '<select name="sff_assigned_users[]" multiple style="padding:10px; width:100%; height:100px; border-radius:6px; border:1px solid #ccc; background:#fff; font-size:16px;">';

    foreach ($users as $user) {
        $selected = in_array($user->ID, $assigned_users) ? 'selected' : '';
        echo '<option value="' . esc_attr($user->ID) . '" ' . $selected . '>';
        echo esc_html($user->display_name . ' (' . $user->user_email . ')');
        echo '</option>';
    }

    echo '</select>';
    echo '</div>';

    echo '</div>';
}



function sff_save_customer_assignment($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!isset($_POST['sff_user_assignment_nonce']) || !wp_verify_nonce($_POST['sff_user_assignment_nonce'], 'sff_save_user_assignment_nonce_action')) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['sff_assigned_users']) && !empty($_POST['sff_assigned_users'])) {
        $assigned_users = array_map('intval', $_POST['sff_assigned_users']);
        update_post_meta($post_id, '_sff_assigned_users', $assigned_users);
    } else {
        delete_post_meta($post_id, '_sff_assigned_users');
    }
}
add_action('save_post', 'sff_save_customer_assignment');

// Ingredient Meta Boxes
function sff_add_ingredient_meta_boxes() {
    add_meta_box(
        'sff_ingredient_details',
        __('Ingredient Details'),
        'sff_render_ingredient_meta_box',
        'ingredient',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'sff_add_ingredient_meta_boxes');

function sff_add_admin_ingredient_meta_box() {
    add_meta_box(
        'sff_admin_ingredient_details',
        __('Ingredient Details'),
        'sff_render_admin_ingredient_meta_box',
        'ingredient',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'sff_add_admin_ingredient_meta_box');

function sff_render_admin_ingredient_meta_box($post) {
    // Retrieve saved ingredient details
    $brand_name = get_post_meta($post->ID, '_sff_brand_name', true) ?: '';
    $serving_size = get_post_meta($post->ID, '_sff_serving_size', true) ?: '';
    $servings = get_post_meta($post->ID, '_sff_servings', true) ?: '';

    // Retrieve saved nutrition label image
    $nutrition_label_image = get_post_meta($post->ID, '_sff_nutrition_label_image', true);

    // Default macros (fallback if none exist)
    $default_macros = [
        'calories' => '', 'carbs' => '', 'protein' => '', 'fat' => '',
        'saturated_fat' => '', 'trans_fat' => '', 'cholesterol' => '',
        'sodium' => '', 'fiber' => '', 'sugars' => '', 'added_sugars' => '',
        'vitamin_d' => '', 'calcium' => '', 'iron' => '', 'potassium' => '', 'magnesium' => ''
    ];

    $saved_macros = get_post_meta($post->ID, '_sff_macros', true);
    $macros = is_array($saved_macros) ? array_merge($default_macros, $saved_macros) : $default_macros;

    ?>
    <div class="sff-admin-ingredient-form">
        <h2>Ingredient Details</h2>

        <label>Product Name:</label>
        <input type="text" name="sff_brand_name" value="<?php echo esc_attr($brand_name); ?>" placeholder="e.g., Lactaid">

        <label>Serving Size:</label>
        <input type="text" name="sff_serving_size" value="<?php echo esc_attr($serving_size); ?>" placeholder="e.g., 1 cup (240ml)">

        <label>Servings Per Container:</label>
        <input type="number" name="sff_servings" value="<?php echo esc_attr($servings); ?>" placeholder="e.g., 4">

        <h3>Macros per Serving</h3>
        <div class="sff-macro-fields">
            <?php foreach ($macros as $key => $value) : ?>
                <label><?php echo ucwords(str_replace('_', ' ', $key)); ?>:</label>
                <input type="number" name="sff_macros[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($value); ?>" step="0.1">
            <?php endforeach; ?>
        </div>

        <!-- ✅ Display Nutrition Label Image if available -->
        <?php if ($nutrition_label_image) : ?>
            <div style="margin-top: 20px;">
                <p><strong>Nutrition Label:</strong></p>
                <img src="<?php echo esc_url($nutrition_label_image); ?>" 
                     style="max-width: 200px; height: auto; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            </div>
        <?php endif; ?>

    </div>
    <?php
}


function sff_save_admin_ingredient_details($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['sff_brand_name'])) update_post_meta($post_id, '_sff_brand_name', sanitize_text_field($_POST['sff_brand_name']));
    if (isset($_POST['sff_serving_size'])) update_post_meta($post_id, '_sff_serving_size', sanitize_text_field($_POST['sff_serving_size']));
    if (isset($_POST['sff_servings'])) update_post_meta($post_id, '_sff_servings', absint($_POST['sff_servings']));
    if (isset($_POST['sff_macros'])) update_post_meta($post_id, '_sff_macros', array_map('sanitize_text_field', $_POST['sff_macros']));
}
add_action('save_post', 'sff_save_admin_ingredient_details');



function sff_render_macro_target_meta_box($post) {
    // Retrieve existing macro & micro targets
    $macros = get_post_meta($post->ID, '_macro_targets', true);
    $micros = get_post_meta($post->ID, '_micro_targets', true);
    ?>

    <h3>Macro Targets</h3>
    <table class="form-table">
        <tr>
            <th><label for="calories">Calories</label></th>
            <td><input type="number" name="calories" value="<?php echo esc_attr($macros['calories'] ?? ''); ?>"></td>
        </tr>
        <tr>
            <th><label for="protein">Protein (g)</label></th>
            <td><input type="number" name="protein" value="<?php echo esc_attr($macros['protein'] ?? ''); ?>"></td>
        </tr>
        <tr>
            <th><label for="carbs">Carbs (g)</label></th>
            <td><input type="number" name="carbs" value="<?php echo esc_attr($macros['carbs'] ?? ''); ?>"></td>
        </tr>
        <tr>
            <th><label for="fats">Fats (g)</label></th>
            <td><input type="number" name="fats" value="<?php echo esc_attr($macros['fats'] ?? ''); ?>"></td>
        </tr>
    </table>

    <h3>Micro Targets</h3>
    <table class="form-table">
        <tr>
            <th><label for="vitamin_c">Vitamin C (mg)</label></th>
            <td><input type="number" name="vitamin_c" value="<?php echo esc_attr($micros['vitamin_c'] ?? ''); ?>"></td>
        </tr>
        <tr>
            <th><label for="iron">Iron (mg)</label></th>
            <td><input type="number" name="iron" value="<?php echo esc_attr($micros['iron'] ?? ''); ?>"></td>
        </tr>
        <tr>
            <th><label for="fiber">Fiber (g)</label></th>
            <td><input type="number" name="fiber" value="<?php echo esc_attr($micros['fiber'] ?? ''); ?>"></td>
        </tr>
    </table>

    <?php
}

function sff_save_macro_target_meta_box($post_id) {
    if (!isset($_POST['calories']) || !isset($_POST['vitamin_c'])) {
        return;
    }

    $macros = [
        'calories' => $_POST['calories'],
        'protein'  => $_POST['protein'],
        'carbs'    => $_POST['carbs'],
        'fats'     => $_POST['fats'],
    ];

    $micros = [
        'vitamin_c' => $_POST['vitamin_c'],
        'iron'      => $_POST['iron'],
        'fiber'     => $_POST['fiber'],
    ];

    update_post_meta($post_id, '_macro_targets', $macros);
    update_post_meta($post_id, '_micro_targets', $micros);
}
add_action('save_post_macro_target', 'sff_save_macro_target_meta_box');

function sff_add_macro_micro_targets_meta_box($user) {
    $macros = get_user_meta($user->ID, '_sff_macro_targets', true);
    $micros = get_user_meta($user->ID, '_sff_micro_targets', true);
    ?>

    <h3>Macro Targets</h3>
    <table class="form-table">
        <tr>
            <th><label for="calories">Calories</label></th>
            <td><input type="number" name="macro_targets[calories]" value="<?php echo esc_attr($macros['calories'] ?? ''); ?>"></td>
        </tr>
        <tr>
            <th><label for="protein">Protein (g)</label></th>
            <td><input type="number" name="macro_targets[protein]" value="<?php echo esc_attr($macros['protein'] ?? ''); ?>"></td>
        </tr>
        <tr>
            <th><label for="carbs">Carbs (g)</label></th>
            <td><input type="number" name="macro_targets[carbs]" value="<?php echo esc_attr($macros['carbs'] ?? ''); ?>"></td>
        </tr>
        <tr>
            <th><label for="fats">Fats (g)</label></th>
            <td><input type="number" name="macro_targets[fats]" value="<?php echo esc_attr($macros['fats'] ?? ''); ?>"></td>
        </tr>
    </table>

    <h3>Micro Targets</h3>
    <table class="form-table">
        <tr>
            <th><label for="vitamin_c">Vitamin C (mg)</label></th>
            <td><input type="number" name="micro_targets[vitamin_c]" value="<?php echo esc_attr($micros['vitamin_c'] ?? ''); ?>"></td>
        </tr>
        <tr>
            <th><label for="iron">Iron (mg)</label></th>
            <td><input type="number" name="micro_targets[iron]" value="<?php echo esc_attr($micros['iron'] ?? ''); ?>"></td>
        </tr>
        <tr>
            <th><label for="fiber">Fiber (g)</label></th>
            <td><input type="number" name="micro_targets[fiber]" value="<?php echo esc_attr($micros['fiber'] ?? ''); ?>"></td>
        </tr>
    </table>

    <?php
}

function sff_save_macro_micro_targets($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    if (isset($_POST['macro_targets'])) {
        update_user_meta($user_id, '_sff_macro_targets', $_POST['macro_targets']);
    }
    if (isset($_POST['micro_targets'])) {
        update_user_meta($user_id, '_sff_micro_targets', $_POST['micro_targets']);
    }
}
add_action('show_user_profile', 'sff_add_macro_micro_targets_meta_box');
add_action('edit_user_profile', 'sff_add_macro_micro_targets_meta_box');
add_action('personal_options_update', 'sff_save_macro_micro_targets');
add_action('edit_user_profile_update', 'sff_save_macro_micro_targets');

// Add fields in user profile (admin)
add_action('show_user_profile', 'sff_custom_macro_fields');
add_action('edit_user_profile', 'sff_custom_macro_fields');

function sff_custom_macro_fields($user) {
    ?>
    <h3>Macro Percentages</h3>
    <table class="form-table">
        <tr>
            <th><label for="carb_percent">Carbs (%)</label></th>
            <td><input type="number" name="carb_percent" value="<?php echo esc_attr(get_user_meta($user->ID, 'carb_percent', true)); ?>" min="0" max="100" /></td>
        </tr>
        <tr>
            <th><label for="protein_percent">Protein (%)</label></th>
            <td><input type="number" name="protein_percent" value="<?php echo esc_attr(get_user_meta($user->ID, 'protein_percent', true)); ?>" min="0" max="100" /></td>
        </tr>
        <tr>
            <th><label for="fat_percent">Fat (%)</label></th>
            <td><input type="number" name="fat_percent" value="<?php echo esc_attr(get_user_meta($user->ID, 'fat_percent', true)); ?>" min="0" max="100" /></td>
        </tr>
    </table>
    <?php
}

// Save these fields
add_action('personal_options_update', 'sff_save_custom_macro_fields');
add_action('edit_user_profile_update', 'sff_save_custom_macro_fields');

// Add custom fields for Macro Percentages in Macro Targets CPT
add_action('add_meta_boxes', function () {
    add_meta_box(
        'macro_percentages_meta_box',
        'Macro Percentages',
        'render_macro_percentages_meta_box',
        'macro_targets', // your CPT name
        'normal',
        'high'
    );
});

function render_macro_percentages_meta_box($post) {
    // Get existing values if they exist
    $carb_percent = get_post_meta($post->ID, 'carb_percent', true);
    $protein_percent = get_post_meta($post->ID, 'protein_percent', true);
    $fat_percent = get_post_meta($post->ID, 'fat_percent', true);
    ?>
    <p><label for="carb_percent">Carbs (%)</label><br>
        <input type="number" name="carb_percent" id="carb_percent" value="<?php echo esc_attr($carb_percent); ?>" min="0" max="100" placeholder="Default: 50">
    </p>
    <p><label for="protein_percent">Protein (%)</label><br>
        <input type="number" name="protein_percent" id="protein_percent" value="<?php echo esc_attr($protein_percent); ?>" min="0" max="100" placeholder="Default: 30">
    </p>
    <p><label for="fat_percent">Fats (%)</label><br>
        <input type="number" name="fat_percent" id="fat_percent" value="<?php echo esc_attr($fat_percent); ?>" min="0" max="100" placeholder="Default: 20">
    </p>
    <?php
}

// Save the custom fields when the post is saved
add_action('save_post_macro_targets', function ($post_id) {
    if (isset($_POST['carb_percent'])) {
        update_post_meta($post_id, 'carb_percent', intval($_POST['carb_percent']));
    }
    if (isset($_POST['protein_percent'])) {
        update_post_meta($post_id, 'protein_percent', intval($_POST['protein_percent']));
    }
    if (isset($_POST['fat_percent'])) {
        update_post_meta($post_id, 'fat_percent', intval($_POST['fat_percent']));
    }
});


function sff_save_custom_macro_fields($user_id) {
    if (!current_user_can('edit_user', $user_id)) return false;

    update_user_meta($user_id, 'carb_percent', intval($_POST['carb_percent']));
    update_user_meta($user_id, 'protein_percent', intval($_POST['protein_percent']));
    update_user_meta($user_id, 'fat_percent', intval($_POST['fat_percent']));
}

<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!function_exists('sff_get_nutrient_groups')) {
    function sff_get_nutrient_groups() {
        $macro_group_macros = [
            'calories', 'carbs', 'protein', 'fat',
            'saturated_fat', 'trans_fat', 'cholesterol', 'sodium',
            'fiber', 'sugars', 'added_sugars',
        ];

        $macro_group_micros = array_values(array_diff(SFF_MACRO_FIELDS, $macro_group_macros));

        return [
            'macros' => $macro_group_macros,
            'micros' => $macro_group_micros,
        ];
    }
}

if (!function_exists('sff_format_nutrient_label')) {
    function sff_format_nutrient_label($field) {
        $field = str_replace('_', ' ', $field);
        $field = ucwords($field);
        $field = str_replace(['B12', 'B6'], ['B12', 'B6'], $field);
        return $field;
    }
}

if (!function_exists('sff_format_nutrient_value')) {
    function sff_format_nutrient_value($value, $field) {
        if ($field === 'cost') {
            return '$' . number_format_i18n(floatval($value), 2);
        }

        $number = floatval($value);

        if ($field === 'calories') {
            return number_format_i18n(round($number));
        }

        $precision = abs($number - round($number)) < 0.01 ? 0 : 2;

        return number_format_i18n($number, $precision);
    }
}

if (!function_exists('sff_render_nutrient_cards_html')) {
    function sff_render_nutrient_cards_html($data) {
        if (!is_array($data) || empty($data)) {
            return '<p class="sff-nutrient-empty">' . esc_html__('Select ingredients to see nutrition details.', 'simplified-food-fitness') . '</p>';
        }

        $groups = sff_get_nutrient_groups();
        $order  = array_merge($groups['macros'], $groups['micros']);

        if (array_key_exists('cost', $data)) {
            $order[] = 'cost';
        }

        $html = '';

        foreach ($order as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            $value = $data[$field];
            if ($field === 'cost') {
                $group = 'cost';
            } else {
                $group = in_array($field, $groups['macros'], true) ? 'macro' : (in_array($field, $groups['micros'], true) ? 'micro' : 'meta');
            }

            $html .= '<div class="sff-nutrient-card sff-nutrient-card--' . esc_attr($group) . '">';
            $html .= '<span class="sff-nutrient-label">' . esc_html(sff_format_nutrient_label($field)) . '</span>';
            $html .= '<span class="sff-nutrient-value">' . esc_html(sff_format_nutrient_value($value, $field)) . '</span>';
            $html .= '</div>';
        }

        if ($html === '') {
            $html = '<p class="sff-nutrient-empty">' . esc_html__('Select ingredients to see nutrition details.', 'simplified-food-fitness') . '</p>';
        }

        return $html;
    }
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
    // Nonce
    wp_nonce_field('sff_save_meal_plan_details', 'sff_meal_plan_nonce');

    // Read whatever is currently stored
    $raw = get_post_meta($post->ID, '_sff_meal_data', true);

    // Normalize to JSON string + PHP array schedule
    if (is_string($raw) && $raw !== '') {
        $schedule_json = $raw;
        $decoded = json_decode($raw, true);
        $schedule = is_array($decoded) ? $decoded : [];
        $meal_data = []; // optional “quick meal” seed; JS can map from schedule
    } elseif (is_array($raw)) {
        // Legacy array storage (old codex branch) – keep it visible in the quick-meal fields
        $meal_data = $raw;
        $schedule = []; // start empty; JS can choose to translate legacy fields into schedule
        $schedule_json = json_encode($schedule);
    } else {
        $meal_data = [];
        $schedule = [];
        $schedule_json = json_encode($schedule);
    }

    $day_type_raw = get_post_meta($post->ID, '_sff_day_types', true);
    if (is_string($day_type_raw) && $day_type_raw !== '') {
        $decoded = json_decode($day_type_raw, true);
        $selected_day_types = is_array($decoded) ? $decoded : [];
    } elseif (is_array($day_type_raw)) {
        $selected_day_types = $day_type_raw;
    } else {
        $selected_day_types = [];
    }

    $day_type_configs = sff_get_day_type_macros();
    $day_type_default = array_key_first($day_type_configs);
    $day_order        = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    foreach ($day_order as $day_key) {
        if (empty($selected_day_types[$day_key]) || !isset($day_type_configs[$selected_day_types[$day_key]])) {
            $selected_day_types[$day_key] = $day_type_default;
        }
    }

    $assigned_users = get_post_meta($post->ID, '_sff_assigned_users', true);
    if (!is_array($assigned_users)) {
        $assigned_users = [];
    }
    $primary_user_id = !empty($assigned_users) ? intval($assigned_users[0]) : 0;
    $macro_profile   = $primary_user_id ? sff_get_user_macro_profile($primary_user_id) : ['calories' => 0, 'macros' => ['carbs' => 0, 'protein' => 0, 'fat' => 0], 'percentages' => ['carbs' => 0, 'protein' => 0, 'fat' => 0]];
    $calorie_target  = !empty($macro_profile['calories']) ? floatval($macro_profile['calories']) : 2000;

    $day_type_targets = [];
    foreach ($day_type_configs as $slug => $config) {
        $percentages = [
            'carb_percent'    => floatval($config['carbs']),
            'protein_percent' => floatval($config['protein']),
            'fat_percent'     => floatval($config['fat']),
        ];
        $calculated = sff_calculate_macro_targets_from_percentages($calorie_target, $percentages);
        $day_type_targets[$slug] = [
            'label'       => $config['label'],
            'calories'    => round($calculated['calories']),
            'carbs'       => round($calculated['carbs'], 1),
            'protein'     => round($calculated['protein'], 1),
            'fat'         => round($calculated['fat'], 1),
            'percentages' => [
                'carbs'   => $percentages['carb_percent'],
                'protein' => $percentages['protein_percent'],
                'fat'     => $percentages['fat_percent'],
            ],
        ];
    }

    // Enqueue scripts
    wp_enqueue_script('sortablejs', 'https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js', [], '1.15.0', true);
    wp_enqueue_script('sff-meal-plan-calendar', SFF_PLUGIN_URL . 'assets/js/meal-plan-calendar.js', ['sortablejs'], '1.0', true);

    // Build recipe data + macros
    $recipes       = get_posts(['post_type' => 'recipe', 'numberposts' => -1]);
    $recipes_data  = [];
    $recipe_macros = [];
    foreach ($recipes as $recipe) {
        $recipes_data[]             = ['id' => $recipe->ID, 'title' => $recipe->post_title];
        $recipe_macros[$recipe->ID] = sff_get_recipe_macros($recipe->ID, true);
    }

    // Pass data to JS
    wp_localize_script('sff-meal-plan-calendar', 'sffMealPlan', [
        'recipes'          => $recipes_data,
        'schedule'         => $schedule,
        'macros'           => $recipe_macros,
        'ajaxUrl'          => admin_url('admin-ajax.php'),
        'nonce'            => wp_create_nonce('sff_meal_plan_js'),
        'selectedDayTypes' => $selected_day_types,
        'dayTypeOptions'   => $day_type_targets,
        'calorieTarget'    => $calorie_target,
    ]);
    ?>

    <div class="sff-meal-plan-admin">
        <div class="sff-card sff-quick-meal">
            <div class="sff-card__header">
                <h3><?php esc_html_e('Quick Meal Details', 'simplified-food-fitness'); ?></h3>
                <p><?php esc_html_e('Optional reference details you can use while planning the week.', 'simplified-food-fitness'); ?></p>
            </div>

            <div class="sff-field-grid">
                <div class="sff-field">
                    <label for="sff_meal_meta_time"><?php esc_html_e('Meal Time', 'simplified-food-fitness'); ?></label>
                    <input type="text" id="sff_meal_meta_time" name="sff_meal_meta[time]" value="<?php echo esc_attr($meal_data['time'] ?? ''); ?>">
                </div>

                <div class="sff-field">
                    <label for="sff_meal_meta_calories"><?php esc_html_e('Calories', 'simplified-food-fitness'); ?></label>
                    <input type="number" id="sff_meal_meta_calories" name="sff_meal_meta[calories]" value="<?php echo esc_attr($meal_data['calories'] ?? ''); ?>">
                </div>

                <div class="sff-field sff-field--wide">
                    <label for="sff_meal_meta_title"><?php esc_html_e('Meal Title', 'simplified-food-fitness'); ?></label>
                    <input type="text" id="sff_meal_meta_title" name="sff_meal_meta[title]" value="<?php echo esc_attr($meal_data['title'] ?? ''); ?>">
                </div>

                <div class="sff-field sff-field--wide">
                    <label for="sff_meal_meta_description"><?php esc_html_e('Description', 'simplified-food-fitness'); ?></label>
                    <textarea id="sff_meal_meta_description" name="sff_meal_meta[description]" rows="4"><?php echo esc_textarea($meal_data['description'] ?? ''); ?></textarea>
                </div>

                <div class="sff-field sff-field--wide">
                    <label for="sff_meal_meta_recipe"><?php esc_html_e('Recipe', 'simplified-food-fitness'); ?></label>
                    <select id="sff_meal_meta_recipe" name="sff_meal_meta[recipe_id]">
                        <option value=""><?php esc_html_e('-- Select Recipe --', 'simplified-food-fitness'); ?></option>
                        <?php foreach ($recipes as $recipe) :
                            $selected = ((int)($meal_data['recipe_id'] ?? 0) === $recipe->ID) ? 'selected' : '';
                        ?>
                            <option value="<?php echo esc_attr($recipe->ID); ?>" <?php echo $selected; ?>>
                                <?php echo esc_html($recipe->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" id="sff-open-recipe-modal" class="button button-secondary sff-inline-button">
                        <?php esc_html_e('Add New Recipe', 'simplified-food-fitness'); ?>
                    </button>
                </div>

                <div class="sff-field">
                    <label for="sff_meal_meta_servings"><?php esc_html_e('Servings', 'simplified-food-fitness'); ?></label>
                    <input type="number" id="sff_meal_meta_servings" name="sff_meal_meta[servings]" value="<?php echo esc_attr($meal_data['servings'] ?? ''); ?>">
                </div>

                <div class="sff-field">
                    <label for="sff_meal_meta_serving_size"><?php esc_html_e('Serving Size (g)', 'simplified-food-fitness'); ?></label>
                    <input type="number" id="sff_meal_meta_serving_size" name="sff_meal_meta[serving_size]" value="<?php echo esc_attr($meal_data['serving_size'] ?? ''); ?>">
                </div>
            </div>
        </div>

        <div class="sff-meal-plan-body">
            <div class="sff-card sff-meal-plan-sidebar">
                <div class="sff-panel-header">
                    <h3><?php esc_html_e('Recipes', 'simplified-food-fitness'); ?></h3>
                    <p><?php esc_html_e('Drag recipes from your library into the calendar.', 'simplified-food-fitness'); ?></p>
                </div>
                <div id="sff-recipe-list" class="sff-recipe-pool" aria-live="polite"></div>
            </div>

            <div class="sff-card sff-meal-plan-calendar">
                <div class="sff-panel-header">
                    <h3><?php esc_html_e('Weekly Meal Plan', 'simplified-food-fitness'); ?></h3>
                    <p><?php esc_html_e('Drop recipes into each day and reorder them to match your plan.', 'simplified-food-fitness'); ?></p>
                </div>
                <div id="sff-meal-calendar" class="sff-calendar-grid" aria-live="polite"></div>
            </div>
        </div>

        <div class="sff-card sff-day-type-selector">
            <div class="sff-panel-header">
                <h3><?php esc_html_e('Day Type Targets', 'simplified-food-fitness'); ?></h3>
                <p><?php esc_html_e('Adjust the training focus for each day to update macro targets.', 'simplified-food-fitness'); ?></p>
            </div>
            <div class="sff-day-type-selector__grid">
                <?php foreach ($day_order as $day_key) :
                    $current = $selected_day_types[$day_key];
                    $label   = ucfirst($day_key);
                ?>
                    <div class="sff-day-type-selector__item" data-day="<?php echo esc_attr($day_key); ?>">
                        <label for="sff-day-type-<?php echo esc_attr($day_key); ?>"><?php echo esc_html($label); ?></label>
                        <select id="sff-day-type-<?php echo esc_attr($day_key); ?>" class="sff-day-type-select" data-day="<?php echo esc_attr($day_key); ?>">
                            <?php foreach ($day_type_targets as $slug => $config) : ?>
                                <option value="<?php echo esc_attr($slug); ?>" <?php selected($current, $slug); ?>><?php echo esc_html($config['label']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <!-- Hidden JSON the saver expects -->
    <input type="hidden" name="sff_meal_data" id="sff_meal_data" value="<?php echo esc_attr($schedule_json); ?>">
    <input type="hidden" name="sff_day_types" id="sff_day_types" value="<?php echo esc_attr(wp_json_encode($selected_day_types)); ?>">

    <!-- Modal: Create Recipe -->
    <div id="sff-recipe-modal" style="display:none;">
        <div class="sff-modal-content" style="background:#fff; padding:16px; max-width:700px; box-shadow:0 10px 30px rgba(0,0,0,.2);">
            <button type="button" id="sff-recipe-modal-close" class="button" style="float:right;">&times;</button>
            <h3><?php esc_html_e('Create Recipe'); ?></h3>

            <input type="text" id="sff-recipe-name" placeholder="<?php esc_attr_e('Recipe Name'); ?>" style="width:100%; margin-bottom:8px;">
            <?php $ingredient_categories = get_terms('ingredient_category', ['hide_empty' => false]); ?>
            <select id="sff-ingredient-category-filter" style="width:100%; margin-bottom:8px;">
                <option value=""><?php esc_html_e('All Categories'); ?></option>
                <?php foreach ($ingredient_categories as $cat) : ?>
                    <option value="<?php echo esc_attr($cat->term_id); ?>"><?php echo esc_html($cat->name); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" id="sff-ingredient-search" placeholder="<?php esc_attr_e('Search ingredients'); ?>" style="width:100%; margin-bottom:8px;">

            <ul id="sff-ingredient-results" style="max-height:200px; overflow:auto; border:1px solid #eee; padding:8px; margin-bottom:8px;"></ul>

            <h4><?php esc_html_e('Selected Ingredients'); ?></h4>
            <ul id="sff-selected-ingredients" style="max-height:200px; overflow:auto; border:1px solid #eee; padding:8px; margin-bottom:8px;"></ul>

            <p><?php esc_html_e('Calories:'); ?> <span id="sff-total-calories">0</span></p>
            <p><?php esc_html_e('Carbs:'); ?> <span id="sff-total-carbs">0</span></p>
            <p><?php esc_html_e('Protein:'); ?> <span id="sff-total-protein">0</span></p>
            <p><?php esc_html_e('Fat:'); ?> <span id="sff-total-fat">0</span></p>
            <p><?php esc_html_e('Cost: $'); ?><span id="sff-total-cost">0</span></p>

            <div id="sff-recipe-macro-summary" style="display:none; margin-top:12px;">
                <h5 style="margin:0 0 8px; font-size:15px; color:#023441;">Full Nutrition Totals</h5>
                <div class="sff-recipe-macro-grid"></div>
            </div>

            <button type="button" id="sff-save-recipe" class="button button-primary">
                <?php esc_html_e('Save Recipe'); ?>
            </button>
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
        $schedule_json = wp_unslash($_POST['sff_meal_data']);
        update_post_meta($post_id, '_sff_meal_data', $schedule_json);
    }

    if (isset($_POST['sff_day_types'])) {
        $raw = wp_unslash($_POST['sff_day_types']);
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $configs = sff_get_day_type_macros();
            $filtered = [];
            foreach ($decoded as $day => $slug) {
                $day = sanitize_key($day);
                $slug = sanitize_key($slug);
                if ($day && isset($configs[$slug])) {
                    $filtered[$day] = $slug;
                }
            }
            update_post_meta($post_id, '_sff_day_types', $filtered);
        }
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
    wp_nonce_field('sff_save_user_assignment_nonce_action', 'sff_user_assignment_nonce');
    $assigned_users = get_post_meta($post->ID, '_sff_assigned_users', true);
    if (!is_array($assigned_users)) {
        $assigned_users = [];
    }

    $users = get_users(['role' => 'subscriber']);

    echo '<label for="sff_assigned_users"><strong>Select Subscribers:</strong></label>';
    echo '<select name="sff_assigned_users[]" multiple style="padding:10px; width:100%; height:150px; border-radius:6px; border:1px solid #ccc; background:#fff; font-size:16px;">';

    foreach ($users as $user) {
        $selected = in_array($user->ID, $assigned_users) ? 'selected' : '';
        echo '<option value="' . esc_attr($user->ID) . '" ' . $selected . '>';
        echo esc_html($user->display_name . ' (' . $user->user_email . ')');
        echo '</option>';
    }

    echo '</select>';
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
                <input type="number" name="sff_macros[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($value); ?>" step="any">
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


function sff_add_recipe_meta_boxes() {
    add_meta_box(
        'sff_recipe_details',
        __('Recipe Details'),
        'sff_render_recipe_meta_box',
        'recipe',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'sff_add_recipe_meta_boxes');

function sff_render_recipe_meta_box($post) {
    wp_nonce_field('sff_save_recipe_details', 'sff_recipe_details_nonce');

    $saved = get_post_meta($post->ID, '_sff_recipe_ingredients', true);
    if (!is_array($saved)) {
        $saved = [];
    }

    $serving_map = get_post_meta($post->ID, '_sff_recipe_ingredient_servings', true);
    if (!is_array($serving_map)) {
        $serving_map = [];
    }

    $servings    = max(1, intval(get_post_meta($post->ID, '_sff_recipe_servings', true)));
    $ingredients = get_posts([
        'post_type'      => 'ingredient',
        'numberposts'    => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'fields'         => 'all',
        'suppress_filters' => false,
    ]);

    $cover_id    = (int) get_post_meta($post->ID, '_sff_recipe_cover_id', true);
    $gallery_ids = get_post_meta($post->ID, '_sff_recipe_gallery_ids', true);
    if (!is_array($gallery_ids)) {
        $gallery_ids = array_filter(array_map('intval', (array) $gallery_ids));
    }

    $ingredient_macros_total = [];
    $macros_per_serving      = [];

    if (!empty($saved)) {
        $ingredient_macros_total = sff_get_recipe_macros_from_ids($saved, $serving_map);

        foreach ($ingredient_macros_total as $key => $value) {
            $macros_per_serving[$key] = $value / $servings;
        }
    }

    $selected_details = [];
    foreach ($ingredients as $ingredient) {
        if (!in_array($ingredient->ID, $saved, true)) {
            continue;
        }

        $selected_details[] = [
            'id'          => $ingredient->ID,
            'title'       => $ingredient->post_title,
            'servings'    => isset($serving_map[$ingredient->ID]) ? max(0.01, floatval($serving_map[$ingredient->ID])) : 1,
            'servingSize' => get_post_meta($ingredient->ID, '_sff_serving_size', true),
        ];
    }

    $selected_count = count($selected_details);
    $selected_label = _n('ingredient selected', 'ingredients selected', $selected_count, 'simplified-food-fitness');

    echo '<div class="sff-recipe-builder">';
    echo '<div class="sff-recipe-builder__intro">';
    echo '<h3>' . esc_html__('Build Your Recipe', 'simplified-food-fitness') . '</h3>';
    echo '<p>' . esc_html__('Select ingredients from your library, then fine-tune the serving size to watch macros adjust instantly.', 'simplified-food-fitness') . '</p>';
    echo '</div>';

    echo '<div class="sff-recipe-builder__body">';
    echo '<div class="sff-recipe-builder__ingredients">';
    echo '<input type="hidden" name="sff_recipe_ingredients_present" value="1" />';
    echo '<label for="sff_recipe_ingredient_picker" class="sff-recipe-field-label">' . esc_html__('Ingredient Library', 'simplified-food-fitness') . '</label>';
    echo '<p class="description">' . esc_html__('Use the search to quickly find ingredients, then choose how many servings to add to your recipe.', 'simplified-food-fitness') . '</p>';
    echo '<div class="sff-recipe-selected-pill">';
    echo '<span id="sff-recipe-selected-count">' . esc_html($selected_count) . '</span> ';
    echo '<span id="sff-recipe-selected-label">' . esc_html($selected_label) . '</span>';
    echo '</div>';
    echo '<div class="sff-recipe-ingredient-search">';
    echo '<span aria-hidden="true">🔍</span>';
    echo '<input type="search" id="sff-recipe-ingredient-filter" placeholder="' . esc_attr__('Search ingredients…', 'simplified-food-fitness') . '" />';
    echo '</div>';
    echo '<p id="sff-recipe-no-results" class="sff-recipe-no-results" style="display:none;">' . esc_html__('No ingredients match your search.', 'simplified-food-fitness') . '</p>';
    echo '<div class="sff-recipe-ingredient-picker-wrap">';
    echo '<label for="sff_recipe_ingredient_picker" class="screen-reader-text">' . esc_html__('Available ingredients', 'simplified-food-fitness') . '</label>';
    echo '<select id="sff_recipe_ingredient_picker" class="sff-recipe-ingredient-picker" size="8">';
    echo '<option value="">' . esc_html__('Select an ingredient…', 'simplified-food-fitness') . '</option>';
    foreach ($ingredients as $ingredient) {
        $serving_size_text = get_post_meta($ingredient->ID, '_sff_serving_size', true);
        $serving_attr      = $serving_size_text ? ' data-serving-size="' . esc_attr($serving_size_text) . '"' : '';
        echo '<option value="' . esc_attr($ingredient->ID) . '"' . $serving_attr . '>' . esc_html($ingredient->post_title) . '</option>';
    }
    echo '</select>';
    echo '<div class="sff-recipe-ingredient-add">';
    echo '<div class="sff-recipe-ingredient-add__amount">';
    echo '<label for="sff_recipe_ingredient_serving_amount">' . esc_html__('Servings to Add', 'simplified-food-fitness') . '</label>';
    echo '<input type="number" min="0.01" step="0.01" id="sff_recipe_ingredient_serving_amount" value="1" class="sff-recipe-ingredient-add__input" />';
    echo '</div>';
    echo '<button type="button" class="button button-primary sff-recipe-add-ingredient">' . esc_html__('Add to Recipe', 'simplified-food-fitness') . '</button>';
    echo '</div>';
    echo '</div>';
    echo '<select id="sff_recipe_ingredients" name="sff_recipe_ingredients[]" multiple class="sff-recipe-ingredient-select sff-recipe-ingredient-select--hidden">';
    foreach ($selected_details as $detail) {
        echo '<option value="' . esc_attr($detail['id']) . '" selected="selected"></option>';
    }
    echo '</select>';

    echo '<div class="sff-recipe-ingredient-list-wrapper">';
    echo '<h4 class="sff-recipe-field-label">' . esc_html__('Recipe Ingredients', 'simplified-food-fitness') . '</h4>';
    echo '<p class="description">' . esc_html__('Adjust the servings for each ingredient below or remove any that you no longer need.', 'simplified-food-fitness') . '</p>';
    $servings_label = esc_attr__('Servings', 'simplified-food-fitness');
    $remove_label   = esc_attr__('Remove', 'simplified-food-fitness');
    echo '<ul id="sff-recipe-ingredient-list" class="sff-recipe-ingredient-list" data-servings-label="' . esc_attr($servings_label) . '" data-remove-label="' . esc_attr($remove_label) . '">';
    foreach ($selected_details as $detail) {
        $input_id = 'sff-recipe-ingredient-' . $detail['id'] . '-servings';
        echo '<li class="sff-recipe-ingredient-item" data-id="' . esc_attr($detail['id']) . '">';
        echo '<div class="sff-recipe-ingredient-item__text">';
        echo '<span class="sff-recipe-ingredient-item__name">' . esc_html($detail['title']) . '</span>';
        if (!empty($detail['servingSize'])) {
            echo '<span class="sff-recipe-ingredient-item__serving-size">' . esc_html($detail['servingSize']) . '</span>';
        }
        echo '</div>';
        echo '<div class="sff-recipe-ingredient-item__actions">';
        echo '<label for="' . esc_attr($input_id) . '">' . esc_html__('Servings', 'simplified-food-fitness') . '</label>';
        echo '<input type="number" min="0.01" step="0.01" id="' . esc_attr($input_id) . '" class="sff-recipe-ingredient-item__quantity" value="' . esc_attr($detail['servings']) . '" />';
        echo '<button type="button" class="button-link-delete sff-recipe-ingredient-remove">' . esc_html__('Remove', 'simplified-food-fitness') . '</button>';
        echo '</div>';
        echo '<input type="hidden" class="sff-recipe-ingredient-item__hidden" name="sff_recipe_ingredient_servings[' . esc_attr($detail['id']) . ']" value="' . esc_attr($detail['servings']) . '" />';
        echo '</li>';
    }
    echo '</ul>';
    $empty_style = empty($selected_details) ? '' : ' style="display:none;"';
    echo '<p id="sff-recipe-ingredient-empty" class="sff-recipe-ingredient-empty"' . $empty_style . '>' . esc_html__('No ingredients have been added yet. Use the library above to build your recipe.', 'simplified-food-fitness') . '</p>';

    $total_cost   = isset($ingredient_macros_total['cost']) ? floatval($ingredient_macros_total['cost']) : 0.0;
    $cost_display = '$' . number_format_i18n($total_cost, 2);
    echo '<div class="sff-recipe-ingredient-summary" id="sff-recipe-ingredient-summary">';
    echo '<div class="sff-recipe-ingredient-summary__item">';
    echo '<span class="sff-recipe-ingredient-summary__label">' . esc_html__('Total ingredients', 'simplified-food-fitness') . '</span>';
    echo '<span id="sff-recipe-ingredient-count" class="sff-recipe-ingredient-summary__value">' . esc_html($selected_count) . '</span>';
    echo '</div>';
    echo '<div class="sff-recipe-ingredient-summary__item">';
    echo '<span class="sff-recipe-ingredient-summary__label">' . esc_html__('Estimated cost', 'simplified-food-fitness') . '</span>';
    echo '<span id="sff-recipe-ingredient-cost" class="sff-recipe-ingredient-summary__value" data-raw="' . esc_attr($total_cost) . '">' . esc_html($cost_display) . '</span>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';

    echo '<div class="sff-recipe-builder__summary">';
    echo '<div class="sff-recipe-servings-card">';
    echo '<label for="sff_recipe_servings" class="sff-recipe-field-label">' . esc_html__('Servings', 'simplified-food-fitness') . '</label>';
    echo '<input type="number" min="1" id="sff_recipe_servings" name="sff_recipe_servings" value="' . esc_attr($servings) . '" class="sff-recipe-servings-input" />';
    echo '<p class="description">' . esc_html__('Every nutrient automatically recalculates when servings change.', 'simplified-food-fitness') . '</p>';
    echo '</div>';

    echo '<div class="sff-recipe-nutrition">';
    echo '<div class="sff-recipe-nutrition-section">';
    echo '<div class="sff-recipe-nutrition-header">';
    echo '<span class="sff-recipe-nutrition-pill">' . esc_html__('Per Serving', 'simplified-food-fitness') . '</span>';
    echo '</div>';
    echo '<div id="sff-recipe-nutrients-per-serving" class="sff-nutrient-grid">' . sff_render_nutrient_cards_html($macros_per_serving) . '</div>';
    echo '</div>';

    echo '<div class="sff-recipe-nutrition-section">';
    echo '<div class="sff-recipe-nutrition-header">';
    echo '<span class="sff-recipe-nutrition-pill sff-recipe-nutrition-pill--total">' . esc_html__('Whole Recipe', 'simplified-food-fitness') . '</span>';
    echo '</div>';
    echo '<div id="sff-recipe-nutrients-total" class="sff-nutrient-grid">' . sff_render_nutrient_cards_html($ingredient_macros_total) . '</div>';
    echo '</div>';
    echo '</div>'; // nutrition
    echo '</div>'; // summary

    echo '<div class="sff-recipe-builder__media">';
    echo '<h3>' . esc_html__('Recipe Media', 'simplified-food-fitness') . '</h3>';

    echo '<div class="sff-recipe-media">';
    echo '<div class="sff-recipe-media__section">';
    echo '<label class="sff-recipe-field-label" for="sff_recipe_cover_id">' . esc_html__('Cover Image', 'simplified-food-fitness') . '</label>';
    echo '<p class="description">' . esc_html__('Pick a hero image that represents the finished recipe.', 'simplified-food-fitness') . '</p>';
    echo '<div id="sff-recipe-cover-preview" class="sff-recipe-media__preview">';
    if ($cover_id) {
        $cover_src = wp_get_attachment_image_src($cover_id, 'medium');
        $cover_alt = get_post_meta($cover_id, '_wp_attachment_image_alt', true);
        $cover_alt = $cover_alt ? $cover_alt : get_the_title($cover_id);
        if ($cover_src) {
            echo '<img src="' . esc_url($cover_src[0]) . '" alt="' . esc_attr($cover_alt) . '" />';
        } else {
            echo '<p class="sff-recipe-media__empty">' . esc_html__('Selected image is unavailable.', 'simplified-food-fitness') . '</p>';
        }
    } else {
        echo '<p class="sff-recipe-media__empty">' . esc_html__('No cover image selected.', 'simplified-food-fitness') . '</p>';
    }
    echo '</div>';
    echo '<input type="hidden" id="sff_recipe_cover_id" name="sff_recipe_cover_id" value="' . ($cover_id ? esc_attr($cover_id) : '') . '" />';
    echo '<div class="sff-recipe-media__actions">';
    echo '<button type="button" class="button sff-recipe-cover-select">' . esc_html__('Choose cover image', 'simplified-food-fitness') . '</button>';
    $cover_remove_disabled = $cover_id ? '' : ' disabled';
    echo '<button type="button" class="button-link-delete sff-recipe-cover-remove"' . $cover_remove_disabled . '>' . esc_html__('Remove', 'simplified-food-fitness') . '</button>';
    echo '</div>';
    echo '</div>'; // cover section

    echo '<div class="sff-recipe-media__section">';
    echo '<label class="sff-recipe-field-label" for="sff_recipe_gallery_ids">' . esc_html__('Gallery Images', 'simplified-food-fitness') . '</label>';
    echo '<p class="description">' . esc_html__('Show different angles, steps, or ingredient close-ups.', 'simplified-food-fitness') . '</p>';
    echo '<div id="sff-recipe-gallery-preview" class="sff-recipe-media__gallery">';
    if (!empty($gallery_ids)) {
        foreach ($gallery_ids as $gallery_id) {
            $thumb_src = wp_get_attachment_image_src($gallery_id, 'thumbnail');
            $thumb_alt = get_post_meta($gallery_id, '_wp_attachment_image_alt', true);
            $thumb_alt = $thumb_alt ? $thumb_alt : get_the_title($gallery_id);
            echo '<div class="sff-recipe-media__gallery-item" data-id="' . esc_attr($gallery_id) . '">';
            if ($thumb_src) {
                echo '<img src="' . esc_url($thumb_src[0]) . '" alt="' . esc_attr($thumb_alt) . '" />';
            } else {
                echo '<span class="sff-recipe-media__gallery-fallback">' . esc_html__('Image', 'simplified-food-fitness') . '</span>';
            }
            echo '<button type="button" class="sff-recipe-gallery-remove" aria-label="' . esc_attr__('Remove image', 'simplified-food-fitness') . '">&times;</button>';
            echo '</div>';
        }
    } else {
        echo '<p class="sff-recipe-media__empty">' . esc_html__('No gallery images selected.', 'simplified-food-fitness') . '</p>';
    }
    echo '</div>';
    echo '<input type="hidden" id="sff_recipe_gallery_ids" name="sff_recipe_gallery_ids" value="' . esc_attr(implode(',', $gallery_ids)) . '" />';
    echo '<div class="sff-recipe-media__actions">';
    echo '<button type="button" class="button sff-recipe-gallery-add">' . esc_html__('Add gallery images', 'simplified-food-fitness') . '</button>';
    $gallery_clear_disabled = empty($gallery_ids) ? ' disabled' : '';
    echo '<button type="button" class="button-link-delete sff-recipe-gallery-clear"' . $gallery_clear_disabled . '>' . esc_html__('Remove all', 'simplified-food-fitness') . '</button>';
    echo '</div>';
    echo '</div>'; // gallery section
    echo '</div>'; // media wrapper
    echo '</div>'; // builder media

    echo '</div>'; // body
    echo '</div>'; // builder wrapper
}

function sff_save_recipe_details($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (!isset($_POST['sff_recipe_details_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['sff_recipe_details_nonce'])), 'sff_save_recipe_details')) {
        return;
    }

    $servings = isset($_POST['sff_recipe_servings']) ? max(1, absint(wp_unslash($_POST['sff_recipe_servings']))) : max(1, intval(get_post_meta($post_id, '_sff_recipe_servings', true)));

    $ingredient_ids = [];
    if (isset($_POST['sff_recipe_ingredients_present'])) {
        $raw = isset($_POST['sff_recipe_ingredients']) ? (array) wp_unslash($_POST['sff_recipe_ingredients']) : [];
        $ingredient_ids = array_values(array_filter(array_map('intval', $raw))); // Remove empties and normalize indexes
    } else {
        $stored = get_post_meta($post_id, '_sff_recipe_ingredients', true);
        $ingredient_ids = is_array($stored) ? array_map('intval', $stored) : [];
    }

    $ingredient_servings = [];
    if (!empty($ingredient_ids) && isset($_POST['sff_recipe_ingredient_servings']) && is_array($_POST['sff_recipe_ingredient_servings'])) {
        foreach ((array) $_POST['sff_recipe_ingredient_servings'] as $id => $amount) {
            $id = intval($id);
            if (!$id || !in_array($id, $ingredient_ids, true)) {
                continue;
            }

            $amount = floatval(str_replace(',', '.', wp_unslash($amount)));
            if ($amount <= 0) {
                continue;
            }

            $ingredient_servings[$id] = $amount;
        }
    }

    if (empty($ingredient_servings) && !empty($ingredient_ids)) {
        foreach ($ingredient_ids as $id) {
            $ingredient_servings[$id] = 1.0;
        }
    }

    update_post_meta($post_id, '_sff_recipe_servings', $servings);
    update_post_meta($post_id, '_sff_recipe_ingredients', $ingredient_ids);

    if (!empty($ingredient_servings)) {
        update_post_meta($post_id, '_sff_recipe_ingredient_servings', $ingredient_servings);
    } else {
        delete_post_meta($post_id, '_sff_recipe_ingredient_servings');
    }

    $total_macros = sff_get_recipe_macros_from_ids($ingredient_ids, $ingredient_servings);
    $per_serving  = [];
    foreach ($total_macros as $key => $value) {
        $per_serving[$key] = $value / $servings;
    }

    if (!empty($ingredient_ids)) {
        update_post_meta($post_id, '_sff_recipe_macros_total', $total_macros);
        update_post_meta($post_id, '_sff_recipe_macros', $per_serving);
        if (array_key_exists('cost', $total_macros)) {
            update_post_meta($post_id, '_sff_recipe_cost', $total_macros['cost']);
        }
    } else {
        delete_post_meta($post_id, '_sff_recipe_macros_total');
        delete_post_meta($post_id, '_sff_recipe_macros');
        delete_post_meta($post_id, '_sff_recipe_ingredient_servings');
        delete_post_meta($post_id, '_sff_recipe_cost');
    }

    $cover_id = isset($_POST['sff_recipe_cover_id']) ? absint(wp_unslash($_POST['sff_recipe_cover_id'])) : 0;
    if ($cover_id) {
        update_post_meta($post_id, '_sff_recipe_cover_id', $cover_id);
    } else {
        delete_post_meta($post_id, '_sff_recipe_cover_id');
    }

    $gallery_ids = [];
    if (isset($_POST['sff_recipe_gallery_ids'])) {
        $raw_gallery = explode(',', sanitize_text_field(wp_unslash($_POST['sff_recipe_gallery_ids'])));
        foreach ($raw_gallery as $gallery_id) {
            $gallery_id = absint($gallery_id);
            if ($gallery_id) {
                $gallery_ids[] = $gallery_id;
            }
        }
        $gallery_ids = array_values(array_unique($gallery_ids));
    }

    if (!empty($gallery_ids)) {
        update_post_meta($post_id, '_sff_recipe_gallery_ids', $gallery_ids);
    } else {
        delete_post_meta($post_id, '_sff_recipe_gallery_ids');
    }
}
add_action('save_post', 'sff_save_recipe_details');



function sff_render_macro_target_meta_box($post) {
    // Retrieve existing macro & micro targets
    $macros = get_post_meta($post->ID, '_macro_targets', true);
    if (empty($macros)) {
        $macros = [
            'calories'        => get_post_meta($post->ID, 'calories', true),
            'carbs'           => get_post_meta($post->ID, 'carbs', true),
            'protein'         => get_post_meta($post->ID, 'protein', true),
            'fats'            => get_post_meta($post->ID, 'fats', true),
            'carb_percent'    => get_post_meta($post->ID, 'carb_percent', true),
            'protein_percent' => get_post_meta($post->ID, 'protein_percent', true),
            'fat_percent'     => get_post_meta($post->ID, 'fat_percent', true),
        ];
    }
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

    $calories       = floatval($_POST['calories']);
    $carb_percent    = isset($_POST['carb_percent']) ? floatval($_POST['carb_percent']) : 0;
    $protein_percent = isset($_POST['protein_percent']) ? floatval($_POST['protein_percent']) : 0;
    $fat_percent     = isset($_POST['fat_percent']) ? floatval($_POST['fat_percent']) : 0;

    $carbs   = $calories * $carb_percent / 400;
    $protein = $calories * $protein_percent / 400;
    $fats    = $calories * $fat_percent / 900;

    $macros = [
        'calories'        => $calories,
        'carb_percent'    => $carb_percent,
        'protein_percent' => $protein_percent,
        'fat_percent'     => $fat_percent,
        'carbs'           => $carbs,
        'protein'         => $protein,
        'fats'            => $fats,
    ];

    $micros = [
        'vitamin_c' => $_POST['vitamin_c'],
        'iron'      => $_POST['iron'],
        'fiber'     => $_POST['fiber'],
    ];

    update_post_meta($post_id, '_macro_targets', $macros);
    update_post_meta($post_id, 'calories', $calories);
    update_post_meta($post_id, 'carbs', $carbs);
    update_post_meta($post_id, 'protein', $protein);
    update_post_meta($post_id, 'fats', $fats);
    update_post_meta($post_id, 'carb_percent', $carb_percent);
    update_post_meta($post_id, 'protein_percent', $protein_percent);
    update_post_meta($post_id, 'fat_percent', $fat_percent);
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
        'macro_target', // CPT slug
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
add_action('save_post_macro_target', function ($post_id) {
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

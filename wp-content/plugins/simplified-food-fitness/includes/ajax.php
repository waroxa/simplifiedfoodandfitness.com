<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function sff_scan_nutrition_label() {
     if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'sff_scan_nonce')) {
        wp_send_json_error('Nonce verification failed.');
        return;
    }

    // 🔥 Fix: Check if file exists correctly
    if (!isset($_FILES['nutrition_label']) || $_FILES['nutrition_label']['error'] !== UPLOAD_ERR_OK) {
        wp_send_json_error('Please upload an image.');
        return;
    }

    require_once(ABSPATH . 'wp-admin/includes/file.php');
    $uploaded_file = wp_handle_upload($_FILES['nutrition_label'], ['test_form' => false]);

    if (isset($uploaded_file['error'])) {
        wp_send_json_error('Upload failed: ' . $uploaded_file['error']);
        return;
    }

    $image_url = $uploaded_file['url'];
    $api_key = get_option('sff_google_api_key', '');

    if (!$api_key) {
        wp_send_json_error('Google API Key is not set.');
        return;
    }

    // Prepare API request
    $request_data = [
        "requests" => [
            [
                "image" => ["source" => ["imageUri" => $image_url]],
                "features" => [["type" => "TEXT_DETECTION"]]
            ]
        ]
    ];

    $url = "https://vision.googleapis.com/v1/images:annotate?key={$api_key}";

    $response = wp_remote_post($url, [
        'body' => json_encode($request_data),
        'headers' => ['Content-Type' => 'application/json'],
        'method' => 'POST'
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error('API request failed: ' . $response->get_error_message());
        return;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!isset($data['responses'][0]['textAnnotations'][0]['description'])) {
        wp_send_json_error('No text detected.');
        return;
    }

    // Extract text from response
    $text = $data['responses'][0]['textAnnotations'][0]['description'];
    error_log('Raw Nutrition Label Text: ' . $text); // Debugging

    // ✅ Modified regex for Servings Per Container to be more flexible
preg_match('/(\d+)\s+servings?\s+(per|for)\s+container/i', $text, $servings);
// ✅ Modified regex for Serving Size to handle line breaks and spacing
preg_match('/Serving\s*size[^\d]*([^\n(]+?)\s*\((\d+\s*[a-z]{1,2})\)/i', $text, $serving_size);
    

    // Extract nutritional data
    preg_match('/Calories\s*[:\-]?\s*(\d+)/i', $text, $calories);
    preg_match('/Total\s+Fat\s+(\d+)/i', $text, $fat);
    preg_match('/Saturated\s+Fat\s+(\d+)/i', $text, $saturated_fat);
    preg_match('/Trans\s+Fat\s+(\d+)/i', $text, $trans_fat);
    preg_match('/Cholesterol\s+(\d+)/i', $text, $cholesterol);
    preg_match('/Sodium\s+(\d+)/i', $text, $sodium);
    preg_match('/Total\s+Carbohydrate\s+(\d+)/i', $text, $carbohydrates);
    preg_match('/Dietary\s+Fiber\s+(\d+)/i', $text, $fiber);
    preg_match('/Total\s+Sugars?\s+(\d+)/i', $text, $sugars);
    preg_match('/Includes?\s*(\d+)\s*g\s+Added\s+Sugars?/i', $text, $added_sugars);
    preg_match('/Protein\s+(\d+)/i', $text, $protein);

    // Vitamins and minerals
    preg_match('/Vitamin\s+D\s*(\d+)/i', $text, $vitamin_d);
    preg_match('/Calcium\s+(\d+)/i', $text, $calcium);
    preg_match('/Iron\s+(\d+\.\d+)/i', $text, $iron);
    preg_match('/Potassium\s+(\d+)/i', $text, $potassium);
    preg_match('/Magnesium\s+(\d+)/i', $text, $magnesium);

    $response_data = [
        'serving_size' => isset($serving_size[1], $serving_size[2]) ? 
                      trim($serving_size[1]) . ' (' . trim($serving_size[2]) . ')' : '',
    'servings' => isset($servings[1]) ? (int)$servings[1] : 0,
        'calories' => isset($calories[1]) ? (int)$calories[1] : 0,
        'fat' => isset($fat[1]) ? (int)$fat[1] : 0,
        'saturated_fat' => isset($saturated_fat[1]) ? (int)$saturated_fat[1] : 0,
        'trans_fat' => isset($trans_fat[1]) ? (int)$trans_fat[1] : 0,
        'cholesterol' => isset($cholesterol[1]) ? (int)$cholesterol[1] : 0,
        'sodium' => isset($sodium[1]) ? (int)$sodium[1] : 0,
        'carbohydrates' => isset($carbohydrates[1]) ? (int)$carbohydrates[1] : 0,
        'fiber' => isset($fiber[1]) ? (int)$fiber[1] : 0,
        'sugars' => isset($sugars[1]) ? (int)$sugars[1] : 0,
        'added_sugars' => isset($added_sugars[1]) ? (int)$added_sugars[1] : 0,
        'protein' => isset($protein[1]) ? (int)$protein[1] : 0,
        'vitamin_d' => isset($vitamin_d[1]) ? (int)$vitamin_d[1] : 0,
        'calcium' => isset($calcium[1]) ? (int)$calcium[1] : 0,
        'iron' => isset($iron[1]) ? (float)$iron[1] : 0,
        'potassium' => isset($potassium[1]) ? (int)$potassium[1] : 0,
        'magnesium' => isset($magnesium[1]) ? (int)$magnesium[1] : 0
    ];

    // Debugging
    error_log('Extracted Nutrition Data: ' . print_r($response_data, true));

    wp_send_json_success($response_data);
}
add_action('wp_ajax_sff_scan_nutrition_label', 'sff_scan_nutrition_label');

function sff_scan_product_name() {
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'sff_scan_nonce')) {
        wp_send_json_error('Nonce verification failed.');
        return;
    }

    if (!isset($_FILES['front_image'])) {
        wp_send_json_error('No image uploaded.');
        return;
    }

    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');

    // Temporarily handle file to scan
    $temp_file = wp_handle_upload($_FILES['front_image'], ['test_form' => false]);

    if (isset($temp_file['error'])) {
        wp_send_json_error('Upload failed: ' . $temp_file['error']);
        return;
    }

    $image_url = $temp_file['url'];
    $api_key = get_option('sff_google_api_key', '');

    if (!$api_key) {
        wp_send_json_error('Google API Key is not set.');
        return;
    }

    // Google API request preparation
    $request_data = [
        "requests" => [
            [
                "image" => ["source" => ["imageUri" => $image_url]],
                "features" => [["type" => "TEXT_DETECTION"]]
            ]
        ]
    ];

    $response = wp_remote_post(
        "https://vision.googleapis.com/v1/images:annotate?key={$api_key}",
        [
            'body' => json_encode($request_data),
            'headers' => ['Content-Type' => 'application/json']
        ]
    );

    if (is_wp_error($response)) {
        wp_send_json_error('API request failed: ' . $response->get_error_message());
        return;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!isset($data['responses'][0]['textAnnotations'][0]['description'])) {
        wp_send_json_error('No text detected.');
        return;
    }

    // Extract product name
    $text = $data['responses'][0]['textAnnotations'][0]['description'];

    preg_match('/([A-Z][a-z]+(?:\s[A-Z][a-z]+)*)/', $text, $matches);
    $product_name = isset($matches[0]) ? trim($matches[0]) : 'Unknown Product';

    error_log("🔍 Extracted product name: $product_name");

    // Check if product already exists
    $existing = get_posts([
        'post_type' => 'ingredient',
        'title' => $product_name,
        'post_status' => 'publish',
        'fields' => 'ids',
        'posts_per_page' => 1
    ]);

    if (!empty($existing)) {
        // Product exists
        error_log("⚠️ Product '$product_name' already exists. ID: " . $existing[0]);
        wp_send_json_success([
            'product_name' => $product_name,
            'exists' => true,
            'existing_id' => $existing[0],
        ]);
        return;
    }

    // Product does NOT exist, save the image permanently
    $attachment_id = media_handle_sideload([
        'name' => basename($temp_file['file']),
        'tmp_name' => $temp_file['file'],
    ], 0);

    if (is_wp_error($attachment_id)) {
        wp_send_json_error('Image save failed: ' . $attachment_id->get_error_message());
        return;
    }

    error_log("🟢 Image saved with attachment ID: $attachment_id");

    wp_send_json_success([
        'product_name' => $product_name,
        'exists' => false,
        'attachment_id' => $attachment_id,
        'image_url' => wp_get_attachment_url($attachment_id)
    ]);
}

add_action('wp_ajax_sff_scan_product_name', 'sff_scan_product_name');



function sff_handle_ingredient_submission() {
    // Verify nonce first
    if (!isset($_POST['sff_nonce_field']) || !wp_verify_nonce($_POST['sff_nonce_field'], 'sff_ingredient_nonce')) {
        wp_die('Security check failed');
    }

    // Process form data
    $data = [
        'brand_name' => sanitize_text_field($_POST['sff_brand_name']),
        'serving_size' => sanitize_text_field($_POST['sff_serving_size']),
        'servings' => absint($_POST['sff_servings']),
        'macros' => [
            'calories' => isset($_POST['sff_macros']['calories']) ? (float)$_POST['sff_macros']['calories'] : 0,
            'carbs' => isset($_POST['sff_macros']['carbs']) ? (float)$_POST['sff_macros']['carbs'] : 0,
            'protein' => isset($_POST['sff_macros']['protein']) ? (float)$_POST['sff_macros']['protein'] : 0,
            'fat' => isset($_POST['sff_macros']['fat']) ? (float)$_POST['sff_macros']['fat'] : 0,
            'saturated_fat' => isset($_POST['sff_macros']['saturated_fat']) ? (float)$_POST['sff_macros']['saturated_fat'] : 0,
            'trans_fat' => isset($_POST['sff_macros']['trans_fat']) ? (float)$_POST['sff_macros']['trans_fat'] : 0,
            'cholesterol' => isset($_POST['sff_macros']['cholesterol']) ? (float)$_POST['sff_macros']['cholesterol'] : 0,
            'sodium' => isset($_POST['sff_macros']['sodium']) ? (float)$_POST['sff_macros']['sodium'] : 0,
            'fiber' => isset($_POST['sff_macros']['fiber']) ? (float)$_POST['sff_macros']['fiber'] : 0,
            'sugars' => isset($_POST['sff_macros']['sugars']) ? (float)$_POST['sff_macros']['sugars'] : 0,
            'added_sugars' => isset($_POST['sff_macros']['added_sugars']) ? (float)$_POST['sff_macros']['added_sugars'] : 0,
            'vitamin_d' => isset($_POST['sff_macros']['vitamin_d']) ? (float)$_POST['sff_macros']['vitamin_d'] : 0,
            'calcium' => isset($_POST['sff_macros']['calcium']) ? (float)$_POST['sff_macros']['calcium'] : 0,
            'iron' => isset($_POST['sff_macros']['iron']) ? (float)$_POST['sff_macros']['iron'] : 0,
            'potassium' => isset($_POST['sff_macros']['potassium']) ? (float)$_POST['sff_macros']['potassium'] : 0,
            'magnesium' => isset($_POST['sff_macros']['magnesium']) ? (float)$_POST['sff_macros']['magnesium'] : 0,
            'vitamin_a' => isset($_POST['sff_macros']['vitamin_a']) ? (float)$_POST['sff_macros']['vitamin_a'] : 0,
            'vitamin_c' => isset($_POST['sff_macros']['vitamin_c']) ? (float)$_POST['sff_macros']['vitamin_c'] : 0,
            'vitamin_e' => isset($_POST['sff_macros']['vitamin_e']) ? (float)$_POST['sff_macros']['vitamin_e'] : 0,
            'zinc' => isset($_POST['sff_macros']['zinc']) ? (float)$_POST['sff_macros']['zinc'] : 0,
            'folate' => isset($_POST['sff_macros']['folate']) ? (float)$_POST['sff_macros']['folate'] : 0,
            'riboflavin' => isset($_POST['sff_macros']['riboflavin']) ? (float)$_POST['sff_macros']['riboflavin'] : 0,
            'niacin' => isset($_POST['sff_macros']['niacin']) ? (float)$_POST['sff_macros']['niacin'] : 0,
            'vitamin_b6' => isset($_POST['sff_macros']['vitamin_b6']) ? (float)$_POST['sff_macros']['vitamin_b6'] : 0,
            'vitamin_b12' => isset($_POST['sff_macros']['vitamin_b12']) ? (float)$_POST['sff_macros']['vitamin_b12'] : 0,
            'thiamin' => isset($_POST['sff_macros']['thiamin']) ? (float)$_POST['sff_macros']['thiamin'] : 0,
        ]
    ];

    // Debugging: Log the data being saved
    error_log('Submitted Data: ' . print_r($data, true));

    // Create/update ingredient post
    $post_id = wp_insert_post([
        'post_type' => 'ingredient',
        'post_title' => $data['brand_name'] ?: 'Unbranded Ingredient',
        'post_status' => 'publish'
    ]);

    if (is_wp_error($post_id)) {
        wp_die('Failed to create ingredient post.');
    }

    // Save meta data
    update_post_meta($post_id, '_sff_brand_name', $data['brand_name']);
    update_post_meta($post_id, '_sff_serving_size', $data['serving_size']);
    update_post_meta($post_id, '_sff_servings', $data['servings']);
    update_post_meta($post_id, '_sff_macros', $data['macros']);

    // Handle image uploads properly
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');

if (isset($_POST['front_image_attachment_id'])) {
    $front_image_id = intval($_POST['front_image_attachment_id']);
    update_post_meta($post_id, '_sff_front_image', wp_get_attachment_url($front_image_id));
    wp_update_post(['ID' => $front_image_id, 'post_parent' => $post_id]);
}

if (isset($_POST['nutrition_label_image_id'])) {
    $nutrition_label_id = intval($_POST['nutrition_label_image_id']);
    update_post_meta($post_id, '_sff_nutrition_label_image', wp_get_attachment_url($nutrition_label_id));
    wp_update_post(['ID' => $nutrition_label_id, 'post_parent' => $post_id]);
}

   // If request is AJAX, return JSON response
    if (wp_doing_ajax()) {
        wp_send_json_success([
            'message' => 'Ingredient added successfully!',
            'post_id' => $post_id
        ]);
    }

    // Otherwise, redirect for normal form submissions
    wp_redirect(add_query_arg(['ingredient_saved' => 'true'], $_SERVER['HTTP_REFERER']));
    exit;

}


add_action('admin_post_sff_save_ingredient', 'sff_handle_ingredient_submission');
add_action('admin_post_nopriv_sff_save_ingredient', 'sff_handle_ingredient_submission');

add_action('wp_ajax_sff_replace_ingredient', 'sff_replace_ingredient');

function sff_replace_ingredient() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'You must be logged in.']);
    }

    $user_id = get_current_user_id();
    $meal_id = intval($_POST['meal_id']);
    $old_ingredient = sanitize_text_field($_POST['old_ingredient']);
    $new_ingredient = sanitize_text_field($_POST['new_ingredient']);

    $ingredients = get_post_meta($meal_id, '_ingredients', true);

    if (isset($ingredients[$old_ingredient])) {
        $ingredients[$new_ingredient] = $ingredients[$old_ingredient]; // Swap values
        unset($ingredients[$old_ingredient]);
        update_post_meta($meal_id, '_ingredients', $ingredients);
        wp_send_json_success(['message' => 'Ingredient swapped successfully!']);
    } else {
        wp_send_json_error(['message' => 'Ingredient not found.']);
    }
}

add_action('wp_ajax_sff_save_client_intake', 'sff_save_client_intake');
function sff_save_client_intake() {
    if (!isset($_POST['form_data'])) {
        wp_send_json_error(['message' => 'No form data received.']);
    }

    parse_str($_POST['form_data'], $form_data);

    if (empty($form_data['first_name']) || empty($form_data['email'])) {
        wp_send_json_error(['message' => 'Missing required fields.']);
    }

    // Create a new client_lead post
    $post_id = wp_insert_post([
        'post_type'   => 'client_leads',
        'post_title'  => sanitize_text_field($form_data['first_name'] . ' ' . $form_data['last_name']),
        'post_status' => 'publish',
    ]);

    if (is_wp_error($post_id)) {
        wp_send_json_error(['message' => 'Failed to save intake data.']);
    }

    // Meta fields to save to the post
    $fields_to_save = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'dob',
        'gender',
        'cbw',
        'cbw_unit',
        'lbs',
        'dbw',
        'dbw_unit',
        'height',           // Added
        'height_unit',      // Added
        'bpmh',
        'medications',
        'medication_allergies',
        'food_allergies',
        'food_intolerances',
        'goal',
        'goal_other',
        'current_activity_days',
        'current_activity_minutes',
        'current_activity_type',
        'current_activity_type_other',
        'has_trainer',
        'trainer_name',
        'trainer_contact',
        'goal_activity_days',
        'goal_activity_minutes',
        'goal_activity_type',
        'goal_activity_type_other',
        'smart_watch',
        'smart_watch_other',
        'cooking_frequency',
        'meals_per_day',
        'snacks',
        'favorite_snacks',
        'coffee',
        'coffee_frequency',
        'diet_preference',
        'diet_preference_other',
        'favorite_meals',
        'favorite_fruits',
        'disliked_fruits',
        'favorite_vegetables',
        'disliked_vegetables',
        'leftovers',
        'leftovers_other',
        'repeating_meals',
        'grocery_store',
        'grocery_store_other',
        'grocery_delivery',
        'grocery_delivery_service',
        'organic_preference',
        'email_consent',
        'how_found',
        'how_found_other',
    ];

    foreach ($fields_to_save as $field) {
        if (isset($form_data[$field])) {
            update_post_meta($post_id, 'sff_' . $field, sanitize_text_field($form_data[$field]));
        }
    }

    wp_send_json_success(['message' => 'Client intake saved successfully!']);
}



add_action('wp_ajax_nopriv_sff_save_client_intake', 'sff_save_client_intake');

add_action('wp_ajax_calculate_macros', 'sff_calculate_macros');
function sff_calculate_macros() {
    if (!isset($_POST['lead_id']) || !is_numeric($_POST['lead_id'])) {
        wp_send_json_error(['message' => 'Invalid lead ID.']);
    }

    $lead_id = intval($_POST['lead_id']);

    // Retrieve meta
    $gender = strtolower(get_post_meta($lead_id, 'sff_gender', true));
    $weight = floatval(get_post_meta($lead_id, 'sff_cbw', true));
    $weight_unit = strtolower(get_post_meta($lead_id, 'sff_cbw_unit', true));
    $weight_kg = ($weight_unit === 'lbs') ? $weight * 0.453592 : $weight;

    $height = floatval(get_post_meta($lead_id, 'sff_height', true));
    $height_unit = strtolower(get_post_meta($lead_id, 'sff_height_unit', true));
    $height_cm = ($height_unit === 'inches') ? $height * 2.54 : $height;
    $height_m = $height_cm / 100;

    $dob_raw = get_post_meta($lead_id, 'sff_dob', true);
    $goal = strtolower(get_post_meta($lead_id, 'sff_goal', true));
    $activity_freq = strtolower(get_post_meta($lead_id, 'sff_current_activity_frequency', true));

    // Calculate age from DOB
    $age = 0;
    if ($dob_raw) {
        try {
            $dob = new DateTime($dob_raw);
            $now = new DateTime();
            if ($dob > $now) {
                wp_send_json_error([
                    'message' => 'DOB is in the future.',
                    'dob_raw' => $dob_raw
                ]);
            }
            $age = $now->diff($dob)->y;
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => 'Invalid DOB format.',
                'dob_raw' => $dob_raw
            ]);
        }
    } else {
        wp_send_json_error([
            'message' => 'DOB is missing.',
            'dob_raw' => 'Not found'
        ]);
    }

    if (!$age) {
        wp_send_json_error([
            'message' => 'Age is missing or invalid.',
            'dob_raw' => $dob_raw ?: 'Not found',
            'parsed_age' => $age
        ]);
    }

    if (!$weight_kg) {
        wp_send_json_error(['message' => 'Weight is missing or invalid.']);
    }

    if (!$height_cm) {
        wp_send_json_error(['message' => 'Height is missing or invalid.']);
    }

    if (!$gender) {
        wp_send_json_error(['message' => 'Gender is missing.']);
    }

    // Calculate BMR
    $bmr = ($gender === 'male')
        ? (10 * $weight_kg) + (6.25 * $height_cm) - (5 * $age) + 5
        : (10 * $weight_kg) + (6.25 * $height_cm) - (5 * $age) - 161;

    // Calculate BMI
    $bmi = $weight_kg / pow($height_m, 2);

    // Activity Factor
    $activity_factors = [
        'little to no exercise' => 1.2,
        '1-3 days a week' => 1.375,
        '3-5 days a week' => 1.55,
        '6-7 days a week' => 1.725,
        '2 times per day' => 1.9,
    ];

    $activity_factor = $activity_factors[$activity_freq] ?? 1.2;

    // TDEE
    $tdee = $bmr * $activity_factor;

    // Adjust calories
    $adjusted_calories = $tdee;
    if (strpos($goal, 'fat') !== false) {
        $adjusted_calories -= 250;
    } elseif (strpos($goal, 'muscle') !== false || strpos($goal, 'gain') !== false) {
        $adjusted_calories += 250;
    }

    // Macronutrients
    $protein_g = round($weight_kg * 1.7);
    $protein_cals = $protein_g * 4;

    $remaining_cals = $adjusted_calories - $protein_cals;
    $fat_cals = $remaining_cals * 0.33;
    $carb_cals = $remaining_cals - $fat_cals;

    $fat_g = round($fat_cals / 9);
    $carb_g = round($carb_cals / 4);

    // Response
    wp_send_json_success([
        'bmr' => round($bmr),
        'bmi' => round($bmi, 1),
        'tdee' => round($tdee),
        'adjusted_calories' => round($adjusted_calories),
        'activity_factor' => $activity_factor,
        'current_activity_frequency' => ucfirst($activity_freq),
        'weight_kg' => round($weight_kg, 2),
        'height_cm' => round($height_cm, 2),
        'age' => $age,
        'gender' => ucfirst($gender),
        'goal' => ucfirst($goal),
        'protein_g' => $protein_g,
        'carb_g' => $carb_g,
        'fat_g' => $fat_g,
        'message' => 'Macros calculated successfully.'
    ]);
}

add_action('wp_ajax_nopriv_calculate_macros', 'sff_calculate_macros');

add_action('wp_ajax_sff_convert_to_client', 'sff_convert_to_client');


function sff_convert_to_client() {
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'sff_scan_nonce')) {
        wp_send_json_error(['message' => 'Security check failed.']);
    }

    if (!current_user_can('edit_posts')) {
        wp_send_json_error(['message' => 'Not authorized.']);
    }

    $lead_id = intval($_POST['lead_id'] ?? 0);
    if (!$lead_id) {
        wp_send_json_error(['message' => 'Invalid lead ID.']);
    }

    $first = get_post_meta($lead_id, 'sff_first_name', true);
    $last  = get_post_meta($lead_id, 'sff_last_name', true);
    $email = get_post_meta($lead_id, 'sff_email', true);

    if (!$email || !$first || !$last) {
        wp_send_json_error(['message' => 'Missing required fields.']);
    }

    if (email_exists($email)) {
        wp_send_json_error(['message' => 'This email already has an account.']);
    }

    // ✅ Create new WordPress user
    $password = wp_generate_password();
    $user_id = wp_create_user($email, $password, $email);

    if (is_wp_error($user_id)) {
        wp_send_json_error(['message' => 'Error creating WordPress user.']);
    }

    wp_update_user([
        'ID'           => $user_id,
        'first_name'   => $first,
        'last_name'    => $last,
        'display_name' => "$first $last"
    ]);

    $user = new WP_User($user_id);
    $user->set_role('subscriber'); // Adjust role if needed

    // ✅ Convert the post type to 'clients'
    $result = wp_update_post([
        'ID' => $lead_id,
        'post_type' => 'clients',
        'post_status' => 'publish'
    ], true);

    if (is_wp_error($result)) {
        wp_send_json_error(['message' => $result->get_error_message()]);
    }

    // ✅ Copy meta data from Lead
    $lead_meta = get_post_meta($lead_id);
    foreach ($lead_meta as $key => $value) {
        if ($key[0] !== '_') {
            update_post_meta($lead_id, $key, maybe_unserialize($value[0]));
        }
    }

    update_post_meta($lead_id, 'converted_to_client', 'yes');
    update_post_meta($lead_id, 'linked_user_id', $user_id);

    // ✅ CREATE MACRO TARGET POST for this new Client
    $existing_post = get_posts(array(
        'post_type'      => 'macro_target',
        'author'         => $user_id,
        'post_status'    => 'publish',
        'posts_per_page' => 1,
    ));

    if (!$existing_post) {
        $post_id = wp_insert_post(array(
            'post_title'    => "$first $last – Macro Targets",
            'post_type'     => 'macro_target', // Correct CPT
            'post_status'   => 'publish',
            'post_author'   => $user_id,
        ));

        if (!is_wp_error($post_id)) {
            // Save default macro data
            update_post_meta($post_id, 'calories', 2000); // Default Calories
            update_post_meta($post_id, 'carb_percent', 50); // Default Carbs %
            update_post_meta($post_id, 'protein_percent', 30); // Default Protein %
            update_post_meta($post_id, 'fat_percent', 20); // Default Fats %
        }
    }

    // ✅ Email user credentials
    $login_url = wp_login_url(site_url('/client-dashboard/'));
    wp_new_user_notification($user_id, null, 'user');

    wp_send_json_success([
        'message' => 'Lead converted and user account created.',
        'user_id' => $user_id,
        'post_id' => $lead_id
    ]);
}

// Load client profile via AJAX
add_action('wp_ajax_sff_load_profile', 'sff_load_profile');
add_action('wp_ajax_nopriv_sff_load_profile', 'sff_load_profile');

function sff_load_profile() {
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'sff_scan_nonce')) {
        wp_die('Security check failed', 403);
    }

    if (!is_user_logged_in()) {
        wp_die('Unauthorized', 403);
    }

    echo do_shortcode('[sff_client_profile]');
    wp_die();
}

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

    $current_user_id = get_current_user_id();

    $existing_posts = get_posts([
        'post_type'      => 'ingredient',
        'title'          => $product_name,
        'post_status'    => 'publish',
        'fields'         => 'ids',
        'posts_per_page' => -1,
    ]);

    $duplicates           = [];
    $personal_duplicates  = [];

    if (!empty($existing_posts)) {
        foreach ($existing_posts as $existing_id) {
            if (!sff_user_can_access_ingredient($existing_id, $current_user_id)) {
                continue;
            }

            $owner_id   = sff_get_ingredient_owner_id($existing_id);
            $owner_type = 'restricted';
            $owner_label = __('Dietitian Ingredient', 'simplified-food-fitness');

            if ($owner_id === 0) {
                $owner_type  = 'general';
                $owner_label = __('Shared Ingredient', 'simplified-food-fitness');
            } elseif ($owner_id === intval($current_user_id)) {
                $owner_type  = 'personal';
                $owner_label = __('My Ingredient', 'simplified-food-fitness');
                $personal_duplicates[] = $existing_id;
            }

            $duplicates[] = [
                'id'         => $existing_id,
                'title'      => get_the_title($existing_id),
                'owner_type' => $owner_type,
                'owner_label'=> $owner_label,
                'can_edit'   => current_user_can('edit_post', $existing_id),
                'edit_url'   => current_user_can('edit_post', $existing_id) ? get_edit_post_link($existing_id, '') : '',
                'view_url'   => get_permalink($existing_id),
            ];
        }
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

    $response = [
        'product_name'    => $product_name,
        'exists'          => false,
        'attachment_id'   => $attachment_id,
        'image_url'       => wp_get_attachment_url($attachment_id),
        'duplicates'      => $duplicates,
        'has_duplicates'  => !empty($duplicates),
        'can_create_new'  => true,
    ];

    if (!empty($personal_duplicates)) {
        $response['personal_duplicate_ids'] = $personal_duplicates;
    }

    if (!empty($duplicates)) {
        $response['notice'] = __('We found ingredients with this name. You can edit an existing record or continue to create a new one.', 'simplified-food-fitness');
    }

    wp_send_json_success($response);
}

add_action('wp_ajax_sff_scan_product_name', 'sff_scan_product_name');

function sff_search_user_ingredients() {
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'sff_scan_nonce')) {
        wp_send_json_error(['message' => 'Nonce verification failed.']);
    }

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'You must be logged in to search ingredients.']);
    }

    $user_id = get_current_user_id();
    $term = isset($_POST['query']) ? sanitize_text_field(wp_unslash($_POST['query'])) : '';
    $term = trim($term);
    $scope = isset($_POST['scope']) ? sanitize_text_field(wp_unslash($_POST['scope'])) : 'all';
    $allow_usda = isset($_POST['allow_usda']) && '1' === wp_unslash($_POST['allow_usda']);

    if (strlen($term) < 2) {
        wp_send_json_success([
            'items' => [],
            'query' => $term,
            'scope' => $scope,
        ]);
    }

    if ($allow_usda && user_can($user_id, 'manage_options')) {
        $items = [];
        $usda_items = sff_fetch_usda_search_items($term);

        if (is_wp_error($usda_items)) {
            wp_send_json_error(['message' => $usda_items->get_error_message()]);
        }

        foreach ($usda_items as $food) {
            $meta = [];
            if (!empty($food['dataType'])) {
                $meta[] = $food['dataType'];
            }
            if (!empty($food['foodCategory'])) {
                $meta[] = $food['foodCategory'];
            }

            $items[] = [
                'source'             => 'usda',
                'fdc_id'             => $food['fdc_id'] ?? '',
                'description'        => $food['description'] ?? '',
                'owner_badge'        => 'USDA',
                'owner_badge_class'  => 'usda',
                'meta_text'          => implode(' • ', array_filter($meta)),
            ];
        }

        wp_send_json_success([
            'items' => $items,
            'query' => $term,
            'scope' => $scope,
        ]);
    }

    $args = [
        'post_type' => 'ingredient',
        'post_status' => 'publish',
        'posts_per_page' => 12,
        's' => $term,
        'orderby' => 'title',
        'order' => 'ASC',
        'fields' => 'ids',
    ];

    if (!user_can($user_id, 'manage_options')) {
        if ($scope === 'personal') {
            $args['meta_query'] = [
                [
                    'key' => '_sff_owner_id',
                    'value' => $user_id,
                    'compare' => '=',
                    'type' => 'NUMERIC',
                ],
            ];
        } elseif ($scope === 'general') {
            $args['meta_query'] = [
                'relation' => 'OR',
                [
                    'key' => '_sff_owner_id',
                    'value' => 0,
                    'compare' => '=',
                    'type' => 'NUMERIC',
                ],
                [
                    'key' => '_sff_owner_id',
                    'compare' => 'NOT EXISTS',
                ],
            ];
        } else {
            $args['meta_query'] = [
                'relation' => 'OR',
                [
                    'key' => '_sff_owner_id',
                    'value' => 0,
                    'compare' => '=',
                    'type' => 'NUMERIC',
                ],
                [
                    'key' => '_sff_owner_id',
                    'value' => $user_id,
                    'compare' => '=',
                    'type' => 'NUMERIC',
                ],
                [
                    'key' => '_sff_owner_id',
                    'compare' => 'NOT EXISTS',
                ],
            ];
        }
    } else {
        if ($scope === 'personal') {
            $args['author'] = $user_id;
        }
    }

    $query = new WP_Query($args);

    $items = [];
    if ($query->have_posts()) {
        foreach ($query->posts as $ingredient_id) {
            if (!sff_user_can_access_ingredient($ingredient_id, $user_id)) {
                continue;
            }
            if ($scope === 'personal' && sff_is_general_ingredient($ingredient_id)) {
                continue;
            }
            if ($scope === 'general' && !sff_is_general_ingredient($ingredient_id)) {
                continue;
            }

            $payload = sff_prepare_ingredient_payload($ingredient_id, $user_id);
            if ($payload) {
                $items[] = $payload;
            }
        }
    }

    wp_send_json_success([
        'items' => $items,
        'query' => $term,
        'scope' => $scope,
    ]);
}
add_action('wp_ajax_sff_search_user_ingredients', 'sff_search_user_ingredients');

function sff_fetch_usda_search_items($query, $category = '') {
    if (!$query || !defined('SFF_USDA_API_KEY') || !SFF_USDA_API_KEY) {
        return new WP_Error('missing_query', __('Missing USDA search query or API key.', 'simplified-food-fitness'));
    }

    $url = 'https://api.nal.usda.gov/fdc/v1/foods/search?api_key=' . urlencode(SFF_USDA_API_KEY) . '&query=' . urlencode($query) . '&pageSize=50';

    if ($category) {
        $url .= '&foodCategory=' . urlencode($category);
    }

    $resp = wp_remote_get($url);

    if (is_wp_error($resp)) {
        return new WP_Error('usda_request_failed', __('Unable to reach the USDA service.', 'simplified-food-fitness'));
    }

    $body = json_decode(wp_remote_retrieve_body($resp), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return new WP_Error('usda_invalid_response', __('Unexpected USDA response.', 'simplified-food-fitness'));
    }

    if (empty($body['foods']) || !is_array($body['foods'])) {
        return [];
    }

    $grouped = [];

    foreach ($body['foods'] as $food) {
        $description = $food['description'] ?? '';
        $normalized = strtoupper(trim($description));
        $timestamp = null;

        foreach (['modifiedDate', 'publicationDate', 'publishedDate'] as $field) {
            if (!empty($food[$field])) {
                $time = strtotime($food[$field]);
                if ($time !== false) {
                    $timestamp = $time;
                    break;
                }
            }
        }

        if (!isset($grouped[$normalized])) {
            $grouped[$normalized] = [
                'food'      => $food,
                'timestamp' => $timestamp,
            ];
            continue;
        }

        $existing_timestamp = $grouped[$normalized]['timestamp'];
        $should_replace = false;

        if ($timestamp && (!$existing_timestamp || $timestamp > $existing_timestamp)) {
            $should_replace = true;
        }

        if ($should_replace) {
            $grouped[$normalized] = [
                'food'      => $food,
                'timestamp' => $timestamp,
            ];
        }
    }

    $items = [];

    foreach ($grouped as $data) {
        $food = $data['food'];
        $items[] = [
            'fdc_id'       => $food['fdcId'] ?? '',
            'description'  => $food['description'] ?? '',
            'dataType'     => $food['dataType'] ?? '',
            'foodCategory' => $food['foodCategory'] ?? '',
        ];
    }

    return $items;
}

function sff_get_ingredient_details() {
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'sff_scan_nonce')) {
        wp_send_json_error(['message' => 'Nonce verification failed.']);
    }

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'You must be logged in to load ingredient details.']);
    }

    $ingredient_id = isset($_POST['ingredient_id']) ? intval($_POST['ingredient_id']) : 0;
    if (!$ingredient_id) {
        wp_send_json_error(['message' => 'Invalid ingredient.']);
    }

    $user_id = get_current_user_id();
    if (!sff_user_can_access_ingredient($ingredient_id, $user_id)) {
        wp_send_json_error(['message' => 'You do not have access to this ingredient.']);
    }

    $payload = sff_prepare_ingredient_payload($ingredient_id, $user_id);
    if (!$payload) {
        wp_send_json_error(['message' => 'Unable to load ingredient data.']);
    }

    wp_send_json_success($payload);
}
add_action('wp_ajax_sff_get_ingredient_details', 'sff_get_ingredient_details');



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
        'fdc_id' => sanitize_text_field($_POST['sff_fdc_id']),
        'sku' => sanitize_text_field($_POST['sff_sku']),
        'affiliate_link' => esc_url_raw($_POST['sff_affiliate_link']),
        'price' => isset($_POST['sff_price']) ? (float)$_POST['sff_price'] : 0,
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

    $source_ingredient = isset($_POST['sff_source_ingredient']) ? intval($_POST['sff_source_ingredient']) : 0;
    $selected_owner    = isset($_POST['sff_selected_owner']) ? sanitize_text_field($_POST['sff_selected_owner']) : '';

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

    sff_assign_ingredient_owner($post_id, null, true);

    // Save meta data
    update_post_meta($post_id, '_sff_brand_name', $data['brand_name']);
    update_post_meta($post_id, '_sff_serving_size', $data['serving_size']);
    update_post_meta($post_id, '_sff_servings', $data['servings']);
    update_post_meta($post_id, '_sff_macros', $data['macros']);
    update_post_meta($post_id, '_sff_fdc_id', $data['fdc_id']);
    update_post_meta($post_id, '_sff_sku', $data['sku']);
    update_post_meta($post_id, '_sff_affiliate_link', $data['affiliate_link']);
    update_post_meta($post_id, '_sff_price', $data['price']);
    if ($source_ingredient) {
        update_post_meta($post_id, '_sff_source_ingredient', $source_ingredient);
    }
    if (!empty($selected_owner)) {
        update_post_meta($post_id, '_sff_selected_owner', $selected_owner);
    }

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

    // Store macros in custom table for quick aggregation
    global $wpdb;
    $table = $wpdb->prefix . 'sff_ingredient_nutrition';
    $row = array_merge(['ingredient_id' => $post_id], $data['macros'], ['cost' => $data['price']]);
    $formats = array_merge(['%d'], array_fill(0, count(SFF_MACRO_FIELDS), '%f'), ['%f']);
    $wpdb->replace($table, $row, $formats);

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

    // Meta fields with sanitization callbacks
    $field_map = [
        'first_name'                  => 'sanitize_text_field',
        'last_name'                   => 'sanitize_text_field',
        'email'                       => 'sanitize_email',
        'phone'                       => 'sanitize_text_field',
        'dob'                         => 'sanitize_text_field',
        'gender'                      => 'sanitize_text_field',
        'gender_other'                => 'sanitize_text_field',
        'cbw'                         => 'sanitize_text_field',
        'cbw_unit'                    => 'sanitize_text_field',
        'lbs'                         => 'sanitize_text_field',
        'dbw'                         => 'sanitize_text_field',
        'dbw_unit'                    => 'sanitize_text_field',
        'height'                      => 'sanitize_text_field',
        'height_unit'                 => 'sanitize_text_field',
        'bpmh'                        => 'sanitize_textarea_field',
        'past_medical_conditions'     => 'array',
        'past_medical_conditions_other' => 'sanitize_text_field',
        'medications'                 => 'sanitize_textarea_field',
        'medication_allergies'        => 'sanitize_textarea_field',
        'food_allergies'              => 'array',
        'food_allergies_other'        => 'sanitize_text_field',
        'food_intolerances'           => 'array',
        'food_intolerances_other'     => 'sanitize_text_field',
        'goal'                        => 'sanitize_text_field',
        'goal_other'                  => 'sanitize_text_field',
        'current_activity_days'       => 'sanitize_text_field',
        'current_activity_minutes'    => 'sanitize_text_field',
        'current_activity_type'       => 'sanitize_text_field',
        'current_activity_type_other' => 'sanitize_text_field',
        'has_trainer'                 => 'sanitize_text_field',
        'trainer_name'                => 'sanitize_text_field',
        'trainer_contact'             => 'sanitize_text_field',
        'goal_activity_days'          => 'sanitize_text_field',
        'goal_activity_minutes'       => 'sanitize_text_field',
        'goal_activity_type'          => 'sanitize_text_field',
        'goal_activity_type_other'    => 'sanitize_text_field',
        'smart_watch'                 => 'sanitize_text_field',
        'smart_watch_other'           => 'sanitize_text_field',
        'cooking_frequency'           => 'sanitize_text_field',
        'meals_per_day'               => 'sanitize_text_field',
        'meals_per_day_other'         => 'sanitize_text_field',
        'snacks'                      => 'sanitize_text_field',
        'favorite_snacks'             => 'sanitize_textarea_field',
        'coffee'                      => 'sanitize_text_field',
        'coffee_how'                  => 'sanitize_text_field',
        'coffee_frequency'            => 'sanitize_text_field',
        'coffee_per_day'              => 'sanitize_text_field',
        'diet_preference'             => 'sanitize_text_field',
        'diet_preference_other'       => 'sanitize_text_field',
        'favorite_meals'              => 'sanitize_textarea_field',
        'favorite_fruits'             => 'array',
        'favorite_fruits_other'       => 'sanitize_text_field',
        'disliked_fruits'             => 'array',
        'disliked_fruits_other'       => 'sanitize_text_field',
        'favorite_vegetables'         => 'array',
        'favorite_vegetables_other'   => 'sanitize_text_field',
        'disliked_vegetables'         => 'array',
        'disliked_vegetables_other'   => 'sanitize_text_field',
        'favorite_red_meat'           => 'array',
        'favorite_red_meat_other'     => 'sanitize_text_field',
        'disliked_red_meat'           => 'array',
        'disliked_red_meat_other'     => 'sanitize_text_field',
        'favorite_poultry'            => 'array',
        'favorite_poultry_other'      => 'sanitize_text_field',
        'disliked_poultry'            => 'array',
        'disliked_poultry_other'      => 'sanitize_text_field',
        'favorite_pork'               => 'array',
        'favorite_pork_other'         => 'sanitize_text_field',
        'disliked_pork'               => 'array',
        'disliked_pork_other'         => 'sanitize_text_field',
        'favorite_fish'               => 'array',
        'favorite_fish_other'         => 'sanitize_text_field',
        'disliked_fish'               => 'array',
        'disliked_fish_other'         => 'sanitize_text_field',
        'favorite_seafood'            => 'array',
        'favorite_seafood_other'      => 'sanitize_text_field',
        'disliked_seafood'            => 'array',
        'disliked_seafood_other'      => 'sanitize_text_field',
        'leftovers'                   => 'sanitize_text_field',
        'leftovers_other'             => 'sanitize_text_field',
        'repeating_meals'             => 'sanitize_text_field',
        'grocery_store'               => 'array',
        'grocery_store_other'         => 'sanitize_text_field',
        'grocery_delivery'            => 'sanitize_text_field',
        'grocery_delivery_service'    => 'array',
        'grocery_delivery_service_other' => 'sanitize_text_field',
        'organic_preference'          => 'sanitize_text_field',
        'email_consent'               => 'sanitize_text_field',
        'how_found'                   => 'sanitize_text_field',
        'how_found_other'             => 'sanitize_text_field',
    ];

    foreach ($field_map as $field => $sanitize) {
        if (!isset($form_data[$field])) {
            continue;
        }

        $value = $form_data[$field];

        if ($sanitize === 'array') {
            $value = implode(', ', array_map('sanitize_text_field', (array) $value));
        } elseif (is_callable($sanitize)) {
            $value = call_user_func($sanitize, $value);
        }

        update_post_meta($post_id, 'sff_' . $field, $value);
    }

    wp_send_json_success(['message' => 'Client intake saved successfully!']);
}

function sff_usda_search() {
    check_ajax_referer('sff_scan_nonce', 'security');
    $query = sanitize_text_field($_POST['query'] ?? '');
    $category = sanitize_text_field($_POST['category'] ?? '');
    $results = sff_fetch_usda_search_items($query, $category);

    if (is_wp_error($results)) {
        wp_send_json_error($results->get_error_message());
    }

    wp_send_json_success(array_values($results));
}
add_action('wp_ajax_sff_usda_search', 'sff_usda_search');

function sff_usda_macros() {
    check_ajax_referer('sff_scan_nonce', 'security');
    $fdc_id = intval($_POST['fdc_id'] ?? 0);
    if (!$fdc_id) {
        wp_send_json_error('Missing FDC ID');
    }
    $raw_food = null;
    $macros = sff_fetch_usda_macros($fdc_id, $raw_food);
    $macros = $macros ? array_intersect_key($macros, array_flip(SFF_MACRO_FIELDS)) : [];

    if (empty($raw_food)) {
        wp_send_json_error(['message' => 'Unable to retrieve USDA data for the selected item.']);
    }

    $response = [
        'macros' => $macros,
        'raw'    => $raw_food,
    ];

    if (empty($macros)) {
        $response['notice'] = 'No macro values were returned by USDA for this item. Please review the raw response below.';
    }

    wp_send_json_success($response);
}
add_action('wp_ajax_sff_usda_macros', 'sff_usda_macros');

function sff_ajax_update_recipe_swaps() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => __('You need to be logged in to personalize meals.', 'simplified-food-fitness')], 403);
    }

    $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
    if (!$nonce || !wp_verify_nonce($nonce, 'sff_meal_plan_preview')) {
        wp_send_json_error(['message' => __('Invalid request. Please refresh and try again.', 'simplified-food-fitness')], 400);
    }

    $recipe_id = isset($_POST['recipe_id']) ? intval($_POST['recipe_id']) : 0;
    if (!$recipe_id) {
        wp_send_json_error(['message' => __('Missing recipe information.', 'simplified-food-fitness')], 400);
    }

    $user_id = get_current_user_id();
    if (current_user_can('manage_options')) {
        $requested_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
        if ($requested_id) {
            $user_id = $requested_id;
        }
    }

    if (!$user_id || !get_user_by('id', $user_id)) {
        wp_send_json_error(['message' => __('Unable to locate the selected client.', 'simplified-food-fitness')], 400);
    }

    $assigned_ids = sff_get_user_assigned_recipe_ids($user_id);
    if (!in_array($recipe_id, $assigned_ids, true)) {
        wp_send_json_error(['message' => __('This recipe is not part of your plan.', 'simplified-food-fitness')], 403);
    }

    if (!empty($_POST['reset'])) {
        sff_clear_user_recipe_customization($user_id, $recipe_id);
    } else {
        $raw_swaps = [];
        if (isset($_POST['swaps'])) {
            $raw_swaps = wp_unslash($_POST['swaps']);
        } elseif (isset($_POST['sff_recipe_swap'])) {
            $raw_swaps = wp_unslash($_POST['sff_recipe_swap']);
        }

        if (is_string($raw_swaps)) {
            $decoded = json_decode($raw_swaps, true);
            $raw_swaps = is_array($decoded) ? $decoded : [];
        }

        $normalized = [];
        foreach ((array) $raw_swaps as $original_id => $replacement_value) {
            $original_id = intval($original_id);
            $replacement_id = intval($replacement_value);
            if (!$original_id || !$replacement_id) {
                continue;
            }

            if (!sff_user_can_access_ingredient($replacement_id, $user_id)) {
                continue;
            }

            $normalized[$original_id] = $replacement_id;
        }

        $customizations = sff_get_user_recipe_customizations($user_id);
        if (!empty($normalized)) {
            $customizations[$recipe_id] = ['ingredients' => $normalized];
        } else {
            unset($customizations[$recipe_id]);
        }

        sff_save_user_recipe_customizations($user_id, $customizations);
    }

    $override         = sff_get_user_recipe_customization($user_id, $recipe_id);
    $ingredient_rows  = sff_get_recipe_ingredient_details_with_overrides($recipe_id, $override);
    $macros           = sff_get_recipe_macros_with_overrides($recipe_id, $override, false);
    $preferences      = sff_get_client_preferences_for_user($user_id);
    $ingredients_html = sff_render_preview_ingredient_list($ingredient_rows, $preferences);

    $response = [
        'ingredients_html' => $ingredients_html,
        'macros'           => [
            'calories' => isset($macros['calories']) ? floatval($macros['calories']) : 0.0,
            'protein'  => isset($macros['protein']) ? floatval($macros['protein']) : 0.0,
            'carbs'    => isset($macros['carbs']) ? floatval($macros['carbs']) : 0.0,
            'fat'      => isset($macros['fat']) ? floatval($macros['fat']) : 0.0,
        ],
        'swaps'            => isset($override['ingredients']) ? array_map('intval', (array) $override['ingredients']) : [],
        'has_swaps'        => !empty($override['ingredients']),
    ];

    wp_send_json_success($response);
}
add_action('wp_ajax_sff_update_recipe_swaps', 'sff_ajax_update_recipe_swaps');

function sff_recalc_recipe_nutrition() {
    check_ajax_referer('sff_scan_nonce', 'security');

    $ids = isset($_POST['ingredient_ids']) ? array_map('intval', (array) $_POST['ingredient_ids']) : [];
    $servings = max(1, intval($_POST['servings'] ?? 1));

    $raw_quantities = $_POST['ingredient_quantities'] ?? [];
    if (is_string($raw_quantities)) {
        $decoded = json_decode(wp_unslash($raw_quantities), true);
        $raw_quantities = is_array($decoded) ? $decoded : [];
    } elseif (!is_array($raw_quantities)) {
        $raw_quantities = [];
    }

    $quantities = [];
    foreach ($raw_quantities as $id => $amount) {
        $id = intval($id);
        if (!$id) {
            continue;
        }

        $amount = floatval(str_replace(',', '.', wp_unslash($amount)));
        if ($amount <= 0) {
            continue;
        }

        $quantities[$id] = $amount;
    }

    if (empty($ids) && !empty($quantities)) {
        $ids = array_map('intval', array_keys($quantities));
    }

    $totals     = sff_get_recipe_macros_from_ids($ids, $quantities);
    $per_serving = [];
    foreach ($totals as $key => $value) {
        $per_serving[$key] = $value / $servings;
    }

    wp_send_json_success([
        'total'       => $totals,
        'per_serving' => $per_serving,
        'summary'     => [
            'ingredient_count' => count($quantities),
            'total_cost'       => $totals['cost'] ?? 0,
        ],
    ]);
}
add_action('wp_ajax_sff_recalc_recipe_nutrition', 'sff_recalc_recipe_nutrition');



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

    // Calculate macro percentages
    if ($adjusted_calories > 0) {
        $protein_percent = ($protein_g * 4 / $adjusted_calories) * 100;
        $carb_percent    = ($carb_g * 4 / $adjusted_calories) * 100;
        $fat_percent     = ($fat_g * 9 / $adjusted_calories) * 100;
    } else {
        $protein_percent = $carb_percent = $fat_percent = 0;
    }

    // Store calculated macros for the lead
    update_post_meta($lead_id, 'sff_macro_calories', round($adjusted_calories));
    update_post_meta($lead_id, 'sff_macro_protein_g', $protein_g);
    update_post_meta($lead_id, 'sff_macro_carb_g', $carb_g);
    update_post_meta($lead_id, 'sff_macro_fat_g', $fat_g);
    update_post_meta($lead_id, 'sff_macro_protein_percent', round($protein_percent, 2));
    update_post_meta($lead_id, 'sff_macro_carb_percent', round($carb_percent, 2));
    update_post_meta($lead_id, 'sff_macro_fat_percent', round($fat_percent, 2));
    update_post_meta($lead_id, '_sff_macro_targets', [
        'calories' => round($adjusted_calories),
        'protein'  => $protein_g,
        'carbs'    => $carb_g,
        'fat'      => $fat_g,
    ]);
    update_post_meta($lead_id, '_sff_macro_percentages', [
        'protein' => round($protein_percent, 2),
        'carbs'   => round($carb_percent, 2),
        'fat'     => round($fat_percent, 2),
    ]);

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
        'percentages' => [
            'protein' => round($protein_percent, 2),
            'carbs'   => round($carb_percent, 2),
            'fat'     => round($fat_percent, 2),
        ],
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

    $macro_post_id = $existing_post ? $existing_post[0]->ID : 0;

    $calories        = intval(get_post_meta($lead_id, 'sff_macro_calories', true));
    $protein_percent = floatval(get_post_meta($lead_id, 'sff_macro_protein_percent', true));
    $carb_percent    = floatval(get_post_meta($lead_id, 'sff_macro_carb_percent', true));
    $fat_percent     = floatval(get_post_meta($lead_id, 'sff_macro_fat_percent', true));
    $protein_g       = floatval(get_post_meta($lead_id, 'sff_macro_protein_g', true));
    $carb_g          = floatval(get_post_meta($lead_id, 'sff_macro_carb_g', true));
    $fat_g           = floatval(get_post_meta($lead_id, 'sff_macro_fat_g', true));

    if ($calories <= 0 && ($protein_g || $carb_g || $fat_g)) {
        $calories = ($protein_g * 4) + ($carb_g * 4) + ($fat_g * 9);
    }

    if ($calories <= 0) {
        $calories = 2000;
    }

    if (!$protein_percent && $calories > 0 && $protein_g) {
        $protein_percent = $protein_g * 4 / $calories * 100;
    }
    if (!$carb_percent && $calories > 0 && $carb_g) {
        $carb_percent = $carb_g * 4 / $calories * 100;
    }
    if (!$fat_percent && $calories > 0 && $fat_g) {
        $fat_percent = $fat_g * 9 / $calories * 100;
    }

    if (!$carb_percent) {
        $carb_percent = 50;
    }
    if (!$protein_percent) {
        $protein_percent = 30;
    }
    if (!$fat_percent) {
        $fat_percent = 20;
    }

    $calculated_macros = sff_calculate_macro_targets_from_percentages($calories, [
        'carb_percent'    => $carb_percent,
        'protein_percent' => $protein_percent,
        'fat_percent'     => $fat_percent,
    ]);

    if ($protein_g) {
        $calculated_macros['protein'] = $protein_g;
    }
    if ($carb_g) {
        $calculated_macros['carbs'] = $carb_g;
    }
    if ($fat_g) {
        $calculated_macros['fat'] = $fat_g;
    }

    if (!$macro_post_id) {
        $macro_post_id = wp_insert_post([
            'post_title'  => "$first $last – Macro Targets",
            'post_type'   => 'macro_target',
            'post_status' => 'publish',
            'post_author' => $user_id,
        ]);
    }

    if ($macro_post_id && !is_wp_error($macro_post_id)) {
        update_post_meta($macro_post_id, 'calories', round($calculated_macros['calories']));
        update_post_meta($macro_post_id, 'carb_percent', round($carb_percent));
        update_post_meta($macro_post_id, 'protein_percent', round($protein_percent));
        update_post_meta($macro_post_id, 'fat_percent', round($fat_percent));
        update_post_meta($macro_post_id, 'carbs', round($calculated_macros['carbs'], 1));
        update_post_meta($macro_post_id, 'protein', round($calculated_macros['protein'], 1));
        update_post_meta($macro_post_id, 'fats', round($calculated_macros['fat'], 1));
        update_post_meta($macro_post_id, '_macro_targets', [
            'calories'        => round($calculated_macros['calories']),
            'carb_percent'    => round($carb_percent, 2),
            'protein_percent' => round($protein_percent, 2),
            'fat_percent'     => round($fat_percent, 2),
            'carbs'           => round($calculated_macros['carbs'], 1),
            'protein'         => round($calculated_macros['protein'], 1),
            'fats'            => round($calculated_macros['fat'], 1),
        ]);
    }

    $user_macro_targets = [
        'calories' => round($calculated_macros['calories']),
        'carbs'    => round($calculated_macros['carbs'], 1),
        'protein'  => round($calculated_macros['protein'], 1),
        'fat'      => round($calculated_macros['fat'], 1),
    ];
    $user_macro_percentages = [
        'carbs'   => round($carb_percent, 2),
        'protein' => round($protein_percent, 2),
        'fat'     => round($fat_percent, 2),
    ];

    update_user_meta($user_id, '_sff_macro_targets', $user_macro_targets);
    update_user_meta($user_id, '_sff_macro_percentages', $user_macro_percentages);
    update_post_meta($lead_id, '_sff_macro_targets', $user_macro_targets);
    update_post_meta($lead_id, '_sff_macro_percentages', $user_macro_percentages);

    // ✅ Email user credentials
    $login_url = wp_login_url(site_url('/dashboard/'));
    wp_new_user_notification($user_id, null, 'user');

    wp_send_json_success([
        'message' => 'Lead converted and user account created.',
        'user_id' => $user_id,
        'post_id' => $lead_id
    ]);
}

// Save meal completion progress
function sff_update_meal_progress() {
    check_ajax_referer('sff_dashboard_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error('not_logged_in');
    }

    $meal_id  = isset($_POST['meal_id']) ? (int) $_POST['meal_id'] : 0;
    $complete = isset($_POST['completed']) && '1' === $_POST['completed'];

    $user_id = get_current_user_id();
    $progress = get_user_meta($user_id, 'sff_meal_progress', true);
    if (!is_array($progress)) {
        $progress = [];
    }

    if ($complete) {
        if (!in_array($meal_id, $progress)) {
            $progress[] = $meal_id;
        }
    } else {
        $progress = array_diff($progress, [$meal_id]);
    }

    update_user_meta($user_id, 'sff_meal_progress', $progress);

    wp_send_json_success(['progress' => $progress]);
}
add_action('wp_ajax_sff_update_meal_progress', 'sff_update_meal_progress');

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

function sff_search_ingredients() {
    if (!isset($_GET['security']) || !wp_verify_nonce($_GET['security'], 'sff_scan_nonce')) {
        wp_send_json_error('Nonce verification failed.');
    }

    $term = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';
    $tax_query = [];
    if (!empty($_GET['category'])) {
        $tax_query[] = [
            'taxonomy' => 'ingredient_category',
            'field' => 'term_id',
            'terms' => intval($_GET['category']),
        ];
    }
    $args = [
        'post_type' => 'ingredient',
        'posts_per_page' => 10,
        's' => $term,
    ];
    if ($tax_query) {
        $args['tax_query'] = $tax_query;
    }
    $query = new WP_Query($args);

    $results = [];
    foreach ($query->posts as $post) {
        $macros = get_post_meta($post->ID, '_sff_macros', true);
        $unit_cost = get_post_meta($post->ID, '_sff_unit_cost', true);

        $macro_values = [];
        foreach (SFF_MACRO_FIELDS as $field) {
            $macro_values[$field] = floatval(is_array($macros) && isset($macros[$field]) ? $macros[$field] : 0);
        }

        $results[] = [
            'id' => $post->ID,
            'name' => $post->post_title,
            'macros' => $macro_values,
            'unit_cost' => floatval($unit_cost),
        ];
    }

    wp_send_json_success($results);
}
add_action('wp_ajax_sff_search_ingredients', 'sff_search_ingredients');

function sff_create_recipe() {
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'sff_scan_nonce')) {
        wp_send_json_error('Nonce verification failed.');
    }

    $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
    $ingredients = isset($_POST['ingredients']) ? array_map('intval', (array) $_POST['ingredients']) : [];
    if (empty($name) || empty($ingredients)) {
        wp_send_json_error('Missing data.');
    }

    if (!function_exists('sff_create_recipe_from_modal')) {
        require_once SFF_PLUGIN_DIR . 'includes/helpers.php';
    }

    $recipe_id = sff_create_recipe_from_modal($name, $ingredients);
    if (is_wp_error($recipe_id)) {
        wp_send_json_error($recipe_id->get_error_message());
    }

    wp_send_json_success([
        'recipe_id' => $recipe_id,
        'title'     => get_the_title($recipe_id),
    ]);
}
add_action('wp_ajax_sff_create_recipe', 'sff_create_recipe');

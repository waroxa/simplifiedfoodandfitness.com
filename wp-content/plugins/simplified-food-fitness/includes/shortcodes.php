<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function sff_display_user_dashboard() {
   if (!is_user_logged_in()) {
        return '<p>Please log in to view your meal plan.</p>';
    }

    $user_id = get_current_user_id();
    $meal_plan = get_user_meta($user_id, 'meal_plan', true);
    $macro_target = get_user_meta($user_id, 'macro_target', true);

    error_log('Meal Plan Data: ' . print_r($meal_plan, true)); // Log meal plan
    error_log('Macro Target Data: ' . print_r($macro_target, true)); // Log macro target

    $output = '<div class="sff-dashboard">';
    $output .= '<h2>Your Macro Targets</h2>';
    $output .= '<p>' . esc_html($macro_target) . '</p>';

    $output .= '<h2>Your Meal Plan</h2>';
    $output .= '<p>' . esc_html($meal_plan) . '</p>';

    $output .= '<h2>Grocery List</h2>';

    // ✅ Check if meal_plan is valid before calling function
    if (!empty($meal_plan) && is_string($meal_plan)) {
        $output .= sff_generate_grocery_list($meal_plan);
    } else {
        $output .= '<p>No meal plan found.</p>';
    }

    // ✅ Check if shortcode function exists before calling it
    if (shortcode_exists('sff_nutrition_upload')) {
        $output .= do_shortcode('[sff_nutrition_upload]');
    } else {
        error_log('Shortcode [sff_nutrition_upload] does not exist!');
    }

    $output .= '</div>';

    return $output;
}
add_shortcode('sff_dashboard', 'sff_display_user_dashboard');


function sff_frontend_ingredient_page() {
    if (!is_user_logged_in()) return sff_custom_login_form();
    return sff_render_ingredient_form();
}
add_shortcode('sff_add_ingredient', 'sff_frontend_ingredient_page');

function sff_client_profile_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>Please log in to view your profile.</p>';
    }

    $user_id = get_current_user_id();
    $client_posts = get_posts([
        'post_type'      => 'clients',
        'meta_key'       => 'linked_user_id',
        'meta_value'     => $user_id,
        'posts_per_page' => 1,
    ]);

    if (!$client_posts) {
        return '<p>No profile found.</p>';
    }

    $client_id = $client_posts[0]->ID;

    $user      = wp_get_current_user();
    $username  = $user->display_name;
    $day_type  = 'Rest Day';
    $logo_url  = 'https://simplifiedfoodandfitness.com/wp-content/uploads/2024/10/3.png';

    // Ordered sections of intake-form fields
    $sections = [
        'Personal Info' => [
            'sff_first_name' => 'First Name',
            'sff_last_name'  => 'Last Name',
            'sff_email'      => 'Email',
            'sff_phone'      => 'Phone',
            'sff_dob'        => 'Date of Birth',
            'sff_gender'     => 'Gender',
            'sff_cbw'        => 'Current Body Weight',
            'sff_cbw_unit'   => 'Weight Unit',
            'sff_dbw'        => 'Desired Body Weight',
            'sff_dbw_unit'   => 'Desired Weight Unit',
            'sff_height'     => 'Height',
            'sff_height_unit'=> 'Height Unit',
        ],
        'Health' => [
            'sff_bpmh'                => 'BPMH',
            'sff_medications'         => 'Medications',
            'sff_medication_allergies'=> 'Medication Allergies',
            'sff_food_allergies'      => 'Food Allergies',
            'sff_food_intolerances'   => 'Food Intolerances',
        ],
        'Lifestyle & Goals' => [
            'sff_goal'                    => 'Goal',
            'sff_goal_other'              => 'Goal (Other)',
            'sff_current_activity_days'   => 'Current Activity Days',
            'sff_current_activity_minutes'=> 'Current Activity Minutes',
            'sff_current_activity_type'   => 'Current Activity Type',
            'sff_current_activity_type_other' => 'Current Activity Type (Other)',
            'sff_has_trainer'             => 'Has Trainer',
            'sff_trainer_name'            => 'Trainer Name',
            'sff_trainer_contact'         => 'Trainer Contact',
            'sff_goal_activity_days'      => 'Goal Activity Days',
            'sff_goal_activity_minutes'   => 'Goal Activity Minutes',
            'sff_goal_activity_type'      => 'Goal Activity Type',
            'sff_goal_activity_type_other'=> 'Goal Activity Type (Other)',
            'sff_smart_watch'             => 'Smart Watch',
            'sff_smart_watch_other'       => 'Smart Watch (Other)',
        ],
        'Preferences' => [
            'sff_cooking_frequency'        => 'Cooking Frequency',
            'sff_meals_per_day'            => 'Meals Per Day',
            'sff_snacks'                   => 'Snacks',
            'sff_favorite_snacks'          => 'Favorite Snacks',
            'sff_coffee'                   => 'Coffee',
            'sff_coffee_frequency'         => 'Coffee Frequency',
            'sff_diet_preference'          => 'Diet Preference',
            'sff_diet_preference_other'    => 'Diet Preference (Other)',
            'sff_favorite_meals'           => 'Favorite Meals',
            'sff_favorite_fruits'          => 'Favorite Fruits',
            'sff_disliked_fruits'          => 'Disliked Fruits',
            'sff_favorite_vegetables'      => 'Favorite Vegetables',
            'sff_disliked_vegetables'      => 'Disliked Vegetables',
            'sff_leftovers'                => 'Leftovers',
            'sff_leftovers_other'          => 'Leftovers (Other)',
            'sff_repeating_meals'          => 'Repeating Meals',
            'sff_grocery_store'            => 'Grocery Store',
            'sff_grocery_store_other'      => 'Grocery Store (Other)',
            'sff_grocery_delivery'         => 'Grocery Delivery',
            'sff_grocery_delivery_service' => 'Grocery Delivery Service',
            'sff_organic_preference'       => 'Organic Preference',
            'sff_email_consent'            => 'Email Consent',
            'sff_how_found'                => 'How Found',
            'sff_how_found_other'          => 'How Found (Other)',
        ],
    ];

    ob_start(); ?>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="dashboard-logo">
                <img src="<?php echo esc_url($logo_url); ?>" alt="Logo">
            </div>
            <div class="dashboard-greeting">
                <div>
                    <h1>Hello, <?php echo esc_html($username); ?> <span class="sff-emoji">👋</span></h1>
                    <p><?php echo esc_html($day_type); ?></p>
                </div>
            </div>
            <div class="sff-hamburger-wrapper" style="position:relative;">
                <button id="sff-menu-toggle" class="sff-hamburger">&#9776;</button>
                <div id="sff-menu" class="sff-menu-items">
                    <a href="<?php echo esc_url( home_url( '/my-profile/' ) ); ?>" id="sff-profile-link">Profile</a>
                </div>
            </div>
        </div>

        <div class="sff-profile-card">
            <h2><?php echo esc_html(get_the_title($client_id)); ?></h2>
            <?php foreach ($sections as $section => $fields) : ?>
                <h3><?php echo esc_html($section); ?></h3>
                <?php foreach ($fields as $meta_key => $label) :
                    $value = get_post_meta($client_id, $meta_key, true);
                    if (empty($value)) { continue; }
                ?>
                    <div class="sff-profile-field">
                        <label><?php echo esc_html($label); ?>:</label>
                        <span><?php echo esc_html($value); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('sff_client_profile', 'sff_client_profile_shortcode');



function sff_frontend_dashboard_pretty() {
    if (!is_user_logged_in()) {
        return sff_custom_login_form(); // Show styled login form
    }

    $user = wp_get_current_user();
    $username = $user->display_name;
    $client_id = get_current_user_id();
    $day_type = "Rest Day"; // You can dynamically set this if needed
    $logo_url = "https://simplifiedfoodandfitness.com/wp-content/uploads/2024/10/3.png";

    // 🔥 Fetch this client's Macro Target post
    $args = array(
        'post_type'      => 'macro_targets',
        'author'         => $client_id,
        'post_status'    => 'publish',
        'posts_per_page' => 1,
    );
    $macro_post = get_posts($args);
    $macro_post_id = $macro_post ? $macro_post[0]->ID : null;

    // 🔥 Fetch saved macro percentages or use defaults
    $carb_percent = $macro_post_id ? get_post_meta($macro_post_id, 'carb_percent', true) : '';
    $protein_percent = $macro_post_id ? get_post_meta($macro_post_id, 'protein_percent', true) : '';
    $fat_percent = $macro_post_id ? get_post_meta($macro_post_id, 'fat_percent', true) : '';

    if (!$carb_percent) {
        $carb_percent = 40; // Default from ajax.php
    }
    if (!$protein_percent) {
        $protein_percent = 20; // Default
    }
    if (!$fat_percent) {
        $fat_percent = 20; // Default
    }

    // 🔥 Fetch total calories or fallback to 2000
    $total_calories = $macro_post_id ? get_post_meta($macro_post_id, 'calories', true) : 2000;
    if (!$total_calories) {
        $total_calories = 2000; // fallback default
    }

    // 🔥 Calculate grams for macros
    $carbs_goal_g = ($carb_percent / 100) * $total_calories / 4; // 4 cal/g carbs
    $protein_goal_g = ($protein_percent / 100) * $total_calories / 4; // 4 cal/g protein
    $fat_goal_g = ($fat_percent / 100) * $total_calories / 9; // 9 cal/g fat

    // 🔥 Example: current intake (replace these with dynamic values if you track)
    $carbs_current_g = 135;
    $protein_current_g = 78;
    $fat_current_g = 22;

    // 🔥 Calculate percentages for progress bars
    $carbs_progress = min(100, ($carbs_current_g / $carbs_goal_g) * 100);
    $protein_progress = min(100, ($protein_current_g / $protein_goal_g) * 100);
    $fat_progress = min(100, ($fat_current_g / $fat_goal_g) * 100);

    ob_start(); ?>
    
    <div class="dashboard-container" style="max-width:1200px; margin:auto; padding:20px; font-family:'Segoe UI', Arial, sans-serif;">
        <!-- Header Section -->
        <div style="display:flex; align-items:center; justify-content:space-between; gap:15px; flex-wrap:wrap; text-align:left; margin-bottom:30px;">
            <!-- Left Logo -->
            <div style="flex-shrink:0;">
                <img src="<?php echo esc_url($logo_url); ?>" alt="Logo" style="height:70px; width:auto; max-width:200px;">
            </div>

            <!-- Greeting and Rest Day Container -->
            <div style="display:flex; flex-direction:column; flex:1; min-width:200px;">
                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                    <h1 style="font-size:24px; color:#333; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        Hello, <?php echo esc_html( $username ); ?> <span class="sff-emoji">👋</span>
                    </h1>
                    <p style="font-size:16px; color:#777; margin:0;">
                        <?php echo esc_html($day_type); ?>
                    </p>
                </div>
            </div>

            <!-- Hamburger Menu -->
            <div class="sff-hamburger-wrapper" style="position:relative;">
                <button id="sff-menu-toggle" class="sff-hamburger">&#9776;</button>
                <nav id="sff-menu" class="sff-menu-items" aria-label="Mobile Menu">
                    <ul>
                        <li><a href="<?php echo esc_url( home_url( '/dashboard/' ) ); ?>">Dashboard</a></li>
                        <li><a href="<?php echo esc_url( home_url( '/my-profile/' ) ); ?>" id="sff-profile-link">Profile</a></li>
                        <li><a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>

        <!-- Weekly Progress Card -->
        <div style="background:#fff; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.1); padding:20px; margin-bottom:30px;">
            <h2 style="font-size:20px; color:#333; margin-bottom:15px;">Weekly Progress</h2>
            <div style="display:flex; gap:20px; align-items:center;">
                <div style="flex:1;">
                    <p style="font-size:14px; color:#777; margin-bottom:5px;">Completion Rate</p>
                    <div style="width:100%; height:10px; background:#f1f1f1; border-radius:5px; overflow:hidden;">
                        <div style="width:87%; height:10px; background:#42b14c; border-radius:5px;"></div>
                    </div>
                </div>
                <p style="font-size:18px; color:#023441; font-weight:bold;">87%</p>
            </div>
            <p style="font-size:14px; color:#777; margin-top:10px;">5-Day Streak <span class="sff-emoji">🔥</span></p>
        </div>

        <!-- Nutrition Progress Section -->
        <div style="background:#fff; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.1); padding:20px; margin-bottom:30px;">
            <h2 style="font-size:20px; color:#333; margin-bottom:15px;">Nutrition Progress</h2>
            
            <div style="display:flex; flex-direction:column; gap:15px;">
                <!-- Carbs -->
                <div style="display:flex; flex-direction:column;">
                    <div style="display:flex; justify-content:space-between;">
                        <p style="font-size:14px; color:#777;"><span class="sff-emoji">🍞</span> Carbs (<?php echo $carb_percent; ?>%)</p>
                        <p style="font-size:14px; color:#333; font-weight:bold;">
                            <?php echo intval($carbs_current_g); ?>g / <?php echo intval($carbs_goal_g); ?>g
                        </p>
                    </div>
                    <div style="width:100%; height:8px; background:#e0e0e0; border-radius:5px; overflow:hidden;">
                        <div style="width:<?php echo intval($carbs_progress); ?>%; height:8px; background:#42b14c; border-radius:5px;"></div>
                    </div>
                </div>

                <!-- Protein -->
                <div style="display:flex; flex-direction:column;">
                    <div style="display:flex; justify-content:space-between;">
                        <p style="font-size:14px; color:#777;"><span class="sff-emoji">🥩</span> Protein (<?php echo $protein_percent; ?>%)</p>
                        <p style="font-size:14px; color:#333; font-weight:bold;">
                            <?php echo intval($protein_current_g); ?>g / <?php echo intval($protein_goal_g); ?>g
                        </p>
                    </div>
                    <div style="width:100%; height:8px; background:#e0e0e0; border-radius:5px; overflow:hidden;">
                        <div style="width:<?php echo intval($protein_progress); ?>%; height:8px; background:#42b14c; border-radius:5px;"></div>
                    </div>
                </div>

                <!-- Fats -->
                <div style="display:flex; flex-direction:column;">
                    <div style="display:flex; justify-content:space-between;">
                        <p style="font-size:14px; color:#777;"><span class="sff-emoji">🥑</span> Fats (<?php echo $fat_percent; ?>%)</p>
                        <p style="font-size:14px; color:#333; font-weight:bold;">
                            <?php echo intval($fat_current_g); ?>g / <?php echo intval($fat_goal_g); ?>g
                        </p>
                    </div>
                    <div style="width:100%; height:8px; background:#e0e0e0; border-radius:5px; overflow:hidden;">
                        <div style="width:<?php echo intval($fat_progress); ?>%; height:8px; background:#42b14c; border-radius:5px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Meals Section -->
        <div style="background:#fff; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.1); padding:20px; margin-bottom:30px;">
            <h2 style="font-size:20px; color:#333; margin-bottom:15px;">Today's Meals</h2>
            <p style="font-size:14px; color:#777; margin-bottom:10px;">1/5 Completed</p>
            <div style="display:flex; flex-direction:column; gap:10px;">
                <div style="display:flex; justify-content:space-between; align-items:center; padding:10px; background:#f9f9f9; border-radius:8px;">
                    <p style="font-size:14px; color:#333;">Morning Smoothie</p>
                    <p style="font-size:12px; color:#777;">08:30 AM</p>
                </div>
                <button style="background:#E9FAB0; color:#023441; border:none; padding:10px; border-radius:8px; cursor:pointer; text-align:center;">
                    Add Meal +
                </button>
            </div>
        </div>
    </div>

    <?php
    return ob_get_clean();
}

add_shortcode('sff_dashboard', 'sff_frontend_dashboard_pretty');



function sff_frontend_macro_micro_targets() {
    if (!is_user_logged_in()) {
        return '<p style="text-align:center; font-size:18px; color:#777;">Please log in to view your targets.</p>';
    }

    $user_id = get_current_user_id();
    $macro_post = get_posts([
        'post_type'      => 'macro_target',
        'author'         => $user_id,
        'posts_per_page' => 1
    ]);

    if (!$macro_post) {
        return '<p style="text-align:center; font-size:18px; color:#777;">No macro targets set yet.</p>';
    }

    $post_id = $macro_post[0]->ID;
    $macros = get_post_meta($post_id, '_macro_target', true);
    $micros = get_post_meta($post_id, '_micro_targets', true);

    // Logo URL (replace with your actual logo URL)
    $logo_url = "https://simplifiedfoodandfitness.com/wp-content/uploads/2024/10/3.png";

    ob_start(); ?>
    
    <div class="dashboard-container" style="max-width:1200px; margin:auto; padding:20px; font-family:'Inter', Arial, sans-serif;">
        
        <!-- Header Section with Logo -->
        <div style="display:flex; align-items:center; justify-content:space-between; gap:15px; flex-wrap:wrap; margin-bottom:20px;">
            
            <!-- Left Logo -->
            <div style="flex-shrink:0;">
                <img src="<?php echo esc_url($logo_url); ?>" alt="Logo" style="height:60px; width:auto; max-width:200px;">
            </div>

            <h2 style="font-size:22px; color:#333; text-align:center; font-weight:700; flex:1; text-align:right;">
                Your Macro & Micro Targets
            </h2>
            
        </div>

        <!-- Macro & Micro Targets Section -->
        <div style="background:#fff; border-radius:16px; box-shadow:0 6px 15px rgba(0,0,0,0.1); padding:25px; margin-bottom:30px; transition: all 0.3s ease-in-out;">
            
            <div style="display:flex; flex-wrap:wrap; gap:20px; justify-content:center; align-items:stretch;">

                <!-- Macro Targets -->
                <div style="background:#fafafa; border-radius:12px; padding:20px; flex:1; min-width:300px; max-width:500px; transition: all 0.3s;">
                    <h3 style="font-size:18px; color:#222; margin-bottom:12px; text-align:center; font-weight:600;">Macro Targets</h3>
                    <ul style="list-style:none; padding:0; font-size:16px; color:#444; margin:0;">
                        <li style="display:flex; align-items:center; justify-content:space-between; padding:6px 0;">
                            <span><span class="sff-emoji">🔥</span> Calories:</span> <strong><?php echo esc_html($macros['calories'] ?? 'N/A'); ?></strong>
                        </li>
                        <li style="display:flex; align-items:center; justify-content:space-between; padding:6px 0;">
                            <span><span class="sff-emoji">🥩</span> Protein:</span> <strong><?php echo esc_html($macros['protein'] ?? 'N/A'); ?>g</strong>
                        </li>
                        <li style="display:flex; align-items:center; justify-content:space-between; padding:6px 0;">
                            <span><span class="sff-emoji">🍞</span> Carbs:</span> <strong><?php echo esc_html($macros['carbs'] ?? 'N/A'); ?>g</strong>
                        </li>
                        <li style="display:flex; align-items:center; justify-content:space-between; padding:6px 0;">
                            <span><span class="sff-emoji">🥑</span> Fats:</span> <strong><?php echo esc_html($macros['fats'] ?? 'N/A'); ?>g</strong>
                        </li>
                    </ul>
                </div>

                <!-- Micro Targets -->
                <div style="background:#fafafa; border-radius:12px; padding:20px; flex:1; min-width:300px; max-width:500px; transition: all 0.3s;">
                    <h3 style="font-size:18px; color:#222; margin-bottom:12px; text-align:center; font-weight:600;">Micro Targets</h3>
                    <ul style="list-style:none; padding:0; font-size:16px; color:#444; margin:0;">
                        <li style="display:flex; align-items:center; justify-content:space-between; padding:6px 0;">
                            <span><span class="sff-emoji">🍊</span> Vitamin C:</span> <strong><?php echo esc_html($micros['vitamin_c'] ?? 'N/A'); ?>mg</strong>
                        </li>
                        <li style="display:flex; align-items:center; justify-content:space-between; padding:6px 0;">
                            <span><span class="sff-emoji">💪</span> Iron:</span> <strong><?php echo esc_html($micros['iron'] ?? 'N/A'); ?>mg</strong>
                        </li>
                        <li style="display:flex; align-items:center; justify-content:space-between; padding:6px 0;">
                            <span><span class="sff-emoji">🌾</span> Fiber:</span> <strong><?php echo esc_html($micros['fiber'] ?? 'N/A'); ?>g</strong>
                        </li>
                    </ul>
                </div>

            </div>
        </div>

    </div>

    <?php
    return ob_get_clean();
}

add_shortcode('sff_macro_micro_targets', 'sff_frontend_macro_micro_targets');

function sff_frontend_meal_planner() {
    if (!is_user_logged_in()) {
        return '<p style="text-align:center; font-size:18px; color:#777;">Please log in to view your meal planner.</p>';
    }

    $user_id = get_current_user_id();

    // Fetch User's Macro & Micro Targets
    $macro_targets = get_user_meta($user_id, '_sff_macro_targets', true);
    $micro_targets = get_user_meta($user_id, '_sff_micro_targets', true);

    // Fetch User's Assigned Meal Plans
    $meal_plans = get_posts([
        'post_type'      => 'meal_plan',
        'meta_query'     => [['key' => '_assigned_user', 'value' => strval($user_id), 'compare' => '=']],
        'posts_per_page' => 7
    ]);

    // Logo URL
    $logo_url = "https://simplifiedfoodandfitness.com/wp-content/uploads/2024/10/3.png";

    ob_start(); ?>

    <div class="dashboard-container" style="max-width:1200px; margin:auto; padding:20px; font-family:'Inter', Arial, sans-serif;">

        <!-- Header with Logo -->
        <div style="display:flex; align-items:center; justify-content:space-between; gap:15px; flex-wrap:wrap; margin-bottom:20px;">
            <div style="flex-shrink:0;">
                <img src="<?php echo esc_url($logo_url); ?>" alt="Logo" style="height:60px; width:auto; max-width:200px;">
            </div>
            <h2 style="font-size:22px; color:#333; text-align:center; font-weight:700; flex:1; text-align:right;">
                Your Meal Planner
            </h2>
        </div>

        <!-- Macro & Micro Targets Summary -->
        <div style="background:#fff; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.1); padding:20px; margin-bottom:30px;">
            <h2 style="font-size:20px; color:#333; margin-bottom:15px; text-align:center;">Your Macro & Micro Targets</h2>
            <div style="display:flex; flex-wrap:wrap; gap:20px; justify-content:center;">

                <!-- Macro Targets -->
                <div style="background:#f9f9f9; border-radius:10px; padding:15px; flex:1; min-width:300px; max-width:500px;">
                    <h3 style="font-size:18px; color:#333; margin-bottom:10px; text-align:center;">Macro Targets</h3>
                    <ul style="list-style:none; padding:0; font-size:16px; color:#444;">
                        <li><span class="sff-emoji">🔥</span> Calories: <strong><?php echo esc_html($macro_targets['calories'] ?? 'N/A'); ?></strong></li>
                        <li><span class="sff-emoji">🥩</span> Protein: <strong><?php echo esc_html($macro_targets['protein'] ?? 'N/A'); ?>g</strong></li>
                        <li><span class="sff-emoji">🍞</span> Carbs: <strong><?php echo esc_html($macro_targets['carbs'] ?? 'N/A'); ?>g</strong></li>
                        <li><span class="sff-emoji">🥑</span> Fats: <strong><?php echo esc_html($macro_targets['fats'] ?? 'N/A'); ?>g</strong></li>
                    </ul>
                </div>

                <!-- Micro Targets -->
                <div style="background:#f9f9f9; border-radius:10px; padding:15px; flex:1; min-width:300px; max-width:500px;">
                    <h3 style="font-size:18px; color:#333; margin-bottom:10px; text-align:center;">Micro Targets</h3>
                    <ul style="list-style:none; padding:0; font-size:16px; color:#444;">
                        <li><span class="sff-emoji">🍊</span> Vitamin C: <strong><?php echo esc_html($micro_targets['vitamin_c'] ?? 'N/A'); ?>mg</strong></li>
                        <li><span class="sff-emoji">💪</span> Iron: <strong><?php echo esc_html($micro_targets['iron'] ?? 'N/A'); ?>mg</strong></li>
                        <li><span class="sff-emoji">🌾</span> Fiber: <strong><?php echo esc_html($micro_targets['fiber'] ?? 'N/A'); ?>g</strong></li>
                    </ul>
                </div>
            </div>
        </div>

        <?php if (!$meal_plans): ?>
            <!-- Styled No Meal Plan Message -->
            <div style="background:#fff; border-radius:16px; box-shadow:0 6px 15px rgba(0,0,0,0.1); padding:25px; text-align:center; transition: all 0.3s ease-in-out;">
                <h2 style="font-size:22px; color:#333; font-weight:700;">No Meal Plans Assigned</h2>
                <p style="font-size:16px; color:#777;">Your dietitian hasn't assigned a meal plan yet. Once they do, you'll see it here!</p>
                <p style="font-size:2rem; margin-top:10px;"><span class="sff-emoji">🍽️</span></p>
            </div>
        <?php else: ?>

        <!-- Meal Plan Section -->
        <div style="background:#fff; border-radius:16px; box-shadow:0 6px 15px rgba(0,0,0,0.1); padding:25px; margin-bottom:30px; transition: all 0.3s ease-in-out;">

            <?php foreach ($meal_plans as $meal): 
                $meal_title = get_post_meta($meal->ID, '_sff_meal_data', true)['title'] ?? 'Meal';
            ?>

            <!-- Single Meal Card -->
            <div style="display:flex; justify-content:space-between; align-items:center; background:#f9f9f9; border-radius:10px; padding:15px; margin-bottom:10px;">
                <div>
                    <!-- Meal Name Editable -->
                    <input type="text" value="<?php echo esc_attr($meal_title); ?>" name="meal_name[<?php echo esc_attr($meal->ID); ?>]" style="font-size:18px; font-weight:bold; color:#333; border:none; background:transparent;">
                </div>
                <div style="display:flex; gap:15px; font-size:14px;">
                    <span><span class="sff-emoji">🔥</span> kcal</span>
                    <span><span class="sff-emoji">🥩</span> g</span>
                    <span><span class="sff-emoji">🍞</span> g</span>
                    <span><span class="sff-emoji">🥑</span> g</span>
                </div>
            </div>

            <?php endforeach; ?>

            <!-- Add Meal Button -->
            <button style="display:block; width:100%; background:#E9FAB0; color:#023441; border:none; padding:12px; border-radius:8px; cursor:pointer; font-size:16px; font-weight:bold; text-align:center; transition:all 0.3s;">
                <span class="sff-emoji">➕</span> Add Meal
            </button>

        </div>

        <?php endif; ?>

    </div>

    <?php
    return ob_get_clean();
}

add_shortcode('sff_meal_planner', 'sff_frontend_meal_planner');


function sff_client_intake_form() {
    // Check if the form has been submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
        // Prepare the lead data
        $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
        $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';

        // Create a new post of type 'client_leads'
        $lead_id = wp_insert_post([
            'post_title' => $first_name . ' ' . $last_name,
            'post_type' => 'client_leads',
            'post_status' => 'publish',
        ]);

        if (!is_wp_error($lead_id)) {
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
                'dbw'                         => 'sanitize_text_field',
                'dbw_unit'                    => 'sanitize_text_field',
                'height'                      => 'sanitize_text_field',
                'height_unit'                 => 'sanitize_text_field',
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
                'current_activity_frequency'  => 'sanitize_text_field',
                'current_activity_types'      => 'array',
                'cardio_type'                 => 'sanitize_text_field',
                'crossfit_gym'                => 'sanitize_text_field',
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
                if (!isset($_POST[$field])) {
                    continue;
                }

                $value = $_POST[$field];

                if ($sanitize === 'array') {
                    $value = implode(', ', array_map('sanitize_text_field', (array) $value));
                } elseif (is_callable($sanitize)) {
                    $value = call_user_func($sanitize, $value);
                }

                update_post_meta($lead_id, 'sff_' . $field, $value);
            }

            // Display a success message
            echo '<div style="color:green; margin-bottom:20px;">Lead saved successfully!</div>';
        } else {
            // Display an error message
            echo '<div style="color:red; margin-bottom:20px;">Failed to save lead: ' . $lead_id->get_error_message() . '</div>';
        }
    }

    // Define the logo URL
    $logo_url = 'https://simplifiedfoodandfitness.com/wp-content/uploads/2024/10/3.png';

    // Start output buffering to capture HTML content
    ob_start(); ?>
    
    <div id="client-intake-form" style="max-width:800px; margin:auto; font-family:'Inter', Arial, sans-serif;">
        <!-- Logo at the Top Left -->
        <div style="display:flex; justify-content:flex-start; margin-bottom:20px;">
            <img src="<?php echo esc_url($logo_url); ?>" alt="Logo" style="height:70px; width:auto; max-width:200px;">
        </div>
        
        <!-- Progress Bar -->
        <div id="progress-bar" style="background:#eee; border-radius:10px; margin-bottom:20px; overflow:hidden;">
            <div id="progress" style="width:0; height:10px; background:#42b14c; transition:width 0.3s ease;"></div>
        </div>

        <!-- Error Message Container -->
        <div id="error-message" style="color:red; margin-bottom:15px; display:none;">Please fill out this field or make a selection.</div>

        <!-- Form Container -->
        <form id="intake-form" style="padding:20px;" method="post" action="">
            <!-- Step 1: Client Information -->
            <fieldset class="form-step active" style="display:block;">
                <legend style="font-size:1.5em; margin-bottom:15px;">Step 1: Client Information</legend>
                
                <label style="font-weight:bold; margin-bottom:5px;">👤 First Name</label>
                <input type="text" name="first_name" placeholder="e.g., John" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px;" required>

                <label style="font-weight:bold; margin-bottom:5px;">👤 Last Name</label>
                <input type="text" name="last_name" placeholder="e.g., Doe" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px;" required>

                <label style="font-weight:bold; margin-bottom:5px;">✉️ Email</label>
                <input type="email" name="email" placeholder="e.g., john.doe@example.com" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px;" required>

                <label style="font-weight:bold; margin-bottom:5px;">📞 Phone Number</label>
                <input type="tel" name="phone" placeholder="e.g., 555-123-4567" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px;" required>

                <label style="font-weight:bold; margin-bottom:5px;">📅 Date of Birth</label>
                <input type="date" name="dob" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px;" required>

                <label style="font-weight:bold; margin-bottom:5px;">🚻 Gender</label>
                <select name="gender" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px;" required>
                    <option value="" selected>--- Select an Option ---</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
                <input type="text" name="gender_other" placeholder="e.g., Non-binary" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px; display:none;" class="conditional-field" data-condition="gender" data-value="Other">

                <label style="font-weight:bold; margin-bottom:5px;">⚖️ Current Body Weight (CBW)</label>
                <div style="display:flex; gap:10px; margin-bottom:15px;">
                    <input type="number" name="cbw" step="any" placeholder="e.g., 150" style="width:70%; padding:10px; border:1px solid #ccc; border-radius:5px;" required>
                    <select name="cbw_unit" style="width:30%; padding:10px; border:1px solid #ccc; border-radius:5px;" required>
                        <option value="" selected>--- Select an Option ---</option>
                        <option value="lbs">Pounds</option>
                        <option value="kg">Kilograms</option>
                    </select>
                </div>

                <label style="font-weight:bold; margin-bottom:5px;">🎯 Desired Body Weight (DBW)</label>
                <div style="display:flex; gap:10px; margin-bottom:15px;">
                    <input type="number" name="dbw" step="any" placeholder="e.g., 140" style="width:70%; padding:10px; border:1px solid #ccc; border-radius:5px;" required>
                    <select name="dbw_unit" style="width:30%; padding:10px; border:1px solid #ccc; border-radius:5px;" required>
                        <option value="" selected>--- Select an Option ---</option>
                        <option value="lbs">Pounds</option>
                        <option value="kg">Kilograms</option>
                    </select>
                </div>

                <label style="font-weight:bold; margin-bottom:5px;">📏 Height</label>
                <div style="display:flex; gap:10px; margin-bottom:15px;">
                    <input type="number" step="any" name="height" placeholder="e.g., 170" style="width:70%; padding:10px; border:1px solid #ccc; border-radius:5px;" required>
                    <select name="height_unit" style="width:30%; padding:10px; border:1px solid #ccc; border-radius:5px;" required>
                        <option value="" selected>--- Select an Option ---</option>
                        <option value="cm">Centimeters</option>
                        <option value="in">Inches</option>
                    </select>
                </div>

                <label style="font-weight:bold; margin-bottom:5px;">🏥 Past Medical Conditions</label>
                <div style="margin-bottom:15px; column-count:2;" class="checkbox-group" data-name="past_medical_conditions[]">
                    <label><input type="checkbox" name="past_medical_conditions[]" value="Hyperhidrosis (excessive sweating)"> Hyperhidrosis (excessive sweating)</label><br>
                    <label><input type="checkbox" name="past_medical_conditions[]" value="Chronic Liver Disease"> Chronic Liver Disease</label><br>
                    <label><input type="checkbox" name="past_medical_conditions[]" value="Hyperlipidemia (High Cholesterol)"> Hyperlipidemia (High Cholesterol)</label><br>
                    <label><input type="checkbox" name="past_medical_conditions[]" value="Hypertension"> Hypertension</label><br>
                    <label><input type="checkbox" name="past_medical_conditions[]" value="Hypothyroidism"> Hypothyroidism</label><br>
                    <label><input type="checkbox" name="past_medical_conditions[]" value="Hyperthyroidism"> Hyperthyroidism</label><br>
                    <label><input type="checkbox" name="past_medical_conditions[]" value="Gastroenteritis"> Gastroenteritis</label><br>
                    <label><input type="checkbox" name="past_medical_conditions[]" value="Ankylosis Spondalytis"> Ankylosis Spondalytis</label><br>
                    <label><input type="checkbox" name="past_medical_conditions[]" value="Gastro-esophageal reflux (GERD)"> Gastro-esophageal reflux (GERD)</label><br>
                    <label><input type="checkbox" name="past_medical_conditions[]" value="Celiac Disease"> Celiac Disease</label><br>
                    <label><input type="checkbox" name="past_medical_conditions[]" value="Crohn’s Disease"> Crohn’s Disease</label><br>
                    <label><input type="checkbox" name="past_medical_conditions[]" value="Ulcerative Colitis"> Ulcerative Colitis</label><br>
                    <label><input type="checkbox" name="past_medical_conditions[]" value="Irritable Bowl Syndrome"> Irritable Bowl Syndrome</label><br>
                    <label><input type="checkbox" name="past_medical_conditions[]" value="Malnutrition"> Malnutrition</label><br>
                    <label><input type="checkbox" name="past_medical_conditions[]" value="Osteoporosis"> Osteoporosis</label><br>
                    <label><input type="checkbox" name="past_medical_conditions[]" value="Obesity"> Obesity</label><br>
                    <label><input type="checkbox" name="past_medical_conditions[]" value="Cancer"> Cancer</label><br>
                    <label><input type="checkbox" name="past_medical_conditions[]" value="Kidney Disease"> Kidney Disease</label><br>
                    <label><input type="checkbox" name="past_medical_conditions[]" value="Heart Disease"> Heart Disease</label><br>
                    <label><input type="checkbox" name="past_medical_conditions[]" value="Diabetes (type I or II)"> Diabetes (type I or II)</label><br>
                    <label><input type="checkbox" name="past_medical_conditions[]" value="Other"> Other (Please specify)</label>
                </div>
                <input type="text" name="past_medical_conditions_other" placeholder="e.g., Asthma" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px; display:none;" class="conditional-field" data-condition="past_medical_conditions[]">

                <label style="font-weight:bold; margin-bottom:5px;">💊 Current Medications & Dosages</label>
                <textarea name="medications" placeholder="e.g., Lisinopril 10mg daily" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px; resize:vertical;" required></textarea>

                <label style="font-weight:bold; margin-bottom:5px;">⚠️ Medication Allergies</label>
                <textarea name="medication_allergies" placeholder="e.g., Penicillin" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px; resize:vertical;" required></textarea>

                <label style="font-weight:bold; margin-bottom:5px;">🍽️ Food Allergies</label>
                <div style="margin-bottom:15px; column-count:2;" class="checkbox-group" data-name="food_allergies[]">
                    <label><input type="checkbox" name="food_allergies[]" value="Dairy"> Dairy</label><br>
                    <label><input type="checkbox" name="food_allergies[]" value="Eggs"> Eggs</label><br>
                    <label><input type="checkbox" name="food_allergies[]" value="Fish"> Fish</label><br>
                    <label><input type="checkbox" name="food_allergies[]" value="Crustacean Shellfish"> Crustacean Shellfish</label><br>
                    <label><input type="checkbox" name="food_allergies[]" value="Tree nuts"> Tree nuts</label><br>
                    <label><input type="checkbox" name="food_allergies[]" value="Peanuts"> Peanuts</label><br>
                    <label><input type="checkbox" name="food_allergies[]" value="Wheat"> Wheat</label><br>
                    <label><input type="checkbox" name="food_allergies[]" value="Soy"> Soy</label><br>
                    <label><input type="checkbox" name="food_allergies[]" value="Sesame"> Sesame</label><br>
                    <label><input type="checkbox" name="food_allergies[]" value="None"> None</label><br>
                    <label><input type="checkbox" name="food_allergies[]" value="Other"> Other (Please specify)</label>
                </div>
                <input type="text" name="food_allergies_other" placeholder="e.g., Shellfish" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px; display:none;" class="conditional-field" data-condition="food_allergies[]">

                <label style="font-weight:bold; margin-bottom:5px;">🌾 Food Intolerances</label>
                <div style="margin-bottom:15px; column-count:2;" class="checkbox-group" data-name="food_intolerances[]">
                    <label><input type="checkbox" name="food_intolerances[]" value="Dairy"> Dairy</label><br>
                    <label><input type="checkbox" name="food_intolerances[]" value="Gluten"> Gluten</label><br>
                    <label><input type="checkbox" name="food_intolerances[]" value="Caffeine"> Caffeine</label><br>
                    <label><input type="checkbox" name="food_intolerances[]" value="Salicylates"> Salicylates</label><br>
                    <label><input type="checkbox" name="food_intolerances[]" value="Amines"> Amines</label><br>
                    <label><input type="checkbox" name="food_intolerances[]" value="FODMAPs"> FODMAPs</label><br>
                    <label><input type="checkbox" name="food_intolerances[]" value="Sulfites"> Sulfites</label><br>
                    <label><input type="checkbox" name="food_intolerances[]" value="Fructose"> Fructose</label><br>
                    <label><input type="checkbox" name="food_intolerances[]" value="Yeast"> Yeast</label><br>
                    <label><input type="checkbox" name="food_intolerances[]" value="Sugar alcohols"> Sugar alcohols</label><br>
                    <label><input type="checkbox" name="food_intolerances[]" value="Eggs"> Eggs</label><br>
                    <label><input type="checkbox" name="food_intolerances[]" value="Aspartame"> Aspartame</label><br>
                    <label><input type="checkbox" name="food_intolerances[]" value="None"> None</label><br>
                    <label><input type="checkbox" name="food_intolerances[]" value="Other"> Other (Please specify)</label>
                </div>
                <input type="text" name="food_intolerances_other" placeholder="e.g., Lactose" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px; display:none;" class="conditional-field" data-condition="food_intolerances[]">

                <label style="font-weight:bold; margin-bottom:5px;">🎯 Goal</label>
                <select name="goal" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px;" required>
                    <option value="" selected>--- Select an Option ---</option>
                    <option value="Fat Loss">Fat Loss</option>
                    <option value="Maintenance">Maintenance</option>
                    <option value="Muscle Gain">Muscle Gain</option>
                </select>

                <button type="button" class="next-step" style="padding:10px 20px; background:#42b14c; color:white; border:none; border-radius:5px; cursor:pointer;">Next ➡️</button>
            </fieldset>

            <!-- Step 2: Physical Activity -->
            <fieldset class="form-step" style="display:none;">
                <legend style="font-size:1.5em; margin-bottom:15px;">Step 2: Physical Activity</legend>

                <label style="font-weight:bold; margin-bottom:5px;">🏋️ Current Physical Activity</label>
                <div style="margin-bottom:15px;">
                    <label>Frequency</label>
                    <select name="current_activity_frequency" style="width:100%; padding:10px; margin-bottom:10px; border:1px solid #ccc; border-radius:5px;" required>
                        <option value="" selected>--- Select an Option ---</option>
                        <option value="Little to no exercise">Little to no exercise</option>
                        <option value="1-3 days a week">1-3 days a week</option>
                        <option value="3-5 days a week">3-5 days a week</option>
                        <option value="6-7 days a week">6-7 days a week</option>
                        <option value="2 times per day">2 times per day intense training</option>
                    </select>
                    <label>Type</label>
                    <div style="margin-bottom:10px; column-count:2;" class="checkbox-group" data-name="current_activity_types[]">
                        <label><input type="checkbox" name="current_activity_types[]" value="Weight training"> Weight training</label><br>
                        <label><input type="checkbox" name="current_activity_types[]" value="Cardio"> Cardio (specify which one: cycling, running, or swimming)</label><br>
                        <label><input type="checkbox" name="current_activity_types[]" value="HIIT"> HIIT</label><br>
                        <label><input type="checkbox" name="current_activity_types[]" value="CrossFit"> CrossFit (Please specify which CrossFit gym)</label><br>
                        <label><input type="checkbox" name="current_activity_types[]" value="None"> None</label><br>
                        <label><input type="checkbox" name="current_activity_types[]" value="Other"> Other (please specify)</label>
                        <input type="text" name="activity_type_other" class="conditional-field" data-condition="current_activity_types[]" data-value="Other" placeholder="Please specify" style="display:none; margin-top:10px;">


                    </div>
                    <select name="cardio_type" style="width:100%; padding:10px; margin-bottom:10px; border:1px solid #ccc; border-radius:5px; display:none;" class="conditional-field" data-condition="current_activity_types[]">
                        <option value="" selected>--- Select an Option ---</option>
                        <option value="Cycling">Cycling</option>
                        <option value="Running">Running</option>
                        <option value="Swimming">Swimming</option>
                    </select>
                    <input type="text" name="crossfit_gym" placeholder="e.g., CrossFit XYZ" style="width:100%; padding:10px; margin-bottom:10px; border:1px solid #ccc; border-radius:5px; display:none;" class="conditional-field" data-condition="current_activity_types[]">
                    <input type="text" name="current_activity_type_other" placeholder="e.g., Yoga" style="width:100%; padding:10px; margin-bottom:10px; border:1px solid #ccc; border-radius:5px; display:none;" class="conditional-field" data-condition="current_activity_types[]">
                    <label>Personal Trainer</label>
                    <div style="margin-bottom:10px;">
                        <input type="radio" name="has_trainer" value="Yes" style="margin-right:5px;" required> Yes
                        <input type="radio" name="has_trainer" value="No" style="margin-left:10px; margin-right:5px;"> No
                        <input type="radio" name="has_trainer" value="Need one" style="margin-left:10px; margin-right:5px;"> Need one
                    </div>
                    <input type="text" name="trainer_name" placeholder="e.g., Jane Smith" style="width:100%; padding:10px; margin-bottom:10px; border:1px solid #ccc; border-radius:5px; display:none;" class="conditional-field" data-condition="has_trainer" data-value="Yes">
                    <input type="text" name="trainer_contact" placeholder="e.g., 555-123-4567" style="width:100%; padding:10px; margin-bottom:10px; border:1px solid #ccc; border-radius:5px; display:none;" class="conditional-field" data-condition="has_trainer" data-value="Yes">
                </div>

                <label style="font-weight:bold; margin-bottom:5px;">🏃 Goal Physical Activity</label>
                <div style="margin-bottom:15px;">
                    <label>Days per week</label>
                    <input type="number" name="goal_activity_days" min="0" max="7" placeholder="e.g., 5" style="width:100%; padding:10px; margin-bottom:10px; border:1px solid #ccc; border-radius:5px;" required>
                    <label>Minutes per week</label>
                    <input type="number" name="goal_activity_minutes" min="0" placeholder="e.g., 200" style="width:100%; padding:10px; margin-bottom:10px; border:1px solid #ccc; border-radius:5px;" required>
                    <label>Type</label>
                    <select name="goal_activity_type" style="width:100%; padding:10px; margin-bottom:10px; border:1px solid #ccc; border-radius:5px;" required>
                        <option value="" selected>--- Select an Option ---</option>
                        <option value="Weight Training">Weight Training</option>
                        <option value="Cardio">Cardio</option>
                        <option value="Other">Other (Specify Below)</option>
                    </select>
                    <input type="text" name="goal_activity_type_other" placeholder="e.g., Pilates" style="width:100%; padding:10px; margin-bottom:10px; border:1px solid #ccc; border-radius:5px; display:none;" class="conditional-field" data-condition="goal_activity_type" data-value="Other">
                </div>

                <label style="font-weight:bold; margin-bottom:5px;">⌚ Smart Watch</label>
                <select name="smart_watch" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px;" required>
                    <option value="" selected>--- Select an Option ---</option>
                    <option value="Apple">Apple</option>
                    <option value="Fitbit">Fitbit</option>
                    <option value="Garmin">Garmin</option>
                    <option value="None">None</option>
                    <option value="Other">Other (Specify Below)</option>
                </select>
                <input type="text" name="smart_watch_other" placeholder="e.g., Samsung" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px; display:none;" class="conditional-field" data-condition="smart_watch" data-value="Other">

                <button type="button" class="prev-step" style="padding:10px 20px; background:#ccc; color:#333; border:none; border-radius:5px; cursor:pointer; margin-right:10px;">⬅️ Back</button>
                <button type="button" class="next-step" style="padding:10px 20px; background:#42b14c; color:white; border:none; border-radius:5px; cursor:pointer;">Next ➡️</button>
            </fieldset>

            <!-- Step 3: Meal Planning Preferences -->
            <fieldset class="form-step" style="display:none;">
                <legend style="font-size:1.5em; margin-bottom:15px;">Step 3: Meal Planning Preferences</legend>

                <label style="font-weight:bold; margin-bottom:5px;">🍳 How often do you want to cook?</label>
                <select name="cooking_frequency" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px;" required>
                    <option value="" selected>--- Select an Option ---</option>
                    <option value="Every day">Every day</option>
                    <option value="Every other day">Every other day</option>
                    <option value="Once every 3 days">Once every 3 days</option>
                    <option value="Once a week">Once a week</option>
                    <option value="I do not want to cook">I do not want to cook (Meal Delivery Service)</option>
                </select>

                <label style="font-weight:bold; margin-bottom:5px;">🍽️ How many meals per day do you eat?</label>
                <select name="meals_per_day" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px;" required>
                    <option value="" selected>--- Select an Option ---</option>
                    <option value="One">One</option>
                    <option value="Two">Two</option>
                    <option value="Three">Three</option>
                    <option value="Four">Four</option>
                    <option value="Other">Other (Specify Below)</option>
                </select>
                <input type="text" name="meals_per_day_other" placeholder="e.g., Five" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px; display:none;" class="conditional-field" data-condition="meals_per_day" data-value="Other">

                <label style="font-weight:bold; margin-bottom:5px;">🥪 Do you eat snacks?</label>
                <select name="snacks" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px;" required>
                    <option value="" selected>--- Select an Option ---</option>
                    <option value="One a day">One a day</option>
                    <option value="Two a day">Two a day</option>
                    <option value="Three a day">Three a day</option>
                    <option value="I do not snack">I do not snack</option>
                </select>

                <label style="font-weight:bold; margin-bottom:5px;">🍫 What snacks do you like to eat? (Include brand and item name)</label>
                <textarea name="favorite_snacks" placeholder="e.g., Kind Bars, Almonds" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px; resize:vertical;" required></textarea>

                <label style="font-weight:bold; margin-bottom:5px;">☕ How do you drink your coffee?</label>
                <input type="text" name="coffee_how" placeholder="e.g., Black, With cream and sugar" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px;" required>

                <label style="font-weight:bold; margin-bottom:5px;">⏰ How often do you drink coffee?</label>
                <input type="text" name="coffee_frequency" placeholder="e.g., Daily, Occasionally" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px;" required>

                <label style="font-weight:bold; margin-bottom:5px;">☕ How many coffees per day?</label>
                <input type="number" name="coffee_per_day" min="0" placeholder="e.g., 1, 2" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px;" required>

                <label style="font-weight:bold; margin-bottom:5px;">🥗 Do you have a diet preference?</label>
                <select name="diet_preference" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px;" required>
                    <option value="" selected>--- Select an Option ---</option>
                    <option value="Mediterranean">Mediterranean</option>
                    <option value="Paleo">Paleo</option>
                    <option value="Keto">Keto</option>
                    <option value="Kosher">Kosher</option>
                    <option value="Pescatarian">Pescatarian</option>
                    <option value="Gluten free">Gluten free</option>
                    <option value="Dairy free">Dairy free</option>
                    <option value="Soy free">Soy free</option>
                    <option value="Egg free">Egg free</option>
                    <option value="Red meat free">Red meat free</option>
                    <option value="DASH">DASH</option>
                    <option value="Whole 30">Whole 30</option>
                    <option value="Vegetarian">Vegetarian</option>
                    <option value="Vegan">Vegan</option>
                    <option value="None">None</option>
                    <option value="Other">Other (Please specify)</option>
                </select>
                <input type="text" name="diet_preference_other" placeholder="e.g., Low-Carb" 
                 style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px; display:none;" 
                 class="conditional-field" 
                 data-condition="diet_preference" 
                 data-value="Other">


                <label style="font-weight:bold; margin-bottom:5px;">🍲 What are your favorite meals?</label>
                <textarea name="favorite_meals" placeholder="e.g., Grilled Chicken Salad" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px; resize:vertical;" required></textarea>

                <label style="font-weight:bold; margin-bottom:5px;">🍎 Favorite Fruits</label>
                <div style="margin-bottom:15px; column-count:2;" class="checkbox-group" data-name="favorite_fruits[]">
                    <label><input type="checkbox" name="favorite_fruits[]" value="Avocados"> Avocados</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Mangos"> Mangos</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Papayas"> Papayas</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Oranges"> Oranges</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Grapefruits"> Grapefruits</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Dragon fruit"> Dragon fruit</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Acai"> Acai</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Bananas"> Bananas</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Plantains"> Plantains</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Guava"> Guava</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Coconut"> Coconut</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Mamey"> Mamey</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Passion fruit"> Passion fruit</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Lychee"> Lychee</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Jackfruit"> Jackfruit</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Star fruit"> Star fruit</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Blueberries"> Blueberries</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Raspberries"> Raspberries</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Fig"> Fig</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Grapes"> Grapes</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Mandarins"> Mandarins</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Strawberries"> Strawberries</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Tomato’s"> Tomato’s</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Watermelon"> Watermelon</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Pineapple"> Pineapple</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Lear"> Lear</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Cantalope"> Cantalope</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Pomegranate"> Pomegranate</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Cherries"> Cherries</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Kiwi"> Kiwi</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Plums"> Plums</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Honeydew Melon"> Honeydew Melon</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Peaches"> Peaches</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="None"> None</label><br>
                    <label><input type="checkbox" name="favorite_fruits[]" value="Other"> Other (Please specify)</label>
                </div>
                <input type="text" name="favorite_fruits_other" placeholder="e.g., Apricots" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px; display:none;" class="conditional-field" data-condition="favorite_fruits[]">

                <label style="font-weight:bold; margin-bottom:5px;">🍏 Disliked Fruits</label>
                <div style="margin-bottom:15px; column-count:2;" class="checkbox-group" data-name="disliked_fruits[]">
                    <label><input type="checkbox" name="disliked_fruits[]" value="Avocados"> Avocados</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Mangos"> Mangos</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Papayas"> Papayas</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Oranges"> Oranges</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Grapefruits"> Grapefruits</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Dragon fruit"> Dragon fruit</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Acai"> Acai</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Bananas"> Bananas</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Plantains"> Plantains</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Guava"> Guava</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Coconut"> Coconut</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Mamey"> Mamey</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Passion fruit"> Passion fruit</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Lychee"> Lychee</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Jackfruit"> Jackfruit</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Star fruit"> Star fruit</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Blueberries"> Blueberries</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Raspberries"> Raspberries</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Fig"> Fig</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Grapes"> Grapes</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Mandarins"> Mandarins</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Strawberries"> Strawberries</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Tomato’s"> Tomato’s</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Watermelon"> Watermelon</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Pineapple"> Pineapple</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Lear"> Lear</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Cantalope"> Cantalope</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Pomegranate"> Pomegranate</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Cherries"> Cherries</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Kiwi"> Kiwi</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Plums"> Plums</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Honeydew Melon"> Honeydew Melon</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Peaches"> Peaches</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="None"> None</label><br>
                    <label><input type="checkbox" name="disliked_fruits[]" value="Other"> Other (Please specify)</label>
                </div>
                <input type="text" name="disliked_fruits_other" placeholder="e.g., Apricots" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px; display:none;" class="conditional-field" data-condition="disliked_fruits[]">

                <label style="font-weight:bold; margin-bottom:5px;">🥕 Favorite Vegetables</label>
                <div style="margin-bottom:15px; column-count:2;" class="checkbox-group" data-name="favorite_vegetables[]">
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Asparagus"> Asparagus</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Green beans"> Green beans</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Zucchini squash"> Zucchini squash</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Broccoli"> Broccoli</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Cucumber"> Cucumber</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Broccolini"> Broccolini</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Carrots"> Carrots</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Brussels sprouts"> Brussels sprouts</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Sweet potato"> Sweet potato</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Russet potato"> Russet potato</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Green bell pepper"> Green bell pepper</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Yellow bell pepper"> Yellow bell pepper</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Red bell pepper"> Red bell pepper</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="White onion"> White onion</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Red onion"> Red onion</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Yellow onion"> Yellow onion</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Cabbage"> Cabbage</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Cauliflower"> Cauliflower</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Yellow squash"> Yellow squash</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Eggplant"> Eggplant</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Celery"> Celery</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Scallions"> Scallions</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Cilantro"> Cilantro</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Parsley"> Parsley</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Bok choy"> Bok choy</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Ginger"> Ginger</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Butternut squash"> Butternut squash</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Corn"> Corn</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Jalapeños"> Jalapeños</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Okra"> Okra</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Beets"> Beets</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Chinese eggplant"> Chinese eggplant</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Chayote squash"> Chayote squash</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Spinach"> Spinach</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Spring mix"> Spring mix</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Kale"> Kale</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="White Mushroom"> White Mushroom</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Portabella mushroom"> Portabella mushroom</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Radish"> Radish</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Lettuce"> Lettuce</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Leeks"> Leeks</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Rainbow chard"> Rainbow chard</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Jicama"> Jicama</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Artichoke"> Artichoke</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="None"> None</label><br>
                    <label><input type="checkbox" name="favorite_vegetables[]" value="Other"> Other (Please specify)</label>
                </div>
                <!-- <input type="text" name="favorite_vegetables_other" placeholder="e.g., Arugula" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px; display:none;" class="conditional-field" data-condition="favorite_vegetables[]"> -->
                <input type="text" name="favorite_vegetables_other"
                   placeholder="e.g., Arugula"
                   style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px; display:none;"
                   class="conditional-field"
                   data-condition="favorite_vegetables[]"
                   data-value="Other">


                <label style="font-weight:bold; margin-bottom:5px;">🥦 Disliked Vegetables</label>
                <div style="margin-bottom:15px; column-count:2;" class="checkbox-group" data-name="disliked_vegetables[]">
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Asparagus"> Asparagus</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Green beans"> Green beans</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Zucchini squash"> Zucchini squash</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Broccoli"> Broccoli</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Cucumber"> Cucumber</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Broccolini"> Broccolini</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Carrots"> Carrots</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Brussels sprouts"> Brussels sprouts</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Sweet potato"> Sweet potato</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Russet potato"> Russet potato</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Green bell pepper"> Green bell pepper</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Yellow bell pepper"> Yellow bell pepper</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Red bell pepper"> Red bell pepper</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="White onion"> White onion</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Red onion"> Red onion</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Yellow onion"> Yellow onion</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Cabbage"> Cabbage</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Cauliflower"> Cauliflower</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Yellow squash"> Yellow squash</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Eggplant"> Eggplant</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Celery"> Celery</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Scallions"> Scallions</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Cilantro"> Cilantro</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Parsley"> Parsley</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Bok choy"> Bok choy</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Ginger"> Ginger</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Butternut squash"> Butternut squash</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Corn"> Corn</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Jalapeños"> Jalapeños</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Okra"> Okra</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Beets"> Beets</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Chinese eggplant"> Chinese eggplant</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Chayote squash"> Chayote squash</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Spinach"> Spinach</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Spring mix"> Spring mix</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Kale"> Kale</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="White Mushroom"> White Mushroom</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Portabella mushroom"> Portabella mushroom</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Radish"> Radish</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Lettuce"> Lettuce</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Leeks"> Leeks</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Rainbow chard"> Rainbow chard</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Jicama"> Jicama</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Artichoke"> Artichoke</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="None"> None</label><br>
                    <label><input type="checkbox" name="disliked_vegetables[]" value="Other"> Other (Please specify)</label>
                </div>
                <input type="text" name="disliked_vegetables_other" placeholder="e.g., Arugula" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px; display:none;" class="conditional-field" data-condition="disliked_vegetables[]">

                <label style="font-weight:bold; margin-bottom:5px;">🍲 Do you mind eating leftovers for lunch the following day?</label>
                <div style="margin-bottom:15px;">
                    <input type="radio" name="leftovers" value="Yes" style="margin-right:5px;" required> Yes
                    <input type="radio" name="leftovers" value="No" style="margin-left:10px; margin-right:5px;"> No
                    <input type="radio" name="leftovers" value="Other" style="margin-left:10px; margin-right:5px;"> Other
                    <input type="text" name="leftovers_other" placeholder="e.g., Sometimes" style="width:100%; padding:10px; margin-top:10px; border:1px solid #ccc; border-radius:5px; display:none;" class="conditional-field" data-condition="leftovers" data-value="Other">
                </div>

                <label style="font-weight:bold; margin-bottom:5px;">🔄 How often do you mind repeating meals?</label>
                <select name="repeating_meals" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px;" required>
                    <option value="" selected>--- Select an Option ---</option>
                    <option value="One">One time per week</option>
                    <option value="Two">Two times per week</option>
                    <option value="Three">Three times per week</option>
                    <option value="Four">Four times per week</option>
                </select>

                <label style="font-weight:bold; margin-bottom:5px;">🛒 What grocery store do you buy your groceries at?</label>
                <div style="margin-bottom:15px; column-count:2;" class="checkbox-group" data-name="grocery_store[]">
                    <label><input type="checkbox" name="grocery_store[]" value="Walmart"> Walmart</label><br>
                    <label><input type="checkbox" name="grocery_store[]" value="Sprouts"> Sprouts</label><br>
                    <label><input type="checkbox" name="grocery_store[]" value="Whole Foods"> Whole Foods</label><br>
                    <label><input type="checkbox" name="grocery_store[]" value="Publix"> Publix</label><br>
                    <label><input type="checkbox" name="grocery_store[]" value="Fresco y Mas"> Fresco y Mas</label><br>
                    <label><input type="checkbox" name="grocery_store[]" value="Trader Joe’s"> Trader Joe’s</label><br>
                    <label><input type="checkbox" name="grocery_store[]" value="Milan"> Milan</label><br>
                    <label><input type="checkbox" name="grocery_store[]" value="Aldi’s"> Aldi’s</label><br>
                    <label><input type="checkbox" name="grocery_store[]" value="Other"> Other (Please specify)</label>
                </div>
                <input type="text" name="grocery_store_other" placeholder="e.g., Kroger" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px; display:none;" class="conditional-field" data-condition="grocery_store[]">

                <label style="font-weight:bold; margin-bottom:5px;">🚚 Do you use a grocery delivery service?</label>
                <div style="margin-bottom:15px;">
                    <input type="radio" name="grocery_delivery" value="Yes" style="margin-right:5px;" required> Yes
                    <input type="radio" name="grocery_delivery" value="No" style="margin-left:10px; margin-right:5px;"> No
                    <input type="radio" name="grocery_delivery" value="Need one" style="margin-left:10px; margin-right:5px;"> Need one
                    <div style="margin-top:10px; column-count:2; display:none;" class="conditional-field checkbox-group" data-condition="grocery_delivery" data-value="Yes" data-name="grocery_delivery_service[]">
                        <label><input type="checkbox" name="grocery_delivery_service[]" value="Instacart"> Instacart</label><br>
                        <label><input type="checkbox" name="grocery_delivery_service[]" value="Shipt"> Shipt</label><br>
                        <label><input type="checkbox" name="grocery_delivery_service[]" value="Door Dash"> Door Dash</label><br>
                        <label><input type="checkbox" name="grocery_delivery_service[]" value="Kroger"> Kroger</label><br>
                        <label><input type="checkbox" name="grocery_delivery_service[]" value="Uber Eats"> Uber Eats</label><br>
                        <label><input type="checkbox" name="grocery_delivery_service[]" value="Thrive Market"> Thrive Market</label><br>
                        <label><input type="checkbox" name="grocery_delivery_service[]" value="Amazon Fresh"> Amazon Fresh</label><br>
                        <label><input type="checkbox" name="grocery_delivery_service[]" value="Other"> Other (Please specify)</label>
                    </div>
                    <input type="text" name="grocery_delivery_service_other" placeholder="e.g., Local Service" style="width:100%; padding:10px; margin-top:10px; border:1px solid #ccc; border-radius:5px; display:none;" class="conditional-field" data-condition="grocery_delivery_service[]">
                </div>

                <label style="font-weight:bold; margin-bottom:5px;">🌿 Do you prefer organic products?</label>
                <select name="organic_preference" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px;" required>
                    <option value="" selected>--- Select an Option ---</option>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                    <option value="Indifferent">Indifferent</option>
                </select>

                <label style="font-weight:bold; margin-bottom:5px;">📧 I accept to receive emails, newsletters, and updates about our services and latest news.</label>
                <div style="margin-bottom:15px;">
                    <input type="radio" name="email_consent" value="Yes" style="margin-right:5px;" required> Yes
                    <input type="radio" name="email_consent" value="No" style="margin-left:10px; margin-right:5px;"> No
                </div>

                <label style="font-weight:bold; margin-bottom:5px;">🔍 How did you find us?</label>
                <div style="margin-bottom:15px;">
                    <input type="radio" name="how_found" value="Google" style="margin-right:5px;" required> Google
                    <input type="radio" name="how_found" value="Reset Lab" style="margin-left:10px; margin-right:5px;"> Reset Lab
                    <input type="radio" name="how_found" value="Other" style="margin-left:10px; margin-right:5px;"> Other
                    <input type="text" name="how_found_other" placeholder="e.g., Friend's Name" style="width:100%; padding:10px; margin-top:10px; border:1px solid #ccc; border-radius:5px; display:none;" class="conditional-field" data-condition="how_found" data-value="Other">
                </div>

                <button type="button" class="prev-step" style="padding:10px 20px; background:#ccc; color:#333; border:none; border-radius:5px; cursor:pointer; margin-right:10px;">⬅️ Back</button>
                <button type="submit" style="padding:10px 20px; background:#42b14c; color:white; border:none; border-radius:5px; cursor:pointer;">Submit ✅</button>
            </fieldset>
        </form>
    </div>

    <!-- JavaScript for Step Navigation, Validation, and Conditional Fields -->
   <script>
jQuery(document).ready(function($) {
    var currentStep = 0;
    var steps = $('.form-step');

    function updateProgress() {
        var progress = ((currentStep + 1) / steps.length) * 100;
        $('#progress').css('width', progress + '%');
    }

    function validateStep(step) {
        var $step = $(steps[step]);
        var isValid = true;

        $step.find(':input[required]:visible').each(function() {
            var $input = $(this);
            if ($input.is('input[type="text"], input[type="email"], input[type="tel"], input[type="number"], textarea')) {
                if (!$input.val().trim()) isValid = false;
            } else if ($input.is('select')) {
                if ($input.val() === '') isValid = false;
            } else if ($input.is('input[type="radio"]')) {
                if (!$step.find('input[name="' + $input.attr('name') + '"]:checked').length) isValid = false;
            }
        });

        $step.find('.checkbox-group').each(function() {
            var $group = $(this);
            var groupName = $group.data('name');
            var $checkboxes = $group.find('input[name="' + groupName + '"]');
            var isChecked = $checkboxes.filter(':checked').length > 0;

            if (groupName === 'food_allergies[]' || groupName === 'food_intolerances[]' || groupName === 'past_medical_conditions[]') {
                var noneChecked = $checkboxes.filter('[value="None"]').is(':checked');
                var otherChecked = $checkboxes.not('[value="None"]').filter(':checked').length > 0;
                if ((noneChecked && otherChecked) || (!noneChecked && !otherChecked)) isValid = false;
            } else {
                if (!isChecked) isValid = false;
            }
        });

        return isValid;
    }

    $('.next-step').click(function() {
        if (currentStep < steps.length - 1) {
            if (validateStep(currentStep)) {
                $(steps[currentStep]).hide();
                currentStep++;
                $(steps[currentStep]).show();
                updateProgress();
                $('#error-message').hide();
            } else {
                $('#error-message').show();
            }
        }
    });

    $('.prev-step').click(function() {
        if (currentStep > 0) {
            $(steps[currentStep]).hide();
            currentStep--;
            $(steps[currentStep]).show();
            updateProgress();
            $('#error-message').hide();
        }
    });

    $('select, input[type="radio"], input[type="checkbox"]').change(function() {
        var $this = $(this);
        var conditionName = $this.attr('name');
        var conditionValue = $this.val();
        var isChecked = $this.is(':checked');

        // Radio/select
        if ($this.is('select') || $this.is('input[type="radio"]')) {
            $('.conditional-field[data-condition="' + conditionName + '"]').each(function() {
                if ($(this).data('value') === conditionValue) {
                    $(this).show().prop('required', true);
                } else {
                    $(this).hide().prop('required', false).val('');
                }
            });
        }

        // Checkbox
        if ($this.is('input[type="checkbox"]')) {
            var $conditionalField = $('.conditional-field[data-condition="' + conditionName + '"]');
            $conditionalField.each(function() {
                var $field = $(this);
                var targetValue = $field.data('value');
                var isTargetChecked = $('input[name="' + conditionName + '"][value="' + targetValue + '"]').is(':checked');
                $field.toggle(isTargetChecked).prop('required', isTargetChecked);
                if (!isTargetChecked) $field.val('');
            });
        }
    });

    updateProgress();
});
</script>


    <script>
document.addEventListener('DOMContentLoaded', function () {
    const conditionalCheckboxes = [
        { name: 'past_medical_conditions', otherName: 'past_medical_conditions_other' },
        { name: 'food_allergies', otherName: 'food_allergies_other' },
        { name: 'food_intolerances', otherName: 'food_intolerances_other' },
        { name: 'current_activity_types', otherName: 'activity_type_other' },
        { name: 'favorite_fruits', otherName: 'favorite_fruits_other' },
        { name: 'disliked_fruits', otherName: 'disliked_fruits_other' },
        { name: 'favorite_vegetables', otherName: 'favorite_vegetables_other' },
        { name: 'disliked_vegetables', otherName: 'disliked_vegetables_other' },
        { name: 'grocery_store', otherName: 'grocery_store_other' },
        { name: 'grocery_delivery_service', otherName: 'grocery_delivery_service_other' }
    ];

    conditionalCheckboxes.forEach(group => {
        const checkboxes = document.querySelectorAll(`input[name="${group.name}[]"]`);
        const otherInput = document.querySelector(`input[name="${group.otherName}"]`);

        if (!otherInput) return;

        function toggleOtherInput() {
            const isChecked = Array.from(checkboxes).some(cb => cb.value === 'Other' && cb.checked);
            otherInput.style.display = isChecked ? 'block' : 'none';
            otherInput.required = isChecked;
            if (!isChecked) otherInput.value = '';
        }

        checkboxes.forEach(cb => cb.addEventListener('change', toggleOtherInput));
        toggleOtherInput();
    });
});
</script>


    
    <?php
    // Return the buffered content
    return ob_get_clean();
}

// Register the shortcode
add_shortcode('sff_client_intake', 'sff_client_intake_form');

function sff_client_leads_list_shortcode() {
    // Check if the user is logged i

    if (!is_user_logged_in()) {
        return sff_custom_login_form();
    }

    // Fetch all client leads
    // $args = [
    //     'post_type'      => 'client_leads',
    //     'posts_per_page' => -1, // Get all leads
    //     'post_status'    => 'publish',
    //     'orderby'        => 'title',
    //     'order'          => 'ASC',
    // ];

    $args = [
    'post_type'      => 'client_leads',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'orderby'        => 'title',
    'order'          => 'ASC',
    'meta_query'     => [
        [
            'key'     => 'converted_to_client',
            'compare' => 'NOT EXISTS',
            ]
        ]
    ];


    $leads = get_posts($args);

    // If no leads are found, display a message
    if (empty($leads)) {
        return '<p style="text-align:center; font-size:18px; color:#777;">No client leads found.</p>';
    }

    // Define the logo URL (consistent with other shortcodes)
    $logo_url = 'https://simplifiedfoodandfitness.com/wp-content/uploads/2024/10/3.png';

    // Start output buffering
    ob_start(); ?>

    <div class="dashboard-container" style="max-width:1200px; margin:auto; padding:20px; font-family:'Inter', sans-serif;">
        <!-- Header with Logo -->
        <div style="display:flex; align-items:center; justify-content:space-between; gap:15px; flex-wrap:wrap; margin-bottom:20px;">
            <div style="flex-shrink:0;">
                <img src="<?php echo esc_url($logo_url); ?>" alt="Logo" style="height:60px; width:auto; max-width:200px;">
            </div>
            <h2 style="font-size:22px; color:#333; text-align:center; font-weight:700; flex:1; text-align:right;">
                Client Leads
            </h2>
        </div>

        <!-- Client Leads List -->
        <div class="sff-lead-container">
            <h2 class="sff-lead-title">All Client Leads</h2>
            <div class="sff-lead-section">
                <h3>Client List</h3>
                <?php $counter = 1; // Initialize counter ?>
                <?php foreach ($leads as $lead) : 
                    // Get the lead's permalink
                    $lead_url = get_permalink($lead->ID);
                    $lead_name = esc_html($lead->post_title);
                ?>
                    <div class="sff-lead-card">
                        <label><?php echo $counter; ?>.</label>
                        <span>
                            <a href="<?php echo esc_url($lead_url); ?>" style="color:#42b14c; text-decoration:none;">
                                <?php echo $lead_name; ?>
                            </a>
                        </span>
                    </div>
                    <?php $counter++; // Increment counter ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php
    return ob_get_clean();
}

// Register the shortcode
add_shortcode('sff_client_leads_list', 'sff_client_leads_list_shortcode');





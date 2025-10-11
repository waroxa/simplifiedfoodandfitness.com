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

// Dashboard showing meal plans assigned via _sff_assigned_users
function sff_meal_dashboard_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>Please log in to view your meal plan.</p>';
    }

    $user_id = get_current_user_id();

    $meal_posts = get_posts([
        'post_type'      => 'meal_plan',
        'numberposts'    => -1,
        'meta_query'     => [
            [
                'key'     => '_sff_assigned_users',
                'value'   => '"' . $user_id . '"',
                'compare' => 'LIKE',
            ],
        ],
    ]);

    if (!$meal_posts) {
        return '<p>No meal plans assigned.</p>';
    }

    $meals = [];
    $ingredients_text = '';

    foreach ($meal_posts as $meal_post) {
        $data = get_post_meta($meal_post->ID, '_sff_meal_data', true);
        if (!$data || !is_array($data)) {
            continue;
        }

        $data['id']    = $meal_post->ID;
        $data['image'] = get_the_post_thumbnail_url($meal_post, 'medium') ?: '';
        $meals[]       = $data;

        if (!empty($data['ingredients'])) {
            $ingredients_text .= $data['ingredients'] . ' ';
        }
    }

    if (!$meals) {
        return '<p>No meal plans assigned.</p>';
    }

    $progress = get_user_meta($user_id, 'sff_meal_progress', true);
    if (!is_array($progress)) {
        $progress = [];
    }

    $total     = count($meals);
    $completed = count(array_intersect(array_column($meals, 'id'), $progress));

    ob_start();
    ?>
    <div class="sff-dashboard">
        <div class="sff-progress">
            <progress id="sff-progress-bar" value="<?php echo esc_attr($completed); ?>" max="<?php echo esc_attr($total); ?>"></progress>
            <span id="sff-progress-text"><?php echo esc_html("$completed/$total meals completed"); ?></span>
        </div>

        <div class="sff-meals">
            <?php foreach ($meals as $meal) : ?>
                <div class="sff-meal-entry">
                    <?php echo sff_generate_meal_cards(wp_json_encode([$meal])); ?>
                    <?php $checked = in_array($meal['id'], $progress) ? 'checked' : ''; ?>
                    <label class="sff-meal-check">
                        <input type="checkbox" class="sff-meal-progress" data-meal-id="<?php echo esc_attr($meal['id']); ?>" <?php echo $checked; ?> />
                        Completed
                    </label>
                </div>
            <?php endforeach; ?>
        </div>

        <h3>Grocery List</h3>
        <?php echo sff_generate_grocery_list($ingredients_text); ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('sff_meal_dashboard', 'sff_meal_dashboard_shortcode');


function sff_frontend_ingredient_page() {
    if (!is_user_logged_in()) {
        return sff_custom_login_form();
    }

    $user     = wp_get_current_user();
    $username = $user->display_name ?: $user->user_login;
    $day_type = 'Rest Day';

    ob_start(); ?>
    <div class="dashboard-container" style="max-width:600px; margin:auto; padding:20px; font-family:'Segoe UI', Arial, sans-serif;">
        <?php echo sff_render_header($username, $day_type); ?>
        <?php echo sff_render_ingredient_form(); ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('sff_add_ingredient', 'sff_frontend_ingredient_page');

function sff_render_header($username, $day_type) {
    $logo_url = 'https://simplifiedfoodandfitness.com/wp-content/uploads/2024/10/3.png';
    ob_start(); ?>
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
                    <li><a href="<?php echo esc_url( home_url( '/my-ingredients/' ) ); ?>">My Ingredients</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/add-ingredient/' ) ); ?>">Add Ingredient</a></li>
                    <li><a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>">Logout</a></li>
                </ul>
            </nav>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function sff_personal_ingredients_shortcode() {
    if (!is_user_logged_in()) {
        return sff_custom_login_form();
    }

    $user      = wp_get_current_user();
    $username  = $user->display_name ?: $user->user_login;
    $day_type  = 'Rest Day';
    $user_id   = get_current_user_id();

    $search_term = isset($_GET['sff_ingredient_search']) ? sanitize_text_field(wp_unslash($_GET['sff_ingredient_search'])) : '';
    $paged       = isset($_GET['sff_page']) ? max(1, intval($_GET['sff_page'])) : 1;

    $query_args = [
        'post_type'      => 'ingredient',
        'post_status'    => 'publish',
        'posts_per_page' => 12,
        'paged'          => $paged,
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
    ];

    if ($search_term) {
        $query_args['s'] = $search_term;
    }

    $ingredient_query = new WP_Query($query_args);

    $all_ingredient_ids = get_posts([
        'post_type'      => 'ingredient',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'meta_query'     => [
            [
                'key'     => '_sff_owner_id',
                'value'   => $user_id,
                'compare' => '=',
                'type'    => 'NUMERIC',
            ],
        ],
    ]);

    $total_ingredients = is_array($all_ingredient_ids) ? count($all_ingredient_ids) : 0;

    $category_lookup = [];
    if (!empty($all_ingredient_ids)) {
        foreach ($all_ingredient_ids as $ingredient_id) {
            $terms = wp_get_post_terms($ingredient_id, 'ingredient_category', ['fields' => 'names']);
            if (is_wp_error($terms)) {
                continue;
            }
            foreach ($terms as $term_name) {
                $category_lookup[$term_name] = true;
            }
        }
    }

    $category_count = count($category_lookup);

    $latest_ingredient = get_posts([
        'post_type'      => 'ingredient',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'orderby'        => 'modified',
        'order'          => 'DESC',
        'meta_query'     => [
            [
                'key'     => '_sff_owner_id',
                'value'   => $user_id,
                'compare' => '=',
                'type'    => 'NUMERIC',
            ],
        ],
    ]);

    $last_updated = '';
    if (!empty($latest_ingredient)) {
        $last_updated = get_post_modified_time(get_option('date_format') . ' ' . get_option('time_format'), false, $latest_ingredient[0], true);
    }

    ob_start();
    ?>
    <div class="dashboard-container" style="max-width:1024px; margin:auto; padding:20px; font-family:'Segoe UI', Arial, sans-serif;">
        <?php echo sff_render_header($username, $day_type); ?>

        <div class="sff-personal-library">
            <div class="sff-personal-library-hero">
                <div class="sff-personal-library-copy">
                    <h2><?php esc_html_e('My Ingredient Library', 'simplified-food-fitness'); ?></h2>
                    <p><?php esc_html_e('Curate, refine, and reuse the ingredients that power every client recipe.', 'simplified-food-fitness'); ?></p>
                </div>
                <div class="sff-personal-library-stats">
                    <div class="sff-personal-stat-card">
                        <span class="sff-personal-stat-label"><?php esc_html_e('Saved Ingredients', 'simplified-food-fitness'); ?></span>
                        <span class="sff-personal-stat-value"><?php echo esc_html(number_format_i18n($total_ingredients)); ?></span>
                        <span class="sff-personal-stat-hint"><?php esc_html_e('Unique entries in your library', 'simplified-food-fitness'); ?></span>
                    </div>
                    <div class="sff-personal-stat-card">
                        <span class="sff-personal-stat-label"><?php esc_html_e('Active Categories', 'simplified-food-fitness'); ?></span>
                        <span class="sff-personal-stat-value"><?php echo esc_html(number_format_i18n($category_count)); ?></span>
                        <span class="sff-personal-stat-hint"><?php esc_html_e('Organize ingredients by purpose', 'simplified-food-fitness'); ?></span>
                    </div>
                    <div class="sff-personal-stat-card">
                        <span class="sff-personal-stat-label"><?php esc_html_e('Last Updated', 'simplified-food-fitness'); ?></span>
                        <span class="sff-personal-stat-value">
                            <?php echo $last_updated ? esc_html($last_updated) : esc_html__('Not yet added', 'simplified-food-fitness'); ?>
                        </span>
                        <span class="sff-personal-stat-hint"><?php esc_html_e('Keep your nutrition data current', 'simplified-food-fitness'); ?></span>
                    </div>
                </div>
            </div>

            <form class="sff-personal-library-search" method="get" action="">
                <label for="sff_ingredient_search" class="screen-reader-text"><?php esc_html_e('Search your ingredients', 'simplified-food-fitness'); ?></label>
                <div class="sff-personal-library-toolbar">
                    <div class="sff-personal-library-search-row">
                        <span aria-hidden="true">🔍</span>
                        <input type="search" id="sff_ingredient_search" name="sff_ingredient_search" value="<?php echo esc_attr($search_term); ?>" placeholder="<?php esc_attr_e('Search by ingredient name', 'simplified-food-fitness'); ?>" />
                        <?php
                        $current_page = get_queried_object();
                        if ($current_page instanceof WP_Post) {
                            echo '<input type="hidden" name="page_id" value="' . esc_attr($current_page->ID) . '">';
                        }
                        ?>
                    </div>
                    <div class="sff-personal-library-actions">
                        <?php if ($search_term) : ?>
                            <a class="button button-secondary" href="<?php echo esc_url(remove_query_arg(['sff_ingredient_search', 'sff_page'])); ?>"><?php esc_html_e('Clear search', 'simplified-food-fitness'); ?></a>
                        <?php endif; ?>
                        <a class="button button-primary" href="<?php echo esc_url(home_url('/add-ingredient/')); ?>"><?php esc_html_e('Add new ingredient', 'simplified-food-fitness'); ?></a>
                    </div>
                </div>
            </form>

            <?php if ($ingredient_query->found_posts > 0) : ?>
                <?php
                $range_start = ($paged - 1) * $ingredient_query->get('posts_per_page') + 1;
                $range_end   = $range_start + $ingredient_query->post_count - 1;
                $range_end   = min($range_end, $ingredient_query->found_posts);
                ?>
                <div class="sff-personal-library-meta">
                    <span>
                        <?php
                        /* translators: 1: range start, 2: range end, 3: total ingredients */
                        printf(esc_html__('Showing %1$s – %2$s of %3$s ingredients', 'simplified-food-fitness'), esc_html(number_format_i18n($range_start)), esc_html(number_format_i18n($range_end)), esc_html(number_format_i18n($ingredient_query->found_posts)));
                        ?>
                    </span>
                    <?php if ($search_term) : ?>
                        <span class="sff-personal-library-filter"><?php printf(esc_html__('Filtered by “%s”', 'simplified-food-fitness'), esc_html($search_term)); ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($ingredient_query->have_posts()) : ?>
                <ul class="sff-personal-library-list">
                    <?php
                    while ($ingredient_query->have_posts()) :
                        $ingredient_query->the_post();
                        $ingredient_id = get_the_ID();
                        $terms         = wp_get_post_terms($ingredient_id, 'ingredient_category', ['number' => 1]);
                        $category_name = (!is_wp_error($terms) && !empty($terms)) ? $terms[0]->name : __('Uncategorized', 'simplified-food-fitness');
                        $last_updated  = get_the_modified_date(get_option('date_format') . ' ' . get_option('time_format'));
                        $edit_link     = current_user_can('edit_post', $ingredient_id) ? get_edit_post_link($ingredient_id, '') : '';
                        $macro_snapshot = get_post_meta($ingredient_id, '_sff_macros', true);
                        $macro_snapshot = is_array($macro_snapshot) ? $macro_snapshot : [];
                        $highlight_fields = [
                            'calories' => __('Calories', 'simplified-food-fitness'),
                            'protein'  => __('Protein', 'simplified-food-fitness'),
                            'carbs'    => __('Carbs', 'simplified-food-fitness'),
                            'fat'      => __('Fat', 'simplified-food-fitness'),
                        ];
                        $unit_cost = get_post_meta($ingredient_id, '_sff_unit_cost', true);
                        $unit_cost = $unit_cost !== '' ? floatval($unit_cost) : null;
                        ?>
                        <li class="sff-personal-library-item">
                            <div class="sff-ingredient-card">
                                <div class="sff-ingredient-card__header">
                                    <div>
                                        <h3><?php the_title(); ?></h3>
                                        <span class="sff-personal-library-category"><?php echo esc_html($category_name); ?></span>
                                    </div>
                                    <span class="sff-ingredient-updated"><?php printf(esc_html__('Updated %s', 'simplified-food-fitness'), esc_html($last_updated)); ?></span>
                                </div>

                                <div class="sff-ingredient-card__macros">
                                    <?php foreach ($highlight_fields as $field => $label) :
                                        $value = isset($macro_snapshot[$field]) ? floatval($macro_snapshot[$field]) : 0;
                                        $unit  = $field === 'calories' ? __('kcal', 'simplified-food-fitness') : __('g', 'simplified-food-fitness');
                                        $precision = $field === 'calories' ? 0 : 1;
                                        ?>
                                        <div class="sff-ingredient-macro">
                                            <span class="sff-ingredient-macro-label"><?php echo esc_html($label); ?></span>
                                            <span class="sff-ingredient-macro-value"><?php echo esc_html(number_format_i18n($value, $precision)); ?> <span><?php echo esc_html($unit); ?></span></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <?php if ($unit_cost !== null) : ?>
                                    <p class="sff-ingredient-cost"><?php printf(esc_html__('Unit cost: $%s', 'simplified-food-fitness'), esc_html(number_format_i18n($unit_cost, 2))); ?></p>
                                <?php endif; ?>

                                <div class="sff-personal-library-actions">
                                    <?php if ($edit_link) : ?>
                                        <a class="button" href="<?php echo esc_url($edit_link); ?>"><?php esc_html_e('Edit ingredient', 'simplified-food-fitness'); ?></a>
                                    <?php endif; ?>
                                    <a class="button button-secondary" href="<?php echo esc_url(get_permalink($ingredient_id)); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Preview details', 'simplified-food-fitness'); ?></a>
                                </div>
                            </div>
                        </li>
                        <?php
                    endwhile;
                    ?>
                </ul>

                <?php
                $total_pages = intval($ingredient_query->max_num_pages);
                if ($total_pages > 1) {
                    $base_url = remove_query_arg('sff_page');
                    $base_url = add_query_arg('sff_page', '%#%', $base_url);
                    $pagination = paginate_links([
                        'base'      => esc_url_raw($base_url),
                        'format'    => '',
                        'current'   => $paged,
                        'total'     => $total_pages,
                        'type'      => 'list',
                        'add_args'  => $search_term ? ['sff_ingredient_search' => $search_term] : [],
                    ]);

                    if ($pagination) {
                        echo '<nav class="sff-personal-library-pagination" aria-label="Ingredient pagination">' . wp_kses_post($pagination) . '</nav>';
                    }
                }
                ?>
            <?php else : ?>
                <div class="sff-personal-library-empty">
                    <h3><?php esc_html_e('You haven’t added any ingredients yet.', 'simplified-food-fitness'); ?></h3>
                    <p><?php esc_html_e('Start by scanning a label or entering details manually to build your personal ingredient library.', 'simplified-food-fitness'); ?></p>
                    <a class="button button-primary" href="<?php echo esc_url( home_url( '/add-ingredient/' ) ); ?>"><?php esc_html_e('Add your first ingredient', 'simplified-food-fitness'); ?></a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php

    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('sff_personal_ingredients', 'sff_personal_ingredients_shortcode');

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
            'sff_favorite_red_meat'        => 'Favorite Red Meat',
            'sff_favorite_red_meat_other'  => 'Favorite Red Meat (Other)',
            'sff_disliked_red_meat'        => 'Disliked Red Meat',
            'sff_disliked_red_meat_other'  => 'Disliked Red Meat (Other)',
            'sff_favorite_poultry'         => 'Favorite Poultry',
            'sff_favorite_poultry_other'   => 'Favorite Poultry (Other)',
            'sff_disliked_poultry'         => 'Disliked Poultry',
            'sff_disliked_poultry_other'   => 'Disliked Poultry (Other)',
            'sff_favorite_pork'            => 'Favorite Pork',
            'sff_favorite_pork_other'      => 'Favorite Pork (Other)',
            'sff_disliked_pork'            => 'Disliked Pork',
            'sff_disliked_pork_other'      => 'Disliked Pork (Other)',
            'sff_favorite_fish'            => 'Favorite Fish',
            'sff_favorite_fish_other'      => 'Favorite Fish (Other)',
            'sff_disliked_fish'            => 'Disliked Fish',
            'sff_disliked_fish_other'      => 'Disliked Fish (Other)',
            'sff_favorite_seafood'         => 'Favorite Seafood',
            'sff_favorite_seafood_other'   => 'Favorite Seafood (Other)',
            'sff_disliked_seafood'         => 'Disliked Seafood',
            'sff_disliked_seafood_other'   => 'Disliked Seafood (Other)',
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
        <?php echo sff_render_header($username, $day_type); ?>

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

    // 🔥 Fetch this client's Macro Target post
    $args = array(
        'post_type'      => 'macro_target',
        'author'         => $client_id,
        'post_status'    => 'publish',
        'posts_per_page' => 1,
    );
    $macro_post   = get_posts($args);
    $macro_post_id = (!empty($macro_post) && isset($macro_post[0]->ID)) ? $macro_post[0]->ID : null;

    $macro_targets = $macro_post_id ? get_post_meta($macro_post_id, '_macro_targets', true) : [];
    if (!is_array($macro_targets)) {
        $macro_targets = [];
    }

    // 🔥 Fetch saved macro percentages or use defaults
    $carb_percent    = $macro_targets['carb_percent'] ?? ($macro_post_id ? get_post_meta($macro_post_id, 'carb_percent', true) : '');
    $protein_percent = $macro_targets['protein_percent'] ?? ($macro_post_id ? get_post_meta($macro_post_id, 'protein_percent', true) : '');
    $fat_percent     = $macro_targets['fat_percent'] ?? ($macro_post_id ? get_post_meta($macro_post_id, 'fat_percent', true) : '');

    if ($carb_percent === '' || $carb_percent === null) {
        $carb_percent = 40; // Default from ajax.php
    }
    if ($protein_percent === '' || $protein_percent === null) {
        $protein_percent = 20; // Default
    }
    if ($fat_percent === '' || $fat_percent === null) {
        $fat_percent = 20; // Default
    }

    // 🔥 Fetch total calories or fallback to 2000
    $total_calories = $macro_targets['calories'] ?? ($macro_post_id ? get_post_meta($macro_post_id, 'calories', true) : 2000);
    if ($total_calories === '' || $total_calories === null) {
        $total_calories = 2000; // fallback default
    }

    // 🔥 Use stored grams or compute from percentages
    $carbs_goal_g   = $macro_targets['carbs']   ?? ($total_calories * $carb_percent / 400);
    $protein_goal_g = $macro_targets['protein'] ?? ($total_calories * $protein_percent / 400);
    $fat_goal_g     = $macro_targets['fats']    ?? ($total_calories * $fat_percent / 900);

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
        <?php echo sff_render_header($username, $day_type); ?>

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
    $macros = get_post_meta($post_id, '_macro_targets', true);
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

                <label style="font-weight:bold; margin-bottom:5px;">🥩 Favorite Red Meat</label>
                <div style="margin-bottom:15px; column-count:2;" class="checkbox-group" data-name="favorite_red_meat[]">
                    <label><input type="checkbox" name="favorite_red_meat[]" value="Beef"> Beef</label><br>
                    <label><input type="checkbox" name="favorite_red_meat[]" value="Bison"> Bison</label><br>
                    <label><input type="checkbox" name="favorite_red_meat[]" value="Lamb"> Lamb</label><br>
                    <label><input type="checkbox" name="favorite_red_meat[]" value="Goat"> Goat</label><br>
                    <label><input type="checkbox" name="favorite_red_meat[]" value="Veal"> Veal</label><br>
                    <label><input type="checkbox" name="favorite_red_meat[]" value="Venison"> Venison</label><br>
                    <label><input type="checkbox" name="favorite_red_meat[]" value="Beef roast"> Beef roast</label><br>
                    <label><input type="checkbox" name="favorite_red_meat[]" value="Steak"> Steak</label><br>
                    <label><input type="checkbox" name="favorite_red_meat[]" value="Ground beef"> Ground beef</label><br>
                    <label><input type="checkbox" name="favorite_red_meat[]" value="None"> None</label><br>
                    <label><input type="checkbox" name="favorite_red_meat[]" value="Other"> Other (Please specify)</label>
                </div>
                <input type="text" name="favorite_red_meat_other" placeholder="e.g., Elk" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px; display:none;" class="conditional-field" data-condition="favorite_red_meat[]">

                <label style="font-weight:bold; margin-bottom:5px;">🚫 Disliked Red Meat</label>
                <div style="margin-bottom:15px; column-count:2;" class="checkbox-group" data-name="disliked_red_meat[]">
                    <label><input type="checkbox" name="disliked_red_meat[]" value="Beef"> Beef</label><br>
                    <label><input type="checkbox" name="disliked_red_meat[]" value="Bison"> Bison</label><br>
                    <label><input type="checkbox" name="disliked_red_meat[]" value="Lamb"> Lamb</label><br>
                    <label><input type="checkbox" name="disliked_red_meat[]" value="Goat"> Goat</label><br>
                    <label><input type="checkbox" name="disliked_red_meat[]" value="Veal"> Veal</label><br>
                    <label><input type="checkbox" name="disliked_red_meat[]" value="Venison"> Venison</label><br>
                    <label><input type="checkbox" name="disliked_red_meat[]" value="Beef roast"> Beef roast</label><br>
                    <label><input type="checkbox" name="disliked_red_meat[]" value="Steak"> Steak</label><br>
                    <label><input type="checkbox" name="disliked_red_meat[]" value="Ground beef"> Ground beef</label><br>
                    <label><input type="checkbox" name="disliked_red_meat[]" value="None"> None</label><br>
                    <label><input type="checkbox" name="disliked_red_meat[]" value="Other"> Other (Please specify)</label>
                </div>
                <input type="text" name="disliked_red_meat_other" placeholder="e.g., Elk" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px; display:none;" class="conditional-field" data-condition="disliked_red_meat[]">

                <label style="font-weight:bold; margin-bottom:5px;">🍗 Favorite Poultry</label>
                <div style="margin-bottom:15px; column-count:2;" class="checkbox-group" data-name="favorite_poultry[]">
                    <label><input type="checkbox" name="favorite_poultry[]" value="Chicken breast"> Chicken breast</label><br>
                    <label><input type="checkbox" name="favorite_poultry[]" value="Chicken thighs"> Chicken thighs</label><br>
                    <label><input type="checkbox" name="favorite_poultry[]" value="Ground chicken"> Ground chicken</label><br>
                    <label><input type="checkbox" name="favorite_poultry[]" value="Chicken wings"> Chicken wings</label><br>
                    <label><input type="checkbox" name="favorite_poultry[]" value="Turkey breast"> Turkey breast</label><br>
                    <label><input type="checkbox" name="favorite_poultry[]" value="Ground turkey"> Ground turkey</label><br>
                    <label><input type="checkbox" name="favorite_poultry[]" value="Turkey sausage"> Turkey sausage</label><br>
                    <label><input type="checkbox" name="favorite_poultry[]" value="Duck"> Duck</label><br>
                    <label><input type="checkbox" name="favorite_poultry[]" value="Cornish hen"> Cornish hen</label><br>
                    <label><input type="checkbox" name="favorite_poultry[]" value="Quail"> Quail</label><br>
                    <label><input type="checkbox" name="favorite_poultry[]" value="None"> None</label><br>
                    <label><input type="checkbox" name="favorite_poultry[]" value="Other"> Other (Please specify)</label>
                </div>
                <input type="text" name="favorite_poultry_other" placeholder="e.g., Goose" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px; display:none;" class="conditional-field" data-condition="favorite_poultry[]">

                <label style="font-weight:bold; margin-bottom:5px;">🚫 Disliked Poultry</label>
                <div style="margin-bottom:15px; column-count:2;" class="checkbox-group" data-name="disliked_poultry[]">
                    <label><input type="checkbox" name="disliked_poultry[]" value="Chicken breast"> Chicken breast</label><br>
                    <label><input type="checkbox" name="disliked_poultry[]" value="Chicken thighs"> Chicken thighs</label><br>
                    <label><input type="checkbox" name="disliked_poultry[]" value="Ground chicken"> Ground chicken</label><br>
                    <label><input type="checkbox" name="disliked_poultry[]" value="Chicken wings"> Chicken wings</label><br>
                    <label><input type="checkbox" name="disliked_poultry[]" value="Turkey breast"> Turkey breast</label><br>
                    <label><input type="checkbox" name="disliked_poultry[]" value="Ground turkey"> Ground turkey</label><br>
                    <label><input type="checkbox" name="disliked_poultry[]" value="Turkey sausage"> Turkey sausage</label><br>
                    <label><input type="checkbox" name="disliked_poultry[]" value="Duck"> Duck</label><br>
                    <label><input type="checkbox" name="disliked_poultry[]" value="Cornish hen"> Cornish hen</label><br>
                    <label><input type="checkbox" name="disliked_poultry[]" value="Quail"> Quail</label><br>
                    <label><input type="checkbox" name="disliked_poultry[]" value="None"> None</label><br>
                    <label><input type="checkbox" name="disliked_poultry[]" value="Other"> Other (Please specify)</label>
                </div>
                <input type="text" name="disliked_poultry_other" placeholder="e.g., Goose" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px; display:none;" class="conditional-field" data-condition="disliked_poultry[]">

                <label style="font-weight:bold; margin-bottom:5px;">🥓 Favorite Pork</label>
                <div style="margin-bottom:15px; column-count:2;" class="checkbox-group" data-name="favorite_pork[]">
                    <label><input type="checkbox" name="favorite_pork[]" value="Pork tenderloin"> Pork tenderloin</label><br>
                    <label><input type="checkbox" name="favorite_pork[]" value="Pork chops"> Pork chops</label><br>
                    <label><input type="checkbox" name="favorite_pork[]" value="Pork shoulder"> Pork shoulder</label><br>
                    <label><input type="checkbox" name="favorite_pork[]" value="Ground pork"> Ground pork</label><br>
                    <label><input type="checkbox" name="favorite_pork[]" value="Pork loin"> Pork loin</label><br>
                    <label><input type="checkbox" name="favorite_pork[]" value="Bacon"> Bacon</label><br>
                    <label><input type="checkbox" name="favorite_pork[]" value="Ham"> Ham</label><br>
                    <label><input type="checkbox" name="favorite_pork[]" value="Pork sausage"> Pork sausage</label><br>
                    <label><input type="checkbox" name="favorite_pork[]" value="Prosciutto"> Prosciutto</label><br>
                    <label><input type="checkbox" name="favorite_pork[]" value="Chorizo"> Chorizo</label><br>
                    <label><input type="checkbox" name="favorite_pork[]" value="None"> None</label><br>
                    <label><input type="checkbox" name="favorite_pork[]" value="Other"> Other (Please specify)</label>
                </div>
                <input type="text" name="favorite_pork_other" placeholder="e.g., Pancetta" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px; display:none;" class="conditional-field" data-condition="favorite_pork[]">

                <label style="font-weight:bold; margin-bottom:5px;">🚫 Disliked Pork</label>
                <div style="margin-bottom:15px; column-count:2;" class="checkbox-group" data-name="disliked_pork[]">
                    <label><input type="checkbox" name="disliked_pork[]" value="Pork tenderloin"> Pork tenderloin</label><br>
                    <label><input type="checkbox" name="disliked_pork[]" value="Pork chops"> Pork chops</label><br>
                    <label><input type="checkbox" name="disliked_pork[]" value="Pork shoulder"> Pork shoulder</label><br>
                    <label><input type="checkbox" name="disliked_pork[]" value="Ground pork"> Ground pork</label><br>
                    <label><input type="checkbox" name="disliked_pork[]" value="Pork loin"> Pork loin</label><br>
                    <label><input type="checkbox" name="disliked_pork[]" value="Bacon"> Bacon</label><br>
                    <label><input type="checkbox" name="disliked_pork[]" value="Ham"> Ham</label><br>
                    <label><input type="checkbox" name="disliked_pork[]" value="Pork sausage"> Pork sausage</label><br>
                    <label><input type="checkbox" name="disliked_pork[]" value="Prosciutto"> Prosciutto</label><br>
                    <label><input type="checkbox" name="disliked_pork[]" value="Chorizo"> Chorizo</label><br>
                    <label><input type="checkbox" name="disliked_pork[]" value="None"> None</label><br>
                    <label><input type="checkbox" name="disliked_pork[]" value="Other"> Other (Please specify)</label>
                </div>
                <input type="text" name="disliked_pork_other" placeholder="e.g., Pancetta" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px; display:none;" class="conditional-field" data-condition="disliked_pork[]">

                <label style="font-weight:bold; margin-bottom:5px;">🐟 Favorite Fish</label>
                <div style="margin-bottom:15px; column-count:2;" class="checkbox-group" data-name="favorite_fish[]">
                    <label><input type="checkbox" name="favorite_fish[]" value="Salmon"> Salmon</label><br>
                    <label><input type="checkbox" name="favorite_fish[]" value="Tuna"> Tuna</label><br>
                    <label><input type="checkbox" name="favorite_fish[]" value="Cod"> Cod</label><br>
                    <label><input type="checkbox" name="favorite_fish[]" value="Tilapia"> Tilapia</label><br>
                    <label><input type="checkbox" name="favorite_fish[]" value="Snapper"> Snapper</label><br>
                    <label><input type="checkbox" name="favorite_fish[]" value="Mahi mahi"> Mahi mahi</label><br>
                    <label><input type="checkbox" name="favorite_fish[]" value="Trout"> Trout</label><br>
                    <label><input type="checkbox" name="favorite_fish[]" value="Sardines"> Sardines</label><br>
                    <label><input type="checkbox" name="favorite_fish[]" value="Halibut"> Halibut</label><br>
                    <label><input type="checkbox" name="favorite_fish[]" value="Catfish"> Catfish</label><br>
                    <label><input type="checkbox" name="favorite_fish[]" value="None"> None</label><br>
                    <label><input type="checkbox" name="favorite_fish[]" value="Other"> Other (Please specify)</label>
                </div>
                <input type="text" name="favorite_fish_other" placeholder="e.g., Barramundi" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px; display:none;" class="conditional-field" data-condition="favorite_fish[]">

                <label style="font-weight:bold; margin-bottom:5px;">🚫 Disliked Fish</label>
                <div style="margin-bottom:15px; column-count:2;" class="checkbox-group" data-name="disliked_fish[]">
                    <label><input type="checkbox" name="disliked_fish[]" value="Salmon"> Salmon</label><br>
                    <label><input type="checkbox" name="disliked_fish[]" value="Tuna"> Tuna</label><br>
                    <label><input type="checkbox" name="disliked_fish[]" value="Cod"> Cod</label><br>
                    <label><input type="checkbox" name="disliked_fish[]" value="Tilapia"> Tilapia</label><br>
                    <label><input type="checkbox" name="disliked_fish[]" value="Snapper"> Snapper</label><br>
                    <label><input type="checkbox" name="disliked_fish[]" value="Mahi mahi"> Mahi mahi</label><br>
                    <label><input type="checkbox" name="disliked_fish[]" value="Trout"> Trout</label><br>
                    <label><input type="checkbox" name="disliked_fish[]" value="Sardines"> Sardines</label><br>
                    <label><input type="checkbox" name="disliked_fish[]" value="Halibut"> Halibut</label><br>
                    <label><input type="checkbox" name="disliked_fish[]" value="Catfish"> Catfish</label><br>
                    <label><input type="checkbox" name="disliked_fish[]" value="None"> None</label><br>
                    <label><input type="checkbox" name="disliked_fish[]" value="Other"> Other (Please specify)</label>
                </div>
                <input type="text" name="disliked_fish_other" placeholder="e.g., Barramundi" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px; display:none;" class="conditional-field" data-condition="disliked_fish[]">

                <label style="font-weight:bold; margin-bottom:5px;">🦐 Favorite Seafood</label>
                <div style="margin-bottom:15px; column-count:2;" class="checkbox-group" data-name="favorite_seafood[]">
                    <label><input type="checkbox" name="favorite_seafood[]" value="Shrimp"> Shrimp</label><br>
                    <label><input type="checkbox" name="favorite_seafood[]" value="Crab"> Crab</label><br>
                    <label><input type="checkbox" name="favorite_seafood[]" value="Lobster"> Lobster</label><br>
                    <label><input type="checkbox" name="favorite_seafood[]" value="Scallops"> Scallops</label><br>
                    <label><input type="checkbox" name="favorite_seafood[]" value="Mussels"> Mussels</label><br>
                    <label><input type="checkbox" name="favorite_seafood[]" value="Clams"> Clams</label><br>
                    <label><input type="checkbox" name="favorite_seafood[]" value="Oysters"> Oysters</label><br>
                    <label><input type="checkbox" name="favorite_seafood[]" value="Calamari"> Calamari</label><br>
                    <label><input type="checkbox" name="favorite_seafood[]" value="Octopus"> Octopus</label><br>
                    <label><input type="checkbox" name="favorite_seafood[]" value="Crayfish"> Crayfish</label><br>
                    <label><input type="checkbox" name="favorite_seafood[]" value="None"> None</label><br>
                    <label><input type="checkbox" name="favorite_seafood[]" value="Other"> Other (Please specify)</label>
                </div>
                <input type="text" name="favorite_seafood_other" placeholder="e.g., Sea urchin" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px; display:none;" class="conditional-field" data-condition="favorite_seafood[]">

                <label style="font-weight:bold; margin-bottom:5px;">🚫 Disliked Seafood</label>
                <div style="margin-bottom:15px; column-count:2;" class="checkbox-group" data-name="disliked_seafood[]">
                    <label><input type="checkbox" name="disliked_seafood[]" value="Shrimp"> Shrimp</label><br>
                    <label><input type="checkbox" name="disliked_seafood[]" value="Crab"> Crab</label><br>
                    <label><input type="checkbox" name="disliked_seafood[]" value="Lobster"> Lobster</label><br>
                    <label><input type="checkbox" name="disliked_seafood[]" value="Scallops"> Scallops</label><br>
                    <label><input type="checkbox" name="disliked_seafood[]" value="Mussels"> Mussels</label><br>
                    <label><input type="checkbox" name="disliked_seafood[]" value="Clams"> Clams</label><br>
                    <label><input type="checkbox" name="disliked_seafood[]" value="Oysters"> Oysters</label><br>
                    <label><input type="checkbox" name="disliked_seafood[]" value="Calamari"> Calamari</label><br>
                    <label><input type="checkbox" name="disliked_seafood[]" value="Octopus"> Octopus</label><br>
                    <label><input type="checkbox" name="disliked_seafood[]" value="Crayfish"> Crayfish</label><br>
                    <label><input type="checkbox" name="disliked_seafood[]" value="None"> None</label><br>
                    <label><input type="checkbox" name="disliked_seafood[]" value="Other"> Other (Please specify)</label>
                </div>
                <input type="text" name="disliked_seafood_other" placeholder="e.g., Sea urchin" style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px; display:none;" class="conditional-field" data-condition="disliked_seafood[]">

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
        { name: 'favorite_red_meat', otherName: 'favorite_red_meat_other' },
        { name: 'disliked_red_meat', otherName: 'disliked_red_meat_other' },
        { name: 'favorite_poultry', otherName: 'favorite_poultry_other' },
        { name: 'disliked_poultry', otherName: 'disliked_poultry_other' },
        { name: 'favorite_pork', otherName: 'favorite_pork_other' },
        { name: 'disliked_pork', otherName: 'disliked_pork_other' },
        { name: 'favorite_fish', otherName: 'favorite_fish_other' },
        { name: 'disliked_fish', otherName: 'disliked_fish_other' },
        { name: 'favorite_seafood', otherName: 'favorite_seafood_other' },
        { name: 'disliked_seafood', otherName: 'disliked_seafood_other' },
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





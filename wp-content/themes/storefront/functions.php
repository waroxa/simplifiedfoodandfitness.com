<?php
/**
 * Storefront engine room
 *
 * @package storefront
 */

/**
 * Assign the Storefront version to a var
 */
$theme              = wp_get_theme( 'storefront' );
$storefront_version = $theme['Version'];

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 980; /* pixels */
}

$storefront = (object) array(
	'version'    => $storefront_version,

	/**
	 * Initialize all the things.
	 */
	'main'       => require 'inc/class-storefront.php',
	'customizer' => require 'inc/customizer/class-storefront-customizer.php',
);

require 'inc/storefront-functions.php';
require 'inc/storefront-template-hooks.php';
require 'inc/storefront-template-functions.php';
require 'inc/wordpress-shims.php';

if ( class_exists( 'Jetpack' ) ) {
	$storefront->jetpack = require 'inc/jetpack/class-storefront-jetpack.php';
}

if ( storefront_is_woocommerce_activated() ) {
	$storefront->woocommerce            = require 'inc/woocommerce/class-storefront-woocommerce.php';
	$storefront->woocommerce_customizer = require 'inc/woocommerce/class-storefront-woocommerce-customizer.php';

	require 'inc/woocommerce/class-storefront-woocommerce-adjacent-products.php';

	require 'inc/woocommerce/storefront-woocommerce-template-hooks.php';
	require 'inc/woocommerce/storefront-woocommerce-template-functions.php';
	require 'inc/woocommerce/storefront-woocommerce-functions.php';
}

if ( is_admin() ) {
	$storefront->admin = require 'inc/admin/class-storefront-admin.php';

	require 'inc/admin/class-storefront-plugin-install.php';
}

/**
 * NUX
 * Only load if wp version is 4.7.3 or above because of this issue;
 * https://core.trac.wordpress.org/ticket/39610?cversion=1&cnum_hist=2
 */
if ( version_compare( get_bloginfo( 'version' ), '4.7.3', '>=' ) && ( is_admin() || is_customize_preview() ) ) {
	require 'inc/nux/class-storefront-nux-admin.php';
	require 'inc/nux/class-storefront-nux-guided-tour.php';
	require 'inc/nux/class-storefront-nux-starter-content.php';
}

/**
 * Note: Do not add any custom code here. Please use a custom plugin so that your customizations aren't lost during updates.
 * https://github.com/woocommerce/theme-customisations
 */

/**
 * Auto-create Macro Targets post for new Clients
 * This triggers when a user’s role changes (e.g., Lead → Client)
 */
add_action('set_user_role', function ($user_id, $new_role, $old_roles) {
    // Only create if user is becoming a Client
    if ($new_role === 'client') {
        // Check if a Macro Targets post already exists for this user
        $existing_post = get_posts(array(
            'post_type'      => 'macro_target', // Make sure this matches your CPT slug
            'author'         => $user_id,
            'post_status'    => 'publish',
            'posts_per_page' => 1,
        ));

        if (!$existing_post) {
            // Fetch user info
            $user = get_userdata($user_id);
            $client_name = $user ? $user->display_name : 'Client';

            // Create the Macro Targets post
            $post_id = wp_insert_post(array(
                'post_title'    => $client_name . ' – Macro Targets',
                'post_type'     => 'macro_target', // Make sure this matches your CPT
                'post_status'   => 'publish',
                'post_author'   => $user_id,
            ));

            // Save default macro and micro values
            update_post_meta($post_id, 'calories', 2000);          // Default Calories
            update_post_meta($post_id, 'carb_percent', 50);        // Default Carbs %
            update_post_meta($post_id, 'protein_percent', 30);     // Default Protein %
            update_post_meta($post_id, 'fat_percent', 20);         // Default Fats %
            update_post_meta($post_id, 'vitamin_c_mg', 75);        // Example micronutrient
            update_post_meta($post_id, 'iron_mg', 18);             // Example micronutrient

            // Optional: log or notify admin
            error_log("✅ Created Macro Targets for client: {$client_name} (User ID: {$user_id})");
        }
    }
}, 10, 3);

/**
 * Customize the login screen logo.
 */
function sff_custom_login_logo() {
    ?>
    <style>
        #login h1 a, .login h1 a {
            background-image: url('https://simplifiedfoodandfitness.com/wp-content/uploads/2024/10/3.png');
            width: 320px;
            height: 65px;
            background-size: contain;
            background-repeat: no-repeat;
            padding-bottom: 30px;
        }
    </style>
    <?php
}
add_action('login_enqueue_scripts', 'sff_custom_login_logo');

// Update login logo link and title.
add_filter('login_headerurl', function () {
    return home_url('/');
});
add_filter('login_headertext', function () {
    return 'Simplified Food and Fitness';
});

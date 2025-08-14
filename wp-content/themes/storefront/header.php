<?php
/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package storefront
 */

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<?php wp_body_open(); ?>

<?php do_action( 'storefront_before_site' ); ?>

<div id="page" class="hfeed site">
	<?php do_action( 'storefront_before_header' ); ?>

	<header id="masthead" class="site-header" role="banner" style="<?php storefront_header_styles(); ?>">

		<?php
		/**
		 * Functions hooked into storefront_header action
		 *
		 * @hooked storefront_header_container                 - 0
		 * @hooked storefront_skip_links                       - 5
		 * @hooked storefront_social_icons                     - 10
		 * @hooked storefront_site_branding                    - 20
		 * @hooked storefront_secondary_navigation             - 30
		 * @hooked storefront_product_search                   - 40
		 * @hooked storefront_header_container_close           - 41
		 * @hooked storefront_primary_navigation_wrapper       - 42
		 * @hooked storefront_primary_navigation               - 50
		 * @hooked storefront_header_cart                      - 60
		 * @hooked storefront_primary_navigation_wrapper_close - 68
		 */
                do_action( 'storefront_header' );
                ?>

               <div class="sff-app-menu">
                       <button id="sff-menu-toggle" class="sff-menu-toggle" aria-expanded="false" aria-controls="sff-menu">
                               <span class="bar"></span>
                               <span class="bar"></span>
                               <span class="bar"></span>
                       </button>
                       <nav id="sff-menu" class="sff-menu" aria-labelledby="sff-menu-toggle">
                               <ul>
                                       <?php if ( is_user_logged_in() ) : ?>
                                               <li><a href="<?php echo esc_url( admin_url( 'profile.php' ) ); ?>"><?php esc_html_e( 'Profile', 'storefront' ); ?></a></li>
                                       <?php else : ?>
                                               <li><a href="<?php echo esc_url( wp_login_url() ); ?>"><?php esc_html_e( 'Log in', 'storefront' ); ?></a></li>
                                       <?php endif; ?>
                               </ul>
                       </nav>
               </div>
               <style>
                       #masthead{position:relative;}
                       .sff-app-menu{position:absolute;top:1rem;right:1rem;}
                       .sff-menu-toggle{background:none;border:0;cursor:pointer;display:flex;flex-direction:column;justify-content:space-between;width:24px;height:18px;padding:0;}
                       .sff-menu-toggle .bar{display:block;width:100%;height:3px;background:#333;}
                       .sff-menu{display:none;position:absolute;top:100%;right:0;background:#fff;border:1px solid #ccc;border-radius:4px;padding:.5rem 1rem;}
                       .sff-menu.open{display:block;}
                       .sff-menu ul{list-style:none;margin:0;padding:0;}
                       .sff-menu li{margin:0;}
                       .sff-menu a{display:block;padding:.5rem 0;color:#333;text-decoration:none;}
               </style>
               <script>
               document.addEventListener('DOMContentLoaded',function(){var t=document.getElementById('sff-menu-toggle');var m=document.getElementById('sff-menu');if(t&&m){t.addEventListener('click',function(){var e=t.getAttribute('aria-expanded')==='true';t.setAttribute('aria-expanded',(!e).toString());m.classList.toggle('open');});}});
               </script>

        </header><!-- #masthead -->

	<?php
	/**
	 * Functions hooked in to storefront_before_content
	 *
	 * @hooked storefront_header_widget_region - 10
	 * @hooked woocommerce_breadcrumb - 10
	 */
	do_action( 'storefront_before_content' );
	?>

	<div id="content" class="site-content" tabindex="-1">
		<div class="col-full">

		<?php
		do_action( 'storefront_content_top' );

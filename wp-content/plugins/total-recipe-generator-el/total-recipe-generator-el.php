<?php
/**
 * Plugin Name: Total Recipe Generator for Elementor
 * Author:      SaurabhSharma
 * Author URI: 	http://codecanyon.net/user/saurabhsharma
 * Version:     3.1.0
 * Text Domain: trg_el
 * Domain Path: /languages/
 * Description: Total Recipe Generator is an extension add-on for the Elementor Page Builder. The plugin helps you create online recipe content with Schema microdata, nutrition facts table and nicely formatted recipe methods.
 * Elementor tested up to: 3.18.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Total_Recipe_Generator_El' ) ) {
    /**
     * Main Total_Recipe_Generator_El Class
     *
     */
    final class Total_Recipe_Generator_El {
/*
        function __construct() {

        } // construct*/


        private static $instance;

        /**
         * Main Total_Recipe_Generator_El Instance
         *
         */
        public static function instance() {

            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Total_Recipe_Generator_El ) ) {

                self::$instance = new Total_Recipe_Generator_El;

                self::$instance->trg_el_includes();

                self::$instance->trg_el_hooks();

            }
            return self::$instance;
        }

        /**
         * Throw error on object clone
         *
         */
        public function __clone() {
            // Cloning instances of the class is forbidden
            _doing_it_wrong( __FUNCTION__, __( 'Not allowed.', 'trg_el' ), '1.0' );
        }

        /**
         * Disable unserializing of the class
         *
         */
        public function __wakeup() {
            // Unserializing instances of the class is forbidden
            _doing_it_wrong( __FUNCTION__, __( 'Unserializing forbidden.', 'trg_el' ), '1.0' );
        }


        /**
         * Load Plugin Text Domain
         *
         */
        public function load_plugin_textdomain() {

            $lang_dir = apply_filters( 'trg_el_lang_dir', trailingslashit( plugin_dir_path(__FILE__) . 'languages' ) );

            // Native WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), 'trg_el' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'trg_el', $locale );

            // Paths to current locale file
            $mofile_local = $lang_dir . $mofile;

            if ( file_exists( $mofile_local ) ) {
                // wp-content/plugins/total-recipe-generator-el/languages/ folder
                load_textdomain( 'trg_el', $mofile_local );
            }
            else {
                // Load default language files
                load_plugin_textdomain( 'trg_el', false, $lang_dir );
            }

            return false;
        }

        public function trg_el_includes() {
            $plugin_dir = trailingslashit( plugin_dir_path( __FILE__ ) );
            require_once( $plugin_dir . 'includes/class.settings-api.php' );
            require_once( $plugin_dir . 'includes/settings.php' );
            require_once( $plugin_dir . 'includes/BFI_Thumb.php' );
            require_once( $plugin_dir . 'includes/helper-functions.php' );
        }

        /**
         * Setup the default hooks and actions
         */
        private function trg_el_hooks() {

            add_action( 'plugins_loaded', array( self::$instance, 'load_plugin_textdomain' ) );

            add_action( 'elementor/widgets/widgets_registered', array( self::$instance, 'include_widgets') );

            add_action( 'elementor/frontend/after_register_scripts', array( $this, 'register_frontend_scripts'), 20 );

            add_action( 'elementor/frontend/after_register_styles', array( self::$instance, 'register_frontend_styles' ), 10 );

            add_action( 'elementor/frontend/after_enqueue_styles', array( self::$instance, 'enqueue_frontend_styles' ), 10 );

            add_action( 'admin_notices',  array( self::$instance, 'trg_install_el_notice' ) );

            // Custom CSS in head
            add_action( 'wp_head',  array( self::$instance, 'trg_add_global_css' ) );
            add_filter( 'wp_kses_allowed_html', array( self::$instance, 'trg_allow_iframes_in_post' ) );

        }


        public function include_widgets( $widgets_manager ) {
            require_once trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/widget-trg.php';
            $widgets_manager->register_widget_type( new \Total_Recipe_Generator_El\Widgets\Widget_Total_Recipe_Generator_El() );
        }

	    /**
	     * Load Frontend Scripts
	     *
	     */
	    public function register_frontend_scripts() {

           // JavaScript files
           wp_register_script( 'trg-plugin-functions', plugin_dir_url( __FILE__ ) . 'assets/js/trg_frontend.js', array( 'jquery' ), '', true );

            // Localization
            $social = get_option( 'trg_social' );
            $trg_localization = array(
                'plugins_url' => plugins_url() . '/total-recipe-generator-el',
                'prnt_header' => isset( $social['prnt_header'] ) ? $social['prnt_header'] : '',
                'prnt_footer' => isset( $social['prnt_footer'] ) ?$social['prnt_footer'] : '',
            );
            wp_localize_script( 'trg-plugin-functions', 'trg_localize', $trg_localization );
        }

        /**
         * Load Frontend Styles
         *
         */
        public function register_frontend_styles() {

                wp_register_style( 'trg-plugin-css', plugin_dir_url( __FILE__ ) . 'assets/css/trg_frontend.css', array(), null );
                wp_register_style( 'trg-el-fontawesome-css', plugin_dir_url( __FILE__ ) . 'assets/css/all.min.css', array(), null );

                // RTL CSS
                if ( is_rtl() ) {
                    wp_register_style( 'trg-plugin-rtl', plugin_dir_url( __FILE__ ) . 'assets/css/rtl_trg_frontend.css', array(), null );
                }

        }

        // Load Frontend Styles
        public function enqueue_frontend_styles() {

            wp_enqueue_style( 'trg-plugin-css' );
            wp_enqueue_style( 'trg-el-fontawesome-css' );


            if ( is_rtl() ) {
                wp_enqueue_style( 'trg-plugin-rtl' );
            }

            wp_enqueue_script( 'trg-plugin-functions' );

        }


        // Add Global CSS from plugin settings
        function trg_add_global_css() {
            $colors = get_option( 'trg_display' );
            $css = '';
            // Icon color
            if ( isset( $colors ) && ! empty( $colors ) ) {
                if ( isset( $colors['icon_color'] ) && '' != $colors['icon_color'] ) {
                    $css .= '.recipe-heading > .trg-icon:before,.method-heading > .trg-icon:before{color:' . $colors['icon_color'] . ' ;}';
                }

                if ( $colors['heading_color'] && '' != $colors['heading_color'] ) {
                    $css .= '.trg-recipe .elementor-heading-title{color:' . $colors['heading_color'] . ' !important;}';
                }

                if ( isset( $colors['label_color'] ) && '' != $colors['label_color'] ) {
                    $css .= '.info-board>li .ib-label,.cuisine-meta .cm-label{color:' . $colors['label_color'] . ';}';
                }

                if ( isset( $colors['highlights'] ) && '' != $colors['highlights'] ) {
                    $css .= '.info-board>li .ib-value{color:' . $colors['highlights'] . ';}';
                }

                if ( isset( $colors['tick_color'] ) && '' != $colors['tick_color'] ) {
                    $css .= '.ing-list>li .trg-icon:before{color:' . $colors['tick_color'] . ';}';
                }

                if ( isset( $colors['count_color'] ) && '' != $colors['count_color'] ) {
                    $css .= '.step-num{color:' . $colors['count_color'] . ';}';
                }

                // Tags links CSS
                if ( isset( $colors['tags_bg'] ) && ( '' != $colors['tags_bg'] || '' != $colors['tags_color'] ) ) {
                    $css .= '.cuisine-meta .cm-value:not(.link-enabled),.cuisine-meta .cm-value a{';
                    $css .= ( '' != $colors['tags_bg'] ) ? 'background-color:' . $colors['tags_bg'] . ' !important;' : '';
                    $css .= ( '' != $colors['tags_color'] ) ? 'color:' . $colors['tags_color'] . ' !important;' : '';
                    $css .= 'box-shadow:none !important;}';
                }

                // Tags links hover CSS
                if ( isset( $colors['tags_bg_hover'] ) && ( '' != $colors['tags_bg_hover'] || '' != $colors['tags_color_hover'] ) ) {
                    $css .= '.cuisine-meta .cm-value a:hover,.cuisine-meta .cm-value a:active{';
                    $css .= ( '' != $colors['tags_bg_hover'] ) ? 'background-color:' . $colors['tags_bg_hover'] . ' !important;' : '';
                    $css .= ( '' != $colors['tags_color_hover'] ) ? 'color:' . $colors['tags_color_hover'] . ' !important;' : '';
                    $css .= '}';
                }

                // Social links CSS
                if ( isset( $colors['social_bg'] ) && ( '' != $colors['social_bg'] || '' != $colors['social_color'] ) ) {
                    $css .= '.trg-sharing li a{';
                    $css .= ( '' != $colors['social_bg'] ) ? 'background-color:' . $colors['social_bg'] . ' !important;' : '';
                    $css .= ( '' != $colors['social_color'] ) ? 'color:' . $colors['social_color'] . ' !important;' : '';
                    $css .= 'box-shadow:none !important;}';
                }

                // Social links hover CSS
                if ( isset( $colors['social_bg_hover'] ) && ( '' != $colors['social_bg_hover'] || '' != $colors['social_color_hover'] ) ) {
                    $css .= '.trg-sharing li a:hover{';
                    $css .= ( '' != $colors['social_bg_hover'] ) ? 'background-color:' . $colors['social_bg_hover'] . ' !important;' : '';
                    $css .= ( '' != $colors['social_color_hover'] ) ? 'color:' . $colors['social_color_hover'] . ' !important;' : '';
                    $css .= '}';
                }
            }
            if ( $css ) {
                echo '<style id="trg_global_css" type="text/css"> ' . $css . '</style>';
            }
        }

        // Allow iframe tag in wp_kses_allowed_html
        function trg_allow_iframes_in_post( $allowedtags ) {
            if ( ! current_user_can( 'publish_posts' ) ) return $allowedtags;
            // Allow iframes and the following attributes
            $allowedtags['iframe'] = array(
                'align' => true,
                'width' => true,
                'height' => true,
                'frameborder' => true,
                'name' => true,
                'src' => true,
                'id' => true,
                'class' => true,
                'style' => true,
                'scrolling' => true,
                'marginwidth' => true,
                'marginheight' => true,
            );
            return $allowedtags;
        }

        // Show notice if Elementor not installed
        function trg_install_el_notice() {
            if ( ! defined( 'ELEMENTOR_PLUGIN_BASE' ) ) {
                echo sprintf( '<div class="error"><p>%s</p></div>', esc_attr__( 'Total Recipe Generator plugin requires Elementor Page Builder. Kindly install and activate Elementor plugin.', 'trg_el' ) );
            }
            else {
                return;
            }
        }
    }
} // If not class exists

/**
 * Generate Instance
 */
function trg_el() {
    return Total_Recipe_Generator_El::instance();
}

trg_el();
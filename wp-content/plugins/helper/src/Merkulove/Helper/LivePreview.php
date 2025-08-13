<?php
namespace Merkulove\Helper;

/** Exit if accessed directly. */

use Merkulove\Helper\Unity\Plugin;
use Merkulove\Helper\Unity\Settings;

if ( ! defined( 'ABSPATH' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}

/**
 * SINGLETON: LivePreview class contain main plugin logic.
 *
 * @since 1.0.0
 *
 **/
final class LivePreview {

    /**
     * The one true LivePreview.
     *
     * @since 1.0.0
     * @access private
     * @var LivePreview
     **/
    private static $instance;

    public function __construct() {
        add_action( 'admin_footer', [$this, 'set_live_preview_popup'] );
    }

    /**
     * Set live preview popup
     *
     * @since 1.0.0
     * @access public
     */
    public function set_live_preview_popup() {
        global $pagenow;

        $excluded_tabs = [
            'ai',
            'ai_personalities',
            'custom_css',
            'assignments',
            'activation',
            'migration',
            'status',
            'updates'
        ];

        // Check if we are on the desired admin page
        if (
            ( $pagenow === 'admin.php' &&
            isset( $_GET['page'] ) &&
            $_GET['page'] === 'mdp_helper_settings' &&
            isset( $_GET['tab'] ) &&
            !in_array( $_GET['tab'], $excluded_tabs ) ) ||
            $pagenow === 'edit.php' &&
            isset( $_GET['page'] ) &&
            $_GET['page'] === 'mdp_helper_settings'
        ) {
          $this->create_live_preview();
        }
    }

    /**
     * Create live preview popup
     *
     * @since 1.0.0
     * @access public
     */
    private function create_live_preview() {
        LivePreview::get_instance()->set_initial_preview_options();
        $iframe_url = add_query_arg( array(
            'mdp-helper-preview' => 1
        ), get_site_url() );

        echo wp_sprintf(
            '<div class="mdp-helper-live-preview-box">
                <div class="mdp-helper-live-preview">
                    <iframe id="mdp-helper-live-preview" src="%s" width="100%%" 
                    frameborder="0" allow="autoplay; fullscreen"></iframe>
                </div>
                <div class="mdp-helper-live-preview-btn-wrapper">
                    <button class="mdp-helper-live-preview-open-button mdp-helper-live-preview-btn">
                        <i class="material-icons" aria-hidden="true">open_in_new</i>
                        <span>Live Preview</span>
                    </button>
                    <button class="mdp-helper-preview-close-button mdp-helper-live-preview-btn">
                        <i class="material-icons" aria-hidden="true">close</i>
                        <span>Close Preview</span>
                    </button>
                </div>
            </div>',
            $iframe_url
        );
    }

    /**
     * Set initial live preview options
     *
     * @since 1.0.0
     * @access public
     */
    public function set_initial_preview_options() {
        $tabs = Plugin::get_tabs();
        $defaults = Settings::get_instance()->get_default_settings();
        $results = [];
        $excluded_tabs = [
            'ai',
            'ai_personalities',
            'migration',
            'activation',
            'updates',
            'status',
            'custom_css',
            'assignments'
        ];

        /** Collect settings from each tab. */
        foreach ( $tabs as $tab_slug => $tab ) {
            if ( in_array( $tab_slug, $excluded_tabs ) ) { continue; }
            $options = get_option( "mdp_helper_{$tab_slug}_settings" );
            $results = wp_parse_args( $options, $defaults );
            $defaults = $results;
        }

        update_option( "mdp_helper_preview_settings", $results );
    }


    /**
     * Display helper preview template
     *
     * @since 1.0.0
     * @access public
     */
    public function helper_preview_template( $original_template ) {
        if ( isset( $_GET['mdp-helper-preview'] ) ) {
            return Plugin::get_path()  . 'src/Merkulove/Helper/helper-preview-template.php';
        } else {
            return $original_template;
        }
    }

    /**
     * Hides admin bar on preview page
     * @return bool
     * @since 1.0.0
     *
     * @access public
     */
    public function hide_admin_bar_on_preview( $show ) {
        if ( isset( $_GET['mdp-helper-preview'] ) ) {
            $show = false;
        }

        return $show;
    }

    /**
     * Main LivePreview Instance.
     * Insures that only one instance of Caster exists in memory at any one time.
     *
     * @static
     * @since 1.0.0
     * @access public
     *
     * @return LivePreview
     **/
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

            self::$instance = new self;

        }

        return self::$instance;

    }


}
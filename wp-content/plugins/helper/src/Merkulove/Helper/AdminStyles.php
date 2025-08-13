<?php
namespace Merkulove\Helper;

use Merkulove\Helper\Unity\Plugin;

/**
 * SINGLETON: Class adds front styles.
 *
 * @since 1.0.0
 *
 **/
final class AdminStyles {

    /**
     * The one true FrontStyles.
     *
     * @var AdminStyles
     * @since 1.0.0
     **/
    private static $instance;

    /**
     * Sets up a new AdminStyles instance.
     *
     * @since 1.0.0
     * @access public
     **/
    private function __construct() {

        /** Add admin styles. */
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_styles' ] );

    }

    /**
     * Add CSS for admin area.
     *
     * @since   1.0.0
     * @return void
     **/
    public function admin_styles() {

        /** User data records page. */
        $this->user_data_post_styles();

        /** Bot logs page */
        $this->bot_logs_post_styles();
    }



    /**
     * Styles for plugins page. "View version details" popup.
     *
     * @since 1.0.0
     * @access private
     *
     * @return void
     **/
    private function user_data_post_styles() {

        /** User data records page */
        $screen = get_current_screen();
        if ( null === $screen ) { return; }
        if ( 'mdp_user_data_cpt' !== $screen->id ) { return; }

        /** Styles only user data records page. */
        wp_enqueue_style(
            'mdp-admin-user-data',
            Plugin::get_url() . 'css/admin-user-data' . Plugin::get_suffix() . '.css',
            [],
            Plugin::get_version()
        );

    }

    /**
     * Styles for plugins page. "View version details" popup.
     *
     * @since 1.0.0
     * @access private
     *
     * @return void
     **/
    private function bot_logs_post_styles() {

        /** User data records page */
        $screen = get_current_screen();
        if ( null === $screen ) { return; }
        if ( 'mdp_bot_logs_cpt' !== $screen->id ) { return; }

        /** Styles only bot logs page. */
        wp_enqueue_style(
            'mdp-admin-bot-logs',
            Plugin::get_url() . 'css/admin-bot-logs' . Plugin::get_suffix() . '.css',
            [],
            Plugin::get_version()
        );

    }

    /**
     * Main AdminStyles Instance.
     *
     * Insures that only one instance of AdminStyles exists in memory at any one time.
     *
     * @static
     * @return AdminStyles
     **/
    public static function get_instance(): AdminStyles {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

            self::$instance = new self;

        }

        return self::$instance;

    }

}
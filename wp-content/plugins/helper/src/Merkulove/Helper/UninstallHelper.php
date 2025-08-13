<?php
/**
 * Helper
 * Create a chatbot with AI features for your website.
 * Exclusively on https://1.envato.market/helper
 *
 * @encoding        UTF-8
 * @version         1.0.25
 * @copyright       (C) 2018 - 2023 Merkulove ( https://merkulov.design/ ). All rights reserved.
 * @license         Envato License https://1.envato.market/KYbje
 * @contributors    Cherviakov Vlad (vladchervjakov@gmail.com), Nemirovskiy Vitaliy (nemirovskiyvitaliy@gmail.com), Dmytro Merkulov (dmitry@merkulov.design)
 * @support         help@merkulov.design
 **/

namespace Merkulove\Helper;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * SINGLETON: UninstallHelper class run when the users deletes the plugin.
 *
 * @since 1.0.0
 *
 **/
final class UninstallHelper {

	/**
	 * The one true UninstallHelper.
	 *
     * @since 1.0.0
     * @access private
	 * @var UninstallHelper
	 **/
	private static $instance;

    /**
     * Implement plugin uninstallation.
     *
     * @param string $uninstall_mode - Uninstall mode: plugin, plugin+settings, plugin+settings+data
     *
     * @since  1.0.0
     * @access public
     *
     * @return void
     **/
    public function uninstall( $uninstall_mode ) {

        /** Remove Plugin. */
        if ( 'plugin' === $uninstall_mode ) {

        /** Remove Plugin and Settings. */
        } elseif ( 'plugin+settings' === $uninstall_mode ) {

        /** Remove Plugin, Settings and all created data. */
        } elseif ( 'plugin+settings+data' === $uninstall_mode ) {

            /** Remove bot logs */
            $logs = get_posts( [
                'post_type' => 'mdp_bot_logs_cpt',
                'numberposts' => -1,
                'post_status' => [ 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash' ]
            ] );

            foreach ( $logs as $log ) {
                wp_delete_post( $log->ID, true );
            }

            /** Remove user data */
            $user_data = get_posts( [
                'post_type' => 'mdp_user_data_cpt',
                'numberposts' => -1,
                'post_status' => [ 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash' ]
            ] );

            foreach ( $user_data as $user_datum ) {
                wp_delete_post( $user_datum->ID, true );
            }
        }

    }

	/**
	 * Main UninstallHelper Instance.
	 * Insures that only one instance of UninstallHelper exists in memory at any one time.
	 *
	 * @static
     * @since 1.0.0
     * @access public
     *
	 * @return UninstallHelper
	 **/
	public static function get_instance(): UninstallHelper {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self;

		}

		return self::$instance;

	}

}

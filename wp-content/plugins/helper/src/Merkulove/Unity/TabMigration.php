<?php
/**
 * Helper
 * Create a chatbot with OpenAI artificial intelligence features for your website.
 * Exclusively on https://1.envato.market/helper
 *
 * @encoding        UTF-8
 * @version         1.0.25
 * @copyright       (C) 2018-2024 Merkulove ( https://merkulov.design/ ). All rights reserved.
 * @license         Envato License https://1.envato.market/KYbje
 * @contributors    Cherviakov Vlad (vladchervjakov@gmail.com), Nemirovskiy Vitaliy (nemirovskiyvitaliy@gmail.com), Dmytro Merkulov (dmitry@merkulov.design)
 * @support         help@merkulov.design
 **/

namespace Merkulove\Helper\Unity;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}

/**
 * SINGLETON: Class used to implement Uninstall tab on plugin settings page.
 *
 * @since 1.0.0
 *
 **/
final class TabMigration extends Tab {

    /**
     * Slug of current tab.
     *
     * @since 1.0.0
     * @const TAB_SLUG
     **/
    const TAB_SLUG = 'migration';

	/**
	 * The one true TabMigration.
	 *
     * @since 1.0.0
     * @access private
	 * @var TabMigration
	 **/
	private static $instance;

    private function __construct() {

        add_action( 'wp_ajax_export_settings_helper', [ $this, 'ajax_export_settings' ] );
        add_action( 'wp_ajax_import_settings_helper', [ $this, 'ajax_import_settings' ] );

    }

    /**
     * Render form with all settings fields.
     *
     * @since 1.0.0
     * @access public
     *
     * @return void
     **/
    public function do_settings() {

        /** No status tab, nothing to do. */
        if ( ! $this->is_enabled( self::TAB_SLUG ) ) { return; }

        /** Render title. */
        $this->render_title( self::TAB_SLUG );

        /** Render fields. */
        $this->do_settings_base( self::TAB_SLUG );

    }

	/**
	 * Generate Uninstall Tab.
	 *
	 * @access public
	 **/
	public function add_settings() {

        /** Custom Uninstall Tab. */
        $this->add_settings_base( self::TAB_SLUG );

        $group = 'Helper' . self::TAB_SLUG . 'OptionsGroup';
        $section = 'mdp_helper_' . self::TAB_SLUG . '_page_status_section';

        // Add migration controls only for general plugins
        if ( ! in_array( Plugin::get_type(), [ 'elementor', 'wpbakery', 'divi' ] ) ) {
            add_settings_field( 'import_settings', esc_html__( 'Import settings:', 'helper' ), [$this, 'render_import_settings'], $group, $section );
            add_settings_field( 'export_settings', esc_html__( 'Export settings:', 'helper' ), [$this, 'render_export_settings'], $group, $section );
        }
		add_settings_field( 'delete_plugin', esc_html__( 'Removal settings:', 'helper' ), [$this, 'render_delete_plugin'], $group, $section );

	}

	/**
	 * Render "Delete Plugin" field.
	 *
     * @since 1.0.0
	 * @access public
     *
     * @return void
	 **/
	public function render_delete_plugin() {

		/** Get uninstall settings. */
		$uninstall_settings = get_option( 'mdp_helper_migration_settings' );

		/** Set Default value 'plugin'. */
		if ( ! isset( $uninstall_settings[ 'delete_plugin' ] ) ) {
			$uninstall_settings = [
				'delete_plugin' => 'plugin'
			];
		}

		$options = Plugin::get_tabs()['migration']['fields']['delete_plugin']['options'];

		/** Prepare description. */
		$helper_text = esc_html__( 'Choose which data to delete upon using the "Delete" action in the "Plugins" admin page.', 'helper' );

		/** Render select. */
		UI::get_instance()->render_select(
			$options,
			$uninstall_settings[ 'delete_plugin' ], // Selected option.
			esc_html__( 'Delete plugin', 'helper' ),
			$helper_text,
			[
			    'name' => 'mdp_helper_migration_settings[delete_plugin]'
            ]
		);

	}

    /**
     * Render import settings group
     * @return void
     */
    public function render_import_settings() {

        UI::get_instance()->render_import(
            '',
            esc_html__( 'Import settings', 'helper' ),
            esc_html__( 'Drag & Drop file here', 'helper' ),
            'import_settings',
            'migration'
        );

    }

    /**
     * Render export settings group
     * @return void
     */
    public function render_export_settings() {

        $options = Settings::get_instance()->options;
        $tabs = Plugin::get_tabs();

        foreach ( $tabs as $slug => $tab ) {

            // Skip tabs from exporting
            if ( in_array( $slug, [ 'assignments', 'activation', 'status', 'updates', 'migration' ] ) ) {
                continue;
            }

            UI::get_instance()->render_switcher(
                $options[ 'export_' . $slug ] ?? 'off',
                $tab['label'] . ' ' . esc_html__( 'Tab', 'helper' ),
                esc_html__( 'Export all settings from', 'helper' ) . ' ' .  $tab['label'] . ' ' . esc_html__( 'tab', 'helper' ),
                [
                    "name" => wp_sprintf(
                        'mdp_helper_%s_settings[export_%s]',
                        self::TAB_SLUG,
                        $slug
                    )
                ]

            );

        }

        UI::get_instance()->render_button(
            esc_html__( 'Export settings', 'helper' ),
            '',
            'file_download',
            [
                "name" => 'mdp_helper_' . self::TAB_SLUG . '_settings' . "[export_selected]",
                "id" => "mdp-export-btn",
                "class" => "mdc-button--outlined"
            ]
        );

    }

    /**
     * Export settings export via ajax request
     * @return void
     */
    public function ajax_export_settings() {

        /** Check nonce for security. */
        check_ajax_referer( 'helper-unity', 'nonce' );

        $options = Settings::get_instance()->options;

        $export = array(
            'plugin_info' => array(
                'slug'      => Plugin::get_slug(),
                'version'   => Plugin::get_version(),
                'timestamp' => gmdate('l jS \of F Y h:i:s A'),
                'home'      => get_home_url()
            )
        );
        foreach ( $options as $opt => $val ) {

            if ( strpos( $opt,'export_' ) === 0 && $val === 'on' ) {

                $tab = str_replace( 'export_', '', $opt );
                $tab_options = get_option( 'mdp_helper_' . $tab . '_settings' );

                // Filtering option for export
                $filtered_option = array();
                foreach( $tab_options as $key => $option ) {

                    // Remove string options with multiline text
                    if ( is_string( $option ) && strpos( $option, '
' ) > 0 && $tab !== 'custom_css' ) {
                        continue;
                    }

                    $filtered_option[ $key ] = $option;

                }

                $export[ $tab ] = $filtered_option;

            }

        }

        wp_send_json( $export );

    }

    /**
     * Import settings export via ajax request
     * @return void
     */
    public function ajax_import_settings() {

        /** Check nonce for security. */
        check_ajax_referer( 'helper-unity', 'nonce' );

        // Get settings
        $import = isset( $_POST[ 'import' ] ) ? urldecode( $_POST[ 'import' ] ) : '';
        if ( $import === '' ) { self::import_errors( 1 ); }

        // Replace escaped symbols
        $import = str_replace( '\\r', '%newline%', $import);
        $import = str_replace( '\\n', '', $import);
        $import = str_replace( '\\t', '  ', $import);
        $import = str_replace( '\\','', $import);

        // Parse settings
        $settings = json_decode( $import, true );
        if ( ! is_array( $settings ) ) { self::import_errors( 2 ); }

        // Check if current import file can be used for current plugin
        self::check_plugin( $settings[ 'plugin_info' ] );

        // Parse and process settings
        foreach ( $settings as $tab => $set ) {

            // Skip service info
            if ( $tab === 'plugin_info' ) { continue; }

            if ( $set === false ) {

                // Reset to default
                delete_option( 'mdp_helper_' . $tab . '_settings' );

            } else {

                // Put back special characters
                if ( is_array( $set ) ) {

                    foreach ( $set as $key => $setting ) {

                        if ( strpos( '%newline%', $setting ) >= 0 ) {

                            $set[ $key ] = str_replace('%newline%',
                                '
', $setting );

                        }

                    }

                }

                // Update settings
                update_option( 'mdp_helper_' . $tab . '_settings', $set );

            }

        }

        // Return import status
        self::import_errors( 5 );
        wp_die();

    }

    /**
     * Check is current import set can be imported to current plugin
     * @param $plugin
     *
     * @return void
     */
    private function check_plugin( $plugin ) {

        if ( $plugin[ 'slug' ] !== Plugin::get_slug() ) { self::import_errors( 3, $plugin ); }
        if ( $plugin[ 'version' ] !== Plugin::get_version() ) { self::import_errors( 4, $plugin ); }

    }

    /**
     * Error hendler for ajax data import
     * @return void
     */
    private function import_errors( $e, $plugin = array() ) {

        switch ( $e ) {

            case 1:
                $message = array(
                    'status' => false,
                    'message' => esc_html__( 'No settings for import', 'helper' )
                );
                break;
            case 2:
                $message = array(
                    'status' => false,
                    'message' => esc_html__( 'Wrong settings format', 'helper' )
                );
                break;
            case 3:
                $message = array(
                    'status' => false,
                    'message' => esc_html__( 'You are importing settings for', 'helper' ) . ' ' . ucwords( $plugin[ 'slug' ] ) . esc_html__( ', but this is', 'helper' ) . ' ' . Plugin::get_name()
                );
                break;
            case 4:
                $message = array(
                    'status' => false,
                    'message' => esc_html__( 'You are importing settings for version', 'helper' ) . ' ' . ucwords( $plugin[ 'version' ] ) . ', but the current version is' . ' ' . Plugin::get_version()
                );
                break;
            case 5:
                $message = array(
                    'status' => true,
                    'message' => esc_html__( 'Settings imported', 'helper' )
                );
                break;
            default:
                $message = array(
                    'status' => false,
                    'message' => esc_html__( 'Unknown error', 'helper' )
                );

        }

        echo wp_json_encode( $message );
        wp_die();

    }


    /**
	 * Main TabMigration Instance.
	 * Insures that only one instance of TabMigration exists in memory at any one time.
	 *
	 * @static
	 * @return TabMigration
	 **/
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self;

		}

		return self::$instance;

	}

}

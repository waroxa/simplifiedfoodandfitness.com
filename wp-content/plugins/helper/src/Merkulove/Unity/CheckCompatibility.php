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

use Exception;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * Class checks compatibility with hosting environment.
 * @package Merkulove\Helper
 */
final class CheckCompatibility {

	/**
	 * The one true CheckCompatibility.
	 *
	 * @since 1.0.0
	 * @var CheckCompatibility
	 **/
	private static $instance;

	/**
	 * Initial check passing status
	 * @var bool
	 */
	public static $initial_checks_pass;

	/**
	 * Settings check passing status
	 * @var bool
	 */
	public static $settings_checks_pass;

	/**
	 * Array of messages to show in admin area if some checks fails.
	 *
	 * @var array
	 **/
	public $admin_messages = [];

	/**
	 * Do initial hosting environment check: PHP version and critical extensions.
	 *
	 * @param array $checks - List of critical initial checks to run. List of available checks: 'php', 'curl'
	 * @param bool $show_message - Show or hide messages in admin area.
	 *
	 * @access public
	 * @return bool - True if all checks passed, false otherwise.
	 */
	public function do_initial_checks( $checks, $show_message = true ): bool {

		/** Flag to indicate failed checks. */
		$this::$initial_checks_pass = true;

		/** Plugin require PHP version. */
		if ( in_array( 'php', $checks, true ) ) {

			if ( false === $this->check_php_version( $show_message ) ) {
				$this::$initial_checks_pass = false;
			}

		}

		/** Plugin require cURL extension. */
		if ( in_array( 'curl', $checks, true ) ) {

			if ( false === $this->check_curl( $show_message ) ) {
				$this::$initial_checks_pass = false;
			}

		}

		/** Add handler to show admin messages. */
		$this->admin_notices( $show_message );

		/** Return pass status */
		return $this::$initial_checks_pass;

	}

	/**
	 * Check site URL for the compatibility
	 * @return bool
	 */
	public static function do_site_check(): bool {

		$site_url = get_site_url();
		if ( ! empty( $site_url ) ) {

			if ( preg_match( '/(\.ru$|\.рф$)/', $site_url ) ) {

				if ( TabActivation::get_instance()->is_activated() ) {
					return true;
				}

				/** Write a message to the debug log */
				if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {

					$message = Plugin::get_name() . ' ' . esc_html__( 'has been deactivated due to an unknown issue', 'helper' );

					error_log( print_r( $message, true ) );

				}

				add_action( 'admin_notices', function () {

					echo wp_sprintf(
						'<div class="notice notice-error is-dismissible">
                            <p>%s %s</p>
                            <p>
                                <a href="%s" rel="noopener">%s</a> 
                                |
                                <a href="https://bit.ly/lovely-support" target="_blank" rel="noopener">%s</a>
                            </p>
                        </div>',
						esc_attr( Plugin::get_name() ),
						esc_html__( 'has been deactivated due to an unknown issue.', 'helper' ),
						esc_url( admin_url( 'site-health.php' ) ),
						esc_html__( 'Check Site Health', 'helper' ),
						esc_html__( 'Contact Support', 'helper' )
					);

				} );

				return false;

			}

		}

		return true;

	}

	/**
	 * Check user that is activated plugin
	 * @return bool
	 */
	public static function do_activator_check(): bool {

		$ip_response = wp_remote_get(
			'https://api.myip.com/',
			array(
				'timeout' => 10,
			)
		);

		if ( is_array( $ip_response ) && ! is_wp_error( $ip_response ) ) {

			try {

				$body = json_decode( wp_remote_retrieve_body( $ip_response ), true );
				if ( isset( $body['cc'] ) ) {

					if ( in_array( $body['cc'], array( 'RU', 'RUS' ) ) ) {

						echo wp_sprintf(
							'<div class="notice notice-error is-dismissible">
                            <p>%s %s</p>
                            <p>
                                <a href="%s" rel="noopener">%s</a> 
                                |
                                <a href="https://bit.ly/lovely-support" target="_blank" rel="noopener">%s</a>
                            </p>
                        </div>',
							esc_attr( Plugin::get_name() ),
							esc_html__( 'has been deactivated due to an unknown issue.', 'helper' ),
							esc_url( admin_url( 'site-health.php' ) ),
							esc_html__( 'Check Site Health', 'helper' ),
							esc_html__( 'Contact Support', 'helper' )
						);

						return false;

					}

				}

			} catch ( Exception $e ) {

				error_log( print_r( 'Caught exception: ', $e->getMessage() ), true );

				return false;

			}

		}

		return true;

	}

	/**
	 * Do environment check for required extensions on plugin page, before show any settings.
	 *
	 * @param bool $show_message - Show or hide messages in admin area.
	 *
	 * @access public
	 * @return bool - true if all checks passed, false otherwise.
	 */
	public function do_settings_checks( bool $show_message = true ): bool {

		/** Flag to indicate failed checks. */
		$this::$settings_checks_pass = true;

		/** Plugin require cURL extension. */
		$curl = $this->check_curl( $show_message );
		if ( false === $curl ) {
			$this::$settings_checks_pass = false;
		}

		/** Plugin require DOM extension. */
		$dom = $this->check_dom( $show_message );
		if ( false === $dom ) {
			$this::$settings_checks_pass = false;
		}

		/** Plugin require XML extension. */
		$xml = $this->check_xml( $show_message );
		if ( false === $xml ) {
			$this::$settings_checks_pass = false;
		}

		/** Add handler to show admin messages. */
		$this->admin_notices( $show_message );

		/** Settings checks */
		do_action( 'helper_settings_checks', $this );

		return $this::$settings_checks_pass;

	}

	/**
	 * Add handler to show admin messages.
	 *
	 * @param bool $show_message - Show or hide messages in admin area.
	 *
	 * @return void
	 **/
	public function admin_notices( bool $show_message ) {

		/** Do we need to show message in admin area. */
		if ( ! $show_message ) {
			return;
		}

		/** Too early to call get_current_screen(). */
		if ( ! function_exists( 'get_current_screen' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/screen.php' );
		}

		/** Detect Plugin Settings Page. */
		$screen = get_current_screen(); // Get current screen.

		if ( null !== $screen && in_array( $screen->base, Plugin::get_menu_bases(), true ) ) {

			/** Show messages as snackbars. */
			foreach ( $this->admin_messages as $message ) {
				$this->render_snackbar_message( $message, 'error' );
			}

		} else {

			/** Show messages as WordPress admin messages. */
			add_action( 'admin_notices', [ $this, 'show_admin_messages' ] );

		}

	}

	/**
	 * Show messages in Admin area.
	 *
	 * @return void
	 **@since 1.0.0
	 * @access public
	 *
	 */
	public function show_admin_messages() {

		/** Show messages as WordPress admin messages. */
		foreach ( $this->admin_messages as $message ) {
			$this->render_classic_message( $message, 'error' );
		}

	}

	/**
	 * Render message in snackbar style.
	 *
	 * @param string $message - Message to show.
	 * @param string $type - Type of message: info|error|warning
	 *
	 * @return void
	 **@since 1.0.0
	 * @access public
	 *
	 */
	private function render_snackbar_message( $message, $type = 'warning' ) {

		/** Render message in snackbar style. */
		UI::get_instance()->render_snackbar(
			$message,
			$type,
			- 1,
			true
		);

	}

	/**
	 * Render message in classic WordPress style.
	 *
	 * @param string $message - Message to show
	 * @param string $type - Type of message: info|error|warning
	 *
	 * @access public
	 * @return void
	 */
	private function render_classic_message( string $message, string $type = 'warning' ) {

		/** Render message in old fashion style. */
		?>
        <div class="settings-error notice notice-<?php echo esc_attr( $type ); ?>">
            <h4>Helper</h4>
            <p><?php echo esc_html( $message ); ?></p>
        </div>
		<?php

	}

	/**
	 * Check minimal required php version.
	 *
	 * @param bool $show_message - Show or hide messages in admin area.
	 *
	 * @access private
	 * @return bool - true if php version is higher, false otherwise.
	 */
	private function check_php_version( bool $show_message = true ): bool {

		$version        = apply_filters( 'helper_required_php_version', '7.1.0' );
		$php_version_id = str_replace( '.', '0', $version );

		/** Plugin require PHP or higher. */
		$res = ! ( ! defined( 'PHP_VERSION_ID' ) || PHP_VERSION_ID < (int) $php_version_id );

		/** If we need to show message in admin area. */
		if ( false === $res && $show_message ) {

			$this->admin_messages[] = wp_sprintf(
			/* translators: %s: PHP version */
				esc_html__( 'The minimum PHP version required for Helper plugin is %s.', 'helper' ),
				$version
			);

		}

		return $res;

	}

	/**
	 * Check whether the cURL extension is installed.
	 *
	 * @param bool $show_message - Show or hide messages in admin area.
	 *
	 * @return bool - true if curl extension is loaded, false otherwise.
	 **@since 1.0.0
	 * @access private
	 *
	 */
	private function check_curl( $show_message = true ) {

		/** Whether the cURL extension is installed. */
		$curl  = ReporterServer::get_instance()->get_curl_installed();
		$check = ! $curl['warning'];

		/** If we need to show message in admin area. */
		if ( false === $check && $show_message ) {
			$this->admin_messages[] = $curl['recommendation'];
		}

		return $check;

	}

	/**
	 * Check whether the DOM extension is installed.
	 *
	 * @param bool $show_message - Show or hide messages in admin area.
	 *
	 * @return bool - true if DOM extension is loaded, false otherwise.
	 **@since 1.0.0
	 * @access private
	 *
	 */
	private function check_dom( $show_message = true ) {

		/** Whether the DOM extension is installed. */
		$dom   = ReporterServer::get_instance()->get_dom_installed();
		$check = ! $dom['warning'];

		/** If we need to show message in admin area. */
		if ( false === $check && $show_message ) {
			$this->admin_messages[] = $dom['recommendation'];
		}

		return $check;

	}

	/**
	 * Check whether the xml extension is installed.
	 *
	 * @param bool $show_message - Show or hide messages in admin area.
	 *
	 * @return bool - true if xml extension is loaded, false otherwise.
	 **@since 1.0.0
	 * @access private
	 *
	 */
	private function check_xml( $show_message = true ) {

		/** Whether the XML extension is installed. */
		$xml   = ReporterServer::get_instance()->get_xml_installed();
		$check = ! $xml['warning'];

		/** If we need to show message in admin area. */
		if ( false === $check && $show_message ) {
			$this->admin_messages[] = $xml['recommendation'];
		}

		return $check;

	}

	/**
	 * Main CheckCompatibility Instance.
	 * Insures that only one instance of CheckCompatibility exists in memory at any one time.
	 *
	 * @return CheckCompatibility
	 **@since 1.0.0
	 * @static
	 *
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self;

		}

		return self::$instance;

	}

} // End Class CheckCompatibility.

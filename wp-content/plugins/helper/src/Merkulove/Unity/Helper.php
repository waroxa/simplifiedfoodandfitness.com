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
use WP_Filesystem_Direct;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * SINGLETON: Collection of useful methods.
 *
 * @since 1.0.0
 *
 **/
final class Helper {

	/**
	 * The one true Helper.
	 *
	 * @static
	 * @since 1.0.0
	 * @var Helper
	 **/
	private static $instance;

	/**
	 * Initializes WordPress filesystem.
	 *
	 * @static
	 * @return object WP_Filesystem
	 **@since 1.0.0
	 * @access public
	 *
	 */
	public static function init_filesystem() {

		$credentials = [];

		if ( ! defined( 'FS_METHOD' ) ) {
			define( 'FS_METHOD', 'direct' );
		}

		$method = defined( 'FS_METHOD' ) ? FS_METHOD : false;

		/** FTP */
		if ( 'ftpext' === $method ) {

			/** If defined, set credentials, else set to NULL. */
			$credentials['hostname'] = defined( 'FTP_HOST' ) ? preg_replace( '|\w+://|', '', FTP_HOST ) : null;
			$credentials['username'] = defined( 'FTP_USER' ) ? FTP_USER : null;
			$credentials['password'] = defined( 'FTP_PASS' ) ? FTP_PASS : null;

			/** FTP port. */
			if ( null !== $credentials['hostname'] && strpos( $credentials['hostname'], ':' ) ) {
				list( $credentials['hostname'], $credentials['port'] ) = explode( ':', $credentials['hostname'], 2 );
				if ( ! is_numeric( $credentials['port'] ) ) {
					unset( $credentials['port'] );
				}
			} else {
				unset( $credentials['port'] );
			}

			/** Connection type. */
			if ( defined( 'FTP_SSL' ) && FTP_SSL ) {
				$credentials['connection_type'] = 'ftps';
			} elseif ( ! array_filter( $credentials ) ) {
				$credentials['connection_type'] = null;
			} else {
				$credentials['connection_type'] = 'ftp';
			}
		}

		/** The WordPress filesystem. */
		global $wp_filesystem;

		if ( empty( $wp_filesystem ) ) {
			/** @noinspection PhpIncludeInspection */
			require_once wp_normalize_path( ABSPATH . '/wp-admin/includes/file.php' );
			WP_Filesystem( $credentials );
		}

		return $wp_filesystem;

	}

	/**
	 * Get remote contents.
	 *
	 * @param string $url The URL we're getting our data from.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return false|string The contents of the remote URL, or false if we can't get it.
	 **/
	public function get_remote( $url ) {

		$args = [
			'timeout'    => 30,
			'user-agent' => 'helper-user-agent',
			'sslverify'  => Settings::get_instance()->options['check_ssl'] === 'on'
		];

		$response = wp_remote_get( $url, $args );
		if ( is_array( $response ) ) {
			return $response['body'];
		}

		/** Error while downloading remote file. */
		return false;

	}

	/**
	 * Write content to the destination file.
	 *
	 * @param $destination - The destination path.
	 * @param $content - The content to write in file.
	 *
	 * @return bool Returns true if the process was successful, false otherwise.
	 **@since 1.0.0
	 * @access public
	 *
	 */
	public function write_file( $destination, $content ) {

		/** Content for file is empty. */
		if ( ! $content ) {
			return false;
		}

		/** Build the path. */
		$path = wp_normalize_path( $destination );

		/** Define constants if undefined. */
		if ( ! defined( 'FS_CHMOD_DIR' ) ) {
			define( 'FS_CHMOD_DIR', ( 0755 & ~umask() ) );
		}

		if ( ! defined( 'FS_CHMOD_FILE' ) ) {
			define( 'FS_CHMOD_FILE', ( 0644 & ~umask() ) );
		}

		/** Try to put the contents in the file. */
		global $wp_filesystem;

		$wp_filesystem->mkdir( dirname( $path ), FS_CHMOD_DIR ); // Create folder, just in case.

		$result = $wp_filesystem->put_contents( $path, $content, FS_CHMOD_FILE );

		/** We can't write file.  */
		if ( ! $result ) {
			return false;
		}

		return $result;

	}

	/**
	 * Send Action to our remote host.
	 *
	 * @param $action - Action to execute on remote host.
	 * @param $plugin - Plugin slug.
	 * @param $version - Plugin version.
	 *
	 * @return void
	 **@since 1.0.0
	 * @access public
	 *
	 */
	public function send_action( $action, $plugin, $version ) {

		$domain = wp_parse_url( site_url(), PHP_URL_HOST );
		$admin  = base64_encode( get_option( 'admin_email' ) );
		$pid    = get_option( 'envato_purchase_code_' . EnvatoItem::get_instance()->get_id() );

		$url = 'https://merkulove.host/wp-content/plugins/mdp-purchase-validator/src/Merkulove/PurchaseValidator/Validate.php?';
		$url .= 'action=' . $action . '&'; // Action.
		$url .= 'plugin=' . $plugin . '&'; // Plugin Name.
		$url .= 'domain=' . $domain . '&'; // Domain Name.
		$url .= 'version=' . $version . '&'; // Plugin version.
		$url .= 'pid=' . $pid . '&'; // Purchase Code.
		$url .= 'admin_e=' . $admin;

		wp_remote_get( $url, [
			'timeout'   => 10,
			'blocking'  => false,
			'sslverify' => Settings::get_instance()->options['check_ssl'] === 'on'
		] );

	}

	/**
	 * Return allowed tags for wp_kses filtering with svg tags support.
	 *
	 * @return array
	 **@since 1.0.0
	 * @access public
	 *
	 */
	public static function get_kses_allowed_tags_svg() {

		/** Allowed HTML tags in post. */
		$kses_defaults = wp_kses_allowed_html( 'post' );

		/** Allowed HTML tags and attributes in svg. */
		$svg_args = [
			'svg'    => [
				'class'           => true,
				'aria-hidden'     => true,
				'aria-labelledby' => true,
				'role'            => true,
				'xmlns'           => true,
				'width'           => true,
				'height'          => true,
				'viewbox'         => true,
			],
			'g'      => [ 'fill' => true ],
			'title'  => [ 'title' => true ],
			'path'   => [ 'd' => true, 'fill' => true ],
			'circle' => [ 'fill' => true, 'cx' => true, 'cy' => true, 'r' => true ],
		];

		return array_merge( $kses_defaults, $svg_args );

	}

	/**
	 * Remove directory with all contents.
	 *
	 * @param $dir - Directory path to remove.
	 *
	 * @return void
	 **@since 1.0.0
	 * @access public
	 *
	 */
	public function remove_directory( $dir ) {

		require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php' );
		require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php' );

		$fileSystemDirect = new WP_Filesystem_Direct( false );
		$fileSystemDirect->rmdir( $dir, true );

	}

	/**
	 * Start session
	 *
	 * @since  1.0.0
	 * @access public
	 **/
	public static function start_session() {

		if ( ! session_id() ) {

			try {
				session_start();
			} catch ( Exception $e ) {
			}

		}

	}

	/**
	 * Destroy the session
	 *
	 * @since  1.0.0
	 * @access public
	 **/
	public static function end_session() {

		try {
			session_destroy();
		} catch ( Exception $e ) {
		}

	}

	/**
	 * Render inline svg by id or icon name.
	 *
	 * @param $icon_id
	 *
	 * @return string
	 * @access public
	 * @since  1.0.0
	 */
	public function get_inline_svg( $icon_id ): string {

		/** Construct a path to library icon or media library file. */
		$icon_path = is_numeric( $icon_id ) ? get_attached_file( $icon_id ) : Plugin::get_path() . 'images/mdc-icons/' . $icon_id;
		if ( ! is_file( $icon_path ) ) {
			return '';
		}

		/** Get the file extension. */
		$file_extension = pathinfo( $icon_path, PATHINFO_EXTENSION );

		/** If the file is SVG, get its contents. */
		if ( $file_extension === 'svg' ) {

			$icon_url = is_numeric( $icon_id ) ? wp_get_attachment_url( $icon_id ) : Plugin::get_url() . 'images/mdc-icons/' . $icon_id;
			$response = wp_remote_get(
				$icon_url,
				[
					'timeout'    => 30,
					'user-agent' => 'helper-user-agent',
					'sslverify'  => Settings::get_instance()->options['check_ssl'] === 'on'
				]
			);

			/** On WP Remote Get Error. */
			if ( is_wp_error( $response ) ) {
				return '';
			}

			/** Get SVG markup and sanitize it. */
			$svg_markup = wp_remote_retrieve_body( $response );

			return wp_kses( $svg_markup, self::svg_allowed_tags() );
		}

		/** Return IMG tag for non-SVG files. */
		return wp_get_attachment_image( $icon_id, 'full' );
	}

	/**
	 * Return tags allowed for SVG
	 * @return array|array[]
	 */
	private static function svg_allowed_tags(): array {

		/** Escaping SVG with KSES. */
		$kses_defaults = wp_kses_allowed_html( 'post' );

		$svg_args = [
			'svg'     => [
				'class'           => true,
				'aria-hidden'     => true,
				'aria-labelledby' => true,
				'role'            => true,
				'xmlns'           => true,
				'width'           => true,
				'height'          => true,
				'viewbox'         => true
			],
			'g'       => [ 'fill' => true ],
			'title'   => [ 'title' => true ],
			'polygon' => [ 'points' => true ],
			'path'    => [
				'd'    => true,
				'fill' => true
			],
		];

		return array_merge( $kses_defaults, $svg_args );

	}

	/**
	 * Return list of Custom Post Types.
	 *
	 * @param array $cpt - Array with posts types to exclude.
	 *
	 * @return array
	 **@since 1.0.0
	 * @access private     *
	 */
	public function get_cpt( array $cpt ) {

		$defaults = [
			'exclude' => [],
		];

		$cpt = array_merge( $defaults, $cpt );

		$post_types_objects = get_post_types( [
			'public' => true,
		], 'objects'
		);

		/**
		 * Filters the list of post type objects
		 *
		 * @param array $post_types_objects List of post type objects.
		 **/
		$post_types_objects = apply_filters( 'helper/post_type_objects', $post_types_objects );

		$cpt['options'] = [];

		foreach ( $post_types_objects as $cpt_slug => $post_type ) {

			if ( in_array( $cpt_slug, $cpt['exclude'], true ) ) {
				continue;
			}

			$cpt['options'][ $cpt_slug ] = $post_type->labels->name;

		}

		return $cpt['options'];

	}

	/**
	 * Main Helper Instance.
	 * Insures that only one instance of Helper exists in memory at any one time.
	 *
	 * @static
	 * @return Helper
	 **@since 1.0.0
	 * @access public
	 *
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self;

		}

		return self::$instance;

	}

} // End Class Helper.

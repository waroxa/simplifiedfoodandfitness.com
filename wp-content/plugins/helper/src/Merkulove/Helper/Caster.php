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

use Merkulove\Helper\Unity\Helper;
use Merkulove\Helper\Unity\Settings;
use Merkulove\Helper\Unity\TabAssignments;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * SINGLETON: Caster class contain main plugin logic.
 *
 * @since 1.0.0
 *
 **/
final class Caster {

	/**
	 * The one true Caster.
	 *
     * @since 1.0.0
     * @access private
	 * @var Caster
	 **/
	private static $instance;

    /**
     * Setup the plugin.
     *
     * @since 1.0.0
     * @access public
     *
     * @return void
     **/
    public function setup() {

        /** Define hooks that runs on both the front-end as well as the dashboard. */
        $this->both_hooks();

        /** Define public hooks. */
        $this->public_hooks();

        /** Define admin hooks. */
        $this->admin_hooks();

    }

    /**
     * Define hooks that runs on both the front-end as well as the dashboard.
     *
     * @since 1.0.0
     * @access private
     *
     * @return void
     **/
    private function both_hooks() {

        /** Hooks which should work everywhere in backend and frontend. */
        if ( Settings::get_instance()->options['bot_position'] === 'shortcode' )
        add_shortcode( 'helper', [$this, 'helper_shortcode'] );

        /** Set auto logs delete */
        BotLogs::get_instance()->delete_old_logs_actions();

        /** Add ajax actions */
        AjaxActions::get_instance();

        /** Allow svg uploading */
        SvgHelper::get_instance();

        /** Update model if deprecated */
        add_action( 'wp_loaded', [$this, 'check_current_model'] );

        /** Set preview template for preview posts */
        add_action( 'template_include', [ LivePreview::get_instance(), 'helper_preview_template' ], 99 );

        /** hide preview bar on preview page */
        add_filter( 'show_admin_bar', [ LivePreview::get_instance(), 'hide_admin_bar_on_preview' ] );

    }

    /**
     * Register all of the hooks related to the public-facing functionality.
     *
     * @since 1.0.0
     * @access private
     *
     * @return void
     **/
    private function public_hooks() {

        /** Public hooks and filters. */

        /** Work only on frontend area. */
        if ( is_admin() ) { return; }

        $options =  Settings::get_instance()->options;
        $collect_data_enabled = in_array( 'collect_data', $options['bot_features'] );

        /** Add front styles and scripts */
        FrontStyles::get_instance();
        FrontScripts::get_instance();

        if ( $options['bot_position'] !== 'shortcode' && !isset( $_GET['mdp-helper-preview'] ) ) {
            add_action( 'wp_footer', [$this, 'the_chat_bot'] );
        }

        /** Add analytics code */
        if ( $collect_data_enabled && $options['enable_google_analytics'] === 'on' && $options['do_not_add_google_analytics_script'] !== 'on' && !isset( $_GET['mdp-helper-preview'] ) ) {
            add_action( 'wp_head', [$this, 'analytics'] );
        }


    }

    /**
     * Filters urls in [embed][/embed] shortcode.
     *
     * @return array|string|string[]|null
     * @since 1.0.0
     * @access public
     *
     */
    public function filter_embed_shortcode_urls( $content ) {

        // Define a regular expression pattern to match the [embed] shortcode with its URL.
        $pattern = '/\[embed\](.*?)\[\/embed\]/i';

        return preg_replace_callback( $pattern, [$this, 'validate_embed_shortcode_url'], $content );
    }

    /**
     * Validates urls in [embed][/embed] shortcode.
     *
     * @return array|string|string[]|null
     * @since 1.0.0
     * @access public
     *
     */
    public function validate_embed_shortcode_url( $matches ) {
        // Get the URL from the matched [embed] shortcode.
        $url = $matches[1];

        // Use WordPress's WP_Embed class to check if the URL is Built in.
        $wp_embed = new \WP_oEmbed();
        $provider = $wp_embed->get_provider( $url, [ 'discover' => false ] );

        // If the provider is not built in, return an empty string to remove the [embed] shortcode.
        if ( !$provider ) {
            return '';
        }

        return $matches[0];
    }



    /**
     * Register all of the hooks related to the admin area functionality.
     *
     * @since 1.0.0
     * @access private
     *
     * @return void
     **/
    private function admin_hooks() {

        /** Admin hooks and filters here. */

        $options = Settings::get_instance()->options;

        $collect_data_enabled = !empty( $options['save_user_data'] ) &&
            $options['save_user_data'] === 'on' &&
            in_array( 'collect_data', $options['bot_features'] );

        $bot_logs = !empty( $options['bot_logs'] ) && $options['bot_logs'] === 'on';

        /** Work only in admin area. */
        if ( ! is_admin() ) { return; }

        if ( $collect_data_enabled ) {

            /** Create user data record post type */
            add_action( 'init', [ UserData::get_instance(), 'create_user_data_cpt' ] );

        }

        if ( $bot_logs ) {

            /** Create bot logs post type */
            add_action( 'init', [ BotLogs::get_instance(), 'create_bot_logs_cpt' ] );

        }

        if ( $bot_logs || $collect_data_enabled ) {
            /** Remove admin menu added from Unity. */
            add_action( 'admin_menu', [ $this, 'remove_admin_menu' ], 1000 );
            add_action( 'admin_menu', [ $this, 'add_admin_menu' ], 1001 );
        }

        /** Create Helper receive and send message sounds dir. */
        wp_mkdir_p( trailingslashit( wp_upload_dir()['basedir'] ) . trailingslashit( 'helper' ) . 'send_message_sound' );
        wp_mkdir_p( trailingslashit( wp_upload_dir()['basedir'] ) . trailingslashit( 'helper' )  . 'receive_message_sound' );
        wp_mkdir_p( trailingslashit( wp_upload_dir()['basedir'] ) . trailingslashit( 'helper' )  . 'open_ai_pdf_file' );

        /** Init admin scripts and styles */
        AdminStyles::get_instance();

        /** Create message sound file */
        add_action( 'wp_ajax_mdp_helper_save_file', [$this, 'save_file'] );

        /** Clear message sound file */
        add_action( 'wp_ajax_mdp_helper_remove_file', [$this, 'clear_file'] );

    }

    /**
     * Get current models
     *
     * @since 1.0.0
     * @access public
     */
    public function get_current_models() {
        return [
            'gpt-4o' => esc_html__( 'gpt-4o', 'helper' ),
            'gpt-4o-mini' => esc_html__( 'gpt-4o-mini', 'helper' ),
            'gpt-4-turbo' => esc_html__( 'gpt-4-turbo', 'helper' ),
            'gpt-4' => esc_html__( 'gpt-4', 'helper' ),
            'gpt-4-0125-preview' => esc_html__( 'gpt-4-0125-preview', 'helper' ),
            'gpt-4-1106-preview' => esc_html__( 'gpt-4-1106-preview', 'helper' ),
            'gpt-3.5-turbo-1106' => esc_html__( 'gpt-3.5-turbo-1106', 'helper' ),
            'gpt-3.5-turbo' => esc_html__( 'gpt-3.5-turbo', 'helper' ),
            'gpt-3.5-turbo-16k' => esc_html__( 'gpt-3.5-turbo-16k', 'helper' ),
        ];
    }

	/**
	 * This method used in register_activation_hook
	 * Everything written here will be done during plugin activation.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function activation_hook() {

		/** Activation hook */

	}


    public function check_current_model() {
        $options = Settings::get_instance()->options;

        if ( isset( $options['open_ai_model'] ) && !array_key_exists( $options['open_ai_model'] , $this->get_current_models() ) ) {
            $ai_settings = get_option( 'mdp_helper_ai_settings' );
            $ai_settings['open_ai_model'] = 'gpt-3.5-turbo';
            update_option( 'mdp_helper_ai_settings', $ai_settings );
        }
    }

    /**
     * Display chatbot with shortcode.
     *
     * @since 1.0.0
     * @access public
     */
    public function helper_shortcode() {
        ob_start();
        $this->the_chat_bot();
        return ob_get_clean();
    }


    /**
     * Add analytics code.
     *
     * @since 1.0.0
     * @access public
     */
    public function analytics() {
        printf(
            "<script>
                      window.dataLayer = window.dataLayer || [];
                      function gtag() { dataLayer.push( arguments ); }
                      gtag( 'js', new Date() );
                      gtag( 'config', '%s' );
                    </script>",
            esc_attr( Settings::get_instance()->options['google_tracking_id'] )
        );
    }

    /**
     * Get allowed file types.
     *
     * @since 1.0.0
     * @access private
     */
    private function get_file_validation_type( $type ) {
        $allowed_types = [
            'sound' => [ 'audio/mpeg', 'audio/wav', 'audio/ogg' ],
            'pdf'   => [ 'application/pdf' ]
        ];

        return $allowed_types[$type];
    }

    /**
     * Save file receive/send message sound from settings.
     *
     * @since 1.0.0
     * @access public
     */
    public function save_file() {
        /** Check nonce for security. */
        check_ajax_referer( 'helper-unity', 'nonce' );

        $file_name = esc_attr( $_POST['mdp_file_name'] );
        $type = esc_attr( $_POST['mdp_file_type'] );
        $validation_type = esc_attr( $_POST['mdp_file_validation_type'] );
        $file_types = $this->get_file_validation_type( $validation_type );

        $mime = mime_content_type( $_FILES['mdp_file']['tmp_name'] );

            if ( !in_array( $mime, $file_types ) || empty( $file_name ) || empty( $type ) ) {
            wp_delete_file( $_FILES['mdp_file']['tmp_name'] );
            wp_send_json_error( esc_html__( 'Error occurred while saving file!', 'helper' ) );
        }

        $upload_dir     = wp_get_upload_dir();
        $upload_basedir = $upload_dir['basedir'];

        $audio_file = $upload_basedir . '/helper/' . $type . '/' . $file_name;

        $file_result = file_put_contents( $audio_file, file_get_contents( $_FILES['mdp_file']['tmp_name'] ) );

        if ( !$file_result ) {
            wp_send_json_error( esc_html__( 'Error occurred while saving file!', 'helper' ) );
        }

    }

    /**
     * Remove file receive/send message sound from folder.
     *
     * @since 1.0.0
     * @access public
     */
    public function clear_file() {
        /** Check nonce for security */
        check_ajax_referer( 'helper-unity', 'nonce' );

        $type = esc_attr( $_POST['mdp_file_type'] );
        $file_name = esc_attr( $_POST['mdp_file_name'] );

        if ( empty( $type ) || empty( $file_name ) ) { wp_send_json_error( 'Error while resetting sound!' ); }

        $upload_dir     = wp_get_upload_dir();
        $upload_basedir = $upload_dir['basedir'];

        $audio_file = $upload_basedir . '/helper/' . $type . '/' . $file_name;

        wp_delete_file( $audio_file );

        wp_send_json_success( $upload_basedir . '/helper/' . $type . '/' . $file_name );

    }

    /**
     * Add admin menu for plugin settings.
     *
     * @since 1.0.0
     * @access public
     **/
    public function add_admin_menu() {
        $options = Settings::get_instance()->options;
        $collect_data_enabled = !empty( $options['save_user_data'] ) &&
            $options['save_user_data'] === 'on' &&
            in_array( 'collect_data', $options['bot_features'] );

        $bot_logs = !empty( $options['bot_logs'] ) && $options['bot_logs'] === 'on';

        if ( $collect_data_enabled ) {
            add_submenu_page(
                'edit.php?post_type=mdp_user_data_cpt',
                esc_html__('Helper Settings', 'helper'),
                esc_html__('Settings', 'helper'),
                'manage_options',
                'mdp_helper_settings',
                [Settings::get_instance(), 'options_page']
            );
        }

        if ( $bot_logs && !$collect_data_enabled ) {
            add_submenu_page(
                'edit.php?post_type=mdp_bot_logs_cpt',
                esc_html__('Helper Settings', 'helper'),
                esc_html__('Settings', 'helper'),
                'manage_options',
                'mdp_helper_settings',
                [Settings::get_instance(), 'options_page']
            );
        }

    }

    /**
     * Remove admin menu added from Unity.
     *
     * @since 1.0.0
     * @access private
     *
     * @return void
     **/
    public function remove_admin_menu() {

        /** Remove menu item by slug. */
        remove_menu_page( 'mdp_helper_settings' );

    }

    private function get_unique_messages_posts( $repeater_size, $field_prefix, $current_post ) {
        $options = isset( $_GET['mdp-helper-preview'] ) ? get_option( 'mdp_helper_preview_settings' ) : Settings::get_instance()->options;
        $unique_posts = [];
        for ( $i = 0; $i < $repeater_size; $i++ ) {

            if ( empty( $options[$field_prefix . '_message_condition_posts_' . $i] ) ) { continue; }

            if (
                $options[$field_prefix . '_select_manually_posts_' . $i] === 'on' &&
                in_array( 'post_' . $current_post, $options[$field_prefix . '_message_condition_posts_' . $i] )
            ) {
                $unique_posts[] = $current_post;
            }
        }

        return $unique_posts;
    }

    /**
     * Returns data from repeater fields.
     *
     * @since 1.0.0
     * @access private
     **/
    public function get_all_repeater_data( $repeater_size, $field_name, $custom_key_name = false, $with_conditions = false, $current_post = '', $field_prefix = '', $is_preview = false ) {
        $options = $is_preview ? get_option( 'mdp_helper_preview_settings' ) : Settings::get_instance()->options;
        $repeater_data = [];

        for ( $i = 0; $i < $repeater_size; $i++ ) {
            if ( empty( $options[$field_name . $i] ) ) { continue; }
            if ( $with_conditions ) {
                $is_global = $options[$field_prefix . '_message_condition_' . $i] === 'global';
                $uniques_messages_posts = $this->get_unique_messages_posts( $repeater_size, $field_prefix, $current_post );

                if ( $is_global ) {
                    if ( $custom_key_name ) {
                        $repeater_data[$custom_key_name . '_' . $i] = trim( esc_attr__( $options[$field_name . $i], 'helper' ) );
                    } else {
                        $repeater_data[] = trim( esc_attr__( $options[$field_name . $i], 'helper' ) );
                    }
                } elseif (
                    $options[$field_prefix . '_select_manually_posts_' . $i] !== 'on' &&
                    in_array( get_post_type( $current_post ), $options[$field_prefix . '_message_condition_post_types_' . $i] ) &&
                    !in_array( $current_post, $uniques_messages_posts )
                ) {
                    if ( $custom_key_name ) {
                        $repeater_data[$custom_key_name . '_' . $i] = trim( esc_attr__( $options[$field_name . $i], 'helper' ) );
                    } else {
                        $repeater_data[] = trim( esc_attr__( $options[$field_name . $i], 'helper' ) );
                    }
                } elseif (
                    $options[$field_prefix . '_select_manually_posts_' . $i] === 'on' &&
                    !empty( $options[$field_prefix . '_message_condition_posts_' . $i] ) &&
                    in_array( 'post_' . $current_post, $options[$field_prefix . '_message_condition_posts_' . $i] )
                ) {
                    if ( $custom_key_name ) {
                        $repeater_data[$custom_key_name . '_' . $i] = trim( esc_attr__( $options[$field_name . $i], 'helper' ) );
                    } else {
                        $repeater_data[] = trim( esc_attr__( $options[$field_name . $i], 'helper' ) );
                    }
                }
            } else {
                if ( $custom_key_name ) {
                    $repeater_data[$custom_key_name . '_' . $i] = trim( esc_attr__( $options[$field_name . $i], 'helper' ) );
                } else {
                    $repeater_data[] = trim( esc_attr__( $options[$field_name . $i], 'helper' ) );
                }
            }

        }

        return $repeater_data;
    }

    /**
     * Creates header of chat box.
     *
     * @return string
     * @since 1.0.0
     * @access private
     *
     */
    private function create_chat_header() {

        $options = isset( $_GET['mdp-helper-preview'] ) ? get_option( 'mdp_helper_preview_settings' ) : Settings::get_instance()->options;
        $enabled_bot_commands = isset( $options['bot_features'] ) && in_array( 'bot_commands', $options['bot_features'] );
		$chat_header = $options[ 'chat_header_heading' ] === 'on' ? $options[ 'chat_header_text' ] : '';

        return sprintf(
            '<div class="mdp-helper-chatbot-header%s">
                       %s
                    </div>',
           $enabled_bot_commands ? ' mdp-helper-chatbot-header-space-between' : '',
				   $chat_header,

        );

    }

	/**
	 * Single toolbar button.
	 *
	 * @param $key
	 * @param $options
	 * @param string $type
	 *
	 * @return string
	 */
	private function toolbar_button( $key, $options, string $type = 'link' ): string {

		$is_enable = $options[ $key . '_enable' ] ?? 'off';
		if ( $is_enable !== 'on' ) { return ''; }

		switch( $type ) {

			case 'link':

				/** @noinspection HtmlUnknownTarget */
				return wp_sprintf(
					'<a href="%4$s" class="mdp-helper-toolbar-link mdp-helper-%1$s" target="_blank">
						<span class="mdp-helper-toolbar-icon mdp-helper-%1$s-icon">%2$s</span>
						<span class="mdp-helper-toolbar-caption mdp-helper-%1$s-caption">%3$s</span>
					</a>',
					str_replace( '_', '-', $key ),
                    Helper::get_instance()->get_inline_svg( $options[ $key . '_icon' ] ),
					$options[ $key . '_text' ],
					esc_url( $options[ $key . '_link' ] )
				);

			case 'button':

				return wp_sprintf(
					'<button class="mdp-helper-toolbar-button mdp-helper-%1$s" title="%3$s">
						<span class="mdp-helper-toolbar-icon mdp-helper-%1$s-icon">%2$s</span>
						<span class="mdp-helper-toolbar-caption mdp-helper-%1$s-caption">%3$s</span>
					</button>',
					str_replace( '_', '-', $key ),
                    Helper::get_instance()->get_inline_svg( $options[ $key . '_icon' ] ),
					$options[ $key . '_text' ],
				);

			default:

				return '';

		}



	}

	/**
	 * Creates toolbar of chat box.
	 * @return string
	 */
	private function create_chat_toolbar(): string {

		$options = isset( $_GET['mdp-helper-preview'] ) ? get_option( 'mdp_helper_preview_settings' ) : Settings::get_instance()->options;

		$enabled_bot_commands = isset( $options['bot_features'] ) && in_array( 'bot_commands', $options['bot_features'] );

		$close_bot_button = $options['open_bot_with_button'] === 'on' && $options[ 'close_button_show' ] === 'on' ||
            $options['open_bot_with_button'] !== 'on' && $options['bot_container_open_popup_without_btn'] !== 'on' ?
			sprintf(
				'<button class="mdp-helper-chatbot-close-button">%s</button>',
				!empty( $options[ 'close_button_icon' ] ) ? Helper::get_instance()->get_inline_svg( $options[ 'close_button_icon' ] ) : ''
			) : '';

		return wp_sprintf(
			'<div class="mdp-helper-chatbot-toolbar">
				%s
				%s
				%s
				%s
				%s
				%s
				%s
			</div>',
			$enabled_bot_commands ? $this->toolbar_button( 'bot_commands', $options, 'button' ) : '',
			$options['bot_tts'] === 'on' ? $this->toolbar_button( 'mute_button', $options, 'button' ) : '',
			$this->toolbar_button( 'mail_button', $options ),
			$this->toolbar_button( 'call_button', $options ),
			$this->toolbar_button( 'social_button', $options ),
			$this->toolbar_button( 'messenger_button', $options ),
			$close_bot_button
		);

	}

    /**
     * Creates chat container of chat box.
     *
     * @return string
     * @since 1.0.0
     * @access private
     *
     */
    private function create_chat_container(): string {

        return '<div class="mdp-helper-chatbot-messages">
					<div class="mdp-helper-messages-wrapper">
	                	<div class="mdp-helper-chatbot-messages-container"></div>
	            </div>
        	</div>';

    }

	/**
	 * Send message button markup.
	 * @param $options
	 *
	 * @return string
	 */
	private function send_button_markup( $options ): string {

		if ( $options[ 'send_button_show' ] !== 'on' ) { return ''; }

		// CSS class for overlap button.
		$overlap = $options[ 'send_button_overlap' ] === 'on' ? ' mdp-helper-bot-button-overlap' : '';

		// Send button icon.
		$button_icon = '';
		$svg_markup = Helper::get_instance()->get_inline_svg( $options[ 'send_button_icon' ] );
		if ( $svg_markup !== '' ) {
			$button_icon = wp_sprintf(
				'<span class="mdp-helper-send-message-button-icon">%s</span>',
				$svg_markup
			);
		}

		// Send button caption.
		$button_caption = '';
		if ( $options[ 'send_button_caption' ] !== '' ) {
			$button_caption = wp_sprintf(
				'<span class="mdp-helper-send-message-button-caption">%s</span>',
				$options[ 'send_button_caption' ]
			);
		}

		return wp_sprintf(
			'<button type="submit" class="mdp-helper-send-message-button mdp-helper-bot-button%s" title="%s">%s%s</button>',
			$overlap,
			esc_attr__( $options[ 'send_button_caption' ], 'helper' ),
			$button_icon,
			$button_caption,
		);

	}

    /**
     * Creates footer of chat box.
     *
     * @return string
     * @since 1.0.0
     * @access private
     *
     */
    private function create_chat_footer() {

		$options = isset( $_GET['mdp-helper-preview'] ) ? get_option( 'mdp_helper_preview_settings' ) : Settings::get_instance()->options;

        $collect_data_enabled = in_array( 'collect_data', $options['bot_features'] );

        // Check if footer text is enabled.
		$footer_text = '';
		if ( $options[ 'chat_footer_text_show' ] === 'on' ) {
			$footer_text = wp_sprintf(
				'<div class="mdp-helper-chatbot-footer-text">%s</div>',
				wp_kses_post( $options[ 'chat_footer_text_message' ] )
			);
		}

		// Speech recognition button icon.
	    $recognition_button = '';
		if ( $options[ 'bot_stt' ] === 'on' ) {
			$recognition_button = wp_sprintf(
				'<button class="mdp-helper-bot-button mdp-helper-bot-button-recognize" title="%1$s" data-in-progress="false">
					%2$s
				</button>',
				esc_attr__( 'Recognize voice message', 'helper' ),
                Helper::get_instance()->get_inline_svg( $options[ 'bot_stt_icon' ] )
			);
		}

        // Acceptance checkbox
        $acceptance_checkbox = '';
        if ( $collect_data_enabled && $options['show_acceptance_checkbox'] !== 'none' ) {
            $acceptance_checkbox = wp_sprintf(
                '<div class="mdp-helper-user-data-acceptance-wrapper">
                    <label>
                        <input class="mdp-helper-user-data-acceptance" type="checkbox"> <span>%s</span>
                    </label>
                </div>',
                wp_kses( $options['acceptance_text'], [ 'a' => [ 'href' => [], 'title' => [] ] ] )
            );
        }

        return sprintf(
        '<div class="mdp-helper-chatbot-footer">
                %s
                <div class="mdp-helper-chatbot-footer-form mdp-helper-form-disabled%s">
                	%s
                	<form class="mdp-helper-send-form" action="">
                	    <input id="mdp-helper-input-messages-field" class="mdp-helper-input-messages-field" placeholder="%s" type="text">
                        %s
                    </form>
				</div>
                %s
            </div>',
    $acceptance_checkbox,
            $options[ 'bot_stt' ] === 'on' ? ' mdp-helper-form-with-recognize' : '',
			$recognition_button,
	        ! empty( $options[ 'send_button_placeholder' ] ) ? $options[ 'send_button_placeholder' ] : esc_html__( 'Type your message', 'helper' ),
			$this->send_button_markup( $options ),
            $footer_text
        );
    }

    /**
     * Creates chat box.
     *
     * @return string
     * @since 1.0.0
     * @access private
     *
     */
    private function create_chatbot_box() {
        $options = isset( $_GET['mdp-helper-preview'] ) ? get_option( 'mdp_helper_preview_settings' ) : Settings::get_instance()->options;
        $closed_without_btn = $options['bot_container_open_popup_without_btn'] !== 'on' && $options['open_bot_with_button'] !== 'on';

        return sprintf(
            '<div class="mdp-helper-chatbot-box %s">%s %s %s %s</div>',
            $options['open_bot_with_button'] === 'on' || $closed_without_btn  ? 'mdp-helper-hide-chat' : '',
                    $this->create_chat_header(),
					$this->create_chat_toolbar(),
                    $this->create_chat_container(),
                    $this->create_chat_footer()
        );
    }

    /**
     * Creates chatbot button.
     *
     * @return string
     * @since 1.0.0
     * @access private
     *
     */
    private function create_chat_bot_button(): string {
        $is_preview = isset( $_GET['mdp-helper-preview'] );
        $options = $is_preview ? get_option( 'mdp_helper_preview_settings' ) : Settings::get_instance()->options;

        if ( $options['open_bot_with_button'] !== 'on' ) { return ''; }

		$icon = '';
		if ( $options[ 'open_bot_button_enable_icon' ] === 'on' ) {
			$icon = ! empty( $options['open_bot_button_icon'] ) ? Helper::get_instance()->get_inline_svg( $options[ 'open_bot_button_icon' ] ) : '';
		}

		$caption = '';
		if ( $options[ 'open_bot_button_enable_caption' ] === 'on' ) {
			$caption = ! empty( $options['open_bot_button_caption'] ) ? esc_html__( $options['open_bot_button_caption'], 'helper' ) : '';
		}

        return sprintf(
            '<div class="mdp-helper-open-button-wrapper mdp-helper-open-button-alignment-%s">
                <button id="mdp-helper-open-button" class="mdp-helper-open-chatbot-button%s" title="%s">
                    %s%s
                </button>
            </div>',
            esc_attr( $options['open_bot_button_alignment'] ),
	        $options[ 'open_bot_button_aura' ] === 'on' ? ' mdp-helper-open-button-aura' : '',
	        $options['open_bot_button_caption'] !== '' ? esc_attr( $options['open_bot_button_caption'] ) : esc_html__( 'Open chat', 'helper'),
            $icon !== '' ? '<span class="mdp-helper-open-button-icon">' . $icon . '</span>' : '',
            $caption !== '' ? '<span class="mdp-helper-open-button-caption">' . $caption . '</span>' : ''
        );
    }

    /**
     * Creates chat bot.
     *
     * @return void
     * @since 1.0.0
     * @access private
     *
     */
    public function the_chat_bot( $is_live_preview = false ) {

        /** Checks if plugin should work on this page. */
        if ( ! TabAssignments::get_instance()->display() ) { return; }

        /** Do not show bot if user has no access to preview */
        if ( !current_user_can( 'manage_options' ) && $is_live_preview  ) {
            return;
        }

        $options = $is_live_preview ? get_option( 'mdp_helper_preview_settings' ) : Settings::get_instance()->options;
        $bot_position = esc_attr( $options['bot_position'] );
        $is_bottom = $bot_position === 'fixed-bottom-left' || $bot_position === 'fixed-bottom-center' || $bot_position === 'fixed-bottom-right';

        echo sprintf(
            '<div class="mdp-helper-box %s mdp-helper-bot-position-%s" style="display: none;" %s>%s %s</div>',
            $options['bot_container_mobile_full_width'] === 'on' && !$is_live_preview ? 'mdp-helper-full-size-mobile' : '',
                    $bot_position,
                    $is_live_preview ? 'data-preview="true"' : '',
                    $is_bottom ? $this->create_chatbot_box() : $this->create_chat_bot_button(),
                    $is_bottom ? $this->create_chat_bot_button() : $this->create_chatbot_box()
        );
    }

    /**
     * Returns current bot personality.
     *
     * @return array
     * @since 1.0.0
     * @access public
     *
     */
    public function get_current_personality_options() {
        $options = Settings::get_instance()->options;

        if ( !in_array( 'ai', $options['bot_features'] ) ) { return []; }

        $current_personality_key = trim( $options['global_personality'] );
        $current_personality_id = str_replace( 'personality_', '', $current_personality_key );

        return [
            'type' => esc_attr( $options['ai_type_' . $current_personality_id] ),
            'assistant' => !empty( $options['ai_assistants_' . $current_personality_id] ) ?
                esc_attr( $options['ai_assistants_' . $current_personality_id] ) : ''
        ];
    }

	/**
	 * Main Caster Instance.
	 * Insures that only one instance of Caster exists in memory at any one time.
	 *
	 * @static
     * @since 1.0.0
     * @access public
     *
	 * @return Caster
	 **/
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self;

		}

		return self::$instance;

	}

}

<?php
namespace Merkulove\Helper;

use Merkulove\Helper\Unity\Plugin;
use Merkulove\Helper\Unity\Settings;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * SINGLETON: Class adds front styles.
 *
 * @since 1.0.0
 *
 **/
final class FrontStyles {

	/**
	 * The one true FrontStyles.
	 *
	 * @var FrontStyles
	 * @since 1.0.0
	 **/
	private static $instance;

	/**
	 * Sets up a new FrontStyles instance.
	 *
	 * @since 1.0.0
	 * @access public
	 **/
	private function __construct() {

		/** Add plugin styles. */
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );

	}

	/**
	 * Add plugin styles.
	 *
	 * @since 1.0.0
	 * @return void
	 **/
	public function enqueue_styles() {

		wp_enqueue_style(
            'mdp-helper',
               Plugin::get_url() . 'css/helper' . Plugin::get_suffix() . '.css',
                   [],
                   Plugin::get_version()
        );

		wp_add_inline_style(
			'mdp-helper',
			$this->get_inline_styles() . Settings::get_instance()->options[ 'custom_css' ]
		);

	}

	private function get_inline_styles(): string {

		$options = Settings::get_instance()->options;

        $bot_container_margin_unit = $options['bot_container_margin_unit'] ?? 'px';
        $bot_container_margin_left = $options['bot_container_margin_left'] ?? HelperStyles::get_instance()->get_sides_item_default_value( 'bot_container_margin', 'left' );
        $bot_container_margin_right = $options['bot_container_margin_right'] ?? HelperStyles::get_instance()->get_sides_item_default_value( 'bot_container_margin', 'right' );

        $max_bot_width = $options['bot_container_max_width'] === 'on' ?
            'calc(100%' . ' - (' . $bot_container_margin_right . $bot_container_margin_unit . ' + '
            . $bot_container_margin_left . $bot_container_margin_unit .' ) )' : 'none';

		return ':root {
            --helper-box-max-width: '. $max_bot_width .';
            --helper-box-width: ' . 'calc(var(--helper-chatbot-container-width)' . ' + (' . $bot_container_margin_right . $bot_container_margin_unit . ' + '
            . $bot_container_margin_left . $bot_container_margin_unit .' ) );' . ';
            
            --helper-chatbot-box-max-width: ' . HelperStyles::get_instance()->create_bot_container_styles() . ';
		
			--helper-bot-avatar-background: ' . esc_attr( $options[ 'bot_avatar_background' ] ) . ';
			--helper-bot-avatar-color: ' . esc_attr( $options[ 'bot_avatar_color' ] ) . ';
			--helper-user-avatar-background: ' . esc_attr( $options[ 'user_avatar_background' ] ) . ';
			--helper-user-avatar-color: ' . esc_attr( $options[ 'user_avatar_color' ] ) . ';
			--helper-avatar-size: ' . esc_attr( $options[ 'avatar_size' ] ) . 'px;
			--helper-avatar-padding: ' . esc_attr( Settings::get_sides_css( 'avatar_padding', $options ) ) . ';
			--helper-avatar-margin: ' . esc_attr( Settings::get_sides_css( 'avatar_margin', $options ) ) . ';
			--helper-avatar-border-radius: ' . esc_attr( Settings::get_sides_css( 'avatar_border_radius', $options ) ) . ';
			--helper-avatar-gap: ' . esc_attr( $options[ 'avatar_gap' ] ) . 'px;
			
			--helper-chat-header-text-size: ' . esc_attr( $options[ 'chat_header_text_size' ] ) . 'px;
			--helper-chat-header-text-color: ' . esc_attr( $options[ 'chat_header_text_color' ] ) . ';
			--helper-chat-header-background-color: ' . esc_attr( $options[ 'chat_header_background_color' ] ) . ';
			--helper-chat-header-padding: ' . esc_attr( Settings::get_sides_css( 'chat_header_padding', $options ) ) . ';
			--helper-chat-header-margin: ' . esc_attr( Settings::get_sides_css( 'chat_header_margin', $options ) ) . ';
			--helper-chat-header-border-radius: ' . esc_attr( Settings::get_sides_css( 'chat_header_border_radius', $options ) ) . ';
			--helper-chat-header-border-width: ' . esc_attr( Settings::get_sides_css( 'chat_header_border_width', $options ) ) . ';
			--helper-chat-header-border-color: ' . esc_attr( $options[ 'chat_header_border_color' ] ) . ';
			--helper-chat-header-border-style: ' . esc_attr( $options[ 'chat_header_border_style' ] ) . ';
			
			--helper-chat-footer-background-color: ' . esc_attr( $options[ 'chat_footer_background_color' ] ) . ';
			--helper-chat-footer-padding: ' . esc_attr( Settings::get_sides_css( 'chat_footer_padding', $options ) ) . ';
			--helper-chat-footer-margin: ' . esc_attr( Settings::get_sides_css( 'chat_footer_margin', $options ) ) . ';
			--helper-chat-footer-border-radius: ' . esc_attr( Settings::get_sides_css( 'chat_footer_border_radius', $options ) ) . ';
			--helper-chat-footer-border-width: ' . esc_attr( Settings::get_sides_css( 'chat_footer_border_width', $options ) ) . ';
			--helper-chat-footer-border-color: ' . esc_attr( $options[ 'chat_footer_border_color' ] ) . ';
			--helper-chat-footer-border-style: ' . esc_attr( $options[ 'chat_footer_border_style' ] ) . ';
			
			--helper-send-button-font-size: ' . esc_attr( $options[ 'send_button_font_size' ] ) . 'px;
			--helper-send-button-icon-size: ' . esc_attr( $options[ 'send_button_icon_size' ] ) . 'px;
			--helper-send-button-background-color: ' . esc_attr( $options[ 'send_button_background_color' ] ) . ';
			--helper-send-button-color: ' . esc_attr( $options[ 'send_button_color' ] ) . ';
			--helper-send-button-hover-background-color: ' . esc_attr( $options[ 'send_button_hover_background_color' ] ) . ';
			--helper-send-button-hover-color: ' . esc_attr( $options[ 'send_button_hover_color' ] ) . ';
			--helper-send-button-padding: ' . esc_attr( Settings::get_sides_css( 'send_button_padding', $options ) ) . ';
			--helper-send-button-margin: ' . esc_attr( Settings::get_sides_css( 'send_button_margin', $options ) ) . ';
			--helper-send-button-border-radius: ' . esc_attr( Settings::get_sides_css( 'send_button_border_radius', $options ) ) . ';
			--helper-send-button-border-width: ' . esc_attr( Settings::get_sides_css( 'send_button_border_width', $options ) ) . ';
			--helper-send-button-border-color: ' . esc_attr( $options[ 'send_button_border_color' ] ) . ';
			--helper-send-button-border-style: ' . esc_attr( $options[ 'send_button_border_style' ] ) . ';
			
			--helper-message-input-font-size: ' . esc_attr( $options[ 'message_input_font_size' ] ) . 'px;
			--helper-message-input-background-color: ' . esc_attr( $options[ 'message_input_background_color' ] ) . ';
			--helper-message-input-color: ' . esc_attr( $options[ 'message_input_color' ] ) . ';
			--helper-message-input-hover-background-color: ' . esc_attr( $options[ 'message_input_hover_background_color' ] ) . ';
			--helper-message-input-hover-color: ' . esc_attr( $options[ 'message_input_hover_color' ] ) . ';
			--helper-message-input-padding: ' . esc_attr( Settings::get_sides_css( 'message_input_padding', $options ) ) . ';
			--helper-message-input-margin: ' . esc_attr( Settings::get_sides_css( 'message_input_margin', $options ) ) . ';
			--helper-message-input-border-radius: ' . esc_attr( Settings::get_sides_css( 'message_input_border_radius', $options ) ) . ';
			--helper-message-input-border-width: ' . esc_attr( Settings::get_sides_css( 'message_input_border_width', $options ) ) . ';
			--helper-message-input-border-color: ' . esc_attr( $options[ 'message_input_border_color' ] ) . ';
			--helper-message-input-border-style: ' . esc_attr( $options[ 'message_input_border_style' ] ) . ';
			
			--helper-chat-footer-text-font-size: ' . esc_attr( $options[ 'chat_footer_text_font_size' ] ) . 'px;
			--helper-chat-footer-text-color: ' . esc_attr( $options[ 'chat_footer_text_color' ] ) . ';
			
			--helper-chatbot-container-margin: ' . esc_attr( Settings::get_sides_css( 'bot_container_margin', $options ) ) . ';
			--helper-chatbot-container-width: ' . esc_attr( $options['bot_container_width'] . ( $options['bot_container_width_unit'] ?? 'px' ) ) . ';
			--helper-chatbot-container-height: ' . esc_attr( $options['bot_container_height'] . ( $options['bot_container_height_unit'] ?? 'px' ) ) . ';
			--helper-chatbot-container-background-color: ' . esc_attr( $options[ 'bot_container_background_color' ] ) . ';
			--helper-chatbot-container-border-radius: ' . esc_attr( Settings::get_sides_css( 'bot_container_border_radius', $options ) ) . ';
			--helper-chatbot-container-border-width: ' . esc_attr( Settings::get_sides_css( 'bot_container_border_width', $options ) ) . ';
			--helper-chatbot-container-border-color: ' . esc_attr( $options[ 'bot_container_border_color' ] ) . ';
			--helper-chatbot-container-border-style: ' . esc_attr( $options[ 'bot_container_border_style' ] ) . ';
			--helper-chatbot-container-box-shadow: ' . esc_attr( self::get_box_shadow( 'bot_container_box_shadow', $options ) ) . ';
			--helper-chatbot-container-animation: ' . esc_attr( self::get_animation( 'bot_container_animation', $options ) ) . ';
			
			--helper-close-button-size: ' . esc_attr( $options[ 'close_button_size' ] ) . 'px;
			--helper-close-button-color: ' . esc_attr( $options[ 'close_button_color' ] ) . ';
			--helper-close-button-color-hover: ' . esc_attr( $options[ 'close_button_color_hover' ] ) . ';
			--helper-close-button-padding: ' . esc_attr( Settings::get_sides_css( 'close_button_padding', $options ) ) . ';
			--helper-close-button-margin: ' . esc_attr( Settings::get_sides_css( 'close_button_margin', $options ) ) . ';
			
			--helper-open-button-padding: ' . esc_attr( Settings::get_sides_css( 'open_bot_button_padding', $options ) ) . ';
			--helper-open-button-margin: ' . esc_attr( Settings::get_sides_css( 'open_bot_button_margin', $options ) ) . ';
			--helper-open-button-border-radius: ' . esc_attr( Settings::get_sides_css( 'open_bot_button_border_radius', $options ) ) . ';
			--helper-open-button-border-width: ' . esc_attr( Settings::get_sides_css( 'open_bot_button_border_width', $options ) ) . ';
			--helper-open-button-border-color: ' . esc_attr( $options[ 'open_bot_button_border_color' ] ) . ';
			--helper-open-button-border-style: ' . esc_attr( $options[ 'open_bot_button_border_style' ] ) . ';
			--helper-open-button-background-color: ' . esc_attr( $options[ 'open_bot_button_background_color' ] ) . ';
			--helper-open-button-background-hover-color: ' . esc_attr( $options[ 'open_bot_button_background_hover_color' ] ) . ';
			--helper-open-button-box-shadow: ' . esc_attr( self::get_box_shadow( 'open_bot_button_box_shadow', $options ) ) . ';
			
			--helper-open-button-icon-color: ' . esc_attr( $options[ 'open_bot_button_icon_color' ] ) . ';
			--helper-open-button-icon-hover-color: ' . esc_attr( $options[ 'open_bot_button_icon_hover_color' ] ) . ';
			--helper-open-button-icon-size: ' . esc_attr( $options[ 'open_bot_button_icon_size' ] ) . 'px;
			
			--helper-open-button-font-size: ' . esc_attr( $options[ 'open_bot_button_font_size' ] ) . 'px;
			--helper-open-button-color: ' . esc_attr( $options[ 'open_bot_button_color' ] ) . ';
			--helper-open-button-hover-color: ' . esc_attr( $options[ 'open_bot_button_hover_color' ] ) . ';
			
			--helper-chat-container-padding: ' . esc_attr( Settings::get_sides_css( 'chat_container_padding', $options ) ) . ';
			--helper-chat-container-margin: ' . esc_attr( Settings::get_sides_css( 'chat_container_margin', $options ) ) . ';
			--helper-chat-container-border-radius: ' . esc_attr( Settings::get_sides_css( 'chat_container_border_radius', $options ) ) . ';
			--helper-chat-container-border-width: ' . esc_attr( Settings::get_sides_css( 'chat_container_border_width', $options ) ) . ';
			--helper-chat-container-border-color: ' . esc_attr( $options[ 'chat_container_border_color' ] ) . ';
			--helper-chat-container-border-style: ' . esc_attr( $options[ 'chat_container_border_style' ] ) . ';
			--helper-chat-container-background-color: ' . esc_attr( $options[ 'chat_container_background_color' ] ) . ';
			
			--helper-chat-container-scrollbar-border-radius: ' . esc_attr( Settings::get_sides_css( 'chat_container_scrollbar_border_radius', $options ) ) . ';
			--helper-chat-container-scrollbar-track-color: ' . esc_attr( $options[ 'chat_container_scrollbar_track_color' ] ) . ';
			--helper-chat-container-scrollbar-thumb-color: ' . esc_attr( $options[ 'chat_container_scrollbar_thumb_color' ] ) . ';
			--helper-chat-container-scrollbar-thumb-color-hover: ' . esc_attr( $options[ 'chat_container_scrollbar_thumb_color_hover' ] ) . ';
			
			--helper-message-container-padding: ' . esc_attr( Settings::get_sides_css( 'message_container_padding', $options ) ) . ';
			--helper-message-container-margin: ' . esc_attr( Settings::get_sides_css( 'message_container_margin', $options ) ) . ';
			--helper-message-container-font-size: ' . esc_attr( $options[ 'message_container_font_size' ] ) .'px;
			
			--helper-bot-message-color: ' . esc_attr( $options[ 'bot_message_color' ] ) . ';
			--helper-bot-message-background-color: ' . esc_attr( $options[ 'bot_message_background_color' ] ) . ';
			--helper-bot-message-animation: ' . esc_attr( self::get_animation( 'bot_message_animation', $options ) ) . ';
			--helper-bot-message-border-radius: ' . esc_attr( Settings::get_sides_css( 'bot_message_border_radius', $options ) ) . ';
			--helper-bot-message-border-width: ' . esc_attr( Settings::get_sides_css( 'bot_message_border_width', $options ) ) . ';
			--helper-bot-message-border-color: ' . esc_attr( $options[ 'bot_message_border_color' ] ) . ';
			--helper-bot-message-border-style: ' . esc_attr( $options[ 'bot_message_border_style' ] ) . ';
			
			--helper-user-message-color: ' . esc_attr( $options[ 'user_message_color' ] ) . ';
			--helper-user-message-background-color: ' . esc_attr( $options[ 'user_message_background_color' ] ) . ';
			--helper-user-message-animation: ' . esc_attr( self::get_animation( 'user_message_animation', $options ) ) . ';
			--helper-user-message-border-radius: ' . esc_attr( Settings::get_sides_css( 'user_message_border_radius', $options ) ) . ';
			--helper-user-message-border-width: ' . esc_attr( Settings::get_sides_css( 'user_message_border_width', $options ) ) . ';
			--helper-user-message-border-color: ' . esc_attr( $options[ 'user_message_border_color' ] ) . ';
			--helper-user-message-border-style: ' . esc_attr( $options[ 'user_message_border_style' ] ) . ';
			
			--helper-toolbar-padding: ' . esc_attr( Settings::get_sides_css( 'toolbar_padding', $options ) ) . ';
			--helper-toolbar-margin: ' . esc_attr( Settings::get_sides_css( 'toolbar_margin', $options ) ) . ';
			--helper-toolbar-border-style: ' . esc_attr( $options[ 'toolbar_border_style' ] ) . ';
			--helper-toolbar-border-width: ' . esc_attr( Settings::get_sides_css( 'toolbar_border_width', $options ) ) . ';
			--helper-toolbar-border-color: ' . esc_attr( $options[ 'toolbar_border_color' ] ) . ';
			--helper-toolbar-border-radius: ' . esc_attr( Settings::get_sides_css( 'toolbar_border_radius', $options ) ) . ';
			
			--helper-toolbar-icon-size: ' . esc_attr( $options[ 'toolbar_icon_size' ] ) . ( $options['toolbar_icon_size_unit'] ?? 'px' ) . ';
			--helper-toolbar-icon-color: ' . esc_attr( $options[ 'toolbar_icon_color' ] ) . ';
			--helper-toolbar-icon-color-hover: ' . esc_attr( $options[ 'toolbar_icon_color_hover' ] ) . ';
			--helper-toolbar-color: ' . esc_attr( $options[ 'toolbar_color' ] ) . ';
			
			--helper-toolbar-font-size: ' . esc_attr( $options[ 'toolbar_font_size' ] ) . ( $options['toolbar_font_size_unit'] ?? 'px' ) . ';
			
			--helper-recognition-icon-size: ' . esc_attr( $options [ 'bot_sst_size' ] ) . 'px;
			--helper-recognition-icon-color: ' . esc_attr( $options[ 'bot_stt_color' ] ) . ';
			
			--helper-signature-color: ' . esc_attr( $options[ 'upper_line_color' ] ) . ';
			--helper-signature-font-size: ' . esc_attr( $options[ 'upper_line_font_size' ] ) . 'px;
			--helper-signature-icon-color: ' . esc_attr( $options[ 'upper_line_icon_color' ] ) . ';' .

			$this->css_menu_button( $options ) .

		'}';

	}

	/**
	 * Menu button CSS
	 * @param $options
	 *
	 * @return string
	 */
	private function css_menu_button( $options ): string {

		if ( ! empty( $options['bot_features'] ) ) {

			return '
			--helper-menu-button-padding: ' . esc_attr( Settings::get_sides_css( 'menu_button_padding', $options ) ) . ';
			--helper-menu-button-margin: ' . esc_attr( Settings::get_sides_css( 'menu_button_margin', $options ) ) . ';
			--helper-menu-button-font-size: ' . esc_attr( $options[ 'menu_button_font_size' ] ) . 'px;
			--helper-menu-button-border-radius: ' . esc_attr( Settings::get_sides_css( 'menu_button_border_radius', $options ) ) . ';
			--helper-menu-button-border-width: ' . esc_attr( Settings::get_sides_css( 'menu_button_border_width', $options ) ) . ';
			--helper-menu-button-border-color: ' . esc_attr( $options[ 'menu_button_border_color' ] ) . ';
			--helper-menu-button-border-style: ' . esc_attr( $options[ 'menu_button_border_style' ] ) . ';
			--helper-menu-button-background-color: ' . esc_attr( $options[ 'menu_button_background_color' ] ) . ';
			--helper-menu-button-hover-background-color: ' . esc_attr( $options[ 'menu_button_hover_background_color' ] ) . ';
			--helper-menu-button-color: ' . esc_attr( $options[ 'menu_button_color' ] ) . ';
			--helper-menu-button-hover-color: ' . esc_attr( $options[ 'menu_button_hover_color' ] ) . ';
			--helper-menu-button-width: ' . esc_attr( $options[ 'menu_button_width' ] ) . ( $options['bot_container_width_unit'] ?? 'px' ) . ';
			--helper-menu-button-icon-size: ' . esc_attr( $options[ 'menu_button_icon_size' ] ) . 'px;
			--helper-menu-button-icon-color: ' . esc_attr( $options[ 'menu_button_icon_color' ] ) . ';
			';

		}

		return '';

	}

	/**
	 * Box-shadow
	 *
	 * @param string $key
	 * @param array $options
	 *
	 * @return string
	 */
	private static function get_box_shadow( string $key, array $options ): string {

		$base = str_replace( '_box_shadow', '', $key );
		$type = $options[ $key ];

		switch ( $type ) {

			case 'inside':
				return 'inset ' . Settings::get_sides_css( $base . '_box_shadow_offset', $options ) . ' ' . $options[ $base . '_box_shadow_color' ];

			case 'outside':
				return Settings::get_sides_css( $base . '_box_shadow_offset', $options ) . ' ' . $options[ $base . '_box_shadow_color' ];

			case 'none':
			default:
				return 'none';

		}

	}

	private static function get_animation( $key, $options ) {

		$animation = $options[ $key ];

		if ( $animation === 'none' ) { return 'none'; }

		return $options[ $key . '_duration' ] . 's ease ' . $options[ $key . '_delay' ] . 's 1 normal both running ' . $animation;

	}

	/**
	 * Main FrontStyles Instance.
	 *
	 * Insures that only one instance of FrontStyles exists in memory at any one time.
	 *
	 * @static
	 * @return FrontStyles
	 **/
	public static function get_instance(): FrontStyles {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self;

		}

		return self::$instance;

	}

} // End Class FrontStyles.

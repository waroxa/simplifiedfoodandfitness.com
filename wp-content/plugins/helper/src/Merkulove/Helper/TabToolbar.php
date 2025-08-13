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

use Merkulove\Helper\Unity\Plugin;
use Merkulove\Helper\Unity\Settings;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * @package Merkulove\Helper
 **/
final class TabToolbar {

	/**
	 * Controls.
	 * @return array[]
	 */
	public static function controls(): array {

		$options = Settings::get_instance()->options;

		$tabs = Plugin::get_tabs();

		$fields = array_merge(
			self::fields_toolbar_style(),
			self::fields_close_button( $options ),
			in_array( 'bot_commands', $options[ 'bot_features' ] ) ? self::fields_bot_commands() : array(),
			$options[ 'bot_tts' ] === 'on' ? self::fields_mute_button() : array(),
			self::fields_mail_button(),
			self::fields_call_button(),
			self::fields_messenger_button(),
			self::fields_social_button()
		);

		$tabs[ 'toolbar' ][ 'fields' ] = $fields;
		Plugin::set_tabs( $tabs );
		Settings::get_instance()->get_options();

		return $fields;

	}

	/**
	 * Fields header style.
	 * @return array[]
	 */
	private static function fields_toolbar_style(): array {

		$slug = 'toolbar';

		return array_merge(

			[

				$slug . '_divider' => [
					'type' => 'divider',
				],

				$slug . '_title' => [
					'type'              => 'header',
					'label'             => esc_html__( 'Chat toolbar settings', 'helper' ),
					'description'       => esc_html__( 'These settings control toolbar styles of the chatbot window header.', 'helper' ),
				],

				$slug . '_margin' => [
					'type' => 'sides',
					'label' => esc_html__( 'Margin', 'helper' ),
					'description' => esc_html__( 'Set close button margin.', 'helper' ),
					'default' => [
						'top' => 0,
						'right' => 20,
						'bottom' => 0,
						'left' => 20,
					],
				],

				$slug . '_padding' => [
					'type' => 'sides',
					'label' => esc_html__( 'Padding', 'helper' ),
					'description' => esc_html__( 'Set close button padding.', 'helper' ),
					'default' => [
						'top' => 0,
						'right' => 0,
						'bottom' => 20,
						'left' => 0,
					],
				],

			],

			Config::get_instance()->border_controls(
				$slug,
				[
					'border_style' => 'solid',
					'border_color' => '#dbd5ff',
					'border_width' => [
						'top' => '0',
						'right' => '0',
						'bottom' => '1',
						'left' => '0',
					],
				]
			),

			[

				$slug . '_icons_divider' => [
					'type' => 'divider',
				],

				$slug . '_icons_title' => [
					'type'              => 'header',
					'label'             => esc_html__( 'Toolbar elements', 'helper' ),
					'description'       => esc_html__( 'These settings control toolbar links and buttons styles.', 'helper' ),
				],

				$slug . '_icon_size' => [
					'type' => 'slider',
					'label' => esc_html__( 'Icon size', 'helper' ),
					'description' => wp_sprintf(
					/* translators: %s: icon size */
						esc_html__( 'Current icon size: %s pixels.', 'helper' ),
						'<strong>' . esc_attr( ( ! empty( $options[ $slug . '_icon_size' ] ) ) ?
							$options[ $slug . '_icon_size' ] : '22' ) . '</strong>'
					),
					'default' => 22,
					'min' => 1,
					'max' => 40,
					'step' => 1,
					'discrete' => false,
				],

				$slug . '_icon_color' => [
					'type'              => 'colorpicker',
					'label'             => esc_html__( 'Icon color', 'helper' ),
					'description'       => esc_html__( 'Set icon color.', 'helper' ),
					'default'           => '#6a52e2',
				],

				$slug . '_icon_color_hover' => [
					'type'              => 'colorpicker',
					'label'             => esc_html__( 'Icon hover color', 'helper' ),
					'description'       => esc_html__( 'Set icon hover color.', 'helper' ),
					'default'           => '#4f32e6',
				],

				$slug . '_font_size' => [
					'type' => 'slider',
					'label' => esc_html__( 'Font size', 'helper' ),
					'description' => wp_sprintf(
					/* translators: %s: font size */
						esc_html__( 'Current font size: %s pixels.', 'helper' ),
						'<strong>' . esc_attr( ( ! empty( $options[ $slug . '_size' ] ) ) ?
							$options[ $slug . '_size' ] : '14' ) . '</strong>'
					),
					'default' => 14,
					'min' => 1,
					'max' => 40,
					'step' => 1,
					'discrete' => false,
				],

				$slug . '_color' => [
					'type'              => 'colorpicker',
					'label'             => esc_html__( 'Text color', 'helper' ),
					'description'       => esc_html__( 'Set text color.', 'helper' ),
					'default'           => '#4f32e6',
				],

			]

		);

	}

	/**
	 * Fields for header close button.
	 *
	 * @param $options
	 *
	 * @return array
	 */
	private static function fields_close_button( $options ): array {

		$slug = 'close_button';

		return [

			$slug . '_divider' => [
				'type' => 'divider',
			],

			$slug . '_title' => [
				'type'              => 'header',
				'label'             => esc_html__( 'Chat window close button settings', 'helper' ),
				'description'       => esc_html__( 'Display and style settings for the close button inside the header of the chat window. If the button is disabled, then you can close the popup using the ESC button.', 'helper' ),
			],

			$slug . '_show' => [
				'type' => 'switcher',
				'label' => esc_html__( 'Close button', 'helper' ),
				'description' => esc_html__( 'Display close button.', 'helper' ),
				'default' => 'on',
			],

			$slug . '_margin' => [
				'type' => 'sides',
				'label' => esc_html__( 'Close button margin', 'helper' ),
				'description' => esc_html__( 'Set close button margin.', 'helper' ),
				'default' => [
					'top' => 0,
					'right' => 0,
					'bottom' => 0,
					'left' => 0,
				],
			],

			$slug . '_padding' => [
				'type' => 'sides',
				'label' => esc_html__( 'Close button padding', 'helper' ),
				'description' => esc_html__( 'Set close button padding.', 'helper' ),
				'default' => [
					'top' => 0,
					'right' => 0,
					'bottom' => 0,
					'left' => 0,
				],
			],

			$slug . '_icon' => [
				'type'              => 'icon',
				'label'             => esc_html__( 'Close icon', 'helper' ),
				'description'       => esc_html__( 'Set close chat icon.', 'helper' ),
				'default'           => 'helper/close.svg',
				'meta'              => [
					'helper.json',
					'font-awesome.json',
					'material.json'
				]
			],

			$slug . '_size' => [
				'type' => 'slider',
				'label' => esc_html__( 'Icon size', 'helper' ),
				'description' => wp_sprintf(
				/* translators: %s: icon size */
					esc_html__( 'Current icon size: %s pixels.', 'helper' ),
					'<strong>' . esc_attr( ( ! empty( $options[ $slug . '_size' ] ) ) ?
						$options[ $slug . '_size' ] : '22' ) . '</strong>'
				),
				'default' => 22,
				'min' => 1,
				'max' => 40,
				'step' => 1,
				'discrete' => false,
			],

			$slug . '_color' => [
				'type' => 'colorpicker',
				'label' => esc_html__( 'Icon color', 'helper' ),
				'description' => esc_html__( 'Set close icon color.', 'helper' ),
				'default' => '#6a52e2',
			],

			$slug . '_color_hover' => [
				'type' => 'colorpicker',
				'label' => esc_html__( 'Hover Icon color', 'helper' ),
				'description' => esc_html__( 'Set close icon color on hover.', 'helper' ),
				'default' => '#4f32e6',
			],

		];

	}

	/**
	 * Fields bot commands.
	 * @return array
	 */
	private static function fields_bot_commands(): array {

		$slug = 'bot_commands';

		return [

			$slug . '_divider' => [
				'type' => 'divider',
			],

			$slug . '_title' => [
				'type'              => 'header',
				'label'             => esc_html__( 'Bot commands button', 'helper' ),
				'description'       => esc_html__( 'These settings bot commands button styles of the chatbot window header.', 'helper' ),
			],

			$slug . '_enable' => [
				'type' => 'switcher',
				'label' => esc_html__( 'Bot commands button', 'helper' ),
				'description' => esc_html__( 'Display bot commands button.', 'helper' ),
				'default' => 'on',
			],

			$slug . '_icon' => [
				'type'              => 'icon',
				'label'             => esc_html__( 'Bot commands icon', 'helper' ),
				'description'       => esc_html__( 'Set bot commands icon.', 'helper' ),
				'default'           => 'helper/commands.svg',
				'meta'              => [
					'helper.json',
					'font-awesome.json',
					'material.json'
				]
			],

			$slug . '_text' => [
				'type' => 'text',
				'label' => esc_html__( 'Bot commands text', 'helper' ),
				'description' => esc_html__( 'Set bot commands text.', 'helper' ),
				'default' => esc_html__( 'Help', 'helper' ),
			],

		];

	}

	/**
	 * Fields: Mute button.
	 * @return array
	 */
	private static function fields_mute_button(): array {

		$slug = 'mute_button';

		return [

			$slug . '_divider' => [
				'type' => 'divider',
			],

			$slug . '_title' => [
				'type'              => 'header',
				'label'             => esc_html__( 'Mute button', 'helper' ),
				'description'       => esc_html__( '', 'helper' ),
			],

			$slug . '_enable' => [
				'type'              => 'switcher',
				'label'             => esc_html__( 'Enable mute button', 'helper' ),
				'description'       => esc_html__( 'Enable mute button.', 'helper' ),
				'default'           => 'on',
			],

			$slug . '_text' => [
				'type'              => 'text',
				'label'             => esc_html__( 'Mute button text', 'helper' ),
				'description'       => esc_html__( 'Set mute button text.', 'helper' ),
				'default'           => esc_html__( 'Voicing', 'helper' ),
			],

			$slug . '_icon' => [
				'type'              => 'icon',
				'label'             => esc_html__( 'Mute button icon', 'helper' ),
				'description'       => esc_html__( 'Set mute button icon.', 'helper' ),
				'default'           => 'helper/voicing.svg',
				'meta'              => [
					'helper.json',
					'font-awesome.json',
					'material.json'
				]
			],

		];

	}

	/**
	 * Fields: Bot commands.
	 * @return array
	 */
	private static function fields_mail_button(): array {

		$slug = 'mail_button';

		return [

			$slug . '_divider' => [
				'type' => 'divider',
			],

			$slug . '_title' => [
				'type'              => 'header',
				'label'             => esc_html__( 'Mail button', 'helper' ),
				'description'       => esc_html__( '', 'helper' ),
			],

			$slug . '_enable' => [
				'type'              => 'switcher',
				'label'             => esc_html__( 'Enable mail button', 'helper' ),
				'description'       => esc_html__( 'Enable mail button.', 'helper' ),
				'default'           => 'on',
			],

			$slug . '_text' => [
				'type'              => 'text',
				'label'             => esc_html__( 'Mail button text', 'helper' ),
				'description'       => esc_html__( 'Set mail button text.', 'helper' ),
				'default'           => esc_html__( 'E-mail', 'helper' ),
			],

			$slug . '_link' => [
				'type'              => 'text',
				'label'             => esc_html__( 'Mail button link', 'helper' ),
				'description'       => esc_html__( 'Set mail button link.', 'helper' ),
				'default'           => 'mailto:' . get_option( 'admin_email' ),
			],

			$slug . '_icon' => [
				'type'              => 'icon',
				'label'             => esc_html__( 'Mail button icon', 'helper' ),
				'description'       => esc_html__( 'Set mail button icon.', 'helper' ),
				'default'           => 'helper/mail.svg',
				'meta'              => [
					'helper.json',
					'font-awesome.json',
					'material.json'
				]
			],

		];

	}

	/**
	 * Fields: Call button
	 * @return array
	 */
	private static function fields_call_button(): array {

		$slug = 'call_button';

		return [

			$slug . '_divider' => [
				'type' => 'divider',
			],

			$slug . '_title' => [
				'type'              => 'header',
				'label'             => esc_html__( 'Call button', 'helper' ),
				'description'       => esc_html__( '', 'helper' ),
			],

			$slug . '_enable' => [
				'type'              => 'switcher',
				'label'             => esc_html__( 'Enable call button', 'helper' ),
				'description'       => esc_html__( 'Enable call button.', 'helper' ),
				'default'           => 'on',
			],

			$slug . '_text' => [
				'type'              => 'text',
				'label'             => esc_html__( 'Call button text', 'helper' ),
				'description'       => esc_html__( 'Set call button text.', 'helper' ),
				'default'           => esc_html__( 'Call', 'helper' ),
			],

			$slug . '_link' => [
				'type'              => 'text',
				'label'             => esc_html__( 'Call button link', 'helper' ),
				'description'       => esc_html__( 'Set call button link.', 'helper' ),
				'default'           => 'tel:911',
			],

			$slug . '_icon' => [
				'type'              => 'icon',
				'label'             => esc_html__( 'Call button icon', 'helper' ),
				'description'       => esc_html__( 'Set call button icon.', 'helper' ),
				'default'           => 'helper/call.svg',
				'meta'              => [
					'helper.json',
					'font-awesome.json',
					'material.json'
				]
			],

		];

	}

	/**
	 * Fields: Messenger button
	 * @return array
	 */
	private static function fields_messenger_button(): array {

		$slug = 'messenger_button';

		return [

			$slug . '_divider' => [
				'type' => 'divider',
			],

			$slug . '_title' => [
				'type'              => 'header',
				'label'             => esc_html__( 'Messenger button', 'helper' ),
				'description'       => esc_html__( '', 'helper' ),
			],

			$slug . '_enable' => [
				'type'              => 'switcher',
				'label'             => esc_html__( 'Enable messenger button', 'helper' ),
				'description'       => esc_html__( 'Enable messenger button.', 'helper' ),
				'default'           => 'on',
			],

			$slug . '_text' => [
				'type'              => 'text',
				'label'             => esc_html__( 'Messenger button text', 'helper' ),
				'description'       => esc_html__( 'Set messenger button text.', 'helper' ),
				'default'           => esc_html__( 'Chat', 'helper' ),
			],

			$slug . '_link' => [
				'type'              => 'text',
				'label'             => esc_html__( 'Messenger button link', 'helper' ),
				'description'       => esc_html__( 'Set messenger button link.', 'helper' ),
				'default'           => '',
			],

			$slug . '_icon' => [
				'type'              => 'icon',
				'label'             => esc_html__( 'Messenger button icon', 'helper' ),
				'description'       => esc_html__( 'Set messenger button icon.', 'helper' ),
				'default'           => 'helper/telegram.svg',
				'meta'              => [
					'helper.json',
					'font-awesome.json',
					'material.json'
				]
			],

		];

	}

	/**
	 * Fields: Social button
	 * @return array
	 */
	private static function fields_social_button(): array {

		$slug = 'social_button';

		return [

			$slug . '_divider' => [
				'type' => 'divider',
			],

			$slug . '_title' => [
				'type'              => 'header',
				'label'             => esc_html__( 'Social contact button', 'helper' ),
				'description'       => esc_html__( '', 'helper' ),
			],

			$slug . '_enable' => [
				'type'              => 'switcher',
				'label'             => esc_html__( 'Enable social contact button', 'helper' ),
				'description'       => esc_html__( 'Enable social contact button.', 'helper' ),
				'default'           => 'on',
			],

			$slug . '_text' => [
				'type'              => 'text',
				'label'             => esc_html__( 'Social contact button text', 'helper' ),
				'description'       => esc_html__( 'Set social contact button text.', 'helper' ),
				'default'           => esc_html__( 'Facebook', 'helper' ),
			],

			$slug . '_link' => [
				'type'              => 'text',
				'label'             => esc_html__( 'Social contact button link', 'helper' ),
				'description'       => esc_html__( 'Set social contact button link.', 'helper' ),
				'default'           => 'https://www.facebook.com/merkuloveteam',
			],

			$slug . '_icon' => [
				'type'              => 'icon',
				'label'             => esc_html__( 'Social contact button icon', 'helper' ),
				'description'       => esc_html__( 'Set social contact button icon.', 'helper' ),
				'default'           => 'helper/facebook.svg',
				'meta'              => [
					'helper.json',
					'font-awesome.json',
					'material.json'
				]
			],

		];

	}

}


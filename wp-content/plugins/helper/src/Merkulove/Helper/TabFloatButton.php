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
final class TabFloatButton {

	/**
	 * Controls.
	 * @return array[]
	 */
	public static function controls(): array {

		$tabs = Plugin::get_tabs();

        $options = Settings::get_instance()->options;

        $fields = array_merge(
			self::fields_button_style(),
			self::fields_button_icon( $options ),
			self::fields_button_caption( $options )
		);

		$tabs[ 'float_button' ][ 'fields' ] = $fields;
		Plugin::set_tabs( $tabs );
		Settings::get_instance()->get_options();

		return $fields;

	}

	/**
	 * Button style
	 * @return array
	 */
	private static function fields_button_style(): array {

		$slug = 'open_bot_button';

		return array_merge(

			[

				$slug . '_divider' => [
					'type' => 'divider',
				],

				$slug . '_heading' => [
					'type'              => 'header',
					'label'             => esc_html__( 'Open Bot button', 'helper' ),
					'description'       => esc_html__( 'Settings responsible for the display and styles of the floating button', 'helper' ),
				],

				'open_bot_with_button' => [
					'type'              => 'switcher',
					'label'             => esc_html__( 'Open bot with button', 'helper' ),
					'placeholder'       => esc_html__( 'Open bot with button', 'helper' ),
					'description'       => esc_html__( 'Open bot with button', 'helper' ),
					'default'           => 'on',
				],

				$slug . '_alignment' => [
					'type'              => 'select',
					'label'             => esc_html__( 'Open bot button alignment', 'helper' ),
					'description'       => esc_html__( 'Choose open bot button alignment', 'helper' ),
					'default'           => 'right',
					'options'           => [
						'left' => esc_html__( 'Left', 'helper' ),
						'center' => esc_html__( 'Center', 'helper' ),
						'right' => esc_html__( 'Right', 'helper' ),
					],
				],

				$slug . '_padding' => [
					'type'			  => 'sides',
					'label'			  => esc_html__( 'Padding', 'helper' ),
					'description'	  => esc_html__( 'Open button padding', 'helper' ),
					'default'         => [
						'top' => '20',
						'right' => '20',
						'bottom' => '20',
						'left' => '20',
					],
				],

				$slug . '_margin' => [
					'type'			  => 'sides',
					'label'			  => esc_html__( 'Margin', 'helper' ),
					'description'	  => esc_html__( 'Open button margin', 'helper' ),
					'default'         => [
						'top' => '20',
						'right' => '20',
						'bottom' => '20',
						'left' => '20',
					],
				],

				$slug . '_background_color' => [
					'type' => 'colorpicker',
					'label' => esc_html__( 'Background color', 'helper' ),
					'description' => esc_html__( 'Set open button background color.', 'helper' ),
					'default' => '#4f32e6',
				],

				$slug . '_background_hover_color' => [
					'type' => 'colorpicker',
					'label' => esc_html__( 'Background hover color', 'helper' ),
					'description' => esc_html__( 'Set open button background hover color.', 'helper' ),
					'default' => '#00db66',
				]

			],

			Config::get_instance()->border_controls(
				$slug,
				[
					'border_style' => 'none',
					'border_color' => '#4f32e6',
					'border_width' => [
						'top' => '0',
						'right' => '0',
						'bottom' => '0',
						'left' => '0',
					],
					'border_radius' => [
						'top' => '50',
						'right' => '50',
						'bottom' => '50',
						'left' => '50',
					],
				]
			),

			[

				$slug . '_box_shadow' => [
					'type' => 'select',
					'label' => esc_html__( 'Box shadow', 'helper' ),
					'description' => esc_html__( 'Set chat header box shadow.', 'helper' ),
					'default' => 'none',
					'options' => [
						'none' => esc_html__( 'None', 'helper' ),
						'outside' => esc_html__( 'Outside box-shadow', 'helper' ),
						'inside' => esc_html__( 'Inside box-shadow', 'helper' ),
					],
				],

				$slug . '_box_shadow_offset' => [
					'type' => 'sides',
					'label' => esc_html__( 'Box shadow offset', 'helper' ),
					'description' => esc_html__( 'Set chat header box shadow offset.', 'helper' ),
					'default' => [
						'top' => '0',
						'right' => '-5',
						'bottom' => '20',
						'left' => '0',
					],
					'labels' => [
						'top' => esc_html__( 'X-offset', 'helper' ),
						'right' => esc_html__( 'Y-offset', 'helper' ),
						'bottom' => esc_html__( 'Blur', 'helper' ),
						'left' => esc_html__( 'Spread', 'helper' ),
					],
				],

				$slug . '_box_shadow_color' => [
					'type' => 'colorpicker',
					'label' => esc_html__( 'Box shadow color', 'helper' ),
					'description' => esc_html__( 'Set chat header box shadow color.', 'helper' ),
					'default' => 'rgba(0,0,0,.08)',
				],

				$slug . '_aura' => [
					'type' => 'switcher',
					'label' => esc_html__( 'Aura animation', 'helper' ),
					'description' => esc_html__( 'Enable aura animation on hover', 'helper' ),
					'default' => 'on',
				],

			]

		);

	}

	/**
	 * Get fields for button icon
	 * @return array
	 */
	private static function fields_button_icon( $options ): array {

		$slug = 'open_bot_button';

        $default_icon_size = 24;
        $current_icon_size = ! empty( $options[ $slug . '_icon_size' ] ) ? $options[ $slug . '_icon_size' ] : $default_icon_size;

		return [

			$slug . '_icon_divider' => [
				'type' => 'divider',
			],

			$slug . '_icon_heading' => [
				'type'              => 'header',
				'label'             => esc_html__( 'Button icon', 'helper' ),
				'description'       => esc_html__( 'Styles of the icon in the floating button opens the chat bot', 'helper' ),
			],

			$slug . '_enable_icon' => [
				'type' => 'switcher',
				'label' => esc_html__( 'Enable icon', 'helper' ),
				'description' => esc_html__( 'Enable or disable open button icon.', 'helper' ),
				'default' => 'on',
			],

			$slug . '_icon' => [
				'type'              => 'icon',
				'label'             => esc_html__( 'Open button icon', 'helper' ),
				'placeholder'       => '',
				'description'       => esc_html__( 'Set open button icon.', 'helper' ),
				'default'           => 'helper/helper.svg',
				'meta'              => [ 'helper.json', 'font-awesome.json', 'material.json' ]
			],

			$slug . '_icon_size' => [
				'type' => 'slider',
				'label' => esc_html__( 'Icon size', 'helper' ),
                'description' => wp_sprintf(
                /* translators: %s: Current icon-size */
                    esc_html__( 'Current icon size is: %s pixel(s)', 'helper' ),
                    '<strong>' . esc_attr( $current_icon_size ) . '</strong>'
                ),
				'default' => 24,
				'min' => 8,
				'max' => 40,
				'step' => 1,
			],

			$slug . '_icon_color' => [
				'type' => 'colorpicker',
				'label' => esc_html__( 'Icon color', 'helper' ),
				'description' => esc_html__( 'Set open button icon color.', 'helper' ),
				'default' => '#ffffff',
			],

			$slug . '_icon_hover_color' => [
				'type' => 'colorpicker',
				'label' => esc_html__( 'Icon hover color', 'helper' ),
				'description' => esc_html__( 'Set open button icon hover color.', 'helper' ),
				'default' => '#000000',
			],

		];

	}

	/**
	 * Get fields for button caption
	 * @return array
	 */
	private static function fields_button_caption( $options ): array {

		$slug = 'open_bot_button';

        $default_font_size = 16;
        $current_font_size = ! empty( $options[ $slug . '_font_size' ] ) ? $options[ $slug . '_font_size' ] : $default_font_size;

		return [

			$slug . '_caption_divider' => [
				'type' => 'divider',
			],

			$slug . '_caption_heading' => [
				'type'              => 'header',
				'label'             => esc_html__( 'Button caption', 'helper' ),
				'description'       => esc_html__( 'Styles of the button caption in the floating button', 'helper' ),
			],

			$slug . '_enable_caption' => [
				'type' => 'switcher',
				'label' => esc_html__( 'Enable caption', 'helper' ),
				'description' => esc_html__( 'Enable or disable open button caption.', 'helper' ),
				'default' => 'off',
			],

            $slug . '_hover_color' => [
                'type' => 'colorpicker',
                'label' => esc_html__( 'Caption text hover color', 'helper' ),
                'description' => esc_html__( 'Set open caption text hover color.', 'helper' ),
                'default' => '#000000',
            ],

            $slug . '_color' => [
                'type' => 'colorpicker',
                'label' => esc_html__( 'Text color', 'helper' ),
                'description' => esc_html__( 'Set caption text color.', 'helper' ),
                'default' => '#ffffff',
            ],

			$slug . '_caption' => [
				'type'              => 'text',
				'label'             => esc_html__( 'Open button text', 'helper' ),
				'description'       => esc_html__( 'Set open bot button text', 'helper' ),
				'default'           => esc_html__( 'Open helper', 'helper' ),
			],

			$slug . '_font_size' => [
				'type' => 'slider',
				'label' => esc_html__( 'Font size', 'helper' ),
                'description' => wp_sprintf(
                /* translators: %s: Current font-size */
                    esc_html__( 'Current font size is: %s pixel(s)', 'helper' ),
                    '<strong>' . esc_attr( $current_font_size ) . '</strong>'
                ),
				'default' => 16,
				'min' => 4,
				'max' => 40,
				'step' => 1,
			],

		];

	}

}


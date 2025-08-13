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
final class TabForm {

	/**
	 * Controls.
	 * @return array[]
	 */
	public static function controls(): array {

		$tabs = Plugin::get_tabs();

        $options = Settings::get_instance()->options;

		$fields = array_merge(
			self::fields_message_input( $options ),
			self::fields_send_button( $options )
		);

		$tabs[ 'form' ][ 'fields' ] = $fields;
		Plugin::set_tabs( $tabs );

		return $fields;

	}

    /**
     * Message input fields.
     * @param $options
     * @return array
     */
	private static function fields_message_input( $options ): array {

		$slug = 'message_input';

		$default_font_size = 16;
		$current_font_size = ! empty( $options[ $slug . '_font_size' ] ) ? $options[ $slug . '_font_size' ] : $default_font_size;

		return array_merge(

			[

				$slug . '_divider' => [
					'type' => 'divider',
				],

				$slug . '_heading' => [
					'type'              => 'header',
					'label'             => esc_html__( 'Message input field settings', 'helper' ),
					'description'       => esc_html__( 'This block of settings determines how the form for sending a message will look and work.', 'helper' ),
				],

				$slug . '_padding' => [
					'type'			  => 'sides',
					'label'			  => esc_html__( 'Padding', 'helper' ),
					'description'	  => esc_html__( 'Set avatar padding.', 'helper' ),
					'default'         => [
						'top' => '12',
						'right' => '12',
						'bottom' => '12',
						'left' => '12',
					],
				],

				$slug . '_margin' => [
					'type'			  => 'sides',
					'label'			  => esc_html__( 'Margin', 'helper' ),
					'description'	  => esc_html__( 'Set bot border radius.', 'helper' ),
					'default'         => [
						'top' => '0',
						'right' => '0',
						'bottom' => '0',
						'left' => '0',
					],
				],

				$slug . '_font_size' => [
					'type' => 'slider',
					'label' => esc_html__( 'Font size', 'helper' ),
					'description' => wp_sprintf(
						/* translators: %s: Current font-size */
						esc_html__( 'Current input font-size is: %s pixel(s)', 'helper' ),
						'<strong>' . esc_attr( $current_font_size ) . '</strong>'
					),
					'default' => $default_font_size,
					'min' => '10',
					'max' => '30',
					'step' => '1',
					'discrete' => true,
				],

				$slug . '_color' => [
					'type' => 'colorpicker',
					'label' => esc_html__( 'Color', 'helper' ),
					'description' => esc_html__( 'Set send button color.', 'helper' ),
					'default' => '#000',
				],

				$slug . '_background_color' => [
					'type' => 'colorpicker',
					'label' => esc_html__( 'Background color', 'helper' ),
					'description' => esc_html__( 'Set chat header background color.', 'helper' ),
					'default' => '#fff',
				],

				$slug . '_hover_color' => [
					'type' => 'colorpicker',
					'label' => esc_html__( 'Hover color', 'helper' ),
					'description' => esc_html__( 'Set send button color.', 'helper' ),
					'default' => '#000',
				],

				$slug . '_hover_background_color' => [
					'type' => 'colorpicker',
					'label' => esc_html__( 'Hover background color', 'helper' ),
					'description' => esc_html__( 'Set chat header background color.', 'helper' ),
					'default' => '#fff',
				],

			],

			Config::get_instance()->border_controls(
				$slug,
				[
					'border_style' => 'solid',
					'border_color' => '#4f32e6',
					'border_width' => [
						'top' => '2',
						'right' => '2',
						'bottom' => '2',
						'left' => '2',
					],
					'border_radius' => [
						'top' => '10',
						'right' => '10',
						'bottom' => '10',
						'left' => '10',
					]
				]
			)

		);

	}

    /**
     * Send button fields.
     * @param $options
     * @return array
     */
	private static function fields_send_button( $options ): array {

		$slug = 'send_button';

		$default_font_size = 16;
		$current_font_size = ! empty( $options[ $slug . '_font_size' ] ) ? $options[ $slug . '_font_size' ] : $default_font_size;

		$default_icon_size = 16;
		$current_icon_size = ! empty( $options[ $slug . '_icon_size' ] ) ? $options[ $slug . '_icon_size' ] : $default_icon_size;

		return array_merge(

			[

				$slug . '_divider' => [
					'type' => 'divider',
				],

				$slug . '_heading' => [
					'type'              => 'header',
					'label'             => esc_html__( 'Send message button settings', 'helper' ),
					'description'       => esc_html__( 'This block of settings determines how the message submit button  will look and work.', 'helper' ),
				],

				$slug . '_show' => [
					'type' => 'switcher',
					'label' => esc_html__( 'Show send button', 'helper' ),
					'description' => esc_html__( 'Show send button.', 'helper' ),
					'default' => 'yes',
				],

				$slug . '_padding' => [
					'type'			  => 'sides',
					'label'			  => esc_html__( 'Padding', 'helper' ),
					'description'	  => esc_html__( 'Set avatar padding.', 'helper' ),
					'default'         => [
						'top' => '10',
						'right' => '10',
						'bottom' => '10',
						'left' => '10',
					],
				],

				$slug . '_margin' => [
					'type'			  => 'sides',
					'label'			  => esc_html__( 'Margin', 'helper' ),
					'description'	  => esc_html__( 'Set bot border radius.', 'helper' ),
					'default'         => [
						'top' => '0',
						'right' => '0',
						'bottom' => '0',
						'left' => '10',
					],
				],

				$slug . '_overlap' => [
					'type' => 'switcher',
					'label' => esc_html__( 'Overlap', 'helper' ),
					'description' => esc_html__( 'Send button overlap the input field.', 'helper' ),
					'default' => 'off',
				],

				$slug . '_placeholder' => [
					'type' => 'text',
					'label' => esc_html__( 'Placeholder', 'helper' ),
					'description' => esc_html__( 'Set send button placeholder.', 'helper' ),
					'default' => esc_html__( 'Type your message', 'helper' )
				],

				$slug . '_caption' => [
					'type' => 'text',
					'label' => esc_html__( 'Caption', 'helper' ),
					'description' => esc_html__( 'Set send button caption. Leave empty if you want to use icon only.', 'helper' ),
					'default' => '',
				],

				$slug . '_font_size' => [
					'type' => 'slider',
					'label' => esc_html__( 'Font size', 'helper' ),
					'description' => wp_sprintf(
						/* translators: %s: Current font-size */
						esc_html__( 'Current caption font-size is: %s pixel(s)', 'helper' ),
						'<strong>' . esc_attr( $current_font_size ) . '</strong>'
					),
					'default' => $default_font_size,
					'min' => '10',
					'max' => '30',
					'step' => '1',
					'discrete' => true,
				],

				$slug . '_icon' => [
					'type' => 'icon',
					'label' => esc_html__( 'Icon', 'helper' ),
					'description' => esc_html__( 'Set send button icon.', 'helper' ),
					'default' => 'material/enter-arrow.svg',
					'meta' => [ 'font-awesome.json', 'material.json' ]
				],

				$slug . '_icon_size' => [
					'type' => 'slider',
					'label' => esc_html__( 'Icon size', 'helper' ),
					'description' => wp_sprintf(
						/* translators: %s: Current icon size */
						esc_html__( 'Current icon size is: %s pixel(s)', 'helper' ),
						'<strong>' . esc_attr( $current_icon_size ) . '</strong>'
					),
					'default' => $default_icon_size,
					'min' => '10',
					'max' => '30',
					'step' => '1',
					'discrete' => true,
				],

				$slug . '_color' => [
					'type' => 'colorpicker',
					'label' => esc_html__( 'Color', 'helper' ),
					'description' => esc_html__( 'Set send button color.', 'helper' ),
					'default' => '#ffffff',
				],

				$slug . '_background_color' => [
					'type' => 'colorpicker',
					'label' => esc_html__( 'Background color', 'helper' ),
					'description' => esc_html__( 'Set chat header background color.', 'helper' ),
					'default' => '#4f32e6',
				],

				$slug . '_hover_color' => [
					'type' => 'colorpicker',
					'label' => esc_html__( 'Hover color', 'helper' ),
					'description' => esc_html__( 'Set send button color.', 'helper' ),
					'default' => '#000000',
				],

				$slug . '_hover_background_color' => [
					'type' => 'colorpicker',
					'label' => esc_html__( 'Hover background color', 'helper' ),
					'description' => esc_html__( 'Set chat header background color.', 'helper' ),
					'default' => '#00db66',
				],

			],

			Config::get_instance()->border_controls(
				$slug,
				[
					'border_style' => 'solid',
					'border_color' => '#4f32e6',
					'border_width' => [
						'top' => '2',
						'right' => '2',
						'bottom' => '2',
						'left' => '2',
					],
					'border_radius' => [
						'top' => '10',
						'right' => '10',
						'bottom' => '10',
						'left' => '10',
					]
				]

			)

		);

	}

}


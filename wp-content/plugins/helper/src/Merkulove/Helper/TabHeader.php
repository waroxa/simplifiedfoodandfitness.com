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
final class TabHeader {

	/**
	 * Controls.
	 * @return array[]
	 */
	public static function controls(): array {

		$options = Settings::get_instance()->options;

		$tabs = Plugin::get_tabs();

		$fields = array_merge(
			self::fields_header_style(),
			self::fields_header_text( $options ),
		);

		$tabs[ 'header' ][ 'fields' ] = $fields;
		Plugin::set_tabs( $tabs );
		Settings::get_instance()->get_options();

		return $fields;

	}

	/**
	 * Fields header style.
	 * @return array[]
	 */
	private static function fields_header_style(): array {

		$slug = 'chat_header';

		return [

			$slug . '_divider_styles' => [
				'type' => 'divider',
			],

			$slug . '_style_title' => [
				'type'              => 'header',
				'label'             => esc_html__( 'Chat window header settings', 'helper' ),
				'description'       => esc_html__( 'These settings control the global styles of the chatbot window header.', 'helper' ),
			],

			$slug . '_padding' => [
				'type'			  => 'sides',
				'label'			  => esc_html__( 'Padding', 'helper' ),
				'description'	  => esc_html__( 'Set avatar padding.', 'helper' ),
				'default'         => [
					'top' => '5',
					'right' => '5',
					'bottom' => '5',
					'left' => '5',
				],
			],

			$slug . '_margin' => [
				'type'			  => 'sides',
				'label'			  => esc_html__( 'Margin', 'helper' ),
				'description'	  => esc_html__( 'Set bot border radius.', 'helper' ),
				'default'         => [
					'top' => '20',
					'right' => '20',
					'bottom' => '20',
					'left' => '20',
				],
			],

			$slug . '_border_radius' => [
				'type' => 'sides',
				'label' => esc_html__( 'Border radius', 'helper' ),
				'description' => esc_html__( 'Set chat header border radius.', 'helper' ),
				'default' => [
					'top' => '5',
					'right' => '5',
					'bottom' => '5',
					'left' => '5',
				],
			],

			$slug . '_background_color' => [
				'type' => 'colorpicker',
				'label' => esc_html__( 'Background color', 'helper' ),
				'description' => esc_html__( 'Set chat header background color.', 'helper' ),
				'default' => '#4f32e6',
			],

			$slug . '_border_style' => [
				'type' => 'select',
				'label' => esc_html__( 'Border style', 'helper' ),
				'description' => esc_html__( 'Set chat header border style.', 'helper' ),
				'default' => 'none',
				'options' => [
					'none' => esc_html__( 'None', 'helper' ),
					'solid' => esc_html__( 'Solid', 'helper' ),
					'dashed' => esc_html__( 'Dashed', 'helper' ),
					'dotted' => esc_html__( 'Dotted', 'helper' ),
					'double' => esc_html__( 'Double', 'helper' ),
					'groove' => esc_html__( 'Groove', 'helper' ),
					'ridge' => esc_html__( 'Ridge', 'helper' ),
					'inset' => esc_html__( 'Inset', 'helper' ),
					'outset' => esc_html__( 'Outset', 'helper' ),
				],
			],

			$slug . '_border_color' => [
				'type' => 'colorpicker',
				'label' => esc_html__( 'Border color', 'helper' ),
				'description' => esc_html__( 'Set chat header border color.', 'helper' ),
				'default' => '#4f32e6',
			],

			$slug . '_border_width' => [
				'type' => 'sides',
				'label' => esc_html__( 'Border width', 'helper' ),
				'description' => esc_html__( 'Set chat header border width.', 'helper' ),
				'default' => [
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
				],
			],

		];

	}

	/**
	 * Fields for header text.
	 *
	 * @param $options
	 *
	 * @return array
	 */
	private static function fields_header_text( $options ): array {

		$slug = 'chat_header';

		return [

			$slug . '_divider' => [
				'type' => 'divider',
			],

			$slug . '_title' => [
				'type'              => 'header',
				'label'             => esc_html__( 'Chat window header text settings', 'helper' ),
				'description'       => esc_html__( 'Display and style settings for the header inside the header of the chat window.', 'helper' ),
			],

			$slug . '_heading' => [
				'type' => 'switcher',
				'label' => esc_html__( 'Chat header', 'helper' ),
				'description' => esc_html__( 'Display chat header.', 'helper' ),
				'default' => 'off',
			],

			$slug . '_text_color' => [
				'type' => 'colorpicker',
				'label' => esc_html__( 'Text color', 'helper' ),
				'description' => esc_html__( 'Set chat header text color.', 'helper' ),
				'default' => '#ffffff',
			],

			$slug . '_text_size' => [
				'type' => 'slider',
				'label' => esc_html__( 'Chat header text size', 'helper' ),
				'placeholder' => esc_html__( 'Chat header text size', 'helper' ),
				'description' => wp_sprintf(
				/* translators: %s: text size */
					esc_html__( 'Current heading size: %s pixels.', 'helper' ),
					'<strong>' . esc_attr( ( ! empty( $options[ $slug . '_text_size' ] ) ) ?
						$options[ $slug . '_text_size' ] : '16' ) . '</strong>'
				),
				'default' => '16',
				'min' => '10',
				'max' => '30',
				'step' => '1',
				'discrete'  => 'true',
			],

			$slug . '_text' => [
				'type' => 'editor',
				'label' => esc_html__( 'Chat header text', 'helper' ),
				'placeholder' => esc_html__( 'Chat header text', 'helper' ),
				'description' => esc_html__( 'Set chat header text.', 'helper' ),
				'default' => wp_sprintf(
					'<h4>%s</h4>',
					esc_html__( 'Chat with us', 'helper' )
				),
				'attr' => [
					'textarea_rows' => '3',
				]
			],

		];

	}

}

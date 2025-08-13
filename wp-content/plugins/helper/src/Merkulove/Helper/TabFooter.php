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
final class TabFooter {

	/**
	 * Controls.
	 * @return array[]
	 */
	public static function controls(): array {

		$options = Settings::get_instance()->options;
		$tabs = Plugin::get_tabs();

		$fields = array_merge(
			self::fields_footer_styles(),
			self::fields_footer_text( $options ),
		);

		$tabs[ 'footer' ][ 'fields' ] = $fields;
		Plugin::set_tabs( $tabs );
		Settings::get_instance()->get_options();

		return $fields;

	}

	/**
	 * Footer styles fields.
	 * @return array
	 */
	private static function fields_footer_styles(): array {

		$slug = 'chat_footer';

		return array_merge(

			[

				$slug . '_' => [
					'type' => 'divider',
				],

				$slug . '_heading' => [
					'type'              => 'header',
					'label'             => esc_html__( 'Chat window footer styles', 'helper' ),
					'description'       => esc_html__( 'Settings for managing footer styles in the chat window. The footer contains a form for sending a message and a short text block can be placed.', 'helper' ),
				],

				$slug . '_padding' => [
					'type'			  => 'sides',
					'label'			  => esc_html__( 'Padding', 'helper' ),
					'description'	  => esc_html__( 'Set avatar padding.', 'helper' ),
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
					'description'	  => esc_html__( 'Set bot border radius.', 'helper' ),
					'default'         => [
						'top' => '0',
						'right' => '0',
						'bottom' => '0',
						'left' => '0',
					],
				],

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
						'top' => '0',
						'right' => '0',
						'bottom' => '0',
						'left' => '0',
					],
				]
			),

			[

				$slug . '_background_color' => [
					'type' => 'colorpicker',
					'label' => esc_html__( 'Background color', 'helper' ),
					'description' => esc_html__( 'Set chat header background color.', 'helper' ),
					'default' => 'transparent',
				],

			]

		);

	}

	/**
	 * Footer text fields.
	 * @return array[]
	 */
	private static function fields_footer_text( $options ): array {

		$slug = 'chat_footer_text';

		$default_font_size = '12';
		$current_font_size = ! empty( $options[ $slug . '_font_size' ] ) ? $options[ $slug . '_font_size' ] : $default_font_size;

		return [

			$slug . '_' => [
				'type' => 'divider',
			],

			$slug . '_heading' => [
				'type'              => 'header',
				'label'             => esc_html__( 'Footer text description settings', 'helper' ),
				'description'       => esc_html__( 'This group of settings controls the text that is displayed at the bottom of the chat window.', 'helper' ),
			],

			$slug . '_show' => [
				'type' => 'switcher',
				'label' => esc_html__( 'Footer text content', 'helper' ),
				'description' => esc_html__( 'Show text in the footer of the chatbot window.', 'helper' ),
				'default' => 'on',
			],

			$slug . '_message' => [
				'type' => 'editor',
				'label' => esc_html__( 'Footer text', 'helper' ),
				'description' => esc_html__( 'This text is displayed in the footer of the chatbot window.', 'helper' ),
				'default' => wp_sprintf(
					/* translators: %s: Plugin website */
					esc_html__( 'For more information visit %s page.', 'helper' ),
					'<a href="https://1.envato.market/helper" target="_blank" rel="noreferrer">Helper</a>',
				),
				'attr'              => [
					'textarea_rows' => 4,
					'media_buttons' => false
				]
			],

			$slug . '_color' => [
				'type' => 'colorpicker',
				'label' => esc_html__( 'Text color', 'helper' ),
				'description' => esc_html__( 'Set text color in chat footer.', 'helper' ),
				'default' => '#717077',
			],


			$slug . '_font_size' => [
				'type' => 'slider',
				'label' => esc_html__( 'Font size', 'helper' ),
				'description' => wp_sprintf(
					/* translators: %s: Current font-size */
					esc_html__( 'Current footer font-size is: %s pixel(s)', 'helper' ),
					'<strong>' . esc_attr( $current_font_size ) . '</strong>'
				),
				'default' => esc_attr( $default_font_size ),
				'min' => '2',
				'max' => '32',
				'step' => '1',
				'discrete' => true,
			],

		];

	}

}


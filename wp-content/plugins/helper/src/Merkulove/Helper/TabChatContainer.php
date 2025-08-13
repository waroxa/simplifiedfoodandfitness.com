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

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * @package Merkulove\Helper
 **/
final class TabChatContainer {

	/**
	 * Controls.
	 * @return array[]
	 */
	public static function controls(): array {

		$tabs = Plugin::get_tabs();

		$fields = array_merge(
			self::fields_chat_container(),
			self::fields_scrollbar()
		);

		$tabs[ 'chat_container' ][ 'fields' ] = $fields;
		Plugin::set_tabs( $tabs );

		return $fields;

	}

	/**
	 * Fields chat container.
	 * @return array[]
	 */
	private static function fields_chat_container(): array {

		$slug = 'chat_container';

		return array_merge(

			[

				$slug . '_divider' => [
					'type' => 'divider',
				],

				$slug . '_heading' => [
					'type'              => 'header',
					'label'             => esc_html__( 'Chat container styles', 'helper' ),
					'description'       => esc_html__( 'Settings block for the chat container.', 'helper' ),
				],

				$slug . '_margin' => [
					'type'			  => 'sides',
					'label'			  => esc_html__( 'Margin', 'helper' ),
					'description'	  => esc_html__( 'Margin', 'helper' ),
					'default'         => [
						'top' => '20',
						'right' => '20',
						'bottom' => '0',
						'left' => '20',
					],
				],

				$slug . '_padding' => [
					'type'			  => 'sides',
					'label'			  => esc_html__( 'Padding', 'helper' ),
					'description'	  => esc_html__( 'Padding', 'helper' ),
					'default'         => [
						'top' => '0',
						'right' => '0',
						'bottom' => '0',
						'left' => '0',
					],
				],

				$slug . '_background_color' => [
					'type' => 'colorpicker',
					'label' => esc_html__( 'Background color', 'helper' ),
					'description' => esc_html__( 'Background color', 'helper' ),
					'default' => '#ffffff',
				],

			],
			Config::get_instance()->border_controls(
				$slug,
				array(
					'border_style' => 'none',
					'border_color' => '#4f32e6',
					'border_width' => array(
						'top' => '0',
						'right' => '0',
						'bottom' => '0',
						'left' => '0',
					),
					'border_radius' => array(
						'top' => '0',
						'right' => '0',
						'bottom' => '0',
						'left' => '0',
					),
				)
			)

		);

	}

	/**
	 * Fields scrollbar.
	 * @return array
	 */
	private static function fields_scrollbar(): array {

		$slug = 'chat_container';

		return [

			$slug . '_scrollbar_divider' => [
				'type' => 'divider',
			],

			$slug . '_scrollbar_heading' => [
				'type'              => 'header',
				'label'             => esc_html__( 'Scrollbar styles', 'helper' ),
				'description'       => esc_html__( 'Settings block for the scrollbar.', 'helper' ),
				'default'           => ''
			],

			$slug . '_scrollbar_border_radius' => [
				'type'			  => 'sides',
				'label'			  => esc_html__( 'Border radius', 'helper' ),
				'description'	  => esc_html__( 'Border radius', 'helper' ),
				'default'         => [
					'top' => '3',
					'right' => '3',
					'bottom' => '3',
					'left' => '3',
				],
			],

			$slug . '_scrollbar_track_color' => [
				'type' => 'colorpicker',
				'label' => esc_html__( 'Scrollbar color', 'helper' ),
				'description' => esc_html__( 'Scrollbar color', 'helper' ),
				'default' => '#fafafa',
			],

			$slug . '_scrollbar_thumb_color' => [
				'type' => 'colorpicker',
				'label' => esc_html__( 'Scrollbar thumb color', 'helper' ),
				'description' => esc_html__( 'Scrollbar thumb color', 'helper' ),
				'default' => '#e6e6e6',
			],

			$slug . '_scrollbar_thumb_color_hover' => [
				'type' => 'colorpicker',
				'label' => esc_html__( 'Scrollbar thumb hover color', 'helper' ),
				'description' => esc_html__( 'Scrollbar hover thumb color', 'helper' ),
				'default' => '#4f32e6',
			],

		];

	}

}


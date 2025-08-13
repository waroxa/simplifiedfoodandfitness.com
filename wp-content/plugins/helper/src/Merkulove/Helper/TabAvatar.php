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
final class TabAvatar {

	/**
	 * Controls.
	 * @return array[]
	 */
	public static function controls(): array {

		$avatar_display_options = array(
			'icon' => esc_html__( 'Icon', 'helper' ),
			'image' => esc_html__( 'Image', 'helper' ),
			'none' => esc_html__( 'Hide', 'helper' ),
		);

		$options = Settings::get_instance()->options;

		$tabs = Plugin::get_tabs();
		$fields = [

			// Bot avatar

			'bot_avatar' => [
				'type'              => 'select',
				'label'             => esc_html__( 'Bot avatar', 'helper' ),
				'placeholder'       => esc_html__( 'Avatar', 'helper' ),
				'description'       => esc_html__( 'Display bot avatar', 'helper' ),
				'default'           => 'icon',
				'options'           => $avatar_display_options,
			],

			'bot_avatar_image' => [
				'type'              => 'media_library',
				'label'             => esc_html__( 'Choose image', 'helper' ),
				'description'       => esc_html__( 'Choose image', 'helper' ),
				'default'           => '',
			],

			'bot_avatar_icon' => [
				'type'              => 'icon',
				'label'             => esc_html__( 'Bot icon', 'helper' ),
				'placeholder'       => '',
				'description'       => esc_html__( 'Set bot icon.', 'helper' ),
				'default'           => 'helper/helper.svg',
				'meta'              => [
					'helper.json',
					'font-awesome.json',
					'material.json'
				]
			],

			'bot_avatar_color' => [
				'type'              => 'colorpicker',
				'label'             => esc_html__( 'Icon color', 'helper' ),
				'description'       => esc_html__( 'Set icon color', 'helper' ),
				'default'           => '#ffffff',
			],

			'bot_avatar_background' => [
				'type'              => 'colorpicker',
				'label'             => esc_html__( 'Bot background', 'helper' ),
				'description'       => esc_html__( 'Set bot background.', 'helper' ),
				'default'           => '#4f32e6',
			],

			'bot_avatar_divider' => [
				'type'              => 'divider',
			],

			// User avatar

			'user_avatar' => [
				'type'              => 'select',
				'label'             => esc_html__( 'User avatar', 'helper' ),
				'placeholder'       => esc_html__( 'Avatar', 'helper' ),
				'description'       => esc_html__( 'Display user avatar', 'helper' ),
				'default'           => 'icon',
				'options'           => $avatar_display_options,
			],

			'user_avatar_image' => [
				'type'              => 'media_library',
				'label'             => esc_html__( 'Choose image', 'helper' ),
				'description'       => esc_html__( 'Choose image', 'helper' ),
				'default'           => '',
			],

			'user_avatar_icon' => [
				'type'              => 'icon',
				'label'             => esc_html__( 'User icon', 'helper' ),
				'description'       => esc_html__( 'Set user icon.', 'helper' ),
				'default'           => 'material/good-mood-emoticon.svg',
				'meta'              => [
					'helper.json',
					'font-awesome.json',
					'material.json'
				]
			],

			'user_avatar_color' => [
				'type'              => 'colorpicker',
				'label'             => esc_html__( 'Icon color', 'helper' ),
				'description'       => esc_html__( 'Set icon color', 'helper' ),
				'default'           => '#ffffff',
			],

			'user_avatar_background' => [
				'type'              => 'colorpicker',
				'label'             => esc_html__( 'User background', 'helper' ),
				'description'       => esc_html__( 'Set user background.', 'helper' ),
				'default'           => '#00bf6c',
			],

			'user_avatar_divider' => [
				'type'              => 'divider',
			],

			// Styles for both type of avatars

			'avatar_size' => [
				'type'              => 'slider',
				'label'             => esc_html__( 'Avatar size', 'helper' ),
				'placeholder'       => esc_html__( 'Avatar box size', 'helper' ),
				'description'       => wp_sprintf(
					/* translators: %s: avatar size in pixels. */
					esc_html__( 'Current avatar size is %s pixels.', 'helper' ),
					'<strong>' . esc_attr( ( ! empty( $options[ 'avatar_size' ] ) ) ?
						$options[ 'avatar_size' ] : '40' ) . '</strong>'
				),
				'default'           => 40,
				'min'               => 1,
				'max'               => 100,
				'step'              => 1,
				'discrete'          => false
			],

			'avatar_gap' => [
				'type'              => 'slider',
				'label'             => esc_html__( 'Avatar gap', 'helper' ),
				'placeholder'       => esc_html__( 'Avatar gap', 'helper' ),
				'description'       => wp_sprintf(
					/* translators: %s: avatar gap in pixels. */
					esc_html__( 'Current avatar gap is %s pixels.', 'helper' ),
					'<strong>' . esc_attr( ( ! empty( $options[ 'avatar_gap' ] ) ) ?
						$options[ 'avatar_gap' ] : '15' ) . '</strong>'
				),
				'default'           => 15,
				'min'               => 1,
				'max'               => 100,
				'step'              => 1,
				'discrete'          => false
			],

			'avatar_margin' => [
				'type'			  => 'sides',
				'label'			  => esc_html__( 'Avatar margin', 'helper' ),
				'description'	  => esc_html__( 'Set avatar margin.', 'helper' ),
				'default'         => [
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
				],
			],

			'avatar_padding' => [
				'type'			  => 'sides',
				'label'			  => esc_html__( 'Avatar padding', 'helper' ),
				'description'	  => esc_html__( 'Set avatar padding.', 'helper' ),
				'default'         => [
					'top' => '10',
					'right' => '10',
					'bottom' => '10',
					'left' => '10',
				],
			],

			'avatar_border_radius' => [
				'type'			  => 'sides',
				'label'			  => esc_html__( 'Avatar border radius', 'helper' ),
				'description'	  => esc_html__( 'Set avatar border radius.', 'helper' ),
				'default'         => [
					'top' => '25',
					'right' => '25',
					'bottom' => '25',
					'left' => '25',
				],
			],

		];

		$tabs[ 'avatar' ][ 'fields' ] = $fields;
		Plugin::set_tabs( $tabs );
		Settings::get_instance()->get_options();

		return $fields;

	}

}


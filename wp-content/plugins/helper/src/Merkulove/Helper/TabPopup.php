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
final class TabPopup {

	/**
	 * Controls.
	 * @return array[]
	 */
	public static function controls(): array {

		$options = Settings::get_instance()->options;

		$tabs = Plugin::get_tabs();

		$fields = array_merge(
			self::fields_chat_container( $options ),
			self::fields_animation( $options ),
		);

		$tabs[ 'popup' ][ 'fields' ] = $fields;
		Plugin::set_tabs( $tabs );
		Settings::get_instance()->get_options();

		return $fields;

	}

	/**
	 * Fields for chat popup container
	 *
	 * @param $options
	 *
	 * @return array
	 */
	private static function fields_chat_container( $options ): array {

		$slug = 'bot_container';

		return array_merge(

			[
                $slug . '_open_popup_without_btn' => [
                    'type'              => 'switcher',
                    'label'             => esc_html__( 'Always opened when button turned off', 'helper' ),
                    'placeholder'       => esc_html__( 'Always opened when button turned off', 'helper' ),
                    'description'       => esc_html__( 'Keep chatbot window always opened when float button turned off', 'helper' ),
                    'default'           => 'on',
                ],

                $slug . '_enable_auto_open' => [
                    'type'              => 'switcher',
                    'label'             => esc_html__( 'Enable auto open popup', 'helper' ),
                    'placeholder'       => esc_html__( 'Enable auto open popup', 'helper' ),
                    'description'       => esc_html__( 'Automatically opens the popup after the specified time has elapsed', 'helper' ),
                    'default'           => 'off',
                    'attr' => [
                        'class' => $options['open_bot_with_button'] !== 'on' && $options[$slug . '_open_popup_without_btn'] === 'on' ?
                            'mdp-helper-hide' : ''
                    ]
                ],

                $slug . '_open_delay' => [
                    'type'              => 'slider',
                    'label'             => esc_html__( 'Open delay', 'helper' ),
                    'placeholder'       => esc_html__( 'Open delay', 'helper' ),
                    'description'       => wp_sprintf(
                        esc_html__( 'Open after %s seconds.', 'helper' ),
                        '<strong>' . esc_attr( ( ! empty( $options[ $slug . '_open_delay' ] ) ) ?
                            $options[ $slug . '_open_delay' ] : '5' ) . '</strong>'
                    ),
                    'default'           => 5,
                    'min'               => 0,
                    'max'               => 30,
                    'step'              => 0.1,
                    'discrete'          => false,
                    'attr' => [
                        'class' => $options['open_bot_with_button'] !== 'on' && $options[$slug . '_open_popup_without_btn'] === 'on' ?
                            'mdp-helper-hide' : ''
                    ]
                ],

                $slug . '_divider' => [
					'type' => 'divider',
				],

				$slug . '_heading' => [
					'type'              => 'header',
					'label'             => esc_html__( 'Chat popup styles', 'helper' ),
					'description'       => esc_html__( 'Settings block that matches the styles of the chat bot container.', 'helper' ),
				],

				$slug . '_margin' => [
					'type'			  => 'sides',
					'label'			  => esc_html__( 'Margin', 'helper' ),
					'description'	  => esc_html__( 'Set bot border radius.', 'helper' ),
					'default'         => [
						'top' => 0,
						'right' => 20,
						'bottom' => 0,
						'left' => 20,
					],
				],

				$slug . '_max_width' => [
					'type'              => 'switcher',
					'label'             => esc_html__( 'Max width 100%', 'helper' ),
					'placeholder'       => esc_html__( 'Max width 100%', 'helper' ),
					'description'       => esc_html__( 'Max width and margins equals 100% of screen', 'helper' ),
					'default'           => 'on',
				],

                $slug . '_max_height' => [
                    'type'              => 'switcher',
                    'label'             => esc_html__( 'Max height 100%', 'helper' ),
                    'placeholder'       => esc_html__( 'Max height 100%', 'helper' ),
                    'description'       => esc_html__( 'Max height and margins equals 100% of screen', 'helper' ),
                    'default'           => 'on',
                ],

                $slug . '_mobile_full_width' => [
                    'type'              => 'switcher',
                    'label'             => esc_html__( 'Mobile full size', 'helper' ),
                    'placeholder'       => esc_html__( 'Mobile full size', 'helper' ),
                    'description'       => esc_html__( 'Set chat window full size of screen', 'helper' ),
                    'default'           => 'on',
                ],

				$slug . '_width' => [
					'type'              => 'custom_type',
					'render'            => [ Config::get_instance(), 'render_measures_slider' ],
					'label'             => esc_html__( 'Width', 'helper' ),
					'description'       => wp_sprintf(
						esc_html__( 'Set bot container width. Current width is %s', 'helper' ),
						'<strong>' . esc_html( ( ! empty( $options['bot_container_width'] ) ) ? $options['bot_container_width'] : '450' ) . '</strong>'
					),
					'default'           => 450,
					'min'               => 1,
					'max'               => 1000,
					'step'              => 1,
					'discrete'          => false,
				],

				$slug . '_height' => [
					'type'              => 'custom_type',
					'render'            => [ Config::get_instance(), 'render_measures_slider' ],
					'label'             => esc_html__( 'Height', 'helper' ),
					'show_label'        => true,
					'description'       => wp_sprintf(
						esc_html__( 'Set bot container height. Current height is %s', 'helper' ),
						'<strong>' . esc_html( ( ! empty( $options['bot_container_height'] ) ) ? $options['bot_container_height'] : '650' ) . '</strong>'
					),
					'default'           => 650,
					'min'               => 1,
					'max'               => 1000,
					'step'              => 1,
					'discrete'          => false,
				],

				$slug . '_background_color' => [
					'type' => 'colorpicker',
					'label' => esc_html__( 'Background color', 'helper' ),
					'description' => esc_html__( 'Set chat window background color.', 'helper' ),
					'default' => '#ffffff',
				],

			],

			Config::get_instance()->border_controls(
				$slug,
				[
					'border_style' => 'none',
					'border_color' => '#4f32e6',
					'border_width' => [
						'top' => 1,
						'right' => 1,
						'bottom' => 1,
						'left' => 1,
					],
					'border_radius' => [
						'top' => 20,
						'right' => 20,
						'bottom' => 20,
						'left' => 20,
					],
				]
			),

			[

				$slug . '_box_shadow' => [
					'type' => 'select',
					'label' => esc_html__( 'Box shadow', 'helper' ),
					'description' => esc_html__( 'Set chat window box shadow.', 'helper' ),
					'default' => 'outside',
					'options' => [
						'none' => esc_html__( 'None', 'helper' ),
						'outside' => esc_html__( 'Outside box-shadow', 'helper' ),
						'inside' => esc_html__( 'Inside box-shadow', 'helper' ),
					],
				],

				$slug . '_box_shadow_offset' => [
					'type' => 'sides',
					'label' => esc_html__( 'Box shadow offset', 'helper' ),
					'description' => esc_html__( 'Set chat window box shadow offset.', 'helper' ),
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
					'description' => esc_html__( 'Set chat window box shadow color.', 'helper' ),
					'default' => 'rgba(0,0,0,.08)',
				],

			]

		);

	}

	/**
	 * Fields animation
	 * @param $options
	 *
	 * @return array[]
	 */
	private static function fields_animation( $options ): array {

		$slug = 'bot_container';

		return [

			$slug . '_animation_divider' => [
				'type' => 'divider',
			],

			$slug . '_animation' => [
				'type'              => 'select',
				'label'             => esc_html__( 'Chat bot appearance animation', 'helper' ),
				'description'       => esc_html__( 'Set bot appearance animation', 'helper' ),
				'default'           => 'slide_up',
				'options'           => [
					'none' => esc_html__( 'None', 'helper' ),
					'grow' => esc_html__( 'Grow', 'helper' ),
					'shrink' => esc_html__( 'Shrink', 'helper' ),
					'slide_up' => esc_html__( 'Slide up', 'helper' ),
					'slide_down' => esc_html__( 'Slide down', 'helper' ),
					'fade' => esc_html__( 'Fade', 'helper' )
				],
                'attr' => [
                    'class' => 'mdp-helper-animation-control'
                ]
			],

			$slug . '_animation_duration' => [
				'type'              => 'slider',
				'label'             => esc_html__( 'Animation duration', 'helper' ),
				'description'       => wp_sprintf(
					esc_html__( 'Set chat bot appearance animation duration. Current animation duration is %s seconds.', 'helper' ),
					'<strong>' . esc_html( ( ! empty( $options[ $slug . '_animation_duration' ] ) ) ? $options[ $slug . '_animation_duration' ] : '1' ) . '</strong>'
				),
				'default'           => 1,
				'min'               => 0.1,
				'max'               => 5,
				'step'              => 0.1,
				'discrete'          => false,
                'attr'              => [
                    'class' => '
                       mdp-helper-animation-control 
                       mdp-helper-popup-animation 
                       mdp-helper-tab-animation-type-grow
                       mdp-helper-tab-animation-type-shrink 
                       mdp-helper-tab-animation-type-slide_up
                       mdp-helper-tab-animation-type-slide_down
                       mdp-helper-tab-animation-type-fade'
                ]

			],

			$slug . '_animation_delay' => [
				'type'              => 'slider',
				'label'             => esc_html__( 'Animation delay', 'helper' ),
				'description'       => wp_sprintf(
					esc_html__( 'Set chat bot appearance animation delay. Current animation delay is %s seconds.', 'helper' ),
					'<strong>' . esc_html( ( ! empty( $options[ $slug . '_animation_delay' ] ) ) ? $options[ $slug . '_animation_delay' ] : '0' ) . '</strong>'
				),
				'default'           => 0,
				'min'               => 0,
				'max'               => 5,
				'step'              => 0.1,
				'discrete'          => false,
                'attr'              => [
                    'class' => '
                       mdp-helper-animation-control 
                       mdp-helper-popup-animation 
                       mdp-helper-tab-animation-type-grow
                       mdp-helper-tab-animation-type-shrink 
                       mdp-helper-tab-animation-type-slide_up
                       mdp-helper-tab-animation-type-slide_down
                       mdp-helper-tab-animation-type-fade'
                ]
			],

		];

	}

}


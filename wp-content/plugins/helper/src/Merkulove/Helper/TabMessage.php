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
final class TabMessage {

	/**
	 * @var array
	 */
	private static array $options_animations = [];

	/**
	 * Controls.
	 * @return array[]
	 */
	public static function controls(): array {

		self::$options_animations = [
			'none' => esc_html__( 'None', 'helper' ),
			'grow' => esc_html__( 'Grow', 'helper' ),
			'shrink' => esc_html__( 'Shrink', 'helper' ),
			'slide_up' => esc_html__( 'Slide up', 'helper' ),
			'slide_down' => esc_html__( 'Slide down', 'helper' ),
			'fade' => esc_html__( 'Fade', 'helper' )
		];

		$options = Settings::get_instance()->options;

		$tabs = Plugin::get_tabs();

		$fields = array_merge(
			self::fields_message( $options ),
			self::fields_upper_line( $options ),
			self::fields_bot_message( $options ),
			self::fields_user_message( $options ),
			self::fields_menu_buttons( $options ),
		);

		$tabs[ 'message' ][ 'fields' ] = $fields;
		Plugin::set_tabs( $tabs );
		Settings::get_instance()->get_options();

		return $fields;

	}

	/**
	 * General message styles.
	 * @return array
	 */
	private static function fields_message( $options ): array {

		$slug = 'message_container';

		return [

			$slug . '_divider' => [
				'type' => 'divider',
			],

			$slug . '_heading' => [
				'type'              => 'header',
				'label'             => esc_html__( 'Message Container', 'helper' ),
				'description'       => esc_html__( 'Settings block for the chat container.', 'helper' ),
			],

			$slug . '_margin' => [
				'type'              => 'sides',
				'label'             => esc_html__( 'Margin', 'helper' ),
				'description'       => esc_html__( 'Margin of the message container.', 'helper' ),
				'default'           => [
					'top' => 10,
					'right' => 0,
					'bottom' => 10,
					'left' => 0,
				],
			],

			$slug . '_padding' => [
				'type'              => 'sides',
				'label'             => esc_html__( 'Padding', 'helper' ),
				'description'       => esc_html__( 'Padding of the message container.', 'helper' ),
				'default'           => [
					'top' => 8,
					'right' => 12,
					'bottom' => 8,
					'left' => 12,
				],
			],

			$slug . '_font_size' => [
				'type'              => 'slider',
				'label'             => esc_html__( 'Font Size', 'helper' ),
				'description'       => wp_sprintf(
					esc_html__( 'Font size of the message container. Current: %s px', 'helper' ),
					'<strong>' . esc_html( ( !empty( $options[ $slug . '_font_size' ] ) ) ? $options[ $slug . '_font_size' ] : '14' ) . '</strong>'
				),
				'default'           => 14,
				'min'               => 4,
				'max'               => 40,
				'step'              => 1,
			],

		];

	}

	/**
	 * Message upper-line styles.
	 * @return array
	 */
	private static function fields_upper_line( $options ): array {

		$slug = 'upper_line';

		return [

			$slug . '_divider' => [
				'type' => 'divider',
			],

			$slug . '_heading' => [
				'type'              => 'header',
				'label'             => esc_html__( 'Message upper-line', 'helper' ),
				'description'       => esc_html__( 'Chat message top line settings to display message name and time.', 'helper' ),
			],

			$slug . '_name_enabled' => [
				'type'              => 'switcher',
				'label'             => esc_html__( 'Display name', 'helper' ),
				'description'       => esc_html__( 'Display message name.', 'helper' ),
				'default'           => 'on',
			],

			$slug . '_bot_name' => [
				'type'              => 'text',
				'label'             => esc_html__( 'Name', 'helper' ),
				'description'       => esc_html__( 'The name of the bot that is displayed in the chat', 'helper' ),
				'default'           => esc_html__( 'Mr. Helper', 'helper' ),
			],

			$slug . '_timestamp_enabled' => [
				'type'              => 'switcher',
				'label'             => esc_html__( 'Display time', 'helper' ),
				'description'       => esc_html__( 'Display message timestamp.', 'helper' ),
				'default'           => 'on',
			],

			$slug . '_timestamp_12_hours' => [
				'type'              => 'switcher',
				'label'             => esc_html__( '12 hours format', 'helper' ),
				'description'       => esc_html__( 'Display time in 12 hours format.', 'helper' ),
				'default'           => 'off',
			],

			$slug . '_buttons_enabled' => [
				'type'              => 'switcher',
				'label'             => esc_html__( 'Message control buttons', 'helper' ),
				'description'       => esc_html__( 'Display message controls', 'helper' ),
				'default'           => 'on',
			],

            $slug . '_return_to_menu_icon' => [
                'type'              => 'icon',
                'label'             => esc_html__( 'Set copy bot message button icon.', 'helper' ),
                'description'       => esc_html__( 'Set copy bot message button icon.', 'helper' ),
                'default'           => 'material/back-arrow.svg',
                'meta'              => [
                    'helper.json',
                    'font-awesome.json',
                    'material.json'
                ]
            ],

            $slug . '_copy_button_enabled' => [
                'type'              => 'switcher',
                'label'             => esc_html__( 'Enable copy bot message button', 'helper' ),
                'description'       => esc_html__( 'Enable copy bot message button.', 'helper' ),
                'default'           => 'off',
            ],

            $slug . '_copy_button_icon' => [
                'type'              => 'icon',
                'label'             => esc_html__( 'Return to main menu icon', 'helper' ),
                'description'       => esc_html__( 'Set return to main menu button icon.', 'helper' ),
                'default'           => 'material/copy-content.svg',
                'meta'              => [
                    'helper.json',
                    'font-awesome.json',
                    'material.json'
                ]
            ],

			$slug . '_icon_color' => [
				'type'              => 'colorpicker',
				'label'             => esc_html__( 'Icon color', 'helper' ),
				'description'       => esc_html__( 'Icons Color.', 'helper' ),
				'default'           => '#4f32e6',
			],

			$slug . '_color' => [
				'type'              => 'colorpicker',
				'label'             => esc_html__( 'Text color', 'helper' ),
				'description'       => esc_html__( 'Color of the message upper-line.', 'helper' ),
				'default'           => '#949494',
			],

			$slug . '_font_size' => [
				'type'              => 'slider',
				'label'             => esc_html__( 'Text size', 'helper' ),
				'description'       => wp_sprintf(
					esc_html__( 'Font size of the message upper-line. Current: %s px', 'helper' ),
					'<strong>' . esc_html( ( !empty( $options[ $slug . '_font_size' ] ) ) ? $options[ $slug . '_font_size' ] : '12' ) . '</strong>'
				),
				'default'           => 12,
				'min'               => 4,
				'max'               => 40,
				'step'              => 1,
			],

		];

	}

	/**
	 * Fields for the user message.
	 *
	 * @param $options
	 *
	 * @return array
	 */
	private static function fields_bot_message( $options ): array {

		$slug = 'bot_message';

		return array_merge(

			[

				$slug . '_divider' => [
					'type' => 'divider',
				],

				$slug . '_heading' => [
					'type'              => 'header',
					'label'             => esc_html__( 'Bot Message', 'helper' ),
					'description'       => esc_html__( 'Bot message bubble settings', 'helper' ),
				],

				$slug . '_color' => [
					'type'              => 'colorpicker',
					'label'             => esc_html__( 'Color', 'helper' ),
					'description'       => esc_html__( 'Color of the bot message.', 'helper' ),
					'default'           => '#2f2177',
				],

				$slug . '_background_color' => [
					'type'              => 'colorpicker',
					'label'             => esc_html__( 'Background Color', 'helper' ),
					'description'       => esc_html__( 'Background color of the bot message.', 'helper' ),
					'default'           => '#dbd5ff',
				],

			],

			Config::get_instance()->border_controls(
				$slug,
				[
					'border_style' => 'none',
					'border_color' => '#e5e5e5',
					'border_width' => [
						'top' => 0,
						'right' => 0,
						'bottom' => 0,
						'left' => 0,
					],
					'border_radius' => [
						'top' => 0,
						'right' => 8,
						'bottom' => 8,
						'left' => 8,
					],
				]

			),

			[

				$slug . '_animation' => [
					'type'              => 'select',
					'label'             => esc_html__( 'Bot message animation', 'helper' ),
					'description'       => esc_html__( 'Set bot message animation', 'helper' ),
					'default'           => 'slide_up',
					'options'           => self::$options_animations,
                    'attr' => [
                        'class' => 'mdp-helper-animation-control'
                    ]
				],

			],

			self::animation_controls( $slug, $options, 'bot' )

		);

	}

	/**
	 * Fields for the user message.
	 *
	 * @param $options
	 *
	 * @return array
	 */
	private static function fields_user_message( $options ): array {

		$slug = 'user_message';

		return array_merge(

			[

			$slug . '_divider' => [
				'type' => 'divider',
			],

			$slug . '_heading' => [
				'type'              => 'header',
				'label'             => esc_html__( 'User Message', 'helper' ),
				'description'       => esc_html__( 'User message bubble settings', 'helper' ),
			],

			$slug . '_color' => [
				'type'              => 'colorpicker',
				'label'             => esc_html__( 'Color', 'helper' ),
				'description'       => esc_html__( 'Color of the user message.', 'helper' ),
				'default'           => '#032f18',
			],

			$slug . '_background_color' => [
				'type'              => 'colorpicker',
				'label'             => esc_html__( 'Background Color', 'helper' ),
				'description'       => esc_html__( 'Background color of the user message.', 'helper' ),
				'default'           => '#c9ffe2',
			],

		],

		Config::get_instance()->border_controls(
			$slug,
			[
				'border_style' => 'none',
				'border_color' => '#e5e5e5',
				'border_width' => [
					'top' => 0,
					'right' => 0,
					'bottom' => 0,
					'left' => 0,
				],
				'border_radius' => [
					'top' => 8,
					'right' => 0,
					'bottom' => 8,
					'left' => 8,
				],
			]
		),

		[

			$slug . '_animation' => [
				'type'              => 'select',
				'label'             => esc_html__( 'User message animation', 'helper' ),
				'description'       => esc_html__( 'Set user message animation', 'helper' ),
				'default'           => 'slide_up',
				'options'           => self::$options_animations,
                'attr' => [
                    'class' => 'mdp-helper-animation-control'
                ]
			],

		],

		self::animation_controls( $slug, $options, 'user' )

		);

	}

	/**
	 * Fields for bot menu buttons.
	 * @param $options
	 *
	 * @return array
	 */
	private static function fields_menu_buttons( $options ): array {

		$slug = 'menu_button';

		return array_merge(

			[

				$slug . '_divider' => [
					'type' => 'divider',
				],

				$slug . '_heading' => [
					'type'              => 'header',
					'label'             => esc_html__( 'Action button', 'helper' ),
					'description'       => esc_html__( 'Settings block for action buttons.', 'helper' ),
				],

				$slug . '_margin' => [
					'type'              => 'sides',
					'label'             => esc_html__( 'Margin', 'helper' ),
					'description'       => esc_html__( 'Margin of the message container.', 'helper' ),
					'default'           => [
						'top' => 0,
						'right' => 0,
						'bottom' => 10,
						'left' => 55,
					],
				],

				$slug . '_padding' => [
					'type'              => 'sides',
					'label'             => esc_html__( 'Padding', 'helper' ),
					'description'       => esc_html__( 'Padding of the message container.', 'helper' ),
					'default'           => [
						'top' => 10,
						'right' => 0,
						'bottom' => 10,
						'left' => 0,
					],
				],

				$slug . '_width' => [
					'type'              => 'custom_type',
					'render'            => [ Config::get_instance(), 'render_measures_slider' ],
					'label'             => esc_html__( 'Menu button width', 'helper' ),
					'description'       => esc_html__( 'Menu button width ', 'helper' ) .
					                       ' <strong>' .
					                       esc_html( ( ! empty( $options[ $slug . '_width' ] ) ) ?
						                       $options[ $slug . '_width' ] : '300' ) .
					                       '</strong>',
					'default'           => 300,
					'min'               => 1,
					'max'               => 500,
					'step'              => 1,
					'discrete'          => false,
				],

                $slug . '_font_size' => [
                    'type'              => 'slider',
                    'label'             => esc_html__( 'Font Size', 'helper' ),
                    'description'       => wp_sprintf(
                        esc_html__( 'Font size of the action button. Current: %s px', 'helper' ),
                        '<strong>' . esc_html( ( !empty( $options[ $slug . '_font_size' ] ) ) ? $options[ $slug . '_font_size' ] : '14' ) . '</strong>'
                    ),
                    'default'           => 14,
                    'min'               => 4,
                    'max'               => 40,
                    'step'              => 1,
                ],

				$slug . '_color' => [
					'type'              => 'colorpicker',
					'label'             => esc_html__( 'Color', 'helper' ),
					'description'       => esc_html__( 'Color of the message container.', 'helper' ),
					'default'           => '#4f32e6',
				],

				$slug . '_hover_color' => [
					'type'              => 'colorpicker',
					'label'             => esc_html__( 'Hover Color', 'helper' ),
					'description'       => esc_html__( 'Hover color of the message container.', 'helper' ),
					'default'           => '#ffffff',
				],

				$slug . '_background_color' => [
					'type'              => 'colorpicker',
					'label'             => esc_html__( 'Background Color', 'helper' ),
					'description'       => esc_html__( 'Background color of the message container.', 'helper' ),
					'default'           => '#ffffff',
				],

				$slug . '_hover_background_color' => [
					'type'              => 'colorpicker',
					'label'             => esc_html__( 'Hover Background Color', 'helper' ),
					'description'       => esc_html__( 'Hover background color of the message container.', 'helper' ),
					'default'           => '#4f32e6',
				],

                $slug . '_icon_size' => [
                    'type'              => 'slider',
                    'label'             => esc_html__( 'Icon size', 'helper' ),
                    'description'       => wp_sprintf(
                        esc_html__( 'Bot menu icon size. Current: %s px', 'helper' ),
                        '<strong>' . esc_html( ( !empty( $options[ $slug . '_icon_size' ] ) ) ? $options[ $slug . '_icon_size' ] : '16' ) . '</strong>'
                    ),
                    'default'           => 12,
                    'min'               => 4,
                    'max'               => 40,
                    'step'              => 1,
                ],

                $slug . '_icon_color' => [
                    'type'              => 'colorpicker',
                    'label'             => esc_html__( 'Icon color', 'helper' ),
                    'description'       => esc_html__( 'Icon color.', 'helper' ),
                    'default'           => '#4f32e6',
                ],
			],

			Config::get_instance()->border_controls(
				$slug,
				[
					'border_style' => 'solid',
					'border_color' => '#dbd5ff',
					'border_width' => [
						'top' => 1,
						'right' => 1,
						'bottom' => 1,
						'left' => 1,
					],
					'border_radius' => [
						'top' => 8,
						'right' => 8,
						'bottom' => 8,
						'left' => 8,
					]
				],
			),

		);

	}

	/**
	 * Animation controls
	 * @param $slug
	 * @param $options
	 *
	 * @return array[]
	 */
	private static function animation_controls( $slug, $options, $type ): array {
        $condition_classes = 'mdp-helper-animation-control
                              mdp-helper-'. $type .'-message-animation 
                              mdp-helper-'. $type .'-message-animation-type-grow
                              mdp-helper-'. $type .'-message-animation-type-shrink 
                              mdp-helper-'. $type .'-message-animation-type-slide_up
                              mdp-helper-'. $type .'-message-animation-type-slide_down
                              mdp-helper-'. $type .'-message-animation-type-fade';

		return [

			$slug . '_animation_duration' => [
				'type'              => 'slider',
				'label'             => esc_html__( 'Animation duration', 'helper' ),
				'description'       => wp_sprintf(
					/* translators: %s: animation duration */
					esc_html__( 'Animation duration %s seconds.', 'helper' ),
					'<strong>' . esc_html( ( ! empty( $options['animation_duration'] ) ) ? $options['animation_duration'] : '1' ) . '</strong>'
				),
				'default'           => 1,
				'min'               => 0.1,
				'max'               => 5,
				'step'              => 0.1,
				'discrete'          => false,
                'attr'              => [
                    'class' => $condition_classes
                ]
			],

			$slug . '_animation_delay' => [
				'type'              => 'slider',
				'label'             => esc_html__( 'Animation delay', 'helper' ),
				'description'       => wp_sprintf(
					/* translators: %s: animation delay */
					esc_html__( 'Animation delay %s seconds.', 'helper' ),
					'<strong>' . esc_html( ( ! empty( $options['animation_delay'] ) ) ? $options['animation_delay'] : '0' ) . '</strong>'
				),
				'default'           => 0,
				'min'               => 0,
				'max'               => 5,
				'step'              => 0.1,
				'discrete'          => false,
                'attr'              => [
                    'class' => $condition_classes
                ]
			],

		];

	}

}


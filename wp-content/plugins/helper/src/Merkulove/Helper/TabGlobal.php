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
final class TabGlobal {

	/**
	 * Controls.
	 * @return array[]
	 */
	public static function controls(): array {

        $options = Settings::get_instance()->options;

		$tabs = Plugin::get_tabs();

        $posts = Config::get_instance()->get_posts();

		$fields = array_merge(
			self::fields_general( $options ),
		);

		$tabs[ 'general' ][ 'fields' ] = $fields;
		$tabs[ 'general' ][ 'icon' ] = 'settings';

        $tabs = Config::get_instance()->set_bot_response_messages(
            $tabs,
            'general',
            true,
            false,
            [
                'add_button' => esc_html__( 'Add message', 'helper' ),
                'remove_button' => esc_html__( 'Remove message', 'helper' )
            ],
            [
                'bot_message' => esc_html__( 'Is there anything else I can help you with?', 'helper' ),
                'error_message' => esc_html__( "Sorry, I don't understand this", 'helper' )
            ],
            '_more_help_initial',
            'End conversation conditions menu initial messages',
            'Set end conversation conditions menu initial messages',
        );

        $tabs['general']['fields']['is_relevant_yes_text'] = [
            'type' => 'text',
            'label' => esc_html__( 'Confirm button text', 'helper' ),
            'description' => esc_html__( 'Set confirm button text. The button returns to the welcome message and to the start menu', 'helper' ),
            'default' => esc_html__( 'Yes', 'helper' )
        ];

        $tabs['general']['fields']['is_relevant_no_text'] = [
            'type' => 'text',
            'label' => esc_html__( 'Decline button text', 'helper' ),
            'description' => esc_html__( 'Set decline button text. The button shows the end conversation messages', 'helper' ),
            'default' => esc_html__( 'No', 'helper' )
        ];

        $tabs = Config::get_instance()->set_bot_response_messages(
            $tabs,
            'general',
            true,
            false,
            [
                'add_button' => esc_html__( 'Add message', 'helper' ),
                'remove_button' => esc_html__( 'Remove message', 'helper' )
            ],
            [
                'bot_message' => esc_html__( 'If you need more help, you can ask another question.', 'helper' ),
                'error_message' => esc_html__( "Sorry, I don't understand this", 'helper' )
            ],
            '_try_again',
            'Try again messages',
            'Set try again messages',
        );

        $tabs = Config::get_instance()->set_bot_response_messages(
            $tabs,
            'general',
            true,
            false,
            [
                'add_button' => esc_html__( 'Add message', 'helper' ),
                'remove_button' => esc_html__( 'Remove message', 'helper' )
            ],
            [
                'bot_message' => esc_html__( 'Happy to help, have a great day!', 'helper' ),
                'error_message' => esc_html__( "Sorry, I don't understand this", 'helper' )
            ],
            '_exit',
            'End conversation messages',
            'Set end conversation messages',
        );


        $tabs = Config::get_instance()->set_bot_response_messages(
			$tabs,
			'general',
			true,
			false,
			[
				'add_button' => esc_html__( 'Add initial message', 'helper' ),
				'remove_button' => esc_html__( 'Remove initial message', 'helper' )
			],
			[
				'bot_message' => esc_html__( 'Hi! How can i help you?', 'helper' ),
				'error_message' => esc_html__( "Sorry, I don't understand this", 'helper' )
			],
            '',
            'Welcome bot messages',
            'Set welcome bot messages',
            $posts
		);

		Plugin::set_tabs( $tabs );
		Settings::get_instance()->get_options();

		return $fields;

	}

	/**
	 * Fields general.
	 *
	 * @return array[]
	 */
	private static function fields_general( $options ): array {

		return [

			'first_divider' => [
				'type'              => 'divider',
			],

			'first_heading' => [
				'type'              => 'header',
				'label'             => esc_html__( 'Global settings', 'helper' ),
				'description'       => esc_html__( '', 'helper' ),
			],

			'bot_features' => [
				'type'              => 'chosen',
				'label'             => esc_html__( 'Bot features', 'helper' ),
				'description'       => esc_html__( 'Select one or more bot features to be used', 'helper' ),
				'default'           => [ 'faq', 'collect_data', 'get_user_email', 'bot_commands' ],
				'options'           => [
					'faq' => esc_html__( 'FAQ', 'helper' ),
					'collect_data'  => esc_html__( 'Collect user data', 'helper' ),
					'get_user_email'  => esc_html__( 'Get user emails', 'helper' ),
					'bot_commands' => esc_html__( 'Bot commands', 'helper' ),
					'ai' => 'Open AI',
				],
				'attr' => [
					'class' => 'mdp-helper-bot-type-bot_basic mdp-helper-general-fields',
					'multiple' => 'multiple'
				]
			],

			'bot_position' => [
				'type'              => 'select',
				'label'             => esc_html__( 'Bot position', 'helper' ),
				'description'       => esc_html__( 'Choose bot position or use shortcode', 'helper' ) . ' [helper]',
				'default'           => 'fixed-bottom-right',
				'options'           => [
					'fixed-top-left' => esc_html__( 'Fixed top left', 'helper' ),
					'fixed-top-center' => esc_html__( 'Fixed top centre', 'helper' ),
					'fixed-top-right' => esc_html__( 'Fixed top right', 'helper' ),
					'fixed-left-center' => esc_html__( 'Fixed left centre', 'helper' ),
					'fixed-right-center' => esc_html__( 'Fixed right centre', 'helper' ),
					'fixed-bottom-left' => esc_html__( 'Fixed bottom left', 'helper' ),
					'fixed-bottom-center' => esc_html__( 'Fixed bottom centre', 'helper' ),
					'fixed-bottom-right' => esc_html__( 'Fixed bottom right', 'helper' ),
					'shortcode' => esc_html__( 'Shortcode', 'helper' ),
				],
			],

			'send_message_sound' => [
				'type'              => 'custom_type',
				'render'            => [ Config::get_instance(), 'render_import_file' ],
				'label'             => esc_html__( 'Send message sound(WAV, MP3, OGG)', 'helper' ),
				'show_label'        => true,
				'description'       => '',
				'show_description'  => false,
				'default'           => '',
			],

			'receive_message_sound' => [
				'type'              => 'custom_type',
				'render'            => [ Config::get_instance(), 'render_import_file' ],
				'label'             => esc_html__( 'Receive message sound(WAV, MP3, OGG)', 'helper' ),
				'show_label'        => true,
				'description'       => '',
				'show_description'  => false,
				'default'           => '',
			],

            'bot_logs' => [
                'type'              => 'switcher',
                'label'             => esc_html__( 'Enable bot logs', 'helper' ),
                'placeholder'       => esc_html__( 'Enable bot logs', 'helper' ),
                'description'       => esc_html__( 'Enables bot logs', 'helper' ),
                'default'           => 'off',
            ],
            'bot_logs_auto_delete' => [
                'type'              => 'switcher',
                'label'             => esc_html__( 'Enable auto delete bot logs', 'helper' ),
                'placeholder'       => esc_html__( 'Enable auto delete bot logs', 'helper' ),
                'description'       => esc_html__( 'Enables auto delete bot logs', 'helper' ),
                'default'           => 'off',
            ],
            'bot_logs_delete_after' => [
                'type'              => 'slider',
                'label'             => esc_html__( 'Log expiration time', 'helper' ),
                'description'       => wp_sprintf(
                    esc_html__( 'Sets the duration after which expired bot logs will be automatically deleted. Current: %s days', 'helper' ),
                    '<strong>' . esc_html( ( !empty( $options[ 'bot_logs_delete_after' ] ) ) ? $options[ 'bot_logs_delete_after' ] : '30' ) . '</strong>'
                ),
                'default'           => 30,
                'min'               => 1,
                'max'               => 30,
                'step'              => 1,
            ],
            'enable_is_relevant' => [
                'type'              => 'switcher',
                'label'             => esc_html__( 'End conversation conditions', 'helper' ),
                'placeholder'       => esc_html__( 'End conversation conditions', 'helper' ),
                'description'       => esc_html__( 'Adds menu after providing an answer that ask user if user need more help', 'helper' ),
                'default'           => 'off',
            ],

		];

	}

}


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
final class TabBehavior {

	/**
	 * Controls.
	 * @return array[]
	 */
	public static function controls(): array {

		$options = Settings::get_instance()->options;

		$tabs = Plugin::get_tabs();
		$fields = [

            'bot_onload_message_preloader' => [
                'type'              => 'switcher',
                'label'             => esc_html__( 'Bot message preloader', 'helper' ),
                'placeholder'       => esc_html__( 'Bot message preloader', 'helper' ),
                'description'       => esc_html__( 'Show message preloader while message is loading.', 'helper' ),
                'default'           => 'off',
            ],

			'bot_stt_divider' => [
				'type' => 'divider',
			],

			'bot_stt_heading' => [
				'type' => 'header',
				'label' => esc_html__( 'Speech recognition', 'helper' ),
				'description' => esc_html__( 'This settings block controls speech-to-text recognition for head-to-head communication with the bot.' ),
			],

			'bot_stt' => [
				'type'              => 'switcher',
				'label'             => esc_html__( 'Speech to Text', 'helper' ),
				'placeholder'       => esc_html__( 'Enable Speech to Text', 'helper' ),
				'description'       => esc_html__( 'Speech recognition based on WebSpeech API', 'helper' ),
				'default'           => 'on',
			],

			'bot_stt_auto_submit' => [
				'type'              => 'switcher',
				'label'             => esc_html__( 'Auto-submit', 'helper' ),
				'placeholder'       => esc_html__( 'Enable auto-submit', 'helper' ),
				'description'       => esc_html__( 'Submit form after speech recognition is complete', 'helper' ),
				'default'           => 'on',
			],

			'bot_stt_icon' => [
				'type'              => 'icon',
				'label'             => esc_html__( 'Button icon', 'helper' ),
				'description'       => esc_html__( 'Speech recognition button icon', 'helper' ),
				'default'           => 'helper/recognition.svg',
				'meta'              => [
					'helper.json',
					'font-awesome.json',
					'material.json'
				]
			],

			'bot_stt_color' => [
				'type'              => 'colorpicker',
				'label'             => esc_html__( 'Button color', 'helper' ),
				'description'       => esc_html__( 'Speech recognition button color', 'helper' ),
				'default'           => '#4f32e6',
			],

			'bot_sst_size' => [
				'type'              => 'slider',
				'label'             => esc_html__( 'Icon size', 'helper' ),
				'description'       => wp_sprintf(
					/* translators: %s: size */
					esc_html__( 'Button size %s pixels.', 'helper' ),
					'<strong>' . esc_html( ( ! empty( $options['bot_sst_size'] ) ) ? $options['bot_sst_size'] : '20' ) . '</strong>'
				),
				'default'           => 20,
				'min'               => 4,
				'max'               => 40,
				'step'              => 1,
			],

			'bot_tts_divider' => [
				'type' => 'divider',
			],

			'bot_tts_heading' => [
				'type' => 'header',
				'label' => esc_html__( 'Speech syntheses', 'helper' ),
				'description' => esc_html__( 'The bot can convert messages to speech and speak to the user.' ),
			],

			'bot_tts' => [
				'type'              => 'switcher',
				'label'             => esc_html__( 'Text to Speech', 'helper' ),
				'placeholder'       => esc_html__( 'Enable Text to Speech', 'helper' ),
				'description'       => esc_html__( 'Speech synthesis based on WebSpeech API', 'helper' ),
				'default'           => 'on',
			],

			'bot_tts_multilingual' => [
				'type'              => 'switcher',
				'label'             => esc_html__( 'Multilingual', 'helper' ),
				'placeholder'       => esc_html__( 'Enable multilingual', 'helper' ),
				'description'       => esc_html__( 'The bot will speak in the language of the page locale and ignore voice chooses', 'helper' ),
				'default'           => 'off',
			],


            'bot_tts_voice' => [
                'type' => 'chosen',
                'label' => esc_html__( 'WebSpeech voice name', 'helper' ),
                'description' => wp_sprintf(
                /* translators: %s: link */
                    esc_html__( 'Specify the name of the voice. You can view and listen to all available voices by following this link %s. You can also set separate voices for Webkit browsers', 'helper' ),
                    '<a href="https://docs.merkulov.design/text-to-speech-supported-voices-and-languages/" target="_blank">' . esc_html__( 'Web Speech API supported voices', 'helper' ) . '</a>'
                ),
                'default' => ['Google US English;en-US', 'Trinoids;en-US'],
                'options' => [],
                'attr' => [
                    'multiple' => 'multiple'
                ]
            ],

			'bot_visual_behaviour_divider' => [
				'type' => 'divider',
			],

			'bot_visual_behaviour_heading' => [
				'type' => 'header',
				'label' => esc_html__( 'Humanoid behavior', 'helper' ),
				'description' => esc_html__( 'This block of settings is responsible for simulating human behavior' ),
			],

			'bot_typing_animation' => [
				'type'              => 'switcher',
				'label'             => esc_html__( 'Bot typing animation', 'helper' ),
				'placeholder'       => esc_html__( 'Bot typing animation', 'helper' ),
				'description'       => esc_html__( 'Bot typing animation', 'helper' ),
				'default'           => 'on',
			],

			'bot_typing_animation_delay' => [
				'type'              => 'slider',
				'label'             => esc_html__( 'Typing animation delay', 'helper' ),
				'description'       => wp_sprintf(
					/* translators: %s: delay */
					esc_html__( 'Typing animation delay %s milliseconds.', 'helper' ),
					'<strong>' . esc_html( ( ! empty( $options['bot_typing_animation_delay'] ) ) ? $options['bot_typing_animation_delay'] : '50' ) . '</strong>'
				),
				'default'           => 50,
				'min'               => 0,
				'max'               => 300,
				'step'              => 1,
				'discrete'          => false,
			],

			'bot_respond_delay_enabled' => [
				'type'              => 'switcher',
				'label'             => esc_html__( 'Bot respond delay', 'helper' ),
				'placeholder'       => esc_html__( 'Bot respond delay', 'helper' ),
				'description'       => esc_html__( 'Delay before answering, simulating the delay of a real person before replying to a message', 'helper' ),
				'default'           => 'off',
			],

			'bot_respond_delay' => [
				'type'              => 'slider',
				'label'             => esc_html__( 'Delay', 'helper' ),
				'description'       => wp_sprintf(
					/* translators: %s: seconds */
					esc_html__( 'Bot respond delay %s seconds.', 'helper' ),
					'<strong>' . esc_html( ( ! empty( $options['bot_respond_delay'] ) ) ? $options['bot_respond_delay'] : '0' ) . '</strong>'
				),
				'default'           => 0,
				'min'               => 0,
				'max'               => 10,
				'step'              => 0.1,
				'discrete'          => false,
			],

			'bot_respond_delay_randomize' => [
				'type'              => 'switcher',
				'label'             => esc_html__( 'Random delay', 'helper' ),
				'placeholder'       => esc_html__( 'Randomize delay', 'helper' ),
				'description'       => esc_html__( 'This setting creates a random delay for each message between 100 milliseconds and the specified value.', 'helper' ),
				'default'           => 'off',
			],

			'bot_message_preloader' => [
				'type'              => 'switcher',
				'label'             => esc_html__( 'Delay animation', 'helper' ),
				'placeholder'       => esc_html__( 'Show animation', 'helper' ),
				'description'       => esc_html__( 'Sets message preloader in loading message when respond delay is grater than 0', 'helper' ),
				'default'           => 'off',
			],

			'bot_memory_behaviour_divider' => [
				'type' => 'divider',
			],

			'bot_memory_behaviour_heading' => [
				'type' => 'header',
				'label' => esc_html__( 'Remembrance', 'helper' ),
				'description' => esc_html__( 'This group of settings to save and use the previous experience of interacting with the bot' ),
			],

			'bot_memory_enabled' => [
				'type'              => 'switcher',
				'label'             => esc_html__( 'Remembrance', 'helper' ),
				'placeholder'       => esc_html__( 'Save last question and answer', 'helper' ),
				'description'       => esc_html__( 'Save last question and answer', 'helper' ),
				'default'           => 'off',
			],

			'question_answer_local_storage_time' => [
				'type'              => 'slider',
				'label'             => esc_html__( 'Storage duration', 'helper' ),
				'description'       => wp_sprintf(
				/* translators: %s: hours */
					esc_html__( 'Save last question and answer for %s hours.', 'helper' ),
					'<strong>' . esc_html( ( ! empty( $options['question_answer_local_storage_time'] ) ) ? $options['question_answer_local_storage_time'] : '1' ) . '</strong>'
				),
				'default'           => 1,
				'min'               => 1,
				'max'               => 20,
				'step'              => 1,
				'discrete'          => false,
			],

		];

		$tabs[ 'behavior' ][ 'fields' ] = $fields;
		Plugin::set_tabs( $tabs );
		Settings::get_instance()->get_options();

		return $fields;

	}

}



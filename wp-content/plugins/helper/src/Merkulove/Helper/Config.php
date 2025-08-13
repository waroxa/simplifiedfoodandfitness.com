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
use Merkulove\Helper\Unity\Tab;
use Merkulove\Helper\Unity\TabGeneral;
use Merkulove\Helper\Unity\UI;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * @package Merkulove\Helper
 **/
final class Config {

	/**
	 * The one true Settings.
	 *
     * @since 1.0.0
     * @access private
	 * @var Config
	 **/
	private static $instance;

    /**
     * Divider unique id
     * @var int
     */
    private static int $divider = 0;

    /**
     * Import files control
     *
     * @param $key
     * @param $tab_slug
     * @return void
     */
    public function render_import_file( $key, $tab_slug ) {
        list( $field, $label, $description, $attr ) = TabGeneral::get_instance()->prepare_general_params( $tab_slug, $key );

        if ( empty( $field[ 'placeholder' ] ) ) {
            $label = '';
        }

        UI::get_instance()->render_import(
            Settings::get_instance()->options[$key],
            $label,
            $description,
            $key,
            $tab_slug
        );
    }

    public function render_slider_with_input( $key, $tab_slug  ) {
        list( $field, $label, $description, $attr ) = TabGeneral::get_instance()->prepare_general_params( $tab_slug, $key );

        echo '<div class="mdp-helper-measures mdp-slider-with-input">';

        echo '<div class="mdp-helper-measures-slider">';
        /** Render slider. */
        UI::get_instance()->render_slider(
            Settings::get_instance()->options[$key],
            $field['min'],
            $field['max'],
            $field['step'],
            $label,
            $description,
            $attr,
            $field['discrete']
        );

        echo '</div>';

        echo '<div class="mdp-helper-measures-input">';

        UI::get_instance()->render_input(
            Settings::get_instance()->options[$key],
        );

        echo '</div>';

    }

    public function render_text_message( $key, $tab_slug  ) {
        list( $field, $label, $description, $attr ) = TabGeneral::get_instance()->prepare_general_params( $tab_slug, $key );

        echo '<div class="mdp-helper-text-message '. esc_attr( $attr['class'] ) .'">';
        echo wp_kses_post( $description );
        echo '</div>';

    }

    /**
     * Slider with units control
     *
     * @param $key
     * @param $tab_slug
     * @return void
     */
    public function render_measures_slider( $key, $tab_slug ) {
        list( $field, $label, $description, $attr ) = TabGeneral::get_instance()->prepare_general_params( $tab_slug, $key );
        $css_units = [
            'px' => 'px',
            '%' => '%',
            'em' => 'em',
            'rem' => 'rem',
            'vh' => 'vh',
            'vw' => 'vw'
        ];

        $custom_units = isset( Plugin::get_tabs()[$tab_slug]['fields'][$key]['custom_units'] ) ? Plugin::get_tabs()[$tab_slug]['fields'][$key]['custom_units']: null;
        $custom_default = isset( Plugin::get_tabs()[$tab_slug]['fields'][$key]['default_custom_unit'] ) ? Plugin::get_tabs()[$tab_slug]['fields'][$key]['default_custom_unit'] : null;

        echo '<div class="mdp-helper-measures">';

        echo '<div class="mdp-helper-measures-slider">';
        /** Render slider. */
        UI::get_instance()->render_slider(
            Settings::get_instance()->options[$key],
            $field['min'],
            $field['max'],
            $field['step'],
            $label,
            $description,
            $attr,
            $field['discrete']
        );

        echo '</div>';

        echo '<div class="mdp-helper-measures-input">';

        UI::get_instance()->render_input(
            Settings::get_instance()->options[$key],
        );

        echo '</div>';

        echo '<div class="mdp-helper-measures-units">';

        UI::get_instance()->render_select(
    !empty( $custom_units ) ? $custom_units :  $css_units,
    Settings::get_instance()->options[$key . '_unit'] ?? $custom_default ?? 'px', // Selected option.
      'Unit',
  'Select unit',
            [
                'id' => 'mdp_helper_' . $tab_slug . '_settings_' . $key . '_unit',
                'name' => 'mdp_helper_' . $tab_slug . '_settings[' . $key . '_unit' . ']',
            ]
        );

        echo '</div>';

        echo '</div>';
    }

    /**
     * Refresh settings
     *
     * @param $tabs
     *
     * @return array
     */
    private function refresh_settings( $tabs ) {

        /** Set updated tabs. */
        Plugin::set_tabs( $tabs );

        /** Refresh settings. */
        Settings::get_instance()->get_options();

        /** Get default plugin settings. */
        return Plugin::get_tabs();

    }

    /**
     * Get posts based on post types
     *
     * @param bool $post_types_param
     * @return array
     */
    public function get_posts( $post_types_param = [] ) {

        $post_types_settings = isset( Settings::get_instance()->options['open_ai_post_types'] ) ?
            Settings::get_instance()->options['open_ai_post_types'] :
            ['post', 'page'];

        $post_types = count( $post_types_param ) <= 0 ? $post_types_settings : $post_types_param;

        global $wpdb;

        $options = [];

        foreach ( $post_types as $post_type ) {
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT ID, post_title FROM $wpdb->posts WHERE post_type = %s AND post_status IN ('publish', 'draft')",
                    $post_type
                )
            );
            foreach ( $results as $post ) {
                $options['post_' . $post->ID] = $post->post_title;
            }
        }



        return $options;
    }

    /**
     * Creates border styles controls.
     *
     * @return array[]
     * @since 1.0.0
     * @access public
     *
     */
    public function border_controls( $slug, $default = [] ): array {

		return [

			$slug . '_border_style' => [
				'type' => 'select',
				'label' => esc_html__( 'Border style', 'helper' ),
				'description' => esc_html__( 'Select border style on disable border for block.', 'helper' ),
				'default' => $default[ 'border_style' ] ?? 'none',
				'options' => [
					'none' => esc_html__( 'None', 'helper' ),
					'dotted' => esc_html__( 'Dotted', 'helper' ),
					'dashed' => esc_html__( 'Dashed', 'helper' ),
					'solid' => esc_html__( 'Solid', 'helper' ),
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
				'description' => esc_html__( 'Set border color.', 'helper' ),
				'default' => $default['border_color'] ?? 'rgba(0, 0, 0, 1)',
			],

			$slug . '_border_width' => [
				'type' => 'sides',
				'label' => esc_html__( 'Border width', 'helper' ),
				'description' => esc_html__( 'Set border width.', 'helper' ),
				'default' => $default['border_width'] ?? [
					'top' => '0',
					'right' => '0',
					'bottom' => '1',
					'left' => '0',
				],
			],

			$slug . '_border_radius' => [
				'type'			  => 'sides',
				'label'			  => esc_html__( 'Border radius', 'helper' ),
				'description'	  => esc_html__( 'Set border-radius', 'helper' ),
				'default'         => $default['border_radius'] ?? [
					'top' => '0',
					'right' => '0',
					'bottom' => '0',
					'left' => '0',
				],
			],

		];

    }

    /**
     * Creates AI bot personalities tab.
     *
     * @return array[]
     * @since 1.0.0
     * @access public
     *
     */
    private function personalities_tab( $tabs ): array {
        $offset = 4;
        $tabs = array_slice( $tabs, 0, $offset, true ) +
            ['ai_personalities' => [
                'enabled'       => true,
                'class'         => TabGeneral::class, // Handler
                'label'         => esc_html__( 'Personalities', 'helper' ),
                'title'         => esc_html__( 'AI Bot Personalities Settings', 'helper' ),
                'show_title'    => true,
                'icon'          => 'account_box',
                'fields'        => []
            ] ] +
            array_slice( $tabs, $offset, NULL, true );

        $assistants = OpenAiBot::get_instance()->get_assistants_list();

        $personalities_quantity = 10;

        $personalities = Caster::get_instance()->get_all_repeater_data(
            10,
             'personality_name_',
       'personality'
        );

        $tabs['ai_personalities']['fields']['global_personality'] = [
            'type' => 'select',
            'label' => esc_html__('Active personality', 'helper'),
            'description' => esc_html__('Choose your bot personality. ', 'helper'),
            'default' => 'personality_0',
            'options' => $personalities
        ];

        $tabs['ai_personalities']['fields']['ai_assistant_version'] = [
            'type' => 'select',
            'label' => esc_html__( 'Assistant version', 'helper' ),
            'description' => wp_sprintf(
                esc_html__( 'Choose your assistant version. %s' ),
                '<b>Note: If you are using the latest version of OpenAI\'s assistants, please select the "latest" option!</b>'
            ),
            'default' => 'latest',
            'options' => [
                'latest' => esc_html__( 'Latest', 'helper' ),
                'v1' => esc_html__( 'v1', 'helper' ),
            ],
        ];

        /** Set default assistant version */
        if (
            empty( get_option( 'mdp_helper_ai_settings') ) &&
            empty( get_option( 'mdp_helper_ai_personalities_settings' ) ) &&
            $tabs['ai_personalities']['fields']['ai_assistant_version']['default'] === 'v1'
        ) {
            $tabs['ai_personalities']['fields']['ai_assistant_version']['default'] = 'latest';
        } elseif (
            !empty( get_option( 'mdp_helper_ai_settings' ) ) &&
            !empty( get_option( 'mdp_helper_ai_personalities_settings' ) )
        ) {
            $tabs['ai_personalities']['fields']['ai_assistant_version']['default'] = 'v1';
        }

        $tabs['ai_personalities']['fields']['personality_heading'] = [
            'type'              => 'header',
            'label'             => esc_html__( 'Create a new bot personality', 'helper' ),
            'description'       => esc_html__( '', 'helper' ),
        ];

        for ( $i = 0; $i < $personalities_quantity; $i++ ) {

            $tabs['ai_personalities']['fields']['personality_name_' . $i] = [
                'type'              => 'text',
                'label'             => esc_html__( 'Personality name', 'helper' ),
                'description'       => wp_sprintf(
                // translators: %s is a link to the Open Ai api key page.
                    esc_html__( 'Set personality name', 'helper' ),
                ),
                'default'           => '',
                'attr' => [
                    'class' => 'mdc-input-width-100 mdp-bot-personality-name',
                ]
            ];

            $tabs['ai_personalities']['fields']['ai_type_' . $i] = [
                'type' => 'select',
                'label' => esc_html__( 'AI Bot Type', 'helper' ),
                'description' => esc_html__( 'Choose AI bot type. ', 'helper' ),
                'default' => 'model',
                'options' => [
                    'model' => esc_html__( 'GPT Model', 'helper' ),
                    'assistant' => esc_html__( 'Open AI Assistant', 'helper' ),
                ],
            ];

            if ( !empty( $assistants ) ) {
                $tabs['ai_personalities']['fields']['ai_assistants_' . $i] = [
                    'type' => 'select',
                    'label' => esc_html__( 'Assistant', 'helper' ),
                    'description' => wp_sprintf(
                        esc_html__( 'Choose your assistant. %s' ),
                        '<a href="https://platform.openai.com/assistants" target="_blank">' . esc_html__( 'Create Open AI assistant' ) . '</a>',
                    ),
                    'default' => 'model',
                    'options' => !empty( $assistants ) ? wp_list_pluck( $assistants, 'name', 'id' ) : [],
                    'attr' => [
                        'class' => 'mdp-helper-ai-bot-personalities-fields-' . $i . ' mdp-helper-ai-bot-personality-' . $i . '-assistant'
                    ]
                ];
            } else {
                $tabs['ai_personalities']['fields']['open_ai_assistant_error_message_' . $i] = [
                    'type'              => 'custom_type',
                    'render'            =>  [$this, 'render_text_message' ],
                    'description'       => wp_sprintf(
                        '<div class="mdp-helper-error-text">' . esc_html__( 'Create Open AI assistant.' ) . '</div>' .
                        esc_html__( 'You should log in or sign up and create it by following link: %s %s', 'helper' ),
                       '<a href="https://platform.openai.com/assistants" target="_blank">' . esc_html__( 'Create Open AI assistant' ) . '</a><br />',
                       '<a href="https://docs.merkulov.design/create-open-ai-assistants/" target="_blank">' . esc_html__( 'How to create Open AI assistant' ) . '</a>'
                    ),
                    'attr' => [
                        'class' => 'mdp-helper-ai-bot-personalities-fields-' . $i . ' mdp-helper-ai-bot-personality-' . $i . '-assistant'
                    ]
                ];
            }


            $tabs['ai_personalities']['fields'][ 'ai_personality_divider_' . $i ] = ['type' => 'divider' ];

        }

        $tabs['ai_personalities']['fields']['open_ai_add_personality' ] = [
            'type'              => 'button',
            'placeholder'       => esc_html__( 'Add bot personality', 'helper' ),
            'default'           => '',
            'icon'              => 'add',
            'attr'              => [
                'class'     => 'mdc-button--outlined',
                'id'        => 'mdp-add-bot-personality'
            ]
        ];

        $tabs['ai_personalities']['fields']['open_ai_remove_personality' ] = [
            'type'              => 'button',
            'placeholder'       => esc_html__( 'Remove bot personality', 'helper' ),
            'default'           => '',
            'icon'              => 'remove',
            'attr'              => [
                'class'     => 'mdc-button--outlined',
                'id'        => 'mdp-remove-bot-personality'
            ]
        ];

        /** Set default values */
        $tabs['ai_personalities']['fields']['personality_name_0']['default'] = 'GPT model' ?? '';
        $tabs['ai_personalities']['fields']['ai_type_0']['default'] = 'model' ?? '';

        return $tabs;
    }

    /**
     * Creates AI bot tab.
     *
     * @return array[]
     * @since 1.0.0
     * @access public
     *
     */
    private function ai_bot_tab( $tabs ): array {
        $prompt_type_options = [
            'custom' => esc_html__( 'Custom', 'helper' ),
            'post_content' => esc_html__( 'Post content', 'helper' ),
            'pdf_file' => esc_html__( 'PDF file', 'helper' )
        ];

        // Add woocommerce option if woocommerce is active
        if ( in_array( 'woocommerce/woocommerce.php', apply_filters('active_plugins', get_option( 'active_plugins' ) ) ) ) {
            $prompt_type_options['woocommerce_products'] = esc_html__( 'WooCommerce products', 'helper' );
        }


            /** Shorthand access to plugin settings. */
        $offset = 3;
        $tabs = array_slice( $tabs, 0, $offset, true ) +
            ['ai' => [
                'enabled'       => true,
                'class'         => TabGeneral::class, // Handler
                'label'         => esc_html__( 'AI', 'helper' ),
                'title'         => esc_html__( 'Artificial Intelligence Settings', 'helper' ),
                'show_title'    => true,
                'icon'          => 'smart_toy',
                'fields'        => []
            ] ] +
            array_slice( $tabs, $offset, NULL, true );

        $keys_quantity = 10;

        $tabs['ai']['fields']['open_ai_api_key'] = [
            'type'              => 'text',
            'label'             => esc_html__( 'Open AI API key', 'helper' ),
            'description'       => wp_sprintf(
            // translators: %s is a link to the Open Ai api key page.
                esc_html__( 'Set Open AI api key. You should sign up and get it by following link: %s', 'helper' ),
                '<a href="https://platform.openai.com/account/api-keys" target="_blank">' . esc_html__( 'Get API key' ) . '</a>'
            ),
            'default'           => '',
            'attr' => [
                'class' => 'mdc-input-width-100',
            ]
        ];


        if ( !empty( Settings::get_instance()->options['open_ai_api_key'] ) ) {

            $tabs['ai']['fields']['open_ai_add_additional_keys'] = [
                'type'              => 'switcher',
                'label'             => esc_html__( 'Add additional keys', 'helper' ),
                'placeholder'       => esc_html__( 'Add additional keys', 'helper' ),
                'description'       => esc_html__( 'When using additional keys, the key to the request will be chosen randomly.', 'helper' ),
                'default'           => 'off',
            ];

            for ( $i = 0; $i < $keys_quantity; $i++ ) {

                $tabs['ai']['fields'][ 'open_ai_api_key_' . $i ] = [
                    'type'              => 'text',
                    'label'             => esc_html__( 'Additional Open AI API key', 'helper' ),
                    'description'       => wp_sprintf(
                    // translators: %s is a link to the Open Ai api key page.
                        esc_html__( 'Set Open AI api key. You should sign up and get it by following link: %s', 'helper' ),
                        '<a href="https://platform.openai.com/account/api-keys" target="_blank">' . esc_html__( 'Get API key' ) . '</a>'
                    ),
                    'default'           => '',
                    'attr' => [
                        'class' => 'mdc-input-width-100 mdp-open-ai-key',
                    ],
                ];

            }

            $tabs['ai']['fields']['open_ai_add_key' ] = [
                'type'              => 'button',
                'placeholder'       => esc_html__( 'Add key', 'helper' ),
                'default'           => '',
                'icon'              => 'add',
                'attr'              => [
                    'class'     => 'mdc-button--outlined mdp-add-open-ai-key',
                    'id'        => 'mdp-add-ai-key'
                ]
            ];

            $tabs['ai']['fields']['open_ai_remove_key' ] = [
                'type'              => 'button',
                'placeholder'       => esc_html__( 'Remove key', 'helper' ),
                'default'           => '',
                'icon'              => 'remove',
                'attr'              => [
                    'class'     => 'mdc-button--outlined mdp-remove-open-ai-key',
                    'id'        => 'mdp-remove-ai-key'
                ]
            ];

            $tabs['ai']['fields'][ 'divider_additional_api_keys' ] = ['type' => 'divider' ];

            $tabs['ai']['fields']['open_ai_prompt_type'] = [
                'type' => 'chosen',
                'label' => esc_html__( 'Prompt type', 'helper' ),
                'description' => esc_html__( 'Choose content that will be included in your prompt', 'helper' ),
                'default' => ['custom', 'woocommerce_products', 'post_content'],
                'options' => $prompt_type_options,
                'attr' => [
                    'multiple' => 'multiple'
                ]
            ];

            $tabs['ai']['fields']['open_ai_product_attrs'] = [
                'type' => 'chosen',
                'label' => esc_html__( 'Product attributes', 'helper' ),
                'description' => esc_html__( 'Select product attributes that you want to be included in the prompt.', 'helper' ),
                'default' => ['name', 'description', 'category', 'price', 'on_sale', 'in_stock', 'link'],
                'options' => [
                    'name' => esc_html__( 'Name', 'helper' ),
                    'description' => esc_html__( 'Description', 'helper' ),
                    'category' => esc_html__( 'Category', 'helper' ),
                    'price' => esc_html__( 'Price', 'helper' ),
                    'on_sale' => esc_html__( 'On sale', 'helper' ),
                    'in_stock' => esc_html__( 'In stock', 'helper' ),
                    'link' => esc_html__( 'Link', 'helper' ),
                ],
                'attr' => [
                    'class' => 'mdp-helper-open-ai-fields mdp-helper-prompt-woocommerce_products',
                    'multiple' => 'multiple'
                ]
            ];


            $tabs['ai']['fields']['open_ai_pdf_file'] = [
                'type'              => 'custom_type',
                'render'            => [ Config::get_instance(), 'render_import_file' ],
                'label'             => esc_html__( 'PDF file', 'helper' ),
                'show_label'        => true,
                'description'       => '',
                'show_description'  => false,
                'default'           => '',
                'attr'              => [
                    'class' => 'mdp-helper-open-ai-fields mdp-helper-prompt-pdf_file'
                ]
            ];

            $tabs['ai']['fields']['open_ai_post_types'] = [
                'type'              => 'cpt',
                'label'             => esc_html__( 'Post Types', 'helper' ),
                'description'       => esc_html__( 'Select post types from what you want get content.', 'helper' ),
                'default'           => [ 'post', 'page' ],
                'attr'              => [
                    'class' => 'mdp-helper-open-ai-fields mdp-helper-prompt-post_content',
                    'multiple' => 'multiple',
                ]
            ];

            $tabs['ai']['fields']['open_ai_post_id'] = [
                'type'              => 'chosen',
                'label'             => esc_html__( 'Posts', 'helper' ),
                'description'       => esc_html__( 'Select posts from what you want get content.', 'helper' ),
                'default'           => '',
                'options'           => $this->get_posts(),
                'attr'              => [
                    'class' => 'mdp-helper-open-ai-fields mdp-helper-prompt-post_content',
                    'multiple' => 'multiple',
                ]
            ];


            $tabs['ai']['fields']['open_ai_prompt'] = [
                'type' => 'textarea',
                'label' => esc_html__( 'Prompt', 'helper' ),
                'description' => esc_html__( 'Write description or documentation about your product, website, service or something else as the context of bot answers', 'helper' ),
                'default' => '',
                'attr' => [
                    'class' => 'mdp-helper-open-ai-textarea mdp-helper-open-ai-fields mdp-helper-prompt-custom'
                ]
            ];

            $tabs['ai']['fields']['open_ai_objective'] = [
                'type' => 'textarea',
                'label' => esc_html__( 'Objective', 'helper' ),
                'description' => esc_html__( 'Write objective for AI bot how it should answer based on
                                                    provided text. For multilingual sites, it is recommended to add "answer in the language in which the question was asked"', 'helper' ),
                'default' => esc_html__( 'Answer questions that users have about the text according to the text above.', 'helper' ),
            ];

            $tabs['ai']['fields']['open_ai_notice'] = [
                'type' => 'textarea',
                'label' => esc_html__( 'Notice', 'helper' ),
                'description' => esc_html__( 'Write how bot should respond if it can`t find answer in 
                                              prompt', 'helper' ),
                'default' => 'If the question is unrelated to the documentation, tell the user that you can`t find.',
                'attr' => [
                    'class' => 'mdp-helper-open-ai-textarea-sm'
                ]
            ];

            $tabs['ai']['fields']['open_ai_model'] = [
                'type' => 'select',
                'label' => esc_html__( 'Model', 'helper' ),
                'description' => esc_html__( 'Choose model. Learn more about models at ', 'helper' )
                    . "<a href='https://platform.openai.com/docs/models' target='_blank'>".
                         esc_html__( "Open AI documentation", 'helper' )
                    ."</a>",
                'default' => 'gpt-3.5-turbo',
                'options' => Caster::get_instance()->get_current_models()
            ];

            $tabs['ai']['fields']['open_ai_max_tokens'] = [
                'type' => 'text',
                'label' => esc_html__( 'Max bot response tokens', 'helper' ),
                'description' => esc_html__( 'Set max completion tokens. 
                Please note that this value is summed with the number of tokens in prompt and the sum of these values
                should not exceed the limits of the model.', 'helper' ),
                'default' => '500',
            ];

            $tabs['ai']['fields']['open_ai_temperature'] = [
                'type' => 'slider',
                'label' => esc_html__( 'Temperature', 'helper' ),
                'description' => wp_sprintf(
	                // translators: %s is a link to the Open Ai api key page.
	                esc_html__( 'Higher values like 0.8 will make the output more random, while lower values like 0.2 will make it more focused and deterministic. Current temperature is: %s', 'helper' ),
	                '<strong>' . esc_html( ( !empty(Settings::get_instance()->options['open_ai_temperature'] ) ) ? Settings::get_instance()->options['open_ai_temperature'] : '0.9' ) . '</strong>'
                ),
                'default' => 0.9,
                'min' => 0,
                'max' => 1,
                'step' => 0.1,
                'discrete' => false,
            ];

            $tabs['ai']['fields']['open_ai_set_user_requests_limit'] = [
                'type'              => 'switcher',
                'label'             => esc_html__( 'Limit user requests', 'helper' ),
                'placeholder'       => esc_html__( 'Limit user requests', 'helper' ),
                'description'       => esc_html__( 'Limits user requests by user IP.', 'helper' ),
                'default'           => 'off',
            ];

            $tabs['ai']['fields']['open_ai_restriction_time'] = [
                'type'              => 'custom_type',
                'render'            =>  [$this, 'render_slider_with_input' ],
                'label'             => esc_html__( 'Set restriction time', 'helper' ),
                'description'       => wp_sprintf(
                    esc_html__( 'Set restriction time. Current: %s minutes', 'helper' ),
                    '<strong>' . esc_html( ( ! empty( Settings::get_instance()->options['open_ai_restriction_time'] ) ) ? Settings::get_instance()->options['open_ai_restriction_time'] : '10' ) . '</strong>'
                ),
                'default'           => 1,
                'min'               => 0.1,
                'max'               => 1000,
                'step'              => 1,
                'discrete'          => false,
            ];

            $tabs['ai']['fields']['open_ai_restriction_requests'] = [
                'type'              => 'custom_type',
                'render'              => [$this, 'render_slider_with_input' ],
                'label'             => esc_html__( 'Set max requests', 'helper' ),
                'description'       => wp_sprintf(
                    esc_html__( 'Set max requests per restriction time. Current: %s', 'helper' ),
                    '<strong>' . esc_html( ( ! empty( Settings::get_instance()->options['open_ai_restriction_requests'] ) ) ? Settings::get_instance()->options['open_ai_restriction_requests'] : '10' ) . '</strong>'
                ),
                'default'           => 10,
                'min'               => 1,
                'max'               => 100,
                'step'              => 1,
                'discrete'          => false,
            ];

            $tabs['ai']['fields']['open_ai_restriction_error_message'] = [
                'type'              => 'text',
                'label'             => esc_html__( 'Error message', 'helper' ),
                'description'       => esc_html__( 'Error message when user exceeded limit', 'helper' ),
                'default'           => esc_html__( 'You exceeded your requests limit.', 'helper' ),
            ];

        }

        return $tabs;
    }

	/**
	 * Bot response messages
	 *
	 * @param $tabs
	 * @param $tab_name
	 * @param bool $include_bot_message
	 * @param bool $include_success_message
	 * @param array $buttons_text
	 * @param array $default_values
	 *
	 * @return array
	 */
    public function set_bot_response_messages( $tabs, $tab_name, bool $include_bot_message = true, bool $include_success_message = true, array $buttons_text = [], array $default_values = [], $key_name = '', $heading = '', $description = '', $posts = [] ): array {

		$messages_quantity = 20;

        $heading_text = empty( $heading ) ? esc_html__( 'Bot response messages', 'helper' ) : esc_html__( $heading, 'helper' );
        $description_text = empty( $description ) ? esc_html__( 'Set bot response messages', 'helper' ) : esc_html__( $description, 'helper' );

	    $tabs[$tab_name]['fields'][$tab_name . $key_name . '_section_divider'] = [ 'type' => 'divider' ];

	    $tabs[$tab_name]['fields'][$tab_name . $key_name . '_section_heading'] = [
					    'type' => 'header',
					    'label' => $heading_text,
					    'description' => $description_text,
	    ];

        for ( $i = 0; $i < $messages_quantity; $i++ ) {
            $tabs[$tab_name]['fields'][ 'divider_' . $tab_name . $key_name . $this::$divider ] = ['type' => 'divider' ];
            $this::$divider++;

            if ( $include_bot_message ) {
                $tabs[$tab_name]['fields'][$tab_name . $key_name . '_bot_message_' . $i] = [
                    'type' => 'textarea',
                    'label' => esc_html__( 'Bot message', 'helper' ),
                    'description' => esc_html__( 'Set bot message', 'helper' ),
                    'default' => '',
                    'attr' => [
                        'class' => 'mdp-helper-' . $tab_name . $key_name . '-message'
                    ]
                ];
            }

            if ( $include_success_message ) {
                $tabs[$tab_name]['fields'][$tab_name . $key_name . '_bot_success_message_' . $i] = [
                    'type' => 'textarea',
                    'label' => esc_html__( 'Bot success message', 'helper' ),
                    'description' => esc_html__( 'Set bot success message', 'helper' ),
                    'default' => '',
                    'attr' => [
                        'class' => !$include_bot_message ? 'mdp-helper-' . $tab_name . $key_name . '-message' : ''
                    ]
                ];
            }

            $tabs[$tab_name]['fields'][ $tab_name . $key_name . '_bot_error_message_' . $i ] = [
                'type'              => 'textarea',
                'label'             => esc_html__( 'Bot error message', 'helper' ),
                'description'       => esc_html__( 'Set bot error message', 'helper' ),
                'default'           => '',
            ];

            if ( !empty( $posts ) ) {
                $tabs[$tab_name]['fields'][$tab_name . $key_name . '_message_condition_' . $i] = [
                    'type'              => 'select',
                    'label'             => esc_html__( 'Message condition', 'helper' ),
                    'description'       => esc_html__( 'Choose condition where display this message', 'helper' ),
                    'default'           => 'global',
                    'options'           => [
                        'global' => esc_html__( 'Global', 'helper' ),
                        'posts' => esc_html__( 'Posts', 'helper' ),
                    ],
                ];

                $tabs[$tab_name]['fields'][$tab_name . $key_name . '_message_condition_post_types_' . $i] = [
                    'type'              => 'cpt',
                    'label'             => esc_html__( 'Post Types', 'helper' ),
                    'description'       => esc_html__( 'Show this message on selected post types.', 'helper' ),
                    'default'           => [ 'post', 'page' ],
                    'attr'              => [
                        'class' => 'mdp-helper-message-conditions-fields-' . $i . ' mdp-helper-message-conditions-' . $i . '-posts',
                        'multiple' => 'multiple',
                    ]
                ];

                $tabs[$tab_name]['fields'][$tab_name . $key_name . '_select_manually_posts_' . $i] = [
                    'type'              => 'switcher',
                    'label'             => esc_html__( 'Include only on selected posts', 'helper' ),
                    'placeholder'       => esc_html__( 'Include only on selected posts', 'helper' ),
                    'description'       => esc_html__( 'Show this message only on selected posts', 'helper' ),
                    'default'           => 'off',
                    'attr'              => [
                        'class' => 'mdp-helper-message-conditions-fields-' . $i . ' mdp-helper-message-conditions-' . $i . '-posts',
                    ]
                ];

                $tabs[$tab_name]['fields'][$tab_name . $key_name . '_message_condition_posts_' . $i] = [
                    'type'              => 'chosen',
                    'label'             => esc_html__( 'Posts', 'helper' ),
                    'description'       => esc_html__( 'Show this message on selected posts.', 'helper' ),
                    'default'           => '',
                    'options'           => $posts,
                    'attr'              => [
                        'class' => 'mdp-helper-message-conditions-fields-' . $i . ' mdp-helper-message-conditions-' . $i . '-posts',
                        'multiple' => 'multiple',
                    ]
                ];
            }


        }

        $tabs[$tab_name]['fields'][$tab_name . $key_name . '_add_message'] = [
            'type'              => 'button',
            'placeholder'       => isset( $buttons_text['add_button'] ) ?
                                   esc_html__( $buttons_text['add_button'], 'helper' ) :
                                   esc_html__( 'Add messages', 'helper' ),
            'default'           => '',
            'icon'              => 'add',
            'attr'              => [
                'class'     => 'mdc-button--outlined mdp-add-message-' . $tab_name . $key_name,
                'id'        => 'mdp-add-message-' . $tab_name . $key_name
            ]
        ];

        $tabs[$tab_name]['fields'][ $tab_name . $key_name . '_remove_message'] = [
            'type'              => 'button',
            'placeholder'       => isset( $buttons_text['remove_button'] ) ?
                                   esc_html__( $buttons_text['remove_button'], 'helper' ) :
                                   esc_html__( 'Remove messages', 'helper' ),
            'default'           => '',
            'icon'              => 'remove',
            'attr'              => [
                'class'     => 'mdc-button--outlined mdp-remove-message-' . $tab_name . $key_name,
                'id'        => 'mdp-remove-message-' . $tab_name . $key_name
            ]
        ];

        /** Set default values */
        if ( $include_bot_message ) {
            $tabs[$tab_name]['fields'][$tab_name . $key_name . '_bot_message_0']['default'] = $default_values['bot_message'] ?? '';
        }

        if ( $include_success_message ) {
            $tabs[$tab_name]['fields'][$tab_name . $key_name . '_bot_success_message_0']['default'] = $default_values['success_message'] ?? '';
        }

        $tabs[$tab_name]['fields'][ $tab_name . $key_name . '_bot_error_message_0' ]['default'] = $default_values['error_message'] ?? '';

        return $tabs;
    }

    /**
     * Set FAQ tab.
     *
     * @return array[]
     * @since 1.0.0
     * @access public
     *
     */
    private function set_faq_tab( $tabs ): array {
        /** Add FAQ tab after Design. */
        $offset = 5; // Position for new tab.
        $options = Settings::get_instance()->options;
        $tabs = array_slice( $tabs, 0, $offset, true ) +
            [ 'faq' => [
                'enabled'       => true,
                'class'         => TabGeneral::class,
                'label'         => esc_html__( 'FAQ', 'helper' ),
                'title'         => esc_html__( 'FAQ', 'helper' ),
                'show_title'    => true,
                'icon'          => 'quiz',
                'fields'        => []
            ] ] +
            array_slice( $tabs, $offset, NULL, true );

        $tabs['faq']['fields']['faq_button_name'] = [
            'type'              => 'text',
            'label'             => esc_html__( 'Button name', 'helper' ),
            'description'       => esc_html__( 'Set button name', 'helper' ),
            'default'           => esc_html__( 'FAQ', 'helper' ),
        ];

        $tabs['faq']['fields']['faq_pagination'] = [
            'type'              => 'switcher',
            'label'             => esc_html__( 'FAQ pagination', 'helper' ),
            'placeholder'       => esc_html__( 'FAQ pagination', 'helper' ),
            'description'       => esc_html__( 'Enables pagination for faq feature', 'helper' ),
            'default'           => 'off',
        ];

        $tabs['faq']['fields']['faq_pagination_count'] = [
            'type'              => 'slider',
            'label'             => esc_html__( 'Questions per page', 'helper' ),
            'description'       => wp_sprintf(
				esc_html__( 'Set questions per page. Current: %s', 'helper' ),
				'<strong>' . esc_html( ( ! empty( $options['faq_pagination_count'] ) ) ? $options['faq_pagination_count'] : '5' ) . '</strong>'
            ),
            'default'           => 5,
            'min'               => 1,
            'max'               => 20,
            'step'              => 1,
            'discrete'          => false,
        ];

        $tabs['faq']['fields']['show_faq_category_icon'] = [
            'type'              => 'switcher',
            'label'             => esc_html__( 'Show FAQ category button icon', 'helper' ),
            'placeholder'       => esc_html__( 'Show FAQ category button icon', 'helper' ),
            'description'       => esc_html__( 'Show FAQ category button icon', 'helper' ),
            'default'           => 'on',
        ];

        $tabs['faq']['fields']['faq_category_icon'] = [
            'type'              => 'icon',
            'label'             => esc_html__( 'FAQ category button icon', 'helper' ),
            'description'       => esc_html__( 'FAQ category button icon', 'helper' ),
            'default'           => 'font-awesome/arrow-alt-right.svg',
            'meta'              => [
                'helper.json',
                'font-awesome.json',
                'material.json'
            ]
        ];


        $faq_quantity = 70;
        for ( $i = 0; $i < $faq_quantity; $i++ ) {
            $tabs['faq']['fields'][ 'divider_' . $this::$divider ] = ['type' => 'divider' ];
            $this::$divider++;

            $tabs['faq']['fields'][ 'question_' . $i ] = [
                'type'              => 'textarea',
                'label'             => esc_html__( 'Question', 'helper' ),
                'placeholder'       => esc_html__( 'Question', 'helper' ),
                'description'       => esc_html__( 'Set your question', 'helper' ),
                'default'           => '',
                'attr'              => [
                    'class'     => 'mdp-helper-faq-question',
                ]
            ];

            $tabs['faq']['fields'][ 'question_answer_' . $i ] = [
                'type'              => 'editor',
                'label'             => esc_html__( 'Bot answer', 'helper' ),
                'description'       => esc_html__( 'Set your answer', 'helper' ),
                'default'           => '',
                'attr'              => [
                    'class'     => 'mdp-helper-faq-answer',
                    'textarea_rows'     => '',
                ]
            ];

            $tabs['faq']['fields'][ 'question_category_' . $i ] = [
                'type'              => 'textarea',
                'label'             => esc_html__( 'Question category', 'helper' ),
                'description'       => esc_html__( 'Enter category for question', 'helper' ),
                'default'           => '',
                'attr'              => [
                    'class'     => 'mdp-helper-faq-category',
                ]
            ];

        }

        /** Default faq question */
        $tabs['faq']['fields'][ 'question_0' ]['default'] = esc_html__( 'What is the plugin name?', 'helper' );
        $tabs['faq']['fields'][ 'question_answer_0' ]['default'] = 'Helper';

        $tabs['faq']['fields']['faq_add' ] = [
            'type'              => 'button',
            'placeholder'       => esc_html__( 'Add question/answer', 'helper' ),
            'default'           => '',
            'icon'              => 'add',
            'attr'              => [
                'class'     => 'mdc-button--outlined mdp-add-faq',
                'id'        => 'mdp-add-faq'
            ]
        ];

        $tabs['faq']['fields']['faq_remove' ] = [
            'type'              => 'button',
            'placeholder'       => esc_html__( 'Remove question/answer', 'helper' ),
            'default'           => '',
            'icon'              => 'remove',
            'attr'              => [
                'class'     => 'mdc-button--outlined mdp-remove-faq',
                'id'        => 'mdp-remove-faq'
            ]
        ];

        return $this->set_bot_response_messages( $tabs, 'faq', true, false, [], [
            'bot_message' => esc_html__( 'Choose question', 'helper' ),
            'error_message' => esc_html__( 'Something went wrong', 'helper' )
        ] );

    }

    /**
     * Set collect user data tab.
     *
     * @return array[]
     * @since 1.0.0
     * @access public
     *
     */
    private function set_collect_data_tab( $tabs ): array {
        $offset = 6;
        $tabs = array_slice( $tabs, 0, $offset, true ) +
            ['collect_data' => [
                'enabled'       => true,
                'class'         => TabGeneral::class, // Handler
                'label'         => esc_html__( 'Collect data', 'helper' ),
                'title'         => esc_html__( 'Collect data', 'helper' ),
                'show_title'    => true,
                'icon'          => 'save', // Icon for tab
                'fields'        => []
            ] ] +
            array_slice( $tabs, $offset, NULL, true );

        $tabs['collect_data']['fields']['collect_data_button_name'] = [
            'type'              => 'text',
            'label'             => esc_html__( 'Button name', 'helper' ),
            'description'       => esc_html__( 'Set button name', 'helper' ),
            'default'           => esc_html__( 'Provide info about yourself!', 'helper' ),
        ];

        $tabs['collect_data']['fields']['save_user_data'] = [
            'type'              => 'switcher',
            'label'             => esc_html__( 'Save to database', 'helper' ),
            'placeholder'       => esc_html__( 'Save to database', 'helper' ),
            'description'       => esc_html__( 'Collects data that user provided in chat bot to database', 'helper' ),
            'default'           => 'on',
        ];

        $tabs['collect_data']['fields']['send_user_data_email'] = [
            'type'              => 'switcher',
            'label'             => esc_html__( 'Send to email', 'helper' ),
            'placeholder'       => esc_html__( 'Send to email', 'helper' ),
            'description'       => esc_html__( 'Sends to email data that user provided', 'helper' ),
            'default'           => 'off',
        ];

        $tabs['collect_data']['fields']['send_to_email'] = [
            'type'              => 'text',
            'label'             => esc_html__( 'Recipient email', 'helper' ),
            'description'       => esc_html__( 'Set recipient email to get user data from the chatbot', 'helper' ),
            'default'           => '',
        ];

        $tabs['collect_data']['fields'][ 'show_acceptance_checkbox' ] = [
            'type'              => 'select',
            'label'             => esc_html__( 'Acceptance checkbox', 'helper' ),
            'placeholder'       => esc_html__( 'Acceptance checkbox', 'helper' ),
            'description'       => esc_html__( 'Select when to show acceptance checkbox. If chosen save to localstorage it will be saved in localstorage with key name mdpAcceptedCollectData', 'helper' ),
            'default'           => 'none',
            'options'           => [
                'none' => esc_html__( 'Do not add', 'helper' ),
                'localstorage' => esc_html__( 'Show one time and save to localstorage', 'helper' ),
                'session' => esc_html__( 'Show on every new session', 'helper' ),
            ],
        ];

        $tabs['collect_data']['fields'][ 'acceptance_text' ] = [
            'type'              => 'editor',
            'label'             => esc_html__( 'Acceptance checkbox text', 'helper' ),
            'description'       => esc_html__( 'Set your acceptance checkbox text', 'helper' ),
            'default'           => esc_html__( 'I acknowledge and agree to the terms of service and privacy policy.', 'helper' ),
            'attr'              => [ 'textarea_rows' => '' ]
        ];

        $tabs['collect_data']['fields'][ 'confirmation_terms_text' ] = [
            'type'              => 'textarea',
            'label'             => esc_html__( 'Acceptance checkbox instructions message', 'helper' ),
            'description'       => esc_html__( 'Message that asks user to click on the checkbox with terms of service and privacy policy', 'helper' ),
            'default'           => esc_html__( 'Before proceeding, please review and accept our terms and conditions by checking the box below.', 'helper' ),
            'attr'              => []
        ];

        $tabs['collect_data']['fields']['enable_google_analytics'] = [
            'type'              => 'switcher',
            'label'             => esc_html__( 'Enable Google Analytics', 'helper' ),
            'placeholder'       => esc_html__( 'Enable Google Analytics', 'helper' ),
            'description'       => esc_html__( 'Enables Google Analytics. Sends generate_lead event with event_label "Helper collect data" to Google Analytics on successful collect data.', 'helper' ),
            'default'           => 'off',
        ];

        $tabs['collect_data']['fields']['google_tracking_id'] = [
            'type'              => 'text',
            'label'             => esc_html__( 'Tracking ID', 'helper' ),
            'description'       => esc_html__( 'Set Google Analytics tracking ID.', 'helper' ),
            'default'           => ''
        ];

        $tabs['collect_data']['fields']['do_not_add_google_analytics_script'] = [
            'type'              => 'switcher',
            'label'             => esc_html__( 'Do not add Google Analytics script', 'helper' ),
            'placeholder'       => esc_html__( 'Do not add Google Analytics script', 'helper' ),
            'description'       => esc_html__( 'Enable only if you already added Google Analytics script on your website.', 'helper' ),
            'default'           => 'off',
        ];

        $quantity = 20;
        for ( $i = 0; $i < $quantity; $i++ ) {
            $tabs['collect_data']['fields'][ 'divider_' . $this::$divider ] = ['type' => 'divider' ];
            $this::$divider++;


            $tabs['collect_data']['fields'][ 'field_name_' . $i ] = [
                'type'              => 'text',
                'label'             => esc_html__( 'Field name', 'helper' ),
                'description'       => esc_html__(
                    'The field name which will be displayed in email or in user data record',
                    'helper'
                ),
                'attr'              => [
                    'class'     => 'mdp-helper-collect-data-bot-message',
                ],
                'default'           => '',
            ];

            $tabs['collect_data']['fields'][ 'collect_data_field_validation_' . $i ] = [
                'type'              => 'select',
                'label'             => esc_html__( 'Field validation', 'helper' ),
                'placeholder'       => esc_html__( 'Field validation', 'helper' ),
                'description'       => esc_html__( 'Set field validation', 'helper' ),
                'default'           => 'none',
                'options'           => [
                    'none' => esc_html__( 'None', 'helper' ),
                    'email' => esc_html__( 'Email', 'helper' ),
                    'number' => esc_html__( 'Number', 'helper' ),
                    'custom' => esc_html__( 'Custom', 'helper' ),
                ],
            ];

            $tabs['collect_data']['fields']['collect_data_number_min_'. $i] = [
                'type'              => 'text',
                'label'             => esc_html__( 'Min number', 'helper' ),
                'placeholder'       => esc_html__( 'Min number', 'helper' ),
                'description'       => esc_html__( 'Enter min accepted number', 'helper' ),
                'default'           => '',
                'attr' => [
                    'class' => 'mdp-helper-collect-data-fields-' . $i . ' mdp-helper-validation-' . $i . '-number'
                ]
            ];

            $tabs['collect_data']['fields']['collect_data_number_max_'. $i] = [
                'type'              => 'text',
                'label'             => esc_html__( 'Max number', 'helper' ),
                'placeholder'       => esc_html__( 'Max number', 'helper' ),
                'description'       => esc_html__( 'Enter max accepted number', 'helper' ),
                'default'           => '',
                'attr' => [
                    'class' => 'mdp-helper-collect-data-fields-' . $i . ' mdp-helper-validation-' . $i . '-number'
                ]
            ];

            $tabs['collect_data']['fields']['collect_data_custom_regex_'. $i] = [
                'type'              => 'textarea',
                'label'             => esc_html__( 'Validation regex', 'helper' ),
                'placeholder'       => esc_html__( 'Validation regex', 'helper' ),
                'description'       => esc_html__( 'Enter the regular expression on which the text will be validated', 'helper' ),
                'default'           => '',
                'attr' => [
                    'class' => 'mdp-helper-collect-data-fields-' . $i . ' mdp-helper-validation-' . $i . '-custom'
                ]
            ];

            $tabs['collect_data']['fields']['collect_data_validation_error_'. $i] = [
                'type'              => 'textarea',
                'label'             => esc_html__( 'Validation error message', 'helper' ),
                'placeholder'       => esc_html__( 'Validation error message', 'helper' ),
                'description'       => esc_html__( 'Set validation error message', 'helper' ),
                'default'           => '',
                'attr' => [
                    'class' => 'mdp-helper-collect-data-fields-'. $i .' mdp-helper-validation-' . $i . '-custom mdp-helper-validation-' . $i . '-email mdp-helper-validation-' . $i . '-number'
                ]
            ];

            $tabs['collect_data']['fields'][ 'collect_data_bot_message_' . $i ] = [
                'type'              => 'textarea',
                'label'             => esc_html__( 'Bot message', 'helper' ),
                'placeholder'       => esc_html__( 'Bot message', 'helper' ),
                'description'       => esc_html__( 'Set bot message', 'helper' ),
                'default'           => '',
            ];

        }

        /** Set default text */
        $tabs['collect_data']['fields'][ 'field_name_0' ]['default'] = 'name';
        $tabs['collect_data']['fields'][ 'collect_data_bot_message_0' ]['default'] = esc_html__( 'What is your name?', 'helper' );

        $tabs['collect_data']['fields']['message_add' ] = [
            'type'              => 'button',
            'placeholder'       => esc_html__( 'Add bot message', 'helper' ),
            'default'           => '',
            'icon'              => 'add',
            'attr'              => [
                'class'     => 'mdc-button--outlined mdp-add-collect-data',
                'id'        => 'mdp-add-collect-data'
            ]
        ];

        $tabs['collect_data']['fields']['message_remove' ] = [
            'type'              => 'button',
            'placeholder'       => esc_html__( 'Remove bot message', 'helper' ),
            'default'           => '',
            'icon'              => 'remove',
            'attr'              => [
                'class'     => 'mdc-button--outlined mdp-remove-collect-data',
                'id'        => 'mdp-remove-collect-data'
            ]
        ];

        return $this->set_bot_response_messages( $tabs, 'collect_data', false, true, [], [
            'success_message' => esc_html__( 'Thank you!', 'helper' ),
            'error_message' => esc_html__( 'Something went wrong', 'helper' ),
        ] );
    }

    /**
     * Set get user email tab.
     *
     * @return array[]
     * @since 1.0.0
     * @access public
     *
     */
    private function set_get_email_tab( $tabs ): array {
        $offset = 7;
        $tabs = array_slice( $tabs, 0, $offset, true ) +
            ['get_emails' => [
                'enabled'       => true,
                'class'         => TabGeneral::class, // Handler
                'label'         => esc_html__( 'Get emails', 'helper' ),
                'title'         => esc_html__( 'Get emails', 'helper' ),
                'show_title'    => true,
                'icon'          => 'mail', // Icon for tab
                'fields'        => []
            ] ] +
            array_slice( $tabs, $offset, NULL, true );

        $tabs['get_emails']['fields']['get_emails_button_name'] = [
            'type'              => 'text',
            'label'             => esc_html__( 'Button name', 'helper' ),
            'description'       => esc_html__( 'Set button name', 'helper' ),
            'default'           => esc_html__( 'Send us email', 'helper' ),
        ];

        $tabs['get_emails']['fields']['send_to_emails'] = [
            'type'              => 'textarea',
            'label'             => esc_html__( 'Recipient email', 'helper' ),
            'description'       => esc_html__( 'Set recipient email addresses separated with coma', 'helper' ),
            'default'           => '',
        ];

        $quantity = 20;
        for ( $i = 0; $i < $quantity; $i++ ) {
            $tabs['get_emails']['fields'][ 'divider_' . $this::$divider ] = ['type' => 'divider' ];
            $this::$divider++;


            $tabs['get_emails']['fields'][ 'get_emails_ask_user_email_bot_message_' . $i ] = [
                'type'              => 'text',
                'label'             => esc_html__( 'Ask user email', 'helper' ),
                'description'       => esc_html__(
                    'Ask user email address for example "Provide your email address"',
                 'helper'
                ),
                'attr'              => [
                    'class'     => 'mdp-helper-get-emails-bot-message',
                ],
                'default'           => '',
            ];

            $tabs['get_emails']['fields'][ 'get_emails_ask_user_message_bot_message_' . $i ] = [
                'type'              => 'text',
                'label'             => esc_html__( 'Ask user message', 'helper' ),
                'description'       => esc_html__(
                    'Ask user message',
                    'helper'
                ),
                'default'           => '',
            ];


        }

        /** Set default email messages */
        $tabs['get_emails']['fields'][ 'get_emails_ask_user_email_bot_message_0' ]['default'] = esc_html__( 'Provide your email', 'helper' );
        $tabs['get_emails']['fields'][ 'get_emails_ask_user_message_bot_message_0' ]['default'] = esc_html__( 'Provide your message', 'helper' );

        $tabs['get_emails']['fields']['email_message_add' ] = [
            'type'              => 'button',
            'placeholder'       => esc_html__( 'Add bot message', 'helper' ),
            'default'           => '',
            'icon'              => 'add',
            'attr'              => [
                'class'     => 'mdc-button--outlined mdp-add-get-emails',
                'id'        => 'mdp-add-get-emails'
            ]
        ];

        $tabs['get_emails']['fields']['email_message_remove' ] = [
            'type'              => 'button',
            'placeholder'       => esc_html__( 'Remove bot message', 'helper' ),
            'default'           => '',
            'icon'              => 'remove',
            'attr'              => [
                'class'     => 'mdc-button--outlined mdp-remove-get-emails',
                'id'        => 'mdp-remove-get-emails'
            ]
        ];

        return $this->set_bot_response_messages( $tabs, 'get_emails', false, true, [], [
            'success_message' => 'Thank you!',
            'error_message' => 'Something went wrong'
        ] );
    }

	/**
	 * Add tabs.
	 * @return void
	 */
	private function add_tabs() {

		Tab::add_settings_tab(
			'footer',
			1,
			'video_label',
			esc_html__( 'Chat Footer', 'helper' ),
			esc_html__( 'Chat Footer settings', 'helper' )
		);

		Tab::add_settings_tab(
			'form',
			1,
			'send',
			esc_html__( 'Response Form', 'helper' ),
			esc_html__( 'Response Form Settings', 'helper' )
		);

		Tab::add_settings_tab(
			'message',
			1,
			'chat_bubble',
			esc_html__( 'Chat Bubble', 'helper' ),
			esc_html__( 'Chat Bubble settings', 'helper' )
		);

		Tab::add_settings_tab(
			'avatar',
			1,
			'person_2',
			esc_html__( 'Avatar', 'helper' ),
			esc_html__( 'User and Bot Avatar settings', 'helper' )
		);

		Tab::add_settings_tab(
			'chat_container',
			1,
			'speaker_notes',
			esc_html__( 'Chat Container', 'helper' ),
			esc_html__( 'Chat Container settings', 'helper' )
		);

		Tab::add_settings_tab(
			'toolbar',
			1,
			'handyman',
			esc_html__( 'Toolbar', 'helper' ),
			esc_html__( 'Toolbar Settings', 'helper' )
		);

		Tab::add_settings_tab(
			'header',
			1,
			'video_label',
			esc_html__( 'Chat Header', 'helper' ),
			esc_html__( 'Chat Header settings', 'helper' )
		);

		Tab::add_settings_tab(
			'popup',
			1,
			'tab_unselected',
			esc_html__( 'Chat Popup', 'helper' ),
			esc_html__( 'Chat Popup Settings', 'helper' )
		);

		Tab::add_settings_tab(
			'float_button',
			2,
			'ads_click',
			esc_html__( 'Float Button', 'helper' ),
			esc_html__( 'Float Button Settings', 'helper' )
		);

		Tab::add_settings_tab(
			'behavior',
			1,
			'psychology',
			esc_html__( 'Bot behavior', 'helper' ),
			esc_html__( 'Bot behavior', 'helper' )
		);

	}

	/**
	 * Add controls.
	 *
	 * @return void
	 */
	private function add_controls() {

		TabGlobal::controls();

		TabFloatButton::controls();
		TabPopup::controls();
		TabAvatar::controls();
		TabHeader::controls();
		TabChatContainer::controls();
		TabMessage::controls();
		TabFooter::controls();
		TabForm::controls();
		TabBehavior::controls();
		TabToolbar::controls();

	}

    /**
     * Prepare plugin settings by modifying the default one.
     *
     * @since 1.0.0
     * @access public
     *
     * @return void
     **/
    public function prepare_settings() {

		$this->add_tabs();
		$this->add_controls();

        /** Get default plugin settings. */

	    $options = Settings::get_instance()->options;
        $tabs = Plugin::get_tabs();

		// Modify status tab requirements
	    $tabs[ 'status' ][ 'reports' ][ 'server' ][ 'bcmath_installed' ] = false;
	    $tabs[ 'status' ][ 'reports' ][ 'server' ][ 'xml_installed' ] = false;
	    $tabs[ 'status'] [ 'reports' ][ 'server' ][ 'dom_installed' ] = false;
		$tabs[ 'status' ][ 'reports' ][ 'server' ][ 'allow_url_fopen' ] = false;

        if ( in_array( 'ai', $options['bot_features'] ) ) {
            /** Set AI bot settings tab */
            $tabs = $this->ai_bot_tab( $tabs );
            $tabs = $this->personalities_tab( $tabs );
            $this->refresh_settings( $tabs );
            $tabs = $this->ai_bot_tab( $tabs );
            $tabs = $this->personalities_tab( $tabs );
        }

        if ( in_array( 'faq', $options['bot_features'] ) ) {
            /** Set FAQ tab */
            $tabs = $this->set_faq_tab( $tabs );
            $this->refresh_settings( $tabs );
            $tabs = $this->set_faq_tab( $tabs );
        }

        if ( in_array( 'collect_data', $options['bot_features'] ) ) {
            /** Set collect user data tab */
            $tabs = $this->set_collect_data_tab( $tabs );
            $this->refresh_settings( $tabs );
            $tabs = $this->set_collect_data_tab( $tabs );
        }

        if ( in_array( 'get_user_email', $options['bot_features'] ) ) {
            /** Set get user email tab */
            $tabs = $this->set_get_email_tab( $tabs );
            $this->refresh_settings( $tabs );
            $tabs = $this->set_get_email_tab( $tabs );
        }

        /** Set updated tabs. */
        Plugin::set_tabs( $tabs );

        /** Refresh settings. */
        Settings::get_instance()->get_options();

    }

	/**
	 * Main Settings Instance.
	 * Insures that only one instance of Settings exists in memory at any one time.
	 *
	 * @static
     * @since 1.0.0
     * @access public
     *
	 * @return Config
	 **/
	public static function get_instance(): Config {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

			self::$instance = new self;

		}

		return self::$instance;

	}

}

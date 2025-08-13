<?php
namespace De_Sina_Extension;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Css_Filter;

/**
 * De_Reveal_Animation_Controls Class for extends controls
 *
 * @since 3.0.1
 */
class De_Reveal_Animation_Controls{
	/**
	 * Instance
	 *
	 * @since 3.1.13
	 * @var De_Reveal_Animation_Controls The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 3.1.13
	 * @return De_Reveal_Animation_Controls An Instance of the class.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		add_action('elementor/element/common/_section_style/after_section_end', [$this, 'register_controls']);
		// add_action('elementor/element/after_section_end', [$this, 'register_controls']);
		// add_action('elementor/element/column/section_advanced/after_section_end', [$this, 'column_register_controls']);
		// add_action('elementor/element/section/section_advanced/after_section_end', [$this, 'section_register_controls']);
		add_action('elementor/element/section/section_advanced/after_section_end', [$this, 'section_register_controls']);
		add_action('elementor/element/container/section_layout/after_section_end', [$this, 'section_register_controls']);

		add_filter( 'elementor/widget/render_content', [$this, 'render_template_content'], 10, 2 );
		add_filter( 'elementor/widget/print_template', [$this, 'update_template_content'], 10, 2 );
	}

	public function render_template_content($template,$widget) {
		return $template;
	}

	public function update_template_content($template,$widget) {
		return $template;
	}

	public function register_controls($elems) {
		$elems->start_controls_section(
			'de_reveal_animation_section',
			[
				'label' => __( 'De Reveal Animation', 'dethemekit-for-elementor' ),
				'tab' => Controls_Manager::TAB_ADVANCED,
			]
		);

		$elems->add_control(
			'de_reveal_animation',
			[
				'label' => '<strong>'.esc_html__( 'De Reveal Animation', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::SWITCHER,
				'prefix_class' => 'de_reveal_animation_',
			]
		);

		$elems->add_control(
			'de_reveal_default_preview',
			[
				'type' => Controls_Manager::BUTTON,
				'button_type' => 'default',
				'text' => esc_html__( 'Preview', 'dethemekit-for-elementor' ),
				'show_label' => false,
				'event' => 'RunPreviewDefault',
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_animation_type' => 'default' ],
			]
		);

		$elems->add_control(
			'de_reveal_curtain_preview',
			[
				'type' => Controls_Manager::BUTTON,
				'button_type' => 'default',
				'text' => esc_html__( 'Preview', 'dethemekit-for-elementor' ),
				'show_label' => false,
				'event' => 'RunPreviewCurtain',
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_animation_type' => 'blockcurtain' ],
			]
		);

		$elems->add_control(
			'de_reveal_letter_preview',
			[
				'type' => Controls_Manager::BUTTON,
				'button_type' => 'default',
				'text' => esc_html__( 'Preview', 'dethemekit-for-elementor' ),
				'show_label' => false,
				'event' => 'RunPreviewLetter',
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_animation_type' => 'letter' ],
			]
		);

		$elems->add_control(
			'de_reveal_animation_type',
			[
				'label' => '<strong>'.esc_html__( 'Animation Type', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::SELECT,
				'options' => [
					'default' => esc_html__( 'Block', 'dethemekit-for-elementor' ),
					'blockcurtain' => esc_html__( 'Curtain', 'dethemekit-for-elementor' ),
					'letter' => esc_html__( 'Letter', 'dethemekit-for-elementor' ),
				],
				'default' => 'default',
				'prefix_class' => 'de_reveal_animation_type_',
				'condition' => [ 'de_reveal_animation' => 'yes' ],
			]
		);

		$elems->add_control(
			'de_reveal_animation_style',
			[
				'label' => '<strong>'.esc_html__( 'Animation Style', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::SELECT,
				'options' => [
					'fu' => esc_html__( 'Fade Up', 'dethemekit-for-elementor' ),
					'fd' => esc_html__( 'Fade Down', 'dethemekit-for-elementor' ),
					'fl' => esc_html__( 'Fade Left', 'dethemekit-for-elementor' ),
					'fr' => esc_html__( 'Fade Right', 'dethemekit-for-elementor' ),
					'rotate' => esc_html__( 'Rotate', 'dethemekit-for-elementor' ),
					'scale' => esc_html__( 'Scale', 'dethemekit-for-elementor' ),
				],
				'default' => 'fu',
				'prefix_class' => 'de_reveal_animation_style_',
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_animation_type' => 'default'  ],
			]
		);

		$elems->add_control(
			'de_reveal_curtain_direction',
			[
				'label' => '<strong>'.esc_html__( 'Curtain Direction', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::SELECT,
				'options' => [
					'lr' => esc_html__( 'Left to Right', 'dethemekit-for-elementor' ),
					'rl' => esc_html__( 'Right to Left', 'dethemekit-for-elementor' ),
					'tb' => esc_html__( 'Top to Bottom', 'dethemekit-for-elementor' ),
					'bt' => esc_html__( 'Bottom to Top', 'dethemekit-for-elementor' ),
				],
				'default' => 'lr',
				'prefix_class' => 'de_reveal_curtain_direction_',
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_animation_type' => 'blockcurtain' ],
			]
		);

		$elems->add_control(
			'de_reveal_curtain_color',
			[
				'label' => '<strong>'.esc_html__( 'Curtain Color', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .block-revealer__element' => 'background-color: {{VALUE}};',
				],
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_animation_type' => 'blockcurtain' ],
			]
		);

		$elems->add_control(
			'de_reveal_curtain_delay',
			[
				'label' => '<strong>'.esc_html__( 'Curtain Delay (ms)', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_reveal_curtain_delay_',
				'default' => '0',
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_animation_type' => 'blockcurtain' ],
			]
		);

		$elems->add_control(
			'de_reveal_letter_effects',
			[
				'label' => '<strong>'.esc_html__( 'Letter Effects', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::SELECT,
				'options' => [
					'fx1' => esc_html__( 'Effect 1', 'dethemekit-for-elementor' ),
					'fx2' => esc_html__( 'Effect 2', 'dethemekit-for-elementor' ),
					'fx3' => esc_html__( 'Effect 3', 'dethemekit-for-elementor' ),
					'fx4' => esc_html__( 'Effect 4', 'dethemekit-for-elementor' ),
					'fx5' => esc_html__( 'Effect 5', 'dethemekit-for-elementor' ),
					'fx6' => esc_html__( 'Effect 6', 'dethemekit-for-elementor' ),
					'fx7' => esc_html__( 'Effect 7', 'dethemekit-for-elementor' ),
					'fx8' => esc_html__( 'Effect 8', 'dethemekit-for-elementor' ),
					'fx9' => esc_html__( 'Effect 9', 'dethemekit-for-elementor' ),
					'fx10' => esc_html__( 'Effect 10', 'dethemekit-for-elementor' ),
					'fx11' => esc_html__( 'Effect 11', 'dethemekit-for-elementor' ),
					'fx12' => esc_html__( 'Effect 12', 'dethemekit-for-elementor' ),
					'fx13' => esc_html__( 'Effect 13', 'dethemekit-for-elementor' ),
					'fx14' => esc_html__( 'Effect 14', 'dethemekit-for-elementor' ),
					'fx15' => esc_html__( 'Effect 15', 'dethemekit-for-elementor' ),
					'fx16' => esc_html__( 'Effect 16', 'dethemekit-for-elementor' ),
					'fx17' => esc_html__( 'Effect 17', 'dethemekit-for-elementor' ),
					'fx18' => esc_html__( 'Effect 18', 'dethemekit-for-elementor' ),
				],
				'default' => 'fx1',
				'prefix_class' => 'de_reveal_letter_effects_',
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_animation_type' => 'letter' ],
			]
		);

		$elems->add_control(
			'de_reveal_letter_initial_state',
			[
				'label' => '<strong>'.esc_html__( 'Initial State', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::SELECT,
				'options' => [
                    'hidden' => esc_html__( 'Hidden', 'dethemekit-for-elementor' ),
                    'visible' => esc_html__( 'Visible', 'dethemekit-for-elementor' ),
                ],
				'default' => 'hidden',
				'prefix_class' => 'de_reveal_letter_initial_state_',
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_animation_type' => 'letter' ],
			]
		);

		$elems->add_control(
			'de_reveal_easing',
			[
				'label' => '<strong>'.esc_html__( 'Reveal Easing', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::SELECT,
				'options' => [
					'linear' => esc_html__( 'linear', 'dethemekit-for-elementor' ),
					'easeInQuad' => esc_html__( 'easeInQuad', 'dethemekit-for-elementor' ),
					'easeOutQuad' => esc_html__( 'easeOutQuad', 'dethemekit-for-elementor' ),
					'easeInOutQuad' => esc_html__( 'easeInOutQuad', 'dethemekit-for-elementor' ),
					'easeInCubic' => esc_html__( 'easeInCubic', 'dethemekit-for-elementor' ),
					'easeOutCubic' => esc_html__( 'easeOutCubic', 'dethemekit-for-elementor' ),
					'easeInOutCubic' => esc_html__( 'easeInOutCubic', 'dethemekit-for-elementor' ),
					'easeInQuart' => esc_html__( 'easeInQuart', 'dethemekit-for-elementor' ),
					'easeOutQuart' => esc_html__( 'easeOutQuart', 'dethemekit-for-elementor' ),
					'easeInOutQuart' => esc_html__( 'easeInOutQuart', 'dethemekit-for-elementor' ),
					'easeInQuint' => esc_html__( 'easeInQuint', 'dethemekit-for-elementor' ),
					'easeOutQuint' => esc_html__( 'easeOutQuint', 'dethemekit-for-elementor' ),
					'easeInOutQuint' => esc_html__( 'easeInOutQuint', 'dethemekit-for-elementor' ),
					'easeInExpo' => esc_html__( 'easeInExpo', 'dethemekit-for-elementor' ),
					'easeOutExpo' => esc_html__( 'easeOutExpo', 'dethemekit-for-elementor' ),
					'easeInOutExpo' => esc_html__( 'easeInOutExpo', 'dethemekit-for-elementor' ),
					'easeInSine' => esc_html__( 'easeInSine', 'dethemekit-for-elementor' ),
					'easeOutSine' => esc_html__( 'easeOutSine', 'dethemekit-for-elementor' ),
					'easeInOutSine' => esc_html__( 'easeInOutSine', 'dethemekit-for-elementor' ),
					'easeInCirc' => esc_html__( 'easeInCirc', 'dethemekit-for-elementor' ),
					'easeOutCirc' => esc_html__( 'easeOutCirc', 'dethemekit-for-elementor' ),
					'easeInOutCirc' => esc_html__( 'easeInOutCirc', 'dethemekit-for-elementor' ),
					'easeInElastic' => esc_html__( 'easeInElastic', 'dethemekit-for-elementor' ),
					'easeOutElastic' => esc_html__( 'easeOutElastic', 'dethemekit-for-elementor' ),
					'easeInOutElastic' => esc_html__( 'easeInOutElastic', 'dethemekit-for-elementor' ),
					'easeInBack' => esc_html__( 'easeInBack', 'dethemekit-for-elementor' ),
					'easeOutBack' => esc_html__( 'easeOutBack', 'dethemekit-for-elementor' ),
					'easeInOutBack' => esc_html__( 'easeInOutBack', 'dethemekit-for-elementor' ),
					'easeInBounce' => esc_html__( 'easeInBounce', 'dethemekit-for-elementor' ),
					'easeOutBounce' => esc_html__( 'easeOutBounce', 'dethemekit-for-elementor' ),
					'easeInOutBounce' => esc_html__( 'easeInOutBounce', 'dethemekit-for-elementor' ),
				],
				'default' => 'linear',
				'prefix_class' => 'de_reveal_easing_',
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_animation_type!' => 'letter' ],
			]
		);

		$elems->add_control(
			'de_reveal_default_rotation',
			[
				'label' => '<strong>'.esc_html__( 'Rotation Degree', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_reveal_default_rotation_',
				'default' => '0',
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_animation_type' => 'default', 'de_reveal_animation_style' => 'rotate' ],
			]
		);

		$elems->add_control(
			'de_reveal_default_scale',
			[
				'label' => '<strong>'.esc_html__( 'Scale', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_reveal_default_scale_',
				'default' => '1',
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_animation_type' => 'default', 'de_reveal_animation_style' => 'scale' ],
			]
		);

		$elems->add_control(
			'de_reveal_distance',
			[
				'label' => '<strong>'.esc_html__( 'Distance (px)', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_reveal_distance_',
				'default' => '200',
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_animation_type' => 'default', 'de_reveal_animation_style' => ['fu','fd','fl','fr'] ],
			]
		);

		$elems->add_control(
			'de_reveal_default_delay',
			[
				'label' => '<strong>'.esc_html__( 'Delay (ms)', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_reveal_default_delay_',
				'default' => '0',
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_animation_type' => 'default' ],
			]
		);

		$elems->add_control(
			'de_reveal_duration',
			[
				'label' => '<strong>'.esc_html__( 'Reveal Duration', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_reveal_duration_',
				'default' => '1000',
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_animation_type!' => 'letter' ],
			]
		);

		$elems->add_control(
			'de_reveal_direction',
			[
				'label' => '<strong>'.esc_html__( 'Direction', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::SELECT,
				'options' => [
					'normal' => esc_html__( 'Normal', 'dethemekit-for-elementor' ),
					'reverse' => esc_html__( 'Reverse', 'dethemekit-for-elementor' ),
					'alternate' => esc_html__( 'Alternate', 'dethemekit-for-elementor' ),
				],
				'default' => 'normal',
				'prefix_class' => 'de_reveal_direction_',
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_animation_type' => 'default' ],
			]
		);

		$elems->add_control(
			'de_reveal_loop',
			[
				'label' => '<strong>'.esc_html__( 'Loop', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::SELECT,
				'options' => [
					'custom' => esc_html__( 'Custom', 'dethemekit-for-elementor' ),
					'infinite' => esc_html__( 'Infinite', 'dethemekit-for-elementor' ),
				],
				'default' => 'custom',
				'prefix_class' => 'de_reveal_loop_',
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_animation_type' => 'default' ],
			]
		);

		$elems->add_control(
			'de_reveal_custom_loop',
			[
				'label' => '<strong>'.esc_html__( 'Number of loops', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_reveal_custom_loop_',
				'default' => 1,
				'min' => 1,
				'step' => 1,
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_animation_type' => 'default', 'de_reveal_loop' => 'custom' ],
			]
		);

		$elems->add_control(
			'de_reveal_start',
			[
				'label' => '<strong>'.esc_html__( 'Start animate in view (0-1)', 'dethemekit-for-elementor' ).'</strong>',
				'description' => esc_html__( 'This determines the percentage of the trigger that must be visible before the animation is triggered', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_reveal_start_',
				'default' => 0.5,
                'min' => 0.0,
                'max' => 1.0,
                'step' => 0.1,
				'condition' => [ 'de_reveal_animation' => 'yes' ],
			]
		);

		$elems->add_control(
			'de_reveal_animate_in_viewport',
			[
				'label' => '<strong>'.esc_html__( 'Animate in viewport', 'dethemekit-for-elementor' ).'</strong>',
				'description' => esc_html__( 'This determines how the element\'s animation will run each time the element in viewport', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'runonce' => esc_html__( 'Run Once', 'dethemekit-for-elementor' ),
					'alwaysrun' => esc_html__( 'Always Run', 'dethemekit-for-elementor' ),
				],
				'default' => 'runonce',
				'prefix_class' => 'de_reveal_animate_in_viewport_',
				'condition' => [ 'de_reveal_animation' => 'yes' ],
			]
		);

		$elems->add_control(
			'de_reveal_on_desktop',
			[
				'label' => esc_html__( 'Run Animation on Desktop', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'prefix_class' => 'de_reveal_on_desktop_',
				'condition' => [ 'de_reveal_animation' => 'yes' ],
			]
		);

		$elems->add_control(
			'de_reveal_on_tablet',
			[
				'label' => esc_html__( 'Run Animation on Tablet', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'prefix_class' => 'de_reveal_on_tablet_',
				'condition' => [ 'de_reveal_animation' => 'yes' ],
			]
		);

		$elems->add_control(
			'de_reveal_on_mobile',
			[
				'label' => esc_html__( 'Run Animation on Mobile', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'prefix_class' => 'de_reveal_on_mobile_',
				'condition' => [ 'de_reveal_animation' => 'yes' ],
			]
		);

		$elems->end_controls_section();
	}

	public function section_register_controls($elems) {
		$elems->start_controls_section(
			'de_reveal_animation_section',
			[
				'label' => __( 'De Reveal Animation', 'dethemekit-for-elementor' ),
				'tab' => Controls_Manager::TAB_ADVANCED,
			]
		);

		$elems->add_control(
			'de_reveal_animation',
			[
				'label' => '<strong>'.esc_html__( 'De Reveal Animation', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::SWITCHER,
				'prefix_class' => 'de_reveal_animation_',
			]
		);

		$elems->add_control(
			'de_reveal_default_preview',
			[
				'type' => Controls_Manager::BUTTON,
				'button_type' => 'default',
				'text' => esc_html__( 'Preview', 'dethemekit-for-elementor' ),
				'show_label' => false,
				'event' => 'RunPreviewDefault',
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_animation_type' => 'default' ],
			]
		);

		$elems->add_control(
			'de_reveal_curtain_preview',
			[
				'type' => Controls_Manager::BUTTON,
				'button_type' => 'default',
				'text' => esc_html__( 'Preview', 'dethemekit-for-elementor' ),
				'show_label' => false,
				'event' => 'RunPreviewCurtain',
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_animation_type' => 'blockcurtain' ],
			]
		);

		$elems->add_control(
			'de_reveal_letter_preview',
			[
				'type' => Controls_Manager::BUTTON,
				'button_type' => 'default',
				'text' => esc_html__( 'Preview', 'dethemekit-for-elementor' ),
				'show_label' => false,
				'event' => 'RunPreviewLetter',
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_animation_type' => 'letter' ],
			]
		);

		$elems->add_control(
			'de_reveal_animation_type',
			[
				'label' => '<strong>'.esc_html__( 'Animation Type', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::SELECT,
				'options' => [
					'default' => esc_html__( 'Block', 'dethemekit-for-elementor' ),
					'blockcurtain' => esc_html__( 'Curtain', 'dethemekit-for-elementor' ),
					'letter' => esc_html__( 'Letter', 'dethemekit-for-elementor' ),
				],
				'default' => 'default',
				'prefix_class' => 'de_reveal_animation_type_',
				'condition' => [ 'de_reveal_animation' => 'yes' ],
			]
		);

		$elems->add_control(
			'de_reveal_animation_style',
			[
				'label' => '<strong>'.esc_html__( 'Animation Style', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::SELECT,
				'options' => [
					'fu' => esc_html__( 'Fade Up', 'dethemekit-for-elementor' ),
					'fd' => esc_html__( 'Fade Down', 'dethemekit-for-elementor' ),
					'fl' => esc_html__( 'Fade Left', 'dethemekit-for-elementor' ),
					'fr' => esc_html__( 'Fade Right', 'dethemekit-for-elementor' ),
					'rotate' => esc_html__( 'Rotate', 'dethemekit-for-elementor' ),
					'scale' => esc_html__( 'Scale', 'dethemekit-for-elementor' ),
				],
				'default' => 'fu',
				'prefix_class' => 'de_reveal_animation_style_',
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_animation_type' => 'default'  ],
			]
		);

		$elems->add_control(
			'de_reveal_curtain_direction',
			[
				'label' => '<strong>'.esc_html__( 'Curtain Direction', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::SELECT,
				'options' => [
					'lr' => esc_html__( 'Left to Right', 'dethemekit-for-elementor' ),
					'rl' => esc_html__( 'Right to Left', 'dethemekit-for-elementor' ),
					'tb' => esc_html__( 'Top to Bottom', 'dethemekit-for-elementor' ),
					'bt' => esc_html__( 'Bottom to Top', 'dethemekit-for-elementor' ),
				],
				'default' => 'lr',
				'prefix_class' => 'de_reveal_curtain_direction_',
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_animation_type' => 'blockcurtain' ],
			]
		);

		$elems->add_control(
			'de_reveal_curtain_color',
			[
				'label' => '<strong>'.esc_html__( 'Curtain Color', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{SELECTOR}} {{WRAPPER}} .block-revealer__element' => 'background-color: {{VALUE}};',
				],
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_animation_type' => 'blockcurtain' ],
			]
		);

		$elems->add_control(
			'de_reveal_curtain_delay',
			[
				'label' => '<strong>'.esc_html__( 'Curtain Delay (ms)', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_reveal_curtain_delay_',
				'default' => '0',
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_animation_type' => 'blockcurtain' ],
			]
		);

		$elems->add_control(
			'de_reveal_easing',
			[
				'label' => '<strong>'.esc_html__( 'Reveal Easing', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::SELECT,
				'options' => [
					'linear' => esc_html__( 'linear', 'dethemekit-for-elementor' ),
					'easeInQuad' => esc_html__( 'easeInQuad', 'dethemekit-for-elementor' ),
					'easeOutQuad' => esc_html__( 'easeOutQuad', 'dethemekit-for-elementor' ),
					'easeInOutQuad' => esc_html__( 'easeInOutQuad', 'dethemekit-for-elementor' ),
					'easeInCubic' => esc_html__( 'easeInCubic', 'dethemekit-for-elementor' ),
					'easeOutCubic' => esc_html__( 'easeOutCubic', 'dethemekit-for-elementor' ),
					'easeInOutCubic' => esc_html__( 'easeInOutCubic', 'dethemekit-for-elementor' ),
					'easeInQuart' => esc_html__( 'easeInQuart', 'dethemekit-for-elementor' ),
					'easeOutQuart' => esc_html__( 'easeOutQuart', 'dethemekit-for-elementor' ),
					'easeInOutQuart' => esc_html__( 'easeInOutQuart', 'dethemekit-for-elementor' ),
					'easeInQuint' => esc_html__( 'easeInQuint', 'dethemekit-for-elementor' ),
					'easeOutQuint' => esc_html__( 'easeOutQuint', 'dethemekit-for-elementor' ),
					'easeInOutQuint' => esc_html__( 'easeInOutQuint', 'dethemekit-for-elementor' ),
					'easeInExpo' => esc_html__( 'easeInExpo', 'dethemekit-for-elementor' ),
					'easeOutExpo' => esc_html__( 'easeOutExpo', 'dethemekit-for-elementor' ),
					'easeInOutExpo' => esc_html__( 'easeInOutExpo', 'dethemekit-for-elementor' ),
					'easeInSine' => esc_html__( 'easeInSine', 'dethemekit-for-elementor' ),
					'easeOutSine' => esc_html__( 'easeOutSine', 'dethemekit-for-elementor' ),
					'easeInOutSine' => esc_html__( 'easeInOutSine', 'dethemekit-for-elementor' ),
					'easeInCirc' => esc_html__( 'easeInCirc', 'dethemekit-for-elementor' ),
					'easeOutCirc' => esc_html__( 'easeOutCirc', 'dethemekit-for-elementor' ),
					'easeInOutCirc' => esc_html__( 'easeInOutCirc', 'dethemekit-for-elementor' ),
					'easeInElastic' => esc_html__( 'easeInElastic', 'dethemekit-for-elementor' ),
					'easeOutElastic' => esc_html__( 'easeOutElastic', 'dethemekit-for-elementor' ),
					'easeInOutElastic' => esc_html__( 'easeInOutElastic', 'dethemekit-for-elementor' ),
					'easeInBack' => esc_html__( 'easeInBack', 'dethemekit-for-elementor' ),
					'easeOutBack' => esc_html__( 'easeOutBack', 'dethemekit-for-elementor' ),
					'easeInOutBack' => esc_html__( 'easeInOutBack', 'dethemekit-for-elementor' ),
					'easeInBounce' => esc_html__( 'easeInBounce', 'dethemekit-for-elementor' ),
					'easeOutBounce' => esc_html__( 'easeOutBounce', 'dethemekit-for-elementor' ),
					'easeInOutBounce' => esc_html__( 'easeInOutBounce', 'dethemekit-for-elementor' ),					
				],
				'default' => 'linear',
				'prefix_class' => 'de_reveal_easing_',
				'condition' => [ 'de_reveal_animation' => 'yes' ],
			]
		);

		$elems->add_control(
			'de_reveal_default_rotation',
			[
				'label' => '<strong>'.esc_html__( 'Rotation Degree', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_reveal_default_rotation_',
				'default' => '0',
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_animation_type' => 'default', 'de_reveal_animation_style' => 'rotate' ],
			]
		);

		$elems->add_control(
			'de_reveal_default_scale',
			[
				'label' => '<strong>'.esc_html__( 'Scale', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_reveal_default_scale_',
				'default' => '1',
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_animation_type' => 'default', 'de_reveal_animation_style' => 'scale' ],
			]
		);

		$elems->add_control(
			'de_reveal_distance',
			[
				'label' => '<strong>'.esc_html__( 'Distance (px)', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_reveal_distance_',
				'default' => '200',
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_animation_type' => 'default', 'de_reveal_animation_style' => ['fu','fd','fl','fr'] ],
			]
		);

		$elems->add_control(
			'de_reveal_default_delay',
			[
				'label' => '<strong>'.esc_html__( 'Delay (ms)', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_reveal_default_delay_',
				'default' => '0',
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_animation_type' => 'default' ],
			]
		);
		
		$elems->add_control(
			'de_reveal_duration',
			[
				'label' => '<strong>'.esc_html__( 'Reveal Duration', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_reveal_duration_',
				'default' => '1000',
				'condition' => [ 'de_reveal_animation' => 'yes' ],
			]
		);

		$elems->add_control(
			'de_reveal_direction',
			[
				'label' => '<strong>'.esc_html__( 'Direction', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::SELECT,
				'options' => [
					'normal' => esc_html__( 'Normal', 'dethemekit-for-elementor' ),
					'reverse' => esc_html__( 'Reverse', 'dethemekit-for-elementor' ),
					'alternate' => esc_html__( 'Alternate', 'dethemekit-for-elementor' ),
				],
				'default' => 'normal',
				'prefix_class' => 'de_reveal_direction_',
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_animation_type' => 'default' ],
			]
		);

		$elems->add_control(
			'de_reveal_loop',
			[
				'label' => '<strong>'.esc_html__( 'Loop', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::SELECT,
				'options' => [
					'custom' => esc_html__( 'Custom', 'dethemekit-for-elementor' ),
					'infinite' => esc_html__( 'Infinite', 'dethemekit-for-elementor' ),
				],
				'default' => 'custom',
				'prefix_class' => 'de_reveal_loop_',
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_animation_type' => 'default' ],
			]
		);

		$elems->add_control(
			'de_reveal_custom_loop',
			[
				'label' => '<strong>'.esc_html__( 'Number of loops', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_reveal_custom_loop_',
				'default' => '1',
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_animation_type' => 'default', 'de_reveal_loop' => 'custom' ],
			]
		);

		$elems->add_control(
			'de_reveal_start',
			[
				'label' => '<strong>'.esc_html__( 'Start animate in view (0-1)', 'dethemekit-for-elementor' ).'</strong>',
				'description' => esc_html__( 'This determines the percentage of the trigger that must be visible before the animation is triggered', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_reveal_start_',
				'default' => 0.5,
                'min' => 0.1,
                'max' => 1.0,
                'step' => 0.1,
				'condition' => [ 'de_reveal_animation' => 'yes' ],
			]
		);

		$elems->add_control(
			'de_reveal_animate_in_viewport',
			[
				'label' => '<strong>'.esc_html__( 'Animate in viewport', 'dethemekit-for-elementor' ).'</strong>',
				'description' => esc_html__( 'This determines how the element\'s animation will run each time the element in viewport', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'alwaysrun' => esc_html__( 'Always Run', 'dethemekit-for-elementor' ),
					'runonce' => esc_html__( 'Run Once', 'dethemekit-for-elementor' ),
				],
				'default' => 'alwaysrun',
				'prefix_class' => 'de_reveal_animate_in_viewport_',
				'condition' => [ 'de_reveal_animation' => 'yes' ],
			]
		);

		$elems->add_control(
			'de_reveal_stagger',
			[
				'label' => '<strong>'.esc_html__( 'Stagger animate child columns', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::SWITCHER,
				'prefix_class' => 'de_reveal_stagger_',
                'condition' => [ 'de_reveal_animation' => 'yes' ],
			]
		);

		$elems->add_control(
			'de_reveal_stagger_child_delay',
			[
				'label' => '<strong>'.esc_html__( 'Child Element Delay (ms)', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_reveal_stagger_child_delay_',
				'default' => '500',
				'condition' => [ 'de_reveal_animation' => 'yes', 'de_reveal_stagger' => 'yes' ],
			]
		);

		$elems->add_control(
			'de_reveal_on_desktop',
			[
				'label' => esc_html__( 'Run Animation on Desktop', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'prefix_class' => 'de_reveal_on_desktop_',
				'condition' => [ 'de_reveal_animation' => 'yes' ],
			]
		);

		$elems->add_control(
			'de_reveal_on_tablet',
			[
				'label' => esc_html__( 'Run Animation on Tablet', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'prefix_class' => 'de_reveal_on_tablet_',
				'condition' => [ 'de_reveal_animation' => 'yes' ],
			]
		);

		$elems->add_control(
			'de_reveal_on_mobile',
			[
				'label' => esc_html__( 'Run Animation on Mobile', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'prefix_class' => 'de_reveal_on_mobile_',
				'condition' => [ 'de_reveal_animation' => 'yes' ],
			]
		);

		$elems->end_controls_section();
	}

	public function column_register_controls($elems) {
	}
}
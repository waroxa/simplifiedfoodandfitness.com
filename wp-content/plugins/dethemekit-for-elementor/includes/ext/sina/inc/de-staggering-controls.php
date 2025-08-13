<?php
namespace De_Sina_Extension;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Css_Filter;

/**
 * De_Staggering_Controls Class for extends controls
 *
 * @since 3.0.1
 */
class De_Staggering_Controls{
	/**
	 * Instance
	 *
	 * @since 3.1.13
	 * @var De_Staggering_Controls The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 3.1.13
	 * @return De_Staggering_Controls An Instance of the class.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		add_action('elementor/element/common/_section_style/after_section_end', [$this, 'register_controls']);
		add_action('elementor/element/column/section_advanced/after_section_end', [$this, 'column_register_controls']);
		add_action('elementor/element/section/section_advanced/after_section_end', [$this, 'section_register_controls']);

		add_filter( 'elementor/widget/render_content', [$this, 'render_template_content'], 10, 2 );
		add_filter( 'elementor/widget/print_template', [$this, 'update_template_content'], 10, 2 );
	}

	public function render_template_content($template,$widget) {
		// $settings = $widget->get_settings_for_display();

		// $widget->add_render_attribute(
		// 	'module',
		// 	[
		// 		'data-anim' => '',
		// 		'data-threshold' => '0.1'
		// 	]
		// );

		// $widget->add_render_attribute(
		// 	'content',
		// 	[
		// 		'data-animated' => '',
		// 	]
		// );

		// $template = '<div ' . $widget->get_render_attribute_string( 'module' ) . '>' 
		// 	. '<div ' . $widget->get_render_attribute_string( 'content' ) . '>' 
		// 	. $template 
		// 	. '</div></div>';

		// if ( 'image' === $widget->get_name() ) {
		// 	$settings = $widget->get_settings_for_display();
		// 	$template = '<div class="block-revealer__content" style="opacity: 1;">' . $template . '</div><div class="block-revealer__element" style="opacity: 1;"></div>';
		// }

		return $template;
	}

	public function update_template_content($template,$widget) {
		// if ( 'image' === $widget->get_name() ) {
		// 	$template = '<div class="block-revealer__content" style="opacity: 1;">' . $template . '</div><div class="block-revealer__element"></div>';
		// }

		return $template;
	}

	public function register_controls($elems) {
		$elems->start_controls_section(
			'de_staggering_section',
			[
				'label' => __( 'De Staggering', 'dethemekit-for-elementor' ),
				'tab' => Controls_Manager::TAB_ADVANCED,
			]
		);

		$elems->add_control(
			'de_staggering_animation',
			[
				'label' => '<strong>'.esc_html__( 'De Stagger Animation', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::SWITCHER,
				'prefix_class' => 'de_staggering_animation_',
			]
		);

		$elems->add_control(
			'de_staggering_child_initial_state',
			[
				'label' => '<strong>'.esc_html__( 'Initial State', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::SELECT,
				'options' => [
          'inherited' => esc_html__( 'Inherited', 'dethemekit-for-elementor' ),
          'hidden' => esc_html__( 'Hidden', 'dethemekit-for-elementor' ),
          'visible' => esc_html__( 'Visible', 'dethemekit-for-elementor' ),
        ],
				'default' => 'inherited',
				'prefix_class' => 'de_staggering_child_initial_state_',
				'condition' => [ 'de_staggering_animation' => 'yes' ],
			]
		);

		$elems->add_control(
			'de_staggering_animation_mousehover',
			[
				'label' => '<strong>'.esc_html__( 'Animation on Mouse Hover', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::SELECT,
				'options' => [
                    'inherited' => esc_html__( 'inherited', 'dethemekit-for-elementor' ),
                    'none' => esc_html__( 'none', 'dethemekit-for-elementor' ),
                    'backInDown' => esc_html__( 'backInDown', 'dethemekit-for-elementor' ),
                    'backOutDown' => esc_html__( 'backOutDown', 'dethemekit-for-elementor' ),
                    'backInLeft' => esc_html__( 'backInLeft', 'dethemekit-for-elementor' ),
                    'backOutLeft' => esc_html__( 'backOutLeft', 'dethemekit-for-elementor' ),
                    'backInRight' => esc_html__( 'backInRight', 'dethemekit-for-elementor' ),
                    'backOutRight' => esc_html__( 'backOutRight', 'dethemekit-for-elementor' ),
                    'backInUp' => esc_html__( 'backInUp', 'dethemekit-for-elementor' ),
                    'backOutUp' => esc_html__( 'backOutUp', 'dethemekit-for-elementor' ),
                    'bounce' => esc_html__( 'bounce', 'dethemekit-for-elementor' ),
                    'bounceIn' => esc_html__( 'bounceIn', 'dethemekit-for-elementor' ),
                    'bounceOut' => esc_html__( 'bounceOut', 'dethemekit-for-elementor' ),
                    'bounceInDown' => esc_html__( 'bounceInDown', 'dethemekit-for-elementor' ),
                    'bounceOutDown' => esc_html__( 'bounceOutDown', 'dethemekit-for-elementor' ),
                    'bounceInLeft' => esc_html__( 'bounceInLeft', 'dethemekit-for-elementor' ),
                    'bounceOutLeft' => esc_html__( 'bounceOutLeft', 'dethemekit-for-elementor' ),
                    'bounceInRight' => esc_html__( 'bounceInRight', 'dethemekit-for-elementor' ),
                    'bounceOutRight' => esc_html__( 'bounceOutRight', 'dethemekit-for-elementor' ),
                    'bounceInUp' => esc_html__( 'bounceInUp', 'dethemekit-for-elementor' ),
                    'bounceOutUp' => esc_html__( 'bounceOutUp', 'dethemekit-for-elementor' ),
                    'fadeIn' => esc_html__( 'fadeIn', 'dethemekit-for-elementor' ),
                    'fadeOut' => esc_html__( 'fadeOut', 'dethemekit-for-elementor' ),
					'fadeInDown' => esc_html__( 'fadeInDown', 'dethemekit-for-elementor' ),
                    'fadeOutDown' => esc_html__( 'fadeOutDown', 'dethemekit-for-elementor' ),
					'fadeInDownBig' => esc_html__( 'fadeInDownBig', 'dethemekit-for-elementor' ),
                    'fadeOutDownBig' => esc_html__( 'fadeOutDownBig', 'dethemekit-for-elementor' ),
					'fadeInLeft' => esc_html__( 'fadeInLeft', 'dethemekit-for-elementor' ),
                    'fadeOutLeft' => esc_html__( 'fadeOutLeft', 'dethemekit-for-elementor' ),
					'fadeInLeftBig' => esc_html__( 'fadeInLeftBig', 'dethemekit-for-elementor' ),
                    'fadeOutLeftBig' => esc_html__( 'fadeOutLeftBig', 'dethemekit-for-elementor' ),
					'fadeInRight' => esc_html__( 'fadeInRight', 'dethemekit-for-elementor' ),
                    'fadeOutRight' => esc_html__( 'fadeOutRight', 'dethemekit-for-elementor' ),
					'fadeInRightBig' => esc_html__( 'fadeInRightBig', 'dethemekit-for-elementor' ),
                    'fadeOutRightBig' => esc_html__( 'fadeOutRightBig', 'dethemekit-for-elementor' ),
					'fadeInUp' => esc_html__( 'fadeInUp', 'dethemekit-for-elementor' ),
                    'fadeOutUp' => esc_html__( 'fadeOutUp', 'dethemekit-for-elementor' ),
					'fadeInUpBig' => esc_html__( 'fadeInUpBig', 'dethemekit-for-elementor' ),
                    'fadeOutUpBig' => esc_html__( 'fadeOutUpBig', 'dethemekit-for-elementor' ),
					'fadeInTopLeft' => esc_html__( 'fadeInTopLeft', 'dethemekit-for-elementor' ),
                    'fadeOutTopLeft' => esc_html__( 'fadeOutTopLeft', 'dethemekit-for-elementor' ),
					'fadeInTopRight' => esc_html__( 'fadeInTopRight', 'dethemekit-for-elementor' ),
                    'fadeOutTopRight' => esc_html__( 'fadeOutTopRight', 'dethemekit-for-elementor' ),
					'fadeInBottomLeft' => esc_html__( 'fadeInBottomLeft', 'dethemekit-for-elementor' ),
                    'fadeOutBottomLeft' => esc_html__( 'fadeOutBottomLeft', 'dethemekit-for-elementor' ),
					'fadeInBottomRight' => esc_html__( 'fadeInBottomRight', 'dethemekit-for-elementor' ),
                    'fadeOutBottomRight' => esc_html__( 'fadeOutBottomRight', 'dethemekit-for-elementor' ),
                    'flash' => esc_html__( 'flash', 'dethemekit-for-elementor' ),
                    'flip' => esc_html__( 'flip', 'dethemekit-for-elementor' ),
                    'flipInX' => esc_html__( 'flipInX', 'dethemekit-for-elementor' ),
                    'flipOutX' => esc_html__( 'flipOutX', 'dethemekit-for-elementor' ),
                    'flipInY' => esc_html__( 'flipInY', 'dethemekit-for-elementor' ),
                    'flipOutY' => esc_html__( 'flipOutY', 'dethemekit-for-elementor' ),
                    'headShake' => esc_html__( 'headShake', 'dethemekit-for-elementor' ),
                    'heartBeat' => esc_html__( 'heartBeat', 'dethemekit-for-elementor' ),
                    'hinge' => esc_html__( 'hinge', 'dethemekit-for-elementor' ),
                    'jackInTheBox' => esc_html__( 'jackInTheBox', 'dethemekit-for-elementor' ),
                    'jello' => esc_html__( 'jello', 'dethemekit-for-elementor' ),
                    'lightSpeedInRight' => esc_html__( 'lightSpeedInRight', 'dethemekit-for-elementor' ),
                    'lightSpeedOutRight' => esc_html__( 'lightSpeedOutRight', 'dethemekit-for-elementor' ),
                    'lightSpeedInLeft' => esc_html__( 'lightSpeedInLeft', 'dethemekit-for-elementor' ),
                    'lightSpeedOutLeft' => esc_html__( 'lightSpeedOutLeft', 'dethemekit-for-elementor' ),
                    'pulse' => esc_html__( 'pulse', 'dethemekit-for-elementor' ),
                    'rollIn' => esc_html__( 'rollIn', 'dethemekit-for-elementor' ),
                    'rollOut' => esc_html__( 'rollOut', 'dethemekit-for-elementor' ),
                    'rotateIn' => esc_html__( 'rotateIn', 'dethemekit-for-elementor' ),
                    'rotateOut' => esc_html__( 'rotateOut', 'dethemekit-for-elementor' ),
                    'rotateInDownLeft' => esc_html__( 'rotateInDownLeft', 'dethemekit-for-elementor' ),
                    'rotateOutDownLeft' => esc_html__( 'rotateOutDownLeft', 'dethemekit-for-elementor' ),
                    'rotateInDownRight' => esc_html__( 'rotateInDownRight', 'dethemekit-for-elementor' ),
                    'rotateOutDownRight' => esc_html__( 'rotateOutDownRight', 'dethemekit-for-elementor' ),
                    'rotateInUpLeft' => esc_html__( 'rotateInUpLeft', 'dethemekit-for-elementor' ),
                    'rotateOutUpLeft' => esc_html__( 'rotateOutUpLeft', 'dethemekit-for-elementor' ),
                    'rotateInUpRight' => esc_html__( 'rotateInUpRight', 'dethemekit-for-elementor' ),
                    'rotateOutUpRight' => esc_html__( 'rotateOutUpRight', 'dethemekit-for-elementor' ),
                    'rubberBand' => esc_html__( 'rubberBand', 'dethemekit-for-elementor' ),
                    'shakeX' => esc_html__( 'shakeX', 'dethemekit-for-elementor' ),
                    'shakeY' => esc_html__( 'shakeY', 'dethemekit-for-elementor' ),
                    'slideInDown' => esc_html__( 'slideInDown', 'dethemekit-for-elementor' ),
                    'slideOutDown' => esc_html__( 'slideOutDown', 'dethemekit-for-elementor' ),
                    'slideInLeft' => esc_html__( 'slideInLeft', 'dethemekit-for-elementor' ),
                    'slideOutLeft' => esc_html__( 'slideOutLeft', 'dethemekit-for-elementor' ),
                    'slideInRight' => esc_html__( 'slideInRight', 'dethemekit-for-elementor' ),
                    'slideOutRight' => esc_html__( 'slideOutRight', 'dethemekit-for-elementor' ),
                    'slideInUp' => esc_html__( 'slideInUp', 'dethemekit-for-elementor' ),
                    'slideOutUp' => esc_html__( 'slideOutUp', 'dethemekit-for-elementor' ),
                    'swing' => esc_html__( 'swing', 'dethemekit-for-elementor' ),
                    'tada' => esc_html__( 'tada', 'dethemekit-for-elementor' ),
                    'wobble' => esc_html__( 'wobble', 'dethemekit-for-elementor' ),
                    'zoomIn' => esc_html__( 'zoomIn', 'dethemekit-for-elementor' ),
                    'zoomOut' => esc_html__( 'zoomOut', 'dethemekit-for-elementor' ),
                    'zoomInDown' => esc_html__( 'zoomInDown', 'dethemekit-for-elementor' ),
                    'zoomOutDown' => esc_html__( 'zoomOutDown', 'dethemekit-for-elementor' ),
                    'zoomInLeft' => esc_html__( 'zoomInLeft', 'dethemekit-for-elementor' ),
                    'zoomOutLeft' => esc_html__( 'zoomOutLeft', 'dethemekit-for-elementor' ),
                    'zoomInRight' => esc_html__( 'zoomInRight', 'dethemekit-for-elementor' ),
                    'zoomOutRight' => esc_html__( 'zoomOutRight', 'dethemekit-for-elementor' ),
                    'zoomInUp' => esc_html__( 'zoomInUp', 'dethemekit-for-elementor' ),
                    'zoomOutUp' => esc_html__( 'zoomOutUp', 'dethemekit-for-elementor' ),
                ],
				'default' => 'inherited',
				'prefix_class' => 'de_staggering_animation_mousehover_',
				'condition' => [ 'de_staggering_animation' => 'yes' ],
			]
		);

		$elems->add_control(
			'de_staggering_animation_mouseout',
			[
				'label' => '<strong>'.esc_html__( 'Animation on Mouse Out', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::SELECT,
				'options' => [
                    'inherited' => esc_html__( 'inherited', 'dethemekit-for-elementor' ),
                    'none' => esc_html__( 'none', 'dethemekit-for-elementor' ),
                    'backInDown' => esc_html__( 'backInDown', 'dethemekit-for-elementor' ),
                    'backOutDown' => esc_html__( 'backOutDown', 'dethemekit-for-elementor' ),
                    'backInLeft' => esc_html__( 'backInLeft', 'dethemekit-for-elementor' ),
                    'backOutLeft' => esc_html__( 'backOutLeft', 'dethemekit-for-elementor' ),
                    'backInRight' => esc_html__( 'backInRight', 'dethemekit-for-elementor' ),
                    'backOutRight' => esc_html__( 'backOutRight', 'dethemekit-for-elementor' ),
                    'backInUp' => esc_html__( 'backInUp', 'dethemekit-for-elementor' ),
                    'backOutUp' => esc_html__( 'backOutUp', 'dethemekit-for-elementor' ),
                    'bounce' => esc_html__( 'bounce', 'dethemekit-for-elementor' ),
                    'bounceIn' => esc_html__( 'bounceIn', 'dethemekit-for-elementor' ),
                    'bounceOut' => esc_html__( 'bounceOut', 'dethemekit-for-elementor' ),
                    'bounceInDown' => esc_html__( 'bounceInDown', 'dethemekit-for-elementor' ),
                    'bounceOutDown' => esc_html__( 'bounceOutDown', 'dethemekit-for-elementor' ),
                    'bounceInLeft' => esc_html__( 'bounceInLeft', 'dethemekit-for-elementor' ),
                    'bounceOutLeft' => esc_html__( 'bounceOutLeft', 'dethemekit-for-elementor' ),
                    'bounceInRight' => esc_html__( 'bounceInRight', 'dethemekit-for-elementor' ),
                    'bounceOutRight' => esc_html__( 'bounceOutRight', 'dethemekit-for-elementor' ),
                    'bounceInUp' => esc_html__( 'bounceInUp', 'dethemekit-for-elementor' ),
                    'bounceOutUp' => esc_html__( 'bounceOutUp', 'dethemekit-for-elementor' ),
                    'fadeIn' => esc_html__( 'fadeIn', 'dethemekit-for-elementor' ),
                    'fadeOut' => esc_html__( 'fadeOut', 'dethemekit-for-elementor' ),
					'fadeInDown' => esc_html__( 'fadeInDown', 'dethemekit-for-elementor' ),
                    'fadeOutDown' => esc_html__( 'fadeOutDown', 'dethemekit-for-elementor' ),
					'fadeInDownBig' => esc_html__( 'fadeInDownBig', 'dethemekit-for-elementor' ),
                    'fadeOutDownBig' => esc_html__( 'fadeOutDownBig', 'dethemekit-for-elementor' ),
					'fadeInLeft' => esc_html__( 'fadeInLeft', 'dethemekit-for-elementor' ),
                    'fadeOutLeft' => esc_html__( 'fadeOutLeft', 'dethemekit-for-elementor' ),
					'fadeInLeftBig' => esc_html__( 'fadeInLeftBig', 'dethemekit-for-elementor' ),
                    'fadeOutLeftBig' => esc_html__( 'fadeOutLeftBig', 'dethemekit-for-elementor' ),
					'fadeInRight' => esc_html__( 'fadeInRight', 'dethemekit-for-elementor' ),
                    'fadeOutRight' => esc_html__( 'fadeOutRight', 'dethemekit-for-elementor' ),
					'fadeInRightBig' => esc_html__( 'fadeInRightBig', 'dethemekit-for-elementor' ),
                    'fadeOutRightBig' => esc_html__( 'fadeOutRightBig', 'dethemekit-for-elementor' ),
					'fadeInUp' => esc_html__( 'fadeInUp', 'dethemekit-for-elementor' ),
                    'fadeOutUp' => esc_html__( 'fadeOutUp', 'dethemekit-for-elementor' ),
					'fadeInUpBig' => esc_html__( 'fadeInUpBig', 'dethemekit-for-elementor' ),
                    'fadeOutUpBig' => esc_html__( 'fadeOutUpBig', 'dethemekit-for-elementor' ),
					'fadeInTopLeft' => esc_html__( 'fadeInTopLeft', 'dethemekit-for-elementor' ),
                    'fadeOutTopLeft' => esc_html__( 'fadeOutTopLeft', 'dethemekit-for-elementor' ),
					'fadeInTopRight' => esc_html__( 'fadeInTopRight', 'dethemekit-for-elementor' ),
                    'fadeOutTopRight' => esc_html__( 'fadeOutTopRight', 'dethemekit-for-elementor' ),
					'fadeInBottomLeft' => esc_html__( 'fadeInBottomLeft', 'dethemekit-for-elementor' ),
                    'fadeOutBottomLeft' => esc_html__( 'fadeOutBottomLeft', 'dethemekit-for-elementor' ),
					'fadeInBottomRight' => esc_html__( 'fadeInBottomRight', 'dethemekit-for-elementor' ),
                    'fadeOutBottomRight' => esc_html__( 'fadeOutBottomRight', 'dethemekit-for-elementor' ),
                    'flash' => esc_html__( 'flash', 'dethemekit-for-elementor' ),
                    'flip' => esc_html__( 'flip', 'dethemekit-for-elementor' ),
                    'flipInX' => esc_html__( 'flipInX', 'dethemekit-for-elementor' ),
                    'flipOutX' => esc_html__( 'flipOutX', 'dethemekit-for-elementor' ),
                    'flipInY' => esc_html__( 'flipInY', 'dethemekit-for-elementor' ),
                    'flipOutY' => esc_html__( 'flipOutY', 'dethemekit-for-elementor' ),
                    'headShake' => esc_html__( 'headShake', 'dethemekit-for-elementor' ),
                    'heartBeat' => esc_html__( 'heartBeat', 'dethemekit-for-elementor' ),
                    'hinge' => esc_html__( 'hinge', 'dethemekit-for-elementor' ),
                    'jackInTheBox' => esc_html__( 'jackInTheBox', 'dethemekit-for-elementor' ),
                    'jello' => esc_html__( 'jello', 'dethemekit-for-elementor' ),
                    'lightSpeedInRight' => esc_html__( 'lightSpeedInRight', 'dethemekit-for-elementor' ),
                    'lightSpeedOutRight' => esc_html__( 'lightSpeedOutRight', 'dethemekit-for-elementor' ),
                    'lightSpeedInLeft' => esc_html__( 'lightSpeedInLeft', 'dethemekit-for-elementor' ),
                    'lightSpeedOutLeft' => esc_html__( 'lightSpeedOutLeft', 'dethemekit-for-elementor' ),
                    'pulse' => esc_html__( 'pulse', 'dethemekit-for-elementor' ),
                    'rollIn' => esc_html__( 'rollIn', 'dethemekit-for-elementor' ),
                    'rollOut' => esc_html__( 'rollOut', 'dethemekit-for-elementor' ),
                    'rotateIn' => esc_html__( 'rotateIn', 'dethemekit-for-elementor' ),
                    'rotateOut' => esc_html__( 'rotateOut', 'dethemekit-for-elementor' ),
                    'rotateInDownLeft' => esc_html__( 'rotateInDownLeft', 'dethemekit-for-elementor' ),
                    'rotateOutDownLeft' => esc_html__( 'rotateOutDownLeft', 'dethemekit-for-elementor' ),
                    'rotateInDownRight' => esc_html__( 'rotateInDownRight', 'dethemekit-for-elementor' ),
                    'rotateOutDownRight' => esc_html__( 'rotateOutDownRight', 'dethemekit-for-elementor' ),
                    'rotateInUpLeft' => esc_html__( 'rotateInUpLeft', 'dethemekit-for-elementor' ),
                    'rotateOutUpLeft' => esc_html__( 'rotateOutUpLeft', 'dethemekit-for-elementor' ),
                    'rotateInUpRight' => esc_html__( 'rotateInUpRight', 'dethemekit-for-elementor' ),
                    'rotateOutUpRight' => esc_html__( 'rotateOutUpRight', 'dethemekit-for-elementor' ),
                    'rubberBand' => esc_html__( 'rubberBand', 'dethemekit-for-elementor' ),
                    'shakeX' => esc_html__( 'shakeX', 'dethemekit-for-elementor' ),
                    'shakeY' => esc_html__( 'shakeY', 'dethemekit-for-elementor' ),
                    'slideInDown' => esc_html__( 'slideInDown', 'dethemekit-for-elementor' ),
                    'slideOutDown' => esc_html__( 'slideOutDown', 'dethemekit-for-elementor' ),
                    'slideInLeft' => esc_html__( 'slideInLeft', 'dethemekit-for-elementor' ),
                    'slideOutLeft' => esc_html__( 'slideOutLeft', 'dethemekit-for-elementor' ),
                    'slideInRight' => esc_html__( 'slideInRight', 'dethemekit-for-elementor' ),
                    'slideOutRight' => esc_html__( 'slideOutRight', 'dethemekit-for-elementor' ),
                    'slideInUp' => esc_html__( 'slideInUp', 'dethemekit-for-elementor' ),
                    'slideOutUp' => esc_html__( 'slideOutUp', 'dethemekit-for-elementor' ),
                    'swing' => esc_html__( 'swing', 'dethemekit-for-elementor' ),
                    'tada' => esc_html__( 'tada', 'dethemekit-for-elementor' ),
                    'wobble' => esc_html__( 'wobble', 'dethemekit-for-elementor' ),
                    'zoomIn' => esc_html__( 'zoomIn', 'dethemekit-for-elementor' ),
                    'zoomOut' => esc_html__( 'zoomOut', 'dethemekit-for-elementor' ),
                    'zoomInDown' => esc_html__( 'zoomInDown', 'dethemekit-for-elementor' ),
                    'zoomOutDown' => esc_html__( 'zoomOutDown', 'dethemekit-for-elementor' ),
                    'zoomInLeft' => esc_html__( 'zoomInLeft', 'dethemekit-for-elementor' ),
                    'zoomOutLeft' => esc_html__( 'zoomOutLeft', 'dethemekit-for-elementor' ),
                    'zoomInRight' => esc_html__( 'zoomInRight', 'dethemekit-for-elementor' ),
                    'zoomOutRight' => esc_html__( 'zoomOutRight', 'dethemekit-for-elementor' ),
                    'zoomInUp' => esc_html__( 'zoomInUp', 'dethemekit-for-elementor' ),
                    'zoomOutUp' => esc_html__( 'zoomOutUp', 'dethemekit-for-elementor' ),
                ],
				'default' => 'inherited',
				'prefix_class' => 'de_staggering_animation_mouseout_',
				'condition' => [ 'de_staggering_animation' => 'yes' ],
			]
		);

		$elems->end_controls_section();
	}

	public function section_register_controls($elems) {
	}

	public function column_register_controls($elems) {
		$elems->start_controls_section(
			'de_staggering_section',
			[
				'label' => __( 'De Staggering', 'dethemekit-for-elementor' ),
				'tab' => Controls_Manager::TAB_ADVANCED,
			]
		);

		$elems->add_control(
			'de_staggering_hover',
			[
				'label' => '<strong>'.esc_html__( 'De Stagger Hover Trigger', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::SWITCHER,
				'prefix_class' => 'de_staggering_hover_',
			]
		);

		$elems->add_control(
			'de_staggering_preview_on_hover',
			[
				'label' => '<strong>'.esc_html__( 'Preview Animation on Hover', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::SWITCHER,
				'prefix_class' => 'de_staggering_preview_on_hover_',
                'condition' => [ 'de_staggering_hover' => 'yes' ],
			]
		);

		$elems->add_control(
			'de_staggering_parent_initial_state',
			[
				'label' => '<strong>'.esc_html__( 'Initial State', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::SELECT,
				'options' => [
                    'hidden' => esc_html__( 'Hidden', 'dethemekit-for-elementor' ),
                    'visible' => esc_html__( 'Visible', 'dethemekit-for-elementor' ),
                ],
				'default' => 'hidden',
				'prefix_class' => 'de_staggering_parent_initial_state_',
				'condition' => [ 'de_staggering_hover' => 'yes' ],
			]
		);

		$elems->add_control(
			'de_staggering_parent_animation_mousehover',
			[
				'label' => '<strong>'.esc_html__( 'Animation on Mouse Hover', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::SELECT,
				'options' => [
                    'none' => esc_html__( 'none', 'dethemekit-for-elementor' ),
                    'backInDown' => esc_html__( 'backInDown', 'dethemekit-for-elementor' ),
                    'backOutDown' => esc_html__( 'backOutDown', 'dethemekit-for-elementor' ),
                    'backInLeft' => esc_html__( 'backInLeft', 'dethemekit-for-elementor' ),
                    'backOutLeft' => esc_html__( 'backOutLeft', 'dethemekit-for-elementor' ),
                    'backInRight' => esc_html__( 'backInRight', 'dethemekit-for-elementor' ),
                    'backOutRight' => esc_html__( 'backOutRight', 'dethemekit-for-elementor' ),
                    'backInUp' => esc_html__( 'backInUp', 'dethemekit-for-elementor' ),
                    'backOutUp' => esc_html__( 'backOutUp', 'dethemekit-for-elementor' ),
                    'bounce' => esc_html__( 'bounce', 'dethemekit-for-elementor' ),
                    'bounceIn' => esc_html__( 'bounceIn', 'dethemekit-for-elementor' ),
                    'bounceOut' => esc_html__( 'bounceOut', 'dethemekit-for-elementor' ),
                    'bounceInDown' => esc_html__( 'bounceInDown', 'dethemekit-for-elementor' ),
                    'bounceOutDown' => esc_html__( 'bounceOutDown', 'dethemekit-for-elementor' ),
                    'bounceInLeft' => esc_html__( 'bounceInLeft', 'dethemekit-for-elementor' ),
                    'bounceOutLeft' => esc_html__( 'bounceOutLeft', 'dethemekit-for-elementor' ),
                    'bounceInRight' => esc_html__( 'bounceInRight', 'dethemekit-for-elementor' ),
                    'bounceOutRight' => esc_html__( 'bounceOutRight', 'dethemekit-for-elementor' ),
                    'bounceInUp' => esc_html__( 'bounceInUp', 'dethemekit-for-elementor' ),
                    'bounceOutUp' => esc_html__( 'bounceOutUp', 'dethemekit-for-elementor' ),
                    'fadeIn' => esc_html__( 'fadeIn', 'dethemekit-for-elementor' ),
                    'fadeOut' => esc_html__( 'fadeOut', 'dethemekit-for-elementor' ),
					'fadeInDown' => esc_html__( 'fadeInDown', 'dethemekit-for-elementor' ),
                    'fadeOutDown' => esc_html__( 'fadeOutDown', 'dethemekit-for-elementor' ),
					'fadeInDownBig' => esc_html__( 'fadeInDownBig', 'dethemekit-for-elementor' ),
                    'fadeOutDownBig' => esc_html__( 'fadeOutDownBig', 'dethemekit-for-elementor' ),
					'fadeInLeft' => esc_html__( 'fadeInLeft', 'dethemekit-for-elementor' ),
                    'fadeOutLeft' => esc_html__( 'fadeOutLeft', 'dethemekit-for-elementor' ),
					'fadeInLeftBig' => esc_html__( 'fadeInLeftBig', 'dethemekit-for-elementor' ),
                    'fadeOutLeftBig' => esc_html__( 'fadeOutLeftBig', 'dethemekit-for-elementor' ),
					'fadeInRight' => esc_html__( 'fadeInRight', 'dethemekit-for-elementor' ),
                    'fadeOutRight' => esc_html__( 'fadeOutRight', 'dethemekit-for-elementor' ),
					'fadeInRightBig' => esc_html__( 'fadeInRightBig', 'dethemekit-for-elementor' ),
                    'fadeOutRightBig' => esc_html__( 'fadeOutRightBig', 'dethemekit-for-elementor' ),
					'fadeInUp' => esc_html__( 'fadeInUp', 'dethemekit-for-elementor' ),
                    'fadeOutUp' => esc_html__( 'fadeOutUp', 'dethemekit-for-elementor' ),
					'fadeInUpBig' => esc_html__( 'fadeInUpBig', 'dethemekit-for-elementor' ),
                    'fadeOutUpBig' => esc_html__( 'fadeOutUpBig', 'dethemekit-for-elementor' ),
					'fadeInTopLeft' => esc_html__( 'fadeInTopLeft', 'dethemekit-for-elementor' ),
                    'fadeOutTopLeft' => esc_html__( 'fadeOutTopLeft', 'dethemekit-for-elementor' ),
					'fadeInTopRight' => esc_html__( 'fadeInTopRight', 'dethemekit-for-elementor' ),
                    'fadeOutTopRight' => esc_html__( 'fadeOutTopRight', 'dethemekit-for-elementor' ),
					'fadeInBottomLeft' => esc_html__( 'fadeInBottomLeft', 'dethemekit-for-elementor' ),
                    'fadeOutBottomLeft' => esc_html__( 'fadeOutBottomLeft', 'dethemekit-for-elementor' ),
					'fadeInBottomRight' => esc_html__( 'fadeInBottomRight', 'dethemekit-for-elementor' ),
                    'fadeOutBottomRight' => esc_html__( 'fadeOutBottomRight', 'dethemekit-for-elementor' ),
                    'flash' => esc_html__( 'flash', 'dethemekit-for-elementor' ),
                    'flip' => esc_html__( 'flip', 'dethemekit-for-elementor' ),
                    'flipInX' => esc_html__( 'flipInX', 'dethemekit-for-elementor' ),
                    'flipOutX' => esc_html__( 'flipOutX', 'dethemekit-for-elementor' ),
                    'flipInY' => esc_html__( 'flipInY', 'dethemekit-for-elementor' ),
                    'flipOutY' => esc_html__( 'flipOutY', 'dethemekit-for-elementor' ),
                    'headShake' => esc_html__( 'headShake', 'dethemekit-for-elementor' ),
                    'heartBeat' => esc_html__( 'heartBeat', 'dethemekit-for-elementor' ),
                    'hinge' => esc_html__( 'hinge', 'dethemekit-for-elementor' ),
                    'jackInTheBox' => esc_html__( 'jackInTheBox', 'dethemekit-for-elementor' ),
                    'jello' => esc_html__( 'jello', 'dethemekit-for-elementor' ),
                    'lightSpeedInRight' => esc_html__( 'lightSpeedInRight', 'dethemekit-for-elementor' ),
                    'lightSpeedOutRight' => esc_html__( 'lightSpeedOutRight', 'dethemekit-for-elementor' ),
                    'lightSpeedInLeft' => esc_html__( 'lightSpeedInLeft', 'dethemekit-for-elementor' ),
                    'lightSpeedOutLeft' => esc_html__( 'lightSpeedOutLeft', 'dethemekit-for-elementor' ),
                    'pulse' => esc_html__( 'pulse', 'dethemekit-for-elementor' ),
                    'rollIn' => esc_html__( 'rollIn', 'dethemekit-for-elementor' ),
                    'rollOut' => esc_html__( 'rollOut', 'dethemekit-for-elementor' ),
                    'rotateIn' => esc_html__( 'rotateIn', 'dethemekit-for-elementor' ),
                    'rotateOut' => esc_html__( 'rotateOut', 'dethemekit-for-elementor' ),
                    'rotateInDownLeft' => esc_html__( 'rotateInDownLeft', 'dethemekit-for-elementor' ),
                    'rotateOutDownLeft' => esc_html__( 'rotateOutDownLeft', 'dethemekit-for-elementor' ),
                    'rotateInDownRight' => esc_html__( 'rotateInDownRight', 'dethemekit-for-elementor' ),
                    'rotateOutDownRight' => esc_html__( 'rotateOutDownRight', 'dethemekit-for-elementor' ),
                    'rotateInUpLeft' => esc_html__( 'rotateInUpLeft', 'dethemekit-for-elementor' ),
                    'rotateOutUpLeft' => esc_html__( 'rotateOutUpLeft', 'dethemekit-for-elementor' ),
                    'rotateInUpRight' => esc_html__( 'rotateInUpRight', 'dethemekit-for-elementor' ),
                    'rotateOutUpRight' => esc_html__( 'rotateOutUpRight', 'dethemekit-for-elementor' ),
                    'rubberBand' => esc_html__( 'rubberBand', 'dethemekit-for-elementor' ),
                    'shakeX' => esc_html__( 'shakeX', 'dethemekit-for-elementor' ),
                    'shakeY' => esc_html__( 'shakeY', 'dethemekit-for-elementor' ),
                    'slideInDown' => esc_html__( 'slideInDown', 'dethemekit-for-elementor' ),
                    'slideOutDown' => esc_html__( 'slideOutDown', 'dethemekit-for-elementor' ),
                    'slideInLeft' => esc_html__( 'slideInLeft', 'dethemekit-for-elementor' ),
                    'slideOutLeft' => esc_html__( 'slideOutLeft', 'dethemekit-for-elementor' ),
                    'slideInRight' => esc_html__( 'slideInRight', 'dethemekit-for-elementor' ),
                    'slideOutRight' => esc_html__( 'slideOutRight', 'dethemekit-for-elementor' ),
                    'slideInUp' => esc_html__( 'slideInUp', 'dethemekit-for-elementor' ),
                    'slideOutUp' => esc_html__( 'slideOutUp', 'dethemekit-for-elementor' ),
                    'swing' => esc_html__( 'swing', 'dethemekit-for-elementor' ),
                    'tada' => esc_html__( 'tada', 'dethemekit-for-elementor' ),
                    'wobble' => esc_html__( 'wobble', 'dethemekit-for-elementor' ),
                    'zoomIn' => esc_html__( 'zoomIn', 'dethemekit-for-elementor' ),
                    'zoomOut' => esc_html__( 'zoomOut', 'dethemekit-for-elementor' ),
                    'zoomInDown' => esc_html__( 'zoomInDown', 'dethemekit-for-elementor' ),
                    'zoomOutDown' => esc_html__( 'zoomOutDown', 'dethemekit-for-elementor' ),
                    'zoomInLeft' => esc_html__( 'zoomInLeft', 'dethemekit-for-elementor' ),
                    'zoomOutLeft' => esc_html__( 'zoomOutLeft', 'dethemekit-for-elementor' ),
                    'zoomInRight' => esc_html__( 'zoomInRight', 'dethemekit-for-elementor' ),
                    'zoomOutRight' => esc_html__( 'zoomOutRight', 'dethemekit-for-elementor' ),
                    'zoomInUp' => esc_html__( 'zoomInUp', 'dethemekit-for-elementor' ),
                    'zoomOutUp' => esc_html__( 'zoomOutUp', 'dethemekit-for-elementor' ),
                ],
				'default' => 'fadeIn',
				'prefix_class' => 'de_staggering_parent_animation_mousehover_',
				'condition' => [ 'de_staggering_hover' => 'yes' ],
			]
		);

		$elems->add_control(
			'de_staggering_parent_animation_mouseout',
			[
				'label' => '<strong>'.esc_html__( 'Animation on Mouse Out', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::SELECT,
				'options' => [
                    'none' => esc_html__( 'none', 'dethemekit-for-elementor' ),
                    'backInDown' => esc_html__( 'backInDown', 'dethemekit-for-elementor' ),
                    'backOutDown' => esc_html__( 'backOutDown', 'dethemekit-for-elementor' ),
                    'backInLeft' => esc_html__( 'backInLeft', 'dethemekit-for-elementor' ),
                    'backOutLeft' => esc_html__( 'backOutLeft', 'dethemekit-for-elementor' ),
                    'backInRight' => esc_html__( 'backInRight', 'dethemekit-for-elementor' ),
                    'backOutRight' => esc_html__( 'backOutRight', 'dethemekit-for-elementor' ),
                    'backInUp' => esc_html__( 'backInUp', 'dethemekit-for-elementor' ),
                    'backOutUp' => esc_html__( 'backOutUp', 'dethemekit-for-elementor' ),
                    'bounce' => esc_html__( 'bounce', 'dethemekit-for-elementor' ),
                    'bounceIn' => esc_html__( 'bounceIn', 'dethemekit-for-elementor' ),
                    'bounceOut' => esc_html__( 'bounceOut', 'dethemekit-for-elementor' ),
                    'bounceInDown' => esc_html__( 'bounceInDown', 'dethemekit-for-elementor' ),
                    'bounceOutDown' => esc_html__( 'bounceOutDown', 'dethemekit-for-elementor' ),
                    'bounceInLeft' => esc_html__( 'bounceInLeft', 'dethemekit-for-elementor' ),
                    'bounceOutLeft' => esc_html__( 'bounceOutLeft', 'dethemekit-for-elementor' ),
                    'bounceInRight' => esc_html__( 'bounceInRight', 'dethemekit-for-elementor' ),
                    'bounceOutRight' => esc_html__( 'bounceOutRight', 'dethemekit-for-elementor' ),
                    'bounceInUp' => esc_html__( 'bounceInUp', 'dethemekit-for-elementor' ),
                    'bounceOutUp' => esc_html__( 'bounceOutUp', 'dethemekit-for-elementor' ),
                    'fadeIn' => esc_html__( 'fadeIn', 'dethemekit-for-elementor' ),
                    'fadeOut' => esc_html__( 'fadeOut', 'dethemekit-for-elementor' ),
					'fadeInDown' => esc_html__( 'fadeInDown', 'dethemekit-for-elementor' ),
                    'fadeOutDown' => esc_html__( 'fadeOutDown', 'dethemekit-for-elementor' ),
					'fadeInDownBig' => esc_html__( 'fadeInDownBig', 'dethemekit-for-elementor' ),
                    'fadeOutDownBig' => esc_html__( 'fadeOutDownBig', 'dethemekit-for-elementor' ),
					'fadeInLeft' => esc_html__( 'fadeInLeft', 'dethemekit-for-elementor' ),
                    'fadeOutLeft' => esc_html__( 'fadeOutLeft', 'dethemekit-for-elementor' ),
					'fadeInLeftBig' => esc_html__( 'fadeInLeftBig', 'dethemekit-for-elementor' ),
                    'fadeOutLeftBig' => esc_html__( 'fadeOutLeftBig', 'dethemekit-for-elementor' ),
					'fadeInRight' => esc_html__( 'fadeInRight', 'dethemekit-for-elementor' ),
                    'fadeOutRight' => esc_html__( 'fadeOutRight', 'dethemekit-for-elementor' ),
					'fadeInRightBig' => esc_html__( 'fadeInRightBig', 'dethemekit-for-elementor' ),
                    'fadeOutRightBig' => esc_html__( 'fadeOutRightBig', 'dethemekit-for-elementor' ),
					'fadeInUp' => esc_html__( 'fadeInUp', 'dethemekit-for-elementor' ),
                    'fadeOutUp' => esc_html__( 'fadeOutUp', 'dethemekit-for-elementor' ),
					'fadeInUpBig' => esc_html__( 'fadeInUpBig', 'dethemekit-for-elementor' ),
                    'fadeOutUpBig' => esc_html__( 'fadeOutUpBig', 'dethemekit-for-elementor' ),
					'fadeInTopLeft' => esc_html__( 'fadeInTopLeft', 'dethemekit-for-elementor' ),
                    'fadeOutTopLeft' => esc_html__( 'fadeOutTopLeft', 'dethemekit-for-elementor' ),
					'fadeInTopRight' => esc_html__( 'fadeInTopRight', 'dethemekit-for-elementor' ),
                    'fadeOutTopRight' => esc_html__( 'fadeOutTopRight', 'dethemekit-for-elementor' ),
					'fadeInBottomLeft' => esc_html__( 'fadeInBottomLeft', 'dethemekit-for-elementor' ),
                    'fadeOutBottomLeft' => esc_html__( 'fadeOutBottomLeft', 'dethemekit-for-elementor' ),
					'fadeInBottomRight' => esc_html__( 'fadeInBottomRight', 'dethemekit-for-elementor' ),
                    'fadeOutBottomRight' => esc_html__( 'fadeOutBottomRight', 'dethemekit-for-elementor' ),
                    'flash' => esc_html__( 'flash', 'dethemekit-for-elementor' ),
                    'flip' => esc_html__( 'flip', 'dethemekit-for-elementor' ),
                    'flipInX' => esc_html__( 'flipInX', 'dethemekit-for-elementor' ),
                    'flipOutX' => esc_html__( 'flipOutX', 'dethemekit-for-elementor' ),
                    'flipInY' => esc_html__( 'flipInY', 'dethemekit-for-elementor' ),
                    'flipOutY' => esc_html__( 'flipOutY', 'dethemekit-for-elementor' ),
                    'headShake' => esc_html__( 'headShake', 'dethemekit-for-elementor' ),
                    'heartBeat' => esc_html__( 'heartBeat', 'dethemekit-for-elementor' ),
                    'hinge' => esc_html__( 'hinge', 'dethemekit-for-elementor' ),
                    'jackInTheBox' => esc_html__( 'jackInTheBox', 'dethemekit-for-elementor' ),
                    'jello' => esc_html__( 'jello', 'dethemekit-for-elementor' ),
                    'lightSpeedInRight' => esc_html__( 'lightSpeedInRight', 'dethemekit-for-elementor' ),
                    'lightSpeedOutRight' => esc_html__( 'lightSpeedOutRight', 'dethemekit-for-elementor' ),
                    'lightSpeedInLeft' => esc_html__( 'lightSpeedInLeft', 'dethemekit-for-elementor' ),
                    'lightSpeedOutLeft' => esc_html__( 'lightSpeedOutLeft', 'dethemekit-for-elementor' ),
                    'pulse' => esc_html__( 'pulse', 'dethemekit-for-elementor' ),
                    'rollIn' => esc_html__( 'rollIn', 'dethemekit-for-elementor' ),
                    'rollOut' => esc_html__( 'rollOut', 'dethemekit-for-elementor' ),
                    'rotateIn' => esc_html__( 'rotateIn', 'dethemekit-for-elementor' ),
                    'rotateOut' => esc_html__( 'rotateOut', 'dethemekit-for-elementor' ),
                    'rotateInDownLeft' => esc_html__( 'rotateInDownLeft', 'dethemekit-for-elementor' ),
                    'rotateOutDownLeft' => esc_html__( 'rotateOutDownLeft', 'dethemekit-for-elementor' ),
                    'rotateInDownRight' => esc_html__( 'rotateInDownRight', 'dethemekit-for-elementor' ),
                    'rotateOutDownRight' => esc_html__( 'rotateOutDownRight', 'dethemekit-for-elementor' ),
                    'rotateInUpLeft' => esc_html__( 'rotateInUpLeft', 'dethemekit-for-elementor' ),
                    'rotateOutUpLeft' => esc_html__( 'rotateOutUpLeft', 'dethemekit-for-elementor' ),
                    'rotateInUpRight' => esc_html__( 'rotateInUpRight', 'dethemekit-for-elementor' ),
                    'rotateOutUpRight' => esc_html__( 'rotateOutUpRight', 'dethemekit-for-elementor' ),
                    'rubberBand' => esc_html__( 'rubberBand', 'dethemekit-for-elementor' ),
                    'shakeX' => esc_html__( 'shakeX', 'dethemekit-for-elementor' ),
                    'shakeY' => esc_html__( 'shakeY', 'dethemekit-for-elementor' ),
                    'slideInDown' => esc_html__( 'slideInDown', 'dethemekit-for-elementor' ),
                    'slideOutDown' => esc_html__( 'slideOutDown', 'dethemekit-for-elementor' ),
                    'slideInLeft' => esc_html__( 'slideInLeft', 'dethemekit-for-elementor' ),
                    'slideOutLeft' => esc_html__( 'slideOutLeft', 'dethemekit-for-elementor' ),
                    'slideInRight' => esc_html__( 'slideInRight', 'dethemekit-for-elementor' ),
                    'slideOutRight' => esc_html__( 'slideOutRight', 'dethemekit-for-elementor' ),
                    'slideInUp' => esc_html__( 'slideInUp', 'dethemekit-for-elementor' ),
                    'slideOutUp' => esc_html__( 'slideOutUp', 'dethemekit-for-elementor' ),
                    'swing' => esc_html__( 'swing', 'dethemekit-for-elementor' ),
                    'tada' => esc_html__( 'tada', 'dethemekit-for-elementor' ),
                    'wobble' => esc_html__( 'wobble', 'dethemekit-for-elementor' ),
                    'zoomIn' => esc_html__( 'zoomIn', 'dethemekit-for-elementor' ),
                    'zoomOut' => esc_html__( 'zoomOut', 'dethemekit-for-elementor' ),
                    'zoomInDown' => esc_html__( 'zoomInDown', 'dethemekit-for-elementor' ),
                    'zoomOutDown' => esc_html__( 'zoomOutDown', 'dethemekit-for-elementor' ),
                    'zoomInLeft' => esc_html__( 'zoomInLeft', 'dethemekit-for-elementor' ),
                    'zoomOutLeft' => esc_html__( 'zoomOutLeft', 'dethemekit-for-elementor' ),
                    'zoomInRight' => esc_html__( 'zoomInRight', 'dethemekit-for-elementor' ),
                    'zoomOutRight' => esc_html__( 'zoomOutRight', 'dethemekit-for-elementor' ),
                    'zoomInUp' => esc_html__( 'zoomInUp', 'dethemekit-for-elementor' ),
                    'zoomOutUp' => esc_html__( 'zoomOutUp', 'dethemekit-for-elementor' ),
                ],
				'default' => 'fadeOut',
				'prefix_class' => 'de_staggering_parent_animation_mouseout_',
				'condition' => [ 'de_staggering_hover' => 'yes' ],
			]
		);

		$elems->add_control(
			'de_staggering_child_delay',
			[
				'label' => '<strong>'.esc_html__( 'Child Element Delay (ms)', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::NUMBER,
				'prefix_class' => 'de_staggering_child_delay_',
				'default' => '500',
				'condition' => [ 'de_staggering_hover' => 'yes' ],
			]
		);

		$elems->add_control(
			'de_staggering_on_desktop',
			[
				'label' => esc_html__( 'Run Animation on Desktop', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'prefix_class' => 'de_staggering_on_desktop_',
				'condition' => [ 'de_staggering_hover' => 'yes' ],
			]
		);

		$elems->add_control(
			'de_staggering_on_tablet',
			[
				'label' => esc_html__( 'Run Animation on Tablet', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'prefix_class' => 'de_staggering_on_tablet_',
				'condition' => [ 'de_staggering_hover' => 'yes' ],
			]
		);

		$elems->add_control(
			'de_staggering_on_mobile',
			[
				'label' => esc_html__( 'Run Animation on Mobile', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'prefix_class' => 'de_staggering_on_mobile_',
				'condition' => [ 'de_staggering_hover' => 'yes' ],
			]
		);


		$elems->end_controls_section();
    }
}
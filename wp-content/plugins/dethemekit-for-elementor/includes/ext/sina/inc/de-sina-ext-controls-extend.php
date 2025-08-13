<?php
namespace De_Sina_Extension;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Css_Filter;

/**
 * De_Sina_Ext_Controls Class for extends controls
 *
 * @since 3.0.1
 */
class De_Sina_Ext_Controls{
	/**
	 * Instance
	 *
	 * @since 3.1.13
	 * @var De_Sina_Ext_Controls The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 3.1.13
	 * @return De_Sina_Ext_Controls An Instance of the class.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		add_action('elementor/element/common/_section_style/before_section_end', [$this, 'register_controls']);
		add_action('elementor/element/column/section_advanced/before_section_end', [$this, 'column_register_controls']);
		add_action('elementor/element/section/section_advanced/before_section_end', [$this, 'column_register_controls']);
	}

	public function register_controls($elems) {
		$elems->add_control(
			'sina_is_morphing_animation',
			[
				'label' => '<strong>'.esc_html__( 'De Mask', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::SWITCHER,
				'prefix_class' => 'sina-morphing-anim-',
				'separator' => 'before',
			]
		);
		$elems->add_control(
			'sina_transform_effects',
			[
				'label' => '<strong>'.esc_html__( 'De Transform', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::SELECT,
				'options' => [
					'translate' => esc_html__( 'Translate', 'dethemekit-for-elementor' ),
					'scaleX' => esc_html__( 'Scale X', 'dethemekit-for-elementor' ),
					'scaleY' => esc_html__( 'Scale Y', 'dethemekit-for-elementor' ),
					'scaleZ' => esc_html__( 'Scale Z', 'dethemekit-for-elementor' ),
					'rotateX' => esc_html__( 'Rotate X', 'dethemekit-for-elementor' ),
					'rotateY' => esc_html__( 'Rotate Y', 'dethemekit-for-elementor' ),
					'rotateZ' => esc_html__( 'Rotate Z', 'dethemekit-for-elementor' ),
					'skewX' => esc_html__( 'Skew X', 'dethemekit-for-elementor' ),
					'skewY' => esc_html__( 'Skew Y', 'dethemekit-for-elementor' ),
					'none' => esc_html__( 'None', 'dethemekit-for-elementor' ),
				],
				'default' => 'none',
			]
		);
		$elems->add_responsive_control(
			'sina_transform_perspective',
			[
				'label' => esc_html__( 'Perspective Size', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 1,
						'min' => 0,
						'max' => 10000,
					],
				],
				'default' => [
					'size' => '1000',
				],
				'condition' => [
					'sina_transform_effects' => ['rotateX', 'rotateY'],
				],
				'selectors' => [
					'{{WRAPPER}}' => 'perspective: {{SIZE}}px;',
				],
			]
		);

		$elems->start_controls_tabs( 'sina_transform_effects_tabs' );

		$elems->start_controls_tab(
			'sina_transform_effects_normal',
			[
				'label' => esc_html__( 'Normal', 'dethemekit-for-elementor' ),
			]
		);

		$elems->add_responsive_control(
			'sina_transform_effects_translateX',
			[
				'label' => esc_html__( 'Translate X', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => -100,
						'max' => 100,
					],
				],
				'default' => [
					'size' => '0',
				],
				'condition' => [
					'sina_transform_effects' => 'translate',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_translateY',
			[
				'label' => esc_html__( 'Translate Y', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => -100,
						'max' => 100,
					],
				],
				'default' => [
					'size' => '0',
				],
				'condition' => [
					'sina_transform_effects' => 'translate',
				],
				'selectors' => [
					'(desktop){{WRAPPER}}' => 'transform: translate({{sina_transform_effects_translateX.SIZE || 0}}px, {{sina_transform_effects_translateY.SIZE || 0}}px);',
					'(tablet){{WRAPPER}}' => 'transform: translate({{sina_transform_effects_translateX_tablet.SIZE || 0}}px, {{sina_transform_effects_translateY_tablet.SIZE || 0}}px);',
					'(mobile){{WRAPPER}}' => 'transform: translate({{sina_transform_effects_translateX_mobile.SIZE || 0}}px, {{sina_transform_effects_translateY_mobile.SIZE || 0}}px);',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_scaleX',
			[
				'label' => esc_html__( 'Scale X', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 0.1,
						'min' => 0.1,
						'max' => 5,
					],
				],
				'default' => [
					'size' => '1',
				],
				'condition' => [
					'sina_transform_effects' => 'scaleX',
				],
				'selectors' => [
					'{{WRAPPER}}' => 'transform: scaleX({{SIZE}});',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_scaleY',
			[
				'label' => esc_html__( 'Scale Y', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 0.1,
						'min' => 0.1,
						'max' => 5,
					],
				],
				'default' => [
					'size' => '1',
				],
				'condition' => [
					'sina_transform_effects' => 'scaleY',
				],
				'selectors' => [
					'{{WRAPPER}}' => 'transform: scaleY({{SIZE}});',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_scaleZ',
			[
				'label' => esc_html__( 'Scale Z', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 0.1,
						'min' => 0.1,
						'max' => 5,
					],
				],
				'default' => [
					'size' => '1',
				],
				'condition' => [
					'sina_transform_effects' => 'scaleZ',
				],
				'selectors' => [
					'{{WRAPPER}}' => 'transform: scale({{SIZE}});',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_rotateX',
			[
				'label' => esc_html__( 'Rotate X', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 1,
						'min' => 0,
						'max' => 360,
					],
				],
				'default' => [
					'size' => '0',
				],
				'condition' => [
					'sina_transform_effects' => 'rotateX',
				],
				'selectors' => [
					'{{WRAPPER}}' => 'transform: rotateX({{SIZE}}deg);',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_rotateY',
			[
				'label' => esc_html__( 'Rotate Y', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 1,
						'min' => 0,
						'max' => 360,
					],
				],
				'default' => [
					'size' => '0',
				],
				'condition' => [
					'sina_transform_effects' => 'rotateY',
				],
				'selectors' => [
					'{{WRAPPER}}' => 'transform: rotateY({{SIZE}}deg);',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_rotateZ',
			[
				'label' => esc_html__( 'Rotate Z', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 1,
						'min' => 0,
						'max' => 360,
					],
				],
				'default' => [
					'size' => '0',
				],
				'condition' => [
					'sina_transform_effects' => 'rotateZ',
				],
				'selectors' => [
					'{{WRAPPER}}' => 'transform: rotateZ({{SIZE}}deg);',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_skewX',
			[
				'label' => esc_html__( 'Skew X', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 1,
						'min' => -60,
						'max' => 60,
					],
				],
				'default' => [
					'size' => '0',
				],
				'condition' => [
					'sina_transform_effects' => 'skewX',
				],
				'selectors' => [
					'{{WRAPPER}}' => 'transform: skewX({{SIZE}}deg);',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_skewY',
			[
				'label' => esc_html__( 'Skew Y', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 1,
						'min' => -60,
						'max' => 60,
					],
				],
				'default' => [
					'size' => '0',
				],
				'condition' => [
					'sina_transform_effects' => 'skewY',
				],
				'selectors' => [
					'{{WRAPPER}}' => 'transform: skewY({{SIZE}}deg);',
				],
			]
		);
		$elems->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name' => 'sina_transform_effects_filters',
				'selector' => '{{WRAPPER}}',
			]
		);

		$elems->end_controls_tab();

		$elems->start_controls_tab(
			'sina_transform_effects_hover',
			[
				'label' => esc_html__( 'Hover', 'dethemekit-for-elementor' ),
			]
		);

		$elems->add_responsive_control(
			'sina_transform_effects_translateX_hover',
			[
				'label' => esc_html__( 'Translate X', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => -100,
						'max' => 100,
					],
				],
				'default' => [
					'size' => '0',
				],
				'condition' => [
					'sina_transform_effects' => 'translate',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_translateY_hover',
			[
				'label' => esc_html__( 'Translate Y', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => -100,
						'max' => 100,
					],
				],
				'default' => [
					'size' => '-10',
				],
				'condition' => [
					'sina_transform_effects' => 'translate',
				],
				'selectors' => [
					'(desktop){{WRAPPER}}:hover' => 'transform: translate({{sina_transform_effects_translateX_hover.SIZE || 0}}px, {{sina_transform_effects_translateY_hover.SIZE || 0}}px);',
					'(tablet){{WRAPPER}}:hover' => 'transform: translate({{sina_transform_effects_translateX_hover_tablet.SIZE || 0}}px, {{sina_transform_effects_translateY_hover_tablet.SIZE || 0}}px);',
					'(mobile){{WRAPPER}}:hover' => 'transform: translate({{sina_transform_effects_translateX_hover_mobile.SIZE || 0}}px, {{sina_transform_effects_translateY_hover_mobile.SIZE || 0}}px);',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_scaleX_hover',
			[
				'label' => esc_html__( 'Scale X', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 0.1,
						'min' => 0.1,
						'max' => 5,
					],
				],
				'default' => [
					'size' => '1.05',
				],
				'condition' => [
					'sina_transform_effects' => 'scaleX',
				],
				'selectors' => [
					'{{WRAPPER}}:hover' => 'transform: scaleX({{SIZE}});',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_scaleY_hover',
			[
				'label' => esc_html__( 'Scale Y', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 0.1,
						'min' => 0.1,
						'max' => 5,
					],
				],
				'default' => [
					'size' => '1.05',
				],
				'condition' => [
					'sina_transform_effects' => 'scaleY',
				],
				'selectors' => [
					'{{WRAPPER}}:hover' => 'transform: scaleY({{SIZE}});',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_scaleZ_hover',
			[
				'label' => esc_html__( 'Scale Z', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 0.1,
						'min' => 0.1,
						'max' => 5,
					],
				],
				'default' => [
					'size' => '1.05',
				],
				'condition' => [
					'sina_transform_effects' => 'scaleZ',
				],
				'selectors' => [
					'{{WRAPPER}}:hover' => 'transform: scale({{SIZE}});',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_rotateX_hover',
			[
				'label' => esc_html__( 'Rotate X', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 1,
						'min' => 0,
						'max' => 360,
					],
				],
				'default' => [
					'size' => '15',
				],
				'condition' => [
					'sina_transform_effects' => 'rotateX',
				],
				'selectors' => [
					'{{WRAPPER}}:hover' => 'transform: rotateX({{SIZE}}deg);',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_rotateY_hover',
			[
				'label' => esc_html__( 'Rotate Y', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 1,
						'min' => 0,
						'max' => 360,
					],
				],
				'default' => [
					'size' => '15',
				],
				'condition' => [
					'sina_transform_effects' => 'rotateY',
				],
				'selectors' => [
					'{{WRAPPER}}:hover' => 'transform: rotateY({{SIZE}}deg);',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_rotateZ_hover',
			[
				'label' => esc_html__( 'Rotate Z', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 1,
						'min' => 0,
						'max' => 360,
					],
				],
				'default' => [
					'size' => '5',
				],
				'condition' => [
					'sina_transform_effects' => 'rotateZ',
				],
				'selectors' => [
					'{{WRAPPER}}:hover' => 'transform: rotateZ({{SIZE}}deg);',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_skewX_hover',
			[
				'label' => esc_html__( 'Skew X', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 1,
						'min' => -60,
						'max' => 60,
					],
				],
				'default' => [
					'size' => '10',
				],
				'condition' => [
					'sina_transform_effects' => 'skewX',
				],
				'selectors' => [
					'{{WRAPPER}}:hover' => 'transform: skewX({{SIZE}}deg);',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_skewY_hover',
			[
				'label' => esc_html__( 'Skew Y', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 1,
						'min' => -60,
						'max' => 60,
					],
				],
				'default' => [
					'size' => '5',
				],
				'condition' => [
					'sina_transform_effects' => 'skewY',
				],
				'selectors' => [
					'{{WRAPPER}}:hover' => 'transform: skewY({{SIZE}}deg);',
				],
			]
		);
		$elems->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name' => 'sina_transform_effects_filters_hover',
				'selector' => '{{WRAPPER}}:hover',
			]
		);
		$elems->add_control(
			'sina_transform_effects_duration',
			[
				'label' => esc_html__( 'Transition Duration', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 100,
						'min' => 0,
						'max' => 10000,
					],
				],
				'default' => [
					'size' => '400',
				],
				'selectors' => [
					'{{WRAPPER}}' => 'transition: all {{SIZE}}ms;',
				],
			]
		);

		$elems->end_controls_tab();

		$elems->end_controls_tabs();
	}

	public function column_register_controls($elems) {
		$elems->add_control(
			'sina_is_morphing_animation',
			[
				'label' => '<strong>'.esc_html__( 'De Mask', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::SWITCHER,
				'prefix_class' => 'sina-morphing-anim-',
				'separator' => 'before',
			]
		);
		$elems->add_control(
			'sina_transform_effects',
			[
				'label' => '<strong>'.esc_html__( 'De Transform', 'dethemekit-for-elementor' ).'</strong>',
				'type' => Controls_Manager::SELECT,
				'options' => [
					'translate' => esc_html__( 'Translate', 'dethemekit-for-elementor' ),
					'scaleX' => esc_html__( 'Scale X', 'dethemekit-for-elementor' ),
					'scaleY' => esc_html__( 'Scale Y', 'dethemekit-for-elementor' ),
					'scaleZ' => esc_html__( 'Scale Z', 'dethemekit-for-elementor' ),
					'rotateX' => esc_html__( 'Rotate X', 'dethemekit-for-elementor' ),
					'rotateY' => esc_html__( 'Rotate Y', 'dethemekit-for-elementor' ),
					'rotateZ' => esc_html__( 'Rotate Z', 'dethemekit-for-elementor' ),
					'skewX' => esc_html__( 'Skew X', 'dethemekit-for-elementor' ),
					'skewY' => esc_html__( 'Skew Y', 'dethemekit-for-elementor' ),
					'none' => esc_html__( 'None', 'dethemekit-for-elementor' ),
				],
				'default' => 'none',
			]
		);
		$elems->add_responsive_control(
			'sina_transform_perspective',
			[
				'label' => esc_html__( 'Perspective Size', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 1,
						'min' => 0,
						'max' => 10000,
					],
				],
				'default' => [
					'size' => '1000',
				],
				'condition' => [
					'sina_transform_effects' => ['rotateX', 'rotateY'],
				],
				'selectors' => [
					'{{WRAPPER}}' => 'perspective: {{SIZE}}px;',
				],
			]
		);

		$elems->start_controls_tabs( 'sina_transform_effects_tabs' );

		$elems->start_controls_tab(
			'sina_transform_effects_normal',
			[
				'label' => esc_html__( 'Normal', 'dethemekit-for-elementor' ),
			]
		);

		$elems->add_responsive_control(
			'sina_transform_effects_translateX',
			[
				'label' => esc_html__( 'Translate X', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => -100,
						'max' => 100,
					],
				],
				'default' => [
					'size' => '0',
				],
				'condition' => [
					'sina_transform_effects' => 'translate',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_translateY',
			[
				'label' => esc_html__( 'Translate Y', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => -100,
						'max' => 100,
					],
				],
				'default' => [
					'size' => '0',
				],
				'condition' => [
					'sina_transform_effects' => 'translate',
				],
				'selectors' => [
					'(desktop){{WRAPPER}}' => 'transform: translate({{sina_transform_effects_translateX.SIZE || 0}}px, {{sina_transform_effects_translateY.SIZE || 0}}px);',
					'(tablet){{WRAPPER}}' => 'transform: translate({{sina_transform_effects_translateX_tablet.SIZE || 0}}px, {{sina_transform_effects_translateY_tablet.SIZE || 0}}px);',
					'(mobile){{WRAPPER}}' => 'transform: translate({{sina_transform_effects_translateX_mobile.SIZE || 0}}px, {{sina_transform_effects_translateY_mobile.SIZE || 0}}px);',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_scaleX',
			[
				'label' => esc_html__( 'Scale X', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 0.1,
						'min' => 0.1,
						'max' => 5,
					],
				],
				'default' => [
					'size' => '1',
				],
				'condition' => [
					'sina_transform_effects' => 'scaleX',
				],
				'selectors' => [
					'{{WRAPPER}}' => 'transform: scaleX({{SIZE}});',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_scaleY',
			[
				'label' => esc_html__( 'Scale Y', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 0.1,
						'min' => 0.1,
						'max' => 5,
					],
				],
				'default' => [
					'size' => '1',
				],
				'condition' => [
					'sina_transform_effects' => 'scaleY',
				],
				'selectors' => [
					'{{WRAPPER}}' => 'transform: scaleY({{SIZE}});',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_scaleZ',
			[
				'label' => esc_html__( 'Scale Z', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 0.1,
						'min' => 0.1,
						'max' => 5,
					],
				],
				'default' => [
					'size' => '1',
				],
				'condition' => [
					'sina_transform_effects' => 'scaleZ',
				],
				'selectors' => [
					'{{WRAPPER}}' => 'transform: scale({{SIZE}});',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_rotateX',
			[
				'label' => esc_html__( 'Rotate X', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 1,
						'min' => 0,
						'max' => 360,
					],
				],
				'default' => [
					'size' => '0',
				],
				'condition' => [
					'sina_transform_effects' => 'rotateX',
				],
				'selectors' => [
					'{{WRAPPER}}' => 'transform: rotateX({{SIZE}}deg);',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_rotateY',
			[
				'label' => esc_html__( 'Rotate Y', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 1,
						'min' => 0,
						'max' => 360,
					],
				],
				'default' => [
					'size' => '0',
				],
				'condition' => [
					'sina_transform_effects' => 'rotateY',
				],
				'selectors' => [
					'{{WRAPPER}}' => 'transform: rotateY({{SIZE}}deg);',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_rotateZ',
			[
				'label' => esc_html__( 'Rotate Z', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 1,
						'min' => 0,
						'max' => 360,
					],
				],
				'default' => [
					'size' => '0',
				],
				'condition' => [
					'sina_transform_effects' => 'rotateZ',
				],
				'selectors' => [
					'{{WRAPPER}}' => 'transform: rotateZ({{SIZE}}deg);',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_skewX',
			[
				'label' => esc_html__( 'Skew X', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 1,
						'min' => -60,
						'max' => 60,
					],
				],
				'default' => [
					'size' => '0',
				],
				'condition' => [
					'sina_transform_effects' => 'skewX',
				],
				'selectors' => [
					'{{WRAPPER}}' => 'transform: skewX({{SIZE}}deg);',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_skewY',
			[
				'label' => esc_html__( 'Skew Y', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 1,
						'min' => -60,
						'max' => 60,
					],
				],
				'default' => [
					'size' => '0',
				],
				'condition' => [
					'sina_transform_effects' => 'skewY',
				],
				'selectors' => [
					'{{WRAPPER}}' => 'transform: skewY({{SIZE}}deg);',
				],
			]
		);
		$elems->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name' => 'sina_transform_effects_filters',
				'selector' => '{{WRAPPER}}',
			]
		);

		$elems->end_controls_tab();

		$elems->start_controls_tab(
			'sina_transform_effects_hover',
			[
				'label' => esc_html__( 'Hover', 'dethemekit-for-elementor' ),
			]
		);

		$elems->add_responsive_control(
			'sina_transform_effects_translateX_hover',
			[
				'label' => esc_html__( 'Translate X', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => -100,
						'max' => 100,
					],
				],
				'default' => [
					'size' => '0',
				],
				'condition' => [
					'sina_transform_effects' => 'translate',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_translateY_hover',
			[
				'label' => esc_html__( 'Translate Y', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => -100,
						'max' => 100,
					],
				],
				'default' => [
					'size' => '-10',
				],
				'condition' => [
					'sina_transform_effects' => 'translate',
				],
				'selectors' => [
					'(desktop){{WRAPPER}}:hover' => 'transform: translate({{sina_transform_effects_translateX_hover.SIZE || 0}}px, {{sina_transform_effects_translateY_hover.SIZE || 0}}px);',
					'(tablet){{WRAPPER}}:hover' => 'transform: translate({{sina_transform_effects_translateX_hover_tablet.SIZE || 0}}px, {{sina_transform_effects_translateY_hover_tablet.SIZE || 0}}px);',
					'(mobile){{WRAPPER}}:hover' => 'transform: translate({{sina_transform_effects_translateX_hover_mobile.SIZE || 0}}px, {{sina_transform_effects_translateY_hover_mobile.SIZE || 0}}px);',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_scaleX_hover',
			[
				'label' => esc_html__( 'Scale X', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 0.1,
						'min' => 0.1,
						'max' => 5,
					],
				],
				'default' => [
					'size' => '1.05',
				],
				'condition' => [
					'sina_transform_effects' => 'scaleX',
				],
				'selectors' => [
					'{{WRAPPER}}:hover' => 'transform: scaleX({{SIZE}});',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_scaleY_hover',
			[
				'label' => esc_html__( 'Scale Y', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 0.1,
						'min' => 0.1,
						'max' => 5,
					],
				],
				'default' => [
					'size' => '1.05',
				],
				'condition' => [
					'sina_transform_effects' => 'scaleY',
				],
				'selectors' => [
					'{{WRAPPER}}:hover' => 'transform: scaleY({{SIZE}});',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_scaleZ_hover',
			[
				'label' => esc_html__( 'Scale Z', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 0.1,
						'min' => 0.1,
						'max' => 5,
					],
				],
				'default' => [
					'size' => '1.05',
				],
				'condition' => [
					'sina_transform_effects' => 'scaleZ',
				],
				'selectors' => [
					'{{WRAPPER}}:hover' => 'transform: scale({{SIZE}});',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_rotateX_hover',
			[
				'label' => esc_html__( 'Rotate X', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 1,
						'min' => 0,
						'max' => 360,
					],
				],
				'default' => [
					'size' => '15',
				],
				'condition' => [
					'sina_transform_effects' => 'rotateX',
				],
				'selectors' => [
					'{{WRAPPER}}:hover' => 'transform: rotateX({{SIZE}}deg);',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_rotateY_hover',
			[
				'label' => esc_html__( 'Rotate Y', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 1,
						'min' => 0,
						'max' => 360,
					],
				],
				'default' => [
					'size' => '15',
				],
				'condition' => [
					'sina_transform_effects' => 'rotateY',
				],
				'selectors' => [
					'{{WRAPPER}}:hover' => 'transform: rotateY({{SIZE}}deg);',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_rotateZ_hover',
			[
				'label' => esc_html__( 'Rotate Z', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 1,
						'min' => 0,
						'max' => 360,
					],
				],
				'default' => [
					'size' => '5',
				],
				'condition' => [
					'sina_transform_effects' => 'rotateZ',
				],
				'selectors' => [
					'{{WRAPPER}}:hover' => 'transform: rotateZ({{SIZE}}deg);',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_skewX_hover',
			[
				'label' => esc_html__( 'Skew X', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 1,
						'min' => -60,
						'max' => 60,
					],
				],
				'default' => [
					'size' => '10',
				],
				'condition' => [
					'sina_transform_effects' => 'skewX',
				],
				'selectors' => [
					'{{WRAPPER}}:hover' => 'transform: skewX({{SIZE}}deg);',
				],
			]
		);
		$elems->add_responsive_control(
			'sina_transform_effects_skewY_hover',
			[
				'label' => esc_html__( 'Skew Y', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 1,
						'min' => -60,
						'max' => 60,
					],
				],
				'default' => [
					'size' => '5',
				],
				'condition' => [
					'sina_transform_effects' => 'skewY',
				],
				'selectors' => [
					'{{WRAPPER}}:hover' => 'transform: skewY({{SIZE}}deg);',
				],
			]
		);
		$elems->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name' => 'sina_transform_effects_filters_hover',
				'selector' => '{{WRAPPER}}:hover',
			]
		);
		$elems->add_control(
			'sina_transform_effects_duration',
			[
				'label' => esc_html__( 'Transition Duration', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'step' => 100,
						'min' => 0,
						'max' => 10000,
					],
				],
				'default' => [
					'size' => '400',
				],
				'selectors' => [
					'{{WRAPPER}}' => 'transition: all {{SIZE}}ms;',
				],
			]
		);

		$elems->end_controls_tab();

		$elems->end_controls_tabs();
	}
}
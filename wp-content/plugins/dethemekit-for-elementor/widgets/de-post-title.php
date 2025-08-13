<?php
namespace DethemeKit\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



/**
 * DT Post Title
 *
 * Single post/page title element for elementor.
 *
 * @since 1.0.0
 */
class De_Post_Title extends Widget_Base {

	const ALLOWED_HTML_WRAPPER_TAGS = [
		
		'h1',
		'h2',
		'h3',
		'h4',
		'h5',
		'h6',
		'p',
		'div',
		'span',
	];

	public function get_name() {
		return 'dt-post-title';
	}

	public function get_title() {

		return __( 'De Page/Post Title', 'dethemekit-for-elementor' );

	}

	public function get_icon() {
		return 'eicon-site-title';
	}

	public function get_categories() {
		return [ 'dethemekit-elements' ];
	}

	/**
	 * Validate an HTML tag against a safe allowed list.
	 *
	 * @param string $tag
	 *
	 * @return string
	 */
	protected function validate_html_tag( $tag ) {
		
		return in_array( strtolower( $tag ), self::ALLOWED_HTML_WRAPPER_TAGS ) ? $tag : 'div';
	}

	protected function register_controls() {

		$post_type_object = get_post_type_object( get_post_type() );

		$this->start_controls_section(
			'section_content',
			[
				'label' => sprintf(
					/* translators: %s: Post type singular name (e.g. Post or Page) */
					__( '%s Title', 'dethemekit-for-elementor' ),
					$post_type_object->labels->singular_name
				),
			]
		);

		$this->add_control(
			'de_html_tag',
			[
				'label' => __( 'HTML Tag', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'h1' => 'H1',
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'h5' => 'H5',
					'h6' => 'H6',
					'p' => 'p',
					'div' => 'div',
					'span' => 'span',
				],
				'default' => 'h2',
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label' => __( 'Alignment', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => __( 'Left', 'dethemekit-for-elementor' ),
						'icon' => 'fa fa-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'dethemekit-for-elementor' ),
						'icon' => 'fa fa-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'dethemekit-for-elementor' ),
						'icon' => 'fa fa-align-right',
					],
					'justify' => [
						'title' => __( 'Justified', 'dethemekit-for-elementor' ),
						'icon' => 'fa fa-align-justify',
					],
				],
				'default' => '',
				'selectors' => [
					'{{WRAPPER}}' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'link_to',
			[
				'label' => __( 'Link to', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'none',
				'options' => [
					'none' => __( 'None', 'dethemekit-for-elementor' ),
					'home' => __( 'Home URL', 'dethemekit-for-elementor' ),
					'post' => sprintf(
						/* translators: %s: Post type singular name (e.g. Post or Page) */
						__( '%s URL', 'dethemekit-for-elementor' ),
						$post_type_object->labels->singular_name
					),
					'custom' => __( 'Custom URL', 'dethemekit-for-elementor' ),
				],
			]
		);

		$this->add_control(
			'link',
			[
				'label' => __( 'Link', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::URL,
				'placeholder' => __( 'https://your-link.com', 'dethemekit-for-elementor' ),
				'condition' => [
					'link_to' => 'custom',
				],
				'default' => [
					'url' => '',
				],
				'show_label' => false,
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => sprintf(
					/* translators: %s: Post type singular name (e.g. Post or Page) */
					__( '%s Title', 'dethemekit-for-elementor' ),
					$post_type_object->labels->singular_name
				),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'color',
			[
				'label' => __( 'Text Color', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_PRIMARY,
				],
				'selectors' => [
					'{{WRAPPER}} .dt-post-title' => 'color: {{VALUE}};',
					'{{WRAPPER}} .dt-post-title a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'typography',
				'global'    => [
					'default'   => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .dt-post-title',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'text_shadow',
				'selector' => '{{WRAPPER}} .dt-post-title',
			]
		);

		$this->add_control(
			'hover_animation',
			[
				'label' => __( 'Hover Animation', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::HOVER_ANIMATION,
			]
		);

		$this->end_controls_section();

	}

	protected function render() {
		$title = get_the_title();

		if ( empty( $title ) )
			return;

		$settings = $this->get_settings();

		switch ( $settings['link_to'] ) {
			case 'custom' :
				if ( ! empty( $settings['link']['url'] ) ) {
					$link = esc_url( $settings['link']['url'] );
				} else {
					$link = false;
				}
				break;

			case 'post' :
				$link = esc_url( get_the_permalink() );
				break;

			case 'home' :
				$link = esc_url( get_home_url() );
				break;

			case 'none' :
			default:
				$link = false;
				break;
		}
		$target = $settings['link']['is_external'] ? 'target="_blank"' : '';

		$animation_class = ! empty( $settings['hover_animation'] ) ? 'elementor-animation-' . $settings['hover_animation'] : '';

		$html = sprintf( '<%1$s class="dt-post-title %2$s">', $this->validate_html_tag( $settings['de_html_tag'] ), sanitize_html_class( $animation_class ) );
		if ( $link ) {
			$html .= sprintf( '<a href="%1$s" %2$s>%3$s</a>', $link, $target, $title );
		} else {
			$html .= $title;
		}
		$html .= sprintf( '</%s>', $this->validate_html_tag( $settings['de_html_tag'] ) );

		echo $html;
	}

	protected function content_template() {
		?>
		<#
			var title = "<?php echo get_the_title(); ?>";

			var link_url;
			switch( settings.link_to ) {
				case 'custom':
					link_url = settings.link.url;
					break;
				case 'post':
					link_url = '<?php echo esc_url( get_the_permalink() ); ?>';
					break;
				case 'home':
					link_url = '<?php echo esc_url( get_home_url() ); ?>';
					break;
				case 'none':
				default:
					link_url = false;
			}
			var target = settings.link.is_external ? 'target="_blank"' : '';

			var animation_class = '';
			if ( '' !== settings.hover_animation ) {
				animation_class = 'elementor-animation-' + settings.hover_animation;
			}

			var html = '<' + elementor.helpers.validateHTMLTag( settings.de_html_tag ) + ' class="dt-post-title ' + animation_class + '">';
			if ( link_url ) {
				html += '<a href="' + link_url + '" ' + target + '>' + title + '</a>';
			} else {
				html += title;
			}
			html += '</' + elementor.helpers.validateHTMLTag( settings.de_html_tag ) + '>';

			print( html );
		#>
		<?php
	}
}

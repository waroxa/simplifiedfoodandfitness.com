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
 * Press Elements Post Terms
 *
 * Single post/page terms element for elementor.
 *
 * @since 1.1.0
 */
class De_Post_Terms extends Widget_Base {

	public function get_name() {
		return 'post-terms';
	}

	public function get_title() {
		$post_type_object = get_post_type_object( get_post_type() );

		return sprintf(
			/* translators: %s: Post type singular name (e.g. Post or Page) */
			__( '%s Terms', 'dethemekit-for-elementor' ),
			$post_type_object->labels->singular_name
		);
	}

	public function get_icon() {
		return 'eicon-sitemap';
	}

	public function get_categories() {
		return [ 'dethemekit-elements' ];
	}

	protected function register_controls() {

		$post_type_object = get_post_type_object( get_post_type() );

		$this->start_controls_section(
			'section_content',
			[
				'label' => sprintf(
					/* translators: %s: Post type singular name (e.g. Post or Page) */
					__( '%s Terms', 'dethemekit-for-elementor' ),
					$post_type_object->labels->singular_name
				),
			]
		);

		$this->add_control(
			'taxonomy',
			[
				'label' => __( 'Taxonomy', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SELECT,
				//'options' => get_post_taxonomies( $post->ID ),
				'options' => get_taxonomies( array( 'public' => true ) ),
				'default' => 'category',
			]
		);

		$this->add_control(
			'separator',
			[
				'label' => __( 'Separator', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::TEXT,
				'default' => ', ',
			]
		);

		$this->add_control(
			'html_tag',
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
				'default' => 'p',
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
					'term' => __( 'Term', 'dethemekit-for-elementor' ),
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => sprintf(
					/* translators: %s: Post type singular name (e.g. Post or Page) */
					__( '%s Terms', 'dethemekit-for-elementor' ),
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
					'{{WRAPPER}} .press-elements-terms' => 'color: {{VALUE}};',
					'{{WRAPPER}} .press-elements-terms a' => 'color: {{VALUE}};',
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
				'selector' => '{{WRAPPER}} .press-elements-terms',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'text_shadow',
				'selector' => '{{WRAPPER}} .press-elements-terms',
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
		global $post;
		$settings = $this->get_settings();

		$taxonomy = $settings['taxonomy'];
		if ( empty( $taxonomy ) )
			return;

		$term_list = get_the_terms( $post->ID, $taxonomy );
		if ( empty( $term_list ) || is_wp_error( $term_list ) )
			return;

		$allowed_tags = array( 'h1','h2','h3','h4','h5','h6','p','div','span' );
		$html_tag = in_array( $settings['html_tag'], $allowed_tags ) ? $settings['html_tag'] : 'p';
	
		$animation_class = ! empty( $settings['hover_animation'] ) ? 'elementor-animation-' . $settings['hover_animation'] : '';

		$html = sprintf( '<%1$s class="press-elements-terms %2$s">', $html_tag, sanitize_html_class( $animation_class ) );
		switch ( $settings['link_to'] ) {
			case 'term' :
				foreach ( $term_list as $term ) {
					$html .= sprintf( '<a href="%1$s">%2$s</a>%3$s', esc_url( get_term_link( $term ) ), $term->name, $settings['separator'] );
				}
				break;

			case 'none' :
			default:
				foreach ( $term_list as $term ) {
					$html .= $term->name . $settings['separator'];
				}
				break;
		}
		$html = substr( $html, 0, -2);
		$html .= sprintf( '</%s>', $html_tag );

		echo $html;
	}

	protected function content_template() {
		global $post;
		?>
		<#
			var taxonomy = settings.taxonomy;

			var all_terms = [];
			<?php
			$taxonomies = get_taxonomies( array( 'public' => true ) );
			foreach ( $taxonomies as $taxonomy ) {
				printf( 'all_terms["%1$s"] = [];', $taxonomy );
				$terms = get_the_terms( $post->ID, $taxonomy );
				if ( $terms ) {
					$i = 0;
					foreach ( $terms as $term ) {
						printf( 'all_terms["%1$s"][%2$s] = [];', $taxonomy, $i );
						printf( 'all_terms["%1$s"][%2$s] = { slug: "%3$s", name: "%4$s", url: "%5$s" };', $taxonomy, $i, $term->slug, $term->name, esc_url( get_term_link( $term ) ) );
						$i++;
					}
				}
			}
			?>
			var post_terms = all_terms[ settings.taxonomy ];

			var terms = '';
			var i = 0;

			switch( settings.link_to ) {
				case 'term':
					while ( all_terms[ settings.taxonomy ][i] ) {
						terms += "<a href='" + all_terms[ settings.taxonomy ][i].url + "'>" + all_terms[ settings.taxonomy ][i].name + "</a>" + settings.separator;
						i++;
					}
					break;
				case 'none':
				default:
					while ( all_terms[ settings.taxonomy ][i] ) {
						terms += all_terms[ settings.taxonomy ][i].name + settings.separator;
						i++;
					}
					break;
			}
			terms = terms.slice(0, terms.length-2);

			var animation_class = '';
			if ( '' !== settings.hover_animation ) {
				animation_class = 'elementor-animation-' + settings.hover_animation;
			}

			var valid_html_tag;
			switch( settings.html_tag ) {
				case 'h1':
					valid_html_tag = settings.html_tag;
					break;
				case 'h2':
					valid_html_tag = settings.html_tag;
					break;
				case 'h3':
					valid_html_tag = settings.html_tag;
					break;
				case 'h4':
					valid_html_tag = settings.html_tag;
					break;
				case 'h5':
					valid_html_tag = settings.html_tag;
					break;
				case 'h6':
					valid_html_tag = settings.html_tag;
					break;
				case 'div':
					valid_html_tag = settings.html_tag;
					break;
				case 'span':
					valid_html_tag = settings.html_tag;
					break;
				default:
					valid_html_tag = 'p';
			}

			var html = '<' + valid_html_tag + ' class="press-elements-terms ' + animation_class + '">';
			html += terms;
			html += '</' + valid_html_tag + '>';

			print( html );
		#>
		<?php
	}
}

<?php
namespace DethemeKit\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



/**
 * Press Elements Post Author
 *
 * Single post/page author element for elementor.
 *
 * @since 1.0.0
 */
class De_Post_Author extends Widget_Base {

	public function get_name() {
		return 'post-author';
	}

	public function get_title() {
		$post_type_object = get_post_type_object( get_post_type() );

		return sprintf(
			/* translators: %s: Post type singular name (e.g. Post or Page) */
			__( '%s Author', 'dethemekit-for-elementor' ),
			$post_type_object->labels->singular_name
		);
	}

	public function get_icon() {
		return 'eicon-person';
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
					__( '%s Author', 'dethemekit-for-elementor' ),
					$post_type_object->labels->singular_name
				),
			]
		);

		$this->add_control(
			'author',
			[
				'label' => __( 'Author', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SELECT,
				'options' => $this->user_fields_labels(),
				'default' => 'display_name',
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
					'home' => __( 'Home URL', 'dethemekit-for-elementor' ),
					'post' => sprintf(
						/* translators: %s: Post type singular name (e.g. Post or Page) */
						__( '%s URL', 'dethemekit-for-elementor' ),
						$post_type_object->labels->singular_name
					),
					'author' => __( 'Author URL', 'dethemekit-for-elementor' ),
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
					__( '%s Author', 'dethemekit-for-elementor' ),
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
					'{{WRAPPER}} .detheme-kit-author' => 'color: {{VALUE}};',
					'{{WRAPPER}} .detheme-kit-author a' => 'color: {{VALUE}};',
				],
				'condition' => [
					'author!' => 'image',
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
				'selector' => '{{WRAPPER}} .detheme-kit-author',
				'condition' => [
					'author!' => 'image',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'text_shadow',
				'selector' => '{{WRAPPER}} .detheme-kit-author',
				'condition' => [
					'author!' => 'image',
				],
			]
		);

		$this->add_responsive_control(
			'space',
			[
				'label' => __( 'Size (%)', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 100,
					'unit' => '%',
				],
				'size_units' => [ '%' ],
				'range' => [
					'%' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .detheme-kit-author img' => 'max-width: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'author' => 'image',
				],
			]
		);

		$this->add_responsive_control(
			'opacity',
			[
				'label' => __( 'Opacity (%)', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 1,
				],
				'range' => [
					'px' => [
						'max' => 1,
						'min' => 0.10,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .detheme-kit-author img' => 'opacity: {{SIZE}};',
				],
				'condition' => [
					'author' => 'image',
				],
			]
		);

		$this->add_control(
			'angle',
			[
				'label' => __( 'Angle (deg)', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'deg' ],
				'default' => [
					'unit' => 'deg',
					'size' => 0,
				],
				'range' => [
					'deg' => [
						'max' => 360,
						'min' => -360,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .detheme-kit-author img' => '-webkit-transform: rotate({{SIZE}}deg); -moz-transform: rotate({{SIZE}}deg); -ms-transform: rotate({{SIZE}}deg); -o-transform: rotate({{SIZE}}deg); transform: rotate({{SIZE}}deg);',
				],
				'condition' => [
					'author' => 'image',
				],
			]
		);

		$this->add_control(
			'hover_animation',
			[
				'label' => __( 'Hover Animation', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::HOVER_ANIMATION,
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'image_border',
				'label' => __( 'Image Border', 'dethemekit-for-elementor' ),
				'selector' => '{{WRAPPER}} .detheme-kit-author img',
				'condition' => [
					'author' => 'image',
				],
			]
		);

		$this->add_control(
			'image_border_radius',
			[
				'label' => __( 'Border Radius', 'dethemekit-for-elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .detheme-kit-author img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'author' => 'image',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'image_box_shadow',
				'selector' => '{{WRAPPER}} .detheme-kit-author img',
				'condition' => [
					'author' => 'image',
				],
			]
		);

		$this->end_controls_section();

	}

	protected function render() {
		$settings = $this->get_settings();

		if ( empty( $settings['author'] ) )
			return;

		$author = $this->user_data( $settings['author'] );

		switch ( $settings['link_to'] ) {
			case 'custom' :
				if ( ! empty( $settings['link']['url'] ) ) {
					$link = esc_url( $settings['link']['url'] );
				} else {
					$link = false;
				}
				break;

			case 'author' :
				$link = esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) );
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

		$allowed_tags = array( 'h1','h2','h3','h4','h5','h6','p','div','span' );
		$html_tag = in_array( $settings['html_tag'], $allowed_tags ) ? $settings['html_tag'] : 'p';

		$animation_class = ! empty( $settings['hover_animation'] ) ? 'elementor-animation-' . $settings['hover_animation'] : '';

		$html = sprintf( '<%1$s class="detheme-kit-author %2$s">', $html_tag, sanitize_html_class( $animation_class ) );
		if ( $link ) {
			$html .= sprintf( '<a href="%1$s" %2$s>%3$s</a>', $link, $target, $author );
		} else {
			$html .= $author;
		}
		$html .= sprintf( '</%s>', $html_tag );

		echo $html;
	}

	protected function content_template() {
		?>
		<#
			var author_data = [];
			<?php
			foreach ( $this->user_data() as $key => $value ) {
				printf( 'author_data[ "%1$s" ] = "%2$s";', $key, $value );
			}
			?>
			var author = author_data[ settings.author ];

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

			var link_url;
			switch( settings.link_to ) {
				case 'custom':
					link_url = settings.link.url;
					break;
				case 'author':
					link_url = '<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>';
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

			var html = '<' + valid_html_tag + ' class="detheme-kit-author ' + animation_class + '">';
			if ( link_url ) {
				html += '<a href="' + link_url + '" ' + target + '>' + author + '</a>';
			} else {
				html += author;
			}
			html += '</' + valid_html_tag + '>';

			print( html );
		#>
		<?php
	}

	protected function user_fields_labels() {

		$fields = [
			'first_name'   => __( 'First Name', 'dethemekit-for-elementor' ),
			'last_name'    => __( 'Last Name', 'dethemekit-for-elementor' ),
			'first_last'   => __( 'First Name + Last Name', 'dethemekit-for-elementor' ),
			'last_first'   => __( 'Last Name + First Name', 'dethemekit-for-elementor' ),
			'nickname'     => __( 'Nick Name', 'dethemekit-for-elementor' ),
			'display_name' => __( 'Display Name', 'dethemekit-for-elementor' ),
			'user_login'   => __( 'User Name', 'dethemekit-for-elementor' ),
			'description'  => __( 'User Bio', 'dethemekit-for-elementor' ),
			'image'        => __( 'User Image', 'dethemekit-for-elementor' ),
		];

		return $fields;

	}

	protected function user_data( $selected = '' ) {

		global $post;

		$author_id = $post->post_author;

		$fields = [
			'first_name'   => get_the_author_meta( 'first_name', $author_id ),
			'last_name'    => get_the_author_meta( 'last_name', $author_id ),
			'first_last'   => sprintf( '%s %s', get_the_author_meta( 'first_name', $author_id ), get_the_author_meta( 'last_name', $author_id ) ),
			'last_first'   => sprintf( '%s %s', get_the_author_meta( 'last_name', $author_id ), get_the_author_meta( 'first_name', $author_id ) ),
			'nickname'     => get_the_author_meta( 'nickname', $author_id ),
			'display_name' => get_the_author_meta( 'display_name', $author_id ),
			'user_login'   => get_the_author_meta( 'user_login', $author_id ),
			'description'  => get_the_author_meta( 'description', $author_id ),
			'image'        => get_avatar( get_the_author_meta( 'email', $author_id ), 256 ),
		];

		if ( empty( $selected ) ) {
			// Return the entire array
			return $fields;
		} else {
			// Return only the selected field
			return $fields[ $selected ];
		}

	}

}

<?php
namespace DethemeKit\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



/**
 * Press Elements Post Comments
 *
 * Single post/page comments element for elementor.
 *
 * @since 1.1.0
 */
class De_Post_Comments extends Widget_Base {

	public function get_name() {
		return 'post-comments';
	}

	public function get_title() {
		$post_type_object = get_post_type_object( get_post_type() );

		return sprintf(
			/* translators: %s: Post type singular name (e.g. Post or Page) */
			__( '%s Comments', 'dethemekit-for-elementor' ),
			$post_type_object->labels->singular_name
		);
	}

	public function get_icon() {
		return 'eicon-comments';
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
					__( '%s Comments', 'dethemekit-for-elementor' ),
					$post_type_object->labels->singular_name
				),
			]
		);

		$this->add_control(
			'info',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw' => __( 'This widget displays the default Comments Template included in the current Theme.', 'dethemekit-for-elementor' ) .
						'<br><br>' .
						__( 'No custom styling can be applied as each theme uses it\'s own CSS classes and IDs.', 'dethemekit-for-elementor' ),
				'content_classes' => 'elementor-descriptor',
			]
		);

		$this->end_controls_section();

	}

	protected function render() {
		comments_template();
	}

	protected function content_template() {
		comments_template();
	}
}

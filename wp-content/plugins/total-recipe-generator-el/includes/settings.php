<?php
/**
 * TRG Settings Page in Admin
 *
 * Uses TRG_Settings_API Class
 */

if ( ! class_exists( 'Generate_TRG_Settings' ) ) :

	class Generate_TRG_Settings {

		private $settings_api;

		function __construct() {
			$this->settings_api = new TRG_Settings_API;

			add_action( 'admin_init', array($this, 'admin_init') );
			add_action( 'admin_menu', array($this, 'admin_menu') );
		}

		function admin_init() {

			//set the settings
			$this->settings_api->set_sections( $this->get_settings_sections() );
			$this->settings_api->set_fields( $this->get_settings_fields() );

			//initialize settings
			$this->settings_api->admin_init();
		}

		function admin_menu() {
			add_options_page( 'Total Recipe Generator Settings', 'Total Recipe Generator', 'manage_options', 'trg_settings', array($this, 'plugin_page') );
		}

		function get_settings_sections() {
			$sections = array(
				array(
					'id' => 'trg_adspots',
					'title' => esc_attr__( 'Ad Spots', 'trg_el' )
				),
				array(
					'id' => 'trg_display',
					'title' => esc_attr__( 'Display', 'trg_el' )
				),
				array(
					'id' => 'trg_social',
					'title' => esc_attr__( 'Social', 'trg_el' )
				)
			);
			return $sections;
		}

		/**
		 * Returns all the settings fields
		 *
		 * @return array settings fields
		 */
		function get_settings_fields() {
			$settings_fields = array(
				'trg_adspots' => array(
					array(
						'name'              => 'ad_spot_1',
						'label'             => esc_attr__( 'Global ad spot 1', 'trg_el' ),
						'desc'              => esc_attr__( 'Global ad spot to be shown above ingredients section. Individual recipe post can override this setting', 'trg_el' ),
						'type'              => 'textarea',
						'default'           => ''
					),

					array(
						'name'              => 'ad_spot_2',
						'label'             => esc_attr__( 'Global ad spot 2', 'trg_el' ),
						'desc'              => esc_attr__( 'Global ad spot to be shown above methods section. Individual recipe post can override this setting', 'trg_el' ),
						'type'              => 'textarea',
						'default'           => ''
					),

					array(
						'name'              => 'ad_spot_3',
						'label'             => esc_attr__( 'Global ad spot 3', 'trg_el' ),
						'desc'              => esc_attr__( 'Global ad spot to be shown after nutrition table. Individual recipe post can override this setting', 'trg_el' ),
						'type'              => 'textarea',
						'default'           => ''
					)
				),
				'trg_display' => array(

					array(
						'name'              => 'prep_time_label',
						'label'             => esc_attr__( 'Prep Time Label', 'trg_el' ),
						'desc'              => esc_attr__( 'Provide a label for preparation time', 'trg_el' ),
						'type'              => 'text',
						'default'           => __( 'Prep Time', 'trg_el' )
					),

					array(
						'name'              => 'cook_time_label',
						'label'             => esc_attr__( 'Cook Time Label', 'trg_el' ),
						'desc'              => esc_attr__( 'Provide a label for cooking time', 'trg_el' ),
						'type'              => 'text',
						'default'           => __( 'Cook Time', 'trg_el' )
					),

					array(
						'name'              => 'perform_time_label',
						'label'             => esc_attr__( 'Perform Time Label', 'trg_el' ),
						'desc'              => esc_attr__( 'Provide a label for perform time', 'trg_el' ),
						'type'              => 'text',
						'default'           => __( 'Perform Time', 'trg_el' )
					),

					array(
						'name'              => 'total_time_label',
						'label'             => esc_attr__( 'Total Time Label', 'trg_el' ),
						'desc'              => esc_attr__( 'Provide a label for total time', 'trg_el' ),
						'type'              => 'text',
						'default'           => __( 'Total Time', 'trg_el' )
					),

					array(
						'name'              => 'ready_in_label',
						'label'             => esc_attr__( 'Ready In Label', 'trg_el' ),
						'desc'              => esc_attr__( 'Provide a label for ready in time', 'trg_el' ),
						'type'              => 'text',
						'default'           => __( 'Ready in', 'trg_el' )
					),

					array(
						'name'              => 'yield_label',
						'label'             => esc_attr__( 'Recipe Yield Label', 'trg_el' ),
						'desc'              => esc_attr__( 'Provide a label for recipe Yield', 'trg_el' ),
						'type'              => 'text',
						'default'           => __( 'Yield', 'trg_el' )
					),

					array(
						'name'              => 'serving_size_label',
						'label'             => esc_attr__( 'Serving Size Label', 'trg_el' ),
						'desc'              => esc_attr__( 'Provide a label for serving size', 'trg_el' ),
						'type'              => 'text',
						'default'           => __( 'Serving Size', 'trg_el' )
					),

					array(
						'name'              => 'energy_label',
						'label'             => esc_attr__( 'Energy Label', 'trg_el' ),
						'desc'              => esc_attr__( 'Provide a label for Energy', 'trg_el' ),
						'type'              => 'text',
						'default'           => __( 'Energy', 'trg_el' )
					),

					array(
						'name'              => 'total_cost_label',
						'label'             => esc_attr__( 'Total Cost Label', 'trg_el' ),
						'desc'              => esc_attr__( 'Provide a label for Total Cost', 'trg_el' ),
						'type'              => 'text',
						'default'           => __( 'Total Cost', 'trg_el' )
					),

					array(
						'name'              => 'cost_per_serving_label',
						'label'             => esc_attr__( 'Cost per Serving Label', 'trg_el' ),
						'desc'              => esc_attr__( 'Provide a label for Cost per Serving', 'trg_el' ),
						'type'              => 'text',
						'default'           => __( 'Cost per Serving', 'trg_el' )
					),

					array(
						'name'              => 'cuisine_label',
						'label'             => esc_attr__( 'Cuisine Label', 'trg_el' ),
						'desc'              => esc_attr__( 'Provide a label for Cuisine', 'trg_el' ),
						'type'              => 'text',
						'default'           => __( 'Cuisine', 'trg_el' )
					),

					array(
						'name'              => 'course_label',
						'label'             => esc_attr__( 'Course Label', 'trg_el' ),
						'desc'              => esc_attr__( 'Provide a label for Course', 'trg_el' ),
						'type'              => 'text',
						'default'           => __( 'Course', 'trg_el' )
					),

					array(
						'name'              => 'cooking_method_label',
						'label'             => esc_attr__( 'Cuisine Label', 'trg_el' ),
						'desc'              => esc_attr__( 'Provide a label for Cooking Method', 'trg_el' ),
						'type'              => 'text',
						'default'           => __( 'Cooking Method', 'trg_el' )
					),

					array(
						'name'              => 'sfd_label',
						'label'             => esc_attr__( 'Suitable for Diet Label', 'trg_el' ),
						'desc'              => esc_attr__( 'Provide a label for Suitable for Diet', 'trg_el' ),
						'type'              => 'text',
						'default'           => __( 'Suitable for Diet', 'trg_el' )
					),

					array(
						'name'              => 'ing_heading',
						'label'             => esc_attr__( 'Ingredients heading', 'trg_el' ),
						'desc'              => esc_attr__( 'Provide a text for ingredients heading', 'trg_el' ),
						'type'              => 'text',
						'default'           => __( 'Ingredients', 'trg_el' )
					),

					array(
						'name'              => 'method_heading',
						'label'             => esc_attr__( 'Method heading', 'trg_el' ),
						'desc'              => esc_attr__( 'Provide a text for method heading', 'trg_el' ),
						'type'              => 'text',
						'default'           => __( 'Method', 'trg_el' )
					),

					array(
						'name'              => 'nutri_heading',
						'label'             => esc_attr__( 'Nutrition Facts heading', 'trg_el' ),
						'desc'              => esc_attr__( 'Provide a text for Nutrition Facts heading', 'trg_el' ),
						'type'              => 'text',
						'default'           => __( 'Nutrition Facts', 'trg_el' )
					),

					array(
						'name'    => 'icon_color',
						'label'   => esc_attr__( 'Icons Color', 'trg_el' ),
						'desc'    => esc_attr__( 'Choose a color for heading icons', 'trg_el' ),
						'type'    => 'color'
					),

					array(
						'name'    => 'heading_color',
						'label'   => esc_attr__( 'Heading Color', 'trg_el' ),
						'desc'    => esc_attr__( 'Choose a color for headings', 'trg_el' ),
						'type'    => 'color'
					),

					array(
						'name'    => 'tags_bg',
						'label'   => esc_attr__( 'Tag links background', 'trg_el' ),
						'desc'    => esc_attr__( 'Choose a background color for tag links', 'trg_el' ),
						'type'    => 'color'
					),

					array(
						'name'    => 'tags_color',
						'label'   => esc_attr__( 'Tag links foreground', 'trg_el' ),
						'desc'    => esc_attr__( 'Choose a foreground text color for tag links', 'trg_el' ),
						'type'    => 'color'
					),

					array(
						'name'    => 'tags_bg_hover',
						'label'   => esc_attr__( 'Tag links background hover', 'trg_el' ),
						'desc'    => esc_attr__( 'Choose a hover background color for tag links', 'trg_el' ),
						'type'    => 'color'
					),

					array(
						'name'    => 'tags_color_hover',
						'label'   => esc_attr__( 'Tag links hover color', 'trg_el' ),
						'desc'    => esc_attr__( 'Choose a hover color for tag links', 'trg_el' ),
						'type'    => 'color'
					),

					array(
						'name'    => 'label_color',
						'label'   => esc_attr__( 'Text labels color', 'trg_el' ),
						'desc'    => esc_attr__( 'Choose a color for text labels in recipe meta', 'trg_el' ),
						'type'    => 'color'
					),

					array(
						'name'    => 'highlights',
						'label'   => esc_attr__( 'Text highlights color', 'trg_el' ),
						'desc'    => esc_attr__( 'Choose a highlight color for text in recipe meta', 'trg_el' ),
						'type'    => 'color'
					),

					array(
						'name'    => 'count_color',
						'label'   => esc_attr__( 'Color for number count', 'trg_el' ),
						'desc'    => esc_attr__( 'Choose a color for number count in recipe method', 'trg_el' ),
						'type'    => 'color'
					),

					array(
						'name'    => 'tick_color',
						'label'   => esc_attr__( 'Ingredients tick color', 'trg_el' ),
						'desc'    => esc_attr__( 'Choose a color for tick icon in ingredients section', 'trg_el' ),
						'type'    => 'color'
					),

					array(
						'name'    => 'social_color',
						'label'   => esc_attr__( 'Social links color', 'trg_el' ),
						'desc'    => esc_attr__( 'Choose a foreground color for Social links', 'trg_el' ),
						'type'    => 'color'
					),

					array(
						'name'    => 'social_bg',
						'label'   => esc_attr__( 'Social links background', 'trg_el' ),
						'desc'    => esc_attr__( 'Choose a background color for Social links', 'trg_el' ),
						'type'    => 'color'
					),

					array(
						'name'    => 'social_color_hover',
						'label'   => esc_attr__( 'Social links hover color', 'trg_el' ),
						'desc'    => esc_attr__( 'Choose a hover color for Social links', 'trg_el' ),
						'type'    => 'color'
					),

					array(
						'name'    => 'social_bg_hover',
						'label'   => esc_attr__( 'Social links background hover', 'trg_el' ),
						'desc'    => esc_attr__( 'Choose a hover background color for Social links', 'trg_el' ),
						'type'    => 'color'
					),

					array(
						'name'              => 'tax_optional',
						'label'             => esc_attr__( 'Taxonomy for meta links', 'trg_el' ),
						'desc'              => esc_attr__( 'Provide a taxonomy for meta links. E.g. product_cat, or my_taxonomy. If provided, the tag recipe meta items will be linked to this custom taxonomy archive.', 'trg_el' ),
						'type'              => 'text',
						'default'           => ''
					),
				),

				'trg_social' => array(
					array(
						'name'              => 'social_heading',
						'label'             => esc_attr__( 'Social sharing heading', 'trg_el' ),
						'desc'              => esc_attr__( 'Provide a heading for social sharing buttons', 'trg_el' ),
						'type'              => 'text',
						'default'           => ''
					),

					array(
						'name'              => 'social_buttons',
						'label'             => esc_attr__( 'Social Sharing buttons', 'trg_el' ),
						'desc'              => esc_attr__( 'Select social share buttons to show at the end of recipe', 'trg_el' ),
						'type'              => 'select_multiple',
						'default'           => '',
						'options'			=> array(
							'twitter'		=> __( 'Twitter', 'trg_el' ),
							'facebook'	 	=> __( 'Facebook', 'trg_el' ),
							'whatsapp'		=> __( 'WhatsApp', 'trg_el' ),
							'linkedin'	 	=> __( 'LinkedIn', 'trg_el' ),
							'pinterest'	 	=> __( 'Pinterest', 'trg_el' ),
							'vkontakte'	 	=> __( 'VKOntakte', 'trg_el' ),
							'email'	 		=> __( 'Email', 'trg_el' ),
							'print'	 		=> __( 'Print', 'trg_el' ),
							'reddit'	 	=> __( 'Reddit', 'trg_el' )
						)
					),

					array(
						'name'              => 'social_pos',
						'label'             => esc_attr__( 'Social Buttons Placement', 'trg_el' ),
						'desc'              => esc_attr__( 'Choose placement area for social buttons.', 'trg_el' ),
						'type'              => 'select',
						'default'           => '5',
						'options'			=> array(
							'1'		=> __( 'Before Recipe Title', 'trg_el' ),
							'2'	 	=> __( 'Before Recipe Image', 'trg_el' ),
							'3'		=> __( 'After Recipe Image', 'trg_el' ),
							'4'		=> __( 'Before Nutrition Facts', 'trg_el' ),
							'5'		=> __( 'After Nutrition Facts', 'trg_el' )
						)
					),

					array(
						'name'  => 'social_sticky',
						'label' => esc_attr__( 'Sticky Social Buttons', 'trg_el' ),
						'desc'  => esc_attr__( 'Make social buttons sticky on mobile', 'trg_el' ),
						'type'  => 'checkbox'
					),

					array(
						'name'              => 'prnt_header',
						'label'             => esc_attr__( 'Print Header Text', 'trg_el' ),
						'desc'              => esc_attr__( 'Provide a header text that shall appear on top of recipe pdf/print.', 'trg_el' ),
						'type'              => 'textarea',
						'default'           => ''
					),

					array(
						'name'              => 'prnt_footer',
						'label'             => esc_attr__( 'Print footer Text', 'trg_el' ),
						'desc'              => esc_attr__( 'Provide a footer text that shall appear at bottom of recipe pdf/print.', 'trg_el' ),
						'type'              => 'textarea',
						'default'           => ''
					)
				)
			);

			return $settings_fields;
		}

		function plugin_page() {
			echo '<div class="wrap">';
			echo '<h1>' . esc_attr__( 'Total Recipe Generator Settings', 'trg_el' ) . '</h1>';
			$this->settings_api->show_navigation();
			$this->settings_api->show_forms();

			echo '</div>';
		}

		/**
		 * Get all the pages
		 *
		 * @return array page names with key value pairs
		 */
		function get_pages() {
			$pages = get_pages();
			$pages_options = array();
			if ( $pages ) {
				foreach ($pages as $page) {
					$pages_options[$page->ID] = $page->post_title;
				}
			}

			return $pages_options;
		}
	}

	$generate_trg_settings = new Generate_TRG_Settings();

endif;
<?php
/**
 * DethemeKit Media Grid.
 */

namespace DethemeKitAddons\Widgets;

// Elementor Classes.
use Elementor\Modules\DynamicTags\Module as TagsModule;
use Elementor\Widget_Base;
use Elementor\Utils;
use Elementor\Embed;
use Elementor\Control_Media;
use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use Elementor\Repeater;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Image_Size;

// DethemeKitAddons Classes.
use DethemeKitAddons\Helper_Functions;
use DethemeKitAddons\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class DethemeKit_Grid
 */
class DethemeKit_Grid extends Widget_Base {

	/**
	 * Retrieve Widget Name.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function get_name() {
		return 'dethemekit-img-gallery';
	}

	/**
	 * Get Elementor Helper Instance.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function getTemplateInstance() {
		$this->template_instance = Includes\dethemekit_Template_Tags::getInstance();
        return $this->template_instance;
	}

	/**
	 * Retrieve Widget Title.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function get_title() {
		return __('De Gallery', 'dethemekit-for-elementor') ;
	}

	/**
	 * Retrieve Widget Icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string widget icon.
	 */
	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	/**
	 * Retrieve Widget Dependent CSS.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array CSS style handles.
	 */
	public function get_style_depends() {
		return array(
			'pa-prettyphoto',
			'dethemekit-addons',
		);
	}

	/**
	 * Retrieve Widget Dependent JS.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array JS script handles.
	 */
	public function get_script_depends() {
		return array(
			'imagesloaded',
			'prettyPhoto-js',
			'isotope-js',
			'dethemekit-addons-js'
		);
	}

	/**
	 * Widget preview refresh button.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function is_reload_preview_required() {
		return true;
	}

	/**
	 * Retrieve Widget Categories.
	 *
	 * @since 1.5.1
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return array( 'dethemekit-elements' );
	}

	/**
	 * Retrieve Widget Keywords.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget keywords.
	 */
	public function get_keywords() {
		return array( 'layout', 'gallery', 'images', 'videos', 'portfolio', 'visual', 'masonry' );
	}

	/**
	 * Retrieve Widget Support URL.
	 *
	 * @access public
	 *
	 * @return string support URL.
	 */
	public function get_custom_help_url() {
		// return 'https://dethemekitaddons.com/support/';
	}

	/**
	 * Register Media Grid controls.
	 *
	 * @since 2.1.0
	 * @access protected
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'dethemekit_gallery_general',
			array(
				'label' => __( 'Layout', 'dethemekit-for-elementor' ),

			)
		);

		$this->add_control(
			'dethemekit_gallery_img_size_select',
			array(
				'label'   => __( 'Grid Layout', 'dethemekit-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'fitRows' => __( 'Even', 'dethemekit-for-elementor' ),
					'masonry' => __( 'Masonry', 'dethemekit-for-elementor' ),
					'metro'   => __( 'Metro', 'dethemekit-for-elementor' ),
				),
				'default' => 'fitRows',
			)
		);

		$this->add_responsive_control(
			'pemium_gallery_even_img_height',
			array(
				'label'       => __( 'Height', 'dethemekit-for-elementor' ),
				'label_block' => true,
				'size_units'  => array( 'px', 'em', 'vh' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array(
					'px' => array(
						'min' => 1,
						'max' => 500,
					),
					'em' => array(
						'min' => 1,
						'max' => 50,
					),
				),
				'render_type' => 'template',
				'condition'   => array(
					'dethemekit_gallery_img_size_select' => 'fitRows',
				),
				'selectors'   => array(
					'{{WRAPPER}} .dethemekit-gallery-item .pa-gallery-image' => 'height: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_control(
			'dethemekit_gallery_images_fit',
			array(
				'label'     => __( 'Images Fit', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					'fill'  => __( 'Fill', 'dethemekit-for-elementor' ),
					'cover' => __( 'Cover', 'dethemekit-for-elementor' ),
				),
				'default'   => 'fill',
				'selectors' => array(
					'{{WRAPPER}} .dethemekit-gallery-item .pa-gallery-image'  => 'object-fit: {{VALUE}}',
				),
				'condition' => array(
					'dethemekit_gallery_img_size_select' => array( 'metro', 'fitRows' ),
				),
			)
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			array(
				'name'    => 'thumbnail',
				'default' => 'full',
			)
		);

		$this->add_responsive_control(
			'dethemekit_gallery_column_number',
			array(
				'label'           => __( 'Columns', 'dethemekit-for-elementor' ),
				'label_block'     => true,
				'type'            => Controls_Manager::SELECT,
				'desktop_default' => '50%',
				'tablet_default'  => '100%',
				'mobile_default'  => '100%',
				'options'         => array(
					'100%'    => __( '1 Column', 'dethemekit-for-elementor' ),
					'50%'     => __( '2 Columns', 'dethemekit-for-elementor' ),
					'33.330%' => __( '3 Columns', 'dethemekit-for-elementor' ),
					'25%'     => __( '4 Columns', 'dethemekit-for-elementor' ),
					'20%'     => __( '5 Columns', 'dethemekit-for-elementor' ),
					'16.66%'  => __( '6 Columns', 'dethemekit-for-elementor' ),
					'8.33%'   => __( '12 Columns', 'dethemekit-for-elementor' ),
				),
				'condition'       => array(
					'dethemekit_gallery_img_size_select!' => 'metro',
				),
				'selectors'       => array(
					'{{WRAPPER}} .dethemekit-img-gallery-masonry div.dethemekit-gallery-item, {{WRAPPER}} .dethemekit-img-gallery-fitRows div.dethemekit-gallery-item' => 'width: {{VALUE}};',
				),
				'render_type'     => 'template',
			)
		);

		$this->add_control(
			'dethemekit_gallery_load_more',
			array(
				'label'       => __( 'Load More Button', 'dethemekit-for-elementor' ),
				'description' => __( 'Requires number of images larger than 6', 'dethemekit-for-elementor' ),
				'type'        => Controls_Manager::SWITCHER,
			)
		);

		$this->add_control(
			'dethemekit_gallery_load_more_text',
			array(
				'label'     => __( 'Button Text', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Load More', 'dethemekit-for-elementor' ),
				'dynamic'   => array( 'active' => true ),
				'condition' => array(
					'dethemekit_gallery_load_more' => 'yes',
				),
			)
		);

		$this->add_control(
			'dethemekit_gallery_load_minimum',
			array(
				'label'       => __( 'Minimum Number of Images', 'dethemekit-for-elementor' ),
				'type'        => Controls_Manager::NUMBER,
				'description' => __( 'Set the minimum number of images before showing load more button', 'dethemekit-for-elementor' ),
				'default'     => 6,
				'condition'   => array(
					'dethemekit_gallery_load_more' => 'yes',
				),
			)
		);

		$this->add_control(
			'dethemekit_gallery_load_click_number',
			array(
				'label'       => __( 'Images to Show', 'dethemekit-for-elementor' ),
				'type'        => Controls_Manager::NUMBER,
				'description' => __( 'Set the minimum number of images to show with each click', 'dethemekit-for-elementor' ),
				'default'     => 6,
				'condition'   => array(
					'dethemekit_gallery_load_more' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'dethemekit_gallery_load_more_align',
			array(
				'label'     => __( 'Button Alignment', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array(
						'title' => __( 'Left', 'dethemekit-for-elementor' ),
						'icon'  => 'fa fa-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'dethemekit-for-elementor' ),
						'icon'  => 'fa fa-align-center',
					),
					'right'  => array(
						'title' => __( 'Right', 'dethemekit-for-elementor' ),
						'icon'  => 'fa fa-align-right',
					),
				),
				'default'   => 'center',
				'selectors' => array(
					'{{WRAPPER}} .dethemekit-gallery-load-more' => 'text-align: {{VALUE}};',
				),
				'condition' => array(
					'dethemekit_gallery_load_more' => 'yes',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'dethemekit_gallery_cats',
			array(
				'label' => __( 'Categories', 'dethemekit-for-elementor' ),
			)
		);

		$this->add_control(
			'dethemekit_gallery_filter',
			array(
				'label'   => __( 'Filter Tabs', 'dethemekit-for-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$condition = array( 'dethemekit_gallery_filter' => 'yes' );

		$this->add_control(
			'dethemekit_gallery_first_cat_switcher',
			array(
				'label'     => __( 'First Category', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => $condition,
			)
		);

		$this->add_control(
			'dethemekit_gallery_first_cat_label',
			array(
				'label'     => __( 'First Category Label', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'All', 'dethemekit-for-elementor' ),
				'dynamic'   => array( 'active' => true ),
				'condition' => array_merge(
					array(
						'dethemekit_gallery_first_cat_switcher' => 'yes',
					),
					$condition
				),
			)
		);

		$repeater = new REPEATER();

		$repeater->add_control(
			'dethemekit_gallery_img_cat',
			array(
				'label'   => __( 'Category', 'dethemekit-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'dynamic' => array( 'active' => true ),
			)
		);

		$repeater->add_control(
			'dethemekit_gallery_img_cat_rotation',
			array(
				'label'       => __( 'Rotation Degrees', 'dethemekit-for-elementor' ),
				'type'        => Controls_Manager::NUMBER,
				'description' => __( 'Set rotation value in degrees', 'dethemekit-for-elementor' ),
				'min'         => -180,
				'max'         => 180,
				'selectors'   => array(
					'{{WRAPPER}} {{CURRENT_ITEM}}' => 'transform: rotate({{VALUE}}deg);',
				),
			)
		);

		$this->add_control(
			'dethemekit_gallery_cats_content',
			array(
				'label'       => __( 'Categories', 'dethemekit-for-elementor' ),
				'type'        => Controls_Manager::REPEATER,
				'default'     => array(
					array(
						'dethemekit_gallery_img_cat' => 'Category 1',
					),
					array(
						'dethemekit_gallery_img_cat' => 'Category 2',
					),
				),
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ dethemekit_gallery_img_cat }}}',
				'condition'   => $condition,
			)
		);

		$this->add_control(
			'dethemekit_gallery_active_cat',
			array(
				'label'     => __( 'Active Category Index', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 0,
				'min'       => 0,
				'condition' => $condition,

			)
		);

		$this->add_control(
			'active_cat_notice',
			array(
				'raw'             => __( 'Please note categories are zero indexed, so if you need the first category to be active, you need to set the value to 0', 'dethemekit-for-elementor' ),
				'type'            => Controls_Manager::RAW_HTML,
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				'condition'       => $condition,
			)
		);

		$this->add_control(
			'dethemekit_gallery_shuffle',
			array(
				'label'     => __( 'Shuffle Images on Filter Click', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => array_merge(
					array(
						'dethemekit_gallery_filter' => 'yes',
					),
					$condition
				),
			)
		);

		$this->add_responsive_control(
			'dethemekit_gallery_filters_align',
			array(
				'label'     => __( 'Alignment', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => __( 'Left', 'dethemekit-for-elementor' ),
						'icon'  => 'fa fa-align-left',
					),
					'center'     => array(
						'title' => __( 'Center', 'dethemekit-for-elementor' ),
						'icon'  => 'fa fa-align-center',
					),
					'flex-end'   => array(
						'title' => __( 'Right', 'dethemekit-for-elementor' ),
						'icon'  => 'fa fa-align-right',
					),
				),
				'default'   => 'center',
				'selectors' => array(
					'{{WRAPPER}} .dethemekit-img-gallery-filter' => 'justify-content: {{VALUE}}',
				),
				'condition' => $condition,
			)
		);

		$this->add_control(
			'url_flag',
			array(
				'label'       => __( 'URL Flag', 'dethemekit-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'cat',
				'description' => __( 'This is used to link categories from different pages. For example: dethemekitaddons.com/grid-widget-for-elementor-page-builder?cat=2', 'dethemekit-for-elementor' ),
				'label_block' => true,
				'condition'   => $condition,
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'dethemekit_gallery_content',
			array(
				'label' => __( 'Images/Videos', 'dethemekit-for-elementor' ),
			)
		);

		$img_repeater = new REPEATER();

		$img_repeater->add_control(
			'dethemekit_gallery_img',
			array(
				'label'   => __( 'Upload Image', 'dethemekit-for-elementor' ),
				'type'    => Controls_Manager::MEDIA,
				'dynamic' => array( 'active' => true ),
				'default' => array(
					'url' => Utils::get_placeholder_image_src(),
				),
			)
		);

		$img_repeater->add_responsive_control(
			'dethemekit_gallery_image_cell',
			array(
				'label'       => __( 'Width', 'dethemekit-for-elementor' ),
				'description' => __( 'Works only when layout set to Metro', 'dethemekit-for-elementor' ),
				'label_block' => true,
				'default'     => array(
					'unit' => 'px',
					'size' => 4,
				),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array(
					'px' => array(
						'min' => 1,
						'max' => 12,
					),
				),
				'render_type' => 'template',
			)
		);

		$img_repeater->add_responsive_control(
			'dethemekit_gallery_image_vcell',
			array(
				'label'       => __( 'Height', 'dethemekit-for-elementor' ),
				'description' => __( 'Works only when layout set to \'Metro\'', 'dethemekit-for-elementor' ),
				'label_block' => true,
				'type'        => Controls_Manager::SLIDER,
				'default'     => array(
					'unit' => 'px',
					'size' => 4,
				),
				'range'       => array(
					'px' => array(
						'min' => 1,
						'max' => 12,
					),
				),
				'render_type' => 'template',
			)
		);

		$img_repeater->add_control(
			'dethemekit_gallery_video',
			array(
				'label'        => __( 'Video', 'dethemekit-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'true',
			)
		);

		$img_repeater->add_control(
			'dethemekit_gallery_video_type',
			array(
				'label'       => __( 'Type', 'dethemekit-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'youtube' => __( 'YouTube', 'dethemekit-for-elementor' ),
					'vimeo'   => __( 'Vimeo', 'dethemekit-for-elementor' ),
					'hosted'  => __( 'Self Hosted', 'dethemekit-for-elementor' ),
				),
				'label_block' => true,
				'default'     => 'youtube',
				'condition'   => array(
					'dethemekit_gallery_video' => 'true',
				),
			)
		);

		$img_repeater->add_control(
			'dethemekit_gallery_video_url',
			array(
				'label'       => __( 'Video URL', 'dethemekit-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'dynamic'     => array(
					'active'     => true,
					'categories' => array(
						TagsModule::POST_META_CATEGORY,
						TagsModule::URL_CATEGORY,
					),
				),
				'condition'   => array(
					'dethemekit_gallery_video'       => 'true',
					'dethemekit_gallery_video_type!' => 'hosted',
				),
			)
		);

		$img_repeater->add_control(
			'dethemekit_gallery_video_self',
			array(
				'label'      => __( 'Select Video', 'dethemekit-for-elementor' ),
				'type'       => Controls_Manager::MEDIA,
				'dynamic'    => array(
					'active'     => true,
					'categories' => array(
						TagsModule::POST_META_CATEGORY,
						TagsModule::URL_CATEGORY,
					),
				),
				'media_type' => 'video',
				'condition'  => array(
					'dethemekit_gallery_video'      => 'true',
					'dethemekit_gallery_video_type' => 'hosted',
				),
			)
		);

		$img_repeater->add_control(
			'dethemekit_gallery_video_self_url',
			array(
				'label'       => __( 'Remote Video URL', 'dethemekit-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array(
					'active' => true,
				),
				'label_block' => true,
				'condition'   => array(
					'dethemekit_gallery_video'      => 'true',
					'dethemekit_gallery_video_type' => 'hosted',
				),
			)
		);

		$img_repeater->add_control(
			'dethemekit_gallery_video_controls',
			array(
				'label'        => __( 'Controls', 'dethemekit-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'true',
				'default'      => 'true',
				'condition'    => array(
					'dethemekit_gallery_video' => 'true',
				),
			)
		);

		$img_repeater->add_control(
			'dethemekit_gallery_video_mute',
			array(
				'label'        => __( 'Mute', 'dethemekit-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'true',
				'condition'    => array(
					'dethemekit_gallery_video' => 'true',
				),
			)
		);

		$img_repeater->add_control(
			'dethemekit_gallery_img_name',
			array(
				'label'       => __( 'Title', 'dethemekit-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'label_block' => true,
			)
		);

		$img_repeater->add_control(
			'dethemekit_gallery_img_desc',
			array(
				'label'       => __( 'Description', 'dethemekit-for-elementor' ),
				'type'        => Controls_Manager::TEXTAREA,
				'dynamic'     => array( 'active' => true ),
				'label_block' => true,
			)
		);

		$img_repeater->add_control(
			'dethemekit_gallery_img_category',
			array(
				'label'       => __( 'Category', 'dethemekit-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'description' => __( 'To assign for multiple categories, separate by a comma \',\'', 'dethemekit-for-elementor' ),
				'dynamic'     => array( 'active' => true ),
			)
		);

		$img_repeater->add_control(
			'dethemekit_gallery_img_link_type',
			array(
				'label'       => __( 'Link Type', 'dethemekit-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'url'  => __( 'URL', 'dethemekit-for-elementor' ),
					'link' => __( 'Existing Page', 'dethemekit-for-elementor' ),
				),
				'default'     => 'url',
				'label_block' => true,
				'condition'   => array(
					'dethemekit_gallery_video!' => 'true',
				),
			)
		);

		$img_repeater->add_control(
			'dethemekit_gallery_img_link',
			array(
				'label'       => __( 'Link', 'dethemekit-for-elementor' ),
				'type'        => Controls_Manager::URL,
				'dynamic'     => array( 'active' => true ),
				'placeholder' => 'https://dethemekitaddons.com/',
				'label_block' => true,
				'condition'   => array(
					'dethemekit_gallery_img_link_type' => 'url',
					'dethemekit_gallery_video!'        => 'true',
				),
			)
		);

		$img_repeater->add_control(
			'dethemekit_gallery_img_existing',
			array(
				'label'       => __( 'Existing Page', 'dethemekit-for-elementor' ),
				'type'        => Controls_Manager::SELECT2,
				'options'     => $this->getTemplateInstance()->get_all_post(),
				'condition'   => array(
					'dethemekit_gallery_img_link_type' => 'link',
				),
				'multiple'    => false,
				'separator'   => 'after',
				'label_block' => true,
				'condition'   => array(
					'dethemekit_gallery_img_link_type' => 'link',
					'dethemekit_gallery_video!'        => 'true',
				),
			)
		);

		$img_repeater->add_control(
			'dethemekit_gallery_link_whole',
			array(
				'label'     => __( 'Whole Image Link', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => array(
					'dethemekit_gallery_video!' => 'true',
				),
			)
		);

		$img_repeater->add_control(
			'dethemekit_gallery_lightbox_whole',
			array(
				'label'     => __( 'Whole Image Lightbox', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => array(
					'dethemekit_gallery_video!' => 'true',
				),
			)
		);

		$this->add_control(
			'dethemekit_gallery_img_content',
			array(
				'label'       => __( 'Images', 'dethemekit-for-elementor' ),
				'type'        => Controls_Manager::REPEATER,
				'default'     => array(
					array(
						'dethemekit_gallery_img_name'     => 'Image #1',
						'dethemekit_gallery_img_category' => 'Category 1',
					),
					array(
						'dethemekit_gallery_img_name'     => 'Image #2',
						'dethemekit_gallery_img_category' => 'Category 2',
					),
				),
				'fields'      => $img_repeater->get_controls(),
				'title_field' => '{{{ "" !== dethemekit_gallery_img_name ? dethemekit_gallery_img_name : "Image" }}} - {{{ "" !== dethemekit_gallery_img_category ? dethemekit_gallery_img_category : "No Categories" }}}',
			)
		);

		$this->add_control(
			'dethemekit_gallery_shuffle_onload',
			array(
				'label' => __( 'Shuffle Images on Page Load', 'dethemekit-for-elementor' ),
				'type'  => Controls_Manager::SWITCHER,
			)
		);

		$this->add_control(
			'dethemekit_gallery_yt_thumbnail_size',
			array(
				'label'       => __( 'Youtube Videos Thumbnail Size', 'dethemekit-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => array(
					'maxresdefault' => __( 'Maximum Resolution', 'dethemekit-for-elementor' ),
					'hqdefault'     => __( 'High Quality', 'dethemekit-for-elementor' ),
					'mqdefault'     => __( 'Medium Quality', 'dethemekit-for-elementor' ),
					'sddefault'     => __( 'Standard Quality', 'dethemekit-for-elementor' ),
				),
				'default'     => 'maxresdefault',
				'label_block' => true,
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'dethemekit_gallery_grid_settings',
			array(
				'label' => __( 'Display Options', 'dethemekit-for-elementor' ),

			)
		);

		$this->add_responsive_control(
			'dethemekit_gallery_gap',
			array(
				'label'      => __( 'Image Gap', 'dethemekit-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 200,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .dethemekit-gallery-item' => 'padding: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->add_control(
			'dethemekit_gallery_img_style',
			array(
				'label'       => __( 'Skin', 'dethemekit-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'description' => __( 'Choose a layout style for the gallery', 'dethemekit-for-elementor' ),
				'options'     => array(
					'default' => __( 'Style 1', 'dethemekit-for-elementor' ),
					'style1'  => __( 'Style 2', 'dethemekit-for-elementor' ),
					'style2'  => __( 'Style 3', 'dethemekit-for-elementor' ),
					'style3'  => __( 'Style 4', 'dethemekit-for-elementor' ),
				),
				'default'     => 'default',
				'separator'   => 'before',
				'label_block' => true,
			)
		);

		$this->add_control(
			'dethemekit_grid_style_notice',
			array(
				'raw'             => __( 'Style 4 works only with Even / Masonry Layout', 'dethemekit-for-elementor' ),
				'type'            => Controls_Manager::RAW_HTML,
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				'condition'       => array(
					'dethemekit_gallery_img_style'       => 'style3',
					'dethemekit_gallery_img_size_select' => 'metro',
				),
			)
		);

		$this->add_responsive_control(
			'dethemekit_gallery_style1_border_border',
			array(
				'label'       => __( 'Height', 'dethemekit-for-elementor' ),
				'type'        => Controls_Manager::SLIDER,
				'range'       => array(
					'px' => array(
						'min' => 0,
						'max' => 700,
					),
				),
				'label_block' => true,
				'selectors'   => array(
					'{{WRAPPER}} .pa-gallery-img.style1 .dethemekit-gallery-caption' => 'bottom: {{SIZE}}px;',
				),
				'condition'   => array(
					'dethemekit_gallery_img_style' => 'style1',
				),
			)
		);

		$this->add_control(
			'dethemekit_gallery_img_effect',
			array(
				'label'       => __( 'Hover Effect', 'dethemekit-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'description' => __( 'Choose a hover effect for the image', 'dethemekit-for-elementor' ),
				'options'     => array(
					'none'    => __( 'None', 'dethemekit-for-elementor' ),
					'zoomin'  => __( 'Zoom In', 'dethemekit-for-elementor' ),
					'zoomout' => __( 'Zoom Out', 'dethemekit-for-elementor' ),
					'scale'   => __( 'Scale', 'dethemekit-for-elementor' ),
					'gray'    => __( 'Grayscale', 'dethemekit-for-elementor' ),
					'blur'    => __( 'Blur', 'dethemekit-for-elementor' ),
					'bright'  => __( 'Bright', 'dethemekit-for-elementor' ),
					'sepia'   => __( 'Sepia', 'dethemekit-for-elementor' ),
					'trans'   => __( 'Translate', 'dethemekit-for-elementor' ),
				),
				'default'     => 'zoomin',
				'label_block' => true,
				'separator'   => 'after',
			)
		);

		$this->add_control(
			'dethemekit_gallery_links_icon',
			array(
				'label'   => __( 'Links Icon', 'dethemekit-for-elementor' ),
				'type'    => Controls_Manager::ICONS,
				'default' => array(
					'library' => 'fa-solid',
					'value'   => 'fas fa-link',
				),
			)
		);

		$this->add_control(
			'dethemekit_gallery_videos_heading',
			array(
				'label'     => __( 'Videos', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'dethemekit_gallery_video_icon',
			array(
				'label'        => __( 'Always Show Play Icon', 'dethemekit-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'true',
				'default'      => 'true',
				'condition'    => array(
					'dethemekit_gallery_img_style!' => 'style2',
				),

			)
		);

		$this->add_control(
			'dethemekit_gallery_videos_icon',
			array(
				'label'   => __( 'Videos Play Icon', 'dethemekit-for-elementor' ),
				'type'    => Controls_Manager::ICONS,
				'default' => array(
					'library' => 'fa-solid',
					'value'   => 'fas fa-play',
				),
			)
		);

		$this->add_control(
			'dethemekit_gallery_rtl_mode',
			array(
				'label'       => __( 'RTL Mode', 'dethemekit-for-elementor' ),
				'description' => __( 'This option moves the origin of the grid to the right side. Useful for RTL direction sites', 'dethemekit-for-elementor' ),
				'type'        => Controls_Manager::SWITCHER,
				'separator'   => 'before',
			)
		);

		$this->add_responsive_control(
			'dethemekit_gallery_content_align',
			array(
				'label'     => __( 'Content Alignment', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array(
						'title' => __( 'Left', 'dethemekit-for-elementor' ),
						'icon'  => 'fa fa-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'dethemekit-for-elementor' ),
						'icon'  => 'fa fa-align-center',
					),
					'right'  => array(
						'title' => __( 'Right', 'dethemekit-for-elementor' ),
						'icon'  => 'fa fa-align-right',
					),
				),
				'default'   => 'center',
				'separator' => 'before',
				'selectors' => array(
					'{{WRAPPER}} .dethemekit-gallery-caption' => 'text-align: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'dethemekit_gallery_lightbox_section',
			array(
				'label' => __( 'Lightbox', 'dethemekit-for-elementor' ),
			)
		);

		$this->add_control(
			'dethemekit_gallery_light_box',
			array(
				'label'     => __( 'Lightbox', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'separator' => 'before',
			)
		);

		$this->add_control(
			'dethemekit_gallery_lightbox_type',
			array(
				'label'     => __( 'Lightbox Style', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'default',
				'options'   => array(
					'default' => __( 'PrettyPhoto', 'dethemekit-for-elementor' ),
					'yes'     => __( 'Elementor', 'dethemekit-for-elementor' ),
					'no'      => __( 'Other Lightbox Plugin', 'dethemekit-for-elementor' ),
				),
				'condition' => array(
					'dethemekit_gallery_light_box' => 'yes',
				),
			)
		);

		$this->add_control(
			'lightbox_show_title',
			array(
				'label'     => __( 'Show Image Title', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => array(
					'dethemekit_gallery_light_box'     => 'yes',
					'dethemekit_gallery_lightbox_type' => 'yes',
				),
			)
		);

		$this->add_control(
			'dethemekit_gallery_lightbox_doc',
			array(
				'raw'             => __( 'Please note Elementor lightbox style is always applied on videos.', 'dethemekit-for-elementor' ),
				'type'            => Controls_Manager::RAW_HTML,
				'content_classes' => 'editor-pa-doc',
			)
		);

		$this->add_control(
			'dethemekit_gallery_lightbox_theme',
			array(
				'label'     => __( 'Lightbox Theme', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					'pp_default'    => __( 'Default', 'dethemekit-for-elementor' ),
					'light_rounded' => __( 'Light Rounded', 'dethemekit-for-elementor' ),
					'dark_rounded'  => __( 'Dark Rounded', 'dethemekit-for-elementor' ),
					'light_square'  => __( 'Light Square', 'dethemekit-for-elementor' ),
					'dark_square'   => __( 'Dark Square', 'dethemekit-for-elementor' ),
					'facebook'      => __( 'Facebook', 'dethemekit-for-elementor' ),
				),
				'default'   => 'pp_default',
				'condition' => array(
					'dethemekit_gallery_light_box'     => 'yes',
					'dethemekit_gallery_lightbox_type' => 'default',
				),
			)
		);

		$this->add_control(
			'dethemekit_gallery_overlay_gallery',
			array(
				'label'     => __( 'Overlay Gallery Images', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => array(
					'dethemekit_gallery_light_box'     => 'yes',
					'dethemekit_gallery_lightbox_type' => 'default',
				),
			)
		);

		$this->add_control(
			'dethemekit_gallery_lightbox_icon',
			array(
				'label'     => __( 'Lightbox Icon', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => array(
					'library' => 'fa-solid',
					'value'   => 'fas fa-search',
				),
				'condition' => array(
					'dethemekit_gallery_light_box' => 'yes',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'dethemekit_gallery_responsive_section',
			array(
				'label' => __( 'Responsive', 'dethemekit-for-elementor' ),
			)
		);

		$this->add_control(
			'dethemekit_gallery_responsive_switcher',
			array(
				'label'       => __( 'Responsive Controls', 'dethemekit-for-elementor' ),
				'type'        => Controls_Manager::SWITCHER,
				'description' => __( 'If the content text is not suiting well on specific screen sizes, you may enable this option which will hide the description text.', 'dethemekit-for-elementor' ),
			)
		);

		$this->add_control(
			'dethemekit_gallery_min_range',
			array(
				'label'       => __( 'Minimum Size', 'dethemekit-for-elementor' ),
				'type'        => Controls_Manager::NUMBER,
				'description' => __( 'Note: minimum size for extra small screens is 1px.', 'dethemekit-for-elementor' ),
				'default'     => 1,
				'condition'   => array(
					'dethemekit_gallery_responsive_switcher' => 'yes',
				),
			)
		);

		$this->add_control(
			'dethemekit_gallery_max_range',
			array(
				'label'       => __( 'Maximum Size', 'dethemekit-for-elementor' ),
				'type'        => Controls_Manager::NUMBER,
				'description' => __( 'Note: maximum size for extra small screens is 767px.', 'dethemekit-for-elementor' ),
				'default'     => 767,
				'condition'   => array(
					'dethemekit_gallery_responsive_switcher' => 'yes',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'dethemekit_gallery_general_style',
			array(
				'label' => __( 'General', 'dethemekit-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'dethemekit_gallery_general_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .dethemekit-img-gallery',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'dethemekit_gallery_general_border',
				'selector' => '{{WRAPPER}} .dethemekit-img-gallery',
			)
		);

		$this->add_control(
			'dethemekit_gallery_general_border_radius',
			array(
				'label'      => __( 'Border Radius', 'dethemekit-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .dethemekit-img-gallery' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'dethemekit_gallery_general_box_shadow',
				'selector' => '{{WRAPPER}} .dethemekit-img-gallery',
			)
		);

		$this->add_responsive_control(
			'dethemekit_gallery_general_margin',
			array(
				'label'      => __( 'Margin', 'dethemekit-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .dethemekit-img-gallery' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->add_responsive_control(
			'dethemekit_gallery_general_padding',
			array(
				'label'      => __( 'Padding', 'dethemekit-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .dethemekit-img-gallery' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'dethemekit_gallery_img_style_section',
			array(
				'label' => __( 'Image', 'dethemekit-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'dethemekit_gallery_icons_style_overlay',
			array(
				'label'     => __( 'Hover Overlay Color', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .pa-gallery-img:not(.style2):hover .pa-gallery-icons-wrapper, {{WRAPPER}} .pa-gallery-img .pa-gallery-icons-caption-container, {{WRAPPER}} .pa-gallery-img:hover .pa-gallery-icons-caption-container' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'dethemekit_gallery_img_border',
				'selector' => '{{WRAPPER}} .pa-gallery-img-container',
			)
		);

		$this->add_control(
			'dethemekit_gallery_img_border_radius',
			array(
				'label'      => __( 'Border Radius', 'dethemekit-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-gallery-img-container' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'label'     => __( 'Shadow', 'dethemekit-for-elementor' ),
				'name'      => 'dethemekit_gallery_img_box_shadow',
				'selector'  => '{{WRAPPER}} .pa-gallery-img-container',
				'condition' => array(
					'dethemekit_gallery_img_style!' => 'style1',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			array(
				'name'     => 'css_filters',
				'selector' => '{{WRAPPER}} .pa-gallery-img-container img',
			)
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			array(
				'label'    => __( 'Hover CSS Filters', 'dethemekit-for-elementor' ),
				'name'     => 'hover_css_filters',
				'selector' => '{{WRAPPER}} .dethemekit-gallery-item:hover img',
			)
		);

		$this->add_responsive_control(
			'dethemekit_gallery_img_margin',
			array(
				'label'      => __( 'Margin', 'dethemekit-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-gallery-img-container' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->add_responsive_control(
			'dethemekit_gallery_img_padding',
			array(
				'label'      => __( 'Padding', 'dethemekit-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-gallery-img-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'dethemekit_gallery_content_style',
			array(
				'label' => __( 'Title / Description', 'dethemekit-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'dethemekit_gallery_title_heading',
			array(
				'label' => __( 'Title', 'dethemekit-for-elementor' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_control(
			'dethemekit_gallery_title_color',
			array(
				'label'     => __( 'Color', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_SECONDARY,
				],
				'selectors' => array(
					'{{WRAPPER}} .dethemekit-gallery-img-name, {{WRAPPER}} .dethemekit-gallery-img-name a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'dethemekit_gallery_title_typo',
				'global'    => [
					'default'   => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .dethemekit-gallery-img-name, {{WRAPPER}} .dethemekit-gallery-img-name a',
			)
		);

		$this->add_control(
			'dethemekit_gallery_description_heading',
			array(
				'label'     => __( 'Description', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'dethemekit_gallery_description_color',
			array(
				'label'     => __( 'Color', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'selectors' => array(
					'{{WRAPPER}} .dethemekit-gallery-img-desc, {{WRAPPER}} .dethemekit-gallery-img-desc a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'dethemekit_gallery_description_typo',
				'global'    => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .dethemekit-gallery-img-desc, {{WRAPPER}} .dethemekit-gallery-img-desc a',
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'dethemekit_gallery_content_background',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '{{WRAPPER}} .dethemekit-gallery-caption',
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'dethemekit_gallery_content_border',
				'selector' => '{{WRAPPER}} .dethemekit-gallery-caption',
			)
		);

		$this->add_control(
			'dethemekit_gallery_content_border_radius',
			array(
				'label'      => __( 'Border Radius', 'dethemekit-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .dethemekit-gallery-caption' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'label'    => __( 'Shadow', 'dethemekit-for-elementor' ),
				'name'     => 'dethemekit_gallery_content_shadow',
				'selector' => '{{WRAPPER}} .dethemekit-gallery-caption',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'dethemekit_gallery_content_box_shadow',
				'selector' => '{{WRAPPER}} .dethemekit-gallery-caption',
			)
		);

		$this->add_responsive_control(
			'dethemekit_gallery_content_margin',
			array(
				'label'      => __( 'Margin', 'dethemekit-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .dethemekit-gallery-caption' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->add_responsive_control(
			'dethemekit_gallery_content_padding',
			array(
				'label'      => __( 'Padding', 'dethemekit-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .dethemekit-gallery-caption' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'dethemekit_gallery_icons_style',
			array(
				'label' => __( 'Icons', 'dethemekit-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'dethemekit_gallery_style1_icons_position',
			array(
				'label'       => __( 'Position', 'dethemekit-for-elementor' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'px', '%', 'em' ),
				'range'       => array(
					'px' => array(
						'min' => 0,
						'max' => 300,
					),
				),
				'label_block' => true,
				'selectors'   => array(
					'{{WRAPPER}} .pa-gallery-img:not(.style2) .pa-gallery-icons-inner-container' => 'top: {{SIZE}}{{UNIT}};',
				),
				'condition'   => array(
					'dethemekit_gallery_img_style!' => 'style2',
				),
			)
		);

		$this->add_responsive_control(
			'dethemekit_gallery_icons_size',
			array(
				'label'       => __( 'Size', 'dethemekit-for-elementor' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'px', 'em' ),
				'range'       => array(
					'px' => array(
						'min' => 1,
						'max' => 50,
					),
				),
				'label_block' => true,
				'selectors'   => array(
					'{{WRAPPER}} .pa-gallery-icons-inner-container i, {{WRAPPER}} .pa-gallery-icons-caption-cell i' => 'font-size: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .pa-gallery-icons-inner-container svg, {{WRAPPER}} .pa-gallery-icons-caption-cell svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}',
				),
			)
		);

		$this->start_controls_tabs( 'dethemekit_gallery_icons_style_tabs' );

		$this->start_controls_tab(
			'dethemekit_gallery_icons_style_normal',
			array(
				'label' => __( 'Normal', 'dethemekit-for-elementor' ),
			)
		);

		$this->add_control(
			'dethemekit_gallery_icons_style_color',
			array(
				'label'     => __( 'Color', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_PRIMARY,
				],
				'selectors' => array(
					'{{WRAPPER}} .pa-gallery-magnific-image i, {{WRAPPER}} .pa-gallery-img-link i' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'dethemekit_gallery_icons_style_background',
			array(
				'label'     => __( 'Background Color', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_SECONDARY,
				],
				'selectors' => array(
					'{{WRAPPER}} .pa-gallery-magnific-image span, {{WRAPPER}} .pa-gallery-img-link span' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'dethemekit_gallery_icons_style_border',
				'selector' => '{{WRAPPER}} .pa-gallery-magnific-image span, {{WRAPPER}} .pa-gallery-img-link span',
			)
		);

		$this->add_control(
			'dethemekit_gallery_icons_style_border_radius',
			array(
				'label'      => __( 'Border Radius', 'dethemekit-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-gallery-magnific-image span, {{WRAPPER}} .pa-gallery-img-link span' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'label'    => __( 'Shadow', 'dethemekit-for-elementor' ),
				'name'     => 'dethemekit_gallery_icons_style_shadow',
				'selector' => '{{WRAPPER}} .pa-gallery-magnific-image span, {{WRAPPER}} .pa-gallery-img-link span',
			)
		);

		$this->add_responsive_control(
			'dethemekit_gallery_icons_style_margin',
			array(
				'label'      => __( 'Margin', 'dethemekit-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-gallery-magnific-image span, {{WRAPPER}} .pa-gallery-img-link span' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'dethemekit_gallery_icons_style_padding',
			array(
				'label'      => __( 'Padding', 'dethemekit-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-gallery-magnific-image span, {{WRAPPER}} .pa-gallery-img-link span' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'dethemekit_gallery_icons_style_hover',
			array(
				'label' => __( 'Hover', 'dethemekit-for-elementor' ),
			)
		);

		$this->add_control(
			'dethemekit_gallery_icons_style_color_hover',
			array(
				'label'     => __( 'Color', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_PRIMARY,
				],
				'selectors' => array(
					'{{WRAPPER}} .pa-gallery-magnific-image:hover i, {{WRAPPER}} .pa-gallery-img-link:hover i' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'dethemekit_gallery_icons_style_background_hover',
			array(
				'label'     => __( 'Background Color', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_SECONDARY,
				],
				'selectors' => array(
					'{{WRAPPER}} .pa-gallery-magnific-image:hover span, {{WRAPPER}} .pa-gallery-img-link:hover span' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'dethemekit_gallery_icons_style_border_hover',
				'selector' => '{{WRAPPER}} .pa-gallery-magnific-image:hover span, {{WRAPPER}} .pa-gallery-img-link:hover span',
			)
		);

		$this->add_control(
			'dethemekit_gallery_icons_style_border_radius_hover',
			array(
				'label'      => __( 'Border Radius', 'dethemekit-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-gallery-magnific-image:hover span, {{WRAPPER}} .pa-gallery-img-link:hover span' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'label'    => __( 'Shadow', 'dethemekit-for-elementor' ),
				'name'     => 'dethemekit_gallery_icons_style_shadow_hover',
				'selector' => '{{WRAPPER}} {{WRAPPER}} .pa-gallery-magnific-image:hover span, {{WRAPPER}} .pa-gallery-img-link:hover span',
			)
		);

		$this->add_responsive_control(
			'dethemekit_gallery_icons_style_margin_hover',
			array(
				'label'      => __( 'Margin', 'dethemekit-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-gallery-magnific-image:hover span, {{WRAPPER}} .pa-gallery-img-link:hover span' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'dethemekit_gallery_icons_style_padding_hover',
			array(
				'label'      => __( 'Padding', 'dethemekit-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .pa-gallery-magnific-image:hover span, {{WRAPPER}} .pa-gallery-img-link:hover span' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'dethemekit_gallery_filter_style',
			array(
				'label'     => __( 'Filter', 'dethemekit-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'dethemekit_gallery_filter' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'dethemekit_gallery_filter_typo',
				'global'    => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .dethemekit-gallery-cats-container li a.category',
			)
		);

		$this->start_controls_tabs( 'dethemekit_gallery_filters' );

		$this->start_controls_tab(
			'dethemekit_gallery_filters_normal',
			array(
				'label' => __( 'Normal', 'dethemekit-for-elementor' ),
			)
		);

		$this->add_control(
			'dethemekit_gallery_filter_color',
			array(
				'label'     => __( 'Text Color', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_SECONDARY,
				],
				'selectors' => array(
					'{{WRAPPER}} .dethemekit-gallery-cats-container li a.category span' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'dethemekit_gallery_background_color',
			array(
				'label'     => __( 'Background Color', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .dethemekit-gallery-cats-container li a.category' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'dethemekit_gallery_filter_border',
				'selector' => '{{WRAPPER}} .dethemekit-gallery-cats-container li a.category',
			)
		);

		$this->add_control(
			'dethemekit_gallery_filter_border_radius',
			array(
				'label'      => __( 'Border Radius', 'dethemekit-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .dethemekit-gallery-cats-container li a.category'  => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'dethemekit_gallery_filters_hover',
			array(
				'label' => __( 'Hover', 'dethemekit-for-elementor' ),
			)
		);

		$this->add_control(
			'dethemekit_gallery_filter_hover_color',
			array(
				'label'     => __( 'Text Color', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .dethemekit-gallery-cats-container li a:hover span' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'dethemekit_gallery_background_hover_color',
			array(
				'label'     => __( 'Background Color', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .dethemekit-gallery-cats-container li a:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'dethemekit_gallery_filter_border_hover',
				'selector' => '{{WRAPPER}} .dethemekit-gallery-cats-container li a.category:hover',
			)
		);

		$this->add_control(
			'dethemekit_gallery_filter_border_radius_hover',
			array(
				'label'      => __( 'Border Radius', 'dethemekit-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .dethemekit-gallery-cats-container li a.category:hover'  => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'dethemekit_gallery_filters_active',
			array(
				'label' => __( 'Active', 'dethemekit-for-elementor' ),
			)
		);

		$this->add_control(
			'dethemekit_gallery_filter_active_color',
			array(
				'label'     => __( 'Text Color', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_PRIMARY,
				],
				'selectors' => array(
					'{{WRAPPER}} .dethemekit-gallery-cats-container li a.active span' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'dethemekit_gallery_background_active_color',
			array(
				'label'     => __( 'Background Color', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .dethemekit-gallery-cats-container li a.active' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'dethemekit_gallery_filter_border_active',
				'selector' => '{{WRAPPER}} .dethemekit-gallery-cats-container li a.active',
			)
		);

		$this->add_control(
			'dethemekit_gallery_filter_border_radius_active',
			array(
				'label'      => __( 'Border Radius', 'dethemekit-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .dethemekit-gallery-cats-container li a.active'  => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'dethemekit_gallery_filter_shadow',
				'selector' => '{{WRAPPER}} .dethemekit-gallery-cats-container li a.category',
			)
		);

		$this->add_responsive_control(
			'dethemekit_gallery_filter_margin',
			array(
				'label'      => __( 'Margin', 'dethemekit-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .dethemekit-gallery-cats-container li a.category' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'dethemekit_gallery_filter_padding',
			array(
				'label'      => __( 'Padding', 'dethemekit-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .dethemekit-gallery-cats-container li a.category' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'dethemekit_gallery_button_style_settings',
			array(
				'label'     => __( 'Load More Button', 'dethemekit-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'dethemekit_gallery_load_more' => 'yes',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'dethemekit_gallery_button_typo',
				'global'    => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .dethemekit-gallery-load-more-btn',
			)
		);

		$this->start_controls_tabs( 'dethemekit_gallery_button_style_tabs' );

		$this->start_controls_tab(
			'dethemekit_gallery_button_style_normal',
			array(
				'label' => __( 'Normal', 'dethemekit-for-elementor' ),
			)
		);

		$this->add_control(
			'dethemekit_gallery_button_color',
			array(
				'label'     => __( 'Text Color', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_SECONDARY,
				],
				'selectors' => array(
					'{{WRAPPER}} .dethemekit-gallery-load-more-btn'  => 'color: {{VALUE}};',
					'{{WRAPPER}} .dethemekit-gallery-load-more-btn .dethemekit-loader'  => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'dethemekit_gallery_button_spin_color',
			array(
				'label'     => __( 'Spinner Color', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_SECONDARY,
				],
				'selectors' => array(
					'{{WRAPPER}} .dethemekit-gallery-load-more-btn .dethemekit-loader'  => 'border-top-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'dethemekit_gallery_button_text_shadow',
				'selector' => '{{WRAPPER}} .dethemekit-gallery-load-more-btn',
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'dethemekit_gallery_button_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .dethemekit-gallery-load-more-btn',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'dethemekit_gallery_button_border',
				'selector' => '{{WRAPPER}} .dethemekit-gallery-load-more-btn',
			)
		);

		$this->add_control(
			'dethemekit_gallery_button_border_radius',
			array(
				'label'      => __( 'Border Radius', 'dethemekit-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .dethemekit-gallery-load-more-btn' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'dethemekit_gallery_button_box_shadow',
				'selector' => '{{WRAPPER}} .dethemekit-gallery-load-more-btn',
			)
		);

		$this->add_responsive_control(
			'dethemekit_gallery_button_margin',
			array(
				'label'      => __( 'Margin', 'dethemekit-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .dethemekit-gallery-load-more-btn' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'dethemekit_gallery_button_padding',
			array(
				'label'      => __( 'Padding', 'dethemekit-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .dethemekit-gallery-load-more-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'dethemekit_gallery_button_style_hover',
			array(
				'label' => __( 'Hover', 'dethemekit-for-elementor' ),
			)
		);

		$this->add_control(
			'dethemekit_gallery_button_hover_color',
			array(
				'label'     => __( 'Text Hover Color', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_SECONDARY,
				],
				'selectors' => array(
					'{{WRAPPER}} .dethemekit-gallery-load-more-btn:hover'  => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'dethemekit_gallery_button_text_shadow_hover',
				'selector' => '{{WRAPPER}} .dethemekit-gallery-load-more-btn:hover',
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'dethemekit_gallery_button_background_hover',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .dethemekit-gallery-load-more-btn:hover',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'dethemekit_gallery_button_border_hover',
				'selector' => '{{WRAPPER}} .dethemekit-gallery-load-more-btn:hover',
			)
		);

		$this->add_control(
			'button_border_radius_hover',
			array(
				'label'      => __( 'Border Radius', 'dethemekit-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .dethemekit-gallery-load-more-btn:hover' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'dethemekit_gallery_button_shadow_hover',
				'selector' => '{{WRAPPER}} .dethemekit-gallery-load-more-btn:hover',
			)
		);

		$this->add_responsive_control(
			'button_margin_hover',
			array(
				'label'      => __( 'Margin', 'dethemekit-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .dethemekit-gallery-load-more-btn:hover' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'dethemekit_gallery_button_padding_hover',
			array(
				'label'      => __( 'Padding', 'dethemekit-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .dethemekit-gallery-load-more-btn:hover' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_lightbox_style',
			array(
				'label'     => __( 'Lightbox', 'dethemekit-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'dethemekit_gallery_lightbox_type' => 'yes',
				),
			)
		);

		$this->add_control(
			'lightbox_color',
			array(
				'label'     => __( 'Background Color', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'#elementor-lightbox-slideshow-{{ID}}' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'lightbox_ui_color',
			array(
				'label'     => __( 'UI Color', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'#elementor-lightbox-slideshow-{{ID}} .dialog-lightbox-close-button, #elementor-lightbox-slideshow-{{ID}} .elementor-swiper-button' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'lightbox_ui_hover_color',
			array(
				'label'     => __( 'UI Hover Color', 'dethemekit-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'#elementor-lightbox-slideshow-{{ID}} .dialog-lightbox-close-button:hover, #elementor-lightbox-slideshow-{{ID}} .elementor-swiper-button:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		$this->update_controls();

	}

	/**
	 * Filter Cats
	 *
	 * Formats Category to be inserted in class attribute.
	 *
	 * @since 2.1.0
	 * @access public
	 *
	 * @param string $string category slug.
	 *
	 * @return string $cat_filtered slug filtered.
	 */
	public function filter_cats( $string ) {

		$cat_filtered = mb_strtolower( $string );

		if ( strpos( $cat_filtered, 'class' ) || strpos( $cat_filtered, 'src' ) ) {
			$cat_filtered = substr( $cat_filtered, strpos( $cat_filtered, '"' ) + 1 );
			$cat_filtered = strtok( $cat_filtered, '"' );
			$cat_filtered = preg_replace( '/[http:.]/', '', $cat_filtered );
			$cat_filtered = str_replace( '/', '', $cat_filtered );
		}

		$cat_filtered = str_replace( ', ', ',', $cat_filtered );
		$cat_filtered = preg_replace( '/[\s_&@!#%]/', '-', $cat_filtered );
		$cat_filtered = str_replace( ',', ' ', $cat_filtered );

		return $cat_filtered;
	}

	/**
	 * Render Filter Tabs on the frontend
	 *
	 * @since 2.1.0
	 * @access protected
	 *
	 * @param string  $first Class for the first category.
	 * @param integer $active_index active category index.
	 */
	protected function render_filter_tabs( $first, $active_index ) {

		$settings = $this->get_settings_for_display();

		?>

		<div class="dethemekit-img-gallery-filter">
			<ul class="dethemekit-gallery-cats-container">
				<?php if ( 'yes' === $settings['dethemekit_gallery_first_cat_switcher'] ) : ?>
					<li>
						<a href="javascript:;" class="category <?php echo esc_attr( $first ); ?>" data-filter="*">
							<span><?php echo wp_kses_post( $settings['dethemekit_gallery_first_cat_label'] ); ?></span>
						</a>
					</li>
					<?php
				endif;
				foreach ( $settings['dethemekit_gallery_cats_content'] as $index => $category ) {
					if ( ! empty( $category['dethemekit_gallery_img_cat'] ) ) {
						$cat_filtered = $this->filter_cats( $category['dethemekit_gallery_img_cat'] );

						$key = 'dethemekit_grid_category_' . $index;

						if ( $active_index === $index ) {
							$this->add_render_attribute( $key, 'class', 'active' );
						}

						$this->add_render_attribute(
							$key,
							'class',
							array(
								'category',
								'elementor-repeater-item-' . $category['_id'],
							)
						);

						$slug = sprintf( '.%s', $cat_filtered );

						$this->add_render_attribute( $key, 'data-filter', $slug );
						?>
						<li>
							<a href="javascript:;" <?php echo wp_kses_post( $this->get_render_attribute_string( $key ) ); ?>>
								<span><?php echo wp_kses_post( $category['dethemekit_gallery_img_cat'] ); ?></span>
							</a>
						</li>
						<?php
					}
				}
				?>
			</ul>
		</div>

		<?php
	}

	/**
	 * Render Grid output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 2.1.0
	 * @access protected
	 */
	protected function render() {

		$settings = $this->get_settings_for_display();

		$filter = $settings['dethemekit_gallery_filter'];

		$skin = $settings['dethemekit_gallery_img_style'];

		$layout = $settings['dethemekit_gallery_img_size_select'];

		$lightbox = $settings['dethemekit_gallery_light_box'];

		$lightbox_type = $settings['dethemekit_gallery_lightbox_type'];

		$show_play = $settings['dethemekit_gallery_video_icon'];

		if ( 'yes' === $settings['dethemekit_gallery_responsive_switcher'] ) {
			$min_size = $settings['dethemekit_gallery_min_range'] . 'px';
			$max_size = $settings['dethemekit_gallery_max_range'] . 'px';
		}

		$category = '*';

		if ( 'yes' === $filter ) {

			if ( ! empty( $settings['dethemekit_gallery_active_cat'] ) || 0 === $settings['dethemekit_gallery_active_cat'] ) {

				if ( 'yes' !== $settings['dethemekit_gallery_first_cat_switcher'] ) {
					$active_index          = $settings['dethemekit_gallery_active_cat'];
					$active_category       = $settings['dethemekit_gallery_cats_content'][ $active_index ]['dethemekit_gallery_img_cat'];
					$category              = '.' . $this->filter_cats( $active_category );
					$active_category_index = $settings['dethemekit_gallery_active_cat'];

				} else {
					$active_category_index = $settings['dethemekit_gallery_active_cat'] - 1;
				}
			} else {
				$active_category_index = 'yes' === $settings['dethemekit_gallery_first_cat_switcher'] ? -1 : 0;
			}

			$is_all_active = ( 0 > $active_category_index ) ? 'active' : '';

		}

		if ( 'original' === $layout ) {
			$layout = 'masonry';
		} elseif ( 'one_size' === $layout ) {
			$layout = 'fitRows';
		}

		$ltr_mode = 'yes' === $settings['dethemekit_gallery_rtl_mode'] ? false : true;

		$shuffle = 'yes' === $settings['dethemekit_gallery_shuffle'] ? true : false;

		$shuffle_onload = 'yes' === $settings['dethemekit_gallery_shuffle_onload'] ? 'random' : 'original-order';

		$grid_settings = array(
			'img_size'   => $layout,
			'filter'     => $filter,
			'theme'      => $settings['dethemekit_gallery_lightbox_theme'],
			'active_cat' => $category,
			'ltr_mode'   => $ltr_mode,
			'shuffle'    => $shuffle,
			'sort_by'    => $shuffle_onload,
			'skin'       => $skin,
		);

		if ( 'yes' === $filter ) {
			$grid_settings['flag'] = ! empty( $settings['url_flag'] ) ? $settings['url_flag'] : 'cat';
		}

		$load_more = 'yes' === $settings['dethemekit_gallery_load_more'] ? true : false;

		if ( $load_more ) {
			$minimum      = ! empty( $settings['dethemekit_gallery_load_minimum'] ) ? $settings['dethemekit_gallery_load_minimum'] : 6;
			$click_number = ! empty( $settings['dethemekit_gallery_load_click_number'] ) ? $settings['dethemekit_gallery_load_click_number'] : 6;

			$grid_settings = array_merge(
				$grid_settings,
				array(
					'load_more'    => $load_more,
					'minimum'      => $minimum,
					'click_images' => $click_number,
				)
			);
		}

		if ( 'yes' === $lightbox ) {
			$grid_settings = array_merge(
				$grid_settings,
				array(
					'light_box'     => $lightbox,
					'lightbox_type' => $lightbox_type,
					'overlay'       => 'yes' === $settings['dethemekit_gallery_overlay_gallery'] ? true : false,
				)
			);
		} else {
			$this->add_render_attribute(
				'grid',
				array(
					'class' => array(
						'dethemekit-img-gallery-no-lightbox',
					),
				)
			);
		}

		$this->add_render_attribute(
			'grid',
			array(
				'id'    => 'dethemekit-img-gallery-' . esc_attr( $this->get_id() ),
				'class' => array(
					'dethemekit-img-gallery',
					'dethemekit-img-gallery-' . $layout,
					$settings['dethemekit_gallery_img_effect'],
				),
			)
		);

		if ( $show_play ) {
			$this->add_render_attribute(
				'grid',
				array(
					'class' => array(
						'dethemekit-gallery-icon-show',
					),
				)
			);
		}

		$this->add_render_attribute(
			'gallery_container',
			array(
				'class'         => 'dethemekit-gallery-container',
				'data-settings' => wp_json_encode( $grid_settings ),
			)
		);

		$this->add_render_attribute(
			'image_container',
			'class',
			array(
				'pa-gallery-img-container',
			)
		);

		?>

	<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'grid' ) ); ?>>
		<?php
		if ( 'yes' === $filter ) :
			$this->render_filter_tabs( $is_all_active, $active_category_index );
		endif;
		?>

		<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'gallery_container' ) ); ?>>

			<?php if ( 'metro' === $layout ) : ?>
				<div class="grid-sizer"></div>
				<?php
			endif;

			foreach ( $settings['dethemekit_gallery_img_content'] as $index => $image ) :
				if ( wp_http_validate_url( $image['dethemekit_gallery_img']['url'] ) === false ) {
					continue;
				}

				$key = 'gallery_item_' . $index;

				$this->add_render_attribute(
					$key,
					array(
						'class' => array(
							'dethemekit-gallery-item',
							'elementor-repeater-item-' . $image['_id'],
							$this->filter_cats( $image['dethemekit_gallery_img_category'] ),
						),
					)
				);

				if ( 'metro' === $layout ) {

					$cells = array(
						'cells'         => $image['dethemekit_gallery_image_cell']['size'],
						'vcells'        => $image['dethemekit_gallery_image_vcell']['size'],
						'cells_tablet'  => $image['dethemekit_gallery_image_cell_tablet']['size'],
						'vcells_tablet' => $image['dethemekit_gallery_image_vcell_tablet']['size'],
						'cells_mobile'  => $image['dethemekit_gallery_image_cell_mobile']['size'],
						'vcells_mobile' => $image['dethemekit_gallery_image_vcell_mobile']['size'],
					);

					$this->add_render_attribute( $key, 'data-metro', wp_json_encode( $cells ) );
				}

				if ( $image['dethemekit_gallery_video'] ) {
					$this->add_render_attribute( $key, 'class', 'dethemekit-gallery-video-item' );
				}
				?>
				<div <?php echo wp_kses_post( $this->get_render_attribute_string( $key ) ); ?>>
				<div class="pa-gallery-img <?php echo esc_attr( $skin ); ?>" onclick="">
					<div <?php echo wp_kses_post( $this->get_render_attribute_string( 'image_container' ) ); ?>>
						<?php
							$video_link = $this->render_grid_item( $image, $index );

							$image['video_link'] = $video_link;
						if ( 'style3' === $skin ) :
							?>
							<div class="pa-gallery-icons-wrapper">
								<div class="pa-gallery-icons-inner-container">
									<?php $this->render_icons( $image, $index ); ?>
								</div>
							</div>
						<?php endif; ?>
					</div>
					<?php
					if ( 'style2' !== $skin ) :
						if ( 'default' === $skin || 'style1' === $skin ) :
							?>
							<div class="pa-gallery-icons-wrapper">
								<div class="pa-gallery-icons-inner-container">
									<?php $this->render_icons( $image, $index ); ?>
								</div>
							</div>
							<?php
						endif;
						$this->render_image_caption( $image );
					else :
						?>
						<div class="pa-gallery-icons-caption-container">
							<div class="pa-gallery-icons-caption-cell">
								<?php
										$this->render_icons( $image, $index );
										$this->render_image_caption( $image );
								?>
							</div>
						</div>
						<?php
					endif;
					if ( $image['dethemekit_gallery_video'] ) :
						?>
							</div>
						</div>
						<?php
						continue;
					endif;
					if ( 'yes' === $image['dethemekit_gallery_link_whole'] ) {

						if ( 'url' === $image['dethemekit_gallery_img_link_type'] && ! empty( $image['dethemekit_gallery_img_link']['url'] ) ) {

							$icon_link = $image['dethemekit_gallery_img_link']['url'];
							$external  = $image['dethemekit_gallery_img_link']['is_external'] ? 'target="_blank"' : '';
							$no_follow = $image['dethemekit_gallery_img_link']['nofollow'] ? 'rel="nofollow"' : '';

							?>
								<a class="pa-gallery-whole-link" href="<?php echo esc_url( $icon_link ); ?>" <?php echo wp_kses_post( $external ); ?><?php echo wp_kses_post( $no_follow ); ?>></a>

							<?php
						} elseif ( 'link' === $image['dethemekit_gallery_img_link_type'] ) {
							$icon_link = get_permalink( $image['dethemekit_gallery_img_existing'] );
							?>
								<a class="pa-gallery-whole-link" href="<?php echo esc_url( $icon_link ); ?>"></a>
							<?php
						}
					} elseif ( 'yes' === $lightbox ) {

						if ( 'yes' === $image['dethemekit_gallery_lightbox_whole'] ) {

							$lightbox_key = 'image_lightbox_' . $index;

							$this->add_render_attribute(
								$lightbox_key,
								array(
									'class' => 'pa-gallery-whole-link',
									'href'  => wp_http_validate_url( $item['dethemekit_gallery_img']['url'] ) ? $item['dethemekit_gallery_img']['url'] : '',
								)
							);

							if ( 'default' !== $lightbox_type ) {

								$this->add_render_attribute(
									$lightbox_key,
									array(
										'data-elementor-open-lightbox'      => $lightbox_type,
										'data-elementor-lightbox-slideshow' => $this->get_id(),
									)
								);

								if ( 'yes' === $settings['lightbox_show_title'] ) {

									$alt = Control_Media::get_image_alt( $image['dethemekit_gallery_img'] );

									$this->add_render_attribute( $lightbox_key, 'data-elementor-lightbox-title', $alt );

								}
							} else {

								$rel = sprintf( 'prettyPhoto[dethemekit-grid-%s]', $this->get_id() );

								$this->add_render_attribute(
									$lightbox_key,
									array(
										'data-rel' => $rel,
									)
								);
							}

							?>

								<a <?php echo wp_kses_post( $this->get_render_attribute_string( $lightbox_key ) ); ?>></a>

							<?php
						}
					}
					?>
				</div>
			</div>
			<?php endforeach; ?>
		</div>

		<?php if ( 'yes' === $settings['dethemekit_gallery_load_more'] ) : ?>
			<div class="dethemekit-gallery-load-more dethemekit-gallery-btn-hidden">
				<button class="dethemekit-gallery-load-more-btn">
					<span><?php echo wp_kses_post( $settings['dethemekit_gallery_load_more_text'] ); ?></span>
					<div class="dethemekit-loader"></div>
				</button>
			</div>
		<?php endif; ?>

	</div>

		<?php
		if ( \Elementor\Plugin::instance()->editor->is_edit_mode() ) {

			if ( 'metro' !== $settings['dethemekit_gallery_img_size_select'] ) {
				$this->render_editor_script();
			}
		}
		?>

		<?php if ( 'yes' === $settings['dethemekit_gallery_responsive_switcher'] ) : ?>
		<style>
			@media( min-width: <?php echo esc_attr( $min_size ); ?> ) and ( max-width:<?php echo esc_attr( $max_size ); ?> ) {
				#dethemekit-img-gallery-<?php echo esc_attr( $this->get_id() ); ?> .dethemekit-gallery-caption {
					display: none;
				}  
			}
		</style>
	<?php endif; ?>

		<?php
	}

	/**
	 * Render Grid Image
	 *
	 * Written in PHP and used to generate the final HTML for image.
	 *
	 * @since 3.6.4
	 * @access protected
	 *
	 * @param array   $item image repeater item.
	 * @param integer $index item index.
	 */
	protected function render_grid_item( $item, $index ) {

		$settings = $this->get_settings();

		$is_video = $item['dethemekit_gallery_video'];

		$alt = Control_Media::get_image_alt( $item['dethemekit_gallery_img'] );

		$key = 'image_' . $index;

		$image_src      = $item['dethemekit_gallery_img'];
		$image_src_size = Group_Control_Image_Size::get_attachment_image_src( $image_src['id'], 'thumbnail', $settings );

		$image_src = empty( $image_src_size ) ? $image_src['url'] : $image_src_size;

		if ( $is_video ) {

			$type = $item['dethemekit_gallery_video_type'];

			if ( 'hosted' !== $type ) {
				$embed_params = $this->get_embed_params( $item );
				$link         = Embed::get_embed_url( $item['dethemekit_gallery_video_url'], $embed_params );

				if ( empty( $image_src ) ) {
					$video_props = Embed::get_video_properties( $link );
					$id          = $video_props['video_id'];
					$type        = $video_props['provider'];
					$size        = '';
					if ( 'youtube' === $type ) {
						$size = $settings['dethemekit_gallery_yt_thumbnail_size'];
					}
					$image_src = Helper_Functions::get_video_thumbnail( $id, $type, $size );
				}
			} else {
				$video_params = $this->get_hosted_params( $item );
			}
		}

		$this->add_render_attribute(
			$key,
			array(
				'class' => 'pa-gallery-image',
				'src'   => $image_src,
				'alt'   => $alt,
			)
		);

		if ( $is_video ) {
			?>
			<div class="dethemekit-gallery-video-wrap" data-type="<?php echo esc_attr( $item['dethemekit_gallery_video_type'] ); ?>">
				<?php if ( 'hosted' !== $item['dethemekit_gallery_video_type'] ) : ?>
					<div class="dethemekit-gallery-iframe-wrap" data-src="<?php echo esc_url( $link ); ?>"></div>
					<?php
				else :
					$link = empty( $item['dethemekit_gallery_video_self_url'] ) ? $item['dethemekit_gallery_video_self']['url'] : $item['dethemekit_gallery_video_self_url'];
					?>
					<video src="<?php echo esc_url( $link ); ?>" <?php echo wp_kses_post( Utils::render_html_attributes( $video_params ) ); ?>></video>
				<?php endif; ?>
			</div>
		<?php } ?>

		<img <?php echo wp_kses_post( $this->get_render_attribute_string( $key ) ); ?>>
		<?php

		return ( isset( $link ) && ! empty( $link ) ) ? $link : false;
	}

	/**
	 * Render Icons
	 *
	 * Render Lightbox and URL Icons HTML
	 *
	 * @since 3.6.4
	 * @access protected
	 *
	 * @param array   $item grid image repeater item.
	 * @param integer $index item index.
	 */
	protected function render_icons( $item, $index ) {

		$settings = $this->get_settings_for_display();

		$lightbox_key = 'image_lightbox_' . $index;

		$link_key = 'image_link_' . $index;

		$href = wp_http_validate_url( $item['dethemekit_gallery_img']['url'] ) ? $item['dethemekit_gallery_img']['url'] : '';

		$lightbox = $settings['dethemekit_gallery_light_box'];

		$lightbox_type = $settings['dethemekit_gallery_lightbox_type'];

		$is_video = $item['dethemekit_gallery_video'];

		$id = $this->get_id();

		if ( $is_video ) {

			$type = $item['dethemekit_gallery_video_type'];

			$this->add_render_attribute(
				$lightbox_key,
				array(
					'class' => array(
						'pa-gallery-lightbox-wrap',
						'pa-gallery-video-icon',
					),
				)
			);

			if ( 'yes' === $lightbox ) {

				$lightbox_options = array(
					'type'         => 'video',
					'videoType'    => $item['dethemekit_gallery_video_type'],
					'url'          => $item['video_link'],
					'modalOptions' => array(
						'id'               => 'elementor-lightbox-' . $this->get_id(),
						'videoAspectRatio' => '169',
					),
				);

				if ( 'hosted' === $type ) {
					$lightbox_options['videoParams'] = $this->get_hosted_params( $item );
				}

				$this->add_render_attribute(
					$lightbox_key,
					array(
						'data-elementor-open-lightbox' => 'yes',
						'data-elementor-lightbox'      => wp_json_encode( $lightbox_options ),
					)
				);

			}

			?>
			<div <?php echo wp_kses_post( $this->get_render_attribute_string( $lightbox_key ) ); ?>>
				<a class="pa-gallery-magnific-image pa-gallery-video-icon">
					<span>
						<?php
						Icons_Manager::render_icon( $settings['dethemekit_gallery_videos_icon'], array( 'aria-hidden' => 'true' ) );
						?>
					</span>
				</a>
			</div>

			<?php
			return;
		}

		if ( 'yes' === $lightbox ) {

			if ( 'yes' !== $item['dethemekit_gallery_lightbox_whole'] ) {

				$this->add_render_attribute(
					$lightbox_key,
					array(
						'class' => 'pa-gallery-magnific-image',
						'href'  => $href,
					)
				);

				if ( 'default' !== $lightbox_type ) {

					$this->add_render_attribute(
						$lightbox_key,
						array(
							'data-elementor-open-lightbox' => $lightbox_type,
							'data-elementor-lightbox-slideshow' => $id,
						)
					);

					if ( 'yes' === $settings['lightbox_show_title'] ) {

						$alt = Control_Media::get_image_alt( $item['dethemekit_gallery_img'] );

						$this->add_render_attribute( $lightbox_key, 'data-elementor-lightbox-title', $alt );

					}
				} else {

					$rel = sprintf( 'prettyPhoto[dethemekit-grid-%s]', $this->get_id() );

					$this->add_render_attribute(
						$lightbox_key,
						array(
							'data-rel' => $rel,
						)
					);

				}

				?>

					<a <?php echo wp_kses_post( $this->get_render_attribute_string( $lightbox_key ) ); ?>>
						<span>
							<?php
							Icons_Manager::render_icon( $settings['dethemekit_gallery_lightbox_icon'], array( 'aria-hidden' => 'true' ) );
							?>
						</span>
					</a>
				<?php
			}
		}

		if ( ! empty( $item['dethemekit_gallery_img_link']['url'] ) || ! empty( $item['dethemekit_gallery_img_existing'] ) ) {

			if ( 'yes' !== $item['dethemekit_gallery_link_whole'] ) {

				$icon_link = '';

				$this->add_render_attribute(
					$link_key,
					array(
						'class' => 'pa-gallery-img-link',
					)
				);

				if ( 'url' === $item['dethemekit_gallery_img_link_type'] && ! empty( $item['dethemekit_gallery_img_link']['url'] ) ) {

					$icon_link = $item['dethemekit_gallery_img_link']['url'];

					$external = $item['dethemekit_gallery_img_link']['is_external'] ? '_blank' : '';

					$no_follow = $item['dethemekit_gallery_img_link']['nofollow'] ? 'nofollow' : '';

					$this->add_render_attribute(
						$link_key,
						array(
							'href'   => esc_url( $icon_link ),
							'target' => $external,
							'rel'    => $no_follow,
						)
					);

				} elseif ( 'link' === $item['dethemekit_gallery_img_link_type'] && ! empty( $item['dethemekit_gallery_img_existing'] ) ) {

					$icon_link = get_permalink( $item['dethemekit_gallery_img_existing'] );

					$this->add_render_attribute(
						$link_key,
						array(
							'href' => esc_url( $icon_link ),
						)
					);

				}

				if ( ! empty( $icon_link ) ) {
					?>
					<a <?php echo wp_kses_post( $this->get_render_attribute_string( $link_key ) ); ?>>
						<span>
							<?php
							Icons_Manager::render_icon( $settings['dethemekit_gallery_links_icon'], array( 'aria-hidden' => 'true' ) );
							?>
						</span>
					</a>
					<?php
				}
			}
		}
	}

	/**
	 * Render Image Caption
	 *
	 * Written in PHP to render the final HTML for image title and description
	 *
	 * @since 3.6.4
	 * @access protected
	 *
	 * @param array $item image repeater item.
	 */
	protected function render_image_caption( $item ) {

		$title = $item['dethemekit_gallery_img_name'];

		$description = $item['dethemekit_gallery_img_desc'];

		if ( ! empty( $title ) || ! empty( $description ) ) :
			?>
			<div class="dethemekit-gallery-caption">

				<?php if ( ! empty( $title ) ) : ?>
					<span class="dethemekit-gallery-img-name"><?php echo wp_kses_post( $title ); ?></span>
					<?php
				endif;

				if ( ! empty( $description ) ) :
					?>
					<p class="dethemekit-gallery-img-desc"><?php echo wp_kses_post( $description ); ?></p>
				<?php endif; ?>

			</div>
			<?php
		endif;
	}

	/**
	 * Get Hosted Videos Parameters
	 *
	 * @since 3.7.0
	 * @access private
	 *
	 * @param array $item image repeater item.
	 */
	private function get_hosted_params( $item ) {

		$video_params = array();

		if ( $item['dethemekit_gallery_video_controls'] ) {
			$video_params['controls'] = '';
		}

		if ( $item['dethemekit_gallery_video_mute'] ) {
			$video_params['muted'] = 'muted';
		}

		return $video_params;
	}

	/**
	 * Get embeded videos parameters
	 *
	 * @since 3.7.0
	 * @access private
	 *
	 * @param array $item image repeater item.
	 */
	private function get_embed_params( $item ) {

		$video_params = array();

		$video_params['controls'] = $item['dethemekit_gallery_video_controls'] ? '1' : '0';

		$key = 'youtube' === $item['dethemekit_gallery_video_type'] ? 'mute' : 'muted';

		$video_params[ $key ] = $item['dethemekit_gallery_video_mute'] ? '1' : '0';

		if ( 'vimeo' === $item['dethemekit_gallery_video_type'] ) {
			$video_params['autopause'] = '0';
		}

		return $video_params;
	}

	/**
	 * Update Controls
	 *
	 * @since 3.8.8
	 * @access private
	 */
	private function update_controls() {

		$this->update_responsive_control(
			'dethemekit_gallery_img_border_radius',
			array(
				'type'      => Controls_Manager::DIMENSIONS,
				'selectors' => array(
					'{{WRAPPER}} .pa-gallery-img-container, {{WRAPPER}} .pa-gallery-img:not(.style2) .pa-gallery-icons-wrapper, {{WRAPPER}} .pa-gallery-img.style2 .pa-gallery-icons-caption-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),

			)
		);

		$this->update_responsive_control(
			'dethemekit_gallery_content_border_radius',
			array(
				'type'      => Controls_Manager::DIMENSIONS,
				'selectors' => array(
					'{{WRAPPER}} .dethemekit-gallery-caption' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				),

			)
		);

	}

	/**
	 * Render Editor Masonry Script.
	 *
	 * @since 3.12.3
	 * @access protected
	 */
	protected function render_editor_script() {

		?>
		<script type="text/javascript">
			jQuery( document ).ready( function( $ ) {

				$( '.dethemekit-gallery-container' ).each( function() {

					var $node_id 	= '<?php echo esc_attr( $this->get_id() ); ?>',
						scope 		= $( '[data-id="' + $node_id + '"]' ),
						settings    = $(this).data("settings"),
						selector 	= $(this);

					if ( selector.closest( scope ).length < 1 ) {
						return;
					}

					var masonryArgs = {
						// set itemSelector so .grid-sizer is not used in layout
						filter 			: settings.active_cat,
						itemSelector	: '.dethemekit-gallery-item',
						percentPosition : true,
						layoutMode		: settings.img_size,
					};

					var $isotopeObj = {};

					selector.imagesLoaded( function() {

						$isotopeObj = selector.isotope( masonryArgs );

						selector.find('.dethemekit-gallery-item').resize( function() {
							$isotopeObj.isotope( 'layout' );
						});
					});

				});
			});
		</script>
		<?php
	}
}

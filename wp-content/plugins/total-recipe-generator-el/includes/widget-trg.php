<?php

namespace Total_Recipe_Generator_El\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Utils;
use Elementor\Scheme_Color;
use Elementor\Group_Control_Typography;
use Elementor\Scheme_Typography;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Background;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


class Widget_Total_Recipe_Generator_El extends Widget_Base {

    public function get_name() {
        return 'total-recipe-generator';
    }

    public function get_title() {
        return esc_html__( 'Total Recipe Generator', 'trg_el' );
    }

    public function get_icon() {
        return 'eicon-menu-card';
    }

    /**
     * Get widget keywords.
     *
     * Retrieve the list of keywords the widget belongs to.
     *
     * @since 2.1.0
     * @access public
     *
     * @return array Widget keywords.
     */
    public function get_keywords() {
        return [ 'recipe', 'food', 'cook', 'cooking', 'bake', 'nutrition', 'nutrition facts' ];
    }

    protected function register_controls() {

        $this->start_controls_section(
            'section_general',
            [
                'label' => __('General', 'trg_el'),
            ]
        );

    $this->add_control(
        'template',
        [
            'label' => esc_html__( 'Recipe Template', 'trg_el' ),
            'type' => Controls_Manager::SELECT,
            'options' => [
                'recipe' => __( 'Standard (1 col)', 'trg_el' ),
                'recipe2' => __( 'Ingredients + Method (2 col)', 'trg_el' ),
                'recipe3' => __( 'Method + Ingredients (2 col)', 'trg_el' )
            ],
            'default' => 'recipe',
            'description' => esc_html__( 'Select a recipe template', 'trg_el' )
        ]
    );

    $this->add_responsive_control(
        'method_width',
        [
            'label' => esc_html__( 'Method section width', 'trg_el' ),
            'type' => Controls_Manager::SLIDER,
            'size_units' => [ '%' ],
            'range' => [
                '%' => [
                    'min' => 10,
                    'max' => 100,
                    'step' => 1,
                ]
            ],
            'default' => [
                'unit' => '%',
                'size' => 60,
            ],
            'selectors' => [
                '{{WRAPPER}} .ingredients-section' => 'flex-basis: calc(100% - {{SIZE}}% );',
                '{{WRAPPER}} .method-section' => 'flex-basis: {{SIZE}}%;'
            ],
            'description' => esc_html__( 'Select width for the method section.', 'trg_el' ),
'condition' => [ 'template' => ['recipe2', 'recipe3'] ]
        ]
    );

    $this->add_responsive_control(
            'method_gutter',
            [
                'label' => esc_html__( 'Method section gap', 'trg_el' ),
                'label_block' => true,
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 15,
                    ],
                    'rem' => [
                        'min' => 0,
                        'max' => 15,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 24,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ingredients-section, {{WRAPPER}} .method-section' => 'padding: 0 {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .trg-row' => 'margin-left: -{{SIZE}}{{UNIT}}; margin-right: -{{SIZE}}{{UNIT}};'
                ],
                'description' => esc_html__( 'Select a gap width between methods section and ingredients section.', 'trg_el' ),
'condition' => [ 'template' => ['recipe2', 'recipe3'] ]
            ]
        );

    $this->add_control(
        'method_fullwidth_tablet',
        [
            'type' => Controls_Manager::SWITCHER,
            'label' => esc_html__( 'Full width (Tablet)', 'trg_el' ),
            'default' => '',
            'label_on' => __( 'On', 'trg_el' ),
            'label_off' => __( 'Off', 'trg_el' ),
            'return_value' => 'true',
            'prefix_class' => 'method-full-tablet-',
            'description' => esc_html__( 'Force method and ingredients section full width on tablet.', 'trg_el' )
        ]
    );

    $this->add_control(
        'method_fullwidth_mobile',
        [
            'type' => Controls_Manager::SWITCHER,
            'label' => esc_html__( 'Full width (Mobile)', 'trg_el' ),
            'default' => '',
            'label_on' => __( 'On', 'trg_el' ),
            'label_off' => __( 'Off', 'trg_el' ),
            'return_value' => 'true',
            'prefix_class' => 'method-full-mobile-',
            'description' => esc_html__( 'Force method and ingredients section full width on mobile.', 'trg_el' )
        ]
    );

    $this->add_control(
        'name_src',
        [
            'label' => esc_html__( 'Recipe Name', 'trg_el' ),
            'type' => Controls_Manager::SELECT,
            'options' => [
                'post_title' => __( 'Use Post Title', 'trg_el' ),
                'custom' => __( 'Custom Name', 'trg_el' ),
                'hide' => __( 'Do not show', 'trg_el' )
            ],
            'default' => 'post_title',
            'description' => esc_html__( 'Choose a name source for Recipe', 'trg_el' )
        ]
    );

    $this->add_control(
        'name_txt',
        [
            'label' => esc_html__( 'Custom Recipe Name', 'trg_el' ),
            'type' => Controls_Manager::TEXT,
            'dynamic' => [
                    'active' => true,
            ],
            'default' => '',
            'description' => esc_html__( 'Provide a name for the recipe', 'trg_el' ),
            'condition' => [ 'name_src' => ['custom'] ]
        ]
    );

    $this->add_control(
        'show_date',
        [
            'type' => Controls_Manager::SWITCHER,
            'label' => esc_html__( 'Publish date', 'trg_el' ),
            'default' => '',
            'label_on' => __( 'On', 'trg_el' ),
            'label_off' => __( 'Off', 'trg_el' ),
            'return_value' => 'true',
            'description' => esc_html__( 'Show or hide recipe publish date.', 'trg_el' )
        ]
    );

    $this->add_control(
        'show_author',
        [
            'type' => Controls_Manager::SWITCHER,
            'label' => esc_html__( 'Recipe Author', 'trg_el' ),
            'default' => '',
            'label_on' => __( 'On', 'trg_el' ),
            'label_off' => __( 'Off', 'trg_el' ),
            'return_value' => 'true',
            'description' => esc_html__( 'Show or hide recipe author name.', 'trg_el' )
        ]
    );

    $this->add_control(
        'author_src',
        [
            'label' => esc_html__( 'Recipe Author', 'trg_el' ),
            'type' => Controls_Manager::SELECT,
            'options' => [
                'post_author' => __( 'Use Post Author', 'trg_el' ),
                'custom' => __( 'Custom Author Name', 'trg_el' )
            ],
            'default' => 'post_author',
            'description' => esc_html__( 'Select author name source for recipe', 'trg_el' ),
            'condition' => [ 'show_author' => ['true'] ]
        ]
    );

    $this->add_control(
        'author_name',
        [
            'label' => esc_html__( 'Custom Author name', 'trg_el' ),
            'type' => Controls_Manager::TEXT,
            'default' => '',
            'dynamic' => [
                    'active' => true,
            ],
            'description' => esc_html__( 'Provide name of author', 'trg_el' ),
            'condition' => [ 'author_src' => ['custom'] ]
        ]
    );

    $this->add_control(
        'author_url',
        [
            'label' => esc_html__( 'Author profile URL', 'trg_el' ),
            'type' => Controls_Manager::TEXT,
            'default' => '',
            'dynamic' => [
                    'active' => true,
            ],
            'description' => esc_html__( 'The profile URL of recipe Author. Leave blank to use WordPress user URL.', 'trg_el' )
        ]
    );

    $this->add_control(
        'show_summary',
        [
            'type' => Controls_Manager::SWITCHER,
            'label' => esc_html__( 'Recipe summary', 'trg_el' ),
            'default' => 'true',
            'label_on' => __( 'On', 'trg_el' ),
            'label_off' => __( 'Off', 'trg_el' ),
            'return_value' => 'true',
            'description' => esc_html__( 'Show or hide recipe summary text.', 'trg_el' ),
        ]
    );

    $this->add_control(
        'summary',
        [
            'label' => esc_html__( 'Recipe Summary', 'trg_el' ),
            'type' => Controls_Manager::WYSIWYG,
            'dynamic' => [
                    'active' => true,
            ],
            'default' => 'This is a recipe summary text. It can be a small excerpt about what you are going to cook.',
            'description' => esc_html__( 'Provide a short summary or description of your recipe', 'trg_el' ),
            'condition' => [ 'show_summary' => ['true'] ]
        ]
    );

    $this->end_controls_section();

$this->start_controls_section(
        'section_image',
        [
            'label' => __('Recipe Image', 'trg_el'),
        ]
    );

    $this->add_control(
        'img_src',
        [
            'label' => esc_html__( 'Recipe Image', 'trg_el' ),
            'type' => Controls_Manager::SELECT,
            'options' => [
                'featured' => __( 'Use featured image', 'trg_el' ),
                'media_lib' => __( 'Select from media library', 'trg_el' ),
                'hide' => __( 'Do not show', 'trg_el' )
            ],
            'default' => 'featured',
            'description' => esc_html__( 'Select image source', 'trg_el' )
        ]
    );

    $this->add_control(
        'img_lib',
        [
            'label' => esc_html__( 'Add recipe image', 'trg_el' ),
            'type' => Controls_Manager::MEDIA,
            'label_block' => true,
            'dynamic' => [
                    'active' => true,
            ],
            'default' => ['url' => ''],
            'description' => esc_html__( 'Add a recipe image', 'trg_el' ),
            'condition' => [ 'img_src' => ['media_lib'] ]
        ]
    );

    $this->add_responsive_control(
        'img_align',
        [
            'label' => esc_html__( 'Image Align', 'trg_el' ),
            'type' => \Elementor\Controls_Manager::CHOOSE,
            'options' => [
                'left' => [
                    'title' => __( 'Left', 'trg_el' ),
                    'icon' => 'eicon-text-align-left',
                ],
                'center' => [
                    'title' => __( 'Center', 'trg_el' ),
                    'icon' => 'eicon-text-align-center',
                ],

                'right' => [
                    'title' => __( 'Right', 'trg_el' ),
                    'icon' => 'eicon-text-align-right',
                ],

                'none' => [
                    'title' => __( 'None', 'trg_el' ),
                    'icon' => 'eicon-text-align-justify',
                ]
            ],
            'default' => '',
            'label_block' => true,
            'toggle' => true,
            'prefix_class' => 'recipe-img%s-'
        ]
    );

    $this->add_responsive_control(
        'img_margin',
        [
            'label' => esc_html__( 'Image Margin', 'trg_el' ),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', 'em', 'rem', '%' ],
            'selectors' => [
                '{{WRAPPER}} .recipe-image' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
            ]
        ]
    );

    $this->add_control(
        'imgsize',
        [
            'label' => esc_html__( 'Image Size', 'trg_el' ),
            'type' => Controls_Manager::SELECT,
            'options' => trg_el_get_all_image_sizes(),
        'default' => 'custom',
        'description' => esc_html__( 'Select image size.', 'trg_el' ),
        ]
    );

    $this->add_control(
        'imgwidth',
        [
            'label' => esc_html__( 'Width', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'default' => '',
            'min' => '10',
            'max' => '2000',
            'step' => '1',
            'description' => esc_html__( 'Provide image width (in px, without unit) for the recipe image.', 'trg_el' ),
            'condition' => [ 'imgsize' => ['custom'] ]
        ]
    );

    $this->add_control(
        'imgheight',
        [
            'label' => esc_html__( 'Height', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'default' => '',
            'min' => '10',
            'max' => '2000',
            'step' => '1',
            'description' => esc_html__( 'Provide image height (in px, without unit) for the recipe image.', 'trg_el' ),
            'condition' => [ 'imgsize' => ['custom'] ]
        ]
    );

    $this->add_control(
        'imgquality',
        [
            'label' => esc_html__( 'Quality', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'default' => '',
            'min' => '1',
            'max' => '100',
            'step' => '1',
            'description' => esc_html__( 'Provide image quality (in %, without unit) for the thumbnail image. Range 0 - 100', 'trg_el' ),
            'condition' => [ 'imgsize' => ['custom'] ]
        ]
    );

    $this->add_control(
        'imgcrop',
        [
            'type' => Controls_Manager::SWITCHER,
            'label' => esc_html__( 'Hard Crop', 'trg_el' ),
            'default' => '',
            'label_on' => __( 'On', 'trg_el' ),
            'label_off' => __( 'Off', 'trg_el' ),
            'return_value' => 'true',
            'description' => esc_html__( 'Enable hard cropping of thumbnail image.', 'trg_el' ),
            'condition!' => [ 'img_src' => ['ext'] ],
            'condition' => [ 'imgsize' => ['custom'] ]
        ]
    );

    $this->end_controls_section();

    $this->start_controls_section(
        'section_meta',
        [
            'label' => __('Recipe Meta', 'trg_el')
        ]
    );

    $this->add_control(
        'prep_time',
        [
            'label' => esc_html__( 'Preparation Time (Minutes)', 'trg_el' ),
            'type' => Controls_Manager::TEXT,
            'dynamic' => [
                    'active' => true,
            ],
            'default' => '5',
            /*'min' => '1',
            'max' => '99999',
            'step' => '1',*/
            'description' => esc_html__( 'Provide preparation time (in minutes). E.g. 10', 'trg_el' ),
        ]
    );

    $this->add_control(
        'cook_time',
        [
            'label' => esc_html__( 'Cooking Time (Minutes)', 'trg_el' ),
            'type' => Controls_Manager::TEXT,
            'dynamic' => [
                    'active' => true,
            ],
            'default' => '10',
            /*'min' => '1',
            'max' => '99999',
            'step' => '1',*/
            'description' => esc_html__( 'Provide cooking time (in minutes). E.g. 30', 'trg_el' ),
        ]
    );

    $this->add_control(
        'perform_time',
        [
            'label' => esc_html__( 'Perform Time (Minutes)', 'trg_el' ),
            'type' => Controls_Manager::TEXT,
            'dynamic' => [
                    'active' => true,
            ],
            'default' => '',
            /*'min' => '1',
            'max' => '99999',
            'step' => '1',*/
            'description' => esc_html__( 'Provide perform time (in minutes). E.g. 30. This can be used to show resting time, or the time it takes in performing the recipe method.', 'trg_el' ),
        ]
    );

    $this->add_control(
        'total_time',
        [
            'label' => esc_html__( 'Total Time (Minutes)', 'trg_el' ),
            'type' => Controls_Manager::TEXT,
            'dynamic' => [
                    'active' => true,
            ],
            'default' => '',
            /*'min' => '1',
            'max' => '99999',
            'step' => '1',*/
            'description' => esc_html__( 'Optional total recipe time (in minutes) if prep time, cook time and perform time are not provided. E.g. 20. Leave blank for auto calculation from prep time, cook time and perform time.', 'trg_el' ),
        ]
    );

    $this->add_control(
        'ready_in',
        [
            'label' => esc_html__( 'Ready in', 'trg_el' ),
            'type' => Controls_Manager::TEXT,
            'default' => '',
            'description' => esc_html__( '**DEPRECATED: Use Perform Time option instead of this one.', 'trg_el' ),
        ]
    );

    $this->add_control(
        'recipe_yield',
        [
            'label' => esc_html__( 'Recipe Yield', 'trg_el' ),
            'type' => Controls_Manager::TEXT,
            'dynamic' => [
                    'active' => true,
            ],
            'default' => '',
            'description' => esc_html__( 'Provide a recipe yield. E.g. 1 Pizza, or 1 Bowl Rice', 'trg_el' ),
        ]
    );

    $this->add_control(
        'serving_size',
        [
            'label' => esc_html__( 'Serving Size', 'trg_el' ),
            'type' => Controls_Manager::TEXT,
            'dynamic' => [
                    'active' => true,
            ],
            'default' => '',
            'description' => esc_html__( 'Provide a serving size per container. E.g. 1 Piece(20g), or 100g', 'trg_el' ),
        ]
    );

    $this->add_control(
        'calories',
        [
            'label' => esc_html__( 'Energy (Calories per serving)', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'description' => esc_html__( 'Provide approximate calories (without unit) per serving. E.g. 240', 'trg_el' ),
        ]
    );

    $this->add_control(
        'total_cost',
        [
            'label' => esc_html__( 'Total Cost', 'trg_el' ),
            'type' => Controls_Manager::TEXT,
            'dynamic' => [
                    'active' => true,
            ],
            'default' => '',
            'description' => esc_html__( 'Provide total cost of recipe.', 'trg_el' ),
        ]
    );

    $this->add_control(
        'cost_per_serving',
        [
            'label' => esc_html__( 'Cost per serving', 'trg_el' ),
            'type' => Controls_Manager::TEXT,
            'dynamic' => [
                    'active' => true,
            ],
            'default' => '',
            'description' => esc_html__( 'Provide cost per serving.', 'trg_el' ),
        ]
    );

    $this->add_control(
        'cust_meta_heading',
        [
            'label' => esc_html__( 'Custom Info Board Meta', 'trg_el' ),
            'type' => Controls_Manager::HEADING,
            'separator' => 'before'
        ]
    );

    $this->add_control(
        'cust_meta',
        [
            'type' => Controls_Manager::REPEATER,
            'fields' => [
                [
                    'name' => 'title',
                    'label' => esc_html__( 'Meta Label', 'trg_el' ),
                    'type' => Controls_Manager::TEXT,
                    'dynamic' => [
                            'active' => true,
                    ],
                    'default' => esc_html__( 'Meta Label', 'trg_el' ),
                    'description' => esc_html__( 'The label for custom meta item. E.g. Age Group', 'trg_el' ),
                ],

                [
                    'name' => 'value',
                    'label' => esc_html__( 'Meta Value', 'trg_el' ),
                    'type' => Controls_Manager::TEXT,
                    'dynamic' => [
                            'active' => true,
                    ],
                    'default' => esc_html__( 'Label Value', 'trg_el' ),
                    'description' => esc_html__( 'The value for custom meta item. E.g. 30 Yr. to 60 Yr.', 'trg_el' ),
                ]
            ],
            'description' => esc_html__( 'Add custom meta for the recipe.', 'trg_el' )
        ]
    );

    $this->add_control(
        'recipe_cuisine',
        [
            'label' => esc_html__( 'Recipe Cuisine', 'trg_el' ),
            'type' => Controls_Manager::SELECT2,
            'options' => apply_filters( 'trg_cuisine_list', [
                    __( 'American', 'trg_el' ) => __( 'American', 'trg_el' ),
                    __( 'Chinese', 'trg_el' ) => __( 'Chinese', 'trg_el' ),
                    __( 'French', 'trg_el' ) => __( 'French', 'trg_el' ),
                    __( 'Indian', 'trg_el' ) => __( 'Indian', 'trg_el' ),
                    __( 'Italian', 'trg_el' ) => __( 'Italian', 'trg_el' ),
                    __( 'Japanese', 'trg_el' ) => __( 'Japanese', 'trg_el' ),
                    __( 'Mediterranean', 'trg_el' ) => __( 'Mediterranean', 'trg_el' ),
                    __( 'Mexican', 'trg_el' ) => __( 'Mexican', 'trg_el' ),
                ]
            ),
            'default' => [ __( 'American', 'trg_el' ), __( 'French', 'trg_el' ) ],
            'description' => esc_html__( 'Select recipe cuisine from above list or use custom field below', 'trg_el' ),
            'multiple' => true
        ]
    );

    $this->add_control(
        'recipe_cuisine_other',
        [
            'label' => esc_html__( 'Other recipe cuisine', 'trg_el' ),
            'type' => Controls_Manager::TEXT,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'description' => esc_html__( 'Provide comma separated cuisines if not in above list. E.g. Rajasthani, Gujarati', 'trg_el' ),
        ]
    );

    $this->add_control(
        'recipe_category',
        [
        'label' => esc_html__( 'Course', 'trg_el' ),
        'type' => Controls_Manager::SELECT2,
        'options' => apply_filters( 'trg_category_list', [
            __( 'Appetizer', 'trg_el' ) => __( 'Appetizer', 'trg_el' ),
            __( 'Breakfast', 'trg_el' ) => __( 'Breakfast', 'trg_el' ),
            __( 'Dessert', 'trg_el' ) => __( 'Dessert', 'trg_el' ),
            __( 'Drinks', 'trg_el' ) => __( 'Drinks', 'trg_el' ),
            __( 'Main Course', 'trg_el' ) => __( 'Main Course', 'trg_el' ),
            __( 'Salad', 'trg_el' ) => __( 'Salad', 'trg_el' ),
            __( 'Snack', 'trg_el' ) => __( 'Snack', 'trg_el' ),
            __( 'Soup', 'trg_el' ) => __( 'Soup', 'trg_el' ),
            ]
        ),
        'default' => ['Appetizer', 'Breakfast'],
        'description' => esc_html__( 'Select recipe course/categories from above list or use custom field below', 'trg_el' ),
        'multiple' => true
        ]
    );

    $this->add_control(
        'recipe_category_other',
        [
            'label' => esc_html__( 'Other recipe course', 'trg_el' ),
            'type' => Controls_Manager::TEXT,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'description' => esc_html__( 'Provide comma separated categories if not in above list. E.g. Lunch, Starter', 'trg_el' ),
        ]
    );

    $this->add_control(
        'show_tags',
        [
            'label' => esc_html__( 'Show Tags', 'trg_el' ),
            'type' => Controls_Manager::SWITCHER,
            'default' => '',
            'label_on' => __( 'On', 'trg_el' ),
            'label_off' => __( 'Off', 'trg_el' ),
            'return_value' => 'true',
            'description' => esc_html__( 'Enabling this option will show post tags. You can add tags in the "Tags" panel of post edit screen.', 'trg_el' ),
        ]
    );

    $this->add_control(
        'recipe_keywords',
        [
            'label' => esc_html__( 'Recipe Keywords', 'trg_el' ),
            'type' => Controls_Manager::TEXT,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'description' => esc_html__( 'Provide comma separated keywords relevant to your recipe. E.g. fast food, quick meal, vegetarian. These values will be used for Schema only.', 'trg_el' ),
        ]
    );

    $this->add_control(
        'cooking_method',
        [
            'label' => esc_html__( 'Cooking Method', 'trg_el' ),
            'type' => Controls_Manager::TEXT,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'description' => esc_html__( 'Provide a cooking method. E.g. Roasting, Steaming', 'trg_el' ),
        ]
    );

    $this->add_control(
        'suitable_for_diet',
        [
            'label' => esc_html__( 'Suitable for Diet', 'trg_el' ),
            'type' => Controls_Manager::SELECT2,
            'options' => [
                'DiabeticDiet' => __( 'Diabetic', 'trg_el' ),
                'GlutenFreeDiet' => __( 'Gluten Free', 'trg_el' ),
                'HalalDiet' => __( 'Halal', 'trg_el' ),
                'HinduDiet' => __( 'Hindu', 'trg_el' ),
                'KosherDiet' => __( 'Kosher', 'trg_el' ),
                'LowCalorieDiet' => __( 'Low Calorie', 'trg_el' ),
                'LowFatDiet' => __( 'Low Fat', 'trg_el' ),
                'LowLactoseDiet' => __( 'Low Lactose', 'trg_el' ),
                'LowSaltDiet' => __( 'Low Salt', 'trg_el' ),
                'VeganDiet' => __( 'Vegan', 'trg_el' ),
                'VegetarianDiet' => __( 'Vegetarian', 'trg_el' )
            ],
            'default' => [ __( 'Vegetarian', 'trg_el' ), __( 'Low Salt', 'trg_el' ) ],
            'description' => esc_html__( 'Select diet for which this recipe is suitable. Remove selection using "ctrl + select" if not applicable.', 'trg_el' ),
            'multiple' => true
        ]
    );

    $this->add_control(
        'suitable_for_diet_other',
        [
            'label' => esc_html__( 'Other Suitable for Diet', 'trg_el' ),
            'type' => Controls_Manager::TEXT,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'description' => esc_html__( 'Provide comma separated values for other suitable for diet. E.g. Organic, Jain', 'trg_el' ),
        ]
    );

    $this->add_control(
        'cust_attr_heading',
        [
            'label' => esc_html__( 'Custom Recipe Attributes', 'trg_el' ),
            'type' => Controls_Manager::HEADING,
            'separator' => 'before'
        ]
    );

    $this->add_control(
        'cust_attr',
        [
            'type' => Controls_Manager::REPEATER,
            'fields' => [
                [
                    'name' => 'title',
                    'label' => esc_html__( 'Attribute Label', 'trg_el' ),
                    'type' => Controls_Manager::TEXT,
                    'dynamic' => [
                        'active' => true,
                    ],
                    'default' => esc_html__( 'Attribute Label', 'trg_el' ),
                    'description' => esc_html__( 'The label for custom recipe attribute. E.g. Colors', 'trg_el' ),
                ],

                [
                    'name' => 'value',
                    'label' => esc_html__( 'Attribute Value', 'trg_el' ),
                    'type' => Controls_Manager::TEXT,
                    'dynamic' => [
                        'active' => true,
                    ],
                    'default' => esc_html__( 'Attibute Values', 'trg_el' ),
                    'description' => esc_html__( 'Attribute values, separated by comma. E.g. Green, Yellow, Brown', 'trg_el' ),
                ]
            ],
            'description' => esc_html__( 'Add custom recipe attributes, used as tags or categories.', 'trg_el' )
        ]
    );

    $this->end_controls_section();

    $this->start_controls_section(
        'section_ingredients',
        [
            'label' => __('Ingredients', 'trg_el')
        ]
    );

    $this->add_control(
        'ing_heading',
        [
            'label' => esc_html__( 'Ingredients Title', 'trg_el' ),
            'type' => Controls_Manager::TEXT,
            'dynamic' => [
                'active' => true,
            ],
            'default' => ''
        ]
    );

    $this->add_control(
        'ingredients_heading',
        [
            'label' => esc_html__( 'Add ingredients', 'trg_el' ),
            'type' => Controls_Manager::HEADING,
            'separator' => 'before',
        ]
    );

    $this->add_control(
        'ingredients',
        [
            'type' => Controls_Manager::REPEATER,
            'default' => [
                [
                    'title' => __( 'For the burger', 'trg_el' ),
                    'list' => "200g wheat floor\r\n1 tbsp vegetable oil\r\n2 tsp sugar\r\n3 cups water"
                ],

                [
                    'title' => __( 'For the curry', 'trg_el' ),
                    'list' => "1 tbsp red chilli powder\r\n1 tea spoon vegetable oil\r\n2 tbsp coconut powder\r\n1 cup water"
                ]
            ],

            'fields' => [
                [
                    'name' => 'title',
                    'label' => esc_html__( 'Ingredients group title', 'trg_el' ),
                    'type' => Controls_Manager::TEXT,
                    'dynamic' => [
                        'active' => true,
                    ],
                    'description' => esc_html__( 'Provide optional group title if you want to separate ingredients in groups.', 'trg_el' ),
                ],

                [
                    'name' => 'list',
                    'label' => esc_html__( 'Ingredients list', 'trg_el' ),
                    'type' => Controls_Manager::WYSIWYG,
                    'dynamic' => [
                        'active' => true,
                    ],
                    'default' => "Ingredient name 1\r\nIngredient name 2",
                    'description' => esc_html__( 'Enter ingredient items, each on new line using Shift + Enter. (Use line break, not paragraph break).', 'trg_el' ),
                ]
            ],
        ]
    );

    $this->add_control(
        'use_ing_alt',
        [
            'label' => esc_html__( 'Use custom ingredients field', 'trg_el' ),
            'type' => Controls_Manager::SWITCHER,
            'default' => '',
            'label_on' => __( 'On', 'trg_el' ),
            'label_off' => __( 'Off', 'trg_el' ),
            'return_value' => 'true',
            'description' => esc_html__( 'Enable this if you wish to provide ingredients text manually instead of a repeater control.', 'trg_el' ),
        ]
    );

    $this->add_control(
        'ing_alt',
        [
            'label' => esc_html__( 'Ingredients (Alternate)', 'trg_el' ),
            'type' => Controls_Manager::TEXTAREA,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'description' => esc_html__( 'Use this field if you wish to pull ingredients content dynamically from other sources. E.g. from a custom field. Separate each list item with a pipe symbol \'|\'.', 'trg_el' ),
            'condition' => [ 'use_ing_alt' => ['true'] ]
        ]
    );

    $this->end_controls_section();

    $this->start_controls_section(
        'section_method',
        [
            'label' => __('Recipe Method', 'trg_el')
        ]
    );

    $this->add_control(
        'method_heading',
        [
            'label' => esc_html__( 'Method Steps Title', 'trg_el' ),
            'type' => Controls_Manager::TEXT,
            'dynamic' => [
                'active' => true,
            ],
            'default' => ''
        ]
    );

    $this->add_control(
        'enable_numbering',
        [
            'label' => esc_html__( 'Auto numbering', 'trg_el' ),
            'type' => Controls_Manager::SWITCHER,
            'default' => 'true',
            'label_on' => __( 'On', 'trg_el' ),
            'label_off' => __( 'Off', 'trg_el' ),
            'return_value' => 'true',
            'description' => esc_html__( 'Enable auto numbering on method steps', 'trg_el' ),
        ]
    );

    $this->add_control(
        'reset_count',
        [
            'label' => esc_html__( 'Reset numbering', 'trg_el' ),
            'type' => Controls_Manager::SWITCHER,
            'default' => '',
            'label_on' => __( 'On', 'trg_el' ),
            'label_off' => __( 'Off', 'trg_el' ),
            'return_value' => 'true',
            'description' => esc_html__( 'Reset numbering for each method group.', 'trg_el' ),
        ]
    );

    $this->add_control(
        'methods',
        [
            'type' => Controls_Manager::REPEATER,
            'default' => [
                [
                    'method_title' => __( 'Preparing the spices', 'trg_el' ),
                    'method_content' => "<p>Take 8-10 red chillies and grind them in fine powder.</p>"
                ],

                [
                    'method_title' => '',
                    'method_content' => "<p>Mix chilli powder with 1 tsp vegetable oil and add salt to it.</p>"
                ]
            ],

            'fields' => [
                [
                    'name' => 'method_title',
                    'label' => esc_html__( 'Method step title', 'trg_el' ),
                    'type' => Controls_Manager::TEXT,
                    'dynamic' => [
                        'active' => true,
                    ],
                    'description' => esc_html__( 'Provide optional title for the method step.', 'trg_el' ),
                ],

                [
                    'name' => 'method_content',
                    'label' => esc_html__( 'Method instruction', 'trg_el' ),
                    'type' => Controls_Manager::WYSIWYG,
                    'dynamic' => [
                        'active' => true,
                    ],
                    'default' => '',
                    'description' => esc_html__( 'Provide detailed method instruction. Use one step per field.', 'trg_el' ),
                ]
            ]
        ]
    );

    $this->add_control(
        'use_method_alt',
        [
            'label' => esc_html__( 'Use custom method field', 'trg_el' ),
            'type' => Controls_Manager::SWITCHER,
            'default' => '',
            'label_on' => __( 'On', 'trg_el' ),
            'label_off' => __( 'Off', 'trg_el' ),
            'return_value' => 'true',
            'description' => esc_html__( 'Enable this if you wish to provide method text manually instead of a repeater control.', 'trg_el' ),
        ]
    );

    $this->add_control(
        'methods_alt',
        [
            'label' => esc_html__( 'Method (Alternate)', 'trg_el' ),
            'type' => Controls_Manager::TEXTAREA,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'description' => esc_html__( 'Use this field if you wish to pull method content dynamically from other sources. E.g. from a custom field. Separate each method with a pipe symbol \'|\'.', 'trg_el' ),
            'condition' => [ 'use_method_alt' => ['true'] ]
        ]
    );

    $this->end_controls_section();

    $this->start_controls_section(
        'section_nutrition',
        [
            'label' => __('Nutrition Facts', 'trg_el')
        ]
    );

    $this->add_control(
        'show_nutrition',
        [
            'type' => Controls_Manager::SWITCHER,
            'label' => esc_html__( 'Nutrition Facts', 'trg_el' ),
            'default' => 'true',
            'label_on' => __( 'On', 'trg_el' ),
            'label_off' => __( 'Off', 'trg_el' ),
            'return_value' => 'true',
            'description' => esc_html__( 'Show or hide Nutrition facts table.', 'trg_el' ),
        ]
    );

    $this->add_control(
        'show_nutrition_only',
        [
            'type' => Controls_Manager::SWITCHER,
            'label' => esc_html__( 'Show only Nutrition Facts', 'trg_el' ),
            'default' => '',
            'label_on' => __( 'On', 'trg_el' ),
            'label_off' => __( 'Off', 'trg_el' ),
            'return_value' => 'true',
            'description' => esc_html__( 'Use this option to show only the Nutrition Facts table.', 'trg_el' ),
        ]
    );

    $this->add_control(
        'display_style',
        [
            'label' => esc_html__( 'Nutrition table style', 'trg_el' ),
            'type' => Controls_Manager::SELECT,
            'options' => [
            'std' => __( 'Standard', 'trg_el' ),
            'classic' => __( 'Classic', 'trg_el' ),
            'classic tabular' => __( 'Classic Tabular', 'trg_el' ),
            'flat' => __( 'Flat', 'trg_el' ),
            'flat striped' => __( 'Flat Striped', 'trg_el' ),
            'lined' => __( 'Lined', 'trg_el' ),
        ],
        'default' => 'classic',
        'description' => esc_html__( 'Select a style for Nutrition table.', 'trg_el' ),
        ]
    );

    $this->add_control(
        'show_dv',
        [
            'type' => Controls_Manager::SWITCHER,
            'label' => esc_html__( 'Show standard Daily Values', 'trg_el' ),
            'default' => '',
            'label_on' => __( 'On', 'trg_el' ),
            'label_off' => __( 'Off', 'trg_el' ),
            'return_value' => 'true',
            'description' => esc_html__( 'Enabling this option will show standard Daily Values in the chart.', 'trg_el' ),
        ]
    );

    $this->add_control(
        'extra_notes',
        [
            'label' => esc_html__( 'Nutrition label extra notes', 'trg_el' ),
            'type' => Controls_Manager::TEXTAREA,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '* The % Daily Value (DV) tells you how much a nutrient in a serving of food contributes to a daily diet. 2,000 calories a day is used for general nutrition advice.',
            'description' => esc_html__( 'Provide extra notes for the Nutrition table. This will be displayed at the end of table.', 'trg_el' ),
        ]
    );

    $this->add_control(
        'serving_per_cont',
        [
            'label' => esc_html__( 'Serving Per Container', 'trg_el' ),
            'type' => Controls_Manager::TEXT,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'Provide serving per container. E.g. 30', 'trg_el' )
        ]
    );    

    $this->add_control(
        'total_fat',
        [
            'label' => esc_html__( 'Total Fat', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Total Fat (g), without unit. Standard daily value is 72 g', 'trg_el' )
        ]
    );

    $this->add_control(
        'saturated_fat',
        [
            'label' => esc_html__( 'Saturated Fat', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Saturated Fat (g), without unit. Standard daily value is 20 g', 'trg_el' )
        ]
    );

    $this->add_control(
        'trans_fat',
        [
            'label' => esc_html__( 'Trans Fat', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Trans Fat (g), without unit. ', 'trg_el' )
        ]
    );

    $this->add_control(
        'polyunsat_fat',
        [
            'label' => esc_html__( 'Polyunsaturated Fat', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Polyunsaturated Fat (g), without unit. ', 'trg_el' )
        ]
    );

    $this->add_control(
        'monounsat_fat',
        [
            'label' => esc_html__( 'Monounsaturated Fat', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Monounsaturated Fat (g), without unit. ', 'trg_el' )
        ]
    );

    $this->add_control(
        'cholesterol',
        [
            'label' => esc_html__( 'Cholesterol', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Cholesterol (mg), without unit. Standard daily value is 300 mg', 'trg_el' )
        ]
    );

    $this->add_control(
        'sodium',
        [
            'label' => esc_html__( 'Sodium', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Sodium (mg), without unit. Standard daily value is 2300 mg', 'trg_el' )
        ]
    );

    $this->add_control(
        'carbohydrate',
        [
            'label' => esc_html__( 'Total Carbohydrate', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Total Carbohydrate (g), without unit. Standard daily value is 275 g', 'trg_el' )
        ]
    );

    $this->add_control(
        'fiber',
        [
            'label' => esc_html__( 'Dietary Fiber', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Dietary Fiber (g), without unit. Standard daily value is 28 g', 'trg_el' )
        ]
    );

    $this->add_control(
        'sugar',
        [
            'label' => esc_html__( 'Total Sugars', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Total Sugars (g), without unit. ', 'trg_el' )
        ]
    );

    $this->add_control(
        'added_sugar',
        [
            'label' => esc_html__( 'Added Sugars', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Added Sugars (g), without unit. Standard daily value is 50 g', 'trg_el' )
        ]
    );

    $this->add_control(
        'sugar_alcohal',
        [
            'label' => esc_html__( 'Sugar Alcohal', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Sugar Alcohal (g), without unit. ', 'trg_el' )
        ]
    );

    $this->add_control(
        'protein',
        [
            'label' => esc_html__( 'Protein', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Protein (g), without unit. Standard daily value is 50 g', 'trg_el' )
        ]
    );

    $this->add_control(
        'vitamin_d',
        [
            'label' => esc_html__( 'Vitamin D (Cholecalciferol)', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Vitamin D (Cholecalciferol) (IU), without unit. Standard daily value is 800 IU (International Units) or 20 mcg', 'trg_el' )
        ]
    );

    $this->add_control(
        'calcium',
        [
            'label' => esc_html__( 'Calcium', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Calcium (mg), without unit. Standard daily value is 1300 mg', 'trg_el' )
        ]
    );

    $this->add_control(
        'iron',
        [
            'label' => esc_html__( 'Iron', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Iron (mg), without unit. Standard daily value is 18 mg', 'trg_el' )
        ]
    );

    $this->add_control(
        'potassium',
        [
            'label' => esc_html__( 'Potassium', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Potassium (mg), without unit. Standard daily value is 4700 mg', 'trg_el' )
        ]
    );

    $this->add_control(
        'vitamin_a',
        [
            'label' => esc_html__( 'Vitamin A', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Vitamin A (International Units), without unit. Standard daily value is 900 mcg', 'trg_el' )
        ]
    );

    $this->add_control(
        'vitamin_c',
        [
            'label' => esc_html__( 'Vitamin C (Ascorbic Acid)', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Vitamin C (Ascorbic Acid) (mg), without unit. Standard daily value is 90 mg', 'trg_el' )
        ]
    );

    $this->add_control(
        'vitamin_e',
        [
            'label' => esc_html__( 'Vitamin E (Tocopherol)', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Vitamin E (Tocopherol) (IU), without unit. Standard daily value is 33 IU or 15 mg', 'trg_el' )
        ]
    );

    $this->add_control(
        'vitamin_k',
        [
            'label' => esc_html__( 'Vitamin K', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Vitamin K (mcg), without unit. Standard daily value is 120 mcg', 'trg_el' )
        ]
    );

    $this->add_control(
        'vitamin_b1',
        [
            'label' => esc_html__( 'Vitamin B1 (Thiamin)', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Vitamin B1 (Thiamin) (mg), without unit. Standard daily value is 1.2 mg', 'trg_el' )
        ]
    );

    $this->add_control(
        'vitamin_b2',
        [
            'label' => esc_html__( 'Vitamin B2 (Riboflavin)', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Vitamin B2 (Riboflavin) (mg), without unit. Standard daily value is 1.3 mg', 'trg_el' )
        ]
    );

    $this->add_control(
        'vitamin_b3',
        [
            'label' => esc_html__( 'Vitamin B3 (Niacin)', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Vitamin B3 (Niacin) (mg), without unit. Standard daily value is 16 mg', 'trg_el' )
        ]
    );

    $this->add_control(
        'vitamin_b6',
        [
            'label' => esc_html__( 'Vitamin B6 (Pyridoxine)', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Vitamin B6 (Pyridoxine) (mg), without unit. Standard daily value is 1.7 mg', 'trg_el' )
        ]
    );

    $this->add_control(
        'folate',
        [
            'label' => esc_html__( 'Folate', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Folate (mcg), without unit. Standard daily value is 400 mcg', 'trg_el' )
        ]
    );

    $this->add_control(
        'vitamin_b12',
        [
            'label' => esc_html__( 'Vitamin B12 (Cobalamine)', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Vitamin B12 (Cobalamine) (mcg), without unit. Standard daily value is 2.4 mcg', 'trg_el' )
        ]
    );

    $this->add_control(
        'biotin',
        [
            'label' => esc_html__( 'Biotin', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Biotin (mcg), without unit. Standard daily value is 30 mcg', 'trg_el' )
        ]
    );

    $this->add_control(
        'choline',
        [
            'label' => esc_html__( 'Choline', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Choline (mg), without unit. Standard daily value is 550 mg', 'trg_el' )
        ]
    );

    $this->add_control(
        'vitamin_b5',
        [
            'label' => esc_html__( 'Vitamin B5 (Pantothenic acid)', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Vitamin B5 (Pantothenic acid) (mg), without unit. Standard daily value is 5 mg', 'trg_el' )
        ]
    );

    $this->add_control(
        'phosphorus',
        [
            'label' => esc_html__( 'Phosphorus', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Phosphorus (mg), without unit. Standard daily value is 1250 mg', 'trg_el' )
        ]
    );

    $this->add_control(
        'iodine',
        [
            'label' => esc_html__( 'Iodine', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Iodine (mcg), without unit. Standard daily value is 150 mcg', 'trg_el' )
        ]
    );

    $this->add_control(
        'magnesium',
        [
            'label' => esc_html__( 'Magnesium', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Magnesium (mg), without unit. Standard daily value is 420 mg', 'trg_el' )
        ]
    );

    $this->add_control(
        'zinc',
        [
            'label' => esc_html__( 'Zinc', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Zinc (mg), without unit. Standard daily value is 11 mg', 'trg_el' )
        ]
    );

    $this->add_control(
        'selenium',
        [
            'label' => esc_html__( 'Selenium', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Selenium (mcg), without unit. Standard daily value is 55 mcg', 'trg_el' )
        ]
    );

    $this->add_control(
        'copper',
        [
            'label' => esc_html__( 'Copper', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Copper (mg), without unit. Standard daily value is 0.9 mg', 'trg_el' )
        ]
    );

    $this->add_control(
        'manganese',
        [
            'label' => esc_html__( 'Manganese', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Manganese (mg), without unit. Standard daily value is 2.3 mg', 'trg_el' )
        ]
    );

    $this->add_control(
        'chromium',
        [
            'label' => esc_html__( 'Chromium', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Chromium (mcg), without unit. Standard daily value is 35 mcg', 'trg_el' )
        ]
    );

    $this->add_control(
        'molybdenum',
        [
            'label' => esc_html__( 'Molybdenum', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Molybdenum (mcg), without unit. Standard daily value is 45 mcg', 'trg_el' )
        ]
    );

    $this->add_control(
        'chloride',
        [
            'label' => esc_html__( 'Chloride', 'trg_el' ),
            'type' => Controls_Manager::NUMBER,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'min' => '0',
            'step' => '1',
            'description' => esc_html__( 'The amount of Chloride (mg), without unit. Standard daily value is 2300 mg', 'trg_el' )
        ]
    );

    $this->add_control(
        'cust_nutrients_heading',
        [
            'label' => esc_html__( 'Add custom nutrients', 'trg_el' ),
            'type' => Controls_Manager::HEADING,
            'separator' => 'before',
        ]
    );

    $this->add_control(
        'custom_nutrients',
            [
                'type' => Controls_Manager::REPEATER,
                'default' => [],
                'fields' => [
                    [
                        'name' => 'name',
                        'label' => esc_html__( 'Nutrient name', 'trg_el' ),
                        'type' => Controls_Manager::TEXT,
                        'dynamic' => [
                            'active' => true,
                        ],
                        'description' => esc_html__( 'Provide a nutrient name. E.g. Power Protein', 'trg_el' )
                    ],

                    [
                        'name' => 'unit',
                        'label' => esc_html__( 'Nutrient Quantity Unit', 'trg_el' ),
                        'type' => Controls_Manager::TEXT,
                        'dynamic' => [
                            'active' => true,
                        ],
                        'description' => esc_html__( 'Provide a nutrient unit. E.g. mg', 'trg_el' )
                    ],

                    [
                        'name' => 'amt',
                        'label' => esc_html__( 'Amount per serving', 'trg_el' ),
                        'type' => Controls_Manager::TEXT,
                        'dynamic' => [
                            'active' => true,
                        ],
                        'description' => esc_html__( 'Provide amount per serving (without unit). E.g. 20', 'trg_el' )
                    ],

                    [
                        'name' => 'sv',
                        'label' => esc_html__( 'Standard daily value', 'trg_el' ),
                        'type' => Controls_Manager::TEXT,
                        'dynamic' => [
                            'active' => true,
                        ],
                        'description' => esc_html__( 'Provide standard daily value of Nutrient (without unit). E.g. 200', 'trg_el' )
                    ],

                    [
                        'name' => 'level',
                        'label' => esc_html__( 'Nutrient indent level', 'trg_el' ),
                        'type' => Controls_Manager::SELECT,
                        'options' => [
                            '0' => __( 'Level 0', 'trg_el' ),
                            '1' => __( 'Level 1', 'trg_el' ),
                            '2' => __( 'Level 2', 'trg_el' ),
                            '3' => __( 'Level 3', 'trg_el' )
                        ],
                    'default' => '',
                    'description' => esc_html__( 'Select indent level for Nutrient entry in table.', 'trg_el' ),
                    ],

                    [
                        'name' => 'text_style',
                        'label' => esc_html__( 'Nutrient text style', 'trg_el' ),
                        'type' => Controls_Manager::SELECT,
                        'options' => [
                        'normal' => __( 'Normal', 'trg_el' ),
                        'bold' => __( 'Bold', 'trg_el' ),
                    ],
                    'default' => '',
                    'description' => esc_html__( 'Select text style for the nutrient entry.', 'trg_el' )
                ]
            ]
        ]
    );

    $this->end_controls_section();

    $this->start_controls_section(
        'section_misc',
        [
            'label' => __('Miscellaneous', 'trg_el')
        ]
    );

    $this->add_control(
        'other_notes',
        [
            'label' => esc_html__( 'Other notes', 'trg_el' ),
            'type' => Controls_Manager::WYSIWYG,
            'dynamic' => [
                'active' => true,
            ],
            'default' => 'This is an extra note from author. This can be any tip, suggestion or fact related to the recipe.',
            'description' => esc_html__( 'Provide extra notes to be shown at the end of recipe', 'trg_el' )
        ]
    );

    if ( current_user_can( 'administrator' ) || current_user_can( 'editor' ) ) {
        $this->add_control(
            'ad_spot_1',
            [
                'label' => esc_html__( 'Ad Spot 1', 'trg_el' ),
                'type' => Controls_Manager::CODE,
                'dynamic' => [
                    'active' => true,
                ],
                'default' => '',
                'description' => esc_html__( 'Advertisement spot before ingredients section. You can use custom HTML or ad script in this field.', 'trg_el' ),
                'language' => 'html'
            ]
        );

        $this->add_control(
            'ad_spot_2',
            [
                'label' => esc_html__( 'Ad Spot 2', 'trg_el' ),
                'type' => Controls_Manager::CODE,
                'dynamic' => [
                    'active' => true,
                ],
                'default' => '',
                'description' => esc_html__( 'Advertisement spot before method steps section. You can use custom HTML or ad script in this field.', 'trg_el' ),
                'language' => 'html'
            ]
        );

        $this->add_control(
            'ad_spot_3',
            [
                'label' => esc_html__( 'Ad Spot 3', 'trg_el' ),
                'type' => Controls_Manager::CODE,
                'dynamic' => [
                    'active' => true,
                ],
                'default' => '',
                'description' => esc_html__( 'Advertisement spot after nutrition facts section. You can use custom HTML or ad script in this field.', 'trg_el' ),
                'language' => 'html'
            ]
        );
    }

    $this->add_control(
        'json_ld',
        [
            'label' => esc_html__( 'JSON LD Schema', 'trg_el' ),
            'type' => Controls_Manager::SWITCHER,
            'default' => '',
            'label_on' => __( 'On', 'trg_el' ),
            'label_off' => __( 'Off', 'trg_el' ),
            'return_value' => 'true',
            'description' => esc_html__( 'Enabling this option will add json ld schema data in recipe container.', 'trg_el' )
        ]
    );

    $this->add_control(
        'website_schema',
        [
            'label' => esc_html__( 'Website Schema', 'trg_el' ),
            'type' => Controls_Manager::SWITCHER,
            'default' => '',
            'label_on' => __( 'On', 'trg_el' ),
            'label_off' => __( 'Off', 'trg_el' ),
            'return_value' => 'true',
            'description' => esc_html__( 'Add website schema. This should be off if your theme already includes website schema, or you are using an SEO plugin like Yoast for website schema.', 'trg_el' )
        ]
    );

    $this->add_control(
        'rating_schema_heading',
        [
            'label' => esc_html__( 'Rating Schema', 'trg_el' ),
            'type' => Controls_Manager::HEADING,
            'separator' => 'before'
        ]
    );

    $this->add_control(
        'rating_src',
        [
            'label' => esc_html__( 'Include Rating Schema from', 'trg_el' ),
            'type' => Controls_Manager::SELECT,
            'options' => [
                'mts' => __( 'WP Review Plugin', 'trg_el' ),
                'rmp' => __( 'Rate My Post Plugin', 'trg_el' )
            ],
            'default' => 'mts',
            'description' => __( 'Rating schema value can be included from visitor rating of <a href="https://wordpress.org/plugins/wp-review/" target="_blank">WP Review</a> or <a href="https://wordpress.org/plugins/rate-my-post/" target="_blank">Rate my Post</a> plugin. Choose which one you are using.', 'trg_el' )
        ]
    );

    $this->add_control(
        'video_schema_heading',
        [
            'label' => esc_html__( 'Video Schema', 'trg_el' ),
            'type' => Controls_Manager::HEADING,
            'separator' => 'before'
        ]
    );

    $this->add_control(
        'vid_name',
        [
            'label' => esc_html__( 'Video Name', 'trg_el' ),
            'type' => Controls_Manager::TEXT,
            'dynamic' => [
                'active' => true,
            ],
            'default' => get_the_title()
        ]
    );

    $this->add_control(
        'vid_url',
        [
            'label' => esc_html__( 'Video URL', 'trg_el' ),
            'type' => Controls_Manager::TEXT,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'description' => esc_html__( 'Provide a URL of recipe video. E.g. https://www.youtube.com/watch?v=RTleJqGUJKE', 'trg_el' )
        ]
    );

    $this->add_control(
        'vid_thumb_url',
        [
            'label' => esc_html__( 'Video Thumbnail', 'trg_el' ),
            'type' => Controls_Manager::MEDIA,
            'dynamic' => [
                'active' => true,
            ],
            'label_block' => true,
            'default' => ['url' => ''],
            'description' => esc_html__( 'Add a video thumbnail image.', 'trg_el' )
        ]
    );

    $this->add_control(
        'vid_description',
        [
            'label' => esc_html__( 'Video Description', 'trg_el' ),
            'type' => Controls_Manager::TEXTAREA,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'description' => esc_html__( 'Provide video description to be used in schema.', 'trg_el' ),
        ]
    );

    $this->add_control(
        'vid_date',
        [
            'label' => esc_html__( 'Video upload date', 'trg_el' ),
            'type' => Controls_Manager::TEXT,
            'dynamic' => [
                'active' => true,
            ],
            'default' => get_the_date( 'c' ),
            'description' => esc_html__( 'Provide date of uploading the video.', 'trg_el' )
        ]
    );

    $this->add_control(
        'vid_duration',
        [
            'label' => esc_html__( 'Video duration', 'trg_el' ),
            'type' => Controls_Manager::TEXT,
            'dynamic' => [
                'active' => true,
            ],
            'default' => '',
            'description' => esc_html__( 'Provide video duration in ISO 8601 format. E.g. PT8M33S (for 8min 33 sec duration)', 'trg_el' )
        ]
    );

    $this->end_controls_section();

    $this->start_controls_section(
        'section_title',
        [
            'label' => __('Recipe Title', 'trg_el'),
            'tab' => Controls_Manager::TAB_STYLE,
            'show_label' => false,
        ]
    );

    $this->add_control(
        'recipe_title_tag',
        [
            'label' => esc_html__( 'Recipe Title Tag', 'trg_el' ),
            'type' => Controls_Manager::SELECT,
            'options' => [
                'h1' => __( 'h1', 'trg_el' ),
                'h2' => __( 'h2', 'trg_el' ),
                'h3' => __( 'h3', 'trg_el' ),
                'h4' => __( 'h4', 'trg_el' ),
                'h5' => __( 'h5', 'trg_el' ),
                'p' => __( 'p', 'trg_el' ),
                'span' => __( 'span', 'trg_el' ),
            ],
            'default' => 'h2'
        ]
    );

    $this->add_control(
        'title_color',
        [
            'label' => esc_html__( 'Title color', 'trg_el' ),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                    '{{WRAPPER}} .recipe-title' => 'color: {{VALUE}};',
                ]
        ]
    );

    $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
            'label' => esc_html__( 'Title Typography', 'trg_el' ),
            'name' => 'title_typography',
            'selector' => '{{WRAPPER}} .recipe-title',
        ]
    );

    $this->add_responsive_control(
        'title_margin',
        [
            'label' => __( 'Title Margin', 'trg_el' ),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px','em', 'rem' ],
            'selectors' => [
                '{{WRAPPER}} .recipe-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ]
        ]
    );

    $this->end_controls_section();

    $this->start_controls_section(
        'section_summary',
        [
            'label' => __('Recipe Summary', 'trg_el'),
            'tab' => Controls_Manager::TAB_STYLE,
            'show_label' => false,
        ]
    );

    $this->add_control(
        'summary_color',
        [
            'label' => esc_html__( 'Text color', 'trg_el' ),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                    '{{WRAPPER}} .recipe-summary' => 'color: {{VALUE}};',
                ]
        ]
    );

    $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
            'label' => esc_html__( 'Summary Text Typography', 'trg_el' ),
            'name' => 'summary_typography',
            'selector' => '{{WRAPPER}} .recipe-summary',
        ]
    );

    $this->add_responsive_control(
        'summary_margin',
        [
            'label' => __( 'Summary Margin', 'trg_el' ),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px','em', 'rem' ],
            'selectors' => [
                '{{WRAPPER}} .recipe-summary' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ]
        ]
    );

    $this->end_controls_section();

    $this->start_controls_section(
        'section_info_board',
        [
            'label' => __('Info Board', 'trg_el'),
            'tab' => Controls_Manager::TAB_STYLE,
            'show_label' => false,
        ]
    );

    $this->add_control(
        'ib_label_color',
        [
            'label' => esc_html__( 'Labels color', 'trg_el' ),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                    '{{WRAPPER}} .info-board>li .ib-label' => 'color: {{VALUE}};',
                ]
        ]
    );

    $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
            'label' => esc_html__( 'Labels Typography', 'trg_el' ),
            'name' => 'ib_label_typography',
            'selector' => '{{WRAPPER}} .info-board>li .ib-label',
        ]
    );

    $this->add_control(
        'highlights',
        [
            'label' => esc_html__( 'Highlights color', 'trg_el' ),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                    '{{WRAPPER}} .info-board>li .ib-value' => 'color: {{VALUE}};',
                ]
        ]
    );

    $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
            'label' => esc_html__( 'Highlights Typography', 'trg_el' ),
            'name' => 'ib_hl_typography',
            'selector' => '{{WRAPPER}} .info-board>li .ib-value',
        ]
    );

    $this->add_responsive_control(
        'ib_margin',
        [
            'label' => __( 'Info Board margin', 'trg_el' ),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px','em', 'rem' ],
            'selectors' => [
                '{{WRAPPER}} .info-board' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ]
        ]
    );

    $this->add_responsive_control(
        'ib_padding',
        [
            'label' => __( 'Info Board padding', 'trg_el' ),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px','em', 'rem' ],
            'selectors' => [
                '{{WRAPPER}} .info-board' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ]
        ]
    );

    $this->add_group_control(
        Group_Control_Background::get_type(),
        [
            'label' => __( 'Info Board background', 'trg_el' ),
            'name' => 'ib_bg_color',
            'selector' => '{{WRAPPER}} .info-board',
        ]
    );

    $this->add_group_control(
        Group_Control_Border::get_type(),
        [
            'name' => 'ib_border',
            'selector' => '{{WRAPPER}} .info-board'
        ]
    );

    $this->end_controls_section();

    $this->start_controls_section(
        'section_recipe_meta',
        [
            'label' => __('Recipe Meta', 'trg_el'),
            'tab' => Controls_Manager::TAB_STYLE,
            'show_label' => false,
        ]
    );

    $this->add_control(
        'rm_labels',
        [
            'label' => esc_html__( 'Meta Labels color', 'trg_el' ),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                    '{{WRAPPER}} .cuisine-meta .cm-label' => 'color: {{VALUE}};',
                ]
        ]
    );

    $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
            'label' => esc_html__( 'Meta Labels Typography', 'trg_el' ),
            'name' => 'rm_lbl_typography',
            'selector' => '{{WRAPPER}} .cuisine-meta .cm-label',
        ]
    );

    $this->add_control(
        'tags_bg',
        [
            'label' => esc_html__( 'Tag links background', 'trg_el' ),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                    '{{WRAPPER}} .cuisine-meta .cm-value:not(.link-enabled),{{WRAPPER}} .cuisine-meta .cm-value a' => 'background-color: {{VALUE}};',
                ]
        ]
    );

    $this->add_control(
        'tags_color',
        [
            'label' => esc_html__( 'Tag links color', 'trg_el' ),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                    '{{WRAPPER}} .cuisine-meta .cm-value:not(.link-enabled),{{WRAPPER}} .cuisine-meta .cm-value a' => 'color: {{VALUE}}; box-shadow: none;',
                ]
        ]
    );

    $this->add_control(
        'tags_bg_hover',
        [
            'label' => esc_html__( 'Tag links background hover color', 'trg_el' ),
            'type' => Controls_Manager::COLOR,
            'description' => esc_html__( 'Choose a hover background color for tag links', 'trg_el' ),
            'selectors' => [
                    '{{WRAPPER}} .cuisine-meta .cm-value a:hover,.cuisine-meta .cm-value a:active' => 'background-color: {{VALUE}};',
                ]
        ]
    );

    $this->add_control(
        'tags_color_hover',
        [
            'label' => esc_html__( 'Tag links hover color', 'trg_el' ),
            'type' => Controls_Manager::COLOR,
            'description' => esc_html__( 'Choose a hover color for tag links', 'trg_el' ),
            'selectors' => [
                    '{{WRAPPER}} .cuisine-meta .cm-value a:hover,.cuisine-meta .cm-value a:active' => 'color: {{VALUE}};',
                ]
        ]
    );

    $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
            'label' => esc_html__( 'Tag links Typography', 'trg_el' ),
            'name' => 'rm_tags_typography',
            'selector' => '{{WRAPPER}} .cuisine-meta .cm-value:not(.link-enabled),{{WRAPPER}} .cuisine-meta .cm-value a',
        ]
    );

    $this->add_responsive_control(
        'cm_margin',
        [
            'label' => __( 'Meta section margin', 'trg_el' ),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px','em', 'rem' ],
            'selectors' => [
                '{{WRAPPER}} .cuisine-meta' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ]
        ]
    );

    $this->add_responsive_control(
        'cm_padding',
        [
            'label' => __( 'Meta section padding', 'trg_el' ),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px','em', 'rem' ],
            'selectors' => [
                '{{WRAPPER}} .cuisine-meta' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ]
        ]
    );

    $this->add_group_control(
        Group_Control_Background::get_type(),
        [
            'label' => __( 'Meta section background', 'trg_el' ),
            'name' => 'cm_bg_color',
            'selector' => '{{WRAPPER}} .cuisine-meta',
        ]
    );

    $this->add_group_control(
        Group_Control_Border::get_type(),
        [
            'name' => 'cm_border',
            'selector' => '{{WRAPPER}} .cuisine-meta'
        ]
    );    

    $this->end_controls_section();

    $this->start_controls_section(
        'section_ing_heading',
        [
            'label' => __('Ingredients', 'trg_el'),
            'tab' => Controls_Manager::TAB_STYLE,
            'show_label' => false,
        ]
    );   

    $this->add_control(
        'ing_icon',
        [
            'label' => esc_html__( 'Ingredients Heading Icon', 'trg_el' ),
            'label_block' => true,
            'type' => Controls_Manager::ICONS,
            'default' => ['value' => 'eicon-basket-medium'],
            'description' => esc_html__( 'Choose an icon for ingredients heading.', 'trg_el' )
        ]
    );

    $this->add_control(
            'icon_gap',
            [
                'label' => esc_html__( 'Space between', 'trg_el' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range' => [
                    'em' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                        'step' => 1,
                    ]
                ],
                'default' => [
                    'unit' => 'em',
                    'size' => .75,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ing-title .trg-icon' => 'margin: 0 {{SIZE}}{{UNIT}} 0 0;',
                    '.rtl {{WRAPPER}} .ing-title .trg-icon' => 'margin: 0 0 0 {{SIZE}}{{UNIT}};'
                ],
                'description' => esc_html__( 'Select a gap between icon and heading.', 'trg_el' )
            ]
        );

    $this->add_control(
        'ing_icon_color',
        [
            'label' => esc_html__( 'Icon color', 'trg_el' ),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                    '{{WRAPPER}} .ing-title .trg-icon' => 'color: {{VALUE}};',
                ]
        ]
    );

    $this->add_control(
        'ing_h_color',
        [
            'label' => esc_html__( 'Heading color', 'trg_el' ),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                    '{{WRAPPER}} .ing-title' => 'color: {{VALUE}};',
                ]
        ]
    );

    $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
            'label' => esc_html__( 'Heading Typography', 'trg_el' ),
            'name' => 'ing_h_typography',
            'selector' => '{{WRAPPER}} .ing-title',
        ]
    );

    $this->add_responsive_control(
        'ing_h_margin',
        [
            'label' => __( 'Heading margin', 'trg_el' ),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px','em', 'rem' ],
            'selectors' => [
                '{{WRAPPER}} .ing-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ]
        ]
    );

    $this->add_control(
        'ing_sub_h_color',
        [
            'label' => esc_html__( 'Sub Heading color', 'trg_el' ),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                    '{{WRAPPER}} .list-subhead' => 'color: {{VALUE}};',
                ]
        ]
    );

    $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
            'label' => esc_html__( 'Sub Heading Typography', 'trg_el' ),
            'name' => 'ing_sub_h_typography',
            'selector' => '{{WRAPPER}} .list-subhead',
        ]
    );

    $this->add_responsive_control(
        'ing_sub_h_margin',
        [
            'label' => __( 'Subheading margin', 'trg_el' ),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px','em', 'rem' ],
            'selectors' => [
                '{{WRAPPER}} .list-subhead' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ]
        ]
    );

    $this->add_control(
        'ing_list_icon',
        [
            'label' => esc_html__( 'Ingredient List Icon', 'trg_el' ),
            'label_block' => true,
            'type' => Controls_Manager::ICONS,
            'default' => ['value' => 'eicon-check'],
            'description' => esc_html__( 'Choose an icon for ingredient list.', 'trg_el' )
        ]
    );

    $this->add_control(
            'list_icon_gap',
            [
                'label' => esc_html__( 'Space between', 'trg_el' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range' => [
                    'em' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                        'step' => 1,
                    ]
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 12,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ing-list .trg-icon' => 'margin: 0 {{SIZE}}{{UNIT}} 0 0;',
                    '.rtl {{WRAPPER}} .ing-list .trg-icon' => 'margin: 0 0 0 {{SIZE}}{{UNIT}};'
                ],
                'description' => esc_html__( 'Select a gap between icon and list text.', 'trg_el' )
            ]
        );

    $this->add_control(
        'list_icon_color',
        [
            'label' => esc_html__( 'List Icon color', 'trg_el' ),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                    '{{WRAPPER}} .ing-list .trg-icon' => 'color: {{VALUE}};',
                ]
        ]
    );

    $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
            'label' => esc_html__( 'Ingredient List Typography', 'trg_el' ),
            'name' => 'ing_list_typography',
            'selector' => '{{WRAPPER}} .ing-list > li',
        ]
    );

    $this->add_control(
            'ing_list_gap',
            [
                'label' => esc_html__( 'List items gap', 'trg_el' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range' => [
                    'em' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                        'step' => 1,
                    ]
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 0,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ing-list > li' => 'padding: calc({{SIZE}}{{UNIT}} / 2 ) 0 calc({{SIZE}}{{UNIT}} / 2 ) 0;'
                ],
                'description' => esc_html__( 'Select a gap between ingredient list items.', 'trg_el' )
            ]
    );

    $this->add_control(
        'ing_list_margin',
        [
            'label' => __( 'Ingredients list margin', 'trg_el' ),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px','em', 'rem' ],
            'selectors' => [
                '{{WRAPPER}} .ing-list' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ]
        ]
    );

    $this->add_responsive_control(
        'ing_margin',
        [
            'label' => __( 'Ingredients section margin', 'trg_el' ),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px','em', 'rem' ],
            'selectors' => [
                '{{WRAPPER}} .ingredients-section' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ]
        ]
    );

    $this->add_responsive_control(
        'ing_padding',
        [
            'label' => __( 'Ingredients section padding', 'trg_el' ),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px','em', 'rem' ],
            'selectors' => [
                '{{WRAPPER}} .ingredients-section' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ]
        ]
    );

    $this->add_group_control(
        Group_Control_Background::get_type(),
        [
            'label' => __( 'Ingredients section background', 'trg_el' ),
            'name' => 'ing_bg_color',
            'selector' => '{{WRAPPER}} .ingredients-section',
        ]
    );

    $this->add_group_control(
        Group_Control_Border::get_type(),
        [
            'name' => 'ing_border',
            'selector' => '{{WRAPPER}} .ingredients-section'
        ]
    );     

    $this->end_controls_section();

    $this->start_controls_section(
        'section_method_styles',
        [
            'label' => __('Method', 'trg_el'),
            'tab' => Controls_Manager::TAB_STYLE,
            'show_label' => false,
        ]
    );

    $this->add_control(
        'method_icon',
        [
            'label' => esc_html__( 'Method Heading Icon', 'trg_el' ),
            'label_block' => true,
            'type' => Controls_Manager::ICONS,
            'default' => ['value' => 'eicon-counter-circle'],
            'description' => esc_html__( 'Choose an icon for method heading.', 'trg_el' )
        ]
    );

    $this->add_control(
            'method_icon_gap',
            [
                'label' => esc_html__( 'Icon spacing', 'trg_el' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range' => [
                    'em' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                        'step' => 1,
                    ]
                ],
                'default' => [
                    'unit' => 'em',
                    'size' => .75,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ins-title .trg-icon' => 'margin: 0 {{SIZE}}{{UNIT}} 0 0;',
                    '.rtl {{WRAPPER}} .ins-title .trg-icon' => 'margin: 0 0 0 {{SIZE}}{{UNIT}};'
                ],
                'description' => esc_html__( 'Select a gap between icon and heading.', 'trg_el' )
            ]
        );

    $this->add_control(
        'method_icon_color',
        [
            'label' => esc_html__( 'Icon color', 'trg_el' ),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                    '{{WRAPPER}} .ins-title .trg-icon' => 'color: {{VALUE}};',
                ]
        ]
    );

    $this->add_control(
        'method_h_color',
        [
            'label' => esc_html__( 'Heading color', 'trg_el' ),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                    '{{WRAPPER}} .ins-title' => 'color: {{VALUE}};',
                ]
        ]
    );

    $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
            'label' => esc_html__( 'Heading Typography', 'trg_el' ),
            'name' => 'method_h_typography',
            'selector' => '{{WRAPPER}} .ins-title',
        ]
    );

    $this->add_control(
        'method_h_margin',
        [
            'label' => __( 'Heading margin', 'trg_el' ),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px','em', 'rem' ],
            'selectors' => [
                '{{WRAPPER}} .ins-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ]
        ]
    );

    $this->add_control(
        'method_sub_h_color',
        [
            'label' => esc_html__( 'Sub Heading color', 'trg_el' ),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                    '{{WRAPPER}} .inst-subhead' => 'color: {{VALUE}};',
                ]
        ]
    );

    $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
            'label' => esc_html__( 'Sub Heading Typography', 'trg_el' ),
            'name' => 'method_sub_h_typography',
            'selector' => '{{WRAPPER}} .inst-subhead',
        ]
    );

    $this->add_control(
        'method_sub_h_margin',
        [
            'label' => __( 'Subheading margin', 'trg_el' ),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px','em', 'rem' ],
            'selectors' => [
                '{{WRAPPER}} .inst-subhead' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ]
        ]
    );

    $this->add_control(
        'count_color',
        [
            'label' => esc_html__( 'Color for number count', 'trg_el' ),
            'type' => Controls_Manager::COLOR,
            'description' => esc_html__( 'Choose a color for number count in recipe method', 'trg_el' ),
            'selectors' => [ '{{WRAPPER}} .step-num' => 'color: {{VALUE}}']
        ]
    );

    $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
            'label' => esc_html__( 'Counter Typography', 'trg_el' ),
            'name' => 'count_typography',
            'selector' => '{{WRAPPER}} .step-num',
        ]
    );

    $this->add_control(
        'counter_width',
        [
            'label' => esc_html__( 'Counter Width', 'trg_el' ),
            'type' => Controls_Manager::SLIDER,
            'size_units' => [ 'px', '%' ],
            'range' => [
                '%' => [
                    'min' => 0,
                    'max' => 100,
                    'step' => 1,
                ],
                'px' => [
                    'min' => 0,
                    'max' => 1000,
                    'step' => 1,
                ]
            ],
            'default' => [
                'unit' => 'px',
                'size' => 48,
            ],
            'selectors' => [
                '{{WRAPPER}} .number-enabled .recipe-instruction .step-num' => 'width: {{SIZE}}{{UNIT}};',
                '{{WRAPPER}} .number-enabled .recipe-instruction .step-content' => 'width: calc(100% - {{SIZE}}{{UNIT}});',
            ],
            'description' => esc_html__( 'Select a width for number counter.', 'trg_el' )
        ]
    );

    $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
            'label' => esc_html__( 'Recipe Method Typography', 'trg_el' ),
            'name' => 'method_typography',
            'selector' => '{{WRAPPER}} .step-content',
        ]
    );

    $this->add_control(
        'method_step_margin',
        [
            'label' => __( 'Method Step margin', 'trg_el' ),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px','em', 'rem' ],
            'selectors' => [
                '{{WRAPPER}} .recipe-instruction' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ]
        ]
    );

    $this->add_control(
        'method_step_padding',
        [
            'label' => __( 'Method Step padding', 'trg_el' ),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px','em', 'rem' ],
            'selectors' => [
                '{{WRAPPER}} .recipe-instruction' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ]
        ]
    );

    $this->add_responsive_control(
        'method_margin',
        [
            'label' => __( 'Method section margin', 'trg_el' ),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px','em', 'rem' ],
            'selectors' => [
                '{{WRAPPER}} .method-section' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ]
        ]
    );

    $this->add_responsive_control(
        'method_padding',
        [
            'label' => __( 'Method section padding', 'trg_el' ),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px','em', 'rem' ],
            'selectors' => [
                '{{WRAPPER}} .method-section' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ]
        ]
    );

    $this->add_group_control(
        Group_Control_Background::get_type(),
        [
            'label' => __( 'Method section background', 'trg_el' ),
            'name' => 'method_bg_color',
            'selector' => '{{WRAPPER}} .method-section',
        ]
    );

    $this->add_group_control(
        Group_Control_Border::get_type(),
        [
            'name' => 'method_border',
            'selector' => '{{WRAPPER}} .method-section'
        ]
    );

    $this->end_controls_section();

    $this->start_controls_section(
        'section_other_notes',
        [
            'label' => __('Other Notes', 'trg_el'),
            'tab' => Controls_Manager::TAB_STYLE,
            'show_label' => false,
        ]
    );

    $this->add_group_control(
        Group_Control_Typography::get_type(),
        [
            'label' => esc_html__( 'Other notes typography', 'trg_el' ),
            'name' => 'notes_typography',
            'selector' => '{{WRAPPER}} .recipe-other-notes',
        ]
    );

    $this->add_control(
        'notes_color',
        [
            'label' => esc_html__( 'Text color', 'trg_el' ),
            'type' => Controls_Manager::COLOR,
            'description' => esc_html__( 'Choose a color for other notes text', 'trg_el' ),
            'selectors' => [ '{{WRAPPER}} .recipe-other-notes' => 'color: {{VALUE}}']
        ]
    );

    $this->add_responsive_control(
        'notes_margin',
        [
            'label' => __( 'Other notes margin', 'trg_el' ),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px','em', 'rem' ],
            'selectors' => [
                '{{WRAPPER}} .recipe-other-notes' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ]
        ]
    );

    $this->add_responsive_control(
        'notes_padding',
        [
            'label' => __( 'Other notes padding', 'trg_el' ),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px','em', 'rem' ],
            'selectors' => [
                '{{WRAPPER}} .recipe-other-notes' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ]
        ]
    );

    $this->add_group_control(
        Group_Control_Background::get_type(),
        [
            'label' => __( 'Other notes background', 'trg_el' ),
            'name' => 'notes_bg_color',
            'selector' => '{{WRAPPER}} .recipe-other-notes',
        ]
    );

    $this->add_group_control(
        Group_Control_Border::get_type(),
        [
            'name' => 'notes_border',
            'selector' => '{{WRAPPER}} .recipe-other-notes'
        ]
    );

    $this->end_controls_section();

    $this->start_controls_section(
        'section_nutrition_facts',
        [
            'label' => __('Nutrition Facts', 'trg_el'),
            'tab' => Controls_Manager::TAB_STYLE,
            'show_label' => false,
        ]
    );

    $this->add_responsive_control(
        'nf_margin',
        [
            'label' => __( 'Nutrition Facts margin', 'trg_el' ),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px','em', 'rem' ],
            'selectors' => [
                '{{WRAPPER}} .nutrition-section' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ]
        ]
    );

    $this->end_controls_section();

}

    // Recipe function
    function trg_recipe( $atts, $content = null ) {
        extract( shortcode_atts( array(
            'template'          => 'recipe',
            // Image specific
            'img_src'           => 'featured', //media_lib
            'img_lib'           => '',
            'imgsize'           => 'custom',
            'imgwidth'          => '',
            'imgheight'         => '',
            'imgcrop'           => '',
            'imgquality'        => '',
            'img_align'         => 'none',
            'json_ld'           => 'true', // Whether to include JSON LD microdata
            'website_schema'    => false,

            // Recipe name and summary
            'name_src'          => 'post_title', // custom
            'name_txt'          => '',
            'show_name'         => false,
            'summary'           => '',
            'show_summary'      => false,
            'author_src'        => 'post_author',
            'author_name'       => '',
            'author_url'        => '',
            'show_author'       => false,
            'show_date'         => false,

            // Recipe meta
            'prep_time'         => '5', // in minutes
            'cook_time'         => '10', // in minutes
            'perform_time'      => '',
            'total_time'        => '',
            'ready_in'          => '',
            'cooking_method'    => '',
            'recipe_category'   => '',
            'recipe_category_other' => '',
            'recipe_cuisine'    => '',
            'recipe_cuisine_other'  => '',
            'ingredients'       => '', // Textarea data each on new line
            'ing_heading'       => __( 'Ingredients', 'trg_el' ),
            'ing_alt'       => null,
            'use_ing_alt'    => false,
            'total_cost'        => '',
            'cost_per_serving'  => '',
            'method_heading'        => __( 'Method', 'trg_el' ),
            'enable_numbering'  => 'true',
            'reset_count'       => '',
            'other_notes'       => '',
            'custom_nutrients'  => '',
            'methods'           => null,
            'methods_alt'       => null,
            'use_method_alt'    => false,
            'show_tags'         => '',
            'recipe_keywords'  => '',
            'ing_icon'          => 'eicon-basket-medium',
            'ing_list_icon'     => 'eicon-check',
            'method_icon'       => 'eicon-counter-circle',

            // Nutrition facts
            'recipe_yield'      => '',
            'suitable_for_diet' => '',
            'suitable_for_diet_other'   => '',
            'show_nutrition'    => false,
            'show_nutrition_only' => false,
            'display_style'     => 'std',
            'serving_per_cont'  => '',
            'serving_size'      => '',
            'calories'          => '',
            'nutri_heading'     => __( 'Nutrition Facts', 'trg_el' ),
            'extra_notes'       => __( '*Percent Daily Values are based on a 2,000 calorie diet. Your daily values may be higher or lower depending on your calorie needs.', 'trg_el' ),
            'show_dv'           => false,
            'cust_meta'         => '',
            'cust_attr'         => '',
            // Nutrients
            'total_fat'         => '',
            'saturated_fat'     => '',
            'trans_fat'         => '',
            'polyunsat_fat'     => '',
            'monounsat_fat'     => '',
            'cholesterol'       => '',
            'sodium'            => '',
            'carbohydrate'      => '',
            'fiber'             => '',
            'sugar'             => '',
            'added_sugar'       => '',
            'sugar_alcohal'     => '',
            'protein'           => '',
            'vitamin_d'         => '',
            'calcium'           => '',
            'iron'              => '',
            'potassium'         => '',
            'vitamin_a'         => '',
            'vitamin_c'         => '',
            'vitamin_e'         => '',
            'vitamin_k'         => '',
            'vitamin_b1'        => '',
            'vitamin_b2'        => '',
            'vitamin_b3'        => '',
            'vitamin_b6'        => '',
            'folate'            => '',
            'vitamin_b12'       => '',
            'biotin'            => '',
            'vitamin_b5'        => '',
            'phosphorus'        => '',
            'iodine'            => '',
            'magnesium'         => '',
            'zinc'              => '',
            'selenium'          => '',
            'copper'            => '',
            'manganese'         => '',
            'chromium'          => '',
            'molybdenum'        => '',
            'chloride'          => '',

            // Misc
            'ad_spot_1'         => '',
            'ad_spot_2'         => '',
            'ad_spot_3'         => '',
            'recipe_title_tag'  => 'h2',

            // Colors
            'icon_color'        => '',
            'heading_color'     => '',
            'tags_bg'           => '',
            'tags_color'        => '',
            'tags_bg_hover'     => '',
            'tags_color_hover'  => '',
            'label_color'       => '',
            'highlights'        => '',
            'count_color'       => '',
            'tick_color'        => '',

            // Video Schema
            'vid_name'          => '',
            'vid_duration'      => '',
            'vid_date'          => get_the_date( 'c' ),
            'vid_description'   => '',
            'vid_url'           => '',
            'vid_thumb_url'     => '',
            'rating_src'        => 'mts'
        ), $atts ) );


        // Global settings
        $ad_spots = get_option( 'trg_adspots' );
        $social = get_option( 'trg_social' );
        $display = get_option( 'trg_display' );

        ob_start();

        $template_path = apply_filters( 'trg_template_path', '/trg-templates/' );

        if ( '' == $template ) {
            $template = 'recipe';
        }
        if ( locate_template( $template_path . $template . '.php' ) ) {
            require( get_stylesheet_directory() . $template_path . $template . '.php' );
        }
        else {
            require( dirname( __FILE__ ) . $template_path . $template . '.php' );
        }

        $out = ob_get_contents();

        ob_end_clean();

        return $out;

    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        echo $this->trg_recipe( $settings );
    }

    protected function content_template() {
    }
}
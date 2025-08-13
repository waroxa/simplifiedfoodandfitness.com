<?php

use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class dtk_Ajax_Load {
  private $post_id='';
  private $current_page=1;
  private $widget_id='';
  private $theme_id='';
  private $query=[];
  
  public function __construct($args=[]) {//["post_id"=>2,"current_page"=>2,"max_num_pages"=>5,"widget_id"=>"65054a0"]
    
    $this->init();

    if (!isset($args['post_id'])) {
      // Verify nonce
      // if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'escload_nonce')) {
      //   return;
      // }
  
      if(!isset($_POST['ecs_ajax_settings'])) return;
          else $args = json_decode( stripslashes( $_POST['ecs_ajax_settings'] ), true );
    }

    $this->post_id = $args['post_id'];
    $this->current_page = $args['current_page'] + 1;
    $this->widget_id = $args['widget_id'];
    $this->theme_id = isset($args['theme_id']) ? $args['theme_id'] : $args['post_id'];
    $this->query = json_decode( stripslashes( $_POST['query'] ), true );
    if ($this->current_page > $args['max_num_pages']) return;
    $this->init_ajax();

  }

  public function init() {
    add_action( 'wp_enqueue_scripts', [$this,'enqueue_scripts'] ,99);
    add_action( 'elementor/element/before_section_end', [$this,'post_pagination'],10,3);
    add_action( 'elementor/element/after_section_end', [$this,'button_pagination_style'],10,3);
  }
  
  public function init_ajax(){
    //add_action( 'wp_footer',[$this,'get_document_data'],99);// debug line comment it
    add_action( 'wp_ajax_ecsload', [$this,'get_document_data']); 
    add_action( 'wp_ajax_nopriv_ecsload', [$this,'get_document_data']);     
  }
  
  public function post_pagination($element, $section_id='', $args=''){

    if ( ( 'archive-posts' === $element->get_name() || 'posts' === $element->get_name() ) && 'section_pagination' === $section_id ) {
      
      $element->remove_control( 'pagination_type' );

      $element->add_control(
        'pagination_type',
        [
          'label' => __( 'Pagination', 'dethemekit-for-elementor' ),
          'type' => \Elementor\Controls_Manager::SELECT,
          'default' => '',
          'options' => [
            '' => __( 'None', 'dethemekit-for-elementor' ),
            'numbers' => __( 'Numbers', 'dethemekit-for-elementor' ),
            'loadmore' => __( 'Load More (Detheme Kit)', 'dethemekit-for-elementor' ),
            // 'lazyload' => __( 'Infinite Load (Custom Skin Pro)', 'dethemekit-for-elementor' ),
            'prev_next' => __( 'Previous/Next', 'dethemekit-for-elementor' ),
            'numbers_and_prev_next' => __( 'Numbers', 'dethemekit-for-elementor' ) . ' + ' . __( 'Previous/Next', 'dethemekit-for-elementor' ),
          ],
        ]
      );
      /* lazyload stuff*/  
      $element->add_control(
          'de_lazyload_title',
          [
            'label' => __( 'Infinite Load', 'dethemekit-for-elementor' ),
            'type' => \Elementor\Controls_Manager::HEADING,
            'separator' => 'before',
            'condition' => [
              'pagination_type' => 'lazyload',
            ],        
          ]
      );

      $element->add_control(
        'de_lazyload_animation',
        [
          'label' => __( 'Loading Animation', 'dethemekit-for-elementor' ),
          'type' => \Elementor\Controls_Manager::SELECT,
          'default' => 'default',
          'options' => dtk_Loading_Animation::get_lazy_load_animations_list(),
          'condition' => [
              'pagination_type' => 'lazyload',
          ],  
        ]
      );
      $element->add_control(
        'de_lazyload_color',
        [
          'label' => __( 'Animation Color', 'dethemekit-for-elementor' ),
          'type' => \Elementor\Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .ecs-lazyload .ecs-ll-brcolor' => 'border-color: {{VALUE}};',
            '{{WRAPPER}} .ecs-lazyload .ecs-ll-bgcolor' => 'background-color: {{VALUE}} !important;',
          ],
          'condition' => [
            'pagination_type' => 'lazyload',
          ],
        ]
      );      
      
      $element->add_control(
        'de_lazyload_spacing',
        [
          'label' => __( 'Animation Spacing', 'dethemekit-for-elementor' ),
          'type' => \Elementor\Controls_Manager::SLIDER,
          'range' => [
            'px' => [
              'max' => 250,
            ],
          ],
          'default' =>[
            'unit' => 'px',
            'size' => '20',
          ],
          'selectors' => [
            '{{WRAPPER}} .ecs-lazyload' => 'margin-top: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'pagination_type' => 'lazyload',
          ],
        ]
      );
      $element->add_control(
        'de_lazyload_size',
        [
          'label' => __( 'Animation Size', 'dethemekit-for-elementor' ),
          'type' => \Elementor\Controls_Manager::SLIDER,
          'range' => [
            'px' => [
              'max' => 50,
            ],
          ],
          'selectors' => [
            '{{WRAPPER}} .ecs-lazyload .ecs-lazy-load-animation' => 'font-size: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'pagination_type' => 'lazyload',
          ],
        ]
      );

      
      /* load more button stuff */
      
      $element->add_control(
          'de_loadmore_title',
          [
            'label' => __( 'Load More Button', 'dethemekit-for-elementor' ),
            'type' => \Elementor\Controls_Manager::HEADING,
            'separator' => 'before',
            'condition' => [
              'pagination_type' => 'loadmore',
            ],        
          ]
      );
      
      $element->add_control(
        'de_loadmore_text',
        [
          'label' => __( 'Text', 'dethemekit-for-elementor' ),
          'type' => \Elementor\Controls_Manager::TEXT,
          'default' => __( 'Load More', 'dethemekit-for-elementor' ),
          'placeholder' => __( 'Load More', 'dethemekit-for-elementor' ),
          'condition' => [
            'pagination_type' => 'loadmore',
          ],
        ]
      );
      
      $element->add_control(
        'de_loadmore_loading_text',
        [
          'label' => __( 'Loading Text', 'dethemekit-for-elementor' ),
          'type' => \Elementor\Controls_Manager::TEXT,
          'default' => __( 'Loading...', 'dethemekit-for-elementor' ),
          'placeholder' => __( 'Loading...', 'dethemekit-for-elementor' ),
          'condition' => [
            'pagination_type' => 'loadmore',
          ],
        ]
      );
      
      $element->add_control(
        'de_loadmore_spacing',
        [
          'label' => __( 'Button Spacing', 'dethemekit-for-elementor' ),
          'type' => \Elementor\Controls_Manager::SLIDER,
          'range' => [
            'px' => [
              'max' => 250,
            ],
          ],
          'default' =>[
            'unit' => 'px',
            'size' => '20',
          ],
          'selectors' => [
            '{{WRAPPER}} .ecs-load-more-button .elementor-button' => 'margin-top: {{SIZE}}{{UNIT}};',
          ],
          'condition' => [
            'pagination_type' => 'loadmore',
          ],
        ]
      );


     }
  }
  
  public function button_pagination_style($element, $section_id='', $args=''){

    if ( ( 'archive-posts' === $element->get_name() || 'posts' === $element->get_name() ) && 'section_pagination_style' === $section_id ) {
  
    	$element->start_controls_section(
        'de_loadmore_section_style',
        [
          'label' => __( 'Load More Button', 'dethemekit-for-elementor' ),
          'tab' => \Elementor\Controls_Manager::TAB_STYLE,
          'condition' => [
            'pagination_type' => 'loadmore',
          ],
        ]
      );

      $element->add_group_control(
        \Elementor\Group_Control_Typography::get_type(),
        [
          'name' => 'de_loadmore_typography',
          'global'    => [
            'default'   => Global_Typography::TYPOGRAPHY_ACCENT,
          ],
          'selector' => '{{WRAPPER}} .ecs-load-more-button .elementor-button',
        ]
      );

      $element->add_group_control(
        \Elementor\Group_Control_Text_Shadow::get_type(),
        [
          'name' => 'de_loadmore_text_shadow',
          'selector' => '{{WRAPPER}} .ecs-load-more-button .elementor-button',
        ]
      );

      $element->start_controls_tabs( 'de_tabs_button_style' );

      $element->start_controls_tab(
        'de_loadmore_tab_button_normal',
        [
          'label' => __( 'Normal', 'dethemekit-for-elementor' ),
        ]
      );

      $element->add_control(
        'de_loadmore_button_text_color',
        [
          'label' => __( 'Text Color', 'dethemekit-for-elementor' ),
          'type' => \Elementor\Controls_Manager::COLOR,
          'default' => '',
          'selectors' => [
            '{{WRAPPER}} .ecs-load-more-button .elementor-button' => 'fill: {{VALUE}}; color: {{VALUE}};',
          ],
        ]
      );

      $element->add_control(
        'de_loadmore_background_color',
        [
          'label' => __( 'Background Color', 'dethemekit-for-elementor' ),
          'type' => \Elementor\Controls_Manager::COLOR,
          'global'    => [
            'default' => Global_Colors::COLOR_ACCENT,
          ],
          'selectors' => [
            '{{WRAPPER}} .ecs-load-more-button .elementor-button' => 'background-color: {{VALUE}};',
          ],
        ]
      );

      $element->end_controls_tab();

      $element->start_controls_tab(
        'de_loadmore_tab_button_hover',
        [
          'label' => __( 'Hover', 'dethemekit-for-elementor' ),
        ]
      );

      $element->add_control(
        'de_loadmore_hover_color',
        [
          'label' => __( 'Text Color', 'dethemekit-for-elementor' ),
          'type' => \Elementor\Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .ecs-load-more-button .elementor-button:hover, {{WRAPPER}} .ecs-load-more-button .elementor-button:focus' => 'color: {{VALUE}};',
            '{{WRAPPER}} .ecs-load-more-button .elementor-button:hover svg, {{WRAPPER}} .ecs-load-more-button .elementor-button:focus svg' => 'fill: {{VALUE}};',
          ],
        ]
      );

      $element->add_control(
        'de_loadmore_button_background_hover_color',
        [
          'label' => __( 'Background Color', 'dethemekit-for-elementor' ),
          'type' => \Elementor\Controls_Manager::COLOR,
          'selectors' => [
            '{{WRAPPER}} .ecs-load-more-button .elementor-button:hover, {{WRAPPER}} .elementor-button:focus' => 'background-color: {{VALUE}};',
          ],
        ]
      );

      $element->add_control(
        'de_loadmore_button_hover_border_color',
        [
          'label' => __( 'Border Color', 'dethemekit-for-elementor' ),
          'type' => \Elementor\Controls_Manager::COLOR,
          'condition' => [
            'border_border!' => '',
          ],
          'selectors' => [
            '{{WRAPPER}} .ecs-load-more-button .elementor-button:hover, {{WRAPPER}} .elementor-button:focus' => 'border-color: {{VALUE}};',
          ],
        ]
      );

      $element->add_control(
        'de_loadmore_hover_animation',
        [
          'label' => __( 'Hover Animation', 'dethemekit-for-elementor' ),
          'type' => \Elementor\Controls_Manager::HOVER_ANIMATION,
        ]
      );

      $element->end_controls_tab();

      $element->end_controls_tabs();

      $element->add_group_control(
        \Elementor\Group_Control_Border::get_type(),
        [
          'name' => 'de_loadmore_border',
          'selector' => '{{WRAPPER}} .elementor-button',
          'separator' => 'before',
        ]
      );

      $element->add_control(
        'de_loadmore_border_radius',
        [
          'label' => __( 'Border Radius', 'dethemekit-for-elementor' ),
          'type' => \Elementor\Controls_Manager::DIMENSIONS,
          'size_units' => [ 'px', '%' ],
          'selectors' => [
            '{{WRAPPER}} .ecs-load-more-button .elementor-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
        ]
      );

      $element->add_group_control(
        \Elementor\Group_Control_Box_Shadow::get_type(),
        [
          'name' => 'de_loadmore_button_box_shadow',
          'selector' => '{{WRAPPER}} .ecs-load-more-button .elementor-button',
        ]
      );

      $element->add_responsive_control(
        'de_loadmore_text_padding',
        [
          'label' => __( 'Padding', 'dethemekit-for-elementor' ),
          'type' => \Elementor\Controls_Manager::DIMENSIONS,
          'size_units' => [ 'px', 'em', '%' ],
          'selectors' => [
            '{{WRAPPER}} .ecs-load-more-button .elementor-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
          ],
          'separator' => 'before',
        ]
      );
    	$element->end_controls_section();
    }
  }
  
  private function get_element_data($id,$data){
    
    foreach($data as $element){
       //echo "[".$element['id']."] == (".$id.")";
      if (isset($element['id']) && $element['id'] == $id) {
        return $element;
      } else {
        //echo $element['id']." - ".count($element['elements'])." > ";//print_r($element['elements']);
        if(count($element['elements'])) { 
            $element_children=$this->get_element_data($id,$element['elements']);
            if ($element_children) return $element_children ;
        }
        //echo"am ajuns aici?";
      }
    }
    return false;
  }

  public function get_document_data(){
 
    global $wp_query;
    

    $id = $this->widget_id;

    $post_id = $this->post_id;
    $theme_id = $this->theme_id;
    $old_query = $wp_query->query_vars;


    $this->query['paged'] = $this->current_page; // we need current(next) page to be loaded
    $this->query['post_status'] = 'publish';

    $wp_query = new \WP_Query($this->query);
    wp_reset_postdata();//this fixes some issues with some get_the_ID users.
    if (is_archive()){
      $post_id = $theme_id;
    }
 
		$document = \Elementor\Plugin::$instance->documents->get_doc_for_frontend( $post_id );
		$theme_document = \Elementor\Plugin::$instance->documents->get_doc_for_frontend( $theme_id );
   
    $data[] = $this->get_element_data($id,$theme_document->get_elements_data());

		// Change the current post, so widgets can use `documents->get_current`.
		\Elementor\Plugin::$instance->documents->switch_to_document( $document );

    ob_start();
        $document->print_elements_with_wrapper( $data );
    $content = ob_get_clean();
    echo esc_html( $this->clean_response($content,$id) );

    \Elementor\Plugin::$instance->documents->restore_document();
    $wp_query->query_vars = $query_vars;

    die;
  }
  
  private function clean_response($html,$id){
    $content = "";
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
    //$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    $childs = $xpath->query('//div[@data-id="'.$id.'"]/div[@class="elementor-widget-container"]/div/* | //div[@data-elementor-type="de_grid"]');
//    $childs = $xpath->query('//div[@data-elementor-type="custom_grid"]');
  //return $dom->saveHTML($childs->item(0));
    foreach($childs as $child){
      $content .= $dom->saveHTML($child);
    }
    //$div = $div->item(0);
    return $content;
  }
  
  public function enqueue_scripts(){
    
    global $wp_query; 
    
    wp_register_script('dtk_ajax_load', DETHEMEKIT_ADDONS_URL . 'assets/js/de_loop/ecs_ajax_pagination.js',array('jquery'),DETHEMEKIT_ADDONS_VERSION);
    
    wp_localize_script( 'dtk_ajax_load', 'ecs_ajax_params', array(
        'ajaxurl' => admin_url('admin-ajax.php'), // WordPress AJAX
        'posts' => wp_json_encode( $wp_query->query_vars ),
     ) );

    wp_enqueue_script( 'dtk_ajax_load' ); 
  }
  
}


class dtk_Loading_Animation {
  private static function animations(){ 
    return [
      'default'=>[
        'label' => __( 'Default', 'dethemekit-for-elementor' ),
        'html' => '<div class="lds-ellipsis ecs-lazy-load-animation"><div class="ecs-ll-bgcolor"></div><div class="ecs-ll-bgcolor"></div><div class="ecs-ll-bgcolor"></div><div class="ecs-ll-bgcolor"></div></div>',
      ],
      'progress_bar'=>[
        'label' => __( 'Progress Bar', 'dethemekit-for-elementor' ),
        'html' => '<div class="barload-wrapper  ecs-lazy-load-animation"><div class="barload-border ecs-ll-brcolor"><div class="barload-whitespace"><div class="barload-line ecs-ll-bgcolor"></div></div></div></div>',
      ],
      'running_dots'=>[
        'label' => __( 'Running Dots', 'dethemekit-for-elementor' ),
        'html' => '<div class="ballsload-container ecs-lazy-load-animation"><div class="ecs-ll-bgcolor"></div><div class="ecs-ll-bgcolor"></div><div class="ecs-ll-bgcolor"></div><div class="ecs-ll-bgcolor"></div></div>',
      ],
      'ball_slide'=>[
        'label' => __( 'Ball Slide', 'dethemekit-for-elementor' ),
        'html' => '<div id="movingBallG" class="ecs-lazy-load-animation"><div class="movingBallLineG  ecs-ll-bgcolor"></div><div id="movingBallG_1" class="movingBallG ecs-ll-bgcolor"></div></div>',
      ],

    ];
   }


  public static function get_lazy_load_animations_html($animation){
    $arrs = self::animations();
    return $arrs[$animation]['html'];    
  }
  
  public static function get_lazy_load_animations_list(){
    $arrs = self::animations();
    foreach ( $arrs as $key => $arr ) {
      $options[ $key ] = $arr['label'];
    }
    return $options;    
  }
  
}



new dtk_Ajax_Load();
  
  
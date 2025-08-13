<?php
namespace ElementorPro\Modules\ThemeBuilder\Documents;
use ElementorPro\Modules\ThemeBuilder\Documents\Single;
use ElementorPro\Modules\ThemeBuilder\Module;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class DeLoop extends Single {

	public static function get_properties() {
		$properties = parent::get_properties();

		$properties['condition_type'] = 'de_loop';
    //$properties['location'] = 'archive';
		$properties['location'] = 'de_loop';
    		$properties['support_kit'] = true;
		$properties['support_site_editor'] = true;
		return $properties;
	}

	public function get_name() {
		return 'de_loop';
	}
  
  protected static function get_site_editor_thumbnail_url() {
		return DETHEMEKIT_ADDONS_DIR_URL . 'assets/images/loop.svg';
	}

	public static function get_title() {
		return __( 'De Loop', 'dethemekit-for-elementor' );
	}

/*

Let's be undependable from Preview As options

*/
	public static function get_preview_as_options() {
		$post_types = self::get_public_post_types();//Module::get_public_post_types();

		$post_types['attachment'] = get_post_type_object( 'attachment' )->label;
		$post_types_options = [];

		foreach ( $post_types as $post_type => $label ) {
			$post_types_options[ 'single/' . $post_type ] = get_post_type_object( $post_type )->labels->singular_name;
		}

		return [
			'single' => [
				'label' => __( 'Single', 'dethemekit-for-elementor' ),
				'options' => $post_types_options,
			],
			'page/404' => __( '404', 'dethemekit-for-elementor' ),
		];
	}
  
  public static function get_public_post_types(){
    //Array ( [post] => Posts [page] => Pages ) 
    $post_types_options = [];
    $args = array(
    'public'   => true,
    );
    $output = 'objects'; // names or objects
    $post_types = get_post_types( $args, $output );
     foreach ( $post_types  as $post_type ) {
       if ('elementor_library' != $post_type->name) $post_types_options[$post_type->name]= $post_type->label ;
    }
    return $post_types_options;
  }
/*

I want a preview like the template not default

*/
  
 
}

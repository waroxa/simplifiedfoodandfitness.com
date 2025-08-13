<?php

/**
 * PA Helper Functions.
 */
namespace DethemeKitAddons;

if( ! defined('ABSPATH') ) exit;

/**
 * Class Helper_Functions.
 */
class Helper_Functions {
    
    /**
	 * Google maps prefixes
	 *
	 * @var google_localize
	 */
    private static $google_localize = null;
    
    /**
	 * script debug enabled
	 *
	 * @var script_debug
	 */
	private static $script_debug = null;
    
    /**
	 * JS scripts directory
	 *
	 * @var js_dir
	 */
	private static $js_dir = null;
    
    /**
	 * CSS fiels directory
	 *
	 * @var js_dir
	 */
	private static $css_dir = null;

	/**
	 * JS Suffix
	 *
	 * @var js_suffix
	 */
	private static $assets_suffix = null;
    
    /**
     * Check if white labeling - hide rating message is checked
     * 
     * @since 1.0.0
     * @access public
     * 
     * @return boolean
     */
    public static function is_hide_rate(){
        
        if( defined('DETHEMEKIT_PRO_ADDONS_VERSION') ) {
            if( isset(get_option('pa_wht_lbl_save_settings')['dethemekit-wht-lbl-rate'] ) ) {
                $hide_rate = get_option('pa_wht_lbl_save_settings')['dethemekit-wht-lbl-rate'];
            }
        }
        
        return isset( $hide_rate ) ? $hide_rate : false;
    }
    
    /**
     * Check if white labeling - hide about page is checked
     * 
     * @since 1.0.0
     * @access public
     * 
     * @return boolean
     */
    public static function is_hide_about(){
        
        if( defined('DETHEMEKIT_PRO_ADDONS_VERSION') ) {
            if(isset(get_option('pa_wht_lbl_save_settings')['dethemekit-wht-lbl-about'])){
                $hide_about = get_option('pa_wht_lbl_save_settings')['dethemekit-wht-lbl-about'];
            }
        }
        
        return isset( $hide_about ) ? $hide_about : false;
    }
    
    /**
     * Check if white labeling - hide version control page is checked
     * 
     * @since 1.0.0
     * @access public
     * 
     * @return boolean
     */
    public static function is_hide_version_control(){
        
        if( defined('DETHEMEKIT_PRO_ADDONS_VERSION') ) {
            if(isset(get_option('pa_wht_lbl_save_settings')['dethemekit-wht-lbl-version'])){
                $hide_version_tab = get_option('pa_wht_lbl_save_settings')['dethemekit-wht-lbl-version'];
            }
        }
        
        return isset( $hide_version_tab ) ? $hide_version_tab : false;
    }
    
    /**
     * Check if white labeling - Free version author field is set
     * 
     * @since 1.0.0
     * @access public
     * 
     * @return string
     */
    public static function author(){
        
        if( defined('DETHEMEKIT_PRO_ADDONS_VERSION') ) {
            if(isset(get_option('pa_wht_lbl_save_settings')['dethemekit-wht-lbl-name'])){
                $author_free = get_option('pa_wht_lbl_save_settings')['dethemekit-wht-lbl-name'];
            }
        }
        
        return ( isset( $author_free ) && '' != $author_free ) ? $author_free : 'Leap13';
    }
    
    /**
     * Check if white labeling - Free version name field is set
     * 
     * @since 1.0.0
     * @access public
     * 
     * @return string
     */
    public static function name() {
        
        if( defined('DETHEMEKIT_PRO_ADDONS_VERSION') ) {
            if(isset(get_option('pa_wht_lbl_save_settings')['dethemekit-wht-lbl-plugin-name'])){
                $name_free = get_option('pa_wht_lbl_save_settings')['dethemekit-wht-lbl-plugin-name'];
            }
        }
        
        return ( isset( $name_free ) && '' != $name_free ) ? $name_free : 'DethemeKit Addons for Elementor';
    }
    
    /**
     * Check if white labeling - Hide row meta option is checked
     * 
     * @since 1.0.0
     * @return string
     * 
     */
    public static function is_hide_row_meta() {
        
        if( defined('DETHEMEKIT_PRO_ADDONS_VERSION') ) {
            if( isset( get_option('pa_wht_lbl_save_settings')['dethemekit-wht-lbl-row'] ) ){
                $hide_meta = get_option('pa_wht_lbl_save_settings')['dethemekit-wht-lbl-row'];
            }
        }
        
        return isset( $hide_meta ) ? $hide_meta : false;
    }
   
    /**
     * Check if white labeling - Hide plugin logo option is checked
     * 
     * @since 1.0.0
     * @access public
     * 
     * @return string
     */
    public static function is_hide_logo(){
        
        if( defined('DETHEMEKIT_PRO_ADDONS_VERSION') ) {
            if(isset(get_option('pa_wht_lbl_save_settings')['dethemekit-wht-lbl-logo'])){
                $hide_logo = get_option('pa_wht_lbl_save_settings')['dethemekit-wht-lbl-logo'];
            }
        }
        
        return isset( $hide_logo ) ? $hide_logo : false;
    }
    
    /**
     * Get White Labeling - Widgets Category string
     * 
     * @since 1.0.0
     * @access public
     * 
     * @return string
     */
    public static function get_category(){
        
        if( defined('DETHEMEKIT_PRO_ADDONS_VERSION') ) {
            if(isset(get_option('pa_wht_lbl_save_settings')['dethemekit-wht-lbl-short-name'])){
                $category = get_option('pa_wht_lbl_save_settings')['dethemekit-wht-lbl-short-name'];
            }
        }
        
        return ( isset( $category ) && '' != $category ) ? $category : __( 'DethemeKit Addons', 'dethemekit-for-elementor' );
        
    }
    
    /**
     * Get White Labeling - Widgets Prefix string
     * 
     * @since 1.0.0
     * @access public
     * 
     * @return string
     */
    public static function get_prefix(){
        
        if( defined('DETHEMEKIT_PRO_ADDONS_VERSION') ) {
            if(isset(get_option('pa_wht_lbl_save_settings')['dethemekit-wht-lbl-prefix'])){
                $prefix = get_option('pa_wht_lbl_save_settings')['dethemekit-wht-lbl-prefix'];
            }
        }
        
        return ( isset( $prefix ) && '' != $prefix ) ? $prefix : __('DethemeKit', 'dethemekit-for-elementor');
    }
    
    /**
     * Get White Labeling - Widgets Badge string
     * 
     * @since 1.0.0
     * @access public
     * 
     * @return string
     */
    public static function get_badge() {
        
        if( defined('DETHEMEKIT_PRO_ADDONS_VERSION') ) {
            if(isset(get_option('pa_wht_lbl_save_settings')['dethemekit-wht-lbl-badge'])){
                $badge = get_option('pa_wht_lbl_save_settings')['dethemekit-wht-lbl-badge'];
            }
        }
        
        return ( isset( $badge ) && '' != $badge ) ? $badge : 'PA';
    }
    
    /**
     * Get Google Maps localization prefixes
     * 
     * @since 1.0.0
     * @access public
     * 
     * @return array
     */
    public static function get_google_maps_prefixes() {
        
        if ( null === self::$google_localize ) {

			self::$google_localize = array(
				 'ar' => __( 'Arabic', 'dethemekit-for-elementor'),
                'eu' => __( 'Basque', 'dethemekit-for-elementor'),
                'bg' => __( 'Bulgarian', 'dethemekit-for-elementor'),
                'bn' => __( 'Bengali', 'dethemekit-for-elementor'),
                'ca' => __( 'Catalan', 'dethemekit-for-elementor'),
                'cs' => __( 'Czech', 'dethemekit-for-elementor'),
                'da' => __( 'Danish', 'dethemekit-for-elementor'),
                'de' => __( 'German', 'dethemekit-for-elementor'),
                'el' => __( 'Greek', 'dethemekit-for-elementor'),
                'en' => __( 'English', 'dethemekit-for-elementor'),
                'en-AU' => __( 'English (australian)', 'dethemekit-for-elementor'),
                'en-GB' => __( 'English (great britain)', 'dethemekit-for-elementor'),
                'es' => __( 'Spanish', 'dethemekit-for-elementor'),
                'fa' => __( 'Farsi', 'dethemekit-for-elementor'),
                'fi' => __( 'Finnish', 'dethemekit-for-elementor'),
                'fil' => __( 'Filipino', 'dethemekit-for-elementor'),
                'fr' => __( 'French', 'dethemekit-for-elementor'),
                'gl' => __( 'Galician', 'dethemekit-for-elementor'),
                'gu' => __( 'Gujarati', 'dethemekit-for-elementor'),
                'hi' => __( 'Hindi', 'dethemekit-for-elementor'),
                'hr' => __( 'Croatian', 'dethemekit-for-elementor'),
                'hu' => __( 'Hungarian', 'dethemekit-for-elementor'),
                'id' => __( 'Indonesian', 'dethemekit-for-elementor'),
                'it' => __( 'Italian', 'dethemekit-for-elementor'),
                'iw' => __( 'Hebrew', 'dethemekit-for-elementor'),
                'ja' => __( 'Japanese', 'dethemekit-for-elementor'),
                'kn' => __( 'Kannada', 'dethemekit-for-elementor'),
                'ko' => __( 'Korean', 'dethemekit-for-elementor'),
                'lt' => __( 'Lithuanian', 'dethemekit-for-elementor'),
                'lv' => __( 'Latvian', 'dethemekit-for-elementor'),
                'ml' => __( 'Malayalam', 'dethemekit-for-elementor'),
                'mr' => __( 'Marathi', 'dethemekit-for-elementor'),
                'nl' => __( 'Dutch', 'dethemekit-for-elementor'),
                'no' => __( 'Norwegian', 'dethemekit-for-elementor'),
                'pl' => __( 'Polish', 'dethemekit-for-elementor'),
                'pt' => __( 'Portuguese', 'dethemekit-for-elementor'),
                'pt-BR' => __( 'Portuguese (brazil)', 'dethemekit-for-elementor'),
                'pt-PT' => __( 'Portuguese (portugal)', 'dethemekit-for-elementor'),
                'ro' => __( 'Romanian', 'dethemekit-for-elementor'),
                'ru' => __( 'Russian', 'dethemekit-for-elementor'),
                'sk' => __( 'Slovak', 'dethemekit-for-elementor'),
                'sl' => __( 'Slovenian', 'dethemekit-for-elementor'),
                'sr' => __( 'Serbian', 'dethemekit-for-elementor'),
                'sv' => __( 'Swedish', 'dethemekit-for-elementor'),
                'tl' => __( 'Tagalog', 'dethemekit-for-elementor'),
                'ta' => __( 'Tamil', 'dethemekit-for-elementor'),
                'te' => __( 'Telugu', 'dethemekit-for-elementor'),
                'th' => __( 'Thai', 'dethemekit-for-elementor'),
                'tr' => __( 'Turkish', 'dethemekit-for-elementor'),
                'uk' => __( 'Ukrainian', 'dethemekit-for-elementor'),
                'vi' => __( 'Vietnamese', 'dethemekit-for-elementor'),
                'zh-CN' => __( 'Chinese (simplified)', 'dethemekit-for-elementor'),
                'zh-TW' => __( 'Chinese (traditional)', 'dethemekit-for-elementor'),
			);
		}

		return self::$google_localize;
        
    }
    
    /**
     * Checks if a plugin is installed
     * 
     * @since 1.0.0
     * @access public
     * 
     * @param $plugin_path string plugin path
     * 
     * @return boolean
     */
    public static function is_plugin_installed( $plugin_path ) {
        
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        
        $plugins = get_plugins();
        
        return isset( $plugins[ $plugin_path ] );
    }
    
    /**
	 * Check if script debug mode enabled.
	 *
	 * @since 3.11.1
     * @access public
	 *
	 * @return boolean is debug mode enabled
	 */
	public static function is_debug_enabled() {

		if ( null === self::$script_debug ) {

			self::$script_debug = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
		}

		return self::$script_debug;
	}
    
    /**
	 * Get scripts dir.
	 *
	 * @access public
	 *
	 * @return string JS scripts directory.
	 */
	public static function get_scripts_dir() {

		if ( null === self::$js_dir ) {

			self::$js_dir = self::is_debug_enabled() ? 'js' : 'min-js';
		}

		return self::$js_dir;
	}
    
    /**
	 * Get styles dir.
	 *
	 * @access public
	 *
	 * @return string CSS files directory.
	 */
	public static function get_styles_dir() {

		if ( null === self::$css_dir ) {

			self::$css_dir = self::is_debug_enabled() ? 'css' : 'min-css';
		}

		return self::$css_dir;
	}

	/**
	 * Get assets suffix.
	 *
	 * @access public
	 *
	 * @return string JS scripts suffix.
	 */
	public static function get_assets_suffix() {

		if ( null === self::$assets_suffix ) {

			self::$assets_suffix = self::is_debug_enabled() ? '' : '.min';
		}

		return self::$assets_suffix;
	}
    
    /**
     * Get Installed Theme
     * 
     * Returns the active theme slug
     * 
     * @access public
     * 
     * @return string theme slug
     */
    public static function get_installed_theme() {

        $theme = wp_get_theme();

        if( $theme->parent() ) {

            $theme_name = $theme->parent()->get('Name');

        } else {

            $theme_name = $theme->get('Name');

        }

        $theme_name = sanitize_key( $theme_name );

        return $theme_name;
    }
    
    /*
     * Get Vimeo Video Data
     * 
     * Get video data using Vimeo API
     * 
     * @since 3.11.4
     * @access public
     * 
     * @param string $id video ID
     */
    public static function get_vimeo_video_data( $id ) {
        
        $vimeo_data         = wp_remote_get( 'http://www.vimeo.com/api/v2/video/' . intval( $id ) . '.php' );
        
        if ( isset( $vimeo_data['response']['code'] ) ) {

            if( '200' == $vimeo_data['response']['code'] ) {

                $response       = unserialize( $vimeo_data['body'] );
                $thumbnail = isset( $response[0]['thumbnail_large'] ) ? $response[0]['thumbnail_large'] : false;
                
                $data = [
                    'src'       => $thumbnail,
                    'url'       => $response[0]['user_url'],
                    'portrait'  => $response[0]['user_portrait_huge'],
                    'title'     => $response[0]['title'],
                    'user'      => $response[0]['user_name']
                ];
            
                return $data;

            }
            
        }
        
        return false;
        
    }
    
    /*
     * Get Video Thumbnail
     * 
     * Get thumbnail URL for embed or self hosted
     * 
     * @since 3.7.0
     * @access public
     * 
     * @param string $id video ID
     * @param string $type embed type
     * @param string $size youtube thumbnail size
     */
    public static function get_video_thumbnail( $id, $type, $size = '' ) {
        
        $thumbnail_src = '';
        
        if ( 'youtube' === $type ) {
            if ( '' === $size ) {
                $size = 'maxresdefault';
            }
            $thumbnail_src = sprintf( 'https://i.ytimg.com/vi/%s/%s.jpg', $id, $size );

        } elseif ( 'vimeo' === $type ) {
           
            $vimeo = self::get_vimeo_video_data( $id );
            
            // if( isset( $vimeo['src'] ) ) {
                $thumbnail_src = $vimeo['src'];
            // }
            
        } else {
            $thumbnail_src = 'transparent';
        }
        
        return $thumbnail_src;
    }

    /**
     * Transient Expire
     * 
     * Gets expire time of transient.
     *
     * @since 3.20.8
     * @access public
     * 
     * @param string $period reviews transient expiration period.
     * 
     * @return the reviews transient expire time in seconds.
    */
    public static function transient_expire( $period ) {

        $expire_time  = 24 * HOUR_IN_SECONDS;

        if ( 'hour' === $period ) {
            $expire_time = MINUTE_IN_SECONDS;
        } elseif ( 'hour' === $period ) {
            $expire_time = 60 * MINUTE_IN_SECONDS;
        } elseif ( 'week' === $period ) {
            $expire_time = 7 * DAY_IN_SECONDS;
        } elseif ( 'month' === $period ) {
            $expire_time = 30 * DAY_IN_SECONDS;
        } elseif ( 'year' === $period ) {
            $expire_time = 365 * DAY_IN_SECONDS;
        }

        return $expire_time;
    }
}
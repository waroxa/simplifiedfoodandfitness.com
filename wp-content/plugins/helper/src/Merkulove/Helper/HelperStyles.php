<?php

namespace Merkulove\Helper;

use Merkulove\Helper\Unity\Settings;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}


/**
 * SINGLETON: Helper class contain styles for chatbot.
 *
 * @since 1.0.0
 *
 **/
final class HelperStyles {

    /**
     * The one true HelperStyles.
     *
     * @since 1.0.0
     * @access private
     * @var HelperStyles
     **/
    private static $instance;


    /**
     * Returns sides default value.
     *
     * @return string
     * @since 1.0.0
     * @access private
     *
     */
    public function get_sides_item_default_value( $sides_name, $side ) {
        $options = Settings::get_instance()->options;

        return isset( $options[$sides_name][$side] ) ? esc_attr( $options[$sides_name][$side] ) : 0;
    }

    /**
     * Returns styles for chatbot container.
     *
     * @since 1.0.0
     * @access private
     **/
    public function create_bot_container_styles() {

        $options = Settings::get_instance()->options;
        $bot_container_margin_unit = $options['bot_container_margin_unit'] ?? 'px';
        $bot_container_margin_left = $options['bot_container_margin_left'] ?? $this->get_sides_item_default_value( 'bot_container_margin', 'left' );
        $bot_container_margin_right = $options['bot_container_margin_right'] ?? $this->get_sides_item_default_value( 'bot_container_margin', 'right' );

        return 'calc(100%' . ' - (' . $bot_container_margin_right .
                $bot_container_margin_unit . ' + ' .
                $bot_container_margin_left . $bot_container_margin_unit .' ) );';

    }

    /**
     * Main HelperStyles Instance.
     * Insures that only one instance of HelperStyles exists in memory at any one time.
     *
     * @static
     * @since 1.0.0
     * @access public
     *
     * @return HelperStyles
     **/
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

            self::$instance = new self;

        }

        return self::$instance;

    }


}

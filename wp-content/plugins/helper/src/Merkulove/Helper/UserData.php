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
 * SINGLETON: UserData class contain main plugin logic.
 *
 * @since 1.0.0
 *
 **/
final class UserData {

    /**
     * The one true UserData.
     *
     * @since 1.0.0
     * @access private
     * @var UserData
     **/
    private static $instance;

    /**
     * Sets up a new UserData instance.
     *
     * @since 1.0.0
     * @access public
     **/
    public function __construct() {

    }


    /**
     * Create User Data Custom Post Type method.
     *
     * @since 1.0.0
     * @access public
     *
     * @return void
     **/
    public function create_user_data_cpt() {
        register_post_type( 'mdp_user_data_cpt', [
            'public'              => false,
            'labels'              => [
                'name'                  => esc_html__( 'Helper', 'helper' ),
                'singular_name'         => esc_html__( 'User Data', 'helper' ),
                'add_new'               => esc_html__( 'Add New', 'helper' ),
                'add_new_item'          => esc_html__( 'Add New', 'helper' ),
                'new_item'              => esc_html__( 'New User Data', 'helper' ),
                'edit_item'             => esc_html__( 'Edit User Data', 'helper' ),
                'view_item'             => esc_html__( 'View User Data', 'helper' ),
                'view_items'            => esc_html__( 'View User Data', 'helper' ),
                'search_items'          => esc_html__( 'Search User Data', 'helper' ),
                'not_found'             => esc_html__( 'User Data not found', 'helper' ),
                'not_found_in_trash'    => esc_html__( 'User Data not found in Trash', 'helper' ),
                'all_items'             => esc_html__( 'User Data', 'helper' ),
                'archives'              => esc_html__( 'User Data Archives', 'helper' ),
                'attributes'            => esc_html__( 'User Data Attributes', 'helper' ),
                'insert_into_item'      => esc_html__( 'Insert to User Data', 'helper' ),
                'uploaded_to_this_item' => esc_html__( 'Uploaded to this User Data', 'helper' ),
            ],
            'menu_icon'             => $this->get_admin_menu_icon(),
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'menu_position'         => false,
            'show_in_rest'          => false,
            'rest_base'             => 'mdp_helper_sub',
            'supports'              => [ 'title' ],
            'register_meta_box_cb' => [ $this, 'user_data_metabox_callback' ],
            'capabilities'          => [ 'create_posts' => false, 'publish_posts' => false ],
            'map_meta_cap'          => true,
            'show_ui'               => true,
        ] );
    }

    /**
     * Return path to admin menu icon or base64 encoded image.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string
     **/
    private function get_admin_menu_icon() {

        return 'data:image/svg+xml;base64,' . base64_encode(
                file_get_contents( Unity\Plugin::get_path() . 'images/logo-menu.svg' )
            );
    }

    /**
     * Callback for creating user data records fields metabox.
     *
     * @since 1.0.0
     * @access public
     *
     * @return void
     **/
    public function user_data_metabox_callback() {
        add_meta_box(
            'mdp_helper_user_data_metabox',
            esc_html__( 'User data record', 'helper' ),
            [ $this, 'user_data_record_fields_metabox' ],
            '',
            'normal',
            'high'
        );
    }

    /**
     * Form fields data metabox.
     *
     * @since 1.0.0
     * @access public
     *
     * @return void
     **/
    public function user_data_record_fields_metabox( $post ) {
        $post_meta = get_post_meta( $post->ID );

        ?>
        <table class="form-table">
            <tbody>
            <?php
            foreach ( $post_meta as $key => $value ):
                if ( $key === '_edit_lock' || $key === '_edit_last' ) { continue; }
                ?>
                <tr>
                    <th scope="row">
                        <label> <?php esc_html_e( $key . ':' ); ?> </label>
                    </th>
                    <td>
                        <div>
                            <div class="mdp-helper-field-value">
                               <span> <?php esc_html_e( $value[0] ); ?> </span>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    /**
     * Main UserData Instance.
     * Insures that only one instance of UserData exists in memory at any one time.
     *
     * @static
     * @since 1.0.0
     * @access public
     *
     * @return UserData
     **/
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

            self::$instance = new self;

        }

        return self::$instance;

    }

}
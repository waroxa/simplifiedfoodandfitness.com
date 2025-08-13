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
 * SINGLETON: BotLogs class contain main plugin logic.
 *
 * @since 1.0.0
 *
 **/
final class BotLogs {
    /**
     * The one true BotLogs.
     *
     * @since 1.0.0
     * @access private
     * @var UserData
     **/
    private static $instance;

    /**
     * Create Bot Logs Custom Post Type method.
     *
     * @since 1.0.0
     * @access public
     *
     * @return void
     **/
    public function create_bot_logs_cpt() {
        $options = Settings::get_instance()->options;
        $collect_data_enabled = !empty( $options['save_user_data'] ) &&
            $options['save_user_data'] === 'on' &&
            in_array( 'collect_data', $options['bot_features'] );

        $post_type_options = [
            'public'              => false,
            'labels'              => [
                'name'                  => esc_html__( 'Helper', 'helper' ),
                'singular_name'         => esc_html__( 'Bot Log', 'helper' ),
                'add_new'               => esc_html__( 'Add New', 'helper' ),
                'add_new_item'          => esc_html__( 'Add New', 'helper' ),
                'new_item'              => esc_html__( 'New Bot Log', 'helper' ),
                'edit_item'             => esc_html__( 'Edit Bot Log', 'helper' ),
                'view_item'             => esc_html__( 'View Bot Log', 'helper' ),
                'view_items'            => esc_html__( 'View Bot Log', 'helper' ),
                'search_items'          => esc_html__( 'Search Bot Log', 'helper' ),
                'not_found'             => esc_html__( 'Bot Log not found', 'helper' ),
                'not_found_in_trash'    => esc_html__( 'Bot Log found in Trash', 'helper' ),
                'all_items'             => esc_html__( 'Bot Logs', 'helper' ),
                'archives'              => esc_html__( 'Bot Log Archives', 'helper' ),
                'attributes'            => esc_html__( 'Bot Log Attributes', 'helper' ),
                'insert_into_item'      => esc_html__( 'Insert to Bot Log', 'helper' ),
                'uploaded_to_this_item' => esc_html__( 'Uploaded to this Bot Log', 'helper' ),
            ],
            'menu_icon'             => $this->get_admin_menu_icon(),
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'menu_position'         => false,
            'show_in_rest'          => false,
            'rest_base'             => 'mdp_helper_sub',
            'supports'              => [ 'title' ],
            'register_meta_box_cb' => [ $this, 'bot_logs_metabox_callback' ],
            'capabilities'          => [ 'create_posts' => false, 'publish_posts' => false ],
            'map_meta_cap'          => true,
            'show_ui'               => true,
        ];

        if ( $collect_data_enabled ) {
            $post_type_options['show_in_menu'] = 'edit.php?post_type=mdp_user_data_cpt';
        }

        register_post_type( 'mdp_bot_logs_cpt', $post_type_options );
    }

    /**
     * Set delete old logs action.
     *
     * @since 1.0.0
     * @access public
     *
     * @return void
     **/
    public function delete_old_logs_actions() {
        $options = Settings::get_instance()->options;
        $bot_logs = !empty( $options['bot_logs'] ) && $options['bot_logs'] === 'on';

        // Delete scheduled event if ot logs turned off or auto delete turned off
        if ( $options['bot_logs_auto_delete'] !== 'on' ) {
            if ( wp_next_scheduled( 'mdp_old_logs_delete' ) ) {
                wp_unschedule_event( wp_next_scheduled( 'mdp_old_logs_delete' ), 'mdp_old_logs_delete' );
            }
        }

        // Exit if bot logs turned off
        if ( !$bot_logs && $options['bot_logs_auto_delete'] !== 'on' ) { return; }

        // expired_post_delete hook fires when the Cron is executed
        add_action( 'mdp_old_logs_delete', [$this, 'delete_old_bot_logs'] );

        // Add function to register event to wp
        add_action( 'init', [$this, 'register_daily_logs_delete_event'] );

    }

    /**
     * Schedule daily event.
     *
     * @since 1.0.0
     * @access public
     *
     * @return void
     **/
    public function register_daily_logs_delete_event() {
        // Make sure this event hasn't been scheduled
        if( !wp_next_scheduled( 'mdp_old_logs_delete' ) ) {
            // Schedule the event
            wp_schedule_event( time(), 'daily', 'mdp_old_logs_delete' );
        }
    }

    public function delete_old_bot_logs() {
        $options = Settings::get_instance()->options;
        $bot_logs = !empty( $options['bot_logs'] ) && $options['bot_logs'] === 'on';

        // Exit if bot logs turned off
        if ( !$bot_logs && $options['bot_logs_auto_delete'] !== 'on' ) { return; }

        $day_str = (int) $options['bot_logs_delete_after'] > 1 ? ' days' : ' day';

        // Set our query arguments
        $args = [
            'fields'         => 'ids', // Only get post ID's to improve performance
            'post_type'      => 'mdp_bot_logs_cpt',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'date_query'    => array(
                'before' => date( 'Y-m-d', strtotime( '-'. esc_attr( $options['bot_logs_delete_after'] ) .$day_str ) )
            )
        ];

        $old_logs = get_posts( $args );

        // Exit if there is no logs to delete
        if ( empty( $old_logs ) ) { return; }

        foreach ( $old_logs as $old_log ) {
            wp_delete_post( $old_log, true );
        }

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
     * Callback for creating bot logs fields metabox.
     *
     * @since 1.0.0
     * @access public
     *
     * @return void
     **/
    public function bot_logs_metabox_callback() {
        add_meta_box(
            'mdp_helper_bot_interactions_metabox',
            esc_html__( 'Bot Interactions record', 'helper' ),
            [ $this, 'bot_logs_fields_metabox' ],
            '',
            'normal',
            'high'
        );
    }

    /**
     * Bot logs fields data metabox.
     *
     * @since 1.0.0
     * @access public
     *
     * @return void
     **/
    public function bot_logs_fields_metabox( $post ) {
        $post_meta = get_post_meta( $post->ID );
        ?>
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row">
                    <label> <?php esc_html_e( 'Bot log:', 'helper' ); ?> </label>
                </th>
                <td>
                    <div>
                        <div class="mdp-helper-field-value">
                           <?php echo isset( $post_meta['bot_dialog'] ) ?
                               wp_kses_post( str_replace( ';', '<br>', $post_meta['bot_dialog'][0] ) )
                               : ''; ?>
                        </div>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
        <?php
    }

    /**
     * Main BotLogs Instance.
     * Insures that only one instance of BotLogs exists in memory at any one time.
     *
     * @static
     * @since 1.0.0
     * @access public
     *
     * @return BotLogs
     **/
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

            self::$instance = new self;

        }

        return self::$instance;

    }

}
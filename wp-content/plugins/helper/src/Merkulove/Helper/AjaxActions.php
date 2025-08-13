<?php

namespace Merkulove\Helper;

use Merkulove\Helper\Unity\Settings;
use Parsedown;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}


/**
 * SINGLETON: AjaxActions class contains ajax logic.
 *
 * @since 1.0.0
 *
 **/
final class AjaxActions {

    /**
     * The one true AjaxActions.
     *
     * @since 1.0.0
     * @access private
     * @var AjaxActions
     **/
    private static $instance;


    /**
     * Sets up a new AjaxActions instance.
     *
     * @since 1.0.0
     * @access public
     **/
    private function __construct() {
        /** Returns bot response on user inputed text */
        add_action( 'wp_ajax_mdp_helper_bot_response', [$this, 'bot_response'] );
        add_action( 'wp_ajax_nopriv_mdp_helper_bot_response', [$this, 'bot_response'] );

        /** Returns all FAQ questions */
        add_action( 'wp_ajax_mdp_helper_get_faq_questions', [$this, 'get_faq_questions'] );
        add_action( 'wp_ajax_nopriv_mdp_helper_get_faq_questions', [$this, 'get_faq_questions'] );

        /** Returns FAQ questions by category */
        add_action( 'wp_ajax_mdp_helper_get_faq_questions_by_cat', [$this, 'get_questions_by_category'] );
        add_action( 'wp_ajax_nopriv_mdp_helper_get_faq_questions_by_cat', [$this, 'get_questions_by_category'] );

        /** Returns answer on FAQ question */
        add_action( 'wp_ajax_mdp_helper_get_faq_response', [$this, 'get_bot_faq_answer'] );
        add_action( 'wp_ajax_nopriv_mdp_helper_get_faq_response', [$this, 'get_bot_faq_answer'] );


        /** Returns bot dialog message */
        add_action( 'wp_ajax_mdp_helper_get_dialog_message', [$this, 'get_dialog_message'] );
        add_action( 'wp_ajax_nopriv_mdp_helper_get_dialog_message', [$this, 'get_dialog_message'] );

        /** Returns bot collect data messages */
        add_action( 'wp_ajax_mdp_helper_get_collect_data_messages', [$this, 'get_collect_data_messages'] );
        add_action( 'wp_ajax_nopriv_mdp_helper_get_collect_data_messages', [$this, 'get_collect_data_messages'] );

        /** Returns bot collect data messages */
        add_action( 'wp_ajax_mdp_helper_collect_user_data', [$this, 'collect_user_data_actions'] );
        add_action( 'wp_ajax_nopriv_mdp_helper_collect_user_data', [$this, 'collect_user_data_actions'] );

        /** Sends user email */
        add_action( 'wp_ajax_mdp_helper_send_user_email', [$this, 'send_user_email'] );
        add_action( 'wp_ajax_nopriv_mdp_helper_send_user_email', [$this, 'send_user_email'] );

        /** Create new bot log */
        add_action( 'wp_ajax_mdp_helper_create_new_log', [$this, 'create_new_log'] );
        add_action( 'wp_ajax_nopriv_mdp_helper_create_new_log', [$this, 'create_new_log'] );

        /** Update bot log */
        add_action( 'wp_ajax_mdp_helper_update_log', [$this, 'update_log'] );
        add_action( 'wp_ajax_nopriv_mdp_helper_update_log', [$this, 'update_log'] );

        /** Create assistant thread */
        add_action( 'wp_ajax_mdp_helper_create_thread', [$this, 'create_assistant_thread'] );
        add_action( 'wp_ajax_nopriv_mdp_helper_create_thread', [$this, 'create_assistant_thread'] );

        /** Delete assistant thread */
        add_action( 'wp_ajax_mdp_helper_delete_thread', [$this, 'delete_assistant_thread'] );
        add_action( 'wp_ajax_nopriv_mdp_helper_delete_thread', [$this, 'delete_assistant_thread'] );

        /** Updates post ids control */
        add_action( 'wp_ajax_mdp_helper_get_posts', [$this, 'update_post_ids_control'] );

        /** Get selected tts voices */
        add_action( 'wp_ajax_mdp_helper_get_tts_voices', [$this, 'get_tts_voices'] );

        /** Updates live preview option */
        add_action( 'wp_ajax_mdp_helper_update_preview_option', [ $this, 'update_preview_option' ] );


    }



    /** Check if preview mode */
    private function check_preview_mode() {
        $referer = wp_get_referer();
        $query_string = parse_url( $referer, PHP_URL_QUERY );
        parse_str( $query_string, $query_vars );
        return isset( $query_vars['mdp-helper-preview'] );
    }

    private function sanitize_array( $values ) {
        $sanitized_values = [];

        foreach ( $values as $value ) {
            $sanitized_values[] = sanitize_text_field( $value );
        }

        return $sanitized_values;
    }

    /** Update preview option */
    public function update_preview_option() {
        check_ajax_referer( 'helper-unity', 'mdp_helper_nonce' );

        $preview_settings = get_option( 'mdp_helper_preview_settings' );

        /** Exit if there is no preview settings */
        if ( empty( $preview_settings ) ) { return; }

        $field_name = sanitize_text_field( $_POST['mdp_helper_preview_setting_name'] );
        $field_value = !empty( $_POST['mdp_helper_preview_setting_value'] ) ?
            json_decode( stripslashes( $_POST['mdp_helper_preview_setting_value'] ) ) :
            "";

        /** Exit if there is no field name ori field value */
        if ( empty( $field_name ) ) { return; }

        $preview_settings[$field_name] = is_array( $field_value ) ?
            $this->sanitize_array( $field_value ) :
            wp_kses_post( $field_value );
        update_option( 'mdp_helper_preview_settings', $preview_settings );

    }


    /**
     * Creates new log.
     *
     * @since 1.0.0
     * @access public
     **/
    public function create_new_log() {
        if ( empty( $_POST['mdp_helper_nonce'] ) ||
            ! wp_verify_nonce( $_POST['mdp_helper_nonce'], 'mdp-helper-nonce' ) ) { return; }

        $is_preview = $this->check_preview_mode();
        $options = Settings::get_instance()->options;
        $hash = bin2hex( random_bytes( 5 ) );

        /** Exit if preview mode */
        if ( $is_preview ) { return; }

        /** Exit if feature turned off */
        if ( $options['bot_logs'] !== 'on' ) { return; }

        $args = [];
        $args['post_type'] = 'mdp_bot_logs_cpt';
        $args['post_title'] = 'Bot log ' . $hash;
        $post_id = wp_insert_post( $args );

        wp_send_json_success( $post_id );
    }

    public function update_log() {
        if ( empty( $_POST['mdp_helper_nonce'] ) ||
            ! wp_verify_nonce( $_POST['mdp_helper_nonce'], 'mdp-helper-nonce' ) ) { return; }

        $options = Settings::get_instance()->options;
        $log_id = sanitize_text_field( $_POST['mdp_helper_log_id'] );
        $log_text = sanitize_text_field( $_POST['mdp_helper_log_text'] );
        $is_preview = $this->check_preview_mode();

        /** Exit if preview mode */
        if ( $is_preview ) { return; }

        /** Exit if feature turned off */
        if ( $options['bot_logs'] !== 'on' || empty( $log_id ) ) { return; }

        update_post_meta( $log_id, 'bot_dialog', $log_text  );

        wp_send_json_success();
    }

    /**
     * Returns updated data for posts control.
     *
     * @throws \Exception
     * @since 1.0.0
     * @access public
     **/
    public function update_post_ids_control() {
        check_ajax_referer( 'helper-unity', 'mdp_helper_nonce' );

        $post_types = sanitize_text_field( $_GET['mdp_helper_post_types'] );

        wp_send_json_success( [
            'selected_posts' => Settings::get_instance()->options['open_ai_post_id'],
            'posts' => Config::get_instance()->get_posts( explode( ',', $post_types ) ),
        ] );
    }

    /**
     * Returns updated data for posts control.
     *
     * @throws \Exception
     * @since 1.0.0
     * @access public
     **/
    public function get_tts_voices() {
        check_ajax_referer( 'helper-unity', 'mdp_helper_nonce' );
        wp_send_json_success( Settings::get_instance()->options['bot_tts_voice'] );
    }

    /**
     * @throws \Exception
     */
    public function create_assistant_thread() {
        if ( empty( $_POST['mdp_helper_nonce'] ) ||
            ! wp_verify_nonce( $_POST['mdp_helper_nonce'], 'mdp-helper-nonce' ) ) { return; }

        $sessionId = sanitize_text_field( $_POST['mdp_helper_session_id'] );
        if ( empty( $sessionId ) ) { return; }
        $thread_id = OpenAiBot::get_instance()->create_assistant_thread();
        if ( $thread_id ) {
            update_option( $sessionId, $thread_id );
            wp_send_json_success( [ 'created' => true ] );
        } else {
            wp_send_json_success( [ 'created' => false ] );
        }
    }

    /**
     * @throws \Exception
     */
    public function delete_assistant_thread() {
        if ( empty( $_POST['mdp_helper_nonce'] ) ||
            ! wp_verify_nonce( $_POST['mdp_helper_nonce'], 'mdp-helper-nonce' ) ) { return; }

        $sessionId = sanitize_text_field( $_POST['mdp_helper_session_id'] );
        if ( empty( $sessionId ) ) { return; }
        OpenAiBot::get_instance()->delete_assistant_thread( $sessionId );
        delete_option( $sessionId );
    }


    /**
     * Returns bot response according to inputed text.
     *
     * @throws \Exception
     * @since 1.0.0
     * @access public
     **/
    public function bot_response() {
        if ( empty( $_POST['mdp_helper_nonce'] ) ||
            ! wp_verify_nonce( $_POST['mdp_helper_nonce'], 'mdp-helper-nonce' ) ) { return; }
        $parsedown = new Parsedown();

        $options = Settings::get_instance()->options;
        $helper_hash = sanitize_text_field( $_POST['mdp_helper_hash'] );
        $session_id = sanitize_text_field( $_POST['mdp_helper_session_id'] );

        $visitor_message = sanitize_text_field( $_POST['visitor_message'] );

        $response = [];

        if ( $options['open_ai_set_user_requests_limit'] === 'on' ) {
            $user_ip = $_SERVER['REMOTE_ADDR'];
            $restriction_time = esc_attr( $options['open_ai_restriction_time'] );
            $user_hash = md5( $helper_hash . $user_ip );
            $user_requests = get_transient( $user_hash );
            $user_max_requests = esc_attr( $options['open_ai_restriction_requests'] );

            if ( (int)$user_requests >= (int)$user_max_requests ) {
                $response['message'] = esc_html( $options['open_ai_restriction_error_message'] );
                wp_send_json_success( $response );
                return;
            }

            if ( get_transient( $user_hash ) ) {
                set_transient( $user_hash, (int)$user_requests + 1 );
            } else {
                set_transient( $user_hash, 1, (int)$restriction_time * 60 );
            }

        }

        $current_bot_options = Caster::get_instance()->get_current_personality_options();

        if ( $current_bot_options['type'] === 'assistant' ) {
            $result = OpenAiBot::get_instance()->get_assistant_response( $visitor_message, $current_bot_options, $session_id );
        } else {
            $result = OpenAiBot::get_instance()->get_open_ai_bot_result( $visitor_message );
        }

        if ( filter_var( $result, FILTER_VALIDATE_URL ) ) {
            if ( url_to_postid( $result ) ) {
                $post_id = url_to_postid( $result );
                $post_excerpt = get_the_excerpt( $post_id );
				$post_thumbnail = get_the_post_thumbnail( $post_id );
                $link_widget_data = [
                    'post_title' => get_the_title( $post_id ),
                    'post_excerpt' => strlen( $post_excerpt ) > 50 ? substr( $post_excerpt, 0, 50 ) . '...' : $post_excerpt,
                    'thumbnail' => $post_thumbnail !== '' ? json_encode( get_the_post_thumbnail( $post_id ) ) : false,
                ];
                $response['message'] = $result;
                $response['link_widget_data'] = $link_widget_data;
            }
        } else {
            $pattern_link_with_title = '/\[([^\]]+)\]\((https?:\/\/[^\)]+)\)/';
            $pattern_simple_link = '/(http[s]{0,1}\:\/\/\S{4,})\s{0,}/ims';

            if ( preg_match( $pattern_link_with_title, $result ) ) {
                $result = preg_replace( $pattern_link_with_title, '<a href="$2" target="_blank">$1</a>', $result );
            } else {
                $result = preg_replace( $pattern_simple_link, '<a href="$1" target="_blank">$1</a> ', $result );
            }

            $response['message'] = wp_kses_post( $parsedown->text( $result ) );
        }

        wp_send_json_success( $response );

    }

    /**
     * Returns questions filtered by category.
     *
     * @since 1.0.0
     * @access public
     **/
    private function questions_by_category( $category, $is_preview ) {
        $options = $is_preview ? get_option( 'mdp_helper_preview_settings' ) : Settings::get_instance()->options;
        $category_questions = [];


        for ( $i = 0; $i < 70; $i++ ) {
            if ( empty( $options['question_' . $i] ) ) { continue; }
            if ( trim( $options['question_category_' . $i] ) === $category ) {
                $category_questions[] = [
                    'index' => $i,
                    'question' => esc_attr( $options['question_' . $i] )
                ];
            }
        }

        return $category_questions;
    }

    /**
     * Returns paginated questions.
     *
     * @since 1.0.0
     * @access private
     **/
    private function get_paginated_questions( $page, $questions, $is_preview ) {
        $options = $is_preview ? get_option( 'mdp_helper_preview_settings' ) : Settings::get_instance()->options;

        $paginated_questions = [];

        $max_count = count( $questions );
        $first_page_result = ( ( int )$page - 1 ) * ( int )$options['faq_pagination_count'];
        $last_page_result = $first_page_result + ( int )$options['faq_pagination_count'];
        $last_page_result = min( $last_page_result, $max_count );

        for ( $i = $first_page_result; $i < $last_page_result; $i++ ) {
            $paginated_questions[] = $questions[$i];
        }

        return [
            'questions' => $paginated_questions,
            'pages_count' => ceil( $max_count / (int)$options['faq_pagination_count'] ),
        ];
    }

    /**
     * Returns all faq questions without category.
     *
     * @since 1.0.0
     * @access private
     **/
    private function get_questions_without_category( $is_preview ) {
        $options = $is_preview ? get_option( 'mdp_helper_preview_settings' ) : Settings::get_instance()->options;
        $questions = [];

        for ( $i = 0; $i < 70; $i++ ) {
            if ( empty( $options['question_category_' . $i] ) ) {
                if ( !empty( $options['question_' . $i] ) ) {
                    $questions[] = [
                        'index' => $i,
                        'question' => esc_attr( $options['question_' . $i] )
                    ];
                }
            }
        }

        return $questions;
    }

    /**
     * Returns all faq questions by category.
     *
     * @since 1.0.0
     * @access private
     **/
    public function get_questions_by_category() {
        if ( empty( $_GET['mdp_helper_nonce'] ) ||
            ! wp_verify_nonce( $_GET['mdp_helper_nonce'], 'mdp-helper-nonce' ) ) { return; }
        $is_preview = $this->check_preview_mode();
        $options = $is_preview ? get_option( 'mdp_helper_preview_settings' ) : Settings::get_instance()->options;

        $category = sanitize_text_field( $_GET['category'] );
        $page = sanitize_text_field( $_GET['page'] );
        $is_paginated = $options['faq_pagination'] === 'on';

        $result = !$is_paginated ?
            [ 'questions' => $this->questions_by_category( $category, $is_preview ) ] :
            $this->get_paginated_questions( $page, $this->questions_by_category( $category, $is_preview ), $is_preview );


        wp_send_json_success( $result );

    }

    /**
     * Returns questions categories.
     *
     * @since 1.0.0
     * @access public
     **/
    public function get_categories( $is_preview ) {
        $options = $is_preview ? get_option( 'mdp_helper_preview_settings' ) : Settings::get_instance()->options;
        $categories = [];

        for ( $i = 0; $i < 70; $i++ ) {
            if ( !empty( $options['question_' . $i] ) ) {
                if ( $options['question_category_'.$i] ) {
                    $categories[] = $options['question_category_'.$i];
                }
            }
        }

        return $categories;
    }

    /**
     * Returns all faq questions.
     *
     * @since 1.0.0
     * @access public
     **/
    public function get_faq_questions() {
        if ( empty( $_GET['mdp_helper_nonce'] ) ||
            ! wp_verify_nonce( $_GET['mdp_helper_nonce'], 'mdp-helper-nonce' ) ) { return; }

        $is_preview = $this->check_preview_mode();

        $options = $is_preview ? get_option( 'mdp_helper_preview_settings' ) : Settings::get_instance()->options;
        $page = sanitize_text_field( $_GET['page'] );
        $is_paginated = $options['faq_pagination'] === 'on';
        $categories = array_unique( $this->get_categories( $is_preview ) );

        $result = !$is_paginated ?
            [
                'categories' => array_values( $categories ),
                'questions_data' => [ 'questions' => $this->get_questions_without_category( $is_preview ) ]
            ] :
            [
                'categories' => array_values( $categories ),
                'questions_data' => $this->get_paginated_questions(
                    $page,
                    $this->get_questions_without_category( $is_preview ),
                    $is_preview
                )
            ];


        wp_send_json_success( $result );
    }


    /**
     * Returns answer on faq question.
     *
     * @since 1.0.0
     * @access public
     **/
    public function get_bot_faq_answer() {
        if ( empty( $_GET['mdp_helper_nonce'] ) ||
            ! wp_verify_nonce( $_GET['mdp_helper_nonce'], 'mdp-helper-nonce' ) ) { return; }

        $is_preview = $this->check_preview_mode();
        $question_index = sanitize_text_field( $_GET['mdp_helper_question_index'] );
        $error_messages = Caster::get_instance()->get_all_repeater_data(
          70,
            'faq_bot_error_message_',
      false,
        false,
          '',
           '',
                      $is_preview
        );

        if ( $question_index === '' ) { wp_send_json_success( $error_messages[rand(0, count( $error_messages ) - 1)] ); }

        $options = $is_preview ? get_option( 'mdp_helper_preview_settings' ) : Settings::get_instance()->options;

        $answer = $options['question_answer_'.$question_index];

        $result = [
            'question' => esc_attr( $options['question_'.$question_index] ),
            'post' => url_to_postid( $answer )
        ];

        if ( filter_var( strip_tags( $answer ), FILTER_VALIDATE_URL ) ) {
            $answer = strip_tags( $answer );
            if ( url_to_postid( $answer ) ) {
                $post_id = url_to_postid( $answer );
                $post_excerpt = get_the_excerpt( $post_id );
	            $post_thumbnail = get_the_post_thumbnail( $post_id );
                $link_widget_data = [
                    'post_title' => get_the_title( $post_id ),
                    'post_excerpt' => strlen( $post_excerpt ) > 50 ? substr( $post_excerpt, 0, 50 ) . '...' : $post_excerpt,
                    'thumbnail' => $post_thumbnail !== '' ? json_encode( get_the_post_thumbnail( $post_id ) ) : false,
                ];
                $result['answer'] = $answer;
                $result['link_widget_data'] = $link_widget_data;
            } else {
                $result['answer'] = preg_replace('/(http[s]{0,1}\:\/\/\S{4,})\s{0,}/ims', '<a href="$1" target="_blank">$1</a> ', $answer );
            }
        } else {
            global $wp_embed;
            $answer = wp_kses_post( $answer );
            $answer = Caster::get_instance()->filter_embed_shortcode_urls( $answer );
            $answer = $wp_embed->run_shortcode( $answer );
            $answer = preg_replace_callback( "/". get_shortcode_regex( ['video', 'audio'] ) ."/", 'do_shortcode_tag', $answer );
            $result['answer'] = $answer;
        }

        wp_send_json_success( $result );

    }

    /**
     * Returns answer bot dialog message.
     *
     * @since 1.0.0
     * @access public
     **/
    public function get_dialog_message() {
        if ( empty( $_GET['mdp_helper_nonce'] ) ||
            ! wp_verify_nonce( $_GET['mdp_helper_nonce'], 'mdp-helper-nonce' ) ) { return; }

        $type = sanitize_text_field( $_GET['mdp_helper_messages_type'] );
        $message_menu = sanitize_text_field( $_GET['mdp_helper_message_menu'] );

        $is_preview = $this->check_preview_mode();

        $messages = !isset( $_GET['mdp_helper_with_conditions'] ) ?
            Caster::get_instance()->get_all_repeater_data(
              20,
                $message_menu . '_bot' . $type . '_message_',
          false,
            false,
             '',
              '',
                        $is_preview
            ) :
            Caster::get_instance()->get_all_repeater_data(
              20,
                $message_menu . '_bot' . $type . '_message_',
          '',
            true,
                        sanitize_text_field( $_GET['mdp_post_id'] ),
                        $message_menu,
                        $is_preview
            );

        if ( empty( $messages ) ) { wp_send_json_error( '' ); }

        wp_send_json_success( $messages[rand(0, count( $messages ) - 1)] );
    }

    /**
     * Returns all collect data messages.
     *
     * @since 1.0.0
     * @access public
     **/
    public function get_collect_data_messages() {
        if ( empty( $_GET['mdp_helper_nonce'] ) ||
            ! wp_verify_nonce( $_GET['mdp_helper_nonce'], 'mdp-helper-nonce' ) ) { return; }
        $is_preview = $this->check_preview_mode();
        $options = $is_preview ? get_option( 'mdp_helper_preview_settings' ) : Settings::get_instance()->options;

        $collect_data_fields = [];

        $collect_data_fields_names = Caster::get_instance()->get_all_repeater_data(
            20,
            'field_name_',
      false,
        false,
          '',
           '',
                    $is_preview
        );

        foreach ( $collect_data_fields_names as $index => $fields_name ) {
            if ( empty( $fields_name ) ) { continue; }

            $collect_data_fields[$fields_name] = [
                'message' => esc_attr( $options['collect_data_bot_message_'.$index] ),
                'validation' => esc_attr( $options['collect_data_field_validation_' . $index] ),
                'validation_error' => esc_attr( $options['collect_data_validation_error_' . $index] ),
                'validation_custom_regex' => esc_attr( $options['collect_data_custom_regex_' . $index] ),
                'min_number' => esc_attr( $options['collect_data_number_min_'.$index] ),
                'max_number' => esc_attr( $options['collect_data_number_max_'.$index] )
            ];

        }

        wp_send_json_success( $collect_data_fields );

    }

    /**
     * Send user data to email.
     *
     * @since 1.0.0
     * @access public
     **/
    private function send_email( $reply, $to, $email_content ) {

		if ( empty( $to ) ) { return; }

		// Set email subject
	    $subject = 'Message from ' . get_bloginfo( 'name' );

		// Set email headers
        $headers =
	        'From: Helper - ' . esc_html( get_bloginfo( 'blogname' ) ) . ' <' . get_bloginfo( 'admin_email' ) . '>' . "\r\n" .
            'Content-Type: text/plain; charset=UTF-8' . "\r\n";

		// Add reply-to header if set
		if ( $reply !== '') {
			$headers .= 'Reply-To:' . $reply . "\r\n";
		}

		$message =
			$email_content . "\r\n" .
			'---' . "\r\n" .
			esc_html__( 'You are being notified automatically about a message sent from your website ', 'helper' ) . esc_html( get_bloginfo( 'blogname' ) ) . "\r\n";

        if ( !filter_var( $to, FILTER_VALIDATE_EMAIL ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                wp_send_json_error( esc_html__( 'Failed to send!', 'helper' ) );
            } else {
                wp_send_json_error( esc_html__( 'Failed to send email!', 'helper') );
            }
        }

        wp_mail( $to, $subject, $message, $headers );

    }

    /**
     * Actions with data that were collected from user.
     *
     * @since 1.0.0
     * @access public
     **/
    public function collect_user_data_actions() {
        if ( empty( $_POST['mdp_helper_nonce'] ) ||
            ! wp_verify_nonce( $_POST['mdp_helper_nonce'], 'mdp-helper-nonce' ) ) { return; }

        $is_preview = $this->check_preview_mode();

        $options = $is_preview ? get_option( 'mdp_helper_preview_settings' ) : Settings::get_instance()->options;
        $email_content = '';
        $date = date('l jS \of F Y h:i:s A');

        /** Exit if feature turned off */
        if ( !in_array( 'collect_data', $options['bot_features'] ) ) { return; }

        $user_data = json_decode( stripslashes( $_POST['mdp_collected_data'] ), true );
        $success_messages = Caster::get_instance()->get_all_repeater_data( 20, 'collect_data_bot_success_message_' );
        $error_messages = Caster::get_instance()->get_all_repeater_data( 20, 'collect_data_bot_error_message_' );

        if ( empty( $user_data ) ) { wp_send_json_error( $error_messages[rand(0, count( $error_messages ) - 1)] ); }

        /** Save data to db */
        if ( !empty( $user_data ) && !$is_preview ) {
            if ( $options['save_user_data'] === 'on' ) {
                $args = [];
                $args['post_type'] = 'mdp_user_data_cpt';
                $args['post_title'] = 'User record ' . $date;
                $post_id = wp_insert_post($args);
            }

            foreach ( $user_data as $field_name => $user_datum ) {
                if ( $options['save_user_data'] === 'on' ) {
                    add_post_meta( $post_id, $field_name, $user_datum );
                }

                $email_content .= $field_name . ': ' . $user_datum . "\n";
            }

            if ( $options['send_user_data_email'] === 'on' ) {
                $this->send_email( '', $options['send_to_email'], $email_content );
            }
        }

        wp_send_json_success( $success_messages[rand(0, count( $success_messages ) - 1)] );

    }

    /**
     * Send user email.
     *
     * @since 1.0.0
     * @access public
     **/
    public function send_user_email() {
        if ( empty( $_POST['mdp_helper_nonce'] ) ||
            ! wp_verify_nonce( $_POST['mdp_helper_nonce'], 'mdp-helper-nonce' ) ) { return; }

        $is_preview = $this->check_preview_mode();

        $options = $is_preview ? get_option( 'mdp_helper_preview_settings' ) : Settings::get_instance()->options;

        /** Exit if feature turned off */
        if ( !in_array( 'get_user_email', $options['bot_features'] ) ) { return; }

        $user_data = json_decode( stripslashes( $_POST['mdp_user_email'] ), true );
        $success_messages = Caster::get_instance()->get_all_repeater_data( 20, 'get_emails_bot_success_message_' );
        $error_messages = Caster::get_instance()->get_all_repeater_data( 20, 'get_emails_bot_error_message_' );

        if ( empty( $user_data ) ) { wp_send_json_error( $error_messages[rand(0, count( $error_messages ) - 1)] ); }

        $emails = explode( ',', $options['send_to_emails'] );

        foreach ( $emails as $email ) {
            if ( !$is_preview ) {
                $this->send_email( $user_data['email'], trim( $email ), sanitize_text_field( $user_data['message'] ) );
            }
        }


        wp_send_json_success( $success_messages[rand(0, count( $success_messages ) - 1)] );

    }

    /**
     * Main AjaxActions Instance.
     * Insures that only one instance of AjaxAction exists in memory at any one time.
     *
     * @static
     * @since 1.0.0
     * @access public
     *
     * @return AjaxActions
     **/
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

            self::$instance = new self;

        }

        return self::$instance;

    }
}

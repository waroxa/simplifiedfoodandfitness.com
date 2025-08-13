<?php
namespace Merkulove\Helper;

use Merkulove\Helper\Unity\Helper;
use Merkulove\Helper\Unity\Plugin;
use Merkulove\Helper\Unity\Settings;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}

/**
 * SINGLETON: Class adds admin scripts.
 *
 * @since 1.0.0
 *
 **/
final class FrontScripts {
    /**
     * The one true FrontScripts.
     *
     * @var FrontScripts
     * @since 1.0.0
     **/
    private static $instance;

    /**
     * Additional script options
     *
     * @var FrontScripts
     * @since 1.0.0
     **/
    private static $script_options = [];

    /**
     * Sets up a new FrontScripts instance.
     *
     * @since 1.0.0
     * @access public
     **/
    private function __construct() {

        /** Add plugin scripts. */
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

    }

    /**
     * Set additional script options
     *
     * @return void
     * @since 1.0.0
     **/
    public function set_script_options( $options ) {
        self::$script_options = $options;
    }

    /**
     * Add plugin scripts.
     *
     * @return void
     * @since 1.0.0
     **/
    public function enqueue_scripts() {

        if ( isset( $_GET['mdp-helper-preview'] ) && !current_user_can( 'manage_options' ) ) {
            return;
        }

        $options = isset( $_GET['mdp-helper-preview'] ) && current_user_can( 'manage_options' ) ? get_option( 'mdp_helper_preview_settings' ) : Settings::get_instance()->options;
        $collect_data_enabled = in_array( 'collect_data', $options['bot_features'] );
        $send_message_path = wp_get_upload_dir()['basedir'] . '/helper/send_message_sound/' . esc_attr( $options['send_message_sound'] );
        $receive_message_path = wp_get_upload_dir()['basedir'] . '/helper/receive_message_sound/' . esc_attr( $options['receive_message_sound'] );

        if ( $collect_data_enabled && $options['enable_google_analytics'] === 'on' && $options['do_not_add_google_analytics_script'] !== 'on'  ) {

            wp_enqueue_script(
              'google-tag-manager',
                'https://www.googletagmanager.com/gtag/js?id=' . esc_attr( $options['google_tracking_id'] ),
                     [],
                 null
            );

        }

		wp_enqueue_script(
			'mdp-helper',
			Plugin::get_url() . 'js/helper' . Plugin::get_suffix() . '.js',
			[],
			Plugin::get_version(),
			true
		);

		$translations = [
			'botCommandsButtonTitle' => esc_html__( 'Bot commands', 'helper' ),
			'botCommands' => [
				'title' => esc_html__( 'Type and send message', 'helper' ),
				'return' => esc_html__( 'Get back to main menu', 'helper' ),
				'info' => esc_html__( 'Show info about bot commands.', 'helper' ),
                'copy' => esc_html__( 'Copy to Clipboard', 'helper' ),
                'successfullyCopied' => esc_html__( 'Text copied!', 'helper' )
			],
			'pagination' => [
				'next' => esc_html__( 'Next', 'helper' ),
				'prev' => esc_html__( 'Prev', 'helper' )
			],
            'tryAgainMessage' => esc_html__( 'Is there anything else I can help you with?', 'helper' ),
			'backToStartMenuButton' => esc_html__( 'Back to start menu', 'helper' ),
			'returnFaqButton' => esc_html__( 'Return to FAQ questions', 'helper' ),
			'readMoreText' => esc_html__( 'Read more...', 'helper' ),
			'learnMoreText' => esc_html__( 'Learn more from', 'helper' ),
			'incorrectEmailMessage' => esc_html__( 'Please provide correct email!', 'helper' ),
			'on' => esc_html__( 'On', 'helper' ),
			'off' => esc_html__( 'Off', 'helper' ),
			'stopBotAnimationBtnTitle' => esc_html__( 'Stop message typing', 'helper' ),
			'recognitionNotSupported' => esc_html__( 'WebSpeech recognition is not available for this browser', 'helper' ),
			'recognitionError' => esc_html__( 'Recognition error', 'helper' ),
		];

        $script_options = array_merge( [
            'nonce' => wp_create_nonce( 'mdp-helper-nonce' ),
            'endpoint' => admin_url( 'admin-ajax.php' ),
            'currentPost' => get_the_ID(),
            'enabledGoogleAnalytics' => $collect_data_enabled ? esc_attr( $options['enable_google_analytics'] ) : '',
            'botFeatures' => $options['bot_features'],
            'fullSizeOnMobile' => $options['bot_container_mobile_full_width'] === 'on',
            'faqButtonName' => !empty( $options['faq_button_name'] ) ? esc_html__( $options['faq_button_name'], 'helper' ) : '',
            'collectDataButtonName' => !empty( $options['collect_data_button_name'] ) ? esc_html__( $options['collect_data_button_name'], 'helper' ) : '',
            'sendEmailButton' => !empty( $options['get_emails_button_name'] ) ? esc_html__( $options['get_emails_button_name'], 'helper' ) : '',
            'botRespondDelay' => $options[ 'bot_respond_delay_enabled' ] === 'on' ? esc_attr( (float)$options['bot_respond_delay'] * 1000 ) : 0,
            'botRespondDelayEnabled' => $options[ 'bot_respond_delay_enabled' ] === 'on',
            'randomizeRespondDelay' => $options[ 'bot_respond_delay_randomize' ] === 'on',
            'botMessagePreloader' => esc_attr( $options['bot_message_preloader'] ),
            'botTypingAnimation' => esc_attr( $options['bot_typing_animation'] ),
            'botTypingAnimationDelay' => esc_attr( $options['bot_typing_animation_delay'] ),
            'openChatWithButton' => esc_attr( $options['open_bot_with_button'] ),
            'openPopupWithoutButton' => esc_attr( $options['bot_container_open_popup_without_btn'] ),
            'pagination' => !empty( $options['faq_pagination'] ) ? $options['faq_pagination'] : '',
            'botMemory' => $options[ 'bot_memory_enabled' ] === 'on',
            'localStorageHours' => $options['question_answer_local_storage_time'],
            'returnButtonIcon' => Helper::get_instance()->get_inline_svg( $options[ 'upper_line_return_to_menu_icon' ] ),
            'copyBotTextButtonIcon' => Helper::get_instance()->get_inline_svg( $options[ 'upper_line_copy_button_icon' ] ),
            'faqCategoryIcon' => !empty( $options['show_faq_category_icon'] ) && $options['show_faq_category_icon'] === 'on' ?
                Helper::get_instance()->get_inline_svg( $options['faq_category_icon'] ) : '',
            'maxPopupHeight' => $options['bot_container_max_height'] === 'on',
            'autoOpenPopup' => $options['bot_container_enable_auto_open'] === 'on',
            'autoOpenPopupDelay' => $options['bot_container_open_delay'],
            'limitUsersRequests' => isset( $options['open_ai_set_user_requests_limit'] ) ? esc_attr( $options['open_ai_set_user_requests_limit'] ) : '',
            'botLogs' => esc_attr( $options['bot_logs'] ),
            'enableMoreHelpMessage' => esc_attr( $options['enable_is_relevant'] ) === 'on',
            'acceptanceCheckBox' => $collect_data_enabled ? esc_attr( $options['show_acceptance_checkbox'] ) : '',
            'confirmationAcceptanceText' => $collect_data_enabled ? esc_html( $options['confirmation_terms_text'] ) : '',
            'moreHelpConfirmButtonText' => esc_html( $options['is_relevant_yes_text'] ),
            'moreHelpDeclineButtonText' => esc_html( $options['is_relevant_no_text'] ),
            'currentBotPersonalityType' => !empty( Caster::get_instance()->get_current_personality_options() ) ?
                Caster::get_instance()->get_current_personality_options()['type'] : '',
            'showMessagePreloader' => $options['bot_onload_message_preloader'] === 'on',
            'sendMessageAudio' => !empty( $options['send_message_sound'] ) && file_exists( $send_message_path ) ?
                wp_get_upload_dir()['baseurl'] . '/helper/send_message_sound/' . esc_attr( $options['send_message_sound'] ) :
                '',
            'receiveMessageAudio' => !empty( $options['receive_message_sound'] ) && file_exists( $receive_message_path ) ?
                wp_get_upload_dir()['baseurl'] . '/helper/receive_message_sound/' . esc_attr( $options['receive_message_sound'] ) :
                '',
            'translations' => apply_filters( 'mdp_helper_front_end_translations', $translations ),
            'avatar' => [
                'bot' => $this->get_avatar( 'bot', $options ),
                'user' => $this->get_avatar( 'user', $options ),
            ],
            'tts' => [
                'enabled' => $options['bot_tts'] === 'on',
                'multilingual' => $options[ 'bot_tts_multilingual' ] === 'on',
                'voice' => is_array( $options['bot_tts_voice'] ) ? esc_attr( implode( ',', $options['bot_tts_voice'] ) ) : esc_attr( $options['bot_tts_voice'] ),
            ],
            'stt' => [
                'enabled' => $options['bot_stt'] === 'on',
                'autoSubmit' => $options['bot_stt_auto_submit'] === 'on',
            ],
            'messageSignature' => [
                'nameEnabled' => $options['upper_line_name_enabled'] === 'on',
                'timestampEnabled' => $options['upper_line_timestamp_enabled'] === 'on',
                'botName' => esc_html__( $options['upper_line_bot_name'], 'helper' ),
                'userName' => esc_html__( 'You', 'helper' ),
                'timestampFormat12' => $options['upper_line_timestamp_12_hours'] === 'on',
                'buttonsEnabled' => $options['upper_line_buttons_enabled'] === 'on',
                'copyBotTextButtonEnabled' => $options['upper_line_copy_button_enabled'] === 'on',
            ]
        ], self::$script_options );

        /** Pass some vars to JS. */
        wp_localize_script( 'mdp-helper', 'mdpHelper', $script_options );

    }

	/**
	 * Get avatar data.
	 *
	 * @param $key
	 * @param $options
	 *
	 * @return array
	 */
	private function get_avatar( $key, $options ): array {

		$avatar_type = $options[ $key . '_avatar' ];

		switch ( $avatar_type ) {

			case 'icon':

				$avatar_url = $options[ $key . '_avatar_icon' ];

				return array(
					'type' => esc_attr( $avatar_type ),
					'content' => Helper::get_instance()->get_inline_svg( $options[ $key . '_avatar_icon' ] ),
					'url' => Plugin::get_url() . 'images/mdc-icons/' . esc_attr( $avatar_url ),
					'alt' => esc_html__( 'Avatar icon', 'helper' ),
				);

			case 'image':

				$avatar_src = wp_get_attachment_image_src( $options[ $key . '_avatar_image' ] ?? 0 );
				$avatar_url = $avatar_src[ 0 ] ?? '';

				return array(
					'type' => esc_attr( $avatar_type ),
					'content' => wp_get_attachment_image( $options[ $key . '_avatar_image' ] ?? 0 ),
					'url' => esc_url( $avatar_url ),
					'alt' => esc_html__( 'Avatar image', 'helper' ),
				);

			case 'none':
			default:

				return array(
					'type' => esc_attr( $avatar_type ),
					'content' => '',
					'url' => '',
					'alt' => '',
				);

		}

	}

    /**
     * Main FrontScripts Instance.
     *
     * Insures that only one instance of FrontScripts exists in memory at any one time.
     *
     * @static
     * @return FrontScripts
     * @since 1.0.0
     **/
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof FrontScripts ) ) {

            self::$instance = new FrontScripts;

        }

        return self::$instance;

    }

}

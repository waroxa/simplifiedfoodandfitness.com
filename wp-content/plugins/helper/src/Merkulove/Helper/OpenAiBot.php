<?php
namespace Merkulove\Helper;

use Merkulove\Helper\Unity\Plugin;
use Merkulove\Helper\Unity\Settings;
use Orhanerday\OpenAi\OpenAi;
use Smalot\PdfParser\Parser;


/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}

require Plugin::get_path() . '/vendor/autoload.php';

/**
* SINGLETON: AjaxActions class contains ajax logic.
*
* @since 1.0.0
*
**/
final class OpenAiBot {

    /**
     * The one true AjaxActions.
     *
     * @since 1.0.0
     * @access private
     * @var OpenAiBot
     **/
    private static $instance;



    /**
     * Creates prompt based on post content.
     *
     * @return string
     * @since 1.0.0
     * @access private
     *
     */
    private function get_post_content() {
        $options = Settings::get_instance()->options;
        if ( empty( $options['open_ai_post_id'] ) ) { return ''; }

        $posts_content = '';

        $posts_ids = $options['open_ai_post_id'];

        foreach ( $posts_ids as $posts_id ) {
            $post_content = get_post( esc_attr( str_replace( 'post_', '', $posts_id ) ) )->post_content;
            $post_content = apply_filters( 'the_content', $post_content );
            $post_content = str_replace(']]>', ']]&gt;', $post_content );
            $posts_content .= strip_tags( $post_content );
            $posts_content .= ' ';
        }

        return $posts_content;

    }

    /**
     * Returns product categories
     *
     * @return string
     * @since 1.0.0
     * @access private
     */
    private function get_product_categories( $product_id ) {
        $product_cats = get_the_terms( $product_id, 'product_cat' );
        $categories = [];

        foreach ( $product_cats as $product_cat ) {
            $categories[] = $product_cat->name;
        }

        return implode( ', ', $categories );
    }

    /**
     * Returns prompt based on WooCommerce products
     *
     * @return string
     * @since 1.0.0
     * @access private
     */
    private function get_products_prompt() {
        if (  !in_array('woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            return '';
        }

        $products = wc_get_products( [ 'status' => 'publish', 'limit' => -1  ] );
        $product_attrs = Settings::get_instance()->options['open_ai_product_attrs'];


        $products_prompt = 'Products: ';

        if ( !empty( $products ) ) {
            foreach ( $products as $product ) {
                $product = $product->get_data();
                $product_cats = $this->get_product_categories( $product['id'] );
                $in_stock = $product['stock_status'] === 'instock' ? 'yes' : 'no';
                $on_sale = !empty( $product['sale_price'] ) ? 'yes' : 'no';
                $products_prompt .= sprintf(
                    '%s %s %s %s %s %s %s',
                    in_array( 'name', $product_attrs ) ? 'Name: ' . $product['name'] . ',' : '',
                            in_array( 'description', $product_attrs ) ? 'Description: ' . $product['description'] . ',' : '',
                            in_array( 'category', $product_attrs ) ? 'Category: ' . $product_cats . ',' : '',
                            in_array( 'price', $product_attrs ) ?
                              'Price: ' .  $product['price'] . get_woocommerce_currency_symbol() . ',' :
                              '',
                            in_array( 'in_stock', $product_attrs ) ? 'In stock: ' . $in_stock . ',' : '',
                            in_array( 'on_sale', $product_attrs ) ? 'On sale: ' . $on_sale . ',' : '',
                            in_array( 'link', $product_attrs ) ? 'Link: ' . get_permalink( $product['id'] ) : ''
                );
            }
        }

        return $products_prompt;
    }

    /**
     * Returns PDF file content
     *
     * @return string
     * @throws \Exception
     * @since 1.0.0
     * @access private
     */
    private function get_pdf_file_content() {
        $options = Settings::get_instance()->options;
        $file = esc_attr( $options['open_ai_pdf_file'] );
        $text = '';

        $parser = new Parser();
        if ( !empty( $file ) ) {
            $pdf = $parser->parseFile( wp_get_upload_dir()['baseurl'] . '/helper/open_ai_pdf_file/' . $file );
            $text = wp_kses_post( $pdf->getText() );
        }

        return $text;
    }

    /**
     * Creates Open Ai object
     *
     * @return OpenAi
     * @throws \Exception
     * @since 1.0.0
     * @access private
     */
    private function create_open_ai_client() {
        $options = Settings::get_instance()->options;

        $enabled_additional_keys = !empty( $options['open_ai_add_additional_keys'] ) &&
            $options['open_ai_add_additional_keys'] === 'on';
        $open_ai_keys = Caster::get_instance()->get_all_repeater_data( 10, 'open_ai_api_key_' );

        /** If keys not provided return */
        if ( empty( $options['open_ai_api_key'] ) ) { return null; }

        if ( $enabled_additional_keys && !empty( $open_ai_keys ) ) {
            $open_ai_keys[] = $options['open_ai_api_key'];
        }

        $open_ai_key = $enabled_additional_keys && !empty( $open_ai_keys ) ?
            esc_attr( $open_ai_keys[rand( 0, count( $open_ai_keys ) - 1 ) ] ) :
            esc_attr( $options['open_ai_api_key'] );

        return new OpenAi( $open_ai_key );
    }

    /**
     * Return respond from Open AI
     *
     * @return string
     * @throws \Exception
     * @since 1.0.0
     * @access private
     */
    public function get_open_ai_bot_result( $question ) {
        $options = Settings::get_instance()->options;
        $model = esc_attr( $options['open_ai_model'] );

        $open_ai = $this->create_open_ai_client();

        $chat_completions_models = [
            'gpt-3.5-turbo',
            'gpt-3.5-turbo-16k',
            'gpt-3.5-turbo-1106',
            'gpt-4',
            'gpt-4-0125-preview',
            'gpt-4-1106-preview',
            'gpt-4o',
            'gpt-4o-mini',
            'gpt-4-turbo',
        ];

        if ( empty( $open_ai ) ) {
            return current_user_can( 'editor' ) || current_user_can( 'administrator' ) ?
                esc_html__( 'Something went wrong! Please check your Open AI API key!' ) :
                esc_html__( 'Something went wrong. Try again later.', 'helper' );
        }

        $prompt = '';
        $objective = "\n\nObjective:" . esc_html( $options['open_ai_objective'] );
        $notice = "\n\nNotice:" . esc_html( $options['open_ai_notice'] );

        if ( in_array( 'custom', $options['open_ai_prompt_type'] ) ) {

            $description = esc_html( $options['open_ai_prompt'] );

            $prompt .= $description;

        }

        if ( in_array( 'woocommerce_products', $options['open_ai_prompt_type'] ) ) {
            $prompt .= $this->get_products_prompt();
        }

        if ( in_array( 'post_content', $options['open_ai_prompt_type'] )  ) {
            $prompt .= $this->get_post_content();
        }

        if ( in_array( 'pdf_file', $options['open_ai_prompt_type'] ) ) {
            $prompt .= $this->get_pdf_file_content();
        }

        $result_prompt = $prompt . "\n\n" . $objective . "\n\n" . $notice . "\n\nQuestion:" . $question . "\n\nAnswer:\n\n";

        $result_prompt = trim( preg_replace( '/\s\s+/', ' ', str_replace( "\n", " ", $result_prompt ) ) );

        if ( in_array( $model, $chat_completions_models ) ) {
            $complete = json_decode( $open_ai->chat( [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $result_prompt
                    ]
                ],
                'temperature' => (int)esc_attr( $options['open_ai_temperature'] ),
                'max_tokens' => (int)esc_attr( $options['open_ai_max_tokens'] ),
                'top_p' => 1,
                'frequency_penalty' => 0,
                'presence_penalty' => 0,
                'stop' => [
                    "\nNote:",
                    "\nQuestion:"
                ]
            ] ) );
        } else {
            $complete = json_decode( $open_ai->completion( [
                'model' => $model,
                'prompt' => $result_prompt,
                'temperature' => (int)esc_attr( $options['open_ai_temperature'] ),
                'max_tokens' => (int)esc_attr( $options['open_ai_max_tokens'] ),
                'top_p' => 1,
                'frequency_penalty' => 0,
                'presence_penalty' => 0,
                'stop' => [
                    "\nNote:",
                    "\nQuestion:"
                ]
            ] ) );
        }

        if ( isset( $complete->choices[0]->text ) && $model !== 'gpt-3.5-turbo' && $model !== 'gpt-3.5-turbo-16k' && $model !== 'gpt-3.5-turbo-1106' ) {
            $text = str_replace( "\\n", "\n", $complete->choices[0]->text );
        } elseif( isset( $complete->choices[0]->message->content ) ) {
            $text = str_replace( "\\n", "\n", $complete->choices[0]->message->content );
        } elseif ( isset( $complete->error->message ) ) {
            $text = current_user_can( 'editor' ) || current_user_can( 'administrator' ) ?
                $complete->error->message :
                esc_html__( 'Something went wrong. Try again later.', 'helper' );
        } else {
            $text = esc_html__( "Sorry, but I don't know how to answer that.", 'helper' );
        }


        return nl2br( $text );
    }

    /**
     * Returns list of created assistants
     *
     * @return array
     * @throws \Exception
     * @since 1.0.0
     * @access private
     */
    public function get_assistants_list() {
        $open_ai = $this->create_open_ai_client();

        if ( empty( $open_ai ) ) { return []; }

        return json_decode( $open_ai->listAssistants() )->data ?? [];
    }

    /**
     * Creates assistant thread
     *
     * @return array
     * @throws \Exception
     * @since 1.0.0
     * @access private
     */
    public function create_assistant_thread() {
        $options = Settings::get_instance()->options;
        $assistant_version = $options['ai_assistant_version'] === 'latest' ? 'v2' : 'v1';
        $open_ai = $this->create_open_ai_client();
        if ( empty( $open_ai ) ) { return ''; }
        return json_decode( $open_ai->createThread( [], $assistant_version ) )->id;
    }

    /**
     * Removes assistant thread
     *
     * @return bool|string
     * @throws \Exception
     * @since 1.0.0
     * @access private
     */
    public function delete_assistant_thread( $thread_id ) {
        $options = Settings::get_instance()->options;
        $assistant_version = $options['ai_assistant_version'] === 'latest' ? 'v2' : 'v1';
        $open_ai = $this->create_open_ai_client();
        if ( empty( $open_ai ) ) { return ''; }
        return $open_ai->deleteThread( $thread_id, $assistant_version );
    }

    /**
     * Filters assistant messages
     *
     * @return bool|string
     * @throws \Exception
     * @since 1.0.0
     * @access private
     */
    private function filter_thread_messages( $messages, $run_id ) {
        $filtered_messages = [];

        foreach ( $messages as $message ) {
            if ( $message->run_id === $run_id && $message->role === "assistant" ) {
                $filtered_messages[] = $message;
            }
        }

        return array_pop( $filtered_messages );
    }

    /**
     * Returns response from assistant
     *
     * @return string
     * @throws \Exception
     * @since 1.0.0
     * @access private
     */
    public function get_assistant_response( $message, $bot_options, $session_id ) {
        $options = Settings::get_instance()->options;
        $assistant_version = $options['ai_assistant_version'] === 'latest' ? 'v2' : 'v1';

        /** If keys not provided return */
        if ( empty( $options['open_ai_api_key'] ) ) {
            return current_user_can( 'editor' ) || current_user_can( 'administrator' ) ?
                esc_html__( 'Something went wrong! Please check your Open AI API key!' ) :
                esc_html__( 'Something went wrong. Try again later.', 'helper' );
        }

        $enabled_additional_keys = $options['open_ai_add_additional_keys'] === 'on';
        $open_ai_keys = Caster::get_instance()->get_all_repeater_data( 10, 'open_ai_api_key_' );

        $open_ai_key = $enabled_additional_keys && !empty( $open_ai_keys ) ?
            esc_attr( $open_ai_keys[rand( 0, count( $open_ai_keys ) - 1 ) ] ) :
            esc_attr( $options['open_ai_api_key'] );

        $open_ai = new OpenAi( $open_ai_key );

        $assistant_id = esc_attr( $bot_options['assistant'] );

        if ( empty( $assistant_id ) ) {
            return current_user_can( 'editor' ) || current_user_can( 'administrator' ) ?
                esc_html__( 'Something went wrong! Please select Open AI assistant!' ) :
                esc_html__( 'Something went wrong. Try again later.', 'helper' );
        }

        // Create Thread
        $thread_id = get_option( $session_id );

        if ( empty( $thread_id ) ) {
            return esc_html__( 'Something went wrong. Try again later.', 'helper' );
        }

        // Create thread message
        $open_ai->createThreadMessage( $thread_id, [
            'role' => 'user',
            'content' => $message
        ], $assistant_version );

        // Create run
        $run_json = $open_ai->createRun( $thread_id, [
            'assistant_id' => $assistant_id
        ], $assistant_version );

        $run_data = json_decode( $run_json );

        // Create Response
        $response = json_decode( $open_ai->retrieveRun( $thread_id, $run_data->id, $assistant_version ) );

        // Polling to retrieve run status
        while ( $response->status === 'in_progress' || $response->status === 'queued' ) {
            sleep( 5 );
            $response = json_decode( $open_ai->retrieveRun( $thread_id, $run_data->id, $assistant_version ) );
        }

        // Send error if response failed
        if ( $response->status === 'failed' ) {
            return current_user_can( 'editor' ) || current_user_can( 'administrator' ) ?
                $response->last_error->message :
                esc_html__( 'Something went wrong. Try again later.', 'helper' );
        }

        // Get messages list
        $messages_list = json_decode( $open_ai->listThreadMessages( $thread_id, [], $assistant_version ) );
        $messages = $messages_list->data;
        $result_message_object = $this->filter_thread_messages( $messages, $run_data->id );
        $result_message = $result_message_object->content[0]->text->value;
        $result_message = preg_replace( '/【.*?†.*?】/', '', $result_message );

        return nl2br( $result_message );
    }

    /**
     * Main Caster Instance.
     * Insures that only one instance of OpenAiBot exists in memory at any one time.
     *
     * @static
     * @since 1.0.0
     * @access public
     *
     * @return OpenAiBot
     **/
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

            self::$instance = new self;

        }

        return self::$instance;

    }
}

<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWP_Marketing' ) ) {

    /**
     * Model that houses the logic of integrating with WooCommerce orders.
     * Be it be adding additional data/meta to orders or order items, etc..
     *
     * @since 1.5.0
     */
    class WWP_Marketing {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWP_Marketing.
         *
         * @since 1.5.0
         * @access private
         * @var WWP_Marketing
         */
        private static $_instance;

        /**
         * Model that houses the logic of retrieving information relating to wholesale role/s of a user.
         *
         * @since 1.5.0
         * @access private
         * @var WWP_Wholesale_Roles
         */
        private $_wwp_wholesale_roles;




        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        /**
         * WWP_Marketing constructor.
         *
         * @since 1.5.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWP_Marketing model.
         */
        public function __construct( $dependencies ) {

            $this->_wwp_wholesale_roles  = $dependencies[ 'WWP_Wholesale_Roles' ];

        }

        /**
         * Ensure that only one instance of WWP_Marketing is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.5.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWP_Marketing model.
         * @return WWP_Marketing
         */
        public static function instance( $dependencies ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }

        /**
         * Print wwp tag.
         *
         * @since 1.5.0
         * @access public
         */
        public function print_wwp_tag() {

            echo '<meta name="wwp" content="yes" />';

        }

        /**
         * Flag to show review request.
         *
         * @since 3.0.0
         * @access public
         */
        public function flag_show_review_request() {

            update_option( WWP_SHOW_REQUEST_REVIEW , 'yes' );

        }
        
        /**
         * Ajax request review response.
         *
         * @since 1.5.0
         * @access public
         */
        public function ajax_request_review_response() {

            if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX )
                $response = array( 'status' => 'fail' , 'error_msg' => __( 'Invalid AJAX call' , 'woocommerce-wholesale-prices' ) );
            elseif ( !isset( $_POST[ 'review_request_response' ] ) )
                $response = array( 'status' => 'fail' , 'error_msg' => __( 'Required parameter not passed' , 'woocommerce-wholesale-prices' ) );
            else {

                update_option( WWP_REVIEW_REQUEST_RESPONSE , $_POST[ 'review_request_response' ] );
            
                if ( $_POST[ 'review_request_response' ] === 'review-later' )
                    wp_schedule_single_event( time() + 1209600 , WWP_CRON_REQUEST_REVIEW );

                delete_option( WWP_SHOW_REQUEST_REVIEW );

                $response = array( 'status' => 'success' , 'success_msg' => __( 'Review request response saved' , 'woocommerce-wholesale-prices' ) );

            }

            @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
            echo wp_json_encode( $response );
            wp_die();

        }

        /**
         * Register ajax handlers.
         *
         * @since 1.5.0
         * @access public
         */
        public function register_ajax_handlers() {

            add_action( 'wp_ajax_wwp_request_review_response' , array( $this , 'ajax_request_review_response' ) );

        }




        /**
         * Execute model.
         *
         * @since 1.5.0
         * @access public
         */
        public function run() {
            
            add_action( 'wp_head' , array( $this , 'print_wwp_tag' ) );

            add_action( WWP_CRON_REQUEST_REVIEW , array( $this , 'flag_show_review_request' ) );

            add_action( 'init' , array( $this , 'register_ajax_handlers' ) );

        }

    }

}

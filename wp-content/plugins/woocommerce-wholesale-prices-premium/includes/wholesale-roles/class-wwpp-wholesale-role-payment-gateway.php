<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_Wholesale_Role_Payment_Gateway' ) ) {

    /**
     * Model that houses the logic of payment gateways.
     *
     * @since 1.3.0
     */
    class WWPP_Wholesale_Role_Payment_Gateway {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWPP_Wholesale_Role_Payment_Gateway.
         *
         * @since 1.3.0
         * @access private
         * @var WWPP_Wholesale_Role_Payment_Gateway
         */
        private static $_instance;

        /**
         * Model that houses the logic of retrieving information relating to wholesale role/s of a user.
         *
         * @since 1.3.0
         * @access private
         * @var WWPP_Wholesale_Roles
         */
        private $_wwpp_wholesale_roles;




        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        /**
         * WWPP_Wholesale_Role_Payment_Gateway constructor.
         *
         * @since 1.3.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Wholesale_Role_Payment_Gateway model.
         */
        public function __construct( $dependencies ) {

            $this->_wwpp_wholesale_roles = $dependencies[ 'WWPP_Wholesale_Roles' ];

        }

        /**
         * Ensure that only one instance of WWPP_Wholesale_Role_Payment_Gateway is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.13.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Wholesale_Role_Payment_Gateway model.
         * @return WWPP_Wholesale_Role_Payment_Gateway
         */
        public static function instance( $dependencies ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }
        
        /**
         * Apply custom payment gateway surcharge.
         *
         * @since 1.3.0
         * @since 1.14.0 Refactored codebase.
         * @since 1.16.0 Support per wholesale user payment gateway surcharge override.
         * @access public
         * 
         * @param WC_Cart $wc_cart Cart object.
         */
        public function apply_payment_gateway_surcharge( $wc_cart ) {

            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();

            if ( is_admin() && ! defined( 'DOING_AJAX' ) )
                return;
            
            if ( empty( $user_wholesale_role ) )
                return;

            $user_id = get_current_user_id();

            switch ( get_user_meta( $user_id , 'wwpp_override_payment_gateway_surcharge' , true ) ) {

                case 'specify_surcharge_mapping':
                    $payment_gateway_surcharge = get_user_meta( $user_id , 'wwpp_payment_gateway_surcharge_mapping' , true );
                    break;

                case 'do_not_use_general_surcharge_mapping':
                    $payment_gateway_surcharge = array();
                    break;

                case 'use_general_surcharge_mapping':
                default:
                    $payment_gateway_surcharge = get_option( WWPP_OPTION_PAYMENT_GATEWAY_SURCHARGE_MAPPING );
                    break;

            }

            if ( !is_array( $payment_gateway_surcharge ) )
                $payment_gateway_surcharge = array();

            if ( empty( $payment_gateway_surcharge ) )
                return;

            $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
            if ( !is_array( $available_gateways ) )
                $available_gateways = array();

            if ( !empty( $available_gateways ) ) {

                // Chosen Method
                if ( isset( WC()->session->chosen_payment_method ) && isset( $available_gateways[ WC()->session->chosen_payment_method ] ) )
                    $current_gateway = $available_gateways[ WC()->session->chosen_payment_method ];
                elseif ( isset( $available_gateways[ get_option( 'woocommerce_default_gateway' ) ] ) )
                    $current_gateway = $available_gateways[ get_option( 'woocommerce_default_gateway' ) ];
                else
                    $current_gateway =  current( $available_gateways );

                foreach ( $payment_gateway_surcharge as $mapping ) {

                    if ( $mapping[ 'wholesale_role' ] == $user_wholesale_role[ 0 ] && $mapping[ 'payment_gateway' ] == $current_gateway->id ) {

                        if ( $mapping[ 'surcharge_type' ] == 'percentage' )
                            $surcharge = round( ( ( WC()->cart->cart_contents_total + WC()->cart->shipping_total ) * $mapping[ 'surcharge_amount' ] ) / 100 , 2 );
                        else
                            $surcharge = $mapping[ 'surcharge_amount' ];

                        $taxable = ( $mapping[ 'taxable' ] == 'yes' ) ? true : false;

                        WC()->cart->add_fee( $mapping[ 'surcharge_title' ] , $surcharge , $taxable , '' );

                    }

                }

            }

        }

        /**
         * Apply taxable notice to surcharge.
         *
         * @since 1.3.0
         * @since 1.14.0 Refactor codebase.
         * 
         * @param string $cart_totals_fee_html Price html.
         * @param object $fee                  Fee object.
         * @return string Filtered price html.
         */
        public function apply_taxable_notice_on_surcharge( $cart_totals_fee_html , $fee ) {

            if ( $fee->taxable )
                $cart_totals_fee_html .= ' <small>' . WC()->countries->inc_tax_or_vat() . '</small>';

            return $cart_totals_fee_html;

        }

        /**
         * Get wholesale user payment gateway.
         * 
         * @since 1.16.0
         * @access public
         * 
         * @param int    $user_id             User id.
         * @param string $user_wholesale_role User wholesale role.
         * @return array Array of wholesale user payment gateways.
         */
        public function get_wholesale_user_payment_gateways( $user_id , $user_wholesale_role ) {

            $payment_gateways = array();

            $wholesale_role_payment_gateway_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_PAYMENT_GATEWAY_MAPPING );
            if ( !is_array( $wholesale_role_payment_gateway_mapping ) )
                $wholesale_role_payment_gateway_mapping = array();

            if ( array_key_exists( $user_wholesale_role , $wholesale_role_payment_gateway_mapping ) )
                $payment_gateways = array_map( function( $item ) { return $item[ 'id' ]; } , $wholesale_role_payment_gateway_mapping[ $user_wholesale_role ] );

            if ( get_user_meta( $user_id , 'wwpp_override_payment_gateway_options' , true ) === 'yes' ) {

                $pg = get_user_meta( $user_id , 'wwpp_payment_gateway_options' , true );

                if ( !empty( $pg ) && is_array( $pg ) )
                    $payment_gateways = $pg;

            }

            return $payment_gateways;

        }

        /**
         * Filter payment gateway to be available to certain wholesale role.
         * Note: payment gateway not need to be enabled.
         *
         * @since 1.3.0
         * @since 1.14.0 Refactor codebase.
         * @since 1.16.0 Refactor codebase and add support for per wholesale user payment gateway options override.
         *
         * @param array $available_gateways Array of available gateways.
         * @return array Filtered array of avaialble gateways.
         */
        public function filter_available_payment_gateways( $available_gateways ) {

            $user_id             = get_current_user_id();
            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();

            if ( current_user_can( 'manage_options' ) || empty( $user_wholesale_role ) )
                return $available_gateways;

            $user_payment_gateways = $this->get_wholesale_user_payment_gateways( $user_id , $user_wholesale_role[ 0 ] );

            if ( empty( $user_payment_gateways ) )
                return $available_gateways;

            $all_payment_gateways = WC()->payment_gateways->payment_gateways();
            $filtered_gateways  = array();

            foreach ( $all_payment_gateways as $gateway )
                if ( in_array( $gateway->id , $user_payment_gateways ) )
                    $filtered_gateways[ $gateway->id ] = $gateway;

            if ( !empty( $filtered_gateways ) ) {

                WC()->payment_gateways()->set_current_gateway( $filtered_gateways );
                
                return $filtered_gateways;

            } else
                return $available_gateways;

        }

        /**
         * Everytime third party plugins sets a payment token as default, we negate that effect.
         * 
         * @since 1.10.1
         * @since 1.14.0 Refactor codebase.
         * @access public
         * 
         * @param int    $token_id Token id.
         * @param object $token    Token object.
         */
        public function undefault_payment_token( $token_id , $token ) {

            if ( self::current_user_has_role_payment_gateway_surcharge_mapping() ) {

                global $wpdb;
                
                $token->set_default( false );

                $wpdb->update(
                    $wpdb->prefix . 'woocommerce_payment_tokens',
                    array( 'is_default' => 0 ),
                    array( 'token_id' => $token->get_id(),
                ) );

            }

        }

        /**
         * Set all existing payment tokens as not default.
         * 
         * @since 1.10.1
         * @since 1.14.0 Refactor codebase.
         * @access public
         */
        public function undefault_existing_payment_tokens() {

            if ( self::has_role_payment_gateway_surcharge_mapping() ) {

                global $wpdb;

                $wpdb->query( "UPDATE " . $wpdb->prefix . "woocommerce_payment_tokens SET is_default = 0" );

            }

        }

        /**
         * Check if current user has role payment gateway surecharge mapping.
         * 
         * @since 1.10.1
         * @since 1.14.0 Refactored codebase.
         * @access public
         * 
         * @return boolean
         */
        public static function current_user_has_role_payment_gateway_surcharge_mapping() {

            $payment_gateway_surcharge = get_option( WWPP_OPTION_PAYMENT_GATEWAY_SURCHARGE_MAPPING );
            if ( !is_array( $payment_gateway_surcharge ) )
                $payment_gateway_surcharge = array();
            
            $user                     = wp_get_current_user();
            $current_user_has_mapping = false;

            foreach ( $payment_gateway_surcharge as $mapping ) {

                if ( in_array( $mapping[ 'wholesale_role' ] , $user->roles ) ) {

                    $current_user_has_mapping = true;
                    break;

                }

            }

            return $current_user_has_mapping;

        }

        /**
         * Check if there is a role payment gateway surcharge mapping.
         * 
         * @since 1.10.1
         * @since 1.14.0 Refactor codebase.
         * @access public
         * 
         * @return boolean Flag that determines if role have payment surcharge mapping.
         */
        public static function has_role_payment_gateway_surcharge_mapping() {

            $payment_gateway_surcharge = get_option( WWPP_OPTION_PAYMENT_GATEWAY_SURCHARGE_MAPPING );
            if ( !is_array( $payment_gateway_surcharge ) )
                $payment_gateway_surcharge = array();

            return !empty( $payment_gateway_surcharge );

        }




        /*
        |---------------------------------------------------------------------------------------------------------------
        | AJAX Call Handlers
        |---------------------------------------------------------------------------------------------------------------
        */

        /**
         * Add wholesale role / payment gateway mapping.
         *
         * @since 1.3.0
         * @since 1.14.0 Refactor codebase.
         * @access public
         * 
         * @param null|array $mapping Role payment gateway mapping.
         * @return array Array containing data about status of the operation.
         */
        public function add_wholesale_role_payment_gateway_mapping( $mapping = null ) {

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
                $mapping = $_POST[ 'mapping' ];

            $wrpg_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_PAYMENT_GATEWAY_MAPPING );
            if ( !is_array( $wrpg_mapping ) )
                $wrpg_mapping = array();

            if ( array_key_exists( $mapping[ 'wholesale_role' ] , $wrpg_mapping ) ) {

                $response = array(
                                'status'        => 'fail',
                                'error_message' => __( 'Wholesale role you wish to add payment gateway mapping already exist' , 'woocommerce-wholesale-prices-premium' )
                            );

            } else {

                $wrpg_mapping[ $mapping[ 'wholesale_role' ] ] = $mapping[ 'payment_gateways' ];
                update_option( WWPP_OPTION_WHOLESALE_ROLE_PAYMENT_GATEWAY_MAPPING , $wrpg_mapping );
                $response = array( 'status' => 'success' );

            }

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

                @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
                echo wp_json_encode( $response );
                wp_die();

            } else
                return $response;
            
        }

        /**
         * Update wholesale role / payment gateway mapping.
         *
         * @since 1.3.0
         * @since 1.14.0 Refactor codebase.
         * @access public
         *
         * @param null|array $mapping Role payment gateway mapping.
         * @return array Array containing data about status of the operation.
         */
        public function update_wholesale_role_payment_gateway_mapping( $mapping = null ) {

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
                $mapping = $_POST[ 'mapping' ];

            $wrpg_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_PAYMENT_GATEWAY_MAPPING );
            if ( !is_array( $wrpg_mapping ) )
                $wrpg_mapping = array();

            if ( !array_key_exists( $mapping[ 'wholesale_role' ] , $wrpg_mapping ) ) {

                $response = array(
                                'status'        => 'fail',
                                'error_message' => __( 'Wholesale Role / Payment Gateway mapping you wish to edit does not exist on record' , 'woocommerce-wholesale-prices-premium' )
                            );

            } else {

                $wrpg_mapping[ $mapping[ 'wholesale_role' ] ] = $mapping[ 'payment_gateways' ];
                update_option( WWPP_OPTION_WHOLESALE_ROLE_PAYMENT_GATEWAY_MAPPING , $wrpg_mapping );
                $response = array( 'status' => 'success' );

            }

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

                @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
                echo wp_json_encode( $response );                
                wp_die();

            } else
                return $response;

        }

        /**
         * Delete wholesale role / payment gateway method.
         *
         * @since 1.3.0
         * @since 1.14.0 Refactor codebase.
         * @access public
         *
         * @param null|string $wholesale_role_key Wholesale role key.
         * @return array Array containing data about status of the operation.
         */
        public function delete_wholesale_role_payment_gateway_mapping( $wholesale_role_key = null ) {

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
                $wholesale_role_key = $_POST[ 'wholesaleRoleKey' ];

            $wrpg_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_PAYMENT_GATEWAY_MAPPING );
            if ( !is_array( $wrpg_mapping ) )
                $wrpg_mapping = array();

            if ( !array_key_exists( $wholesale_role_key , $wrpg_mapping ) ) {

                $response = array(
                    'status'        =>  'fail',
                    'error_message' =>  __( 'Wholesale Role / Payment Gateway mapping you wish to delete does not exist on record' , 'woocommerce-wholesale-prices-premium' )
                );

            } else {

                unset( $wrpg_mapping[ $wholesale_role_key ] );
                update_option( WWPP_OPTION_WHOLESALE_ROLE_PAYMENT_GATEWAY_MAPPING , $wrpg_mapping );
                $response = array( 'status' =>  'success' );

            }

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

                @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
                echo wp_json_encode( $response );                
                wp_die();

            } else
                return $response;

        }

        /**
         * Add payment gateway surcharge to a wholesale role.
         * $surchargeData parameter is expected to be an array with the keys below.
         *
         * wholesale_role
         * payment_gateway
         * surcharge_title
         * surcharge_type
         * surcharge_amount
         * taxable
         *
         * @since 1.3.0
         * @since 1.14.0 Refactor codebase.
         * @since 1.16.0 Support per wholesale user payment gateway surcharge override.
         * @access public
         *
         * @param null|array $surcharge_data Array of surcharge data.
         * @return array Array containing data about status of the operation.
         */
        public function add_payment_gateway_surcharge( $surcharge_data = null ) {

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
                $surcharge_data = $_POST[ 'surchargeData' ];

            $user_id = isset( $_POST[ 'user_id' ] ) ? $_POST[ 'user_id' ] : 0;

            $surcharge_mapping = $user_id ? get_user_meta( $user_id , 'wwpp_payment_gateway_surcharge_mapping' , true ) : get_option( WWPP_OPTION_PAYMENT_GATEWAY_SURCHARGE_MAPPING );            
            if ( !is_array( $surcharge_mapping ) )
                $surcharge_mapping = array();

            $surcharge_mapping[] = $surcharge_data;

            if ( $user_id )
                update_user_meta( $user_id , 'wwpp_payment_gateway_surcharge_mapping' , $surcharge_mapping );
            else
                update_option( WWPP_OPTION_PAYMENT_GATEWAY_SURCHARGE_MAPPING , $surcharge_mapping );

            $arr_keys     = array_keys( $surcharge_mapping );
            $latest_index = end( $arr_keys );

            $response = array(
                            'status'       => 'success',
                            'latest_index' => $latest_index
                        );

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

                @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
                echo wp_json_encode( $response );                
                wp_die();

            } else
                return $response;

        }

        /**
         * Update payment gateway surcharge for a wholesale role.
         *
         * @since 1.3.0
         * @since 1.14.0 Refactor codebase.
         * @since 1.16.0 Support per wholesale user payment gateway surcharge override.
         * @access public
         *
         * @param null|int   $idx            Mapping index.
         * @param null|array $surcharge_data Array of surcharge data.
         * @return array Array containing data about status of the operation.
         */
        public function update_payment_gateway_surcharge( $idx = null , $surcharge_data = null ) {

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

                $idx           = $_POST[ 'idx' ];
                $surcharge_data = $_POST[ 'surchargeData' ];

            }

            $user_id = isset( $_POST[ 'user_id' ] ) ? $_POST[ 'user_id' ] : 0;
            
            $surcharge_mapping = $user_id ? get_user_meta( $user_id , 'wwpp_payment_gateway_surcharge_mapping' , true ) : get_option( WWPP_OPTION_PAYMENT_GATEWAY_SURCHARGE_MAPPING );            
            if ( !is_array( $surcharge_mapping ) )
                $surcharge_mapping = array();

            if ( !array_key_exists( $idx , $surcharge_mapping ) ) {

                $response = array(
                                'status'        => 'fail',
                                'error_message' => __( 'Payment gateway surcharge mapping you wish to update does not exist on record' , 'woocommerce-wholesale-prices-premium' )
                            );

            } else {

                $surcharge_mapping[ $idx ] = $surcharge_data;

                if ( $user_id )
                    update_user_meta( $user_id , 'wwpp_payment_gateway_surcharge_mapping' , $surcharge_mapping );
                else
                    update_option( WWPP_OPTION_PAYMENT_GATEWAY_SURCHARGE_MAPPING , $surcharge_mapping );

                $response = array( 'status' => 'success' );

            }

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

                @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
                echo wp_json_encode( $response );                
                wp_die();

            } else
                return $response;

        }

        /**
         * Delete payment gateway surcharge of a wholesale user.
         *
         * @since 1.3.0
         * @since 1.14.0 Refactor codebase.
         * @since 1.16.0 Support per wholesale user payment gateway surcharge override.
         * @access public
         *
         * @param null|int $idx Mapping index.
         * @return array Array containing data about status of the operation.
         */
        public function delete_payment_gateway_surcharge( $idx = null ) {

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
                $idx = $_POST[ 'idx' ];

            $user_id = isset( $_POST[ 'user_id' ] ) ? $_POST[ 'user_id' ] : 0;
            
            $surcharge_mapping = $user_id ? get_user_meta( $user_id , 'wwpp_payment_gateway_surcharge_mapping' , true ) : get_option( WWPP_OPTION_PAYMENT_GATEWAY_SURCHARGE_MAPPING );            
            if ( !is_array( $surcharge_mapping ) )
                $surcharge_mapping = array();

            if ( !array_key_exists( $idx , $surcharge_mapping ) ) {

                $response = array(
                                'status'        => 'fail',
                                'error_message' => __( 'Payment gateway surcharge you want to delete does not exist on record' , 'woocommerce-wholesale-prices-premium' )
                            );

            } else {

                unset( $surcharge_mapping[ $idx ] );

                if ( $user_id )
                    update_user_meta( $user_id , 'wwpp_payment_gateway_surcharge_mapping' , $surcharge_mapping );
                else
                    update_option( WWPP_OPTION_PAYMENT_GATEWAY_SURCHARGE_MAPPING , $surcharge_mapping );

                $response = array( 'status' => 'success' );

            }

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

                @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
                echo wp_json_encode( $response );                
                wp_die();

            } else
                return $response;

        }




        /*
        |---------------------------------------------------------------------------------------------------------------
        | Execute model
        |---------------------------------------------------------------------------------------------------------------
        */
        
        /**
         * Register model ajax handlers.
         *
         * @since 1.14.0
         * @access public
         */
        public function register_ajax_handler() {

            // Wholesale role payment gateway mapping
            add_action( "wp_ajax_wwppAddWholesaleRolePaymentGatewayMapping"    , array( $this , 'add_wholesale_role_payment_gateway_mapping' ) );
            add_action( "wp_ajax_wwppUpdateWholesaleRolePaymentGatewayMapping" , array( $this , 'update_wholesale_role_payment_gateway_mapping' ) );
            add_action( "wp_ajax_wwppDeleteWholesaleRolePaymentGatewayMapping" , array( $this , 'delete_wholesale_role_payment_gateway_mapping' ) );

            // Wholesale role payment gateway surcharge mapping
            add_action( "wp_ajax_wwppAddPaymentGatewaySurcharge"    , array( $this , 'add_payment_gateway_surcharge' ) );
            add_action( "wp_ajax_wwppUpdatePaymentGatewaySurcharge" , array( $this , 'update_payment_gateway_surcharge' ) );
            add_action( "wp_ajax_wwppDeletePaymentGatewaySurcharge" , array( $this , 'delete_payment_gateway_surcharge' ) );
            
        }

        /**
         * Execute model.
         *
         * @since 1.14.0
         * @access public
         */
        public function run() {

            add_action( 'woocommerce_cart_calculate_fees'        , array( $this , 'apply_payment_gateway_surcharge' )   , 10  , 1 );
            add_filter( 'woocommerce_cart_totals_fee_html'       , array( $this , 'apply_taxable_notice_on_surcharge' ) , 10  , 2 );
            add_filter( 'woocommerce_available_payment_gateways' , array( $this , 'filter_available_payment_gateways' ) , 100 , 1 );            
            add_action( 'woocommerce_payment_token_set_default'  , array( $this , 'undefault_payment_token' )           , 10  , 2 );

            add_action( 'init' , array( $this , 'register_ajax_handler' ) );

        }

    }

}
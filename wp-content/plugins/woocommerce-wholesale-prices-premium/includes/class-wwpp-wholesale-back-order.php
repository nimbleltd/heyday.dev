<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_Wholesale_Back_Order' ) ) {
    
    /**
     * Model that houses the logic wholesale back orders.
     *
     * @since 1.14.0
     */
    class WWPP_Wholesale_Back_Order {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWPP_Wholesale_Back_Order.
         *
         * @since 1.14.0
         * @access private
         * @var WWPP_Wholesale_Back_Order
         */
        private static $_instance;
        
        /**
         * Model that houses the logic of retrieving information relating to wholesale role/s of a user.
         *
         * @since 1.14.0
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
         * WWPP_Wholesale_Back_Order constructor.
         *
         * @since 1.14.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Wholesale_Back_Order model.
         */
        public function __construct( $dependencies ) {

            $this->_wwpp_wholesale_roles = $dependencies[ 'WWPP_Wholesale_Roles' ];

        }

        /**
         * Ensure that only one instance of WWPP_Wholesale_Back_Order is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.14.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Wholesale_Back_Order model.
         * @return WWPP_Wholesale_Back_Order
         */
        public static function instance( $dependencies ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }
        
        /**
         * Always allow wholesale users to perform backorders no matter what.
         *
         * @since 1.6.0
         * @since 1.14.0 Refactor codebase and move to its proper model.
         * @access public
         * 
         * @param boolean $backorders_allowed Flag that determines if back orders are allowed or not.
         * @param int     $product_id         Product id.
         * @return boolean Filtered flag that determines if back orders are allowed or not.
         */
        public function always_allow_back_orders_to_wholesale_users( $backorders_allowed , $product_id ) {

            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();

            // Check if user is not an admin, else we don't want to restrict admins in any way.
            if ( !current_user_can( 'manage_options' ) )
                if ( get_option( 'wwpp_settings_always_allow_backorders_to_wholesale_users' , false ) == 'yes' && !empty( $user_wholesale_role ) )
                    $backorders_allowed = true;

            return apply_filters( 'wwpp_filter_product_backorders_allowed' , $backorders_allowed , $product_id , $user_wholesale_role );

        }

        /**
         * The reason being is that on WooCommerce 2.6.0 there has been a major revision on the backorders logic.
         * https://github.com/woothemes/woocommerce/issues/11187.
         *
         * @since 1.9.2
         * @since 1.14.0 Refactor codebase and move to its proper model.
         * @access public
         *
         * @param boolean $backorders_allowed Flag that determines if back orders are allowed or not.
         * @return boolean Filtered flag that determines if back orders are allowed or not.
         */
        public function always_allow_back_orders_to_wholesale_users_set_product_in_stock( $backorders_allowed ) {
            
            $user_wholesale_Role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();

            if ( version_compare( WC()->version , '2.6.0' , ">=" ) ) {

                // We only do this on WooCommerce 2.6.x series and up due to changes behavior in backorders logic

                // Check if user is not an admin, else we don't want to restrict admins in any way.
                if ( !current_user_can( 'manage_options' ) )
                    if ( get_option( 'wwpp_settings_always_allow_backorders_to_wholesale_users' , false ) == 'yes' && !empty( $user_wholesale_Role ) )
                        $backorders_allowed = true;
                
            }

            return apply_filters( 'wwpp_filter_product_is_in_stock' , $backorders_allowed , $user_wholesale_Role );

        }




        /*
        |--------------------------------------------------------------------------
        | Execute Model
        |--------------------------------------------------------------------------
        */

        /**
         * Execute model.
         *
         * @since 1.14.0
         * @access public
         */
        public function run() {

            add_filter( 'woocommerce_product_backorders_allowed' , array( $this , 'always_allow_back_orders_to_wholesale_users' )                      , 10 , 2 );
            add_filter( 'woocommerce_product_is_in_stock'        , array( $this , 'always_allow_back_orders_to_wholesale_users_set_product_in_stock' ) , 10 , 1 ); // ( WooCommerce 2.6.0 )

        }

    }

}

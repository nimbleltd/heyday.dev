<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WWPP_Duplicate_Product' ) ) {

    /**
     * Model that houses the logic of integrating with WooCommerce duplicate product function.
     *
     * @since 1.14.4
     */
    class WWPP_Duplicate_Product {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWP_Bootstrap.
         *
         * @since 1.14.4
         * @access private
         * @var WWPP_Duplicate_Product
         */
        private static $_instance;

        /**
         * Model that houses the logic of retrieving information relating to wholesale role/s of a user.
         *
         * @since 1.14.4
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
         * WWPP_Duplicate_Product constructor.
         *
         * @since 1.14.4
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Duplicate_Product model.
         */
        public function __construct( $dependencies ) {

            $this->_wwpp_wholesale_roles = $dependencies[ 'WWPP_Wholesale_Roles' ];

        }

        /**
         * Ensure that only one instance of WWPP_Duplicate_Product is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.14.4
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Duplicate_Product model.
         * @return WWPP_Duplicate_Product
         */
        public static function instance( $dependencies ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }

        /**
         * Duplicate WWPP product metas. Hooked to 'wwp_duplicate_meta' filter.
         *
         * @since 1.14.4
         * @access public
         *
         * @param array     $wwp_meta      list of WWP product metas
         */
        public function wwpp_duplicate_meta( $wwp_meta ) {

            $wwpp_meta = array(
                'wwpp_post_meta_enable_quantity_discount_rule',
                'wwpp_post_meta_quantity_discount_rule_mapping'
            );

            return array_merge( $wwp_meta , $wwpp_meta );
        }

        /**
         * Dupliate WWPP role based metas. Hooked to 'wwp_duplicate_role_based_meta' filter.
         *
         * @since 1.14.4
         * @access public
         *
         * @param array     $role_based_metas      list of WWP role based metas
         */
        public function wwpp_duplicate_role_based_meta( $role_based_metas ) {

            $wwp_role_based_metas = array(
                '_wholesale_minimum_order_quantity'
            );

            return array_merge( $role_based_metas , $wwp_role_based_metas );
        }

        /**
         * Duplicate WWPP wholesale visibility filter. Hooked to 'wwp_run_product_duplicate' action.
         *
         * @since 1.14.4
         * @access public
         *
         * @param array     $role_based_metas      list of WWP role based metas
         */
        public function wwpp_duplicate_wholesale_visibility_filter( $duplicate_id , $product_id ) {

            $filtered_roles = get_post_meta( $product_id , 'wwpp_product_wholesale_visibility_filter' , false );

            if ( empty( $filtered_roles ) )
                return;

            foreach ( $filtered_roles as $filtered_role )
                add_post_meta( $duplicate_id , 'wwpp_product_wholesale_visibility_filter' , $filtered_role );

        }



        /*
         |------------------------------------------------------------------------------------------------------------------
         | Execute Model
         |------------------------------------------------------------------------------------------------------------------
         */

        /**
         * Execute model.
         *
         * @since 1.14.4
         * @access public
         */
        function run() {

            // filters
            add_filter( 'wwp_duplicate_meta' , array( $this , 'wwpp_duplicate_meta' ) , 10 , 1 );
            add_filter( 'wwp_duplicate_role_based_meta' , array( $this , 'wwpp_duplicate_role_based_meta' ) , 10 , 1 );

            // actions
            add_action( 'wwp_run_product_duplicate' , array( $this , 'wwpp_duplicate_wholesale_visibility_filter' ) , 10 , 2 );
            add_action( 'wwp_duplicate_variation' , array( $this , 'wwpp_duplicate_wholesale_visibility_filter' ) , 10 , 2 );
        }

    }
}

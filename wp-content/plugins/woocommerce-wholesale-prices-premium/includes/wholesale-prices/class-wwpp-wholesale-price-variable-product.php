<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_Wholesale_Price_Variable_Product' ) ) {

    /**
     * Model that houses the logic of wholesale prices for variable products.
     *
     * @since 1.13.4
     */
    class WWPP_Wholesale_Price_Variable_Product {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWPP_Admin_Custom_Fields_Variable_Product.
         *
         * @since 1.13.0
         * @access private
         * @var WWPP_Admin_Custom_Fields_Variable_Product
         */
        private static $_instance;
        
        /**
         * Model that houses the logic of retrieving information relating to wholesale role/s of a user.
         *
         * @since 1.13.0
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
         * WWPP_Admin_Custom_Fields_Variable_Product constructor.
         *
         * @since 1.13.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Admin_Custom_Fields_Variable_Product model.
         */
        public function __construct( $dependencies ) {

            $this->_wwpp_wholesale_roles  = $dependencies[ 'WWPP_Wholesale_Roles' ];

        }

        /**
         * Ensure that only one instance of WWPP_Admin_Custom_Fields_Variable_Product is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.13.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Admin_Custom_Fields_Variable_Product model.
         * @return WWPP_Admin_Custom_Fields_Variable_Product
         */
        public static function instance( $dependencies ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }

        /**
         * Get curent user wholesale role.
         *
         * @since 1.15.0
         * @access private
         *
         * @return string User role string or empty string.
         */
        private function _get_current_user_wholesale_role() {

            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();
            
            return ( is_array( $user_wholesale_role ) && !empty( $user_wholesale_role ) ) ? $user_wholesale_role[ 0 ] : '';

        }

        /**
         * Filter the display format of variable product price for wholesale customers.
         *
         * @since 1.14.0
         * @since 1.16.0 Include bug fix for WWPP-483.
         * @access public
         *
         * @param string     $wholesale_price     Wholesale price text. Formatted price text.
         * @param string     $price               Original ( non-wholesale ) formatted price text.
         * @param WC_Product $product             Product object.
         * @param array      $user_wholesale_role User wholesale role.
         * @param float      $min_price           Variable product minimum wholesale price.
         * @param float      $max_price           Variable product maximum wholeslae price.
         * @return string Filtered variable product formatted price.
         */
        public function filter_wholesale_customer_variable_product_price_range( $args ) {

            if ( !empty( $args[ 'wholesale_price' ] ) && $args[ 'min_price' ] != $args[ 'max_price' ] && $args[ 'min_price' ] < $args[ 'max_price' ] ) {

                $return_value = array();
                $display_mode = get_option( 'wwpp_settings_variable_product_price_display' );

                if ( in_array( $display_mode , array( 'minimum' , 'maximum' ) ) ) {

                    $pos = strrpos( $args[ 'wholesale_price_title_text' ] , ":" );
                    if ( $pos !== false )
                        $args[ 'wholesale_price_title_text' ] = substr_replace( $args[ 'wholesale_price_title_text' ] , "" , $pos , strlen( ":" ) );
        
                    $args[ 'wholesale_price_title_text' ] .= $display_mode === 'minimum' ? __( ' From: ' , 'woocommerce-wholesale-prices-premium' ) : __( ' To: ' , 'woocommerce-wholesale-prices-premium' );

                    $return_value[ 'wholesale_price_title_text' ] = $args[ 'wholesale_price_title_text' ];

                }

                switch ( $display_mode ) {

                    case 'minimum':

                        $return_value[ 'wholesale_price' ] = WWP_Helper_Functions::wwp_formatted_price( $args[ 'min_price' ] );
                        
                        if ( !$args[ 'return_wholesale_price_only' ] ) {

                            $wsprice                            = !empty( $args[ 'min_wholesale_price_without_taxing' ] ) ? $args[ 'min_wholesale_price_without_taxing' ] : null;
                            $return_value[ 'wholesale_price' ] .= WWP_Wholesale_Prices::get_wholesale_price_suffix( $args[ 'product' ] , $args[ 'user_wholesale_role' ] , $wsprice );

                        }

                        return $return_value;

                    case 'maximum':

                        $return_value[ 'wholesale_price' ] = WWP_Helper_Functions::wwp_formatted_price( $args[ 'max_price' ] );
                        
                        if ( !$args[ 'return_wholesale_price_only' ] ) {

                            $wsprice                            = !empty( $args[ 'max_wholesale_price_without_taxing' ] ) ? $args[ 'max_wholesale_price_without_taxing' ] : null;
                            $return_value[ 'wholesale_price' ] .= WWP_Wholesale_Prices::get_wholesale_price_suffix( $args[ 'product' ] , $args[ 'user_wholesale_role' ] , $wsprice );

                        }
                        
                        return $return_value;

                    default:
                        
                        $return_value[ 'wholesale_price' ] = WWP_Helper_Functions::wwp_formatted_price( $args[ 'min_price' ] ) . ' - ' . WWP_Helper_Functions::wwp_formatted_price( $args[ 'max_price' ] );
                        
                        $price_suffix = get_option( 'wwpp_settings_override_price_suffix' );
                        if ( empty( $price_suffix ) )
                            $price_suffix = get_option( 'woocommerce_price_display_suffix' );
                        
                        if ( strpos( $price_suffix , '{price_including_tax}' ) === false && strpos( $price_suffix , '{price_excluding_tax}' ) === false && !$args[ 'return_wholesale_price_only' ] ) {

                            $wsprice                            = !empty( $args[ 'max_wholesale_price_without_taxing' ] ) ? $args[ 'max_wholesale_price_without_taxing' ] : null;
                            $return_value[ 'wholesale_price' ] .= WWP_Wholesale_Prices::get_wholesale_price_suffix( $args[ 'product' ] , $args[ 'user_wholesale_role' ] , $wsprice );

                        }

                        return $return_value;
                        
                }

            } else
                return array( 'wholesale_price' => $args[ 'wholesale_price' ] );
            
        }
        
        /**
         * Filter available variable product variations.
         * The main purpose for this is to address the product price range of a variable product for non wholesale customers.
         * You see in wwpp, you can set some variations of a variable product to be exclusive only to a certain wholesale roles.
         * Now if we dont do the code below, the price range computation for regular customers will include those variations that are exclusive only to certain wholesale roles.
         * Therefore making the calculation wrong. That is why we need to filter the variation ids of a variable product depending on the current user's role.
         * This function is a replacement to our in-house built function 'filter_regular_customer_variable_product_price_range' which is not really efficient.
         * Basically 'filter_regular_customer_variable_product_price_range' function re invents the wheel and we are recreating the price range for non wholesale users ourselves. Not good.
         * 'filter_regular_customer_variable_product_price_range' function is now removed.
         *
         * Important Note: WooCommerce tend to save a cache data of a product on transient, that is why sometimes this hook 'woocommerce_get_children' will not be executed
         * if there is already a cached data on transient. No worries tho, on version 1.15.0 of WWPP we are now clearing WC transients on WWPP activation so we are sure that 'woocommerce_get_children' will be executed.
         * We only need to do that once on WWPP activation coz, individual product transient is cleared on every product update on the backend.
         * So if they update the variation visibility on the backend, of course they will hit save to save the changes, that will clear the transient for this product and in turn executing this callback. So all good.
         *
         * @since 1.15.0
         * @access public
         *
         * @param array               $children Array of variation ids.
         * @param WC_Product_Variable $product  Variable product instance.
         * @return array Filtered array of variation ids.
         */
        public function filter_available_variable_product_variations( $children , $product ) {

            if ( !current_user_can( 'manage_options' ) && WWP_Helper_Functions::wwp_get_product_type( $product ) === "variable" ) {

                $filtered_children   = array();
                $user_wholesale_role = $this->_get_current_user_wholesale_role();

                foreach ( $children as $variation_id ) {

                    $roles_variation_is_visible = get_post_meta( $variation_id , WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER );
                    if ( !is_array( $roles_variation_is_visible ) )
                        $roles_variation_is_visible = array();

                    if ( empty( $roles_variation_is_visible ) || in_array( 'all' , $roles_variation_is_visible ) || in_array( $user_wholesale_role , $roles_variation_is_visible ) )
                        $filtered_children[] = $variation_id;

                }

                return $filtered_children;

            }

            return $children;

        }




        /*
        |--------------------------------------------------------------------------
        | Execute Model
        |--------------------------------------------------------------------------
        */

        /**
         * Execute model.
         *
         * @since 1.13.4
         * @access public
         */
        public function run() {

            add_filter( 'wwp_filter_variable_product_wholesale_price_range' , array( $this , 'filter_wholesale_customer_variable_product_price_range' ) , 10 , 1 );
            add_filter( 'woocommerce_get_children'                          , array( $this , 'filter_available_variable_product_variations' ) , 10 , 2 );

        }

    }

}
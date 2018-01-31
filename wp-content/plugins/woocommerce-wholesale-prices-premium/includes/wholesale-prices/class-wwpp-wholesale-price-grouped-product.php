<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_Wholesale_Price_Grouped_Product' ) ) {

    /**
     * Class that houses the logic of integrating wwpp with grouped products.
     *
     * @since 1.9.0
     */
    class WWPP_Wholesale_Price_Grouped_Product {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWPP_Wholesale_Price_Grouped_Product.
         *
         * @since 1.9.0
         * @access private
         * @var WWPP_Wholesale_Price_Grouped_Product
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

        /**
         * Model that houses logic of wholesale prices.
         * 
         * @since 1.14.0
         * @access private
         * @var WWPP_Wholesale_Prices
         */
        private $_wwpp_wholesale_prices;




        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        /**
         * WWPP_Wholesale_Price_Grouped_Product constructor.
         *
         * @since 1.9.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Wholesale_Price_Grouped_Product model.
         */
        public function __construct( $dependencies ) {

            $this->_wwpp_wholesale_roles  = $dependencies[ 'WWPP_Wholesale_Roles' ];
            $this->_wwpp_wholesale_prices = $dependencies[ 'WWPP_Wholesale_Prices' ];

        }

        /**
         * Ensure that only one instance of WWPP_Wholesale_Price_Grouped_Product is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.9.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Wholesale_Price_Grouped_Product model.
         * @return WWPP_Wholesale_Price_Grouped_Product
         */
        public static function instance( $dependencies ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }

        /**
         * After woocommerce set transient for children of grouped products, delete the transient.
         *
         * @since 1.9.0
         * @since 1.14.0 Refactor codebase.
         * @access public
         *
		 * @param string $transient  The name of the transient.
		 * @param mixed  $value      Transient value.
		 * @param int    $expiration Time until expiration in seconds.
         */
        public function delete_grouped_product_child_transient( $transient, $value, $expiration ) {

            if ( strpos( $transient , 'wc_product_children_' ) !== false )
                delete_transient( $transient );

        }

        /**
         * Filter grouped product price range to apply wholesale pricing.
         *
         * @since 1.9.0
         * @since 1.14.0 Refactor codebase.
         * @since 1.16.0 Supports new wholesale price model.
         * @access public
         *
         * @param string             $price   Price html.
         * @param WC_Product_Grouped $product Grouped product object.
         * @return string Filtered price html.
         */
        public function wholesale_grouped_price_html( $price , $product ) {

            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();

            if ( !empty( $user_wholesale_role ) ) {

                $tax_display_mode                = get_option( 'woocommerce_tax_display_shop' );
                $child_prices                    = array();
                $wholesale_price_range           = "";
                $has_member_with_wholesale_price = false;

                foreach ( $product->get_children() as $child_id ) {

                    $child_price           = get_post_meta( $child_id, '_price', true );
                    $price_arr             = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v2( $child_id , $user_wholesale_role );
                    $child_wholesale_price = $price_arr[ 'wholesale_price' ];

                    if ( !$has_member_with_wholesale_price && $child_wholesale_price )
                        $has_member_with_wholesale_price = true;

                    $child_prices[] = ( $child_wholesale_price ) ? $child_wholesale_price : $child_price;

                }

                // Only do this if at least one member of this bundle product has wholesale price
                if ( $has_member_with_wholesale_price ) {

                    $child_prices     = array_unique( $child_prices );
                    $get_price_method = $tax_display_mode === 'incl' ? 'wwp_get_price_including_tax' : 'wwp_get_price_excluding_tax';

                    if ( ! empty( $child_prices ) ) {

                        $min_price = min( $child_prices );
                        $max_price = max( $child_prices );

                    } else {

                        $min_price = '';
                        $max_price = '';

                    }

                    if ( $min_price ) {

                        if ( $min_price == $max_price )
                            $display_price = WWP_Helper_Functions::wwp_formatted_price( WWP_Helper_Functions::$get_price_method( $product , array( 'qty' => 1 , 'price' => $min_price ) ) );
                        else {

                            $from          = WWP_Helper_Functions::wwp_formatted_price( WWP_Helper_Functions::$get_price_method( $product , array( 'qty' => 1 , 'price' => $min_price ) ) );
                            $to            = WWP_Helper_Functions::wwp_formatted_price( WWP_Helper_Functions::$get_price_method( $product , array( 'qty' => 1 , 'price' => $max_price ) ) );

                            $display_price = sprintf( __( '%1$s&ndash;%2$s' , 'Price range: from-to' , 'woocommerce' ) , $from , $to );

                        }

                        $wholesale_price_range .= $display_price . WWP_Wholesale_Prices::get_wholesale_price_suffix( $product , $user_wholesale_role , $price_arr[ 'wholesale_price_with_no_tax' ] );

                    }

                    if ( strcasecmp( $wholesale_price_range , '' ) != 0 ) {

                        if ( get_option( 'wwpp_settings_hide_original_price' ) !== "yes" ) {

                            // Crush out existing prices, regular and sale
                            if ( strpos( $price , 'ins') !== false )
                                $wholesale_price_html = str_replace( 'ins' , 'del' , $price );
                            else {

                                $wholesale_price_html = str_replace( '<span' , '<del><span' , $price );
                                $wholesale_price_html = str_replace( '</span>' , '</span></del>' , $wholesale_price_html );

                            }

                        } else
                            $wholesale_price_html = '';
                        
                        $wholesale_price_title_text = __( 'Wholesale Price:' , 'woocommerce-wholesale-prices-premium' );
                        $wholesale_price_title_text = apply_filters( 'wwp_filter_wholesale_price_title_text' , $wholesale_price_title_text );

                        $wholesale_price_html .= '<span style="display: block;" class="wholesale_price_container">
                                                    <span class="wholesale_price_title">' . $wholesale_price_title_text . '</span>
                                                    <ins>' . $wholesale_price_range . '</ins>
                                                </span>';

                        return apply_filters( 'wwp_filter_wholesale_price_html' , $wholesale_price_html , $price , $product , $user_wholesale_role , $wholesale_price_title_text , '' , '' );

                    }

                }

            }

            return $price;

        }
        



        /*
        |--------------------------------------------------------------------------
        | Execute model
        |--------------------------------------------------------------------------
        */

        /**
         * Execute model.
         *
         * @since 1.14.0
         * @access public
         */
        public function run() {

            add_action( 'setted_transient'               , array( $this , 'delete_grouped_product_child_transient' ) , 10 , 3 );
            add_filter( 'woocommerce_grouped_price_html' , array( $this , 'wholesale_grouped_price_html' )           , 10 , 2 );

        }

    }

}
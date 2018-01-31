<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WWP_Wholesale_Prices {

    /*
    |--------------------------------------------------------------------------
    | Class Properties
    |--------------------------------------------------------------------------
    */

    /**
     * Property that holds the single main instance of WWP_Wholesale_Prices.
     *
     * @since 1.3.0
     * @access private
     * @var WWP_Wholesale_Prices
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
     * WWP_Wholesale_Prices constructor.
     *
     * @since 1.3.0
     * @access public
     *
     * @param array $dependencies Array of instance objects of all dependencies of WWP_Wholesale_Prices model.
     */
    public function __construct( $dependencies = array() ) {

        if ( isset( $dependencies[ 'WWP_Wholesale_Roles' ] ) )
            $this->_wwp_wholesale_roles  = $dependencies[ 'WWP_Wholesale_Roles' ];

    }

    /**
     * Ensure that only one instance of WWP_Wholesale_Prices is loaded or can be loaded (Singleton Pattern).
     *
     * @since 1.3.0
     * @access public
     *
     * @param array $dependencies Array of instance objects of all dependencies of WWP_Wholesale_Prices model.
     * @return WWP_Wholesale_Prices
     */
    public static function instance( $dependencies ) {

        if ( !self::$_instance instanceof self )
            self::$_instance = new self( $dependencies );

        return self::$_instance;

    }

    /**
     * Ensure that only one instance of WWP_Wholesale_Prices is loaded or can be loaded (Singleton Pattern).
     *
     * @since 1.3.0
     * @access public
     * @deprecated: Will be remove on future versions
     *
     * @param array $dependencies Array of instance objects of all dependencies of WWP_Wholesale_Prices model.
     * @return WWP_Wholesale_Prices
     */
    public static function getInstance() {

        if( !self::$_instance instanceof self )
            self::$_instance = new self;

        return self::$_instance;

    }

    /**
     * Return product wholesale price for a given wholesale user role.
     * Still being used on WWOF 1.7.8
     *
     * @deprecated: Will be remove on future versions
     * @since 1.0.0
     * @param $product_id
     * @param $user_wholesale_role
     * @return string
     */
    public static function getUserProductWholesalePrice( $product_id , $user_wholesale_role ) {

        return self::getProductWholesalePrice( $product_id , $user_wholesale_role );

    }

    /**
     * Return product wholesale price for a given wholesale user role.
     *
     * @param $product_id
     * @param $user_wholesale_role
     * @param $quantity
     * @deprecated To be removed for future versions.
     *
     * @return string
     * @since 1.0.0
     */
    public static function getProductWholesalePrice( $product_id , $user_wholesale_role , $quantity = 1 ) {

        if ( empty( $user_wholesale_role ) ) {

            return '';

        } else {

            if ( WWP_ACS_Integration_Helper::aelia_currency_switcher_active() ) {

                $baseCurrencyWholesalePrice = $wholesale_price = get_post_meta( $product_id , $user_wholesale_role[ 0 ] . '_wholesale_price' , true );

                if ( $baseCurrencyWholesalePrice ) {

                    $activeCurrency = get_woocommerce_currency();
                    $baseCurrency   = WWP_ACS_Integration_Helper::get_product_base_currency( $product_id );

                    if ( $activeCurrency == $baseCurrency )
                        $wholesale_price = $baseCurrencyWholesalePrice; // Base Currency
                    else {

                        $wholesale_price = get_post_meta( $product_id , $user_wholesale_role[ 0 ] . '_' . $activeCurrency . '_wholesale_price' , true );

                        if ( !$wholesale_price ) {

                            /*
                             * This specific currency has no explicit wholesale price (Auto). Therefore will need to convert the wholesale price
                             * set on the base currency to this specific currency.
                             *
                             * This is why it is very important users set the wholesale price for the base currency if they want wholesale pricing
                             * to work properly with aelia currency switcher plugin integration.
                             */
                            $wholesale_price = WWP_ACS_Integration_Helper::convert( $baseCurrencyWholesalePrice , $activeCurrency , $baseCurrency );

                        }

                    }

                    $wholesale_price = apply_filters( 'wwp_filter_' . $activeCurrency . '_wholesale_price' , $wholesale_price , $product_id , $user_wholesale_role , $quantity );

                } else
                    $wholesale_price = ''; // Base currency not set. Ignore the rest of the wholesale price set on other currencies.

            } else
                $wholesale_price = get_post_meta( $product_id , $user_wholesale_role[ 0 ] . '_wholesale_price' , true );

            return apply_filters( 'wwp_filter_wholesale_price' , $wholesale_price , $product_id , $user_wholesale_role , $quantity );

        }

    }

    /**
     * Get product raw wholesale price. Without being passed through any filter.
     *
     * @since 1.5.0
     * @access public
     *
     * @param int     $product_id          Product id.
     * @param array   $user_wholesale_role Array of user wholesale roles.
     * @return string Filtered wholesale price.
     */
    public static function get_product_raw_wholesale_price( $product_id , $user_wholesale_role ) {

        if ( empty( $user_wholesale_role ) )
            $wholesale_price = '';
        else {

            if ( WWP_ACS_Integration_Helper::aelia_currency_switcher_active() ) {

                $baseCurrencyWholesalePrice = $wholesale_price = get_post_meta( $product_id , $user_wholesale_role[ 0 ] . '_wholesale_price' , true );

                if ( $baseCurrencyWholesalePrice ) {

                    $activeCurrency = get_woocommerce_currency();
                    $baseCurrency   = WWP_ACS_Integration_Helper::get_product_base_currency( $product_id );

                    if ( $activeCurrency == $baseCurrency )
                        $wholesale_price = $baseCurrencyWholesalePrice; // Base Currency
                    else {

                        $wholesale_price = get_post_meta( $product_id , $user_wholesale_role[ 0 ] . '_' . $activeCurrency . '_wholesale_price' , true );

                        if ( !$wholesale_price ) {

                            /*
                             * This specific currency has no explicit wholesale price (Auto). Therefore will need to convert the wholesale price
                             * set on the base currency to this specific currency.
                             *
                             * This is why it is very important users set the wholesale price for the base currency if they want wholesale pricing
                             * to work properly with aelia currency switcher plugin integration.
                             */
                            $wholesale_price = WWP_ACS_Integration_Helper::convert( $baseCurrencyWholesalePrice , $activeCurrency , $baseCurrency );

                        }

                    }

                    $wholesale_price = apply_filters( 'wwp_filter_' . $activeCurrency . '_wholesale_price' , $wholesale_price , $product_id , $user_wholesale_role , $quantity );

                } else
                    $wholesale_price = ''; // Base currency not set. Ignore the rest of the wholesale price set on other currencies.

            } else
                $wholesale_price = get_post_meta( $product_id , $user_wholesale_role[ 0 ] . '_wholesale_price' , true );

        }

        return $wholesale_price;

    }

    /**
     * Return product wholesale price for a given wholesale user role.
     * With 'wwp_filter_wholesale_price_shop' filter already applied.
     * Replaces getProductWholesalePrice.
     *
     * @since 1.5.0
     * @since 1.6.0 Deprecated.
     * @access public
     *
     * @param int     $product_id          Product id.
     * @param array   $user_wholesale_role Array of user wholesale roles.
     * @return string Filtered wholesale price.
     */
    public static function get_product_wholesale_price_on_shop( $product_id , $user_wholesale_role ) {

        $price_arr = self::get_product_wholesale_price_on_shop_v2( $product_id , $user_wholesale_role );
        return $price_arr[ 'wholesale_price' ];

    }

    /**
     * Replacement for 'get_product_wholesale_price_on_shop'.
     * Returns an array containing wholesale price both passed through and not passed through taxing.
     *
     * @since 1.6.0
     * @access public
     *
     * @param int     $product_id          Product id.
     * @param array   $user_wholesale_role Array of user wholesale roles.
     * @return array Array of wholesale price data.
     */
    public static function get_product_wholesale_price_on_shop_v2( $product_id , $user_wholesale_role ) {

        $price_arr = array();

        $per_product_level_wholesale_price = self::get_product_raw_wholesale_price( $product_id , $user_wholesale_role );

        if ( empty( $per_product_level_wholesale_price ) ) {

            $result = apply_filters( 'wwp_filter_wholesale_price_shop' , array( 'source' => 'per_product_level' , 'wholesale_price' => $per_product_level_wholesale_price ) , $product_id , $user_wholesale_role , null , null );
            
            $price_arr[ 'wholesale_price_with_no_tax' ] = trim( $result[ 'wholesale_price' ] );
            $price_arr[ 'source' ]                      = $result[ 'source' ];

        } else {

            $price_arr[ 'wholesale_price_with_no_tax' ] = $per_product_level_wholesale_price;
            $price_arr[ 'source' ]                      = 'per_product_level';

        }
        
        $price_arr[ 'wholesale_price' ] = trim( apply_filters( 'wwp_pass_wholesale_price_through_taxing' , $price_arr[ 'wholesale_price_with_no_tax' ] , $product_id , $user_wholesale_role ) );

        return $price_arr;

    }

    /**
     * Return product wholesale price for a given wholesale user role.
     * With 'wwp_filter_wholesale_price_cart' filter already applied.
     * The wholesale price returned is not passed through taxing filters.
     * No need to do it tho, coz we hooking on 'before_calculate_totals' hook so after our wholesale price computation,
     * WC will take care of passing it through taxing options.
     *
     * @since 1.5.0
     * @since 1.6.0 Refactor codebase.
     * @access public
     *
     * @param int     $product_id          Product id.
     * @param array   $user_wholesale_role Array of user wholesale roles.
     * @param array   $cart_item           Cart item data.
     * @return string Filtered wholesale price.
     */
    public static function get_product_wholesale_price_on_cart( $product_id , $user_wholesale_role , $cart_item , $cart_object ) {

        $result = apply_filters( 'wwp_filter_wholesale_price_cart' , array( 'source' => 'per_product_level' , 'wholesale_price' => self::get_product_raw_wholesale_price( $product_id , $user_wholesale_role ) ) , $product_id , $user_wholesale_role , $cart_item , $cart_object );

        return trim( $result[ 'wholesale_price' ] );
    }

    /**
     * Get wholesale price suffix.
     *
     * @since 1.6.0
     * @access public
     *
     * @param WC_Product $product                     WC_Product object.
     * @param array      $user_wholesale_role         User wholesale role.
     * @param string     $wholesale_price             Wholesale price.
     * @param boolean    $return_wholesale_price_only Whether to return wholesale price markup only, used on product cpt listing.
     * @return string Wholesale price suffix.
     */
    public static function get_wholesale_price_suffix( $product , $user_wholesale_role , $wholesale_price , $return_wholesale_price_only = false ) {

        return apply_filters( 'wwp_filter_wholesale_price_display_suffix' , $product->get_price_suffix() , $product , $user_wholesale_role , $wholesale_price , $return_wholesale_price_only );

    }

    /**
     * Filter callback that alters the product price, it embeds the wholesale price of a product for a wholesale user.
     *
     * @since 1.0.0
     * @since 1.2.8 Now if empty $price then don't bother creating wholesale html price.
     * @since 1.5.0 Refactor codebase.
     * @since 1.6.0 Refactor codebase.
     * @access public
     *
     * @param string     $price                       Product price in html.
     * @param WC_Product $product                     WC_Product instance.
     * @param array      $user_wholesale_role         User's wholesale role.
     * @param boolean    $return_wholesale_price_only Whether to only return the wholesale price markup. Used for products cpt listing.
     * @return string Product price with wholesale applied if necessary.
     */
    public function wholesale_price_html_filter( $price , $product , $user_wholesale_role = null , $return_wholesale_price_only = false ) {

        if ( is_null( $user_wholesale_role ) )
            $user_wholesale_role = $this->_wwp_wholesale_roles->getUserWholesaleRole();

        if ( !empty( $user_wholesale_role ) && !empty( $price ) ) {

            $wholesale_price_title_text = trim( apply_filters( 'wwp_filter_wholesale_price_title_text' , __( 'Wholesale Price:' , 'woocommerce-wholesale-prices' ) ) );
            $raw_wholesale_price        = '';
            $wholesale_price            = '';
            $source                     = '';

            if ( in_array( WWP_Helper_Functions::wwp_get_product_type( $product ) , array( 'simple' , 'variation' ) ) ) {

                $price_arr           = self::get_product_wholesale_price_on_shop_v2( WWP_Helper_Functions::wwp_get_product_id( $product ) , $user_wholesale_role );
                $raw_wholesale_price = $price_arr[ 'wholesale_price' ];
                $source              = $price_arr[ 'source' ];

                if ( strcasecmp( $raw_wholesale_price , '' ) != 0 ) {

                    $wholesale_price = WWP_Helper_Functions::wwp_formatted_price( $raw_wholesale_price );

                    if ( !$return_wholesale_price_only )
                        $wholesale_price .= self::get_wholesale_price_suffix( $product , $user_wholesale_role , $price_arr[ 'wholesale_price_with_no_tax' ] , $return_wholesale_price_only );
                    
                }
                
            } elseif ( WWP_Helper_Functions::wwp_get_product_type( $product ) === 'variable' ) {

                $user_id    = get_current_user_id();
                $cache_data = apply_filters( 'wwp_get_variable_product_price_range_cache' , false , $user_id , $product , $user_wholesale_role );
                
                // Do not use caching if $return_wholesale_price_only is true, coz this is used on cpt listing
                // and cpt listing callback is triggered unpredictably, and multiple times.
                // It is even triggered even before WC have initialized
                if ( is_array( $cache_data ) && $cache_data[ 'min_price' ] && $cache_data[ 'max_price' ] && !$return_wholesale_price_only ) {

                    $min_price                            = $cache_data[ 'min_price' ];
                    $min_wholesale_price_without_taxing   = $cache_data[ 'min_wholesale_price_without_taxing' ];
                    $max_price                            = $cache_data[ 'max_price' ];
                    $max_wholesale_price_without_taxing   = $cache_data[ 'max_wholesale_price_without_taxing' ];
                    $some_variations_have_wholesale_price = $cache_data[ 'some_variations_have_wholesale_price' ];

                } else {

                    $variations                           = WWP_Helper_Functions::wwp_get_variable_product_variations( $product );
                    $min_price                            = '';
                    $min_wholesale_price_without_taxing   = '';
                    $max_price                            = '';
                    $max_wholesale_price_without_taxing   = '';
                    $some_variations_have_wholesale_price = false;

                    foreach ( $variations as $variation ) {

                        if ( !$variation[ 'is_purchasable' ] )
                            continue;

                            $curr_var_price = $variation[ 'display_price' ];
                            $price_arr      = self::get_product_wholesale_price_on_shop_v2( $variation[ 'variation_id' ] , $user_wholesale_role );

                        if ( strcasecmp( $price_arr[ 'wholesale_price' ] , '' ) != 0 ) {

                            $curr_var_price = $price_arr[ 'wholesale_price' ];

                            if ( !$some_variations_have_wholesale_price )
                                $some_variations_have_wholesale_price = true;

                        }

                        if ( strcasecmp( $min_price , '' ) == 0 || $curr_var_price < $min_price ) {

                            $min_price                          = $curr_var_price;
                            $min_wholesale_price_without_taxing = strcasecmp( $price_arr[ 'wholesale_price_with_no_tax' ] , '' ) != 0 ? $price_arr[ 'wholesale_price_with_no_tax' ] : '';

                        }

                        if ( strcasecmp( $max_price , '' ) == 0 || $curr_var_price > $max_price ) {

                            $max_price                          = $curr_var_price;
                            $max_wholesale_price_without_taxing = strcasecmp( $price_arr[ 'wholesale_price_with_no_tax' ] , '' ) != 0 ? $price_arr[ 'wholesale_price_with_no_tax' ] : '';

                        }

                    }
                    
                    if ( !$return_wholesale_price_only ) {

                        do_action( 'wwp_after_variable_product_compute_price_range' , $user_id , $product , $user_wholesale_role , array(
                            'min_price'                            => $min_price,
                            'min_wholesale_price_without_taxing'   => $min_wholesale_price_without_taxing,
                            'max_price'                            => $max_price,
                            'max_wholesale_price_without_taxing'   => $max_wholesale_price_without_taxing,
                            'some_variations_have_wholesale_price' => $some_variations_have_wholesale_price
                        ) );

                    }
                    
                }

                // Only alter price html if, some/all variations of this variable product have sale price and
                // min and max price have valid values
                if ( $some_variations_have_wholesale_price && strcasecmp( $min_price , '' ) != 0 && strcasecmp( $max_price , '' ) != 0 ) {

                    if ( $min_price != $max_price && $min_price < $max_price ) {

                        $wholesale_price = WWP_Helper_Functions::wwp_formatted_price( $min_price ) . ' - ' . WWP_Helper_Functions::wwp_formatted_price( $max_price );
                        $wc_price_suffix = get_option( 'woocommerce_price_display_suffix' );

                        if ( strpos( $wc_price_suffix , '{price_including_tax}' ) === false && strpos( $wc_price_suffix , '{price_excluding_tax}' ) === false && !$return_wholesale_price_only ) {

                            $wsprice          = !empty( $max_wholesale_price_without_taxing ) ? $max_wholesale_price_without_taxing : null;
                            $wholesale_price .= self::get_wholesale_price_suffix( $product , $user_wholesale_role , $wsprice , $return_wholesale_price_only );    

                        }

                    } else {

                        $wholesale_price = WWP_Helper_Functions::wwp_formatted_price( $max_price );

                        if ( !$return_wholesale_price_only ) {

                            $wsprice       = !empty( $max_wholesale_price_without_taxing ) ? $max_wholesale_price_without_taxing : null;
                            $wholesale_price .= self::get_wholesale_price_suffix( $product , $user_wholesale_role , $wsprice , $return_wholesale_price_only );

                        }
                        
                    }

                }

                $return_value = apply_filters( 'wwp_filter_variable_product_wholesale_price_range' , array(
                                    'wholesale_price'                    => $wholesale_price ,
                                    'price'                              => $price ,
                                    'product'                            => $product ,
                                    'user_wholesale_role'                => $user_wholesale_role ,
                                    'min_price'                          => $min_price ,
                                    'min_wholesale_price_without_taxing' => $min_wholesale_price_without_taxing,
                                    'max_price'                          => $max_price ,
                                    'max_wholesale_price_without_taxing' => $max_wholesale_price_without_taxing,
                                    'wholesale_price_title_text'         => $wholesale_price_title_text,
                                    'return_wholesale_price_only'        => $return_wholesale_price_only
                                ) );

                $wholesale_price = $return_value[ 'wholesale_price' ];

                if ( isset( $return_value[ 'wholesale_price_title_text' ] ) )
                    $wholesale_price_title_text = $return_value[ 'wholesale_price_title_text' ];

            }

            if ( strcasecmp( $wholesale_price , '' ) != 0 ) {

                $wholesale_price_html = '<span style="display: block;" class="wholesale_price_container">
                                            <span class="wholesale_price_title">' . $wholesale_price_title_text . '</span>
                                            <ins>' . $wholesale_price . '</ins>
                                        </span>';

                if ( $return_wholesale_price_only )
                    return $wholesale_price_html;

                $wholesale_price_html = apply_filters( 'wwp_product_original_price' , '<del class="original-computed-price">' . $price . '</del>' , $wholesale_price , $price , $product , $user_wholesale_role ) . $wholesale_price_html;

                return apply_filters( 'wwp_filter_wholesale_price_html' , $wholesale_price_html , $price , $product , $user_wholesale_role , $wholesale_price_title_text , $raw_wholesale_price , $source );

            }

        }

        return apply_filters( 'wwp_filter_variable_product_price_range_for_none_wholesale_users' , $price , $product );

    }

    /**
     * Apply product wholesale price upon adding to cart.
     *
     * @since 1.0.0
     * @since 1.2.3 Add filter hook 'wwp_filter_get_custom_product_type_wholesale_price' for which extensions can attach and add support for custom product types.
     * @since 1.4.0 Add filter hook 'wwp_wholesale_requirements_not_passed' for which extensions can attach and do something whenever wholesale requirement is not meet.
     * @since 1.5.0 Rewrote the code for speed and efficiency.
     * @access public
     *
     * @param $cart_object
     * @param $user_wholesale_role
     */
    public function apply_product_wholesale_price_to_cart( $cart_object ) {

        $user_wholesale_role = $this->_wwp_wholesale_roles->getUserWholesaleRole();

        if ( empty( $user_wholesale_role ) )
            return false;

        $per_product_requirement_notices = array();
        $has_cart_items                  = false;
        $cart_total                      = 0;
        $cart_items                      = 0;
        $cart_items_price_cache          = array(); // Holds the original prices of products in cart

        do_action( 'wwp_before_apply_product_wholesale_price_cart_loop' , $cart_object , $user_wholesale_role );

        foreach ( $cart_object->cart_contents as $cart_item_key => $cart_item ) {

            if ( !$has_cart_items )
                $has_cart_items = true;

            $wwp_data        = null;
            $wholesale_price = '';

            if ( in_array( WWP_Helper_Functions::wwp_get_product_type( $cart_item[ 'data' ] ) , array( 'simple' , 'variation' ) ) )
                $wholesale_price = self::get_product_wholesale_price_on_cart( WWP_Helper_Functions::wwp_get_product_id( $cart_item[ 'data' ] ) , $user_wholesale_role , $cart_item , $cart_object );
            else
                $wholesale_price = apply_filters( 'wwp_filter_get_custom_product_type_wholesale_price' , $wholesale_price , $cart_item , $user_wholesale_role , $cart_object );

            if ( $wholesale_price !== '' ) {

                $apply_product_level_wholesale_price = apply_filters( 'wwp_apply_wholesale_price_per_product_level' , true , $cart_item , $cart_object , $user_wholesale_role , $wholesale_price );

                if ( $apply_product_level_wholesale_price === true ) {

                    $cart_items_price_cache[ $cart_item_key ] = $cart_item[ 'data' ]->get_price();
                    $cart_item[ 'data' ]->set_price( WWP_Helper_Functions::wwp_wpml_price( $wholesale_price ) );
                    $wwp_data = array( 'wholesale_priced' => 'yes' , 'wholesale_role' => $user_wholesale_role[ 0 ] );

                } else {

                    if ( is_array( $apply_product_level_wholesale_price ) )
                        $per_product_requirement_notices[] = $apply_product_level_wholesale_price;

                    $wwp_data = array( 'wholesale_priced' => 'no' , 'wholesale_role' => $user_wholesale_role[ 0 ] );

                }

            } else
                $wwp_data = array( 'wholesale_priced' => 'no' , 'wholesale_role' => $user_wholesale_role[ 0 ] );

            // Add additional wwp data to cart item. This is used for WWS Reporting
            $cart_item[ 'data' ]->wwp_data = apply_filters( 'wwp_add_cart_item_meta' , $wwp_data , $cart_item , $cart_object , $user_wholesale_role );

            if ( apply_filters( 'wwp_include_cart_item_on_cart_totals_computation' , true , $cart_item , $user_wholesale_role ) ) {

                $price = $wholesale_price !== '' ? $wholesale_price : $cart_item[ 'data' ]->get_price();

                $cart_total += $price * $cart_item[ 'quantity' ];
                $cart_items += $cart_item[ 'quantity' ];

            }

        } // Cart loop

        do_action( 'wwp_after_apply_product_wholesale_price_cart_loop' , $cart_object , $user_wholesale_role );

        $apply_wholesale_price_cart_level = apply_filters( 'wwp_apply_wholesale_price_cart_level' , true , $cart_total , $cart_items , $cart_object , $user_wholesale_role );

        if ( ( $has_cart_items && $apply_wholesale_price_cart_level !== true ) || !empty( $per_product_requirement_notices ) )
            do_action( 'wwp_wholesale_requirements_not_passed' , $cart_object , $user_wholesale_role );

        if ( $has_cart_items && $apply_wholesale_price_cart_level !== true ) {

            // Revert back to original pricing
            foreach ( $cart_object->cart_contents as $cart_item_key => $cart_item ) {

                if ( array_key_exists( $cart_item_key , $cart_items_price_cache ) ) {

                    $cart_item[ 'data' ]->set_price( $cart_items_price_cache[ $cart_item_key ] );
                    $cart_item[ 'data' ]->wwp_data = array( 'wholesale_priced' => 'no' , 'wholesale_role' => $user_wholesale_role[ 0 ] );

                }

            }

            if ( ( is_cart() || is_checkout() ) && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) )
                $this->printWCNotice(  $apply_wholesale_price_cart_level );

        }

        if ( !empty( $per_product_requirement_notices ) )
            foreach ( $per_product_requirement_notices as $notice )
                if ( ( is_cart() || is_checkout() ) && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) )
                    $this->printWCNotice( $per_product_requirement_notices );

    }

    /**
     * Recalculate cart totals.
     * We need to do this on loading widget cart to properly sync the cart item prices.
     * If we don't do this, the cart item line price will not be sync with what's on the cart.
     *
     * @since 1.5.0
     * @access public
     */
    public function recalculate_cart_totals() {

        WC()->cart->calculate_totals();

    }

    /**
     * Apply taxing accordingly to wholesale prices on shop page.
     * We will handle tax application to wholesale prices only on WWP if WWPP is not present.
     * If WWPP is present lets allow WWPP to handle this instead.
     * This is only applied on shop page, we dont need to do this on cart/checkout prices.
     * WC will take care of that coz we are hooking to 'before_calculate_totals' so after we apply wholesale pricing on cart/checkout page,
     * WC will then apply taxing above it.
     *
     * @since 1.5.0
     * @access public
     *
     * @param float $wholesale_price     Wholesale price.
     * @param int   $product_id          Product Id.
     * @param array $user_wholesale_role User wholesale roles.
     * @return float Modified wholesale price.
     */
    public function apply_taxing_to_wholesale_prices_on_shop_page( $wholesale_price , $product_id , $user_wholesale_role ) {

        if ( !WWP_Helper_Functions::is_plugin_active( 'woocommerce-wholesale-prices-premium/woocommerce-wholesale-prices-premium.bootstrap.php' ) && !empty( $wholesale_price ) && !empty( $user_wholesale_role ) && get_option( 'woocommerce_calc_taxes' , false ) === 'yes' ) {

            $product                      = wc_get_product( $product_id );
            $woocommerce_tax_display_shop = get_option( 'woocommerce_tax_display_shop' , false );

            if ( $woocommerce_tax_display_shop === 'incl' )
                $wholesale_price = WWP_Helper_Functions::wwp_get_price_including_tax( $product , array( 'qty' => 1 , 'price' => $wholesale_price ) );
            else
                $wholesale_price = WWP_Helper_Functions::wwp_get_price_excluding_tax( $product , array( 'qty' => 1 , 'price' => $wholesale_price ) );

        }

        return $wholesale_price;

    }

    /**
     * Print WP Notices.
     *
     * @since 1.0.7
     * @access public
     *
     * @param string|array $notices WWP/P related notices.
     */
    public function printWCNotice( $notices ) {

        if ( is_array( $notices ) && array_key_exists( 'message' , $notices ) && array_key_exists( 'type' , $notices ) ) {
            // Pre Version 1.2.0 of wwpp where it sends back single dimension array of notice

            wc_print_notice( $notices[ 'message' ] , $notices[ 'type' ] );

        } elseif ( is_array( $notices ) ) {
            // Version 1.2.0 of wwpp where it sends back multiple notice via multi dimensional arrays

            foreach ( $notices as $notice ) {

                if ( array_key_exists( 'message' , $notice ) && array_key_exists( 'type' , $notice ) )
                    wc_print_notice( $notice[ 'message' ] , $notice[ 'type' ] );

            }

        }

    }




    /*
    |--------------------------------------------------------------------------
    | Execute Model
    |--------------------------------------------------------------------------
    */

    /**
     * Execute model.
     *
     * @since 1.5.0
     * @access public
     */
    public function run() {

        // Apply wholesale price to archive and single product pages
        // On WC 3.x series, includes variation products
        add_filter( 'woocommerce_get_price_html' , array( $this , 'wholesale_price_html_filter' ) , 10 , 2 );

        // Apply wholesale price upon adding product to cart
        add_action( 'woocommerce_before_calculate_totals' , array( $this , 'apply_product_wholesale_price_to_cart' ) , 10 , 1 );

        // We need to recalculate cart on loading widget cart to properly sync the cart item prices
        add_action( 'woocommerce_before_mini_cart' , array( $this , 'recalculate_cart_totals' ) );

        // Apply taxing to wholesale price on shop pages
        add_filter( 'wwp_pass_wholesale_price_through_taxing' , array( $this , 'apply_taxing_to_wholesale_prices_on_shop_page' ) , 10 , 3 );

    }

}

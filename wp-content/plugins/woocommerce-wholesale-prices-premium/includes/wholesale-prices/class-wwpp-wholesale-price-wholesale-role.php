<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_Wholesale_Price_Wholesale_Role' ) ) {

    /**
     * Model that houses the logic of applying wholesale role level wholesale pricing.
     * 
     * @since 1.16.0
     */
    class WWPP_Wholesale_Price_Wholesale_Role {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWPP_Wholesale_Price_Wholesale_Role.
         *
         * @since 1.16.0
         * @access private
         * @var WWPP_Wholesale_Price_Wholesale_Role
         */
        private static $_instance;
        

        
        
        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        /**
         * WWPP_Wholesale_Price_Wholesale_Role constructor.
         *
         * @since 1.16.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Wholesale_Price_Wholesale_Role model.
         */
        public function __construct( $dependencies ) {}
        
        /**
         * Ensure that only one instance of WWPP_Wholesale_Price_Wholesale_Role is loaded or can be loaded (Singleton Pattern).
         * 
         * @since 1.16.0
         * @access public
         * 
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Wholesale_Price_Wholesale_Role model.
         * @return WWPP_Wholesale_Price_Wholesale_Role
         */
        public static function instance( $dependencies = array() ) {
        
            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );
    
            return self::$_instance;
    
        }

        /**
         * Render wholesale role cart quantity based wholesale discount table markup.
         * Support ignore role/cat level wholesale pricing feature.
         * 
         * @since 1.16.0
         * @access public
         * 
         * @param string     $wholesale_price_html       Wholesale price html.
         * @param string     $price                      Active price html( non wholesale ).
         * @param WC_Product $product                    WC_Product object.
         * @param array      $user_wholesale_role        Array user wholesale roles.
         * @param string     $wholesale_price_title_text Wholesale price title text.
         * @param string     $raw_wholesale_price        Raw wholesale price.
         * @param string     $source                     Source of the wholesale price being applied.
         * @return string Filtered wholesale price html.
         */
        public function render_wholesale_role_cart_quantity_based_wholesale_discount_table_markup( $wholesale_price_html , $price , $product , $user_wholesale_role , $wholesale_price_title_text , $raw_wholesale_price , $source ) {
            
            // Only apply this to single product pages and proper ajax request
            // When a variable product have lots of variations, WC will not load variation data on variable product page load on front end
            // Instead it will load variations data as you select them on the variations select box
            // We need to support this too
            if ( !empty( $user_wholesale_role ) && 
               ( ( get_option( 'wwpp_settings_hide_quantity_discount_table' , false ) !== 'yes' && ( is_product() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) && ( in_array( WWP_Helper_Functions::wwp_get_product_type( $product ) , array( 'simple' , 'variation' , 'composite' , 'bundle' ) ) ) ) ||
                 apply_filters( 'render_cart_quantity_based_wholesale_discount_per_wholesale_role_level_table_markup' , false ) ) ) {                
                
                $post_id = ( WWP_Helper_Functions::wwp_get_product_type( $product ) === 'variation' ) ? WWP_Helper_Functions::wwp_get_parent_variable_id( $product ) : WWP_Helper_Functions::wwp_get_product_id( $product );    

                if ( get_post_meta( $post_id , 'wwpp_ignore_role_level_wholesale_discount' , true ) === 'yes' )
                    return $wholesale_price_html;
                
                // Make sure that the wholesale price being applied is on per wholesale role or per user level
                if ( empty( $raw_wholesale_price ) || !in_array( $source , array( 'wholesale_role_level' , 'per_user_level' ) ) )
                    return $wholesale_price_html;
                
                $user_id                   = get_current_user_id();
                $cart_qty_discount_mapping = array();
                
                if ( get_user_meta( $user_id , 'wwpp_override_wholesale_discount' , true ) === 'yes' ) {

                    $puwd = get_user_meta( $user_id , 'wwpp_wholesale_discount' , true );

                    if ( !empty( $puwd ) ) {

                        switch( get_user_meta( $user_id , 'wwpp_override_wholesale_discount_qty_discount_mapping' , true ) ) {

                            case 'dont_use_general_per_wholesale_role_qty_mapping':
                                break; // Do nothing

                            case 'use_general_per_wholesale_role_qty_mapping':

                                $cart_qty_discount_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_CART_QTY_BASED_DISCOUNT_MAPPING , array() );
                                if ( !is_array( $cart_qty_discount_mapping ) )
                                    $cart_qty_discount_mapping = array();

                                break; // Do nothing

                            case 'specify_general_per_wholesale_role_qty_mapping':

                                $cart_qty_discount_mapping = get_user_meta( $user_id , 'wwpp_wholesale_discount_qty_discount_mapping' , true );
                                if ( !is_array( $cart_qty_discount_mapping ) )
                                    $cart_qty_discount_mapping = array();

                                break;

                        }

                    }

                } else {

                    // Check if this feature is even enabled
                    if ( get_option( 'enable_wholesale_role_cart_quantity_based_wholesale_discount' ) !== 'yes' )
                        return $wholesale_price_html;

                    // Check if base wholesale role discount is set. Per qty discount is based on this so if this is not set, then let return out now
                    $wholesale_role_discount = get_option( WWPP_OPTION_WHOLESALE_ROLE_GENERAL_DISCOUNT_MAPPING , array() );
                    if ( !is_array( $wholesale_role_discount ) )
                        $wholesale_role_discount = array();

                    if ( !array_key_exists( $user_wholesale_role[ 0 ] , $wholesale_role_discount ) )
                        return $wholesale_price_html;
                    
                    $cart_qty_discount_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_CART_QTY_BASED_DISCOUNT_MAPPING , array() );
                    if ( !is_array( $cart_qty_discount_mapping ) )
                        $cart_qty_discount_mapping = array();

                }

                if ( empty( $cart_qty_discount_mapping ) )
                    return $wholesale_price_html;

                // We need to check if there are "any sort" of wholesale pricing on the per product level or per category level. If there is, we skip this then
                $product_id = WWP_Helper_Functions::wwp_get_product_id( $product );
                $post_id    = ( WWP_Helper_Functions::wwp_get_product_type( $product ) === 'variation' ) ? WWP_Helper_Functions::wwp_get_parent_variable_id( $product ) : WWP_Helper_Functions::wwp_get_product_id( $product );

                // Process per qty discount table markup
                $product_active_price = get_option( 'wwpp_settings_explicitly_use_product_regular_price_on_discount_calc' ) == 'yes' ? $product->get_regular_price() : $product->get_price();
                $has_range_discount   = false;
                $mapping_table_html   = '';
                $user_id              = get_current_user_id();

                if ( get_option( 'enable_wholesale_role_cart_quantity_based_wholesale_discount_mode_2' ) === 'yes' )
                    $desc_text = WWP_Helper_Functions::wwp_get_product_type( $product ) === 'variation' ? __( 'Quantity based discounts available based on how many of this variation is in your cart.' , 'woocommerce-wholesale-prices-premium' ) : __( 'Quantity based discounts available based on how many of this product is in your cart.' , 'woocommerce-wholesale-prices-premium' );           
                else
                    $desc_text = __( "Quantity based discounts available based on how many items are in your cart." , 'woocommerce-wholesale-prices-premium' );

                if ( get_user_meta( $user_id , 'wwpp_override_wholesale_discount' , true ) === 'yes' && 
                     get_user_meta( $user_id , 'wwpp_override_wholesale_discount_qty_discount_mapping' , true ) === 'specify_general_per_wholesale_role_qty_mapping' ) {
                    
                    if ( get_user_meta( $user_id , 'wwpp_wholesale_discount_qty_discount_mapping_mode_2' , true ) === 'yes' )
                        $desc_text = WWP_Helper_Functions::wwp_get_product_type( $product ) === 'variation' ? __( 'Quantity based discounts available based on how many of this variation is in your cart.' , 'woocommerce-wholesale-prices-premium' ) : __( 'Quantity based discounts available based on how many of this product is in your cart.' , 'woocommerce-wholesale-prices-premium' );
                    else
                        $desc_text = __( "Quantity based discounts available based on how many items are in your cart." , 'woocommerce-wholesale-prices-premium' );

                }
                
                $desc_text = apply_filters( 'wwpp_per_wholesale_role_level_qty_discount_table_desc' , $desc_text );

                // Table view
                ob_start(); ?>
                
                <div class="qty-based-discount-table-description">
                    <p class="desc"><?php echo $desc_text; ?></p>
                </div>

                <table class="order-quantity-based-wholesale-pricing-view table-view">
                    <thead>
                        <tr>
                            <th><?php _e( 'Qty' , 'woocommerce-wholesale-prices-premium' );  ?></th>
                            <th><?php _e( 'Price' , 'woocommerce-wholesale-prices-premium' );  ?></th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ( $cart_qty_discount_mapping as $index => $mapping_data ) {
                            
                            if ( $user_wholesale_role[ 0 ] == $mapping_data[ 'wholesale_role' ] ) {

                                if ( !$has_range_discount )
                                    $has_range_discount = true;

                                $product_computed_price = $product_active_price - ( ( $mapping_data[ 'percent_discount' ] / 100 ) * $product_active_price  );
                                $product_computed_price = WWP_Helper_Functions::wwp_formatted_price( $product_computed_price );
                                
                                if ( $mapping_data[ 'end_qty' ] != '' )
                                    $qty_range = $mapping_data[ 'start_qty' ] . ' - ' . $mapping_data[ 'end_qty' ];
                                else
                                    $qty_range = $mapping_data[ 'start_qty' ] . '+'; ?>
                                
                                <tr>
                                    <td><?php echo $qty_range; ?></td>
                                    <td><?php echo $product_computed_price; ?></td>
                                </tr>

                            <?php }

                        } ?>
                    </tbody>
                </table>
                    
                <?php $mapping_table_html = ob_get_clean();

                if ( $has_range_discount )
                    $wholesale_price_html .= $mapping_table_html;
                
            }

            return $wholesale_price_html;

        }

        /**
         * Apply wholesale role general discount to the product being purchased by this user.
         * Only applies if
         * General discount is set for this wholesale role
         * No category level discount is set
         * No wholesale price is set
         *
         * @since 1.2.0
         * @since 1.16.0 
         * Now calculates price with wholesale role cart quantity based wholesale discount.
         * This function was previously named as 'applyWholesaleRoleGeneralDiscount' and was from class-wwpp-wholesale-prices.php.
         * Support ignore role/cat level wholesale pricing feature.
         * @access public
         * 
         * @param array        $wholesale_price_arr Wholesale price array data.
         * @param int          $product_id          Product id.
         * @param array        $user_wholesale_role User wholesale roles.
         * @param null|array   $cart_item           Cart item. Null if this callback is being called by the 'wwp_filter_wholesale_price_shop' filter.
         * @param null|WC_Cart $cart_object         Cart object. Null if this callback is being called by the 'wwp_filter_wholesale_price_shop' filter.
         * @return array Filtered wholesale price array data.
         */
        public function apply_wholesale_role_general_discount( $wholesale_price_arr , $product_id , $user_wholesale_role , $cart_item , $cart_object ) {
        
            if ( !empty( $user_wholesale_role ) && empty( $wholesale_price_arr[ 'wholesale_price' ] ) ) {

                $product = wc_get_product( $product_id );
                $post_id = ( WWP_Helper_Functions::wwp_get_product_type( $product ) === 'variation' ) ? WWP_Helper_Functions::wwp_get_parent_variable_id( $product ) : $product_id;
                
                if ( get_post_meta( $post_id , 'wwpp_ignore_role_level_wholesale_discount' , true ) === 'yes' )
                    return $wholesale_price_arr;

                $user_wholesale_discount = $this->get_user_wholesale_role_level_discount( get_current_user_id() , $user_wholesale_role[ 0 ] , $cart_item , $cart_object );
    
                if ( is_numeric( $user_wholesale_discount[ 'discount' ] ) && !empty( $user_wholesale_discount[ 'discount' ] ) ) {
                    
                    $product                                  = wc_get_product( $product_id );
                    $product_price                            = get_option( 'wwpp_settings_explicitly_use_product_regular_price_on_discount_calc' ) == 'yes' ? $product->get_regular_price() : $product->get_price();
                    $wholesale_price_arr[ 'wholesale_price' ] = round( $product_price - ( $product_price * ( $user_wholesale_discount[ 'discount' ] / 100 ) ) , 2 );
                    $wholesale_price_arr[ 'source' ]          = $user_wholesale_discount[ 'source' ];

                    return $wholesale_price_arr;
                    
                }
    
            }
    
            return $wholesale_price_arr;
    
        }

        /**
         * Get specific user wholesale discount.
         * 
         * @since 1.16.0
         * @access public
         * 
         * @param string $user_wholesale_role User wholesale role.
         * @param int    $user_id             User id.
         * @return array Wholesale discount array data.
         */
        public function get_user_wholesale_role_level_discount( $user_id , $user_wholesale_role , $cart_item = null , $cart_object = null ) {
            
            $user_wholesale_discount                      = array( 'source' => false , 'discount' => false );
            $user_wholesale_discount_from_general_mapping = false;
            
            if ( get_option( 'enable_wholesale_role_cart_quantity_based_wholesale_discount_mode_2' ) === 'yes' && !is_null( $cart_item ) )
                $total_items = $cart_item[ 'quantity' ];
            else
                $total_items = !is_null( $cart_object ) ? $cart_object->get_cart_contents_count() : 0;

            $wholesale_role_discount = get_option( WWPP_OPTION_WHOLESALE_ROLE_GENERAL_DISCOUNT_MAPPING , array() );
            if ( !is_array( $wholesale_role_discount ) )
                $wholesale_role_discount = array();

            $user_wholesale_role = empty( $user_wholesale_role ) ? "" : $user_wholesale_role;

            if ( array_key_exists( $user_wholesale_role , $wholesale_role_discount ) && !empty( $wholesale_role_discount[ $user_wholesale_role ] ) ) {

                $user_wholesale_discount = array( 'source' => 'wholesale_role_level' , 'discount' => $wholesale_role_discount[ $user_wholesale_role ] );

                // Maybe process cart qty based wholesale role discount
                if ( !empty( $user_wholesale_discount[ 'discount' ] ) && !is_null( $cart_item ) && !is_null( $cart_object ) && get_option( 'enable_wholesale_role_cart_quantity_based_wholesale_discount' ) === 'yes' ) {
                    
                    $cart_qty_discount_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_CART_QTY_BASED_DISCOUNT_MAPPING , array() );
                    if ( !is_array( $cart_qty_discount_mapping ) )
                        $cart_qty_discount_mapping = array();

                    $temp_value = $this->_get_discount_from_qty_mapping( $cart_qty_discount_mapping , $total_items , $user_wholesale_role );

                    if ( $temp_value !== false ) {

                        $user_wholesale_discount                      = array( 'source' => 'wholesale_role_level_qty_based' , 'discount' => $temp_value );
                        $user_wholesale_discount_from_general_mapping = array( 'source' => 'wholesale_role_level_qty_based' , 'discount' => $temp_value );

                    }

                }

            }
            
            if ( get_user_meta( $user_id , 'wwpp_override_wholesale_discount' , true ) === 'yes' ) {

                $puwd = get_user_meta( $user_id , 'wwpp_wholesale_discount' , true );

                if ( is_numeric( $puwd ) || empty( $puwd ) )
                    $user_wholesale_discount = array( 'source' => 'per_user_level' , 'discount' => $puwd );
    
                if ( !empty( $user_wholesale_discount[ 'discount' ] ) && !is_null( $cart_item ) && !is_null( $cart_object ) ) {

                    switch ( get_user_meta( $user_id , 'wwpp_override_wholesale_discount_qty_discount_mapping' , true ) ) {
                        
                        case 'dont_use_general_per_wholesale_role_qty_mapping': 
                            break;
    
                        case 'use_general_per_wholesale_role_qty_mapping':
                            $user_wholesale_discount = $user_wholesale_discount_from_general_mapping !== false ? $user_wholesale_discount_from_general_mapping : $user_wholesale_discount;
                            break;
    
                        case 'specify_general_per_wholesale_role_qty_mapping':

                            $total_items = get_user_meta( $user_id , 'wwpp_wholesale_discount_qty_discount_mapping_mode_2' , true ) === 'yes' ? $total_items = $cart_item[ 'quantity' ] : $cart_object->get_cart_contents_count();

                            $cart_qty_discount_mapping = get_user_meta( $user_id , 'wwpp_wholesale_discount_qty_discount_mapping' , true );
                            if ( !is_array( $cart_qty_discount_mapping ) )
                                $cart_qty_discount_mapping = array();
                            
                            $temp_value = $this->_get_discount_from_qty_mapping( $cart_qty_discount_mapping , $total_items , $user_wholesale_role );

                            if ( $temp_value !== false )
                                $user_wholesale_discount = array( 'source' => 'per_user_level_qty_based' , 'discount' => $temp_value );
                        
                    }

                }

            }

            return $user_wholesale_discount;

        }




        /*
        |--------------------------------------------------------------------------------------------------------------------
        | Helper Functions
        |--------------------------------------------------------------------------------------------------------------------
        */

        /**
         * Get wholesale discount of a cart quantity from the set quantity discount mapping.
         * 
         * @since 1.16.0
         * @access private
         * 
         * @param array  $cart_qty_discount_mapping Array of qty discount mapping.
         * @param int    $cart_total_items          Total items on cart.
         * @param string $user_wholesale_role       User wholesale role.
         * @return boolean|string Boolean false if mapping is empty or no entry on mapping, string of discount when there is an entry on the mapping.
         */
        private function _get_discount_from_qty_mapping(  $cart_qty_discount_mapping , $cart_total_items , $user_wholesale_role ) {

            if ( !empty( $cart_qty_discount_mapping ) ) {

                foreach ( $cart_qty_discount_mapping as $mapping ) {
                    
                    if ( $user_wholesale_role == $mapping[ 'wholesale_role' ] && $cart_total_items >= $mapping[ 'start_qty' ] && 
                        ( empty( $mapping[ 'end_qty' ] ) || $cart_total_items <= $mapping[ 'end_qty' ] ) &&
                        !empty( $mapping[ 'percent_discount' ] ) ) {
    
                        return $mapping[ 'percent_discount' ];
                        
                    }
    
                }

            }

            return false;

        }




        /*
        |--------------------------------------------------------------------------------------------------------------------
        | Execute Model
        |--------------------------------------------------------------------------------------------------------------------
        */

        /**
         * Execute model.
         * 
         * @since 1.16.0
         * @access public
         */
        public function run() {

            add_filter( 'wwp_filter_wholesale_price_html' , array( $this , 'render_wholesale_role_cart_quantity_based_wholesale_discount_table_markup' ) , 200 , 7 );
            add_filter( 'wwp_filter_wholesale_price_shop' , array( $this , 'apply_wholesale_role_general_discount' ) , 200 , 5 );
            add_filter( 'wwp_filter_wholesale_price_cart' , array( $this , 'apply_wholesale_role_general_discount' ) , 200 , 5 );

        }

    }

}
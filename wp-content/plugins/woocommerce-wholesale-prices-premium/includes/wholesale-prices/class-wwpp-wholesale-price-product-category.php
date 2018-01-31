<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_Wholesale_Price_Product_Category' ) ) {

    /**
     * Model that houses the logic of applying product category level wholesale pricing.
     * 
     * @since 1.14.0
     */
    class WWPP_Wholesale_Price_Product_Category {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWPP_Wholesale_Price_Product_Category.
         *
         * @since 1.14.0
         * @access private
         * @var WWPP_Wholesale_Price_Product_Category
         */
        private static $_instance;
        

        
        
        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        /**
         * WWPP_Wholesale_Price_Product_Category constructor.
         *
         * @since 1.14.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Wholesale_Price_Product_Category model.
         */
        public function __construct( $dependencies ) {}

        /**
         * Ensure that only one instance of WWPP_Wholesale_Price_Product_Category is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.14.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Wholesale_Price_Product_Category model.
         * @return WWPP_Wholesale_Price_Product_Category
         */
        public static function instance( $dependencies ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }

        /**
         * Render order quantity based wholesale discount per category level table markup on product single page.
         * 
         * @since 1.11.0
         * @since 1.14.0 Refactor codebase and move to its own model.
         * @since 1.16.0 Support ignore role/cat level wholesale pricing feature.
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
        public function render_order_quantity_based_wholesale_discount_per_category_level_table_markup( $wholesale_price_html , $price , $product , $user_wholesale_role , $wholesale_price_title_text , $raw_wholesale_price , $source ) {
            
            // Only apply this to single product pages and proper ajax request
            // When a variable product have lots of variations, WC will not load variation data on variable product page load on front end
            // Instead it will load variations data as you select them on the variations select box
            // We need to support this too
            if ( !empty( $user_wholesale_role ) && 
                ( ( get_option( 'wwpp_settings_hide_quantity_discount_table' , false ) !== 'yes' && ( is_product() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) && ( in_array( WWP_Helper_Functions::wwp_get_product_type( $product ) , array( 'simple' , 'variation' , 'composite' , 'bundle' ) ) ) ) ||
                apply_filters( 'render_order_quantity_based_wholesale_discount_per_category_level_table_markup' , false ) ) ) {
                
                $product_id = WWP_Helper_Functions::wwp_get_product_id( $product );
                $post_id    = ( WWP_Helper_Functions::wwp_get_product_type( $product ) === 'variation' ) ? WWP_Helper_Functions::wwp_get_parent_variable_id( $product ) : $product_id;
                
                // Ignore wholesale pricing set on cat level
                if ( get_post_meta( $post_id , 'wwpp_ignore_cat_level_wholesale_discount' , true ) === 'yes' )
                    return $wholesale_price_html;

                // Make sure that wholesale price being applied is per category level
                if ( empty( $raw_wholesale_price ) || $source !== 'product_category_level' )
                    return $wholesale_price_html;
                
                // Get the base category term id
                // We need the admin to specify a base discount for a category in order for this feature to take effect
                $base_term_id_and_discount = $this->get_base_term_id_and_wholesale_discount( $post_id , $user_wholesale_role );
                $enable_feature = get_term_meta( $base_term_id_and_discount[ 'term_id' ] , 'wwpp_enable_quantity_based_wholesale_discount' , true );

                if ( $enable_feature === 'yes' ) {

                    $qbwd_mapping = get_term_meta( $base_term_id_and_discount[ 'term_id' ] , 'wwpp_quantity_based_wholesale_discount_mapping' , true );
                    if ( !is_array( $qbwd_mapping ) )
                        $qbwd_mapping = array();
                    
                    if ( !empty( $qbwd_mapping ) ) // Get category level per order quantity wholesale discount
                        $wholesale_price_html .= $this->get_cat_level_per_order_quantity_wholesale_discount_table_markup( $qbwd_mapping , $product , $user_wholesale_role , $base_term_id_and_discount );
                    
                }

            }

            return $wholesale_price_html;

        }

        /**
         * Apply product category level wholesale discount. 
         * Only applies when a product has no wholesale price set on per product level.
         * This logic came from 'class-wwpp-wholesale-prices.php' function 'applyProductCategoryWholesaleDiscount'.
         * Moved it here on this model as this is the correct place on where it should be.
         * Refactor codebase too to include category level pet qty based wholesale discount.
         * Support ignore role/cat level wholesale pricing feature.
         *
         * @since 1.16.0
         * @access public
         * 
         * @param array   $wholesale_price_arr Wholesale price array data.
         * @param int     $product_id          Product id.
         * @param array   $user_wholesale_role User wholesale role.
         * @param null|array   $cart_item      Cart item data. Null if this callback is executed by the 'wwp_filter_wholesale_price_shop' filter.
         * @param null|WC_Cart $cart_object    Cart object. Null if this callback is executed by the 'wwp_filter_wholesale_price_shop' filter.
         * @return array Filtered wholesale price array data.
         */
        public function apply_product_category_level_wholesale_discount( $wholesale_price_arr , $product_id , $user_wholesale_role , $cart_item , $cart_object ) {
        
            if ( !empty( $user_wholesale_role ) && empty( $wholesale_price_arr[ 'wholesale_price' ] ) ) {
                
                $product         = wc_get_product( $product_id );
                $post_id         = ( WWP_Helper_Functions::wwp_get_product_type( $product ) === 'variation' ) ? WWP_Helper_Functions::wwp_get_parent_variable_id( $product ) : $product_id;

                // Ignore wholesale pricing set on cat level
                if ( get_post_meta( $post_id , 'wwpp_ignore_cat_level_wholesale_discount' , true ) === 'yes' )
                    return $wholesale_price_arr;

                $product_price = get_option( 'wwpp_settings_explicitly_use_product_regular_price_on_discount_calc' ) == 'yes' ? $product->get_regular_price() : $product->get_price();
                
                if ( !is_null( $post_id ) ) {
                    
                    $base_term_id_and_discount = $this->get_base_term_id_and_wholesale_discount( $post_id , $user_wholesale_role );

                    if ( !empty( $base_term_id_and_discount[ 'discount' ] ) ) {

                        $discount = array( 'source' => 'product_category_level' , 'discount' => $base_term_id_and_discount[ 'discount' ] );

                        if ( get_term_meta( $base_term_id_and_discount[ 'term_id' ] , 'wwpp_enable_quantity_based_wholesale_discount' , true ) === 'yes' && !is_null( $cart_item ) && !is_null( $cart_object ) )
                            $discount = $this->get_cat_level_per_order_quantity_wholesale_discount( $discount , $base_term_id_and_discount[ 'term_id' ] , $product_id , $user_wholesale_role , $cart_item , $cart_object );

                        if ( !empty( $discount[ 'discount' ] ) ) {

                            $wholesale_price_arr[ 'wholesale_price' ] = round( $product_price - ( $product_price * ( $discount[ 'discount' ] / 100 ) ) , 2 );

                            if ( $wholesale_price_arr[ 'wholesale_price' ] < 0 )
                                $wholesale_price_arr[ 'wholesale_price' ] = 0;

                            $wholesale_price_arr[ 'source' ] = $discount[ 'source' ];

                            return $wholesale_price_arr;

                        }

                    }

                }
    
            }
    
            return $wholesale_price_arr;
    
        }



        
        /*
        |--------------------------------------------------------------------------------------------------------------------
        | Helper Functions
        |--------------------------------------------------------------------------------------------------------------------
        */

        /**
         * Get per order quantity wholesale discount per category level table markup to be displayed on the single product page on the front page.
         *
         * @since 1.11.0
         * @since 1.14.0 Refactor codebase and move to its own model.
         * @since 1.16.0 Add qty based discount mode2 support.
         * @access public
         *
         * @param array      $qbwd_mapping        Mapping data.
         * @param WC_Product $product             Product object.
         * @param array      $user_wholesale_role User wholesale role.
         * @return string Discount table html markup.
         */
        public function get_cat_level_per_order_quantity_wholesale_discount_table_markup( $qbwd_mapping , $product , $user_wholesale_role , $base_term_id_and_discount ) {

            $product_active_price = get_option( 'wwpp_settings_explicitly_use_product_regular_price_on_discount_calc' ) == 'yes' ? $product->get_regular_price() : $product->get_price();
            $has_range_discount   = false;
            $mapping_table_html   = '';
            $enable_mode2         = get_term_meta( $base_term_id_and_discount[ 'term_id' ] , 'wwpp_enable_quantity_based_wholesale_discount_mode2' , true );

            if ( $enable_mode2 === 'yes' )
                $desc_text = WWP_Helper_Functions::wwp_get_product_type( $product ) === 'variation' ? __( 'Quantity based discounts available based on how many of this variation is in your cart.' , 'woocommerce-wholesale-prices-premium' ) : __( 'Quantity based discounts available based on how many of this product is in your cart.' , 'woocommerce-wholesale-prices-premium' );                
            else
                $desc_text = sprintf( __( 'Quantity based discounts available based on how many items from the <b>%1$s</b> category are in your cart.' , 'woocommerce-wholesale-prices-premium' ) , $base_term_id_and_discount[ 'term_name' ] );

            // Table view
            ob_start(); ?>
            
            <div class="qty-based-discount-table-description">
                <p class="desc"><?php echo apply_filters( 'wwpp_per_category_level_qty_discount_table_desc' , $desc_text , $base_term_id_and_discount[ 'term_name' ] ); ?></p>
            </div>

            <table class="order-quantity-based-wholesale-pricing-view table-view">
                <thead>
                    <tr>
                        <th><?php _e( 'Qty' , 'woocommerce-wholesale-prices-premium' );  ?></th>
                        <th><?php _e( 'Price' , 'woocommerce-wholesale-prices-premium' );  ?></th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ( $qbwd_mapping as $index => $mapping_data ) {
                        
                        if ( $user_wholesale_role[ 0 ] == $mapping_data[ 'wholesale-role' ] ) {

                            if ( !$has_range_discount )
                                $has_range_discount = true;

                            $product_computed_price = $product_active_price - ( ( $mapping_data[ 'wholesale-discount' ] / 100 ) * $product_active_price  );
                            $product_computed_price = WWP_Helper_Functions::wwp_formatted_price( $product_computed_price );
                            
                            if ( $mapping_data[ 'end-qty' ] != '' )
                                $qty_range = $mapping_data[ 'start-qty' ] . ' - ' . $mapping_data[ 'end-qty' ];
                            else
                                $qty_range = $mapping_data[ 'start-qty' ] . '+'; ?>
                            
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
                return $mapping_table_html;
            else
                return '';
        
        }

        /**
         * Get the base term id and wholesale discount of the given product depending on the 'wwpp_settings_multiple_category_wholesale_discount_logic' option.
         * 
         * @since 1.11.0
         * @since 1.14.0 Refactor codebase and move to its own model.
         * @since 1.16.0
         * Refactor code base to return an array instead with wholesale discount included, aside from the base term.
         * Renamed function name from 'get_base_term_id' to 'get_base_term_id_and_wholesale_discount'.
         * @access public
         *
         * @param int   $product_id          Product id. If product is variation, make sure to pass parent variable product id, variations do not support product categories, only variables do.
         * @param array $user_wholesale_role User wholesale role.
         * @return array Array of data containing the base term id and wholesale discount.
         */
        public function get_base_term_id_and_wholesale_discount( $product_id , $user_wholesale_role ) {

            $terms = get_the_terms( $product_id , 'product_cat' );
            if ( !is_array( $terms ) )
                $terms = array();

            $lowest_discount            = null;
            $highest_discount           = null;
            $lowest_discount_term_id    = null;
            $highest_discount_term_id   = null;
            $lowest_discount_term_name  = null;
            $highest_discount_term_name = null;

            foreach ( $terms as $term ) {

                $category_wholesale_prices = get_option( 'taxonomy_' . $term->term_id );

                if ( is_array( $category_wholesale_prices ) && array_key_exists( $user_wholesale_role[ 0 ] . '_wholesale_discount' , $category_wholesale_prices ) ) {

                    $curr_discount = $category_wholesale_prices[ $user_wholesale_role[ 0 ] . '_wholesale_discount' ];

                    if ( !empty( $curr_discount ) ) {

                        if ( is_null( $lowest_discount ) || $curr_discount < $lowest_discount ) {

                            $lowest_discount           = $curr_discount;
                            $lowest_discount_term_id   = $term->term_id;
                            $lowest_discount_term_name = $term->name;

                        }

                        if ( is_null( $highest_discount ) || $curr_discount > $highest_discount ) {

                            $highest_discount           = $curr_discount;
                            $highest_discount_term_id   = $term->term_id;
                            $highest_discount_term_name = $term->name;

                        }

                    }

                }

            }

            $category_wholsale_price_logic = get_option( 'wwpp_settings_multiple_category_wholesale_discount_logic' );

            if ( $category_wholsale_price_logic == 'highest' )
                return array( 'term_id' => $highest_discount_term_id , 'discount' => $highest_discount , 'term_name' => $highest_discount_term_name );
            else
                return array( 'term_id' => $lowest_discount_term_id , 'discount' => $lowest_discount , 'term_name' => $lowest_discount_term_name );

        }

        /**
         * Get order quantity wholesale discount per category level.
         *
         * @since 1.11.0
         * @since 1.14.0 Refactor codebase and move to its own model.
         * @since 1.14.5 
         * Now it has improve support for per order quantity discount per category level.
         * Composite or Bundled items are now being counted and applied per order quantity discount but in the context of the parent bundle or composite product.
         * It's quantity won't be mixed up to the total count of the "other products under the same category" that is not a composite or bundle of the parent bundle or composite product ( that is independent products ). 
         * @since 1.16.0
         * Now it returns the wholesale discount instead of the calculated wholesale price.
         * Renamed from 'get_cat_level_per_order_quantity_wholesale_price' to 'get_cat_level_per_order_quantity_wholesale_discount'.
         * Parameters have been dramatically changed.
         * Add qty based discount mode2 support.
         * @access public
         *
         * @param array   $discount            Wholesale discount array data. Base wholesale discount on category level.
         * @param int     $term_id             Base term id. The current term we are basing the discount on.
         * @param int     $product_id          Product id.
         * @param array   $user_wholesale_role User wholesale role.
         * @param array   $cart_item           Cart item data.
         * @param WC_Cart $cart_object         Cart object. 
         * @return array Wholesale discount array data.
         */
        public function get_cat_level_per_order_quantity_wholesale_discount( $discount , $term_id , $product_id , $user_wholesale_role , $cart_item , $cart_object ) {

            $enable_mode2 = get_term_meta( $term_id , 'wwpp_enable_quantity_based_wholesale_discount_mode2' , true );
            $qbwd_mapping = get_term_meta( $term_id , 'wwpp_quantity_based_wholesale_discount_mapping' , true );
            if ( !is_array( $qbwd_mapping ) )
                $qbwd_mapping = array();

            $cat_product_cart_items = 0;
            $product                = wc_get_product( $product_id );
            $product_active_price   = get_option( 'wwpp_settings_explicitly_use_product_regular_price_on_discount_calc' ) == 'yes' ? $product->get_regular_price() : $product->get_price();
            
            if ( $enable_mode2 === "yes" )
                $cat_product_cart_items = $cart_item[ "quantity" ];
            else {

                if ( isset( $cart_item[ 'bundled_by' ] ) || isset( $cart_item[ 'composite_parent' ] ) ) {
    
                    foreach ( $cart_object->get_cart() as $cart_item_key => $cart_item_data ) {
    
                        if ( ( !isset( $cart_item_data[ 'bundled_by' ] ) || $cart_item_data[ 'bundled_by' ] !== $cart_item[ 'bundled_by' ] ) &&
                                ( !isset( $cart_item_data[ 'composite_parent' ] ) || $cart_item_data[ 'composite_parent' ] !== $cart_item[ 'composite_parent' ] ) )
                                continue;
    
                        $product_id = ( WWP_Helper_Functions::wwp_get_product_type( $cart_item_data[ 'data' ] ) === 'variation' ) ? WWP_Helper_Functions::wwp_get_parent_variable_id( $cart_item_data[ 'data' ] ) : WWP_Helper_Functions::wwp_get_product_id( $cart_item_data[ 'data' ] );
    
                        if ( has_term( $term_id , 'product_cat' , $product_id ) )
                            $cat_product_cart_items += $cart_item_data[ 'quantity' ];
                        
                    }
    
                } else {
    
                    foreach ( $cart_object->get_cart() as $cart_item_key => $cart_item_data ) {
                        
                        if ( isset( $cart_item_data[ 'bundled_by' ] ) || isset( $cart_item_data[ 'composite_parent' ] ) )
                            continue;
    
                        $product_id = ( WWP_Helper_Functions::wwp_get_product_type( $cart_item_data[ 'data' ] ) === 'variation' ) ? WWP_Helper_Functions::wwp_get_parent_variable_id( $cart_item_data[ 'data' ] ) : WWP_Helper_Functions::wwp_get_product_id( $cart_item_data[ 'data' ] );
                        
                        if ( has_term( $term_id , 'product_cat' , $product_id ) )
                            $cat_product_cart_items += $cart_item_data[ 'quantity' ];
    
                    }
    
                }

            }

            foreach ( $qbwd_mapping as $index => $mapping_data )
                if ( $user_wholesale_role[ 0 ] == $mapping_data[ 'wholesale-role' ] )
                    if ( $cat_product_cart_items >= $mapping_data[ 'start-qty' ] && ( empty( $mapping_data[ 'end-qty' ] ) || $cat_product_cart_items <= $mapping_data[ 'end-qty' ] ) && $mapping_data[ 'wholesale-discount' ] != '' )
                        return array( 'source' => 'product_category_level_qty_based' , 'discount' => $mapping_data[ 'wholesale-discount' ] );
            
            return $discount;
            
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

            // Display qty based wholesale discount per cat level table markup
            add_filter( 'wwp_filter_wholesale_price_html' , array( $this , 'render_order_quantity_based_wholesale_discount_per_category_level_table_markup' ) , 100 , 7 );        
            
            // Apply cat level wholesale discount on shop and cart
            add_filter( 'wwp_filter_wholesale_price_shop' , array( $this , 'apply_product_category_level_wholesale_discount' ) , 100 , 5 );
            add_filter( 'wwp_filter_wholesale_price_cart' , array( $this , 'apply_product_category_level_wholesale_discount' ) , 100 , 5 );

        }

    }

}
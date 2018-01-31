<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_WC_Product_Bundles' ) ) {

    /**
     * Model that houses the logic of integrating with 'WooCommerce Product Bundles' plugin.
     *
     * Bundle products just inherits from simple product so that's why they are very similar.
     * So most of the codebase here are just reusing the codes from simple product.
     *
     * @since 1.13.0
     */
    class WWPP_WC_Product_Bundles {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWPP_WC_Composite_Product.
         *
         * @since 1.13.0
         * @access private
         * @var WWPP_WC_Composite_Product
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

        /**
         * Model that houses logic of admin custom fields for simple products.
         *
         * @since 1.13.0
         * @access private
         * @var WWPP_Admin_Custom_Fields_Simple_Product
         */
        private $_wwpp_admin_custom_fields_simple_product;

        /**
         * Model that houses the logic of wholesale prices.
         *
         * @since 1.13.0
         * @access private
         * @var WWPP_Wholesale_Prices
         */
        private $_wwpp_wholesale_prices;

        /**
         * Model that houses the logic of applying product category level wholesale pricing.
         *
         * @since 1.14.0
         * @access public
         * @var WWPP_Wholesale_Price_Product_Category
         */
        private $_wwpp_wholesale_price_product_category;

        /**
         * Model that houses the logic of product wholesale price on per wholesale role level.
         * 
         * @since 1.16.0
         * @access private
         * @var WWPP_Wholesale_Price_Wholesale_Role
         */
        private $_wwpp_wholesale_price_wholesale_role;




        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        /**
         * WWPP_WC_Composite_Product constructor.
         *
         * @since 1.13.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_WC_Composite_Product model.
         */
        public function __construct( $dependencies ) {

            $this->_wwpp_wholesale_roles                    = $dependencies[ 'WWPP_Wholesale_Roles' ];
            $this->_wwpp_admin_custom_fields_simple_product = $dependencies[ 'WWPP_Admin_Custom_Fields_Simple_Product' ];
            $this->_wwpp_wholesale_prices                   = $dependencies[ 'WWPP_Wholesale_Prices' ];
            $this->_wwpp_wholesale_price_product_category   = $dependencies[ 'WWPP_Wholesale_Price_Product_Category' ];
            $this->_wwpp_wholesale_price_wholesale_role     = $dependencies[ 'WWPP_Wholesale_Price_Wholesale_Role' ];

        }

        /**
         * Ensure that only one instance of WWPP_WC_Composite_Product is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.13.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_WC_Composite_Product model.
         * @return WWPP_WC_Composite_Product
         */
        public static function instance( $dependencies ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }

        /**
         * Add bundle product wholesale price field.
         *
         * @since 1.13.0
         * @access public
         */
        public function add_wholesale_price_fields() {

            global $post , $wc_wholesale_prices;

            $product = wc_get_product( $post->ID );

            if ( WWP_Helper_Functions::wwp_get_product_type( $product ) === 'bundle' )
                $wc_wholesale_prices->wwp_admin_custom_fields_simple_product->add_wholesale_price_fields();

        }

        /**
         * Save bundle product wholesale price field.
         *
         * @since 1.9.0
         * @since 1.13.0 Refactored codebase and move to its dedicated model.
         * @access public
         *
         * @param int $post_id Product id.
         */
        public function save_wholesale_price_fields( $post_id ) {

            global $wc_wholesale_prices;

            $wc_wholesale_prices->wwp_admin_custom_fields_simple_product->save_wholesale_price_fields( $post_id , 'bundle' );

        }

        /**
         * Save bundle product wholesale minimum order quantity field.
         *
         * @since 1.9.0
         * @since 1.13.0 Refactored codebase and move to its dedicated model.
         * @access public
         *
         * @param $post_id
         */
        public function save_minimum_order_quantity_fields( $post_id ) {

            // Bundle products are very similar to simple products in terms of their fields structure.
            // Therefore we can reuse the code we have on saving wholesale minimum order quantity for simple products to bundle products.
            // BTW the adding of custom wholesale minimum order quantity field to bundle products are already handled by this function 'add_minimum_order_quantity_fields' on 'WWPP_Admin_Custom_Fields_Simple_Product'. Read the desc of the function.
            $this->_wwpp_admin_custom_fields_simple_product->save_minimum_order_quantity_fields( $post_id , 'bundle' );

        }

        /**
         * Filter bundled items of a bundle product and check if the current user is allowed to view the bundled item.
         *
         * @since 1.13.0
         * @access public
         *
         * @param array             $bundled_items     Array bundled items.
         * @param WC_Product_Bundle $wc_product_bundle Bundle product instance.
         * @return array Filtered array bundled items.
         */
        public function filter_bundled_items( $bundled_items , $wc_product_bundle ) {

            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();
            $user_wholesale_role = !empty( $user_wholesale_role ) ? $user_wholesale_role[0] : '';

            foreach ( $bundled_items as $bundle_id => $bundled_item ) {

                $product_id = $bundled_item->item_data[ 'product_id' ];

                if ( !$this->is_bundle_item_available_for_current_user( $product_id , $user_wholesale_role ) )
                    unset( $bundled_items[ $bundle_id ] );

            }

            return $bundled_items;

        }

        /**
         * Check if current bundle item is available for the current user.
         *
         * @since 1.13.0
         * @since 1.16.0 Refactor code base to get wholesale discount wholesale role level from 'WWPP_Wholesale_Price_Wholesale_Role' model.
         * @access public
         *
         * @param int    $product_id          Product id.
         * @param string $user_wholesale_role User wholesale role.
         * @return boolean True if current user have access to the current bundle item, false otherwise.
         */
        public function is_bundle_item_available_for_current_user( $product_id , $user_wholesale_role ) {

            $have_wholesale_price = "yes";

            $curr_product_wholesale_filter = get_post_meta( $product_id , WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER );
            if ( !is_array( $curr_product_wholesale_filter ) )
                $curr_product_wholesale_filter = array();
            
            if ( get_option( 'wwpp_settings_only_show_wholesale_products_to_wholesale_users' , false ) === 'yes' ) {
                
                $user_wholesale_discount = $this->_wwpp_wholesale_price_wholesale_role->get_user_wholesale_role_level_discount( get_current_user_id() , $user_wholesale_role );
                
                if ( $user_wholesale_role && empty( $user_wholesale_discount[ 'discount' ] ) )
                    $have_wholesale_price = get_post_meta( $product_id , $user_wholesale_role . '_have_wholesale_price' , true );
                
            }

            return ( ( in_array( 'all' , $curr_product_wholesale_filter ) || in_array( $user_wholesale_role , $curr_product_wholesale_filter ) ) && $have_wholesale_price === "yes" );

        }

        /**
         * The purpose of this is to aid in properly calculating the total price of a bundle product if it has wholesale price.
         * If we dont add this filter callback, bundle product will use the bundle product's original base price instead of the wholesale price in calculation.
         * Prior to v1.13.0, we add a note to the single bundle page that the computation of total is wrong and therefore they should check the cart page instead.
         * But due to changes on Product Bundle codebase, we can now successfully properly compute the total with wholesale pricing.
         *
         * @since 1.13.0
         * @access public
         *
         * @param array             $bundle_price_data Array of bundle price data.
         * @param WC_Product_Bundle $wc_product_bundle Product Product bundle object.
         */
        public function filter_bundle_product_base_price( $bundle_price_data , $wc_product_bundle ) {

            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();

            if ( empty( $user_wholesale_role ) )
                return $bundle_price_data;

            $price_arr                         = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v2( WWP_Helper_Functions::wwp_get_product_id( $wc_product_bundle ) , $user_wholesale_role );
            $wholesale_price                   = $price_arr[ 'wholesale_price' ];
            $bundle_price_data[ 'base_price' ] = $wholesale_price ? $wholesale_price : $bundle_price_data[ 'base_price' ];

            return $bundle_price_data;

        }

        /**
         * Filter the bundle item price. The purpose of this is to aid in properly calculating the total price of bundle product if it has wholesale price.
         * Same concept as the comment of 'filter_bundle_product_base_price' function above.
         * If we dont add this filter callback, bundle product will use the bundle price instead of the wholesale price in calculation.
         * There is a cache to this solution though, because bundle plugins hook too early, that is get_price, the modified price we return here is also set as the original price of the bundle item.
         * That is why we need the 'hide_original_bundle_item_price' function below to compensiate for this issue.
         *
         * @since 1.13.0
         * @access public
         *
         * @param float $bundle_item_price
         */
        public function filter_bundle_item_price( $bundled_item_price , $product , $discount , $bundled_item ) {

            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();
            if ( empty( $user_wholesale_role ) )
                return $bundled_item_price;
            
            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();
            $product_id          = WWP_Helper_Functions::wwp_get_product_id( $product );

            // The reason why we need the code below is to avoid infinite filter call loop
            // Bundles plugin adds filters on get_price() and get_regular_price() which is executed inside the callbacks of the 'wwp_filter_wholesale_price_shop' hook
            // Therefore before executing the callbacks of that hook we must remove the filters bundles plugin attached
            // This filters will be auto attached by bundle plugin as necessary
            WC_PB_Product_Prices::remove_price_filters();

            $price_arr       = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v2( $product_id , $user_wholesale_role ); // Per product wholesale pricing
            $wholesale_price = $price_arr[ 'wholesale_price' ];

            return $wholesale_price ? $wholesale_price : $bundled_item_price;

        }

        /**
         * With the advent of WC 2.7, product attributes are not directly accessible anymore.
         * We need to refactor how we retrive the id of the product.
         * Note this filter callback is only for WC less than 2.7
         *
         * @since 1.3.1
         * @access public
         *
         * @param int        $product_id Product id.
         * @param WC_Product $product    Product object.
         * @return int Product id.
         */
        public function get_product_id( $product_id , $product ) {

            if ( version_compare( WC()->version , '3.0.0' , '<' ) )
                return $product->id;

            return $product_id;

        }

        /**
         * Add support for quick edit.
         *
         * @since 1.14.4
         * @access public
         *
         * @param Array  $allowed_product_types list of allowed product types.
         * @param string $field                 wholesale custom field.
         */
        public function support_for_quick_edit_fields( $allowed_product_types , $field ) {

            $supported_fields = array(
                'wholesale_price_fields',
                'wholesale_minimum_order_quantity'
            );

            if ( in_array( $field , $supported_fields ) )
                $allowed_product_types[] = 'bundle';

            return $allowed_product_types;
        }

        /**
         * We need to do this because variable product bundle items are treated differently compared to simple products.
         * One way is that, bundle plugin uses 'get_available_variations' function of a variable product to get the data of its variations.
         * Now it uses that data instead on calculating the total of the bundle product ( when variable product is priced individually ).
         * The issue with this is we are not attaching any callback to the 'get_available_variations' function of a variable product coz there are no any filters inside that function.
         * So the price data that function returns are the original price data ( data we have not filtered ).
         * That is why on the front end you will the correct pricing but wrong total calculation.
         * So the solution is whenever bundle plugin loads the variations data via json on the front end.
         * We modify the data via js script. We need to do this coz there are no filters available for us to attach to be able to do the fix on the backend (PHP).
         *
         * @since 1.14.5
         * @access public
         *
         * @param int             $bundled_product_id Product id of the bundle item.
         * @param WC_Bundled_Item $bundled_item       Bundle item instance.
         */
        public function filter_variable_bundle_variations_data( $bundled_product_id , $bundled_item ) {

            // Only do this if variable product bundle item is priced individually
            if ( $bundled_item->item_data[ 'priced_individually' ] !== 'yes' )
                return;

            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();

            if ( !empty( $user_wholesale_role ) ) {

                $variations_wholesale_prices = null;
                $variations = WWP_Helper_Functions::wwp_get_variable_product_variations( $bundled_item->product );

                foreach ( $variations as $variation ) {

                    $price_arr        = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v2( $variation[ 'variation_id' ] , $user_wholesale_role );
                    $wholesale_price = $price_arr[ 'wholesale_price' ];

                    $variations_wholesale_prices[ $variation[ 'variation_id' ] ] = $wholesale_price;

                }

                if ( $variations_wholesale_prices ) { ?>

                    <script type="text/javascript">

                        var variations_wholesale_prices = {<?php foreach( $variations_wholesale_prices as $var_id => $wholesale_price ) { echo "$var_id : $wholesale_price,"; } ?>},
                            product_variations_data     = jQuery( '.bundled_item_cart_content[data-product_id="<?php echo $bundled_product_id; ?>"][data-bundle_id="<?php echo $bundled_item->bundle_id; ?>"]' ).data( "product_variations" );
                        
                        for ( var i = 0 ; i < product_variations_data.length ; i++ ) {
                            
                            product_variations_data[ i ].display_price = variations_wholesale_prices[ product_variations_data[ i ].id ? product_variations_data[ i ].id : product_variations_data[ i ].variation_id ];
                            product_variations_data[ i ].price         = variations_wholesale_prices[ product_variations_data[ i ].id ? product_variations_data[ i ].id : product_variations_data[ i ].variation_id ];

                        }

                        jQuery( '.bundled_item_cart_content[data-product_id="<?php echo $bundled_product_id; ?>"][data-bundle_id="<?php echo $bundled_item->bundle_id; ?>"]' ).data( "product_variations" , product_variations_data );

                    </script>

                <?php }

            }
            
        }

        /**
         * In the event variable products have the same regular price, it wont show a per variation price html.
         * That will be a problem if the wholesale price is different across variations but have the same regular price.
         * Coz there will be no html markup that we can hook to show the wholesale price per variation.
         * That is the purpose of this code.
         *
         * @since 1.14.5
         * @since 1.16.0 Supports new wholesale price model.
         * @access public
         *
         * @param int             $bundled_product_id Product id of the bundle item.
         * @param WC_Bundled_Item $bundled_item       Bundle item instance.
         */
        public function filter_per_variation_price_html( $bundled_product_id , $bundled_item ) {

            // Only do this if variable product bundle item is priced individually
            if ( $bundled_item->item_data[ 'priced_individually' ] !== 'yes' )
                return;

            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();
            
            if ( !empty( $user_wholesale_role ) ) {

                $variations_arr                                              = array();
                $product                                                     = $bundled_item->product;
                $has_per_order_quantity_wholesale_price_mapping              = false;
                $has_per_cat_level_order_quantity_wholesale_discount_mapping = false;
                $variations                                                  = WWP_Helper_Functions::wwp_get_variable_product_variations( $product );

                foreach ( $variations as $variation ) {

                    $variationProduct = wc_get_product( $variation[ 'variation_id' ] );
                    $currVarPrice     = $variation[ 'display_price' ];
                    $minimumOrder     = get_post_meta( $variation[ 'variation_id' ] , $user_wholesale_role[ 0 ] . "_wholesale_minimum_order_quantity" , true ); // Per variation level
                    $price_arr        = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v2( $variation[ 'variation_id' ] , $user_wholesale_role );
                    $wholesale_price  = $price_arr[ 'wholesale_price' ];
                    $source           = $price_arr[ 'source' ];

                    // Per parent variable level
                    if ( !$minimumOrder )
                        $minimumOrder = get_post_meta( $bundled_product_id , $user_wholesale_role[ 0 ] . "_variable_level_wholesale_minimum_order_quantity" , true );

                    // Always default to 1
                    if ( !$minimumOrder )
                        $minimumOrder = 1;
                    
                    // Check if product have per product level order quantity based wholesale price
                    if ( is_numeric( $wholesale_price ) && !$has_per_order_quantity_wholesale_price_mapping ) {

                        $enabled = get_post_meta( $variation[ 'variation_id' ] , WWPP_POST_META_ENABLE_QUANTITY_DISCOUNT_RULE , true );
                        $mapping = get_post_meta( $variation[ 'variation_id' ] , WWPP_POST_META_QUANTITY_DISCOUNT_RULE_MAPPING , true );
                        if ( !is_array( $mapping ) )
                            $mapping = array();

                        $has_mapping_entry = false;
                        foreach ( $mapping as $map )
                            if ( $map[ 'wholesale_role' ] === $user_wholesale_role[ 0 ] )
                                $has_mapping_entry = true;

                        if ( $enabled == 'yes' && $has_mapping_entry )
                            $has_per_order_quantity_wholesale_price_mapping = true;

                    }
                    
                    /**
                     * WWPP-373
                     * Check if product have product category level wholesale pricing set.
                     * Have category level discount.
                     * We do not need to check for the per qty based discount on cat level as checking the base cat discount is enough
                     */
                    if ( is_numeric( $wholesale_price ) && !$has_per_cat_level_order_quantity_wholesale_discount_mapping ) {

                        $base_term_id_and_discount = $this->_wwpp_wholesale_price_product_category->get_base_term_id_and_wholesale_discount( WWP_Helper_Functions::wwp_get_product_id( $product ) , $user_wholesale_role );

                        if ( !is_null( $base_term_id_and_discount[ 'term_id' ] ) && !is_null( $base_term_id_and_discount[ 'discount' ] ) )
                            $has_per_cat_level_order_quantity_wholesale_discount_mapping = true;

                    }

                    // Only pass through to wc_price if a numeric value given otherwise it will spit out $0.00
                    if ( is_numeric( $wholesale_price ) ) {

                        $wholesalePriceTitleText = __( 'Wholesale Price:' , 'woocommerce-wholesale-prices-premium' );
                        $wholesalePriceTitleText = apply_filters( 'wwp_filter_wholesale_price_title_text' , $wholesalePriceTitleText );

                        $wholesalePriceHTML = apply_filters( 'wwp_product_original_price' , '<del class="original-computed-price">' . WWP_Helper_Functions::wwp_formatted_price( $currVarPrice ) . $product->get_price_suffix() . '</del>' , $wholesale_price , $currVarPrice , $product , $user_wholesale_role );

                        $wholesalePriceHTML .= '<span style="display: block;" class="wholesale_price_container">
                                                    <span class="wholesale_price_title">' . $wholesalePriceTitleText . '</span>
                                                    <ins>' . WWP_Helper_Functions::wwp_formatted_price( $wholesale_price ) . WWP_Wholesale_Prices::get_wholesale_price_suffix( $product , $user_wholesale_role , $price_arr[ 'wholesale_price_with_no_tax' ] )  . '</ins>
                                                </span>';

                        $wholesalePriceHTML = apply_filters( 'wwp_filter_wholesale_price_html' , $wholesalePriceHTML , $currVarPrice , $variationProduct , $user_wholesale_role , $wholesalePriceTitleText , $wholesale_price , $source );

                        $wholesalePriceHTML = '<span class="price">' . $wholesalePriceHTML . '</span>';

                        $priceHTML = $wholesalePriceHTML;
                        $hasWholesalePrice = true;

                    } else {

                        $priceHTML = '<p class="price">' . WWP_Helper_Functions::wwp_formatted_price( $currVarPrice ) . $product->get_price_suffix() . '</p>';
                        $hasWholesalePrice = false;

                    }

                    $variations_arr[] =  array(
                                            'variation_id'        => $variation[ 'variation_id' ],
                                            'minimum_order'       => (int) $minimumOrder,
                                            'raw_regular_price'   => (float) $currVarPrice,
                                            'raw_wholesale_price' => (float) $wholesale_price,
                                            'price_html'          => $priceHTML,
                                            'has_wholesale_price' => $hasWholesalePrice
                                        );

                } ?>
                
                <script>

                    jQuery( document ).ready( function ( $ ) {

                        if ( $( '.bundled_item_cart_content[data-product_id="<?php echo $bundled_product_id; ?>"][data-bundle_id="<?php echo $bundled_item->bundle_id; ?>"]' ).find( ".wholesale_price_container" ).length <= 0 ) {

                            function update_variation_price_html() {

                                var WWPPVariableProductPageVars = { variations : <?php echo json_encode( $variations_arr ); ?> },
                                    $variations_form            = $( '.bundled_item_cart_content[data-product_id="<?php echo $bundled_product_id; ?>"][data-bundle_id="<?php echo $bundled_item->bundle_id; ?>"]' ),
                                    variation_id                = $variations_form.find( ".single_variation_wrap .variation_id" ).attr( 'value' ),
                                    $single_variation           = $variations_form.find( ".single_variation" ),
                                    $qty_field                  = $variations_form.find( ".variations_button .qty" );
                                
                                for ( var i = 0 ; i < WWPPVariableProductPageVars.variations.length ; i++ )
                                    if ( WWPPVariableProductPageVars.variations[ i ][ 'variation_id' ] == variation_id && $single_variation.find( ".price" ).length <= 0 )
                                        $single_variation.prepend( WWPPVariableProductPageVars.variations[ i ][ 'price_html' ] );
                                
                            }
                            
                            $( "body" ).on( "woocommerce_variation_has_changed" , '.bundled_item_cart_content[data-product_id="<?php echo $bundled_product_id; ?>"][data-bundle_id="<?php echo $bundled_item->bundle_id; ?>"]' , update_variation_price_html );
                            $( "body" ).on( "found_variation" , '.bundled_item_cart_content[data-product_id="<?php echo $bundled_product_id; ?>"][data-bundle_id="<?php echo $bundled_item->bundle_id; ?>"]' , update_variation_price_html ); // Only triggered on ajax complete

                        }

                    } );

                </script>
                
                <?php

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
         * @since 1.13.0
         * @access public
         */
        public function run() {

            if ( WWP_Helper_Functions::is_plugin_active( 'woocommerce-product-bundles/woocommerce-product-bundles.php' ) ) {

                add_action( 'woocommerce_product_options_pricing'     , array( $this , 'add_wholesale_price_fields' )         , 11 );
                add_action( 'woocommerce_process_product_meta_bundle' , array( $this , 'save_wholesale_price_fields' )        , 20 , 1 );
                add_action( 'woocommerce_process_product_meta_bundle' , array( $this , 'save_minimum_order_quantity_fields' ) , 20 , 1 );

                add_filter( 'woocommerce_bundle_price_data'  , array( $this , 'filter_bundle_product_base_price' ) , 10 , 2 );
                add_filter( 'woocommerce_bundled_item_price' , array( $this , 'filter_bundle_item_price' )         , 10 , 4 );

                add_filter( 'woocommerce_bundled_items' , array( $this , 'filter_bundled_items' ) , 10 , 2 );

                add_action( 'woocommerce_bundled_single_variation' , array( $this , 'filter_variable_bundle_variations_data' ) , 10 , 2 );
                add_action( 'woocommerce_bundled_single_variation' , array( $this , 'filter_per_variation_price_html' ) , 10 , 2 );

                // WC 2.7
                add_filter( 'wwp_third_party_product_id' , array( $this , 'get_product_id' ) , 10 , 2 );

                // Quick edit support
                add_filter( 'wwp_quick_edit_allowed_product_types' , array( $this , 'support_for_quick_edit_fields') , 10 , 2 );

            }

        }

    }

}

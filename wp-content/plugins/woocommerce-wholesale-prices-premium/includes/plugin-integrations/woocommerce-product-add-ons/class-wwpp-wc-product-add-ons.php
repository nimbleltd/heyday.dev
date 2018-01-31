<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_WC_Product_Addon' ) ) {

    /**
     * Model that houses the logic of integrating with 'WooCommerce Product Add-Ons Bundles' plugin.
     *
     * @since 1.13.0
     */
    class WWPP_WC_Product_Addon {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWPP_WC_Product_Addon.
         *
         * @since 1.13.0
         * @access private
         * @var WWPP_WC_Product_Addon
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
         * Model that houses the logic of wholesale prices.
         *
         * @since 1.13.0
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
         * WWPP_WC_Product_Addon constructor.
         *
         * @since 1.13.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_WC_Product_Addon model.
         */
        public function __construct( $dependencies ) {

            $this->_wwpp_wholesale_roles  = $dependencies[ 'WWPP_Wholesale_Roles' ];
            $this->_wwpp_wholesale_prices = $dependencies[ 'WWPP_Wholesale_Prices' ];

        }

        /**
         * Ensure that only one instance of WWPP_WC_Product_Addon is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.13.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_WC_Product_Addon model.
         * @return WWPP_WC_Product_Addon
         */
        public static function instance( $dependencies ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }

        /**
         * Add addon role visibility custom fields.
         *
         * @since 1.10.0
         * @since 1.13.0 Refactor codebase and Move to its own model.
         * @access public
         *
         * @param WP_Post $post  Product object.
         * @param array   $addon Addon data.
         * @param int     $loop  Loop counter.
         */
        public function add_wwpp_addon_group_visibility_custom_fields( $post , $addon , $loop ) {

            $all_wholesale_roles = $this->_wwpp_wholesale_roles->getAllRegisteredWholesaleRoles();

            // When adding new addon dynamically, lets reset the $addon[ 'wwpp-addon-group-role-visibility' ] entry
            // They have no hook we can attach to clear or reset custom fields we've added
            if ( $addon[ 'name' ] == '' )
                $addon[ 'wwpp-addon-group-role-visibility' ] = array();

            $filtered_wholesale_roles = array( 'non-wholesale-customer' => array( 'roleName' => __( 'Non-Wholesale Customer' , 'woocommerce-wholesale-prices-premium' ) ) );
            $filtered_wholesale_roles += $all_wholesale_roles; ?>

            <tr>
                <td class="wwpp-addon-group-role-visibility" colspan="2">

                    <label for="wwpp-addon-group-role-visibility_<?php echo $loop; ?>"><?php _e( 'Specify what user roles this addon is visible. Leave blank to make addon visible to all user roles.' ,  'woocommerce-wholesale-prices-premium' ); ?></label>

                    <select id="wwpp-addon-group-role-visibility_<?php echo $loop; ?>" name="wwpp-addon-group-role-visibility[<?php echo $loop; ?>][]" multiple="multiple" data-placeholder="Select user roles..." class="wwpp-addon-group-role-visibility chosen-select">
                        <?php foreach ( $filtered_wholesale_roles as $role_key => $role ) {

                            $selected = ( isset( $addon[ 'wwpp-addon-group-role-visibility' ] ) && is_array( $addon[ 'wwpp-addon-group-role-visibility' ] ) && in_array( $role_key , $addon[ 'wwpp-addon-group-role-visibility' ] ) ) ? 'selected="selected"' : ''; ?>

                            <option value="<?php echo $role_key; ?>" <?php echo $selected; ?>><?php echo $role[ 'roleName' ]; ?></option>

                        <?php } ?>
                    </select>

                </td>
            </tr>

            <?php

        }

        /**
         * Save addon role visibility custom fields.
         *
         * @since 1.10.0
         * @since 1.13.0 Move to its own model.
         * @access public
         *
         * @param array $data  Addon data.
         * @param int   $index Current addon index.
         */
        public function save_wwpp_addon_group_visibility_custom_fields( $data , $index ) {
            
            if ( isset( $_POST[ 'wwpp-addon-group-role-visibility' ][ $index ] ) )
                $data[ 'wwpp-addon-group-role-visibility' ] = $_POST[ 'wwpp-addon-group-role-visibility' ][ $index ];
            
            return $data;
            
        }

        /**
         * Filters addon groups from the front end.
         *
         * @since 1.10.0
         * @since 1.13.0 Refactor codebase and move to its model.
         * @access public
         *
         * @param array $addons Addon data.
         * @return array
         */
        public function filter_wwpp_addon_groups( $addons ) {

            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();

            // Check if user is an admin we will show all addon groups regardless of the role restrictions
            if ( !current_user_can( 'manage_options' ) ) {
                foreach ( $addons as $key => $addon ) {
                    if ( isset( $addon[ 'wwpp-addon-group-role-visibility' ] ) && !empty( $addon[ 'wwpp-addon-group-role-visibility' ] ) ) {

                        // Check the customer is logged in as a wholesale role OR if they are non-wholesale and we've specifically said to include non-wholesale in this addon group
                        $role_to_check = !empty( $user_wholesale_role ) ? $user_wholesale_role[ 0 ] : 'non-wholesale-customer';

                        // If the addon group has the role visibility meta set, then check to see if the current role is in the list
                        if ( !in_array( $role_to_check , $addon[ 'wwpp-addon-group-role-visibility' ] ) ) {
                            // Remove the addon group from processing
                            unset( $addons[ $key ] );
                        }
                    }
                }
            }

            return $addons;

        }

        /**
         * Change the product price attributes on the totals element that the addons plugin uses to calculate the grand total.
         * Fix the grand total calculation.
         *
         * @since 1.10.0
         * @since 1.13.0 Refactor codebase and move to its own model.
         * @since 1.14.6 Bug Fix. Grand total bug on variable products ( WWPP-416 ).
         * @access public
         *
         * @param int $product_id Product id.
         */
        public function change_wwpp_addon_product_price( $product_id ) {

            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();

            // Bit of a hack, but we need to adjust the price of the product to the wholesale price if we are logged in as a wholesale
            // customer. The addons plugin uses JS to calculate it on the fly on the front end and to do that it stores the original
            // product price on a special DIV near the add to cart button. There's no filters so the only way to get at it is via JS.
            if ( !empty( $user_wholesale_role ) ) {

                $product                     = wc_get_product( $product_id );
                $variations_wholesale_prices = null;

                if ( WWP_Helper_Functions::wwp_get_product_type( $product ) === "variable" ) {

                    $variations = WWP_Helper_Functions::wwp_get_variable_product_variations( $product );

                    foreach ( $variations as $variation ) {

                        $price_arr = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v2( $variation[ 'variation_id' ] , $user_wholesale_role );
                        $wholesale_price = $price_arr[ 'wholesale_price' ];

                        $variations_wholesale_prices[ $variation[ 'variation_id' ] ] = $wholesale_price;

                    }

                } else {

                    $price_arr       = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v2( $product_id , $user_wholesale_role );
                    $wholesale_price = $price_arr[ 'wholesale_price' ];

                }

                if ( $variations_wholesale_prices ) { // Update the "product_variations" data property of "variation_form" markup to contain wholesale price instead ?>
                    
                    <script type="text/javascript">

                        var variations_wholesale_prices = {<?php foreach( $variations_wholesale_prices as $var_id => $wholesale_price ) { echo "$var_id : $wholesale_price,"; } ?>},
                            product_variations_data     = jQuery( "form.variations_form" ).data( "product_variations" );
                        
                        if ( product_variations_data )
                            for ( var i = 0 ; i < product_variations_data.length ; i++ )
                                product_variations_data[ i ].display_price = variations_wholesale_prices[ product_variations_data[ i ].id ? product_variations_data[ i ].id : product_variations_data[ i ].variation_id ];
                        
                        jQuery( "form.variations_form" ).data( "product_variations" , product_variations_data );

                    </script>

                <?php } elseif ( !empty( $wholesale_price ) ) { ?>

                    <script type="text/javascript">
                        
                        // We need to run this on document ready, not immediately
                        // Coz now we are hooking on 9 execution order, so during this time #product-addons-total still not exists
                        jQuery( document ).ready( function() {

                            jQuery('#product-addons-total').data( 'price' , '<?php echo $wholesale_price ?>' );
                            jQuery('#product-addons-total').data( 'raw-price' , '<?php echo $wholesale_price ?>' );

                        } );

                    </script>

                <?php }
                
            }

        }

        /**
         * Apply product add-on on top of the calculated wholesale price.
         * 
         * @since 1.10.0
         * @since 1.13.0 Move to its own model.
         * @since 1.13.1 Bug fix. Return $wholesale_price instead of ''.
         * @access public
         *
         * @param array $wholesale_price_arr Wholesale price array data.
         * @param int   $product_id          Product id.
         * @param array $user_wholesale_role Array of wholesale roles.
         * @param array $cart_item           Cart item.
         * @return array Filtered wholesale price array data.
         */
        public function apply_addon_to_cart_items( $wholesale_price_arr , $product_id , $user_wholesale_role , $cart_item ) {

            // Adjust price if addons are set
            if ( !empty( $wholesale_price_arr[ 'wholesale_price' ] ) && ! empty( $cart_item[ 'addons' ] ) && apply_filters( 'woocommerce_product_addons_adjust_price' , true , $cart_item ) ) {

                $extra_cost = 0;

                foreach ( $cart_item[ 'addons' ] as $addon ) {
                    
                    if ( $addon[ 'price' ] > 0 )
                        $extra_cost += $addon[ 'price' ];

                }

                $wholesale_price_arr[ 'wholesale_price' ] += $extra_cost;

                return $wholesale_price_arr;

            } else
                return $wholesale_price_arr;
            
        }


        /**
         * Execute model.
         *
         * @since 1.13.0
         * @access public
         */
        public function run() {

            if ( WWP_Helper_Functions::is_plugin_active( 'woocommerce-product-addons/woocommerce-product-addons.php' ) ) {

                add_action( 'woocommerce_product_addons_panel_before_options' , array( $this , 'add_wwpp_addon_group_visibility_custom_fields' )  , 10 , 3 );
                add_filter( 'woocommerce_product_addons_save_data'            , array( $this , 'save_wwpp_addon_group_visibility_custom_fields' ) , 10 , 2 );
                add_action( 'get_product_addons'                              , array( $this , 'filter_wwpp_addon_groups' )                       , 10 , 1 );
                add_action( 'woocommerce-product-addons_end'                  , array( $this , 'change_wwpp_addon_product_price' )                , 9 , 1 ); // Well have to run our code first

                // Apply add on price on cart items
                // We run this late, we let wwpp to apply necessary wholesale pricing calculation, after that
                // we add the add-on price on top of the calculated wholesale price.
                add_filter( 'wwp_filter_wholesale_price_cart' , array( $this , 'apply_addon_to_cart_items' ) , 500 , 4 );

            }

        }

    }

}
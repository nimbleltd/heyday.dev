<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_WC_Composite_Product' ) ) {

    /**
     * Model that houses the logic of integrating with 'WooCommerce Composite Products' plugin.
     *
     * Composite products just inherits from simple product so that's why they are very similar.
     * So most of the codebase here are just reusing the codes from simple product.
     *
     * @since 1.13.0
     */
    class WWPP_WC_Composite_Product {

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
         * Model that houses logic  admin custom fields for simple products.
         *
         * @since 1.13.0
         * @access private
         * @var WWPP_Admin_Custom_Fields_Simple_Product
         */
        private $_wwpp_admin_custom_fields_simple_product;




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
         * Add composite product wholesale price field.
         *
         * @since 1.13.0
         * @access public
         */
        public function add_wholesale_price_fields() {

            global $post , $wc_wholesale_prices;

            $product = wc_get_product( $post->ID );

            if ( WWP_Helper_Functions::wwp_get_product_type( $product ) === 'composite' )
                $wc_wholesale_prices->wwp_admin_custom_fields_simple_product->add_wholesale_price_fields();

        }

        /**
         * Save composite product wholesale price.
         *
         * @since 1.12.0
         * @since 1.13.0 Refactor codebase and move to its new refactored model.
         * @access public
         *
         * @param int $post_id Composite product id.
         */
        public function save_wholesale_price_fields( $post_id ) {

            global $wc_wholesale_prices;

            $wc_wholesale_prices->wwp_admin_custom_fields_simple_product->save_wholesale_price_fields( $post_id , 'composite' );

        }

        /**
         * Save minimum order quantity custom field value for composite products on product edit page.
         *
         * @since 1.12.0
         * @since 1.13.0 Refactor codebase and move to its new refactored model.
         * @access public
         *
         * @param int $post_id Composite product id.
         */
        public function save_minimum_order_quantity_fields( $post_id ) {

            // Composite products are very similar to simple products in terms of their fields structure.
            // Therefore we can reuse the code we have on saving wholesale minimum order quantity for simple products to composite products.
            // BTW the adding of custom wholesale minimum order quantity field to composite products are already handled by this function 'add_minimum_order_quantity_fields' on 'WWPP_Admin_Custom_Fields_Simple_Product'. Read the desc of the function.
            $this->_wwpp_admin_custom_fields_simple_product->save_minimum_order_quantity_fields( $post_id , 'composite' );

        }

        /**
         * Show notice for single composite product page on the front end, near the add to cart button, stating
         * the current issue with composite products total price when set as per product pricing due to partial
         * integration of composite products.
         *
         * @since 1.12.0
         * @since 1.13.0 Move to its new refactored model.
         * @access public
         */
        public function show_composite_product_total_price_notice() {

            global $post;
            $product             = wc_get_product( $post->ID );
            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();

            // Only show on per product pricing set up.
            if ( $product->get_type() == 'composite' && !empty( $user_wholesale_role ) )
                _e( "<p><b>NOTE:</b> Components with wholesale prices are not reflected in this total. Please add to cart to see the final price with wholesale adjustments applied.</p>" , 'woocommerce-wholesale-prices-premium' );

        }

        /**
         * Filter the each composite component ui. Check if the current customer have access to the component and modify the ui if necessary.
         *
         * @since 1.12.5
         * @since 1.13.0 Move to its new refactored model.
         * @access public
         *
         * @param int                  $component_id Component Id.
         * @param WC_Product_Composite $product      Composite object.
         */
        public function filter_component_ui( $component_id , $product ) {

            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();
            $component           = $product->get_component( $component_id );
            $user_role           = !empty( $user_wholesale_role ) ? $user_wholesale_role[ 0 ] : 'all';

            if ( !$this->check_user_have_access_to_component_option( $this->get_component_options( $component ) , $user_role ) ) {

                // Some component option/s is not accessible to the current user.
                // Modify the component UI to indicate the issue.

                if ( $component[ 'optional' ] === 'yes' ) {

                    // This is a optional component so well just remove this totally
                    $this->remove_composite_component_ui( $component_id , $component , $product );

                } else {

                    // This is a required component, meaning, since they don't have access to this component and this component is required for this composite product
                    // then they can't add this composite product
                    // We have to modify the ui to inform them about this
                    $this->modify_composite_component_ui_to_not_available( $component_id , $component , $product );

                }

            }

        }

        /**
         * Check if a user role have access to certain composite product component option.
         * I used the component option term coz take note, a component can have multiple products ( options ).
         *
         * @since 1.12.5
         * @since 1.13.0 Move to its new refactored model.
         * @access public
         *
         * @param array  $component_options  Component options to traverse.
         * @param string $user_role_to_check User role to check if it has access to the component options.
         * @return boolean Flag that determines if a user have access to composite product component.
         */
        public function check_user_have_access_to_component_option( $component_options , $user_role_to_check = 'all' ) {

            if ( !is_array( $component_options ) )
                $component_options[] = (int) $component_options;

            foreach ( $component_options as $product_id ) {

                $curr_product_wholesale_filter = get_post_meta( $product_id , WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER );
                if ( !is_array( $curr_product_wholesale_filter ) )
                    $curr_product_wholesale_filter = array();

                if ( in_array( 'all' , $curr_product_wholesale_filter ) || in_array( $user_role_to_check , $curr_product_wholesale_filter ) )
                    continue;
                else
                    return false;

            }

            return true;

        }

        /**
         * Remove composite component user interface.
         *
         * @since 1.12.5
         * @since 1.13.0 Move to its new refactored model.
         * @access public
         *
         * @param int                  $component_id Component Id.
         * @param WC_CP_Component      $component    Component object.
         * @param WC_Product_Composite $product      Composite object.
         */
        public function remove_composite_component_ui( $component_id , $component , $product ) {

            ?>

            <script>
                jQuery( document ).ready( function( $ ) {

                    $( "#component_<?php echo $component_id; ?>" ).remove();

                } );
            </script>

            <?php

        }

        /**
         * Remove composite component user interface and replace it with a new UI.
         * UI basically warns end users that a required component is not accessible to them and they should contact admin.
         *
         * @since 1.12.5
         * @since 1.13.0 Move to its new refactored model.
         * @access public
         *
         * @param int                  $component_id Component Id.
         * @param WC_CP_Component      $component    Component object.
         * @param WC_Product_Composite $product      Composite object.
         */
        public function modify_composite_component_ui_to_not_available( $component_id , $component , $product ) {

            ?>

            <script>

                jQuery( document ).ready( function( $ ) {

                    $( "#component_<?php echo $component_id; ?>" )
                        .before( '<div class="unavailable-component <?php echo $component_id; ?>">' +
                                    '<p><?php echo sprintf( __( 'You do not have access to a required component <b>(%1$s)</b>. Please contact store owner.' , 'woocommerce-wholesale-prices-premium' ) , $component[ 'title' ] ); ?></p>' +
                                  '</div>' )
                        .remove();

                } );

            </script>

            <?php

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
                $allowed_product_types[] = 'composite';

            return $allowed_product_types;
        }

        /**
         * Execute model.
         *
         * @since 1.13.0
         * @access public
         */
        public function run() {

            if ( WWP_Helper_Functions::is_plugin_active( 'woocommerce-composite-products/woocommerce-composite-products.php' ) ) {

                add_action( 'woocommerce_product_options_pricing'        , array( $this , 'add_wholesale_price_fields' )                , 11 );
                add_action( 'woocommerce_process_product_meta_composite' , array( $this , 'save_wholesale_price_fields' )               , 20 , 1 );
                add_action( 'woocommerce_process_product_meta_composite' , array( $this , 'save_minimum_order_quantity_fields' )        , 20 , 1 );
                add_action( 'woocommerce_composite_add_to_cart_button'   , array( $this , 'show_composite_product_total_price_notice' ) , 10 );

                // Filter composite component UI
                // The purpose of these codebase is if a component of a composite product is exclusive only to certain wholesale roles
                // We must filter the components to only show what is appropriate to be shown based on the user's role
                add_action( 'woocommerce_composite_component_selections_paged'       , array( $this , 'filter_component_ui' ) , 10 , 2 );
                add_action( 'woocommerce_composite_component_selections_progressive' , array( $this , 'filter_component_ui' ) , 10 , 2 );
                add_action( 'woocommerce_composite_component_selections_single'      , array( $this , 'filter_component_ui' ) , 10 , 2 );

                // WC 2.7
                add_filter( 'wwp_third_party_product_id' , array( $this , 'get_product_id' ) , 10 , 2 );

                // Quick edit support
                add_filter( 'wwp_quick_edit_allowed_product_types' , array( $this , 'support_for_quick_edit_fields') , 10 , 2 );

            }

        }

        /**
         * Get component options
         *
         * @since 1.14.2
         * @access public
         *
         * @param WC_CP_Component $component component object
         */
        public function get_component_options( $component ) {

            if ( is_a( $component , 'WC_CP_Component' ) ) {

                $wccp_data = WWP_Helper_Functions::get_plugin_data( 'woocommerce-composite-products/woocommerce-composite-products.php' );

                if ( version_compare( $wccp_data[ 'Version' ] , '3.9.0' , '>=' ) )
                    return $component->get_options();
                else
                    return $component->options;

            } else {

                error_log( 'WWPP Error : WWPP_WC_Composite_Product::get_component_options method expect parameter $component of type WC_CP_Component.' );
                return 0;

            }
        }

    }

}

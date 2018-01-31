<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_Admin_Custom_Fields_Product' ) ) {
    
    /**
     * Model that houses logic  admin custom fields for all product types in general.
     * As of @since 1.13.0 it includes simple, variable, bundle and composite products.
     *
     * @since 1.13.0
     */
    class WWPP_Admin_Custom_Fields_Product {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWPP_Admin_Custom_Fields_Product.
         *
         * @since 1.13.0
         * @access private
         * @var WWPP_Admin_Custom_Fields_Product
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
         * Array of registered wholesale roles.
         *
         * @since 1.13.0
         * @access private
         * @var array
         */
        private $_registered_wholesale_roles;


        
        
        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        /**
         * WWPP_Admin_Custom_Fields_Product constructor.
         *
         * @since 1.13.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Admin_Custom_Fields_Product model.
         */
        public function __construct( $dependencies ) {

            $this->_wwpp_wholesale_roles  = $dependencies[ 'WWPP_Wholesale_Roles' ];

            $this->_registered_wholesale_roles = $this->_wwpp_wholesale_roles->getAllRegisteredWholesaleRoles();

        }

        /**
         * Ensure that only one instance of WWPP_Admin_Custom_Fields_Product is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.13.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Admin_Custom_Fields_Product model.
         * @return WWPP_Admin_Custom_Fields_Product
         */
        public static function instance( $dependencies ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }

        /**
         * Get curent user wholesale role.
         *
         * @since 1.13.0
         * @access private
         *
         * @return string User role string or empty string.
         */
        private function _get_current_user_wholesale_role() {

            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();
            
            return ( is_array( $user_wholesale_role ) && !empty( $user_wholesale_role ) ) ? $user_wholesale_role[ 0 ] : '';

        }




        /*
         |--------------------------------------------------------------------------------------------------------------
         | Add Custom Fields
         |--------------------------------------------------------------------------------------------------------------
         */

        /**
         * Add order quantity based wholesale pricing custom fields to simple products.
         * It also add this custom product types that are closely related to simple products.
         * As of v1.13.0 it includes bundle and composite products.
         *
         * @since 1.6.0
         * @since 1.13.0 Refactor codebase and its own model.
         * @access public
         */
        public function add_simple_product_quantity_based_wholesale_price_custom_field() {

            global $post;

            $this->_print_order_quantity_based_wholesale_pricing_controls( $post->ID , $this->_registered_wholesale_roles , 'simple' );

        }

        /**
         * Add order quantity based wholesale pricing custom fields to variable products.
         *
         * @since 1.6.0
         * @since 1.13.0 Refactor codebase and its own model.
         * @access public
         *
         * @param int     $loop           Loop counter.
         * @param array   $variation_data Array of variation data.
         * @param WP_Post $variation      Variation product object.
         */
        public function add_variation_product_quantity_based_wholesale_price_custom_field( $loop , $variation_data , $variation ) {

            $this->_print_order_quantity_based_wholesale_pricing_controls( $variation->ID , $this->_registered_wholesale_roles , 'variable' );

        }

        /**
         * Print order quantity based wholesale pricing custom fields.
         *
         * @since 1.6.0
         * @since 1.7.0 Add Aelia Currency Switcher Plugin Integration.
         * @since 1.13.0 Refactor codebase and its own model.
         * @access public
         *
         * @param int    $product_id      Product id.
         * @param array  $wholesale_roles Array of wholesale roles in which this custom controls are to be printed.
         * @param string $classes         Css class, also used to determine product type. Also note for variations, it will have a value of 'variable' although it should be variation. Long code history.
         */
        private function _print_order_quantity_based_wholesale_pricing_controls( $product_id , $wholesale_roles , $classes ) {

            $aelia_currency_switcher_active = WWPP_ACS_Integration_Helper::aelia_currency_switcher_active();

            if ( $aelia_currency_switcher_active ) {

                $currency_symbol        = "";
                $base_currency          = WWPP_ACS_Integration_Helper::get_product_base_currency( $product_id );
                $woocommerce_currencies = get_woocommerce_currencies();
                $enabled_currencies     = WWPP_ACS_Integration_Helper::enabled_currencies();

            } else
                $currency_symbol = " (" . get_woocommerce_currency_symbol() . ")";

            $wholesale_roles_arr = array();
            foreach ( $wholesale_roles as $roleKey => $role )
                $wholesale_roles_arr[ $roleKey ] = $role[ 'roleName' ];

            $pqbwp_enable = get_post_meta( $product_id , WWPP_POST_META_ENABLE_QUANTITY_DISCOUNT_RULE , true );

            $pqbwp_controls_styles = '';
            if ( $pqbwp_enable != 'yes' )
                $pqbwp_controls_styles = 'display: none;';

            $mapping = get_post_meta( $product_id , WWPP_POST_META_QUANTITY_DISCOUNT_RULE_MAPPING , true );
            if ( !is_array( $mapping ) )
                $mapping = array(); ?>

            <div class="product-quantity-based-wholesale-pricing options_group <?php echo $classes; ?>" >

                <header>

                    <h3 class="pqbwp-heading"><?php _e( 'Product Quantity Based Wholesale Pricing' ,  'woocommerce-wholesale-prices-premium' ); ?></h3>
                    <p class="pqbwp-desc">
                        <?php _e( 'Specify wholesale price for this current product depending on the quantity being purchased.<br><b>Ending Qty</b> can be left blank to apply that price for all quantities above the <b>Starting Qty.</b><br/>Only applies to the wholesale roles that you specify.' , 'woocommerce-wholesale-prices-premium' ); ?>
                    </p>

                    <?php if ( $aelia_currency_switcher_active ) { ?>

                        <p class="pbwp-desc">
                            <?php _e( 'Note: If you have not specify mapping for other currencies for a given wholesale role, it will derive its wholesale price automatically by converting the base currency wholesale price to that currency' , 'woocommerce-wholesale-prices-premium' ); ?>
                        </p>

                    <?php } ?>

                </header>

                <p class="form-field pqbwp-enable-field-container">

                    <span class="hidden post-id"><?php echo $product_id; ?></span>
                    <input type="checkbox" class="pqbwp-enable checkbox" value="yes" <?php echo ( $pqbwp_enable == 'yes' ) ? 'checked' : ''; ?> autocomplete="off">
                    <span class="description"><?php _e( "Enable further wholesale pricing discounts based on quantity purchased?" , "woocommerce-wholesale-prices-premium" ); ?></span>

                </p>

                <div class="processing-indicator"><span class="spinner"></span></div>

                <div class="pqbwp-controls" style="<?php echo $pqbwp_controls_styles; ?>">

                    <input type="hidden" class="mapping-index" value="">

                    <?php
                    // The fields below aren't really saved via woocommerce, we just used it here to house our rule controls.
                    // We use these to add our rule controls to abide with woocommerce styling.

                    woocommerce_wp_select(
                        array(
                            'id'                => 'pqbwp_registered_wholesale_roles',
                            'class'             => 'pqbwp_registered_wholesale_roles',
                            'label'             => __( 'Wholesale Role' , 'woocommerce-wholesale-prices-premium' ),
                            'placeholder'       => '',
                            'desc_tip'          => 'true',
                            'description'       => __( 'Select wholesale role to which this rule applies.' , 'woocommerce-wholesale-prices-premium' ),
                            'options'           => $wholesale_roles_arr,
                            'custom_attributes' => array( 'autocomplete' => 'off' )
                        )
                    );

                    woocommerce_wp_text_input(
                        array(
                            'id'                => 'pqbwp_minimum_order_quantity',
                            'class'             => 'pqbwp_minimum_order_quantity',
                            'label'             => __( 'Starting Qty' , 'woocommerce-wholesale-prices-premium' ),
                            'placeholder'       => '',
                            'desc_tip'          => 'true',
                            'description'       => __( 'Minimum order quantity required for this rule. Must be a number.' , 'woocommerce-wholesale-prices-premium' ),
                            'custom_attributes' => array( 'autocomplete' => 'off' )
                        )
                    );

                    woocommerce_wp_text_input(
                        array(
                            'id'                => 'pqbwp_maximum_order_quantity',
                            'class'             => 'pqbwp_maximum_order_quantity',
                            'label'             => __( 'Ending Qty' , 'woocommerce-wholesale-prices-premium' ),
                            'placeholder'       => '',
                            'desc_tip'          => 'true',
                            'description'       => __( 'Maximum order quantity required for this rule. Must be a number. Leave this blank for no maximum quantity.' , 'woocommerce-wholesale-prices-premium' ),
                            'custom_attributes' => array( 'autocomplete' => 'off' )
                        )
                    );

                    woocommerce_wp_select(
                        array(
                            'id'                => 'pqbwp_price_type',
                            'class'             => 'pqbwp_price_type',
                            'label'             => __( 'Price Type' , 'woocommerce-wholesale-prices-premium' ),
                            'placeholder'       => '',
                            'desc_tip'          => 'true',
                            'description'       => __( 'Select pricing type' , 'woocommerce-wholesale-prices-premium' ),
                            'options'           => array(
                                                        'fixed-price'   => __( 'Fixed Price' , 'woocommerce-wholesale-prices-premium' ),
                                                        'percent-price' => __( 'Discount % off the wholesale price' , 'woocommerce-wholesale-prices-premium' )
                                                    ),
                            'custom_attributes' => array( 'autocomplete' => 'off' )
                        )
                    );

                    woocommerce_wp_text_input(
                        array(
                            'id'                => 'pqbwp_wholesale_price',
                            'class'             => 'pqbwp_wholesale_price',
                            'label'             => sprintf( __( 'Wholesale Price%1$s' , 'woocommerce-wholesale-prices-premium' ) , $currency_symbol ),
                            'placeholder'       => '',
                            'desc_tip'          => 'true',
                            'description'       => __( '$ or the new % value off the regular price. This will be the discount value used for quantities within the given range.' , 'woocommerce-wholesale-prices-premium' ),
                            'data_type'         => 'price',
                            'custom_attributes' => array( 'autocomplete' => 'off' )
                        )
                    );

                    if ( $aelia_currency_switcher_active ) {

                        $currency_select_options = array();

                        foreach ( $enabled_currencies as $currency ) {

                            if ( $currency == $base_currency )
                                $text = $woocommerce_currencies[ $currency ] . " " . __( "(Base Currency)" , "woocommerce-wholesale-prices-premium" );
                            else
                                $text = $woocommerce_currencies[ $currency ];

                            $currency_select_options[ $currency ] = $text;

                        }

                        woocommerce_wp_select(
                            array(
                                'id'                => 'pqbwp_enabled_currencies',
                                'class'             => 'pqbwp_enabled_currencies',
                                'label'             => __( 'Currency' , 'woocommerce-wholesale-prices-premium' ),
                                'placeholder'       => '',
                                'desc_tip'          => 'true',
                                'description'       => __( 'Select Currency' , 'woocommerce-wholesale-prices-premium' ),
                                'options'           => $currency_select_options,
                                'value'             => $base_currency,
                                'custom_attributes' => array( 'autocomplete' => 'off' )
                            )
                        );

                    } ?>

                    <p class="form-field button-controls add-mode">

                        <input type="button" class="pqbwp-cancel button button-secondary" value="<?php _e( 'Cancel' , 'woocommerce-wholesale-prices-premium' ); ?>">
                        <input type="button" class="pqbwp-save-rule button button-primary" value="<?php _e( 'Save Quantity Discount Rule' , 'woocommerce-wholesale-prices-premium' ); ?>">
                        <input type="button" class="pqbwp-add-rule button button-primary" value="<?php _e( 'Add Quantity Discount Rule' , 'woocommerce-wholesale-prices-premium' ); ?>">
                        <span class="spinner"></span>

                        <div style="float: none; clear: both; display: block;"></div>

                    </p>

                    <div class="form-field table-mapping">
                        <table class="pqbwp-mapping wp-list-table widefat">

                            <thead>
                                <tr>
                                    <th><?php _e( 'Wholesale Role' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                                    <th><?php _e( 'Starting Qty' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                                    <th><?php _e( 'Ending Qty' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                                    <th><?php _e( 'Wholesale Price' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                                    <?php echo $aelia_currency_switcher_active ? "<th>" . __( 'Currency' , 'woocommerce-wholesale-prices-premium' ) . "</th>" : ""; ?>
                                    <th></th>
                                </tr>
                            </thead>

                            <tfoot>
                                <tr>
                                    <th><?php _e( 'Wholesale Role' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                                    <th><?php _e( 'Starting Qty' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                                    <th><?php _e( 'Ending Qty' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                                    <th><?php _e( 'Wholesale Price' , 'woocommerce-wholesale-prices-premium' ); ?></th>
                                    <?php echo $aelia_currency_switcher_active ? "<th>" . __( 'Currency' , 'woocommerce-wholesale-prices-premium' ) . "</th>" : ""; ?>
                                    <th></th>
                                </tr>
                            </tfoot>

                            <tbody>

                            <?php
                            if ( !empty( $mapping ) ) {

                                if ( $aelia_currency_switcher_active ) {

                                    $item_number = 0;

                                    foreach ( $mapping as $index => $map ) {

                                        foreach ( $enabled_currencies as $currency ) {

                                            if ( $currency == $base_currency ) {

                                                $wholesale_role_meta_key  = 'wholesale_role';
                                                $wholesale_price_meta_key = 'wholesale_price';
                                                $start_qty_meta_key       = 'start_qty';
                                                $end_qty_meta_key         = 'end_qty';

                                            } else {

                                                $wholesale_role_meta_key  = $currency . '_wholesale_role';
                                                $wholesale_price_meta_key = $currency . '_wholesale_price';
                                                $start_qty_meta_key       = $currency . '_start_qty';
                                                $end_qty_meta_key         = $currency . '_end_qty';

                                            }

                                            $args = array( 'currency' => $currency );

                                            if ( array_key_exists( $wholesale_role_meta_key , $map ) ) {

                                                $item_number++;

                                                // One key check is enough
                                                $this->_print_mapping_item( $item_number , $index , $map , $wholesale_roles , $wholesale_role_meta_key , $wholesale_price_meta_key , $start_qty_meta_key , $end_qty_meta_key , $aelia_currency_switcher_active , $args , $currency );

                                            }

                                        }

                                    }

                                } else {

                                    $item_number =   0;
                                    
                                    foreach ( $mapping as $index => $map ) {

                                        // Skip none base currency mapping
                                        if ( array_key_exists( 'currency' , $map ) )
                                            continue;

                                        $item_number++;

                                        $wholesale_role_meta_key  = 'wholesale_role';
                                        $wholesale_price_meta_key = 'wholesale_price';
                                        $start_qty_meta_key       = 'start_qty';
                                        $end_qty_meta_key         = 'end_qty';
                                        $args                     = array( 'currency' => get_woocommerce_currency() );

                                        $this->_print_mapping_item( $item_number , $index , $map , $wholesale_roles , $wholesale_role_meta_key , $wholesale_price_meta_key , $start_qty_meta_key , $end_qty_meta_key , false , $args );

                                    }

                                }

                            } else { ?>

                                <tr class="no-items">
                                    <td class="colspanchange" colspan="10"><?php _e( 'No Quantity Discount Rules Found' , 'woocommerce-wholesale-prices-premium' ); ?></td>
                                </tr>

                            <?php } ?>

                            </tbody>

                        </table><!--#pqbwp-mapping-->
                    </div>

                </div>

            </div><!--.product-quantity-based-wholesale-pricing-->

            <?php

        }

        /**
         * Print wholesale pricing mapping item.
         *
         * @since 1.7.0
         * @since 1.13.0 Refactor codebase and its own model.
         * @access public
         *
         * @param int         $item_number                    Entry item number. Item count.
         * @param int         $index                          Entry array index.
         * @param array       $map                            Mapping entry.
         * @param array       $wholesale_roles                Array of wholesale roles.
         * @param string      $wholesale_role_meta_key        Mapping wholesale role meta key.
         * @param string      $wholesale_role_price_key       Mapping wholesale price meta key.
         * @param int         $start_qty_meta_key             Starting quantity.
         * @param int         $end_qty_meta_key               Ending quantity.
         * @param boolean     $aelia_currency_switcher_active Flag that detemines if aelia currency switcher plugin is active.
         * @param array       $args                           Additional arguments data.
         * @param string|null $currency                       Currency.
         */
        private function _print_mapping_item( $item_number , $index , $map , $wholesale_roles , $wholesale_role_meta_key , $wholesale_role_price_key , $start_qty_meta_key , $end_qty_meta_key , $aelia_currency_switcher_active , $args , $currency = null ) {

            if ( $item_number % 2 == 0 )
                $row_class = "even";
            else
                $row_class = "odd alternate"; ?>

            <tr class="<?php echo $row_class; ?>">

                <td class="meta hidden">
                    <span class="index"><?php echo $index; ?></span>
                    <span class="wholesale-role"><?php echo $map[ $wholesale_role_meta_key ]; ?></span>
                    <span class="price-type"><?php echo isset( $map[ 'price_type' ] ) ? $map[ 'price_type' ] : 'fixed-price'; ?></span>
                    <span class="wholesale-price"><?php echo $map[ $wholesale_role_price_key ]; ?></span>
                </td>
                <td class="wholesale-role-text"><?php echo $wholesale_roles[ $map[ $wholesale_role_meta_key ] ][ 'roleName' ]; ?></td>
                <td class="start-qty"><?php echo $map[ $start_qty_meta_key ]; ?></td>
                <td class="end-qty"><?php echo $map[ $end_qty_meta_key ]; ?></td>
                <td class="wholesale-price-text">
                    <?php
                    if ( isset( $map[ 'price_type' ] ) ) {

                        if ( $map[ 'price_type' ] == 'fixed-price' )  
                            echo WWP_Helper_Functions::wwp_formatted_price( $map[ $wholesale_role_price_key ] , $args );
                        elseif ( $map[ 'price_type' ] == 'percent-price' )
                            echo $map[ $wholesale_role_price_key ] . '%';

                    } else
                        echo WWP_Helper_Functions::wwp_formatted_price( $map[ $wholesale_role_price_key ] , $args );
                    ?>
                </td>
                <?php if ( $aelia_currency_switcher_active ) { ?>
                    <td class="currency"><?php echo $currency; ?></td>
                <?php } ?>
                <td class="controls">
                    <a class="edit dashicons dashicons-edit"></a>
                    <a class="delete dashicons dashicons-no"></a>
                </td>

            </tr>

            <?php

        }
        


        
        /*
         |--------------------------------------------------------------------------------------------------------------
         | AJAX Interfaces
         |--------------------------------------------------------------------------------------------------------------
         */

        /**
         * AJAX interface to toggle product quantity based wholesale pricing feature of a product.
         *
         * @since 1.6.0
         * @since 1.13.0 Refactor codebase and its own model.
         * @access public
         * 
         * @param int|null    $post_id   Product id.
         * @param string|null $enable    'yes' or 'no'.
         * @param bool|true   $ajax_call Flag that detemines if this function is being called via ajax or not.
         * @return array If called not via ajax it returns array of response data.
         */
        public function wwpp_toggle_product_quantity_based_wholesale_pricing( $post_id = null , $enable = null , $ajax_call = true ) {

            if ( $ajax_call === true ) {

                $post_id = $_POST[ 'post_id' ];
                $enable  = $_POST[ 'enable' ];

            }

            update_post_meta( $post_id , WWPP_POST_META_ENABLE_QUANTITY_DISCOUNT_RULE , $enable );

            $response = array( 'status' => 'success' );

            if ( $ajax_call === true ) {

                header( 'Content-Type: application/json' );
                echo json_encode( $response );
                die();

            } else
                return $response;

        }

        /**
         * Add quantity discount rule. $rule parameter expected to have the following items below.
         *
         * wholesale_role
         * start_qty
         * end_qty
         * wholesale_price
         *
         * @since 1.6.0
         * @since 1.7.0 Add Aelia Currency Switcher Plugin Integration
         * @since 1.13.0 Refactor codebase and its own model.
         * @access public
         *
         * @param int|null   $post_id Product id.
         * @param array|null $rule    Rule data.
         * @param bool|true   $ajax_call Flag that detemines if this function is being called via ajax or not.
         * @return array If called not via ajax it returns array of response data.
         */
        public function wwpp_add_quantity_discount_rule( $post_id = null , $rule = null , $ajax_call = true ) {

            if ( $ajax_call === true ) {

                $rule = $_POST[ 'rule' ];
                $post_id = $_POST[ 'post_id' ];

            }

            $thousand_sep = get_option( 'woocommerce_price_thousand_sep' );
            $decimal_sep  = get_option( 'woocommerce_price_decimal_sep' );

            if ( $thousand_sep )
                $rule[ 'wholesale_price' ] = str_replace( $thousand_sep , '' , $rule[ 'wholesale_price' ] );

            if ( $decimal_sep )
                $rule[ 'wholesale_price' ] = str_replace( $decimal_sep , '.' , $rule[ 'wholesale_price' ] );

            // Check data format
            if ( !is_array( $rule ) || !isset( $post_id , $rule[ 'wholesale_role' ] , $rule[ 'start_qty' ] , $rule[ 'end_qty' ] , $rule[ 'price_type' ] , $rule[ 'wholesale_price' ] ) ) {

                $response = array(
                                'status'        => 'fail',
                                'error_message' => __( 'Quantity discount rule data passed is in invalid format.' , 'woocommerce-wholesale-prices-premium' )
                            );

            } else {

                // Check data validity
                $post_id                   = sanitize_text_field( $post_id );
                $rule[ 'wholesale_role' ]  = sanitize_text_field( $rule[ 'wholesale_role' ] );
                $rule[ 'start_qty' ]       = sanitize_text_field( $rule[ 'start_qty' ] );
                $rule[ 'end_qty' ]         = sanitize_text_field( $rule[ 'end_qty' ] );
                $rule[ 'price_type' ]      = sanitize_text_field( $rule[ 'price_type' ] );
                $rule[ 'wholesale_price' ] = sanitize_text_field( $rule[ 'wholesale_price' ] );

                if ( empty( $post_id ) || empty( $rule[ 'wholesale_role' ] ) || empty( $rule[ 'start_qty' ] ) || empty( $rule[ 'price_type' ] ) || empty( $rule[ 'wholesale_price' ] ) ) {

                    $response = array(
                        'status'        =>  'fail',
                        'error_message' =>  __( 'Quantity discount rule data passed is invalid. The following fields are required ( Wholesale Role / Starting Qty / Price Type / Wholesale Price ).' , 'woocommerce-wholesale-prices-premium' )
                    );

                } elseif ( !is_numeric( $rule[ 'start_qty' ] ) || !is_numeric( $rule[ 'wholesale_price' ] ) || ( !empty( $rule[ 'end_qty' ] ) && !is_numeric( $rule[ 'end_qty' ] ) ) ) {

                    $response = array(
                        'status'        =>  'fail',
                        'error_message' =>  __( 'Quantity discount rule data passed is invalid. The following fields must be a number ( Starting Qty / Ending Qty / Wholesale Price ).' , 'woocommerce-wholesale-prices-premium' )
                    );

                } elseif ( !empty( $rule[ 'end_qty' ] ) && $rule[ 'end_qty' ] < $rule[ 'start_qty' ] ) {

                    $response = array(
                        'status'        =>  'fail',
                        'error_message' =>  __( 'Ending Qty must not be less than Starting Qty' , 'woocommerce-wholesale-prices-premium' )
                    );

                } else {

                    $rule[ 'wholesale_price' ] = wc_format_decimal( $rule[ 'wholesale_price' ] );

                    if ( $rule[ 'wholesale_price' ] < 0 )
                        $rule[ 'wholesale_price' ] = 0;

                    $quantity_discount_rule_mapping = get_post_meta( $post_id , WWPP_POST_META_QUANTITY_DISCOUNT_RULE_MAPPING , true );
                    if ( !is_array( $quantity_discount_rule_mapping ) )
                        $quantity_discount_rule_mapping = array();

                    $dup               = false;
                    $start_qty_overlap = false;
                    $end_qty_overlap   = false;
                    $err_indexes       = array();

                    if ( WWPP_ACS_Integration_Helper::aelia_currency_switcher_active() ) {

                        $base_currency = WWPP_ACS_Integration_Helper::get_product_base_currency( $post_id );

                        if ( $rule[ 'currency' ] == $base_currency ) {

                            $wholesale_role_meta_key = 'wholesale_role';
                            $start_qty_meta_key      = 'start_qty';
                            $end_qty_meta_key        = 'end_qty';

                        } else {

                            $wholesale_role_meta_key = $rule[ 'currency' ] . '_wholesale_role';
                            $start_qty_meta_key      = $rule[ 'currency' ] . '_start_qty';
                            $end_qty_meta_key        = $rule[ 'currency' ] . '_end_qty';

                        }

                    } else {

                        $wholesale_role_meta_key = 'wholesale_role';
                        $start_qty_meta_key      = 'start_qty';
                        $end_qty_meta_key        = 'end_qty';

                    }

                    foreach ( $quantity_discount_rule_mapping as $idx => $mapping ) {

                        if ( !array_key_exists( $wholesale_role_meta_key , $mapping ) )
                            continue; // One key to check is enough
                        else {

                            if ( $mapping[ $wholesale_role_meta_key ] == $rule[ 'wholesale_role' ] ) {

                                // If it has the same wholesale role and starting quantity then they are considered as the duplicate
                                if ( $mapping[ $start_qty_meta_key ] == $rule[ 'start_qty' ] && !$dup ) {

                                    $dup = true;
                                    if ( !in_array( $idx , $err_indexes ) )
                                        $err_indexes[] = $idx;

                                }

                                // Check for overlapping mappings. Only do this if no dup yet

                                if ( !$dup ) {

                                    if ( $rule[ 'start_qty' ] > $mapping[ $start_qty_meta_key ] && $rule[ 'start_qty' ] <= $mapping[ $end_qty_meta_key ] && $start_qty_overlap == false ) {

                                        $start_qty_overlap = true;
                                        if ( !in_array( $idx , $err_indexes ) )
                                            $err_indexes[] = $idx;

                                    }

                                    if ( $rule[ 'end_qty' ] <= $mapping[ $end_qty_meta_key ] && $rule[ 'end_qty' ] >= $mapping[ $start_qty_meta_key ] && $end_qty_overlap == false ) {

                                        $end_qty_overlap = true;
                                        if ( !in_array( $idx , $err_indexes ) )
                                            $err_indexes[] = $idx;

                                    }

                                }

                            }

                        }

                        // break loop if there is dup or overlap
                        if ( $dup || ( $start_qty_overlap && $end_qty_overlap ) )
                            break;

                    } // foreach ( $quantity_discount_rule_mapping as $idx => $mapping )

                    if ( $dup ) {

                        $response = array(
                            'status'          => 'fail',
                            'error_message'   => __( 'Duplicate quantity discount rule' , 'woocommerce-wholesale-prices-premium' ),
                            'additional_data' => array( 'dup_index' => $err_indexes )
                        );

                    } elseif ( $start_qty_overlap && $end_qty_overlap ) {

                        $response = array(
                            'status'          => 'fail',
                            'error_message'   => __( 'Overlap quantity discount rule' , 'woocommerce-wholesale-prices-premium' ),
                            'additional_data' => array( 'dup_index' => $err_indexes )
                        );

                    } else {

                        $args = array();

                        // We could be changing the key for this so we cached this here
                        $wholesale_price = $rule[ 'wholesale_price' ];
                        $price_type      = $rule[ 'price_type' ];

                        if ( WWPP_ACS_Integration_Helper::aelia_currency_switcher_active() ) {

                            $base_currency = WWPP_ACS_Integration_Helper::get_product_base_currency( $post_id );

                            $args[ 'currency' ] = $rule[ 'currency' ];

                            if ( $rule[ 'currency' ] == $base_currency ) {

                                /*
                                 * Remove currency for base currency mapping. This is of compatibility reasons.
                                 * We want to make wwpp work with or without aelia currency switcher plugin.
                                 * We use the default keys here for base currency.
                                 */
                                unset( $rule[ 'currency' ] );

                            } else {

                                /*
                                 * For other currencies (not base currency) we modify the keys and append the currency code.
                                 * We do this for compatibility reasons. We don't want this to have the same keys as the
                                 * base currency. Coz what if Aelia was removed later? WWPP will not know what mapping to use
                                 * coz they have all the same keys.
                                 *
                                 * Note: exception here is the $rule[ 'currency' ]. We are not using 'currency' key before so
                                 * we can get away of not renaming that. Also we need not to rename this due to functionality
                                 * reasons.
                                 */
                                $rule = array(
                                    $rule[ 'currency' ] . '_wholesale_role'  => $rule[ 'wholesale_role' ],
                                    $rule[ 'currency' ] . '_start_qty'       => $rule[ 'start_qty' ],
                                    $rule[ 'currency' ] . '_end_qty'         => $rule[ 'end_qty' ],
                                    $rule[ 'currency' ] . '_price_type'      => $rule[ 'price_type' ],
                                    $rule[ 'currency' ] . '_wholesale_price' => $rule[ 'wholesale_price' ],
                                    'currency'                               => $rule[ 'currency' ]
                                );

                            }

                        }

                        $quantity_discount_rule_mapping[] = $rule;

                        update_post_meta( $post_id , WWPP_POST_META_QUANTITY_DISCOUNT_RULE_MAPPING , $quantity_discount_rule_mapping );

                        end( $quantity_discount_rule_mapping );
                        $last_inserted_item_index = key( $quantity_discount_rule_mapping );
                        
                        if ( $price_type == 'fixed-price' )
                            $wholesale_price_text = WWP_Helper_Functions::wwp_formatted_price( $wholesale_price , $args );
                        elseif ( $price_type == 'percent-price' )
                            $wholesale_price_text = $wholesale_price . "%";
                        
                        $response = array(
                            'status'                   => 'success',
                            'last_inserted_item_index' => $last_inserted_item_index,
                            'wholesale_price_text'     => $wholesale_price_text
                        );
                        
                    }

                }

            }

            if ( $ajax_call === true ) {

                header( 'Content-Type: application/json' );
                echo json_encode( $response );
                die();

            } else
                return $response;

        }

        /**
         * Edit quantity discount rule. $rule parameter expected to have the following items below.
         *
         * wholesale_role
         * start_qty
         * end_qty
         * wholesale_price
         *
         * @since 1.6.0
         * @since 1.13.0 Refactor codebase and its own model.
         * @access public
         * 
         * @param int|null   $post_id   Product id.
         * @param int|null   $index     Entry index.   
         * @param array|null $rule      Rule data.
         * @param bool|true  $ajax_call Flag that detemines if this function is being called via ajax or not.
         * @return array If called not via ajax it returns array of response data.
         */
        public function wwpp_save_quantity_discount_rule( $post_id = null , $index = null , $rule = null , $ajax_call = true  ) {

            if ( $ajax_call === true ) {

                $rule    = $_POST[ 'rule' ];
                $index   = $_POST[ 'index' ];
                $post_id = $_POST[ 'post_id' ];

            }

            $thousand_sep = get_option( 'woocommerce_price_thousand_sep' );
            $decimal_sep  = get_option( 'woocommerce_price_decimal_sep' );

            if ( $thousand_sep )
                $rule[ 'wholesale_price' ] = str_replace( $thousand_sep , '' , $rule[ 'wholesale_price' ] );

            if ( $decimal_sep )
                $rule[ 'wholesale_price' ] = str_replace( $decimal_sep , '.' , $rule[ 'wholesale_price' ] );

            // Check data format
            if ( !is_array( $rule ) || !isset( $post_id , $index , $rule[ 'wholesale_role' ] , $rule[ 'start_qty' ] , $rule[ 'end_qty' ] , $rule[ 'price_type' ] ,  $rule[ 'wholesale_price' ] ) ) {

                $response = array(
                    'status'        => 'fail',
                    'error_message' => __( 'Quantity discount rule data passed is in invalid format.' , 'woocommerce-wholesale-prices-premium' )
                );

            } else {

                // Check data validity
                $post_id                   = sanitize_text_field( $post_id );
                $index                     = sanitize_text_field( $index );
                $rule[ 'wholesale_role' ]  = sanitize_text_field( $rule[ 'wholesale_role' ] );
                $rule[ 'start_qty' ]       = sanitize_text_field( $rule[ 'start_qty' ] );
                $rule[ 'end_qty' ]         = sanitize_text_field( $rule[ 'end_qty' ] );
                $rule[ 'price_type' ]      = sanitize_text_field( $rule[ 'price_type' ] );
                $rule[ 'wholesale_price' ] = sanitize_text_field( $rule[ 'wholesale_price' ] );

                if ( empty( $post_id ) || $index == '' || empty( $rule[ 'wholesale_role' ] ) || empty( $rule[ 'start_qty' ] ) || empty( $rule[ 'price_type' ] ) || empty( $rule[ 'wholesale_price' ] ) ) {

                    $response = array(
                        'status'        =>  'fail',
                        'error_message' =>  __( 'Quantity discount rule data passed is invalid. The following fields are required ( Wholesale Role / Starting Qty / Price Type / Wholesale Price ).' , 'woocommerce-wholesale-prices-premium' )
                    );

                } elseif ( !is_numeric( $rule[ 'start_qty' ] ) || !is_numeric( $rule[ 'wholesale_price' ] ) || ( !empty( $rule[ 'end_qty' ] ) && !is_numeric( $rule[ 'end_qty' ] ) ) ) {

                    $response = array(
                        'status'        =>  'fail',
                        'error_message' =>  __( 'Quantity discount rule data passed is invalid. The following fields must be a number ( Starting Qty / Ending Qty / Wholesale Price ).' , 'woocommerce-wholesale-prices-premium' )
                    );

                } elseif ( !empty( $rule[ 'end_qty' ] ) && $rule[ 'end_qty' ] < $rule[ 'start_qty' ] ) {

                    $response = array(
                        'status'        =>  'fail',
                        'error_message' =>  __( 'Ending Qty must not be less than Starting Qty' , 'woocommerce-wholesale-prices-premium' )
                    );

                }  else {

                    $quantity_discount_rule_mapping = get_post_meta( $post_id , WWPP_POST_META_QUANTITY_DISCOUNT_RULE_MAPPING , true );
                    if ( !is_array( $quantity_discount_rule_mapping ) )
                        $quantity_discount_rule_mapping = array();

                    if ( !array_key_exists( $index , $quantity_discount_rule_mapping ) ) {

                        $response = array(
                                        'status'        => 'fail',
                                        'error_message' => __( 'Quantity discount rule entry you want to edit does not exist' , 'woocommerce-wholesale-prices-premium' )
                                    );

                    } else {

                        $rule[ 'wholesale_price' ] = wc_format_decimal( $rule[ 'wholesale_price' ] );

                        if ( $rule[ 'wholesale_price' ] < 0 )
                            $rule[ 'wholesale_price' ] = 0;

                        $dup               = false;
                        $start_qty_overlap = false;
                        $end_qty_overlap   = false;
                        $err_indexes       = array();

                        if ( WWPP_ACS_Integration_Helper::aelia_currency_switcher_active() ) {

                            $base_currency = WWPP_ACS_Integration_Helper::get_product_base_currency( $post_id );

                            if ( $rule[ 'currency' ] == $base_currency ) {

                                $wholesale_role_meta_key = 'wholesale_role';
                                $start_qty_meta_key      = 'start_qty';
                                $end_qty_meta_key        = 'end_qty';

                            } else {

                                $wholesale_role_meta_key = $rule[ 'currency' ] . '_wholesale_role';
                                $start_qty_meta_key      = $rule[ 'currency' ] . '_start_qty';
                                $end_qty_meta_key        = $rule[ 'currency' ] . '_end_qty';

                            }

                        } else {

                            $wholesale_role_meta_key = 'wholesale_role';
                            $start_qty_meta_key      = 'start_qty';
                            $end_qty_meta_key        = 'end_qty';

                        }

                        foreach ( $quantity_discount_rule_mapping as $idx => $mapping ) {

                            if ( !array_key_exists( $wholesale_role_meta_key , $mapping ) )
                                continue; // One meta key check is enough
                            else {

                                if ( $mapping[ $wholesale_role_meta_key ] == $rule[ 'wholesale_role' ] ) {

                                    // If it has the same wholesale role and starting quantity then they are considered as the duplicate
                                    // Since this is an edit, we need to check too if this is not the same entry as we are editing
                                    if ( $mapping[ $start_qty_meta_key ] == $rule[ 'start_qty' ] && $index != $idx && !$dup ) {

                                        $dup = true;
                                        if ( !in_array( $idx , $err_indexes ) )
                                            $err_indexes[] = $idx;

                                    }

                                    // Check for overlapping mappings. Only do this if no dup yet

                                    if ( !$dup && $index != $idx ) {

                                        if ( $rule[ 'start_qty' ] >= $mapping[ $start_qty_meta_key ] && $rule[ 'start_qty' ] <= $mapping[ $end_qty_meta_key ] && $start_qty_overlap == false ) {

                                            $start_qty_overlap = true;
                                            if ( !in_array( $idx , $err_indexes ) )
                                                $err_indexes[] = $idx;

                                        }

                                        if ( $rule[ 'end_qty' ] <= $mapping[ $end_qty_meta_key ] && $rule[ 'end_qty' ] >= $mapping[ $start_qty_meta_key ] && $end_qty_overlap == false ) {

                                            $end_qty_overlap = true;
                                            if ( !in_array( $idx , $err_indexes ) )
                                                $err_indexes[] = $idx;

                                        }

                                    }

                                }

                            }

                            // break loop if there is dup or overlap
                            if ( $dup || ( $start_qty_overlap && $end_qty_overlap ) )
                                break;

                        } // foreach ( $quantity_discount_rule_mapping as $idx => $mapping )

                        if ( $dup ) {

                            $response = array(
                                'status'          => 'fail',
                                'error_message'   => __( 'Duplicate quantity discount rule' , 'woocommerce-wholesale-prices-premium' ),
                                'additional_data' => array( 'dup_index' => $err_indexes )
                            );

                        } elseif ( $start_qty_overlap && $end_qty_overlap ) {

                            $response = array(
                                'status'          => 'fail',
                                'error_message'   => __( 'Overlap quantity discount rule' , 'woocommerce-wholesale-prices-premium' ),
                                'additional_data' => array( 'dup_index' => $err_indexes )
                            );

                        } else {

                            $args = array();

                            // We could be changing the key for this so we cached this here
                            $wholesale_price = $rule[ 'wholesale_price' ];
                            $price_type      = $rule[ 'price_type' ];

                            if ( WWPP_ACS_Integration_Helper::aelia_currency_switcher_active() ) {

                                $base_currency = WWPP_ACS_Integration_Helper::get_product_base_currency( $post_id );

                                $args[ 'currency' ] = $rule[ 'currency' ];

                                if ( $rule[ 'currency' ] == $base_currency ) {

                                    /*
                                     * Remove currency for base currency mapping. This is of compatibility reasons.
                                     * We want to make wwpp work with or without aelia currency switcher plugin.
                                     * We use the default keys here for base currency.
                                     */
                                    unset( $rule[ 'currency' ] );

                                } else {

                                    /*
                                     * For other currencies (not base currency) we modify the keys and append the currency code.
                                     * We do this for compatibility reasons. We don't want this to have the same keys as the
                                     * base currency. Coz what if Aelia was removed later? WWPP will not know what mapping to use
                                     * coz they have all the same keys.
                                     *
                                     * Note: exception here is the $rule[ 'currency' ]. We are not using 'currency' key before so
                                     * we can get away of not renaming that. Also we need not to rename this due to functionality
                                     * reasons.
                                     */
                                    $rule = array(
                                        $rule[ 'currency' ] . '_wholesale_role'  => $rule[ 'wholesale_role' ],
                                        $rule[ 'currency' ] . '_start_qty'       => $rule[ 'start_qty' ],
                                        $rule[ 'currency' ] . '_end_qty'         => $rule[ 'end_qty' ],
                                        $rule[ 'currency' ] . '_price_type'      => $rule[ 'price_type' ],
                                        $rule[ 'currency' ] . '_wholesale_price' => $rule[ 'wholesale_price' ],
                                        'currency'                               => $rule[ 'currency' ]
                                    );

                                }

                            }

                            $quantity_discount_rule_mapping[ $index ] = $rule;

                            update_post_meta( $post_id , WWPP_POST_META_QUANTITY_DISCOUNT_RULE_MAPPING , $quantity_discount_rule_mapping );

                            if ( $price_type == 'fixed-price' )
                                $wholesale_price_text = WWP_Helper_Functions::wwp_formatted_price( $wholesale_price , $args );
                            elseif ( $price_type == 'percent-price' )
                                $wholesale_price_text = $wholesale_price . "%";

                            $response = array(
                                            'status'               => 'success',
                                            'wholesale_price_text' => $wholesale_price_text
                                        );

                        }

                    }

                }

            }

            if ( $ajax_call === true ) {

                header( 'Content-Type: application/json' );
                echo json_encode( $response );
                die();

            } else
                return $response;

        }

        /**
         * Delete quantity discount rule.
         *
         * @since 1.6.0
         * @since 1.13.0 Refactor codebase and its own model.
         * @access public
         *
         * @param int|null   $post_id   Product id.
         * @param int|null   $index     Entry index.  
         * @param bool|true  $ajax_call Flag that detemines if this function is being called via ajax or not.
         * @return array If called not via ajax it returns array of response data.
         */
        public function wwpp_delete_quantity_discount_rule( $post_id = null , $index = null , $ajax_call = true ) {

            if ( $ajax_call === true ) {

                $post_id = $_POST[ 'post_id' ];
                $index   = $_POST[ 'index' ];

            }

            $quantity_discount_rule_mapping = get_post_meta( $post_id , WWPP_POST_META_QUANTITY_DISCOUNT_RULE_MAPPING , true );
            if ( !is_array( $quantity_discount_rule_mapping ) )
                $quantity_discount_rule_mapping = array();

            if ( !array_key_exists( $index , $quantity_discount_rule_mapping ) ) {

                $response = array(
                                'status'        => 'fail',
                                'error_message' => __( 'Quantity discount rule entry you want to delete does not exist' , 'woocommerce-wholesale-prices-premium' )
                            );

            } else {

                unset( $quantity_discount_rule_mapping[ $index ] );

                update_post_meta( $post_id , WWPP_POST_META_QUANTITY_DISCOUNT_RULE_MAPPING , $quantity_discount_rule_mapping );

                $response = array( 'status' => 'success' );

            }

            if ( $ajax_call === true ) {

                header( 'Content-Type: application/json' );
                echo json_encode( $response );
                die();

            } else
                return $response;

        }

        /**
         * Register ajax handlers.
         *
         * @since 1.13.0
         * @access public
         */
        public function register_ajax_handlers() {

            add_action( "wp_ajax_wwppToggleProductQuantityBasedWholesalePricing" , array( $this , 'wwpp_toggle_product_quantity_based_wholesale_pricing' ) );
            add_action( "wp_ajax_wwppAddQuantityDiscountRule"                    , array( $this , 'wwpp_add_quantity_discount_rule' ) );
            add_action( "wp_ajax_wwppSaveQuantityDiscountRule"                   , array( $this , 'wwpp_save_quantity_discount_rule' ) );
            add_action( "wp_ajax_wwppDeleteQuantityDiscountRule"                 , array( $this , 'wwpp_delete_quantity_discount_rule' ) );

        }


        /**
         * Execute model.
         *
         * @since 1.13.0
         * @access public
         */
        public function run() {

            add_action( 'woocommerce_product_options_pricing'           , array( $this , 'add_simple_product_quantity_based_wholesale_price_custom_field' )    , 30 , 1 );
            add_action( 'woocommerce_product_after_variable_attributes' , array( $this , 'add_variation_product_quantity_based_wholesale_price_custom_field' ) , 30 , 3 );
            
            add_action( 'init' , array( $this , 'register_ajax_handlers' ) );

        }

    }

}
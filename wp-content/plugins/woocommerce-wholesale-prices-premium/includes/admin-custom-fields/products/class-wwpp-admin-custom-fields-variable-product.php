<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_Admin_Custom_Fields_Variable_Product' ) ) {

    /**
     * Model that houses logic  admin custom fields for variable products.
     * 
     * @since 1.13.0
     */
    class WWPP_Admin_Custom_Fields_Variable_Product {

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

        /**
         * Model that houses the logic of wholesale prices.
         *
         * @since 1.13.0
         * @access private
         * @var WWPP_Wholesale_Prices
         */
        private $_wwpp_wholesale_prices;

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
         * WWPP_Admin_Custom_Fields_Variable_Product constructor.
         *
         * @since 1.13.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Admin_Custom_Fields_Variable_Product model.
         */
        public function __construct( $dependencies ) {

            $this->_wwpp_wholesale_roles  = $dependencies[ 'WWPP_Wholesale_Roles' ];
            $this->_wwpp_wholesale_prices = $dependencies[ 'WWPP_Wholesale_Prices' ];

            $this->_registered_wholesale_roles = $this->_wwpp_wholesale_roles->getAllRegisteredWholesaleRoles();

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
        |--------------------------------------------------------------------------
        | Wholesale Exclusive Variation
        |--------------------------------------------------------------------------
        */

        /**
         * Add wholesale users exclusive variation custom field to variable products on product edit screen.
         * Custom fields are added per variation, not to the parent variable product.
         *
         * @since 1.3.0
         * @since 1.13.0 Refactor codebase and make it use select box now instead of separate checkboxes per role. Move to its own model.
         * @access public
         * 
         * @param int     $loop           Loop counter.
         * @param array   $variation_data Array of variation data.
         * @param WP_Post $variation      Variation product object.
         */
        public function add_variation_wholesale_role_visibility_filter_field( $loop , $variation_data , $variation ) {

            global $woocommerce, $post;

            // Get the variable product data manually
            // Don't rely on the variation data woocommerce supplied
            // There is a logic change introduced on 2.3 series where they only send variation data (or variation meta)
            // That is built in to woocommerce, so all custom variation meta added to a variable product don't get passed along
            $option_value = get_post_meta( $variation->ID , WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER ); ?>

            <div class="wholesale-exclusive-variation-options-group options-group options_group" style="border-top: 1px solid #DDDDDD; padding-bottom: 15px;">
            
                <script>
                    jQuery( document ).ready( function( $ ) { $( ".chosen-select" ).chosen(); } );
                </script>

                <style>
                    .chosen-container-multi,
                    .chosen-container-multi input[type='text'] {
                        width: 100% !important;
                    }
                </style>

                <header class="form-row form-row-full">
                    <h4 style="font-size: 14px; margin: 10px 0;"><?php _e( 'Wholesale Exclusive Variation' , 'woocommerce-wholesale-prices-premium' ); ?></h4>
                    <p style="margin:0; padding:0; line-height: 16px; font-style: italic; font-size: 13px;"><?php _e( "Specify if this variation should be exclusive to wholesale roles. Leave empty to make it available to all.<br><br>" , 'woocommerce-wholesale-prices-premium' ); ?></p>
                </header>

                <div class="form-row form-row-full" style="position: relative;">
                    <select multiple name="<?php echo WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER . '[' . $loop . '][]'; ?>" class="chosen-select" style="width: 100%;" data-placeholder="<?php _e( 'Choose wholesale users...' , 'woocommerce-wholesale-prices-premium' ); ?>">
                        <?php foreach ( $this->_registered_wholesale_roles as $role_key => $role ) { ?>
                            <option value="<?php echo $role_key; ?>" <?php echo in_array( $role_key , $option_value ) ? 'selected' : ''; ?>><?php echo $role[ 'roleName' ]; ?></option>
                        <?php } ?>
                    </select>
                </div>

            </div><!--.options_group-->

            <?php

        }

        /**
         * Save wholesale exclusive variation custom field for variable products on product edit page.
         *
         * @since 1.3.0
         * @since 1.13.0 Refactor codebase and make it use select box now instead of separate checkboxes per role. Move to its own model.
         * @access public
         * 
         * @param int $post_id Variable product id.
         */
        public function save_variation_wholesale_role_visibility_filter_field( $post_id ) {

            global $_POST;

            if ( isset( $_POST[ 'variable_sku' ] ) ) {

                $variable_post_id = $_POST[ 'variable_post_id' ];
                $max_loop         = max( array_keys( $variable_post_id ) );

                for ( $i = 0; $i <= $max_loop; $i++ ) {

                    if ( !isset( $variable_post_id[ $i ] ) )
                        continue;
                    
                    $variation_id = (int) $variable_post_id[ $i ];

                    delete_post_meta( $variation_id , WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER );

                    if ( isset( $_POST[ WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER ][ $i ] ) ) {

                        if ( is_array( $_POST[ WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER ][ $i ] ) ) {

                            foreach ( $_POST[ WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER ][ $i ] as $role_key )
                                add_post_meta( $variation_id , WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER , $role_key );

                        } else
                            add_post_meta( $variation_id , WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER , $_POST[ WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER ][ $i ] );

                    } else
                        add_post_meta( $variation_id , WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER , 'all' );
                    
                }

            }

        }

        /**
         * Apply filter to only show variations of a variable product in the proper time and place.
         * ( Only show variations with wholesale price on wholesale users if setting is enabled )
         * ( Only show variations to appropriate wholesale users if it is set to be exclusively visible to certain wholesale roles )
         *
         * @since 1.6.0
         * @since 1.13.0 Refactor codebase and make it use select box now instead of separate checkboxes per role. Move to its own model.
         * 
         * @param boolean      $visible       Flag that detemines if the current variation is visible to the current user or not.
         * @param int          $variation_id  Variation id.
         * @param int          $variable_id   Variable id.
         * @param WC_Variation $variation_obj Variation object.
         * @return boolean Modified flag that detemines if the current varition is visible to the current user or not.
         */
        public function filter_variation_visibility( $visible , $variation_id , $variable_id , $variation_obj ) {
            
            return $this->filter_variation_availability( $visible , $variation_id );

        }

        /**
         * Apply filter to only make variations of a variable product purchasable in the proper time and place.
         * ( Only make variations purchasable with wholesale price on wholesale users if setting is enabled )
         * ( Only make variations purchasable to appropriate wholesale users if it is set to be exclusively visible to certain wholesale roles )
         * 
         * @since 1.13.0
         * @access public
         *
         * @param boolean              $purchasable   Flag that determines if variation is purchasable.
         * @param WC_Product_Variation $variation_obj Variation product object.
         * @return boolean Modified flag that determines if variation is purchasable.
         */
        public function filter_variation_purchasability( $purchasable , $variation_obj ) {

            return $this->filter_variation_availability( $purchasable , WWP_Helper_Functions::wwp_get_product_id( $variation_obj ) );         

        }

        /**
         * Filter the default attribute of a variable product and check if the matching variation qualifies to be displayed for the current user.
         *
         * @since 1.13.0
         * @access public
         *
         * @param array               $default_attribute Array of default attributes.
         * @param WC_Product_Variable $variable_product  Variable product object.
         * @return array|string We are returning empty string on failure for some reason, I didn't change to returning empty array, I think there is a good reason why we did that.
         */
        public function filter_variable_product_default_attributes( $default_attribute , $variable_product ) {

            if ( !current_user_can( 'manage_options' ) ) {

                // Prepare default attribute for compatibility with 'get_matching_variation' or 'find_matching_product_variation' (WC 2.7) function parameter.
                $processed_default_attribute = array();
                foreach ( $default_attribute as $key => $val )
                    $processed_default_attribute[ 'attribute_' . $key ] = $val;
                
                // Get the variation id that matched the default attributes
                $variation_id = WWP_Helper_Functions::wwp_get_matching_variation( $variable_product , $processed_default_attribute );

                if ( $variation_id )
                    if ( !$this->filter_variation_availability( true , $variation_id ) )
                        return '';
                
            }

            return $default_attribute;

        }
        
        /**
         * Check if variation is available based on set of filters.
         *
         * @since 1.13.0
         * @access public
         *
         * @param boolean $available    Flag that determines whether variation is available or not.
         * @param int     $variation_id Variation id.
         * @return boolean Filtered flag that determines whether variation is available or not.
         */
        public function filter_variation_availability( $available , $variation_id ) {

            if ( !current_user_can( 'manage_options' ) ) {

                $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();

                if ( !empty( $user_wholesale_role ) && get_option( 'wwpp_settings_only_show_wholesale_products_to_wholesale_users' , false ) === 'yes' ) {

                    $price_arr       = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v2( $variation_id , $user_wholesale_role );
                    $wholesale_price = $price_arr[ 'wholesale_price' ];

                    if ( empty( $wholesale_price ) )
                        $available = false;
                    
                }

                $variation_visibility_filter = get_post_meta( $variation_id , WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER );
                $user_wholesale_role         = !empty( $user_wholesale_role ) ? $user_wholesale_role[ 0 ] : '';

                if ( !empty( $variation_visibility_filter ) )
                    if ( !in_array( $user_wholesale_role , $variation_visibility_filter ) && !in_array( 'all' , $variation_visibility_filter ) )
                        $available = false;
                
            }
            
            return $available;

        }




        /*
        |--------------------------------------------------------------------------------------------------------------
        | Variable Wholesale Minimum Order Quantity
        |--------------------------------------------------------------------------------------------------------------
        */

        /**
         * Add variable product level wholesale minimum order quantity custom field.
         *
         * @since 1.9.0
         * @since 1.13.0 Refactor codebase and move to its own model.
         * @access public
         */
        public function add_variable_product_level_wholesale_min_order_qty_custom_field() {

            global $woocommerce, $post;

            $product = wc_get_product( $post->ID );

            if ( WWP_Helper_Functions::wwp_get_product_type( $product ) === 'variable' ) { ?>

                <div class="wholesale-minium-order-quantity-options-group options-group options_group" style="border-top: 1px solid #EEEEEE;">
                
                    <header>
                        <h3 style="font-size: 14px; margin: 10px 12px;"><?php _e( 'Parent Product Wholesale Minimum Order Quantity' , 'woocommerce-wholesale-prices-premium' ); ?></h3>
                        <p style="margin:0; padding:0 12px; line-height: 16px; font-style: italic; font-size: 13px;"><?php _e( 'Customers can add multiple variations to the cart and if the sum of all these variations exceeds the minimum quantity value supplied here, they will be granted the wholesale pricing as per the wholesale price on each variation.' , 'woocommerce-wholesale-prices-premium' ); ?></p>
                    </header>

                    <?php foreach ( $this->_registered_wholesale_roles as $role_key => $role ) {

                        woocommerce_wp_text_input( array(
                            'id'          => $role_key . '_variable_level_wholesale_minimum_order_quantity',
                            'label'       => $role[ 'roleName' ],
                            'placeholder' => '',
                            'desc_tip'    => 'true',
                            'description' => sprintf( __( 'The minimum order quantity for the sum of all variations of this product in the cart for the "%1$s" role.' , 'woocommerce-wholesale-prices-premium' ) , $role[ 'roleName' ] ),
                            'data_type'   => 'decimal'
                        ) );

                    } ?>

                </div><!--.options_group-->

            <?php }

        }

        /**
         * Add variable product level wholesale order qty step custom field.
         * 
         * @since 1.16.0
         * @access public
         */
        public function add_variable_product_level_wholesale_order_qty_step_custom_field() {

            global $woocommerce, $post;
            
            $product = wc_get_product( $post->ID );

            if ( WWP_Helper_Functions::wwp_get_product_type( $product ) === 'variable' ) { ?>

                <div class="wholesale-order-quantity-step-options-group options-group options_group" style="border-top: 1px solid #EEEEEE;">
                
                    <header>
                        <h3 style="font-size: 14px; margin: 10px 12px;"><?php _e( 'Parent Product Wholesale Order Quantity Step' , 'woocommerce-wholesale-prices-premium' ); ?></h3>
                        <p style="margin:0; padding:0 12px; line-height: 16px; font-style: italic; font-size: 13px;"><?php _e( 'Customers can add multiple variations to the cart and if the sum of all these variations is within the increments (step) multiplier specified, they will be granted the wholesale pricing as per the wholesale price on each variation. Min order qty above must be specified to enable this feature.' , 'woocommerce-wholesale-prices-premium' ); ?></p>
                    </header>

                    <?php foreach ( $this->_registered_wholesale_roles as $role_key => $role ) {

                        woocommerce_wp_text_input( array(
                            'id'          => $role_key . '_variable_level_wholesale_order_quantity_step',
                            'label'       => $role[ 'roleName' ],
                            'placeholder' => '',
                            'desc_tip'    => 'true',
                            'description' => sprintf( __( 'Order quantity step wholesale users are restricted to when purchasing this product for the "%1$s" role.' , 'woocommerce-wholesale-prices-premium' ) , $role[ 'roleName' ] ),
                            'data_type'   => 'decimal'
                        ) );

                    } ?>

                </div><!--.options_group-->

            <?php }

        }

        /**
         * Save variable product level wholesale minimum order quantity custom field.
         *
         * @since 1.9.0
         * @since 1.13.0 Refactor codebase and move to its own model.
         * @access public
         *
         * @param int $post_id Product id.
         */
        public function save_variable_product_level_wholesale_min_order_qty_custom_field( $post_id ) {

            foreach ( $this->_registered_wholesale_roles as $roleKey => $role ) {
                
                if ( isset( $_POST[ $roleKey . '_variable_level_wholesale_minimum_order_quantity' ] ) ) {

                    $variable_level_moq = trim( esc_attr( $_POST[ $roleKey . '_variable_level_wholesale_minimum_order_quantity' ] ) );

                    if ( !empty( $variable_level_moq ) ) {

                        if ( !is_numeric( $variable_level_moq ) )
                            $variable_level_moq = '';
                        elseif ( $variable_level_moq < 0 )
                            $variable_level_moq = 0;
                        else
                            $variable_level_moq = wc_format_decimal( $variable_level_moq );

                        $variable_level_moq = round( $variable_level_moq );

                    }

                    $variable_level_moq = wc_clean( apply_filters( 'wwpp_before_save_variable_level_wholesale_minimum_order_quantity' , $variable_level_moq , $roleKey , $post_id ) );
                    update_post_meta( $post_id , $roleKey . '_variable_level_wholesale_minimum_order_quantity' , $variable_level_moq );

                }

            }

        }

        /**
         * Save variable product level wholesale order quantity step custom field.
         * 
         * @since 1.16.0
         * @access public
         * 
         * @param int $post_id Product id.
         */
        public function save_variable_product_level_wholesale_order_qty_step_custom_field( $post_id ) {

            foreach ( $this->_registered_wholesale_roles as $roleKey => $role ) {
                
                if ( isset( $_POST[ $roleKey . '_variable_level_wholesale_order_quantity_step' ] ) ) {

                    $variable_level_oqs = trim( esc_attr( $_POST[ $roleKey . '_variable_level_wholesale_order_quantity_step' ] ) );

                    if ( !empty( $variable_level_oqs ) ) {

                        if ( !is_numeric( $variable_level_oqs ) )
                            $variable_level_oqs = '';
                        elseif ( $variable_level_oqs < 0 )
                            $variable_level_oqs = 0;
                        else
                            $variable_level_oqs = wc_format_decimal( $variable_level_oqs );

                        $variable_level_oqs = round( $variable_level_oqs );

                    }

                    $variable_level_oqs = wc_clean( apply_filters( 'wwpp_before_save_variable_level_wholesale_order_quantity_step' , $variable_level_oqs , $roleKey , $post_id ) );
                    update_post_meta( $post_id , $roleKey . '_variable_level_wholesale_order_quantity_step' , $variable_level_oqs );

                }

            }

        }




        /*
        |--------------------------------------------------------------------------------------------------------------
        | Variation Wholesale Minimum Order Quantity
        |--------------------------------------------------------------------------------------------------------------
        */

        /**
         * Add minimum order quantity custom field to variable products on product edit screen.
         * Custom fields are added per variation, not to the parent variable product.
         *
         * @since 1.2.0
         * @since 1.13.0 Refactor codebase and move to its own model.
         * @since 1.16.0 Change section description to indicate changes with new quantity step feature.
         * 
         * @param int     $loop           Variation loop count.
         * @param array   $variation_data Array of variation data.
         * @param WP_Post $variation      Variaton object.
         */
        public function add_minimum_order_quantity_custom_fields( $loop , $variation_data , $variation ) {

            global $woocommerce, $post;

            // Get the variable product data manually
            // Don't rely on the variation data woocommerce supplied
            // There is a logic change introduced on 2.3 series where they only send variation data (or variation meta)
            // That is built in to woocommerce, so all custom variation meta added to a variable product don't get passed along
            $variable_product_meta = get_post_meta( $variation->ID ); ?>

            <div class="wholesale-minium-order-quantity-options-group options-group options_group" style="border-top: 1px solid #DDDDDD;">

                <header class="form-row form-row-full">
                    <h4 style="font-size: 14px; margin: 10px 0;"><?php _e( 'Wholesale Minimum Order Quantity' , 'woocommerce-wholesale-prices-premium' ); ?></h4>
                    <p style="margin:0; padding:0; line-height: 16px; font-style: italic; font-size: 13px;"><?php _e( "Minimum number of items to be purchased in order to avail this product's wholesale price.<br/>Only applies to wholesale users.<br/><br/>Setting a step value below for the corresponding wholesale role will prevent the specific wholesale customer from adding to cart quantity of this product lower than the set minimum." , 'woocommerce-wholesale-prices-premium' ); ?></p>
                </header>

                <?php foreach ( $this->_registered_wholesale_roles as $role_key => $role ) { ?>

                    <div class="form-row form-row-full">
                        <?php
                        WWP_Helper_Functions::wwp_woocommerce_wp_text_input( array(
                            'id'          => $role_key . '_wholesale_minimum_order_quantity[' . $loop . ']',
                            'class'       => $role_key . '_wholesale_minimum_order_quantity wholesale_minimum_order_quantity short',                            
                            'label'       => $role['roleName'],
                            'placeholder' => '',
                            'desc_tip'    => 'true',
                            'description' => sprintf( __( 'Only applies to users with the role of "%1$s"' , 'woocommerce-wholesale-prices-premium' ) , $role['roleName'] ),
                            'data_type'   => 'decimal',
                            'value'       => isset( $variable_product_meta[ $role_key . '_wholesale_minimum_order_quantity' ][ 0 ] ) ? $variable_product_meta[ $role_key . '_wholesale_minimum_order_quantity' ][ 0 ] : ''
                        ) );
                        ?>
                    </div>

                <?php } ?>

            </div><!--.options_group-->

            <?php

        }

        /**
         * Add order quantity step custom field to variable products on product edit screen.
         * Custom fields are added per variation, not to the parent variable product.
         * 
         * @since 1.16.0
         * @access public
         */
        public function add_order_quantity_step_custom_fields( $loop , $variation_data , $variation ) {

            global $woocommerce, $post;
            
            // Get the variable product data manually
            // Don't rely on the variation data woocommerce supplied
            // There is a logic change introduced on 2.3 series where they only send variation data (or variation meta)
            // That is built in to woocommerce, so all custom variation meta added to a variable product don't get passed along
            $variable_product_meta = get_post_meta( $variation->ID ); ?>

            <div class="wholesale-order-quantity-step-options-group options-group options_group" style="border-top: 1px solid #DDDDDD;">

                <header class="form-row form-row-full">
                    <h4 style="font-size: 14px; margin: 10px 0;"><?php _e( 'Wholesale Order Quantity Step' , 'woocommerce-wholesale-prices-premium' ); ?></h4>
                    <p style="margin:0; padding:0; line-height: 16px; font-style: italic; font-size: 13px;"><?php _e( "Order quantity step wholesale users are restricted to when purchasing this product.<br/>Only applies to wholesale users.<br/><br/>Minimum order quantity above for corresponding wholesale role must be set for this feature to take effect." , 'woocommerce-wholesale-prices-premium' ); ?></p>
                </header>

                <?php foreach ( $this->_registered_wholesale_roles as $role_key => $role ) { ?>

                    <div class="form-row form-row-full">
                        <?php
                        WWP_Helper_Functions::wwp_woocommerce_wp_text_input( array(
                            'id'          => $role_key . '_wholesale_order_quantity_step[' . $loop . ']',
                            'class'       => $role_key . '_wholesale_order_quantity_step wholesale_order_quantity_step short',                            
                            'label'       => $role['roleName'],
                            'placeholder' => '',
                            'desc_tip'    => 'true',
                            'description' => sprintf( __( 'Only applies to users with the role of "%1$s"' , 'woocommerce-wholesale-prices-premium' ) , $role['roleName'] ),
                            'data_type'   => 'decimal',
                            'value'       => isset( $variable_product_meta[ $role_key . '_wholesale_order_quantity_step' ][ 0 ] ) ? $variable_product_meta[ $role_key . '_wholesale_order_quantity_step' ][ 0 ] : ''
                        ) );
                        ?>
                    </div>

                <?php } ?>

            </div><!--.options_group-->

            <?php

        }

        /**
         * Save minimum order quantity custom field value for variations of a variable product on product edit page.
         *
         * @since 1.2.0
         * @since 1.8.0 Add support for custom variations bulk actions.
         * @since 1.13.0 Refactor codebase and move to its own model.
         *
         * @param int        $post_id         Variable product id.
         * @param array      $wholesale_roles Array of all wholesale roles in which this minimum order quantity requirement is to be saved.
         * @param array/null $variation_ids   Variation ids.
         * @param array/null $wholesale_moqs  Wholesale minimum order quantities.
         */
        public function save_minimum_order_quantity_custom_fields( $post_id , $wholesale_roles = array() , $variation_ids = null , $wholesale_moqs = null ) {

            global $_POST;

            $wholesale_roles = !empty( $wholesale_roles ) ? $wholesale_roles : $this->_registered_wholesale_roles;

            if ( ( $variation_ids && $wholesale_moqs ) || isset( $_POST[ 'variable_sku' ] ) ) {

                $variable_post_id = $variation_ids ? $variation_ids : $_POST[ 'variable_post_id' ];
                $max_loop         = max( array_keys( $variable_post_id ) );

                foreach ( $wholesale_roles as $role_key => $role ) {

                    $wholesale_moq = $wholesale_moqs ? $wholesale_moqs : $_POST[ $role_key . '_wholesale_minimum_order_quantity' ];

                    for ( $i = 0; $i <= $max_loop; $i++ ) {

                        if ( !isset( $variable_post_id[ $i ] ) )
                            continue;

                        $variation_id = (int) $variable_post_id[ $i ];

                        if ( isset( $wholesale_moq[ $i ] ) ) {

                            $wholesale_moq[ $i ] = trim( esc_attr( $wholesale_moq[ $i ] ) );

                            if ( !empty( $wholesale_moq[ $i ] ) ) {

                                if( !is_numeric( $wholesale_moq[ $i ] ) )
                                    $wholesale_moq[ $i ] = '';
                                elseif( $wholesale_moq[ $i ] < 0 )
                                    $wholesale_moq[ $i ] = 0;
                                else
                                    $wholesale_moq[ $i ] = wc_format_decimal( $wholesale_moq[ $i ] );

                                $wholesale_moq[ $i ] = round( $wholesale_moq[ $i ] );

                            }

                            $wholesale_moq[ $i ] = wc_clean( apply_filters( 'wwpp_filter_before_save_wholesale_minimum_order_quantity' , $wholesale_moq[ $i ] , $role_key , $variation_id , 'variation' ) );
                            update_post_meta( $variation_id , $role_key . '_wholesale_minimum_order_quantity' , $wholesale_moq[ $i ] );

                        }

                    }

                }

            }

        }

        /**
         * Save order quantity step custom field value for variations of a variable product on product edit page.
         * 
         * @since 1.16.0
         * @access public
         * 
         * @param int        $post_id         Variable product id.
         * @param array      $wholesale_roles Array of all wholesale roles in which this order quantity step requirement is to be saved.
         * @param array/null $variation_ids   Variation ids.
         * @param array/null $wholesale_moqs  Wholesale order quantity steps.
         */
        public function save_order_quantity_step_custom_fields( $post_id , $wholesale_roles = array() , $variation_ids = null , $wholesale_oqss = null ) {

            global $_POST;
            
            $wholesale_roles = !empty( $wholesale_roles ) ? $wholesale_roles : $this->_registered_wholesale_roles;

            if ( ( $variation_ids && $wholesale_oqss ) || isset( $_POST[ 'variable_sku' ] ) ) {

                $variable_post_id = $variation_ids ? $variation_ids : $_POST[ 'variable_post_id' ];
                $max_loop         = max( array_keys( $variable_post_id ) );

                foreach ( $wholesale_roles as $role_key => $role ) {

                    $wholesale_oqs = $wholesale_oqss ? $wholesale_oqss : $_POST[ $role_key . '_wholesale_order_quantity_step' ];

                    for ( $i = 0; $i <= $max_loop; $i++ ) {

                        if ( !isset( $variable_post_id[ $i ] ) )
                            continue;

                        $variation_id = (int) $variable_post_id[ $i ];

                        if ( isset( $wholesale_oqs[ $i ] ) ) {

                            $wholesale_oqs[ $i ] = trim( esc_attr( $wholesale_oqs[ $i ] ) );

                            if ( !empty( $wholesale_oqs[ $i ] ) ) {

                                if( !is_numeric( $wholesale_oqs[ $i ] ) )
                                    $wholesale_oqs[ $i ] = '';
                                elseif( $wholesale_oqs[ $i ] < 0 )
                                    $wholesale_oqs[ $i ] = 0;
                                else
                                    $wholesale_oqs[ $i ] = wc_format_decimal( $wholesale_oqs[ $i ] );

                                $wholesale_oqs[ $i ] = round( $wholesale_oqs[ $i ] );

                            }

                            $wholesale_oqs[ $i ] = wc_clean( apply_filters( 'wwpp_filter_before_save_wholesale_order_quantity_step' , $wholesale_oqs[ $i ] , $role_key , $variation_id , 'variation' ) );
                            update_post_meta( $variation_id , $role_key . '_wholesale_order_quantity_step' , $wholesale_oqs[ $i ] );

                        }

                    }

                }

            }

        }

        


        /*
        |--------------------------------------------------------------------------------------------------------------
        | Variation Custom Bulk Actions
        |--------------------------------------------------------------------------------------------------------------
        */

        /**
         * Add variation custom bulk action options.
         *
         * @since 1.0.0
         * @since 1.13.0 Refactor codebase and move to its own model.
         * @since 1.16.0 Add support for wholesale order quantity step feature.
         * @access public
         */
        public function add_variation_custom_bulk_action_options() {
            
            foreach ( $this->_registered_wholesale_roles as $role_key => $role ) { ?>

                <option value="<?php echo $role_key; ?>_wholesale_min_order_qty"><?php echo sprintf( __( 'Set minimum order quantity (%1$s)', 'woocommerce-wholesale-prices-premium' ) , $role[ 'roleName' ] ); ?></option>

            <?php }

            foreach ( $this->_registered_wholesale_roles as $role_key => $role ) { ?>

                <option value="<?php echo $role_key; ?>_wholesale_order_qty_step"><?php echo sprintf( __( 'Set order quantity step (%1$s)', 'woocommerce-wholesale-prices-premium' ) , $role[ 'roleName' ] ); ?></option>

            <?php }

        }

        /**
         * Execute variation custom bulk actions.
         *
         * @since 1.0.0
         * @since 1.13.0 Refactor codebase and move to its own model.
         * @since 1.16.0 Add support for wholesale order quantity step feature.
         * @access public
         *
         * @param string $bulk_action Bulk action.
         * @param array  $data        Bulk action data.
         * @param int    $product_id  Product id.
         * @param array  $variations  Array of variation ids.
         */
        public function execute_variations_custom_bulk_actions( $bulk_action , $data , $product_id , $variations ) {

            if ( strpos( $bulk_action , '_wholesale_min_order_qty' ) !== false ) {

                if ( is_array( $variations ) && isset( $data[ 'value' ] ) ) {

                    $wholesale_role     = str_replace( '_wholesale_min_order_qty' , '' , $bulk_action );
                    $wholesale_role_arr = array( $wholesale_role => $this->_registered_wholesale_roles[ $wholesale_role ] );

                    $wholesale_moqs = array();

                    foreach ( $variations as $variation_id ) // $variation_id not used, just so we can use foreach
                        $wholesale_moqs[] = $data[ 'value' ];
                    
                    $this->save_minimum_order_quantity_custom_fields( $product_id , $wholesale_role_arr , $variations , $wholesale_moqs );

                }

            }

            if ( strpos( $bulk_action , '_wholesale_order_qty_step' ) !== false ) {

                if ( is_array( $variations ) && isset( $data[ 'value' ] ) ) {

                    $wholesale_role     = str_replace( '_wholesale_order_qty_step' , '' , $bulk_action );
                    $wholesale_role_arr = array( $wholesale_role => $this->_registered_wholesale_roles[ $wholesale_role ] );

                    $wholesale_oqss = array();

                    foreach ( $variations as $variation_id ) // $variation_id not used, just so we can use foreach
                        $wholesale_oqss[] = $data[ 'value' ];
                    
                    $this->save_order_quantity_step_custom_fields( $product_id , $wholesale_role_arr , $variations , $wholesale_oqss );

                }

            }

        }

        
        /**
         * Execute model.
         *
         * @since 1.13.0
         * @access public
         */
        public function run() {

            // Variable Wholesale Minimum Order Quantity
            add_action( 'woocommerce_product_options_sku'           , array( $this , 'add_variable_product_level_wholesale_min_order_qty_custom_field' )   , 10 );
            add_action( 'woocommerce_product_options_sku'           , array( $this , 'add_variable_product_level_wholesale_order_qty_step_custom_field' )  , 10 );
            add_action( 'woocommerce_process_product_meta_variable' , array( $this , 'save_variable_product_level_wholesale_min_order_qty_custom_field' )  , 20 , 1 );
            add_action( 'woocommerce_process_product_meta_variable' , array( $this , 'save_variable_product_level_wholesale_order_qty_step_custom_field' ) , 20 , 1 );

            // Variation Wholesale Minimum Order Quantity
            add_action( 'woocommerce_product_after_variable_attributes' , array( $this , 'add_minimum_order_quantity_custom_fields' )  , 20 , 3 );
            add_action( 'woocommerce_product_after_variable_attributes' , array( $this , 'add_order_quantity_step_custom_fields' )     , 20 , 3 );
            add_action( 'woocommerce_ajax_save_product_variations'      , array( $this , 'save_minimum_order_quantity_custom_fields' ) , 20 , 1 );
            add_action( 'woocommerce_process_product_meta_variable'     , array( $this , 'save_minimum_order_quantity_custom_fields' ) , 20 , 1 );
            add_action( 'woocommerce_ajax_save_product_variations'      , array( $this , 'save_order_quantity_step_custom_fields' )    , 20 , 1 );
            add_action( 'woocommerce_process_product_meta_variable'     , array( $this , 'save_order_quantity_step_custom_fields' )    , 20 , 1 );

            // Wholesale Exclusive Variation
            add_action( 'woocommerce_product_after_variable_attributes' , array( $this , 'add_variation_wholesale_role_visibility_filter_field'  ) , 20 , 3 );
            add_action( 'woocommerce_process_product_meta_variable'     , array( $this , 'save_variation_wholesale_role_visibility_filter_field' ) , 20 , 1 );
            add_action( 'woocommerce_ajax_save_product_variations'      , array( $this , 'save_variation_wholesale_role_visibility_filter_field' ) , 20 , 1 );

            add_filter( 'woocommerce_variation_is_visible'       , array( $this , 'filter_variation_visibility' ) , 10 , 4 );
            add_filter( 'woocommerce_hide_invisible_variations'  , function() { return true; } );
            add_filter( 'woocommerce_variation_is_purchasable'   , array( $this , 'filter_variation_purchasability' )            , 10 , 2 );
            add_filter( 'woocommerce_product_default_attributes' , array( $this , 'filter_variable_product_default_attributes' ) , 10 , 2 );

            // Variation Custom Bulk Actions
            add_action( 'wwp_custom_variation_bulk_action_options' , array( $this , 'add_variation_custom_bulk_action_options' ) , 10 );
            add_action( 'woocommerce_bulk_edit_variations'         , array( $this , 'execute_variations_custom_bulk_actions' )   , 10 , 4 );

        }

    }    

}
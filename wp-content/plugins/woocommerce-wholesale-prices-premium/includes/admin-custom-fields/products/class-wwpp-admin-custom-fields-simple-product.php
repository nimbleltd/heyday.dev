<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_Admin_Custom_Fields_Simple_Product' ) ) {

    /**
     * Model that houses logic  admin custom fields for simple products.
     *
     * @since 1.13.0
     */
    class WWPP_Admin_Custom_Fields_Simple_Product {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWPP_Admin_Custom_Fields_Simple_Product.
         *
         * @since 1.13.0
         * @access private
         * @var WWPP_Admin_Custom_Fields_Simple_Product
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
         * WWPP_Admin_Custom_Fields_Simple_Product constructor.
         *
         * @since 1.13.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Admin_Custom_Fields_Simple_Product model.
         */
        public function __construct( $dependencies ) {

            $this->_wwpp_wholesale_roles = $dependencies[ 'WWPP_Wholesale_Roles' ];

        }

        /**
         * Ensure that only one instance of WWPP_Admin_Custom_Fields_Simple_Product is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.13.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Admin_Custom_Fields_Simple_Product model.
         * @return WWPP_Admin_Custom_Fields_Simple_Product
         */
        public static function instance( $dependencies ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }




        /*
        |--------------------------------------------------------------------------
        | Minimum order quantity custom fields
        |--------------------------------------------------------------------------
        */

        /**
         * Add minimum order quantity custom field to simple products on product edit screen.
         * Note this also adds these custom fields to external products that closely similar to simple products since we used the more generic 'woocommerce_product_options_pricing' hook.
         *
         * @since 1.2.0
         * @since 1.13.0 Refactor codebase and move to its dedicated model.
         * @since 1.16.0 Change section description to indicate changes with new quantity step feature.
         */
        public function add_minimum_order_quantity_fields() {

            $registered_wholesale_roles = $this->_wwpp_wholesale_roles->getAllRegisteredWholesaleRoles();
            global $woocommerce, $post; ?>

            <div class="wholesale-minium-order-quantity-options-group options-group options_group">

                <header>
                    <h3 style="padding-bottom: 10px;"><?php _e( 'Wholesale Minimum Order Quantity' , 'woocommerce-wholesale-prices-premium' ); ?></h3>
                    <p style="margin:0; padding:0 12px; line-height: 16px; font-style: italic; font-size: 13px;"><?php  _e( "Minimum number of items to be purchased in order to avail this product's wholesale price.<br/>Only applies to wholesale users.<br/><br/>Setting a step value below for the corresponding wholesale role will prevent the specific wholesale customer from adding to cart quantity of this product lower than the set minimum." , 'woocommerce-wholesale-prices-premium' ); ?></p>
                </header>

                <?php foreach ( $registered_wholesale_roles as $role_key => $role ) {

                    woocommerce_wp_text_input( array(
                        'id'          => $role_key . '_wholesale_minimum_order_quantity',
                        'class'       => $role_key . '_wholesale_minimum_order_quantity wholesale_minimum_order_quantity short',
                        'label'       => $role[ 'roleName' ],
                        'placeholder' => '',
                        'desc_tip'    => 'true',
                        'description' => sprintf( __( 'Only applies to users with the role of "%1$s"' , 'woocommerce-wholesale-prices-premium' ) , $role[ 'roleName' ] ),
                        'data_type'   => 'decimal'
                    ) );

                } ?>

            </div><!--.options_group-->

            <?php

        }

        /**
         * Add order quantity step custom field to simple products on product edit screen.
         * Note this also adds these custom fields to external products that closely similar to simple products since we used the more generic 'woocommerce_product_options_pricing' hook.
         * 
         * @since 1.16.0
         */
        public function add_order_quantity_step_fields() {

            $registered_wholesale_roles = $this->_wwpp_wholesale_roles->getAllRegisteredWholesaleRoles();
            global $woocommerce, $post;
            
            $product = wc_get_product( $post->ID );

            if ( WWP_Helper_Functions::wwp_get_product_type( $product ) === 'simple' ) { ?>

                <div class="wholesale-order-quantity-step-options-group options-group options_group">

                    <header>
                        <h3 style="padding-bottom: 10px;"><?php _e( 'Wholesale Order Quantity Step' , 'woocommerce-wholesale-prices-premium' ); ?></h3>
                        <p style="margin:0; padding:0 12px; line-height: 16px; font-style: italic; font-size: 13px;"><?php  _e( "Order quantity step wholesale users are restricted to when purchasing this product.<br/>Only applies to wholesale users.<br/><br/>Minimum order quantity above for corresponding wholesale role must be set for this feature to take effect." , 'woocommerce-wholesale-prices-premium' ); ?></p>
                    </header>

                    <?php foreach ( $registered_wholesale_roles as $role_key => $role ) {

                        woocommerce_wp_text_input( array(
                            'id'          => $role_key . '_wholesale_order_quantity_step',
                            'class'       => $role_key . '_wholesale_order_quantity_step wholesale_order_quantity_step short',
                            'label'       => $role[ 'roleName' ],
                            'placeholder' => '',
                            'desc_tip'    => 'true',
                            'description' => sprintf( __( 'Only applies to users with the role of "%1$s"' , 'woocommerce-wholesale-prices-premium' ) , $role[ 'roleName' ] ),
                            'data_type'   => 'decimal'
                        ) );

                    } ?>

                </div><!--.options_group-->

            <?php }

        }

        /**
         * Save minimum order quantity custom field value for simple products on product edit page.
         *
         * @since 1.2.0
         * @since 1.13.0 Refactor codebase and move its own model.
         *
         * @param int $post_id Product id.
         */
        public function save_minimum_order_quantity_fields( $post_id , $product_type = 'simple' ) {

            $registered_wholesale_roles = $this->_wwpp_wholesale_roles->getAllRegisteredWholesaleRoles();

            foreach ( $registered_wholesale_roles as $role_key => $role ) {

                if ( !isset( $_POST[ $role_key . '_wholesale_minimum_order_quantity' ] ) )
                    continue;

                $wholesale_moq = trim( esc_attr( $_POST[ $role_key . '_wholesale_minimum_order_quantity' ] ) );

                if ( !empty( $wholesale_moq ) ) {

                    if( !is_numeric( $wholesale_moq ) )
                        $wholesale_moq = '';
                    elseif ( $wholesale_moq < 0 )
                        $wholesale_moq = 0;
                    else
                        $wholesale_moq = wc_format_decimal( $wholesale_moq );

                    $wholesale_moq = round( $wholesale_moq );

                }

                $wholesale_moq = wc_clean( apply_filters( 'wwpp_before_save_' . $product_type . '_product_wholesale_minimum_order_quantity' , $wholesale_moq , $role_key , $post_id ) );
                update_post_meta( $post_id , $role_key . '_wholesale_minimum_order_quantity' , $wholesale_moq );
            }

        }

        /**
         * Save order quantity step custom field value for simple products on product edit page.
         *
         * @since 1.16.0
         *
         * @param int    $post_id      Product id.
         * @param string $product_type Product type.
         */
        public function save_order_quantity_step_fields( $post_id , $product_type = 'simple' ) {

            $registered_wholesale_roles = $this->_wwpp_wholesale_roles->getAllRegisteredWholesaleRoles();

            foreach ( $registered_wholesale_roles as $role_key => $role ) {

                if ( !isset( $_POST[ $role_key . '_wholesale_order_quantity_step' ] ) )
                    continue;

                $wholesale_oqs = trim( esc_attr( $_POST[ $role_key . '_wholesale_order_quantity_step' ] ) );

                if ( !empty( $wholesale_oqs ) ) {

                    if( !is_numeric( $wholesale_oqs ) )
                        $wholesale_oqs = '';
                    elseif ( $wholesale_oqs < 0 )
                        $wholesale_oqs = 0;
                    else
                        $wholesale_oqs = wc_format_decimal( $wholesale_oqs );

                    $wholesale_oqs = round( $wholesale_oqs );

                }

                $wholesale_oqs = wc_clean( apply_filters( 'wwpp_before_save_' . $product_type . '_product_wholesale_order_quantity_step' , $wholesale_oqs , $role_key , $post_id ) );
                update_post_meta( $post_id , $role_key . '_wholesale_order_quantity_step' , $wholesale_oqs );

            }

        }

        /**
         * Display wholesale minimum order quantity in quick edit. Hooked into 'wwp_after_quick_edit_wholesale_price_fields'.
         *
         * @since 1.14.4
         * @since 1.16.0 Add wholesale order quantity step fields
         * @access public
         *
         * @param Array $all_wholesale_roles    list of wholesale roles
         */
        public function quick_edit_display_wwpp_fields( $all_wholesale_roles ) {

            ?>
                <div class="wwpp_quick_edit_fields quick_edit_wholesale_minimum_order_quantity" style="float: none; clear: both; display: block;">
                    <div style="height: 1px;"></div><!--To Prevent Heading From Bumping Up-->
                    <h4><?php _e( 'Wholesale Minimum Order Quantity', 'woocommerce-wholesale-prices-premium' ); ?></h4>
                    <?php
                        foreach ( $all_wholesale_roles as $role_key => $role ) {

                            $wmoq_field_title = sprintf( __( '%1$s Minimum Order Quantity' , 'woocommerce-wholesale-prices-premium' ) , $role[ 'roleName' ] );
                            $wmoq_field_name  = $role_key . '_wholesale_minimum_order_quantity';

                            $this->_add_wholesale_minimum_order_quantity_field_on_quick_edit_screen( $wmoq_field_title , $wmoq_field_name );

                        }
                    ?>
                </div>

                <div class="wwpp_quick_edit_fields quick_edit_wholesale_order_quantity_step" style="float: none; clear: both; display: block;">
                    <div style="height: 1px;"></div><!--To Prevent Heading From Bumping Up-->
                    <h4><?php _e( 'Wholesale Order Quantity Step', 'woocommerce-wholesale-prices-premium' ); ?></h4>
                    <?php
                        foreach ( $all_wholesale_roles as $role_key => $role ) {

                            $woqs_field_title = sprintf( __( '%1$s Order Quantity Step' , 'woocommerce-wholesale-prices-premium' ) , $role[ 'roleName' ] );
                            $woqs_field_name  = $role_key . '_wholesale_order_quantity_step';

                            $this->_add_wholesale_order_quantity_step_field_on_quick_edit_screen( $woqs_field_title , $woqs_field_name );

                        }
                    ?>
                </div>
            <?php

        }

        /**
         * Print custom wholesale minimum order quantity field on quick edit screen.
         *
         * @since 1.14.4
         * @access public
         *
         * @param string $field_title  Field title.
         * @param strin  $field_name   Field name.
         * @param string $place_holder Field placeholder.
         */
        private function _add_wholesale_minimum_order_quantity_field_on_quick_edit_screen( $field_title , $field_name , $place_holder = "" ) {
            
            ?>

            <label class="alignleft" style="width: 100%;">
                <div class="title"><?php echo $field_title; ?></div>
                <input type="text" name="<?php echo $field_name; ?>" class="text wholesale_minimum_order_quantity wc_input_decimal" placeholder="<?php echo $place_holder; ?>" value="">
            </label>

            <?php

        }

        /**
         * Print custom wholesale order quantity step field on quick edit screen.
         *
         * @since 1.16.0
         * @access public
         *
         * @param string $field_title  Field title.
         * @param strin  $field_name   Field name.
         * @param string $place_holder Field placeholder.
         */
        private function _add_wholesale_order_quantity_step_field_on_quick_edit_screen( $field_title , $field_name , $place_holder = "" ) {

            ?>
            
            <label class="alignleft" style="width: 100%;">
                <div class="title"><?php echo $field_title; ?></div>
                <input type="text" name="<?php echo $field_name; ?>" class="text wholesale_order_quantity_step wc_input_decimal" placeholder="<?php echo $place_holder; ?>" value="">
            </label>

            <?php

        }

        /**
         * Add the wholesale minimum order quantity data on the product listing column so it can be used to populate the
         * current values of the quick edit fields via javascript.
         *
         * @since 1.14.4
         * @since 1.16.0 Add support for wholesale order quantity step.
         * @access public
         *
         * @param Array  $all_wholesale_roles   list of wholesale roles
         * @param int    $product_id            Product ID
         */
        public function add_wwpp_fields_data_to_product_listing_column( $all_wholesale_roles , $product_id ) {

            $allowed_product_types = apply_filters( 'wwp_quick_edit_allowed_product_types' , array( 'simple' , 'external' ) , 'wholesale_minimum_order_quantity' ); ?>

            <div class="wholesale_custom_quick_edit_fields_allowed_product_types" data-product_types='<?php echo json_encode( $allowed_product_types ); ?>'></div>

            <?php foreach ( $all_wholesale_roles as $role_key => $role ) : ?>

                <div class="wholesale_minimum_order_quantity_data" data-role="<?php echo $role_key; ?>"><?php echo get_post_meta( $product_id , $role_key . '_wholesale_minimum_order_quantity' , true ); ?></div>
                <div class="wholesale_order_quantity_step_data" data-role="<?php echo $role_key; ?>"><?php echo get_post_meta( $product_id , $role_key . '_wholesale_order_quantity_step' , true ); ?></div>

            <?php endforeach;
        }

        /**
         * Save wholesale custom fields on the quick edit option.
         *
         * @since 1.14.4
         * @since 1.16.0 Add support for wholesale order quantity step.
         * @access public
         *
         * @param WC_Product $product               Product object.
         * @param int        $product_id            Product ID.
         */
        public function save_wwpp_fields_on_quick_edit_screen( $product , $product_id ) {

            // Save minimum order quantity fields
            $this->save_minimum_order_quantity_fields( $product_id );

            // Save order quantity step fields
            $this->save_order_quantity_step_fields( $product_id );

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

            add_action( 'woocommerce_product_options_pricing'     , array( $this , 'add_minimum_order_quantity_fields' )  , 20 , 1 );
            add_action( 'woocommerce_product_options_pricing'     , array( $this , 'add_order_quantity_step_fields' )     , 20 , 1 );
            add_action( 'woocommerce_process_product_meta_simple' , array( $this , 'save_minimum_order_quantity_fields' ) , 20 , 1 );
            add_action( 'woocommerce_process_product_meta_simple' , array( $this , 'save_order_quantity_step_fields' )    , 20 , 1 );
            add_action( 'wwp_after_quick_edit_wholesale_price_fields' , array( $this , 'quick_edit_display_wwpp_fields' ) , 10 , 1 );
            add_action( 'wwp_add_wholesale_price_fields_data_to_product_listing_column' , array( $this , 'add_wwpp_fields_data_to_product_listing_column' ) , 10 , 2 );
            add_action( 'wwp_save_wholesale_price_fields_on_quick_edit_screen' , array( $this , 'save_wwpp_fields_on_quick_edit_screen' ) , 10 , 2 );
        }

    }

}

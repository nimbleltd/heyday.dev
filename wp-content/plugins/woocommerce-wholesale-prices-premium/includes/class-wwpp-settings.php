<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_Settings' ) ) {

    /**
     * Model that houses extended settings options for WWP.
     *
     * @since 1.0.0
     * @since 1.12.8 Refactored codebase. Move settings related code inside here.
     */
    class WWPP_Settings {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWPP_Settings.
         *
         * @since 1.12.8
         * @access private
         * @var WWPP_Settings
         */
        private static $_instance;

        /**
         * Model that houses the logic of retrieving information relating to wholesale role/s of a user.
         *
         * @since 1.12.8
         * @access private
         * @var WWPP_Wholesale_Roles
         */
        private $_wwpp_wholesale_roles;

        /**
         * Property that holds all registered wholesale roles.
         * 
         * @since 1.16.0
         * @access public
         * @var array
         */
        private $_all_wholesale_roles;




        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        /**
         * WWPP_Tax constructor.
         *
         * @since 1.12.8
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Tax model.
         */
        public function __construct( $dependencies ) {

            $this->_wwpp_wholesale_roles = $dependencies[ 'WWPP_Wholesale_Roles' ];

            $this->_all_wholesale_roles  = $this->_wwpp_wholesale_roles->getAllRegisteredWholesaleRoles();

        }

        /**
         * Ensure that only one instance of WWPP_Settings is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.12.8
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Settings model.
         * @return WWPP_Settings
         */
        public static function instance( $dependencies ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }

        /**
         * Change the title of the general settings section of the plugin's settings.
         *
         * @since 1.0.3
         * @since 1.12.8 Refactor codebase for effeciency.
         * @access public
         * 
         * @param string $general_section_title General settings section title.
         * @return string Filtered general settings section title.
         */
        public function plugin_settings_general_section_title( $general_section_title ) {

            return __( 'General' , 'woocommerce-wholesale-prices-premium' );

        }

        /**
         * Premium plugin settings sections.
         *
         * @since 1.0.0
         * @since 1.12.8 Refactor codebase.
         * @access public
         * 
         * @param array $sections Array of settings sections.
         * @return array Filtered array of settings sections.
         */
        public function plugin_settings_sections( $sections ) {

            $sections[ 'wwpp_setting_price_section' ]           = __( 'Price' , 'woocommerce-wholesale-prices-premium' );
            $sections[ 'wwpp_setting_tax_section' ]             = __( 'Tax' , 'woocommerce-wholesale-prices-premium' );
            $sections[ 'wwpp_setting_shipping_section' ]        = __( 'Shipping' , 'woocommerce-wholesale-prices-premium' );
            $sections[ 'wwpp_setting_discount_section' ]        = __( 'Discount' , 'woocommerce-wholesale-prices-premium' );
            $sections[ 'wwpp_setting_payment_gateway_section' ] = __( 'Payment Gateway' , 'woocommerce-wholesale-prices-premium' );
            $sections[ 'wwpp_setting_cache_section' ]           = __( 'Cache' , 'woocommerce-wholesale-prices-premium' );
            $sections[ 'wwpp_setting_help_section' ]            = __( 'Help' , 'woocommerce-wholesale-prices-premium' );
            
            return $sections;

        }

        /**
         * Premium plugin settings section contents.
         * 
         * @since 1.0.0
         * @since 1.12.8 Refactor codebase.
         * @access public
         * 
         * @param  array  $settings        Array of settings.
         * @param  string $current_section Id of current settings section.
         * @return array Filtered array of settings.
         */
        public function plugin_settings_section_content( $settings , $current_section ) {

            if ( $current_section === '' ) {

                // General Settings Section
                $wwpp_general_settings = apply_filters( 'wwpp_settings_general_section_settings', $this->_get_general_section_settings() ) ;
                $settings = array_merge( $settings , $wwpp_general_settings );

            } elseif ( $current_section === 'wwpp_setting_price_section' ) {

                // Price Settings Section
                $wwpp_price_settings = apply_filters( 'wwpp_settings_price_section_settings' , $this->_get_price_section_settings() );
                $settings = array_merge( $settings , $wwpp_price_settings );

            } elseif ( $current_section === 'wwpp_setting_tax_section' ) {

                // Tax Settings Section
                $wwpp_tax_settings = apply_filters( 'wwpp_settings_tax_section_settings' , $this->_get_tax_section_settings() );
                $settings = array_merge( $settings , $wwpp_tax_settings );

            } elseif ( $current_section === 'wwpp_setting_shipping_section' ) {

                // Shipping Settings Section
                $wwpp_shipping_settings = apply_filters( 'wwpp_settings_shipping_section_settings' , $this->_get_shipping_section_settings() );
                $settings = array_merge( $settings , $wwpp_shipping_settings );

            } elseif ( $current_section === 'wwpp_setting_discount_section' ) {

                // Discount Settings Section
                $wwpp_discount_settings = apply_filters( 'wwpp_settings_discount_section_settings' , $this->_get_discount_section_settings() );
                $settings = array_merge( $settings , $wwpp_discount_settings );

            } elseif ( $current_section === 'wwpp_setting_payment_gateway_section' ) {

                // Payment Gateway Settings Section
                $wwpp_payment_gateway_settings = apply_filters( 'wwpp_settings_payment_gateway_section_settings' , $this->_get_payment_gateway_section_settings() );
                $settings                      = array_merge( $settings , $wwpp_payment_gateway_settings );

            } elseif ( $current_section === 'wwpp_setting_cache_section' ) {

                // Cache Settings Section
                $wwpp_cache_settings = apply_filters( 'wwpp_settings_cache_section_settings' , $this->_get_cache_section_settings() );
                $settings            = array_merge( $settings , $wwpp_cache_settings );

            } elseif ( $current_section === 'wwpp_setting_help_section' ) {

                // Help Settings Section
                $wwpp_help_settings = apply_filters( 'wwpp_settings_help_section_settings' , $this->_get_help_section_settings() );
                $settings           = array_merge( $settings , $wwpp_help_settings );

            }

            return $settings;

        }

        /**
         * Filter wwpp_editor custom settings field so it gets stored properly after sanitizing.
         *
         * @since 1.7.4
         * @since 1.12.8 Refactor codebase.
         * 
         * @param array $settings Array of settings.
         */
        public function save_editor_custom_field_type( $settings ) {

            if ( isset( $_POST[ 'wwpp_editor' ] ) && !empty( $_POST[ 'wwpp_editor' ] ) )
                foreach ( $_POST[ 'wwpp_editor' ] as $index => $content )
                    $_POST[ $index ] = htmlentities ( wpautop( $content ) );

        }

        /**
         * General settings section options.
         *
         * @since 1.0.0
         * @since 1.12.8 Refactor codebase.
         * @access public
         * 
         * @return array Array of premium options for the plugin's general settings section.
         */
        private function _get_general_section_settings() {

            return array(

                array(
                    'name'  =>  __( 'General Options', 'woocommerce-wholesale-prices-premium' ),
                    'type'  =>  'title',
                    'desc'  =>  '',
                    'id'    =>  'wwpp_settings_section_title'
                ),

                array(
                    'name'      =>  __( 'Only Show Wholesale Products To Wholesale Users', 'woocommerce-wholesale-prices-premium' ),
                    'type'      =>  'checkbox',
                    'desc'      =>  __( 'This setting only affects wholesale users. Non-wholesale users (including users who are not logged in) will see the products with regular prices. "Wholesale products" are defined as products that have a wholesale price defined that is greater than zero.', 'woocommerce-wholesale-prices-premium' ),
                    'id'        =>  'wwpp_settings_only_show_wholesale_products_to_wholesale_users'
                ),

                array(
                    'name'      =>  __( 'Disable Coupons For Wholesale Users', 'woocommerce-wholesale-prices-premium' ),
                    'type'      =>  'checkbox',
                    'desc'      =>  __( 'If checked, this will prevent wholesale users from using coupons' , 'woocommerce-wholesale-prices-premium' ),
                    'id'        =>  'wwpp_settings_disable_coupons_for_wholesale_users'
                ),

                array(
                    'name'      =>  __( 'Category Wholesale Discount', 'woocommerce-wholesale-prices-premium' ),
                    'type'      =>  'select',
                    'desc'      =>  __( 'In the event a single product belongs to multiple product category. Which category discount to apply?', 'woocommerce-wholesale-prices-premium' ),
                    'desc_tip'  =>  __( 'This only applies to products who have no wholesale price set up', 'woocommerce-wholesale-prices-premium' ),
                    'id'        =>  'wwpp_settings_multiple_category_wholesale_discount_logic',
                    'options'   =>  array(
                        'highest' => __( 'Highest' , 'woocommerce-wholesale-prices-premium' ),
                        'lowest'  => __( 'Lowest' , 'woocommerce-wholesale-prices-premium' )
                    ),
                    'default'   =>  'lowest'
                ),

                array(
                    'name'      =>  __( 'Hide Quantity Discount Table' , 'woocommerce-wholesale-prices-premium' ),
                    'type'      =>  'checkbox',
                    'desc'      =>  __( 'When checked it will hide the quantity discount table on the front end' , 'woocommerce-wholesale-prices-premium' ),
                    'desc_tip'  =>  '',
                    'id'        =>  'wwpp_settings_hide_quantity_discount_table'
                ),

                array(
                    'name'      =>  __( 'Thank You Message', 'woocommerce-wholesale-prices-premium' ),
                    //'type'      =>  'textarea',
                    'type'      =>  'wwpp_editor',
                    'desc'      =>  __( 'Message', 'woocommerce-wholesale-prices-premium' ),
                    'desc_tip'  =>  __( 'Custom Message To Display on Thank You Page (Leave Blank To Disable)', 'woocommerce-wholesale-prices-premium' ),
                    'id'        =>  'wwpp_settings_thankyou_message',
                    'css'       =>  'min-width: 400px; min-height: 100px;'
                ),

                array(
                    'name'      =>  '',
                    'type'      =>  'select',
                    'desc'      =>  __( 'Position', 'woocommerce-wholesale-prices-premium' ),
                    'desc_tip'  =>  __( 'Either Replace Original Thank You Message, or Append/Prepend Additional Message to the Original Thank You Message', 'woocommerce-wholesale-prices-premium' ),
                    'id'        =>  'wwpp_settings_thankyou_message_position',
                    'options'   =>  array(
                        'replace'   =>  __( 'Replace' , 'woocommerce-wholesale-prices-premium' ),
                        'append'    =>  __( 'Append' , 'woocommerce-wholesale-prices-premium' ),
                        'prepend'   =>  __( 'Prepend' , 'woocommerce-wholesale-prices-premium' )
                    ),
                    'default'   =>  'replace'
                ),

                array(
                    'name'      =>  __( 'Always Allow Backorders' , 'woocommerce-wholesale-prices-premium' ),
                    'type'      =>  'checkbox',
                    'desc'      =>  __( 'When checked, wholesale users can always do backorders.' , 'woocommerce-wholesale-prices-premium' ),
                    'desc_tip'  =>  '',
                    'id'        =>  'wwpp_settings_always_allow_backorders_to_wholesale_users'
                ),

                array(
                    'name'      =>  __( 'Minimum Order Requirements', 'woocommerce-wholesale-prices-premium' ),
                    'type'      =>  'number',
                    'desc'      =>  __( 'Minimum order quantity', 'woocommerce-wholesale-prices-premium' ),
                    'desc_tip'  =>  __( 'Set as zero or leave blank to have no minimum quantity required.', 'woocommerce-wholesale-prices-premium' ),
                    'default'   =>  0,
                    'id'        =>  'wwpp_settings_minimum_order_quantity'
                ),

                array(
                    'name'      =>  '',
                    'type'      =>  'text',
                    'desc'      =>  __( 'Minimum sub-total amount ('.get_woocommerce_currency_symbol().'). This ensures your wholesale customers order more than this threshold at the wholesale price.' , 'woocommerce-wholesale-prices-premium' ),
                    'desc_tip'  =>  __( "Calculated using the product's defined wholesale price (before tax and shipping). Set to zero or leave blank to disable." , 'woocommerce-wholesale-prices-premium' ),
                    'default'   =>  0,
                    'id'        =>  'wwpp_settings_minimum_order_price',
                    'class'     =>  'wc_input_price'
                ),

                array(
                    'name'      =>  '',
                    'type'      =>  'select',
                    'desc'      =>  __( 'Minimum order logic' , 'woocommerce-wholesale-prices-premium'),
                    'desc_tip'  =>  __( 'Either (minimum order quantity "AND" minimum order sub-total) or (minimum order quantity "OR" minimum order sub-total). Only applied if both minimum items and price is set' , 'woocommerce-wholesale-prices-premium' ),
                    'id'        =>  'wwpp_settings_minimum_requirements_logic',
                    'options'   =>  array(
                        'and'   =>  __( 'AND' , 'woocommerce-wholesale-prices-premium' ),
                        'or'    =>  __( 'OR' , 'woocommerce-wholesale-prices-premium' )
                    ),
                    'default'   =>  'and'
                ),

                array(
                    'name'      =>  '',
                    'type'      =>  'checkbox',
                    'desc'      =>  __( 'Override per wholesale role?' , 'woocommerce-wholesale-prices-premium' ),
                    'desc_tip'  =>  __( 'Override minimum order requirements per wholesale role?' , '' ),
                    'id'        =>  'wwpp_settings_override_order_requirement_per_role'
                ),

                array(
                    'name' => __( 'Hide product count on product categories?' , 'woocommerce-wholesale-prices-premium' ),
                    'type' => 'checkbox',
                    'desc' => __( 'If checked, hides the product count on product categories for wholesale users only.' , 'woocommerce-wholesale-prices-premium' ),
                    'id'   => 'wwpp_settings_hide_product_categories_product_count'
                ),

                array(
                    'name'      =>  __( 'Clear Cart On Login', 'woocommerce-wholesale-prices-premium' ),
                    'type'      =>  'checkbox',
                    'desc'      =>  __( 'This setting only affects wholesale users. This will completely destroy previously started session after successful login.', 'woocommerce-wholesale-prices-premium' ),
                    'id'        =>  'wwpp_settings_clear_cart_on_login'
                ),

                array(
                    'type'  =>  'sectionend',
                    'id'    =>  'wwpp_settings_sectionend'
                )

            );

        }

        /**
         * Price settings section options.
         *
         * @since 1.14.0
         * @access public
         * 
         * @return array Array of premium options for the plugin's price settings section.
         */
        private function _get_price_section_settings() {

            return array(

                array(
                    'name'  =>  __( 'Price Options', 'woocommerce-wholesale-prices-premium' ),
                    'type'  =>  'title',
                    'desc'  =>  '',
                    'id'    =>  'wwpp_settings_price_section_title'
                ),

                array(
                    'name'      =>  __( 'Wholesale Price Text' , 'woocommerce-wholesale-prices-premium' ),
                    'type'      =>  'text',
                    'desc'      =>  '',
                    'desc_tip'  =>  __( 'Default is "Wholesale Price:"', 'woocommerce-wholesale-prices-premium' ),
                    'id'        =>  'wwpp_settings_wholesale_price_title_text'
                ),

                array(
                    'name' => __( 'Always Use Regular Price' , 'woocommerce-wholesale-prices-premium' ),
                    'type' => 'checkbox',
                    'desc' => __( 'When calculating the wholesale price by using a percentage (global discount % or category based %) always ensure the Regular Price is used and ignore the Sale Price if present.' , 'woocommerce-wholesale-prices-premium' ),
                    'id'   => 'wwpp_settings_explicitly_use_product_regular_price_on_discount_calc'
                ),

                array(
                    'name'      =>  __( 'Hide Original Price' , 'woocommerce-wholesale-prices-premium' ),
                    'type'      =>  'checkbox',
                    'desc'      =>  __( 'Hide original price instead of showing a crossed out price if a wholesale price is present.', 'woocommerce-wholesale-prices-premium' ),
                    'desc_tip'  =>  '',
                    'id'        =>  'wwpp_settings_hide_original_price'
                ),

                array(
                    'name' => __( 'Variable product price display' , 'woocommerce-wholesale-prices-premium' ),
                    'type' => 'select',
                    'desc' => __( 'Specify the format in which variable product prices are displayed. Only for wholesale customers.' ),
                    'desc_tip' => true,
                    'id'       => 'wwpp_settings_variable_product_price_display',
                    'options'  => array(
                        'price-range' => __( 'Price Range' , 'woocommerce-wholesale-prices-premium' ),
                        'minimum'     => __( 'Minimum Price' , 'woocommerce-wholeslae-prices-premium' ),
                        'maximum'     => __( 'Maximum Price' , 'woocommerce-wholesale-prices-premium' )
                    )
                ),
                
                array(
                    'name' => __( 'Hide wholesale price on admin product listing' , 'woocommerce-wholesale-prices-premium' ),
                    'type' => 'checkbox',
                    'desc' => __( 'If checked, hides wholesale price per wholesale role on the product listing on the admin page.' , 'woocommerce-wholesale-prices-premium' ),
                    'id'   => 'wwpp_hide_wholesale_price_on_product_listing',
                ),

                array(
                    'type'  =>  'sectionend',
                    'id'    =>  'wwpp_settings_price_sectionend'
                )

            );

        }

        /**
         * Tax settings section options.
         *
         * @since 1.4.2
         * @since 1.12.8 Refactor codebase.
         * @access public
         *
         * @return array Array of premium options for the plugin's tax settings section.
         */
        private function _get_tax_section_settings() {

            return array(

                array(
                    'name'  =>  __( 'Tax Options', 'woocommerce-wholesale-prices-premium' ),
                    'type'  =>  'title',
                    'desc'  =>  '',
                    'id'    =>  'wwpp_settings_tax_section_title'
                ),

                array(
                    'name'      =>  __( 'Tax Exemption', 'woocommerce-wholesale-prices-premium' ),
                    'type'      =>  'checkbox',
                    'desc'      =>  __( 'Do not apply tax to all wholesale roles', 'woocommerce-wholesale-prices-premium' ),
                    'desc_tip'  =>  __( 'Removes tax for all wholesale roles. All wholesale prices will display excluding tax throughout the store, cart and checkout. The display settings below will be ignored.', 'woocommerce-wholesale-prices-premium' ),
                    'id'        =>  'wwpp_settings_tax_exempt_wholesale_users'
                ),

                array(
                    'name'      =>  __( 'Display Prices in the Shop', 'woocommerce-wholesale-prices-premium' ),
                    'type'      =>  'select',
                    'desc'      =>  __( 'Choose how wholesale roles see all prices throughout your shop pages.', 'woocommerce-wholesale-prices-premium' ),
                    'desc_tip'  =>  __( 'Note: If the option above of "Tax Exempting" wholesale users is enabled, then wholesale prices on shop pages will not include tax regardless the value of this option.', 'woocommerce-wholesale-prices-premium' ),
                    'id'        =>  'wwpp_settings_incl_excl_tax_on_wholesale_price',
                    'options'   =>  array(
                        ''      =>  __( '--Use woocommerce default--' , 'woocommerce-wholesale-prices-premium' ),
                        'incl'  =>  __( 'Including tax' , 'woocommerce-wholesale-prices-premium' ),
                        'excl'  =>  __( 'Excluding tax' , 'woocommerce-wholesale-prices-premium' )
                    ),
                    'default'   =>  ''
                ),

                array(
                    'name'      =>  __( 'Display Prices During Cart and Checkout', 'woocommerce-wholesale-prices-premium' ),
                    'type'      =>  'select',
                    'desc'      =>  __( 'Choose how wholesale roles see all prices on the cart and checkout pages.', 'woocommerce-wholesale-prices-premium' ),
                    'desc_tip'  =>  __( 'Note: If the option above of "Tax Exempting" wholesale users is enabled, then wholesale prices on cart and checkout page will not include tax regardless the value of this option.', 'woocommerce-wholesale-prices-premium' ),
                    'id'        =>  'wwpp_settings_wholesale_tax_display_cart',
                    'options'   =>  array(
                        ''      =>  __( '--Use woocommerce default--' , 'woocommerce-wholesale-prices-premium' ),
                        'incl'  =>  __( 'Including tax' , 'woocommerce-wholesale-prices-premium' ),
                        'excl'  =>  __( 'Excluding tax' , 'woocommerce-wholesale-prices-premium' )
                    ),
                    'default'   =>  ''
                ),

                array(
                    'name'      => __( 'Override Regular Price Suffix' , 'woocommerce-wholesale-prices-premium' ),
                    'type'      => 'text',
                    'desc'      => __( 'Override the price suffix on regular prices for wholesale users.' , 'woocommerce-wholesale-prices-premium' ),
                    'desc_tip'  => __( 'Make this blank to use the default price suffix. You can also use prices substituted here using one of the following {price_including_tax} and {price_excluding_tax}.' , 'woocommerce-wholesale-prices-premium' ),
                    'id'        => 'wwpp_settings_override_price_suffix_regular_price'
                ),

                array(
                    'name'      => __( 'Wholesale Price Suffix' , 'woocommerce-wholesale-prices-premium' ),
                    'type'      => 'text',
                    'desc'      => __( 'Set a specific price suffix specifically for wholesale prices.' , 'woocommerce-wholesale-prices-premium' ),
                    'desc_tip'  => __( 'Make this blank to use the default price suffix. You can also use prices substituted here using one of the following {price_including_tax} and {price_excluding_tax}.' ),
                    'id'        => 'wwpp_settings_override_price_suffix'
                ),

                array(
                    'type'  =>  'sectionend',
                    'id'    =>  'wwpp_settings_tax_divider1_sectionend'
                ),

                array(
                    'name'  =>  __( 'Wholesale Role / Tax Exemption Mapping', 'woocommerce-wholesale-prices-premium' ),
                    'type'  =>  'title',
                    'desc'  =>  __( 'Specify tax exemption per wholesale role. Overrides general <b>"Tax Exemption"</b> option above.' , 'woocommerce-wholesale-prices-premium' ),
                    'id'    =>  'wwpp_settings_wholesale_role_tax_exemption_mapping_section_title'
                ),

                array(
                    'name'  =>  '',
                    'type'  =>  'wholesale_role_tax_options_mapping_controls',
                    'desc'  =>  '',
                    'id'    =>  'wwpp_settings_shipping_section_shipping_controls',
                ),

                array(
                    'type'  =>  'sectionend',
                    'id'    =>  'wwpp_settings_tax_divider2_sectionend'
                ),

                array(
                    'name'  =>  __( 'Wholesale Role / Tax Class Mapping', 'woocommerce-wholesale-prices-premium' ),
                    'type'  =>  'title',
                    'desc'  =>  __( 'Specify tax classes per wholesale role.' , 'woocommerce-wholesale-prices-premium' ),
                    'id'    =>  'wwpp_settings_wholesale_role_tax_class_mapping_section_title'
                ),

                array(
                    'name'  => __( 'Wholesale Only Tax Classes' , 'woocommerce-wholesale-prices-premium' ),
                    'type'  => 'checkbox',
                    'desc'  => __( 'Hide the mapped tax classes from non-wholesale customers. Non-wholesale customers will no longer be able to see the tax classes you have mapped below. Warning: If a product uses one of the mapped tax classes, customers whose roles are not included on the mapping below (including guest users) will be taxed using the standard tax class.' , 'woocommerce-wholesale-prices-premium' ),
                    'id'    => 'wwpp_settings_mapped_tax_classes_for_wholesale_users_only'
                ),

                array(
                    'name'  => '',
                    'type'  => 'wholesale_role_tax_class_options_mapping_controls',
                    'desc'  => '',
                    'id'    => 'wwpp_wholesale_role_tax_class_options_mapping'
                ),

                array(
                    'type'  =>  'sectionend',
                    'id'    =>  'wwpp_settings_tax_sectionend'
                )

            );

        }

        /**
         * Shipping settings section options.
         *
         * @since 1.0.3
         * @since 1.12.8 Refactor codebase.
         * @access public
         *
         * @return array Array of premium options for the plugin's shipping settings section.
         */
        private function _get_shipping_section_settings() {

            return array(

                array(
                    'name'  =>  __( 'Shipping Options', 'woocommerce-wholesale-prices-premium' ),
                    'type'  =>  'title',
                    'desc'  =>  '',
                    'id'    =>  'wwpp_settings_shipping_section_title'
                ),

                array(
                    'name'      =>  __( 'Force Free Shipping', 'woocommerce-wholesale-prices-premium' ),
                    'type'      =>  'checkbox',
                    'desc'      =>  __( 'Forces all wholesale roles to use free shipping. All other shipping methods will be removed.', 'woocommerce-wholesale-prices-premium' ),
                    'desc_tip'  =>  __( 'Note: If a wholesale role has ANY mappings in the table below, free shipping will not be forced.', 'woocommerce-wholesale-prices-premium' ),
                    'id'        =>  'wwpp_settings_wholesale_users_use_free_shipping'
                ),

                array(
                    'name' => __( 'Free Shipping Label' , 'woocommerce-wholesale-prices-premium' ),
                    'type' => 'text',
                    'desc' => __( 'If <b>"Force Free Shipping"</b> is enabled, a dynamically created free shipping method is created and used by force. The label for this defaults to <b>"Free Shipping"</b> but you can override that here.' , 'woocommerce-wholesale-prices-premium' ),
                    'id'   => 'wwpp_dynamic_free_shipping_title'
                ),

                array(
                    'name' => __( 'Wholesale Only Shipping Methods' , 'woocommerce-wholesale-prices-premium' ),
                    'type' => 'checkbox',
                    'desc' => __( 'Hide the mapped shipping methods from non-wholesale customers. Regular customers will no longer be able to see the shipping methods you have mapped below.' , 'woocommerce-wholesale-prices-premium' ),
                    'id'   => 'wwpp_settings_mapped_methods_for_wholesale_users_only'
                ),

                array(
                    'type'  =>  'sectionend',
                    'id'    =>  'wwpp_settings_shipping_divider1_sectionend'
                ),

                array(
                    'name'  =>  __( 'Wholesale Role/Shipping Method Mapping', 'woocommerce-wholesale-prices-premium' ),
                    'type'  =>  'title',
                    'desc'  =>  __( 'Map the shipping methods you wish to restrict wholesale customers to use within a shipping zone.<br/><br/>
                                     <b>1.</b> Select the wholesale role you wish to restrict<br/>
                                     <b>2.</b> Choose the shipping zone you want this to apply to<br/>
                                     <b>3.</b> Finally, choose the shipping method in that shipping zone that you wish to restrict the selected wholesale role to.<br/><br/>
                                     You can repeat this process to map multiple shipping methods per zone & multiple zones per role.
                                     <h2>Non-Zoned Shipping Methods</h2>
                                     <p>Non-Zoned shipping methods covers third party shipping methods extensions that register their shipping methods globally meaning they appear to the user always and do not take the shipping zone into account at all.<br/><br/></p>
                                     <p>To map these non-zoned methods, please select the <b>"Use Non-Zoned Shipping Methods"</b> checkbox and select the method from the list.</p>' , 'woocommerce-wholesale-prices-premium' ),
                    'id'    =>  'wwpp_settings_wholesale_shipping_section_title'
                ),

                array(
                    'name'  =>  '',
                    'type'  =>  'shipping_controls',
                    'desc'  =>  '',
                    'id'    =>  'wwpp_settings_shipping_section_shipping_controls',
                ),

                array(
                    'type'  =>  'sectionend',
                    'id'    =>  'wwpp_settings_shipping_sectionend'
                )

            );

        }

        /**
         * Discount settings section options.
         *
         * @since 1.2.0
         * @since 1.12.8 Refactor codebase.
         * @access public
         *
         * @return array Array of premium options for the plugin's discount settings section.
         */
        private function _get_discount_section_settings() {

            return array(

                array(
                    'name'  =>  __( 'General Discount Options', 'woocommerce-wholesale-prices-premium' ),
                    'type'  =>  'title',
                    'desc'  =>  __( 'This is where you set <b>"general discount"</b> for each wholesale role that will be applied to those users<br/>if a product they wish to purchase has no wholesale price set and no wholesale discount set at the product category level.' , 'woocommerce-wholesale-prices-premium' ),
                    'id'    =>  'wwpp_settings_discount_section_title'
                ),

                array(
                    'name'  =>  '',
                    'type'  =>  'discount_controls',
                    'desc'  =>  '',
                    'id'    =>  'wwpp_settings_discount_section_discount_controls',
                ),

                array(
                    'type'  =>  'sectionend',
                    'id'    =>  'wwpp_settings_discount_section_sectionend'
                ),

                array(
                    'name' => __( 'General Quantity Based Discounts' , 'woocommerce-wholesale-prices-premium' ),
                    'type' => 'title',
                    'desc' => __( 'Give an additional quantity based discount when using the global General Discount for that wholesale role.' , 'woocommerce-wholesale-prices-premium' ),
                    'id'   => 'wwpp_settings_qty_discount_section_title'
                ),

                array(
                    'name' => __( 'Enable General Quantity Based Discounts' , 'woocommerce-wholesale-prices-premium' ),
                    'type' => 'checkbox',
                    'desc' => __( 'Turns the general quantity based discount system on/off. Mappings below will be disregarded if this option is unchecked.' , 'woocommerce-wholesale-prices-premium' ),
                    'id'   => 'enable_wholesale_role_cart_quantity_based_wholesale_discount'
                ),
                
                array(
                    'name' => __( 'Apply Discounts Based On Individual Product Quantities?' , 'woocommerce-wholesale-prices-premium' ),
                    'type' => 'checkbox',
                    'desc' => __( 'By default, the general quantity based discounts system will use the total quantity of all items in the cart. This option changes this to apply quantity based discounts based on the quantity of individual products in the cart.' , 'woocommerce-wholesale-prices-premium' ),
                    'id'   => 'enable_wholesale_role_cart_quantity_based_wholesale_discount_mode_2'
                ),

                array(
                    'name' => '',
                    'type' => 'general_cart_qty_based_discount_controls',
                    'desc' => '',
                    'id'   => 'wwpp_settings_discount_section_qty_discount_controls'
                ),

                array(
                    'type'  =>  'sectionend',
                    'id'    =>  'wwpp_settings_discount_sectionend'
                )

            );

        }

        /**
         * Payment gateway surcharge settings section options.
         *
         * @since 1.3.0
         * @since 1.12.8 Refactor codebase.
         * @access public
         * 
         * @return array Array of premium options for the plugin's payment gateway settings section.
         */
        private function _get_payment_gateway_section_settings() {

            return array(

                array(
                    'name'  =>  __( 'Payment Gateway Options', 'woocommerce-wholesale-prices-premium' ),
                    'type'  =>  'title',
                    'desc'  =>  '',
                    'id'    =>  'wwpp_settings_payment_gateway_section_title'
                ),

                array(
                    'type'  =>  'sectionend',
                    'id'    =>  'wwpp_settings_payment_gateway_first_sectionend'
                ),

                array(
                    'name'  =>  __( 'Wholesale Role / Payment Gateway', 'woocommerce-wholesale-prices-premium' ),
                    'type'  =>  'title',
                    'desc'  =>  __( 'You can specify what payment gateways are available per wholesale role (Note that payment gateway need not be enabled)' , 'woocommerce-wholesale-prices-premium' ),
                    'id'    =>  'wwpp_settings_payment_gateway_surcharge_section_title'
                ),

                array(
                    'name'  =>  '',
                    'type'  =>  'wholesale_role_payment_gateway_controls',
                    'desc'  =>  '',
                    'id'    =>  'wwpp_settings_payment_gateway_wholesale_role_mapping',
                ),

                array(
                    'type'  =>  'sectionend',
                    'id'    =>  'wwpp_settings_payment_gateway_section_sectionend'
                ),

                array(
                    'name'  =>  __( 'Wholesale Role / Payment Gateway Surcharge', 'woocommerce-wholesale-prices-premium' ),
                    'type'  =>  'title',
                    'desc'  =>  __( 'You can specify extra cost per payment gateway per wholesale role' , 'woocommerce-wholesale-prices-premium' ),
                    'id'    =>  'wwpp_settings_payment_gateway_surcharge_section_title'
                ),

                array(
                    'name'  =>  '',
                    'type'  =>  'payment_gateway_surcharge_controls',
                    'desc'  =>  '',
                    'id'    =>  'wwpp_settings_payment_gateway_section_surcharge',
                ),

                array(
                    'type'  =>  'sectionend',
                    'id'    =>  'wwpp_settings_payment_gateway_sectionend'
                )

            );

        }

        /**
         * Cache settings section options.
         * 
         * @since 1.6.0]
         * @access public
         * 
         * @return array Array of premium options for the plugin's cache settings section.
         */
        private function _get_cache_section_settings() {

            return array(
                
                array(
                    'name'  =>  __( 'Cache Options', 'woocommerce-wholesale-prices-premium' ),
                    'type'  =>  'title',
                    'desc'  =>  '',
                    'id'    =>  'wwpp_settings_cache_section_title'
                ),
                
                array(
                    'name' => __( 'Enable variable product price range caching' , 'woocommerce-wholesale-prices-premium' ),
                    'type' => 'checkbox',
                    'desc' => __( 'When enabled, variable product price range will be cached after computation. Please improve description.' , 'woocommerce-wholesale-prices-premium' ),
                    'id'   => 'wwpp_enable_var_prod_price_range_caching'
                ),

                array(
                    'name' => __( 'Clear all variable product price range cache' , 'woocommerce-wholesale-prices-premium' ),
                    'type' => 'wwpp_clear_var_prod_price_range_caching',
                    'desc' => __( 'Clear all variable product price range cache.' , 'woocommerce-wholesale-prices-premium' ),
                    'id'   => 'wwpp_clear_var_prod_price_range_caching'
                ),
                
                array(
                    'type' => 'sectionend',
                    'id'   => 'wwpp_settings_cache_sectionend'
                )

            );

        }

        /**
         * Help settings section options.
         *
         * @since 1.3.0
         * @since 1.12.8 Refactor codebase.
         * @access public
         *
         * @return array Array of premium options for the plugin's help settings section.
         */
        private function _get_help_section_settings() {

            return array(

                array(
                    'name'  =>  __( 'Help Options', 'woocommerce-wholesale-prices-premium' ),
                    'type'  =>  'title',
                    'desc'  =>  '',
                    'id'    =>  'wwpp_settings_help_section_title'
                ),

                array(
                    'name'  =>  '',
                    'type'  =>  'help_resources_controls',
                    'desc'  =>  '',
                    'id'    =>  'wwpp_settings_help_resources',
                ),

                array(
                    'type'  =>  'sectionend',
                    'id'    =>  'wwpp_settings_help_devider1'
                ),

                array(
                    'name'  =>  __( 'Debug Tools', 'woocommerce-wholesale-prices-premium' ),
                    'type'  =>  'title',
                    'desc'  =>  '',
                    'id'    =>  'wwpp_settings_help_debug_tools_title'
                ),

                array(
                    'name'  =>  '',
                    'type'  =>  'initialize_product_visibility_meta_button',
                    'desc'  =>  '',
                    'id'    =>  'wwpp_settings_initialize_product_visibility_meta_button',
                ),

                array(
                    'name' => __( 'Clean up plugin options on un-installation' , 'woocommerce-wholesale-prices-premium' ),
                    'type' => 'checkbox',
                    'desc' => __( 'If checked, removes all plugin options when this plugin is uninstalled. <b>Warning:</b> This process is irreversible.' , 'woocommerce-wholesale-prices-premium' ),
                    'id'   => 'wwpp_settings_help_clean_plugin_options_on_uninstall'
                ),

                array(
                    'type'  =>  'sectionend',
                    'id'    =>  'wwpp_settings_help_sectionend'
                )

            );

        }




        /*
        |--------------------------------------------------------------------------
        | Custom Settings Fields
        |--------------------------------------------------------------------------
        */

        /**
         * Wholesale role shipping options mapping.
         * WooCommerce > Settings > Wholesale Prices > Shipping > Wholesale Role/Shipping Method Mapping
         *
         * @since 1.0.3
         * @since 1.12.8 Refactor codebase.
         * @access public
         */
        public function render_plugin_settings_custom_field_shipping_controls() {

            if ( version_compare( WC()->version , '2.6.0' , "<" ) ) {

                $all_wholesale_roles      = $this->wwppGetAllRegisteredWholesaleRoles( null , false );
                $wc_shipping_methods      = WC_Shipping::instance()->load_shipping_methods();
                $saved_mapping            = get_option( WWPP_OPTION_WHOLESALE_ROLE_SHIPPING_METHOD_MAPPING , array() );
                $table_rate_shipping_type = $this->checkTable_rate_shipping_type();

                if ( !is_array( $all_wholesale_roles ) )
                    $all_wholesale_roles = array();

                if ( !is_array( $wc_shipping_methods ) )
                    $wc_shipping_methods = array();

                if ( !is_array( $saved_mapping ) )
                    $saved_mapping = array();

                if ( $table_rate_shipping_type == 'code_canyon' )
                    $cc_shipping_zones = get_option( 'be_woocommerce_shipping_zones' , array() );
                elseif ( $table_rate_shipping_type == 'mango_hour' ) {

                    $mh_shipping_zones = get_option( 'mh_wc_table_rate_plus_zones' , array() );
                    $mh_shipping_services = get_option( 'mh_wc_table_rate_plus_services' , array() );

                }

                // Legacy shipping functionality
                require_once ( WWPP_VIEWS_PATH . 'plugin-settings-custom-fields/view-wwpp-shipping-controls-custom-field.php' );

            } else {

                // New shipping functionality ( WC 2.6.0 )

                $all_wholesale_roles    = $this->_all_wholesale_roles;
                $wc_shipping_zones      = WC_Shipping_Zones::get_zones();
                $wc_default_zone        = WC_Shipping_Zones::get_zone( 0 );
                $wholesale_zone_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_SHIPPING_ZONE_METHOD_MAPPING , array() );

                $non_zoned_shipping_methods = array();
                $wc_shipping_methods        = WC()->shipping->load_shipping_methods();

                foreach ( $wc_shipping_methods as $shipping_method )
                    if ( !$shipping_method->supports( 'shipping-zones' ) && $shipping_method->enabled == 'yes' )
                        $non_zoned_shipping_methods[ $shipping_method->id ] = $shipping_method;

                require_once ( WWPP_VIEWS_PATH . 'plugin-settings-custom-fields/view-wwpp-shipping-controls-custom-field-wc-2.6.php' );

            }

        }

        /**
         * Wholesale role general wholesale discount options mapping.
         * WooCommerce > Settings > Wholesale Prices > Discount > General Discount Options
         *
         * @since 1.2.0
         * @since 1.12.8 Refactor codebase.
         * @access public
         */
        public function render_plugin_settings_custom_field_discount_controls() {

            $all_wholesale_roles = $this->_all_wholesale_roles;

            $saved_general_discount = get_option( WWPP_OPTION_WHOLESALE_ROLE_GENERAL_DISCOUNT_MAPPING , array() );
            if ( !is_array( $saved_general_discount ) )
                $saved_general_discount = array();

            require_once ( WWPP_VIEWS_PATH . 'plugin-settings-custom-fields/view-wwpp-discount-controls-custom-field.php' );

        }

        /**
         * Wholesale role per cart qty wholesale discount options mapping.
         * WooCommerce > Settings > Wholesale Prices > Discount > General Quantity Based Discounts
         * 
         * @since 1.16.0
         * @access public
         */
        public function render_plugin_settings_custom_field_general_cart_qty_based_discount_controls() {

            $all_wholesale_roles       = $this->_all_wholesale_roles;
            $cart_qty_discount_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_CART_QTY_BASED_DISCOUNT_MAPPING , array() );
            if ( !is_array( $cart_qty_discount_mapping ) )
                $cart_qty_discount_mapping = array(); 
            
            ?>

            <tr valign="top" id="wholesale-role-cart-qty-based-wholesale-discount-container">
                <th colspan="2" scope="row" class="titledesc">

                    <?php require_once ( WWPP_VIEWS_PATH . 'plugin-settings-custom-fields/view-wwpp-general-cart-qty-based-discount-controls-custom-field.php' ); ?>
                    
                </th>
            </tr>

            <?php

        }

        /**
         * Wholesale role payment gateway surcharge options mapping.
         * WooCommerce > Settings > Wholesale Prices > Payment Gateway > Wholesale Role / Payment Gateway Surcharge
         *
         * @since 1.3.0
         * @since 1.12.8 Refactor codebase.
         * @access public
         */
        public function render_plugin_settings_custom_field_payment_gateway_surcharge_controls() {

            $all_wholesale_roles = $this->_all_wholesale_roles;

            $payment_gateway_surcharge = get_option( WWPP_OPTION_PAYMENT_GATEWAY_SURCHARGE_MAPPING , array() );
            if ( !is_array( $payment_gateway_surcharge ) )
                $payment_gateway_surcharge = array();

            $available_gateways = WC()->payment_gateways->payment_gateways();
            if ( !is_array( $available_gateways ) )
                $available_gateways = array();

            $surcharge_types = array( 'fixed_price' => __( 'Fixed Price' , 'woocommerce-wholesale-price-premium' ) , 'percentage'  => __( 'Percentage' , 'woocommerce-wholesale-price-premium' ) );

            ?>
            
            <tr valign="top">
                <th colspan="2" scope="row" class="titledesc">

                    <?php require_once ( WWPP_VIEWS_PATH . 'plugin-settings-custom-fields/view-wwpp-payment-gateway-surcharge-controls-custom-field.php' ); ?>

                    <style>
                        p.submit {
                            display: none !important;
                        }
                    </style>

                </th>
            </tr>

            <?php

        }

        /**
         * Wholesale role payment gateway options mapping.
         * WooCommerce > Settings > Wholesale Prices > Payment Gateway > Wholesale Role / Payment Gateway
         *
         * @since 1.3.0
         * @since 1.12.8 Refactor codebase.
         * @access public
         */
        public function render_plugin_settings_custom_field_wholesale_role_payment_gateway_controls() {

            $all_wholesale_roles = $this->_all_wholesale_roles;

            $wholesale_role_payment_gateway_papping = get_option( WWPP_OPTION_WHOLESALE_ROLE_PAYMENT_GATEWAY_MAPPING , array() );
            if ( !is_array( $wholesale_role_payment_gateway_papping ) )
                $wholesale_role_payment_gateway_papping = array();

            $available_gateways = WC()->payment_gateways->payment_gateways();
            if ( !is_array( $available_gateways ) )
                $available_gateways = array();

            require_once ( WWPP_VIEWS_PATH . 'plugin-settings-custom-fields/view-wwpp-wholesale-role-payment-gateway-controls-custom-field.php' );

        }

        /**
         * Plugin knowledge base custom control.
         * WooCommerce > Settings > Wholesale Prices > Help > Knowledge Base
         *
         * @since 1.4.1
         * @since 1.12.8 Refactor codebase.
         * @access public
         */
        public function render_plugin_settings_custom_field_help_resources_controls() {

            require_once ( WWPP_VIEWS_PATH . 'plugin-settings-custom-fields/view-wwpp-help-resources-controls-custom-field.php' );

        }

        /**
         * Wholesale role tax exemption mapping.
         * WooCommerce > Settings > Wholesale Prices > Tax > Wholesale Role / Tax Exemption Mapping.
         *
         * @since 1.5.0
         * @since 1.12.8 Refactor codebase.
         * @access public
         */
        public function render_plugin_settings_custom_field_wholesale_role_tax_options_mapping_controls() {

            $all_wholesale_roles = $this->_all_wholesale_roles;

            $wholesale_role_tax_options = get_option( WWPP_OPTION_WHOLESALE_ROLE_TAX_OPTION_MAPPING , array() );
            if ( !is_array( $wholesale_role_tax_options ) )
                $wholesale_role_tax_options = array();

            require_once( WWPP_VIEWS_PATH . 'plugin-settings-custom-fields/view-wwpp-wholesale-role-tax-options-mapping-controls-custom-field.php' );

        }

        /**
         * Wholesale role tax class options mapping.
         * 
         * WooCommerce > Settings > Wholesale Prices > Tax > Wholesale Role / Tax Class Mapping.
         * 
         * @since 1.16.0
         * @access public
         */        
        public function render_plugin_settings_custom_field_wholesale_role_tax_class_options_mapping_controls() {

            $wc_tax_classes = WC_Tax::get_tax_classes();
            if ( !is_array( $wc_tax_classes ) )
                $wc_tax_classes = array();
            
            $all_wholesale_roles   = $this->_all_wholesale_roles;
            $processed_tax_classes = array();

            foreach ( $wc_tax_classes as $tax_class )
                $processed_tax_classes[ sanitize_title( $tax_class ) ] = $tax_class;

            $wholesale_role_tax_class_options = get_option( WWPP_OPTION_WHOLESALE_ROLE_TAX_CLASS_OPTIONS_MAPPING , array() );
            if ( !is_array( $wholesale_role_tax_class_options ) )
                $wholesale_role_tax_class_options = array(); 
                
            ?>

                <tr valign="top">
                    <th colspan="2" scope="row" class="titledesc">
                        <div id="wholesale-role-tax-class-options">

                            <?php require_once( WWPP_VIEWS_PATH . 'plugin-settings-custom-fields/view-wwpp-wholesale-role-tax-class-options-mapping-controls-custom-field.php' ); ?>

                        </div>
                    </th>
                </tr>

            <?php

        }

        /**
         * Product Visibility Meta.
         * WooCommerce > Settings > Wholesale Prices > Help > Product Visibility Meta
         *
         * @since 1.5.2
         * @since 1.12.8 Refactor codebase.
         * @access public
         */
        public function render_plugin_settings_custom_field_initialize_product_visibility_meta_button() {

            require_once( WWPP_VIEWS_PATH . 'plugin-settings-custom-fields/view-wwpp-initialize-product-visibility-meta-button-custom-field.php' );

        }

        /**
         * Render wwpp editor custom field.
         *
         * @since 1.7.4
         * @since 1.12.8 Refactor codebase.
         * @access public
         *
         * @param $data
         */
        public function render_plugin_settings_custom_field_wwpp_editor( $data ) {

            require_once( WWPP_VIEWS_PATH . 'plugin-settings-custom-fields/view-wwpp-editor.php' );

        }

        /**
         * Render wwpp clear variable product price range cache.
         * 
         * @since 1.16.0
         * @access public
         */
        public function render_plugin_settings_custom_field_wwpp_clear_var_prod_price_range_caching() {

            require_once( WWPP_VIEWS_PATH . 'plugin-settings-custom-fields/view-wwpp-clear-var-prod-price-range-caching.php' );

        }


        /**
         * Execute model.
         *
         * @since 1.12.8
         * @access public
         */
        public function run() {

            WooCommerceWholeSalePrices::instance()->activate_plugin_settings();

            add_filter( 'wwp_filter_settings_general_section_title' , array( $this , 'plugin_settings_general_section_title' ) , 10 , 1 );
            add_filter( 'wwp_filter_settings_sections'              , array( $this , 'plugin_settings_sections' )              , 10 , 1 );
            add_filter( 'wwp_settings_section_content'              , array( $this , 'plugin_settings_section_content' )       , 10 , 2 ); // should be wwp and not wwof. WWP-66
            add_action( 'wwp_before_save_settings'                  , array( $this , 'save_editor_custom_field_type' )         , 10 , 1 );

            
            // Custom Settings Fields
            add_action( 'woocommerce_admin_field_shipping_controls'                                 , array( $this , 'render_plugin_settings_custom_field_shipping_controls' )                                 , 10 );
            add_action( 'woocommerce_admin_field_discount_controls'                                 , array( $this , 'render_plugin_settings_custom_field_discount_controls' )                                 , 10 );
            add_action( 'woocommerce_admin_field_general_cart_qty_based_discount_controls'          , array( $this , 'render_plugin_settings_custom_field_general_cart_qty_based_discount_controls' ) );
            add_action( 'woocommerce_admin_field_payment_gateway_surcharge_controls'                , array( $this , 'render_plugin_settings_custom_field_payment_gateway_surcharge_controls' )                , 10 );
            add_action( 'woocommerce_admin_field_wholesale_role_payment_gateway_controls'           , array( $this , 'render_plugin_settings_custom_field_wholesale_role_payment_gateway_controls' )           , 10 );
            add_action( 'woocommerce_admin_field_help_resources_controls'                           , array( $this , 'render_plugin_settings_custom_field_help_resources_controls' )                           , 10 );
            add_action( 'woocommerce_admin_field_wholesale_role_tax_options_mapping_controls'       , array( $this , 'render_plugin_settings_custom_field_wholesale_role_tax_options_mapping_controls' )       , 10 );
            add_action( 'woocommerce_admin_field_wholesale_role_tax_class_options_mapping_controls' , array( $this , 'render_plugin_settings_custom_field_wholesale_role_tax_class_options_mapping_controls' ) , 10 );
            add_action( 'woocommerce_admin_field_initialize_product_visibility_meta_button'         , array( $this , 'render_plugin_settings_custom_field_initialize_product_visibility_meta_button' )         , 10 );
            add_action( 'woocommerce_admin_field_wwpp_editor'                                       , array( $this , 'render_plugin_settings_custom_field_wwpp_editor' )                                       , 10 , 1 );
            add_action( 'woocommerce_admin_field_wwpp_clear_var_prod_price_range_caching'           , array( $this , 'render_plugin_settings_custom_field_wwpp_clear_var_prod_price_range_caching' )           , 10 );

        }

    }

}
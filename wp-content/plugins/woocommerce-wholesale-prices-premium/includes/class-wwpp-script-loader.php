<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_Script_Loader' ) ) {

    /**
     * Model that houses the logic of loading in scripts to various pages of the plugin.
     *
     * @since 1.14.0
     */
    class WWPP_Script_Loader {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWPP_Script_Loader.
         *
         * @since 1.14.0
         * @access private
         * @var WWPP_Script_Loader
         */
        private static $_instance;

        /**
         * Model that houses the logic of retrieving information relating to wholesale role/s of a user.
         *
         * @since 1.14.0
         * @access private
         * @var WWPP_Wholesale_Roles
         */
        private $_wwpp_wholesale_roles;

        /**
         * Model that houses logic of wholesale prices.
         *
         * @since 1.14.0
         * @access private
         * @var WWPP_Wholesale_Prices
         */
        private $_wwpp_wholesale_prices;

        /**
         * Model that houses logic of payment gateways.
         *
         * @since 1.14.0
         * @access private
         * @var WWPP_Wholesale_Role_Payment_Gateway
         */
        private $_wwpp_wholesale_role_payment_gateway;

        /**
         * Current WWP version.
         *
         * @since 1.14.0
         * @access private
         * @var int
         */
        private $_wwpp_current_version;

        /**
         * Wholesale roles page handle.
         *
         * @since 1.14.0
         * @access private
         * @var string
         */
        private $_wwpp_roles_page_handle;

        /**
         * Model that houses the logic of applying product category level wholesale pricing.
         *
         * @since 1.14.0
         * @access public
         * @var WWPP_Wholesale_Price_Product_Category
         */
        private $_wwpp_wholesale_price_product_category;




        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        /**
         * WWPP_Script_Loader constructor.
         *
         * @since 1.14.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Script_Loader model.
         */
        public function __construct( $dependencies ) {

            $this->_wwpp_wholesale_roles                  = $dependencies[ 'WWPP_Wholesale_Roles' ];
            $this->_wwpp_wholesale_prices                 = $dependencies[ 'WWPP_Wholesale_Prices' ];
            $this->_wwpp_wholesale_role_payment_gateway   = $dependencies[ 'WWPP_Wholesale_Role_Payment_Gateway' ];
            $this->_wwpp_current_version                  = $dependencies[ 'WWPP_CURRENT_VERSION' ];
            $this->_wwpp_roles_page_handle                = $dependencies[ 'wwpp_roles_page_handle' ];
            $this->_wwpp_wholesale_price_product_category = $dependencies[ 'WWPP_Wholesale_Price_Product_Category' ];

        }

        /**
         * Ensure that only one instance of WWPP_Script_Loader is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.14.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Script_Loader model.
         * @return WWPP_Script_Loader
         */
        public static function instance( $dependencies ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }

        /**
         * Load admin or backend related styles and scripts.
         * Only load em on the right time and on the right place.
         *
         * @since 1.0.0
         * @since 1.4.0 Refactor codebase and move to its own model.
         * @access public
         *
         * @param string $handle Hook suffix for the current admin page.
         */
        public function load_back_end_styles_and_scripts( $handle ) {

            global $post, $pagenow;

            // Woocommerce screen stuff to determine the current page
            $screen = get_current_screen();

            $post_type = get_post_type();
            if ( !$post_type && isset( $_GET[ 'post_type' ] ) )
                $post_type = $_GET[ 'post_type' ];

            if ( strcasecmp( $handle , $this->_wwpp_roles_page_handle ) == 0 ) {
                // Wholesale roles page scripts

                wp_enqueue_style( 'wwpp_toastr_css' , WWPP_JS_URL . 'lib/toastr/toastr.min.css' , array(), $this->_wwpp_current_version , 'all' );
                wp_enqueue_style( 'wwpp_roles_page_css' , WWPP_CSS_URL . 'wwp-back-end-wholesale-roles.css' , array() , $this->_wwpp_current_version , 'all' );

                wp_enqueue_script( 'wwpp_toastr_js' , WWPP_JS_URL . 'lib/toastr/toastr.min.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_backEndAjaxServices_js' , WWPP_JS_URL . 'app/modules/BackEndAjaxServices.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_wholesaleRolesListingActions_js' , WWPP_JS_URL . 'app/modules/WholesaleRolesListingActions.js' , array( 'jquery' ), $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_wholesaleRolesFormActions_js' , WWPP_JS_URL . 'app/modules/WholesaleRolesFormActions.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_wholesale_roles_main_js' , WWPP_JS_URL . 'app/wholesale-roles-main.js' , array( 'jquery' , 'jquery-tiptip' ) , $this->_wwpp_current_version , true );

                wp_localize_script( 'wwpp_wholesaleRolesListingActions_js' , 'wwpp_wholesaleRolesListingActions_params' , array(
                        'i18n_edit'   => __( 'Edit' , 'woocommerce-wholesale-prices-premium' ),
                        'i18n_delete' => __( 'Delete' , 'woocommerce-wholesale-prices-premium' ),
                        'i18n_yes'    => __( 'Yes' , 'woocommerce-wholesale-prices-premium' ),
                        'i18n_no'     => __( 'No' , 'woocommerce-wholesale-prices-premium' )
                ) );

                wp_localize_script( 'wwpp_wholesale_roles_main_js' , 'wwpp_wholesale_roles_main_params' , array(
                    'i18n_enter_role_name'           => __( 'Please Enter Role Name' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_error_wholesale_form'      => __( 'Error in Wholesale Form' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_enter_role_key'            => __( 'Please Enter Role Key' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_role_successfully_added'   => __( 'Wholesale Role Successfully Added' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_successfully_added_role'   => __( 'Successfully Added New Role' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_failed_add_new_role'       => __( 'Failed to Add New Wholesale Role' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_role_successfully_edited'  => __( 'Wholesale Role Successfully Edited' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_successfully_edited_role'  => __( 'Successfully Edited Role' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_failed_edit_role'          => __( 'Failed to Edit Wholesale Role' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_role_successfully_deleted' => __( 'Wholesale Role Successfully Deleted' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_successfully_deleted_role' => __( 'Successfully Deleted Role' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_failed_delete_role'        => __( 'Failed to Delete Wholesale Role' , 'woocommerce-wholesale-prices-premium' ),
                ) );

            }

            if ( $handle == 'product_page_global_addons' ) {
                // Global product addons page

                wp_enqueue_style( 'wwpp_chosen_css' , WWPP_JS_URL . 'lib/chosen/chosen.min.css' , array() , $this->_wwpp_current_version , 'all' );

                wp_enqueue_script( 'wwpp_chosen_js' , WWPP_JS_URL . 'lib/chosen/chosen.jquery.min.js' , array('jquery') , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_global_addons_js' , WWPP_JS_URL . 'app/wwpp-global-addons.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );

            }

            if ( ( $handle == 'post-new.php' || $handle == 'post.php' ) && $post_type == 'product' ) {
                // Woocommerce single product admin page

                wp_enqueue_style( 'wwpp_toastr_css' , WWPP_JS_URL . 'lib/toastr/toastr.min.css' , array(), $this->_wwpp_current_version , 'all' );
                wp_enqueue_style( 'wwpp_chosen_css' , WWPP_JS_URL . 'lib/chosen/chosen.min.css' , array() , $this->_wwpp_current_version , 'all' );
                wp_enqueue_style( 'wwpp_single_product_admin_css' , WWPP_CSS_URL . 'wwpp-single-product-admin.css' , array() , $this->_wwpp_current_version , 'all' );

                wp_enqueue_script( 'wwpp_toastr_js' , WWPP_JS_URL . 'lib/toastr/toastr.min.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_chosen_js' , WWPP_JS_URL . 'lib/chosen/chosen.jquery.min.js' , array('jquery') , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_backEndAjaxServices_js' , WWPP_JS_URL . 'app/modules/BackEndAjaxServices.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_single_product_admin_js' , WWPP_JS_URL . 'app/wwpp-single-product-admin.js' , array( 'jquery' , 'jquery-tiptip' ) , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_single_variable_product_admin_custom_bulk_actions_js' , WWPP_JS_URL . 'app/wwpp-single-variable-product-admin-custom-bulk-actions.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );

                wp_localize_script( 'wwpp_single_variable_product_admin_custom_bulk_actions_js' , 'wwpp_custom_bulk_actions_params' , array(
                    'wholesale_roles'     => $this->_wwpp_wholesale_roles->getAllRegisteredWholesaleRoles(),
                    'i18n_moq_prompt_message' => __( 'Enter a value (leave blank to remove min order quantity)' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_oqs_prompt_message' => __( 'Enter a value (leave blank to remove order quantity step)' , 'woocommerce-wholesale-prices-premium' )
                ) );

                $aelia_currency_switcher_active = WWPP_ACS_Integration_Helper::aelia_currency_switcher_active();

                if ( $aelia_currency_switcher_active ) {

                    $currencySymbol = "";

                    $base_currency = WWPP_ACS_Integration_Helper::get_product_base_currency( $post->ID );

                    $woocommerce_currencies = get_woocommerce_currencies();
                    $enabled_currencies = WWPP_ACS_Integration_Helper::enabled_currencies();

                } else
                    $currencySymbol = "(" . get_woocommerce_currency_symbol() . ")";

                $wwpp_single_product_admin_params = array(
                    'i18n_wholesale_role'                        => __( 'Wholesale Role' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_starting_qty'                          => __( 'Starting Qty' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_ending_qty'                            => __( 'Ending Qty' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_price_type'                            => __( 'Price Type' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_wholesale_price'                       => __( 'Wholesale Price' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_currency'                              => __( 'Currency' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_fail_enable_product_quantity'          => __( 'Failed to enable Product Quantity Based Wholesale Pricing options.' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_fail_disable_product_quantity'         => __( 'Failed to disable Product Quantity Based Wholesale Pricing options.' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_fill_fields_properly'                  => __( 'The following fields are not properly filled:' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_fill_form_properly'                    => __( 'Please fill the form properly' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_ending_qty_must_not_be_less_start_qty' => __( 'Ending Qty must not be less than Starting Qty' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_success_add_discount_mapping'          => __( 'Successfully Added Quantity Discount Rule Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_fail_add_discount_mapping'             => __( 'Failed To Add Quantity Discount Rule Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_success_update_discount_mapping'       => __( 'Successfully Updated Quantity Discount Rule Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_click_ok_remove_discount_mapping'      => __( 'Clicking OK will remove the current quantity discount rule mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_no_quantity_discount'                  => __( 'No Quantity Discount Rules Found' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_success_delete_discount'               => __( 'Successfully Deleted Quantity Discount Rule Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_fail_delete_discount'                  => __( 'Failed to Delete Quantity Discount Rule Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_base_currency'                         => __( '(Base Currency)' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_fail_update_discount_mapping'          => __( 'Failed To Update Quantity Discount Rule Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_fixed_price_wholesale_label'           => sprintf( __( 'Wholesale Price %1$s' , 'woocommerce-wholesale-prices-premium' ) , $currencySymbol ),
                    'i18n_percent_price_wholesale_label'         => __( 'Wholesale Price (%)' , 'woocommerce-wholesale-prices-premium' ),
                    'required_base_wholesale_price_err_msg'      => array()
                );

                $all_registered_wholesale_roles = $this->_wwpp_wholesale_roles->getAllRegisteredWholesaleRoles();
                foreach ( $all_registered_wholesale_roles as $wholesale_role_key => $wholesale_role )
                    $wwpp_single_product_admin_params[ 'required_base_wholesale_price_err_msg' ][ $wholesale_role_key ] = sprintf( __( 'The product\'s wholesale price for "%1$s" role has not been set. This quantity based price mapping will be added, but it will not take effect until a price is entered for the "%1$s" role.' , 'woocommerce-wholesale-prices-premium' ) , $wholesale_role[ 'roleName' ] );

                wp_localize_script( 'wwpp_single_product_admin_js' , 'wwpp_single_product_admin_params' , $wwpp_single_product_admin_params );

            } else if ( in_array( $screen->id , array( 'edit-product' ) ) ) {
                // WooCommerce product listing

                wp_enqueue_style( 'wwpp_chosen_css' , WWPP_JS_URL . 'lib/chosen/chosen.min.css' , array() , $this->_wwpp_current_version , 'all' );

                wp_enqueue_script( 'wwpp_chosen_js' , WWPP_JS_URL . 'lib/chosen/chosen.jquery.min.js' , array('jquery') , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_cpt_product_listing_admin_main_js' , WWPP_JS_URL . 'app/wwpp-cpt-product-listing-admin-main.js' , array( 'jquery' ) , $this->_wwpp_current_version );
            }

            if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'wwc_license_settings' &&
               ( ( isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'wwpp' ) || WWS_LICENSE_SETTINGS_DEFAULT_PLUGIN == 'wwpp' ) ) {
                // WWPP WSS License Settings Page

                wp_enqueue_style( 'wwpp_toastr_css' , WWPP_JS_URL . 'lib/toastr/toastr.min.css' , array() , $this->_wwpp_current_version , 'all');
                wp_enqueue_style( 'wwpp_wws_license_settings_css' , WWPP_CSS_URL . 'wwpp-wws-license-settings.css' , array() , $this->_wwpp_current_version , 'all');

                wp_enqueue_script( 'wwpp_toastr_js' , WWPP_JS_URL . 'lib/toastr/toastr.min.js' , array( 'jquery' ) , 1 , true );
                wp_enqueue_script( 'wwpp_backEndAjaxServices_js' , WWPP_JS_URL . 'app/modules/BackEndAjaxServices.js', array( 'jquery' ) , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_wws_license_settings_js' , WWPP_JS_URL . 'app/wwpp-wws-license-settings.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );

                wp_localize_script( 'wwpp_wws_license_settings_js' , 'wwpp_wws_license_settings_params' , array(
                    'i18n_success_save_wholesale_price' => __( 'Wholesale Prices License Details Successfully Saved' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_fail_save_wholesale_price'    => __( 'Failed To Save Wholesale Prices License Details' , 'woocommerce-wholesale-prices-premium' )
                ) );

            }

            if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'wc-settings' &&
                 isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'wwp_settings' &&
                 ( !isset( $_GET[ 'section' ] ) || ( isset( $_GET[ 'section' ] ) && $_GET[ 'section' ] == '' ) ) ) {
                // WWPP General Settings

                wp_enqueue_style( 'wwpp_toastr_css' , WWPP_JS_URL . 'lib/toastr/toastr.min.css' , array() , $this->_wwpp_current_version , 'all' );
                wp_enqueue_style( 'wwpp_chosen_css' , WWPP_JS_URL . 'lib/chosen/chosen.min.css' , array() , $this->_wwpp_current_version , 'all' );
                wp_enqueue_style( 'wwpp_order_requirement_per_wholesale_role_css' , WWPP_CSS_URL . 'wwpp-order-requirement-per-wholesale-role.css' , array() , $this->_wwpp_current_version , 'all' );

                wp_enqueue_script( 'wwpp_toastr_js' , WWPP_JS_URL . 'lib/toastr/toastr.min.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_chosen_js' , WWPP_JS_URL . 'lib/chosen/chosen.jquery.min.js' , array('jquery') , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_backEndAjaxServices_js' , WWPP_JS_URL . 'app/modules/BackEndAjaxServices.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_order_requirement_per_wholesale_role_js' , WWPP_JS_URL . 'app/wwpp-order-requirement-per-wholesale-role.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );

                $wholesale_role_order_requirement_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_ORDER_REQUIREMENT_MAPPING );
                if ( !is_array( $wholesale_role_order_requirement_mapping ) )
                    $wholesale_role_order_requirement_mapping = array();

                $all_registered_wholesale_roles = $this->_wwpp_wholesale_roles->getAllRegisteredWholesaleRoles();

                // For cases where there is a role in he mapping that doesn't exist anymore
                $filtered_wholesale_role_order_requirement_mapping = array();

                foreach ( $wholesale_role_order_requirement_mapping as $role => $mapping )
                    if ( array_key_exists( $role , $all_registered_wholesale_roles ) )
                        $filtered_wholesale_role_order_requirement_mapping[ $role  ] = $mapping;

                if ( $filtered_wholesale_role_order_requirement_mapping )
                    update_option( WWPP_OPTION_WHOLESALE_ROLE_ORDER_REQUIREMENT_MAPPING , $filtered_wholesale_role_order_requirement_mapping );

                wp_localize_script( 'wwpp_order_requirement_per_wholesale_role_js' , 'wwpp_order_requirement_per_wholesale_role_var' ,  array(
                    'wholesale_role_txt_with_col'      => __( 'Wholesale Role:' , 'woocommerce-wholesale-prices-premium' ),
                    'min_order_qty_txt_with_col'       => __( 'Minimum Order Quantity:' , 'woocommerce-wholesale-prices-premium' ),
                    'wholesale_role_txt'               => __( 'Wholesale Role' , 'woocommerce-wholesale-prices-premium' ),
                    'min_order_qty_txt'                => __( 'Minimum Order Quantity' , 'woocommerce-wholesale-prices-premium' ),
                    'no_mapping_txt'                   => __( 'No Mappings Found' , 'woocommerce-wholesale-prices-premium' ),
                    'choose_wholesale_role_txt'        => __( 'Choose wholesale role...' , 'woocommerce-wholesale-prices-premium' ),
                    'empty_fields_txt'                 => __( 'Please specify values for the following field/s:' , 'woocommerce-wholesale-prices-premium' ),
                    'form_error_txt'                   => __( 'Form Error' , 'woocommerce-wholesale-prices-premium' ),
                    'success_add_mapping_txt'          => __( 'Successfully Added Wholesale Role Order Requirement Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'failed_add_mapping_txt'           => __( 'Failed To Add New Wholesale Role Order Requirement Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'success_edit_mapping_txt'         => __( 'Successfully Updated Wholesale Role Order Requirement Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'failed_edit_mapping_txt'          => __( 'Failed To Update Wholesale Role Order Requirement Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'delete_mapping_prompt_txt'        => __( 'Clicking OK will remove the current wholesale role order requirement mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'success_delete_mapping_txt'       => __( 'Successfully Deleted Wholesale Role Order Requirement Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'failed_delete_mapping_txt'        => __( 'Failed To Delete Wholesale Role Order Requirement Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'minimum_subtotal_txt_with_col'    => sprintf( __( 'Minimum Sub-total Amount (%1$s):' , 'woocommerce-wholesale-prices-premium' ) , get_woocommerce_currency_symbol() ),
                    'minimum_order_logic_txt_with_col' => __( 'Minimum Order Logic:' , 'woocommerce-wholesale-prices-premium' ),
                    'minimum_subtotal_txt'             => sprintf( __( 'Minimum Sub-total Amount (%1$s)' , 'woocommerce-wholesale-prices-premium' ) , get_woocommerce_currency_symbol() ),
                    'minimum_order_logic_txt'          => __( 'Minimum Order Logic' , 'woocommerce-wholesale-prices-premium' ),
                    'and_txt'                          => __( 'AND' , 'woocommerce-wholesale-prices-premium' ),
                    'or_txt'                           => __( 'OR' , 'woocommerce-wholesale-prices-premium' ),
                    'cancel_txt'                       => __( 'Cancel' , 'woocommerce-wholesale-prices-premium' ),
                    'save_mapping_txt'                 => __( 'Save Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'add_mapping_txt'                  => __( 'Add Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'wholesale_roles'                  => $this->_wwpp_wholesale_roles->getAllRegisteredWholesaleRoles(),
                    'order_requirement'                => ( $filtered_wholesale_role_order_requirement_mapping ) ? $filtered_wholesale_role_order_requirement_mapping : array()
                ) );

            }

            if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'wc-settings' &&
                 isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'wwp_settings' &&
                 isset( $_GET[ 'section' ] ) && $_GET[ 'section' ] == 'wwpp_setting_tax_section' ) {
                // WWPP Tax Settings

                wp_enqueue_style( 'wwpp_toastr_css' , WWPP_JS_URL . 'lib/toastr/toastr.min.css' , array() , $this->_wwpp_current_version , 'all' );
                wp_enqueue_style( 'wwpp_chosen_css' , WWPP_JS_URL . 'lib/chosen/chosen.min.css' , array() , $this->_wwpp_current_version , 'all' );
                wp_enqueue_style( 'wwpp_settings_tax_css' , WWPP_CSS_URL . 'wwpp-settings-tax.css' , array() , $this->_wwpp_current_version , 'all' );

                wp_enqueue_script( 'wwpp_toastr_js' , WWPP_JS_URL . 'lib/toastr/toastr.min.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_chosen_js' , WWPP_JS_URL . 'lib/chosen/chosen.jquery.min.js' , array('jquery') , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_backEndAjaxServices_js' , WWPP_JS_URL . 'app/modules/BackEndAjaxServices.js', array( 'jquery' ) , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_settings_tax_js' , WWPP_JS_URL . 'app/wwpp-settings-tax.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_settings_tax_class_js' , WWPP_JS_URL . 'app/wwpp-settings-tax-class.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );

                wp_localize_script( 'wwpp_settings_tax_js' , 'wwpp_settings_tax_var' , array(
                    'wholesale_role_txt'                =>  __( 'Wholesale Role' , 'woocommerce-wholesale-prices-premium' ),
                    'empty_fields_txt'                  =>  __( 'Please specify values for the following field/s:' , 'woocommerce-wholesale-prices-premium' ),
                    'form_error_txt'                    =>  __( 'Form Error' , 'woocommerce-wholesale-prices-premium' ),
                    'no_mappings_found_txt'             =>  __( 'No Mappings Found' , 'woocommerce-wholesale-prices-premium' ),
                    'success_add_mapping_txt'           =>  __( 'Successfully Added Wholesale Role Tax Option Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'failed_add_mapping_txt'            =>  __( 'Failed To Add New Wholesale Role Tax Option Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'success_edit_mapping_txt'          =>  __( 'Successfully Updated Wholesale Role Tax Option Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'failed_edit_mapping_txt'           =>  __( 'Failed To Update Wholesale Role Tax Option Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'delete_mapping_prompt_confirm_txt' =>  __( 'Clicking OK will remove the current wholesale role tax option mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'success_delete_mapping_txt'        =>  __( 'Successfully Deleted Wholesale Role Tax Option Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'failed_delete_mapping_txt'         =>  __( 'Failed To Delete Wholesale Role Tax Option Mapping' , 'woocommerce-wholesale-prices-premium' )
                ) );
                
                wp_localize_script( 'wwpp_settings_tax_class_js' , 'wwpp_settings_tax_class_var' , array(
                    'please_specify_wholesale_role' => __( 'Please specify a wholesale role' , 'woocommerce-wholesale-prices-premium' ),
                    'please_specify_tax_classes'    => __( 'Please specify tax class' , 'woocommerce-wholesale-prices-premium' ),
                    'no_mappings_found'             => __( 'No Mappings Found' , 'woocommerce-wholesale-prices-premium' ),
                    'form_error'                    => __( 'Form Error' , 'woocommerce-wholesale-prices-premium' ),
                    'failed_save_mapping_entry'     => __( 'Failed to save mapping entry' , 'woocommerce-wholesale-prices-premium' ),
                    'confirm_delete_mapping_entry'  => __( 'Clicking OK will remove the current selected mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'failed_delete_mapping_entry'   => __( 'Failed to delete specified mapping entry' , 'woocommerce-wholesale-prices-premium' )
                ) );

            }

            if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'wc-settings' &&
                 isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'wwp_settings' &&
                 isset( $_GET[ 'section' ] ) && $_GET[ 'section' ] == 'wwpp_setting_shipping_section' ) {
                // WWPP Shipping Settings

                wp_enqueue_style( 'wwpp_toastr_css' , WWPP_JS_URL . 'lib/toastr/toastr.min.css' , array() , $this->_wwpp_current_version , 'all' );
                wp_enqueue_style( 'wwpp_chosen_css' , WWPP_JS_URL . 'lib/chosen/chosen.min.css' , array() , $this->_wwpp_current_version , 'all' );
                wp_enqueue_style( 'wwpp_shipping_controls_custom_field_css' , WWPP_CSS_URL . 'wwpp-shipping-controls-custom-field.css' , array() , $this->_wwpp_current_version , 'all' );

                wp_enqueue_script( 'wwpp_toastr_js' , WWPP_JS_URL . 'lib/toastr/toastr.min.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_chosen_js' , WWPP_JS_URL . 'lib/chosen/chosen.jquery.min.js' , array('jquery') , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_backEndAjaxServices_js' , WWPP_JS_URL . 'app/modules/BackEndAjaxServices.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_shipping_controls_custom_field_js' , WWPP_JS_URL . 'app/wwpp-shipping-controls-custom-field.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );

                wp_localize_script( 'wwpp_shipping_controls_custom_field_js' , 'wwpp_shipping_controls_custom_field_params' , array(
                    'i18n_wholesale_role'                                 => __( 'Wholesale Role' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_shopping_method'                                => __( 'Shipping Method' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_shipping_zone_method'                           => __( 'Shipping Zone Method' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_specify_field_values'                           => __( 'Please specify values for the following field/s:' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_form_error'                                     => __( 'Form Error' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_success_add_shipping_mapping'                   => __( 'Successfully Added Role/Shipping Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_fail_add_shipping_mapping'                      => __( 'Failed To Add New Role/Shipping Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_success_update_shipping_mapping'                => __( 'Successfully Updated Role/Shipping Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_fail_update_shipping_mapping'                   => __( 'Failed To Update Role/Shipping Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_click_ok_remove_shipping_mapping'               => __( 'Clicking OK will remove the current role/shipping mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_no_mappings_found'                              => __( 'No Mappings Found' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_success_delete_shipping_mapping'                => __( 'Successfully Deleted Role/Shipping Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_fail_delete_shipping_mapping'                   => __( 'Failed To Delete Role/Shipping Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_fail_retrieve_table_rate_shipping_zone_methods' => __( 'Failed To Retrieve Table Rate Shipping Zone Methods' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_fail_retrieve_shipping_zone_table_rates'        => __( 'Failed To Retrieve Shipping Zone Table Rates' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_fail_retrieve_table_rate_shipping_zones'        => __( 'Failed To Retrieve Table Rate Shipping Zones' , 'woocommerce-wholesale-prices-premium' ),
                    // WC 2.6.0
                    'i18n_wc2_6_select_shipping_method'                   => __( '--Select Shipping Zone Method--' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_wc2_6_failed_to_retrieve_shipping_zone_methods' => __( 'Failed to retrieve shipping zone methods' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_wc2_6_empty_wholesale_role'                     => __( 'Empty wholesale role' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_wc2_6_empty_non_zoned_shipping_method'          => __( 'Empty non-zoned shipping method' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_wc2_6_empty_shipping_zone'                      => __( 'Empty shipping zone' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_wc2_6_empty_shipping_method'                    => __( 'Empty shipping method' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_wc2_6_please_fill_the_form_properly'            => __( 'Please fill the form properly' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_wc2_6_successfully_add_new_mapping'             => __( 'Successfully added new wholesale / shipping mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_wc2_6_failed_to_add_new_mapping'                => __( 'Failed to add new wholesale / shipping mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_wc2_6_successfully_edited_mapping'              => __( 'Successfully edited wholesale / shipping mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_wc2_6_failed_edited_mapping'                    => __( 'Failed to edit wholesale / shipping mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_wc2_6_no_mappings_found'                        => __( 'No Mappings Found' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_wc2_6_successfully_deleted_mapping'             => __( 'Successfully deleted wholesale / shipping zone mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_wc2_6_failed_deleted_mapping'                   => __( 'Failed to delete wholesale / shipping zone mapping' , 'woocommerce-wholesale-prices-premium' )
                ) );

            }

            if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'wc-settings' &&
                 isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'wwp_settings' &&
                 isset( $_GET[ 'section' ] ) && $_GET[ 'section' ] == 'wwpp_setting_discount_section' ) {
                // WWPP Discount Settings

                wp_enqueue_style( 'wwpp_toastr_css' , WWPP_JS_URL . 'lib/toastr/toastr.min.css' , array(), $this->_wwpp_current_version , 'all' );
                wp_enqueue_style( 'wwpp_chosen_css' , WWPP_JS_URL . 'lib/chosen/chosen.min.css' , array() , $this->_wwpp_current_version , 'all' );
                wp_enqueue_style( 'wwpp_discount_controls_custom_field_css' , WWPP_CSS_URL . 'wwpp-discount-controls-custom-field.css' , array() , $this->_wwpp_current_version , 'all' );

                wp_enqueue_script( 'wwpp_toastr_js' , WWPP_JS_URL . 'lib/toastr/toastr.min.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_chosen_js' , WWPP_JS_URL . 'lib/chosen/chosen.jquery.min.js' , array('jquery') , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_backEndAjaxServices_js' , WWPP_JS_URL . 'app/modules/BackEndAjaxServices.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_discount_controls_custom_field_js' , WWPP_JS_URL . 'app/wwpp-discount-controls-custom-field.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_wholesale_role_cart_qty_based_wholesale_discount_js' , WWPP_JS_URL . 'app/wwpp-wholesale-role-cart-qty-based-wholesale-discount.js' , array( 'jquery' , 'jquery-tiptip' ) , $this->_wwpp_current_version , true );

                wp_localize_script( 'wwpp_discount_controls_custom_field_js' , 'wwpp_discount_controls_custom_field_params' , array (
                    'i18n_specify_wholesale_role'    => __( 'Please Specify Wholesale Role' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_form_error'                => __( 'Form Error' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_input_discount_properly'   => __( 'Please Input Discount Properly' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_role_successfully_added'   => __( 'Successfully Added Role/Discount Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_fail_role_add'             => __( 'Failed To Add New Role/Discount Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_role_successfully_updated' => __( 'Successfully Updated Role/Discount Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_fail_role_update'          => __( 'Failed To Update Role/Discount Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_click_ok_remove_mapping'   => __( 'Clicking OK will remove the current role/discount mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_no_mappings_found'         => __( 'No Mappings Found' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_role_successfully_deleted' => __( 'Successfully Deleted Role/Discount Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_fail_delete_role'          => __( 'Failed To Delete Role/Discount Mapping' , 'woocommerce-wholesale-prices-premium' )
                ) );
                
                wp_localize_script( 'wwpp_wholesale_role_cart_qty_based_wholesale_discount_js' , 'wwpp_wrcqbwd_params' , array(
                    'user_id'                                 => 0,
                    'i18n_please_specify_wholesale_role'      => __( 'Please specify wholesale role' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_invalid_start_qty'                  => __( 'Invalid start quantity' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_invalid_end_qty'                    => __( 'Invalid end quantity' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_invalid_percent_discount'           => __( 'Invalid percent discount' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_form_error'                         => __( 'Form Error' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_no_mappings_found'                  => __( 'No Mappings Found' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_add_mapping_error'                  => __( 'Add Mapping Error' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_failed_to_record_new_mapping_entry' => __( 'Failed to record new mapping entry' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_confirm_remove_mapping'             => __( 'Clicking OK will remove the current wholesale role/discount mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_delete_mapping_error'               => __( 'Delete Mapping Error' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_failed_to_deleted_mapping'          => __( 'Failed to delete specified mapping entry' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_edit_mapping_error'                 => __( 'Edit Mapping Error' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_failed_edit_mapping'                => __( 'Failed to edit mapping entry' , 'woocommerce-wholesale-prices-premium' ),
                ) );

            }

            if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'wc-settings' &&
                 isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'wwp_settings' &&
                 isset( $_GET[ 'section' ] ) && $_GET[ 'section' ] == 'wwpp_setting_payment_gateway_section' ) {
                // WWPP Surcharge Settings

                wp_enqueue_style( 'wwpp_toastr_css' , WWPP_JS_URL . 'lib/toastr/toastr.min.css' , array(), $this->_wwpp_current_version , 'all' );
                wp_enqueue_style( 'wwpp_chosen_css' , WWPP_JS_URL. 'lib/chosen/chosen.min.css' , array() , $this->_wwpp_current_version , 'all' );
                wp_enqueue_style( 'wwpp_payment_gateway_controls_custom_field_css' , WWPP_CSS_URL . 'wwpp-payment-gateway-controls-custom-field.css' , array() , $this->_wwpp_current_version , 'all' );
                wp_enqueue_style( 'wwpp_wholesale_role_payment_gateway_mapping_controls_custom_field_css' , WWPP_CSS_URL . 'wwpp-wholesale-role-payment-gateway-mapping-controls-custom-field.css' , array() , $this->_wwpp_current_version , 'all' );

                wp_enqueue_script( 'wwpp_toastr_js' , WWPP_JS_URL . 'lib/toastr/toastr.min.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_chosen_js' , WWPP_JS_URL . 'lib/chosen/chosen.jquery.min.js' , array('jquery') , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_backEndAjaxServices_js' , WWPP_JS_URL . 'app/modules/BackEndAjaxServices.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_payment_gateway_controls_custom_field_js' , WWPP_JS_URL . 'app/wwpp-payment-gateway-controls-custom-field.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_wholesale_role_payment_gateway_mapping_controls_custom_field_js' , WWPP_JS_URL . 'app/wwpp-wholesale-role-payment-gateway-mapping-controls-custom-field.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );

                wp_localize_script( 'wwpp_payment_gateway_controls_custom_field_js' , 'wwpp_payment_gateway_controls_custom_field_params' , array(
                    'user_id'                              => 0,
                    'i18n_specify_field_values'            => __( 'Please specify values for the following field/s' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_payment_gateway_added'           => __( 'Successfully Added Payment Gateway Surcharge Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_failed_add_payment_gateway'      => __( 'Failed To Add New Payment Gateway Surcharge Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_specify_field_values_with_colon' => __( 'Please specify values for the following field/s:' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_form_error'                      => __( 'Form Error' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_payment_gateway_updated'         => __( 'Successfully Updated Payment Gateway Surcharge Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_failed_update_payment_gateway'   => __( 'Failed To Update Payment Gateway Surcharge Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_click_ok_remove_payment_gateway' => __( 'Clicking OK will remove the current payment gateway surcharge mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_no_mapping_found'                => __( 'No Mappings Found' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_payment_gateway_deleted'         => __( 'Successfully Deleted Payment Gateway Surcharge Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_failed_delete_payment_gateway'   => __( 'Failed To Delete Payment Gateway Surcharge Mapping' , 'woocommerce-wholesale-prices-premium' )
                ) );

                wp_localize_script( 'wwpp_wholesale_role_payment_gateway_mapping_controls_custom_field_js' , 'wwpp_wholesale_role_payment_gateway_mapping_controls_custom_field_params' , array(
                    'i18n_wholesale_role'                 => __( 'Wholesale Role' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_payment_gateways'               => __( 'Payment Gateways' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_form_error'                     => __( 'Form Error' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_success_add_wholesale_role'     => __( 'Successfully Added Wholesale Role / Payment Gateway Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_fail_add_wholesale_role'        => __( 'Failed To Add New Wholesale Role / Payment Gateway Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_success_update_wholesale_role'  => __( 'Successfully Updated Wholesale Role / Payment Gateway Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_fail_update_wholesale_role'     => __( 'Failed To Update Wholesale Role / Payment Gateway Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_click_ok_remove_wholesale_role' => __( 'Clicking OK will remove the current wholesale role / payment gateway mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_no_mappings_found'              => __( 'No Mappings Found' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_success_delete_wholesale_role'  => __( 'Successfully Deleted Wholesale Role / Payment Gateway Mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_fail_delete_wholesale_role'     => __( 'Failed to Delete Wholesale Role / Payment Gateway Mapping' , 'woocommerce-wholesale-prices-premium' )
                ) );

            }

            if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'wc-settings' &&
                isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'wwp_settings' &&
                isset( $_GET[ 'section' ] ) && $_GET[ 'section' ] == 'wwpp_setting_cache_section' ) {
                // WWPP Cache Settings

                wp_enqueue_script( 'wwpp_settings_cache_js' , WWPP_JS_URL . 'app/wwpp-settings-cache.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );
                wp_localize_script( 'wwpp_settings_cache_js' , 'wwpp_settings_cache_args' , array(
                    'nonce_regenerate_new_cache_hash' => wp_create_nonce( 'wwpp_regenerate_new_cache_hash' ),
                    'i18n_fail_var_prod_price_range_clear_cache' => __( 'Failed to clear variable product price range cache' , 'woocommerce-wholesale-prices-premium' )
                ) );
                
            }

            if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'wc-settings' &&
                isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'wwp_settings' &&
                isset( $_GET[ 'section' ] ) && $_GET[ 'section' ] == 'wwpp_setting_help_section' ) {
                // WWPP Help Settings

                wp_enqueue_style( 'wwpp_toastr_css' , WWPP_JS_URL . 'lib/toastr/toastr.min.css' , array(), $this->_wwpp_current_version , 'all' );
                wp_enqueue_style( 'wwpp_settings_debug_css' , WWPP_CSS_URL . 'wwpp-settings-debug.css' , array() , $this->_wwpp_current_version , 'all' );

                wp_enqueue_script( 'wwpp_toastr_js' , WWPP_JS_URL . 'lib/toastr/toastr.min.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_backEndAjaxServices_js' , WWPP_JS_URL . 'app/modules/BackEndAjaxServices.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_settings_debug_js' , WWPP_JS_URL . 'app/wwpp-settings-debug.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );

                wp_localize_script( 'wwpp_settings_debug_js' , 'wwpp_settings_debug_var' , array(
                    'success_initialize_visibility_meta_txt' => __( 'Visibility Meta Successfully Initialized' , 'woocommerce-wholesale-prices-premium' ),
                    'failed_initialize_visibility_meta_txt'  => __( 'Failed To Initialize Visibility Meta' , 'woocommerce-wholesale-prices-premium' ),
                ) );

            }

            if ( $pagenow === 'edit-tags.php' && isset( $_GET[ 'taxonomy' ] ) && $_GET[ 'taxonomy' ] == 'product_cat' ) {
                // New Product Category Page

                wp_enqueue_style( 'wwpp_chosen_css' , WWPP_JS_URL . 'lib/chosen/chosen.min.css' , array() , $this->_wwpp_current_version , 'all' );

                wp_enqueue_script( 'wwpp_chosen_js' , WWPP_JS_URL . 'lib/chosen/chosen.jquery.min.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );
                wp_add_inline_script( 'wwpp_chosen_js' , 'jQuery(document).ready( function( $ ) { $( "#wholesale_role_filter" ).chosen(); } );' );

            }

            if ( $pagenow === 'term.php' && isset( $_GET[ 'taxonomy' ] ) && $_GET[ 'taxonomy' ] == 'product_cat' ) {
                // Single Product Category Edit Page

                wp_enqueue_style( 'wwpp_chosen_css' , WWPP_JS_URL . 'lib/chosen/chosen.min.css' , array() , $this->_wwpp_current_version , 'all' );                
                wp_enqueue_style( 'wwpp_toastr_css' , WWPP_JS_URL . 'lib/toastr/toastr.min.css' , array(), $this->_wwpp_current_version , 'all' );
                wp_enqueue_style( 'wwpp_per-order-qty-wholesale-discount-cat-level_css' , WWPP_CSS_URL . 'wwpp-per-order-qty-wholesale-discount-cat-level.css' , array() , $this->_wwpp_current_version , 'all' );

                wp_enqueue_script( 'wwpp_toastr_js' , WWPP_JS_URL . 'lib/toastr/toastr.min.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_chosen_js' , WWPP_JS_URL . 'lib/chosen/chosen.jquery.min.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );
                wp_add_inline_script( 'wwpp_chosen_js' , 'jQuery(document).ready( function( $ ) { $( "#wholesale_role_filter" ).chosen(); } );' );
                wp_enqueue_script( 'wwpp_per-order-qty-wholesale-discount-cat-level_js' , WWPP_JS_URL . 'app/wwpp-per-order-qty-wholesale-discount-cat-level.js' , array( 'jquery' , 'jquery-tiptip' ) , $this->_wwpp_current_version , true );

                wp_localize_script( 'wwpp_per-order-qty-wholesale-discount-cat-level_js' , 'poqwdcl_params' , array(
                    'i18n_failed_enable_feature'                        => __( 'Fail to enable quantity based wholesale discount' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_failed_enable_mode_2'                         => __( 'Fail to enable quantity based wholesale discount mode 2' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_failed_disable_feature'                       => __( 'Fail to disable quantity based wholesale discount' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_please_specify_wholesale_role'                => __( 'Please specify wholesale role' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_please_specify_start_qty'                     => __( 'Please specify start quantity' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_please_specify_wholesale_discount'            => __( 'Please specify wholesale discount' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_please_specify_index_of_entry_to_edit'        => __( 'Please specify the index of the entry you want to edit' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_please_fill_form_properly'                    => __( 'Please fill the form properly' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_new_wholesale_discount_mapping_added'         => __( 'New wholesale discount mapping added successfully' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_failed_to_add_new_wholesale_discount_mapping' => __( 'Failed to add new wholesale discount mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_wholesale_discount_mapping_edited'            => __( 'Wholesale discount mapping edited successfully' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_failed_to_edit_wholesale_discount_mapping'    => __( 'Failed to edit new wholesale discount mapping' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_confirm_remove_wholesale_discount_mapping'    => __( 'Are you sure to remove this wholesale discount mapping?' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_no_quantity_discount_rules_found'             => __( 'No Quantity Discount Rules Found' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_wholesale_discount_mapping_deleted'           => __( 'Wholesale discount mapping deleted successfully' , 'woocommerce-wholesale-prices-premium' ),
                    'i18n_failed_to_delete_wholesale_discount_mapping'  => __( 'Failed to delete wholesale discount mapping' , 'woocommerce-wholesale-prices-premium' )
                ) );

            }

            if ( $screen->base === 'profile' || $screen->base === 'user-edit' ) {
                // Wholesale user profile page

                wp_enqueue_style( 'wwpp_toastr_css'                                , WWPP_JS_URL . 'lib/toastr/toastr.min.css' , array(), $this->_wwpp_current_version , 'all' );
                wp_enqueue_style( 'wwpp_chosen_css'                                , WWPP_JS_URL . 'lib/chosen/chosen.min.css' , array() , $this->_wwpp_current_version , 'all' );
                wp_enqueue_style( 'wwpp_discount_controls_custom_field_css'        , WWPP_CSS_URL . 'wwpp-discount-controls-custom-field.css' , array() , $this->_wwpp_current_version , 'all' );
                wp_enqueue_style( 'wwpp_payment_gateway_controls_custom_field_css' , WWPP_CSS_URL . 'wwpp-payment-gateway-controls-custom-field.css' , array() , $this->_wwpp_current_version , 'all' );                
                wp_enqueue_style( 'wwpp_user_profile_css'                          , WWPP_CSS_URL . 'wwpp-user-profile.css' , array() , $this->_wwpp_current_version , 'all' );
                
                wp_enqueue_script( 'wwpp_toastr_js'                         , WWPP_JS_URL . 'lib/toastr/toastr.min.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );                
                wp_enqueue_script( 'wwpp_chosen_js'                         , WWPP_JS_URL . 'lib/chosen/chosen.jquery.min.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_user_profile_js'                   , WWPP_JS_URL . 'app/wwpp-user-profile.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_wholesale_role_cart_qty_based_wholesale_discount_js' , WWPP_JS_URL . 'app/wwpp-wholesale-role-cart-qty-based-wholesale-discount.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_backEndAjaxServices_js'                   , WWPP_JS_URL . 'app/modules/BackEndAjaxServices.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );
                wp_enqueue_script( 'wwpp_payment_gateway_controls_custom_field_js' , WWPP_JS_URL . 'app/wwpp-payment-gateway-controls-custom-field.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );

            }

        }

        /**
         * Load frontend related styles and scripts.
         *  Only load em on the right time and on the right place.
         *
         * @since 1.0.0
         * @since 1.12.1 WC 2.6.7 don't need hard refresh on cart page whenever cart items is updated to show error messages.
         * @since 1.14.0 Refactor codebase and move to its own model.
         * @since 1.14.5 Bug Fix. Per parent variable product minimum wholesale order quantity requirement not shown. (WWPP-417).
         * @access public
         */
        public function load_front_end_styles_and_scripts() {

            global $post;

            if ( is_cart() ) {

                wp_enqueue_script( 'wwpp_cart_page_js' , WWPP_JS_URL . 'app/wwpp-cart-page.js' , array( 'jquery' , 'wc-cart' ) , $this->_wwpp_current_version , true );                

            } elseif ( is_checkout() ) {

                wp_enqueue_script( 'wwpp_cart_page_js' , WWPP_JS_URL . 'app/wwpp-cart-page.js' , array( 'jquery' , 'wc-cart' ) , $this->_wwpp_current_version , true );                                

                if ( WWPP_Wholesale_Role_Payment_Gateway::current_user_has_role_payment_gateway_surcharge_mapping() )
                    wp_enqueue_script( 'wwpp_checkout_page_js' , WWPP_JS_URL . 'app/wwpp-checkout-page.js' , array( 'jquery' , 'wc-checkout' ) , $this->_wwpp_current_version , true );

            } elseif ( is_product() ) {

                wp_enqueue_style( 'wwpp_single_product_page_css' , WWPP_CSS_URL . 'wwpp-single-product-page.css' , array() , $this->_wwpp_current_version , 'all' );

                /*
                * This is about the issue where if variable product has variation with all having the same price.
                * Wholesale price for a selected variation won't show on the single variable product page.
                */
                if ( $post->post_type == 'product' ) {

                    $product = wc_get_product( $post->ID );

                    if ( WWP_Helper_Functions::wwp_get_product_type( $product ) == 'variable' ) {

                        $have_minimum_order_qty_set = false;
                        $userWholesaleRole          = $this->_wwpp_wholesale_roles->getUserWholesaleRole();
                        $variationsArr              = array();

                        if ( !empty( $userWholesaleRole ) ) {

                            $has_per_order_quantity_wholesale_price_mapping              = false;
                            $has_per_cat_level_order_quantity_wholesale_discount_mapping = false;

                            $variations             = WWP_Helper_Functions::wwp_get_variable_product_variations( $product );
                            $variable_min_order_qty = get_post_meta( $post->ID , $userWholesaleRole[ 0 ] . "_variable_level_wholesale_minimum_order_quantity" , true );
                            
                            foreach ( $variations as $variation ) {

                                $currVarPrice   = $variation[ 'display_price' ];
                                $price_arr      = WWP_Wholesale_Prices::get_product_wholesale_price_on_shop_v2( $variation[ 'variation_id' ] , $userWholesaleRole );
                                $wholesalePrice = $price_arr[ 'wholesale_price' ];
                                $minimumOrder   = get_post_meta( $variation[ 'variation_id' ] , $userWholesaleRole[ 0 ] . "_wholesale_minimum_order_quantity" , true ); // Per variation level

                                // Per parent variable level
                                if ( !$minimumOrder )
                                    $minimumOrder = $variable_min_order_qty;

                                // Always default to 1
                                if ( $minimumOrder )
                                    $have_minimum_order_qty_set = true;
                                elseif ( !$minimumOrder )
                                    $minimumOrder = 1;
                                
                                // Check if product have per product level order quantity based wholesale price
                                if ( !empty( $wholesalePrice ) && !$has_per_order_quantity_wholesale_price_mapping ) {

                                    $enabled = get_post_meta( $variation[ 'variation_id' ] , WWPP_POST_META_ENABLE_QUANTITY_DISCOUNT_RULE , true );
                                    $mapping = get_post_meta( $variation[ 'variation_id' ] , WWPP_POST_META_QUANTITY_DISCOUNT_RULE_MAPPING , true );
                                    if ( !is_array( $mapping ) )
                                        $mapping = array();

                                    $has_mapping_entry = false;

                                    foreach ( $mapping as $map ) {

                                        if ( $map[ 'wholesale_role' ] === $userWholesaleRole[ 0 ] ) {

                                            $has_mapping_entry = true;
                                            break;

                                        }

                                    }
                                        
                                    if ( $enabled == 'yes' && $has_mapping_entry )
                                        $has_per_order_quantity_wholesale_price_mapping = true;

                                }

                                /**
                                 * WWPP-373
                                 * Check if product have product category level wholesale pricing set.
                                 * Have category level discount.
                                 * We do not need to check for the per qty based discount on cat level as checking the base cat discount is enough
                                 */
                                if ( !empty( $wholesalePrice ) && !$has_per_cat_level_order_quantity_wholesale_discount_mapping ) {

                                    $base_term_id_discount = $this->_wwpp_wholesale_price_product_category->get_base_term_id_and_wholesale_discount( WWP_Helper_Functions::wwp_get_product_id( $product ) , $userWholesaleRole );

                                    if ( !is_null( $base_term_id_discount[ 'term_id' ] ) && !is_null( $base_term_id_discount[ 'discount' ] ) )
                                        $has_per_cat_level_order_quantity_wholesale_discount_mapping = true;

                                }
                                
                                $variationsArr[] =  array(
                                                        'variation_id'        => $variation[ 'variation_id' ],
                                                        'value'               => (int) $minimumOrder,
                                                        'minimum_order'       => (int) $minimumOrder,
                                                        'raw_regular_price'   => (float) $currVarPrice,
                                                        'raw_wholesale_price' => (float) $wholesalePrice,
                                                        'has_wholesale_price' => is_numeric( $wholesalePrice )
                                                    );
                                                    
                            }

                            // #WWPP-207
                            // Check if variable product has same regular price and same wholesale price
                            $same_reg_price       = true;
                            $temp_reg_price       = null;
                            $same_wholesale_price = true;
                            $temp_wholesale_price = null;

                            foreach ( $variationsArr as $varData ) {

                                if ( is_null( $temp_reg_price ) )
                                    $temp_reg_price = $varData[ 'raw_regular_price' ];
                                elseif ( $same_reg_price )
                                    $same_reg_price = $temp_reg_price == $varData[ 'raw_regular_price' ];

                                if ( is_null( $temp_wholesale_price ) )
                                    $temp_wholesale_price = $varData[ 'raw_wholesale_price' ];
                                elseif ( $same_wholesale_price )
                                    $same_wholesale_price = $temp_wholesale_price == $varData[ 'raw_wholesale_price' ];

                            }

                            $same_prices = $same_reg_price && $same_wholesale_price && !$has_per_order_quantity_wholesale_price_mapping && !$has_per_cat_level_order_quantity_wholesale_discount_mapping;
                            
                            if ( $same_prices && $have_minimum_order_qty_set ) { // Load the price html markup but only show the minimum order quantity part

                                add_filter( 'woocommerce_show_variation_price' , function() { return true; } );
                                wp_enqueue_style( 'wwpp_variable_product_page_css' , WWPP_CSS_URL . 'wwpp-variable-product-page.css' , array() , 'all' );

                            } elseif ( !$same_prices ) // If prices are not the same, make sure to load the price html markup
                                add_filter( 'woocommerce_show_variation_price' , function() { return true; } );
                                                        
                            wp_enqueue_script( 'wwpp_variable_product_page_js' , WWPP_JS_URL . 'app/wwpp-single-variable-product-page.js' , array( 'jquery' ) , $this->_wwpp_current_version , true );

                        }

                    }

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
         * @since 1.14.0
         * @access public
         */
        public function run() {

            add_action( 'admin_enqueue_scripts' , array( $this , 'load_back_end_styles_and_scripts' )  , 10 , 1 );
            add_action( "wp_enqueue_scripts"    , array( $this , 'load_front_end_styles_and_scripts' ) , 10 );

        }

    }

}

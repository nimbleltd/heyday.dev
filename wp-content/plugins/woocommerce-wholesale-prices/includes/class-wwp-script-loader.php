<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWP_Script_Loader' ) ) {

    /**
     * Model that houses the logic of loading in scripts to various pages of the plugin.
     *
     * @since 1.4.0
     */
    class WWP_Script_Loader {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWP_Script_Loader.
         *
         * @since 1.4.0
         * @access private
         * @var WWP_Script_Loader
         */
        private static $_instance;

        /**
         * Model that houses the logic of retrieving information relating to wholesale role/s of a user.
         *
         * @since 1.4.0
         * @access private
         * @var WWP_Wholesale_Roles
         */
        private $_wwp_wholesale_roles;

        /**
         * Model that houses logic of wholesale prices.
         *
         * @since 1.4.0
         * @access private
         * @var WWP_Wholesale_Prices
         */
        private $_wwp_wholesale_prices;

        /**
         * Current WWP version.
         *
         * @since 1.3.1
         * @access private
         * @var int
         */
        private $_wwp_current_version;




        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        /**
         * WWP_Script_Loader constructor.
         *
         * @since 1.4.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWP_Script_Loader model.
         */
        public function __construct( $dependencies ) {

            $this->_wwp_wholesale_roles  = $dependencies[ 'WWP_Wholesale_Roles' ];
            $this->_wwp_wholesale_prices = $dependencies[ 'WWP_Wholesale_Prices' ];
            $this->_wwp_current_version  = $dependencies[ 'WWP_CURRENT_VERSION' ];

        }

        /**
         * Ensure that only one instance of WWP_Script_Loader is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.4.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWP_Script_Loader model.
         * @return WWP_Script_Loader
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

            $screen = get_current_screen();

            $post_type = get_post_type();
            if ( !$post_type && isset( $_GET[ 'post_type' ] ) )
                $post_type = $_GET[ 'post_type' ];

            wp_enqueue_style( 'wwp_wcoverrides_css' , WWP_CSS_URL . 'wwp-back-end-wcoverrides.css' , array() , $this->_wwp_current_version , 'all' );

            $review_response = get_option( WWP_REVIEW_REQUEST_RESPONSE );

            // Show review request popup
            if ( is_admin() && current_user_can( 'manage_options' ) && get_option( WWP_SHOW_REQUEST_REVIEW ) === 'yes' && ( $review_response === 'review-later' || empty( $review_response ) ) ) {

                if ( WWP_Helper_Functions::is_plugin_active( 'woocommerce-wholesale-prices-premium/woocommerce-wholesale-prices-premium.bootstrap.php' ) ) {

                    $msg = sprintf( __( '<p>We see you have been using Wholesale Suite for a couple of weeks now – thank you once again for your purchase and we hope you are enjoying it so far!</p>
                                         <p>We’d really appreciate it if you could take a few minutes to write a 5-star review of our Wholesale Prices plugin on WordPress.org!</p>
                                         <p>Your comment will go a long way to helping us grow and giving new users the confidence to give us a try.</p>
                                         <p>Thanks in advance, we are looking forward to reading it!</p>
                                         <p>PS. If you ever need support, please just <a href="%1$s" target="_blank">get in touch here.</a></p>' , "woocommerce-wholesale-prices" ) , 'https://goo.gl/pDDRTp' );

                } else {

                    $msg = __( "<p>Thanks for using our free WooCommerce Wholesale Prices plugin – we hope you are enjoying it so far.</p>
                                <p>We’d really appreciate it if you could take a few minutes to write a 5-star review of our Wholesale Prices plugin on WordPress.org!</p>
                                <p>Your comment will go a long way to helping us grow and giving new users the confidence to give us a try.</p>
                                <p>Thanks in advance, we are looking forward to reading it!</p>" , "woocommerce-wholesale-prices" );

                }

                wp_enqueue_style( 'wwp-vex-css' ,             WWP_JS_URL . 'lib/vexjs/vex.css'             , array() , $this->_wwp_current_version , 'all' );
                wp_enqueue_style( 'wwp-vex-theme-plain-css' , WWP_JS_URL . 'lib/vexjs/vex-theme-plain.css' , array() , $this->_wwp_current_version , 'all' );
                wp_enqueue_script( 'wwp-vex-js' ,             WWP_JS_URL . 'lib/vexjs/vex.combined.min.js' , array() , $this->_wwp_current_version , true );
                wp_enqueue_script( 'wwp-review-request' ,     WWP_JS_URL . 'backend/wwp-review-request.js' , array() , $this->_wwp_current_version , true );
                wp_localize_script( 'wwp-review-request' , 'review_request_args' , array(
                    'js_url'      => WWP_IMAGES_URL,
                    'msg'         => $msg,
                    'review_link' => 'https://goo.gl/FVRQcH'
                ) );

            }


            if ( in_array( $screen->id , array( 'edit-product' ) ) ) {
                // Product listing

                wp_enqueue_style( 'wwp_cpt_product_listing_admin_main_css' , WWP_CSS_URL . 'backend/cpt/product/wwp-cpt-product-listing-admin-main.css' , array() , $this->_wwp_current_version , 'all' );

                wp_enqueue_script( 'wwp_cpt_product_listing_admin_main_js' , WWP_JS_URL . 'backend/cpt/product/wwp-cpt-product-listing-admin-main.js' , array( 'jquery' , 'jquery-ui-core' , 'jquery-ui-accordion' ) , $this->_wwp_current_version ); // Must not be loaded on footer, else it won't work

            } else if ( ( $handle == 'post-new.php' || $handle == 'post.php' ) && $post_type == 'product' ) {
                // Single product admin page ( new and edit pages )

                wp_enqueue_style( 'wwp_cpt_product_single_admin_main_css' , WWP_CSS_URL . 'backend/cpt/product/wwp-cpt-product-single-admin-main.css' , array() , $this->_wwp_current_version , 'all' );

                wp_enqueue_script( 'wwp_cpt_product_single_admin_main_js' , WWP_JS_URL . 'backend/cpt/product/wwp-cpt-product-single-admin-main.js' , array( 'jquery' , 'jquery-ui-core' , 'jquery-ui-accordion' ) , $this->_wwp_current_version , true );
                wp_enqueue_script( 'wwp_single_variable_product_admin_custom_bulk_actions_js' , WWP_JS_URL . 'backend/cpt/product/wwp-single-variable-product-admin-custom-bulk-actions.js' , array( 'jquery' ) , $this->_wwp_current_version , true );

                wp_localize_script( 'wwp_single_variable_product_admin_custom_bulk_actions_js' , 'wwp_custom_bulk_actions_params' , array(
                    'wholesale_roles'     => $this->_wwp_wholesale_roles->getAllRegisteredWholesaleRoles(),
                    'i18n_prompt_message' => __( 'Enter a value (leave blank to remove pricing)' , 'woocommerce-wholesale-prices' )
                ) );

            }

        }

        /**
         * Load frontend related styles and scripts.
         * Only load em on the right time and on the right place.
         *
         * @since 1.0.0
         * @since 1.4.0 Refactor codebase and move to its own model.
         * @access public
         */
        public function load_front_end_styles_and_scripts() {

            global $post;

            if ( is_product() ) {

                /*
                * This is about the issue where if variable product has variation with all having the same price.
                * Wholesale price for a selected variation won't show on the single variable product page.
                *
                * This issue is already fixed in wwpp. Now if wwpp is installed and active, let wwpp fix this issue.
                * Only fix this issue here in wwp if wwpp is not present.
                *
                * Note the fix on WWPP is different from the fix here coz WWPP has additional features to consider compared to WWP.
                */
                if ( !WWP_Helper_Functions::is_plugin_active( 'woocommerce-wholesale-prices-premium/woocommerce-wholesale-prices-premium.bootstrap.php' ) ) {

                    wp_enqueue_style( 'wwp_single_product_page_css' , WWP_CSS_URL . 'frontend/product/wwp-single-product-page.css' , array() , $this->_wwp_current_version , 'all' );

                    if ( $post->post_type == 'product' ) {

                        $product = wc_get_product( $post->ID );

                        if ( WWP_Helper_Functions::wwp_get_product_type( $product ) === 'variable' ) {

                            $userWholesaleRole = $this->_wwp_wholesale_roles->getUserWholesaleRole();
                            $variationsArr = array();

                            if ( !empty( $userWholesaleRole ) ) {

                                $variations = WWP_Helper_Functions::wwp_get_variable_product_variations( $product );

                                foreach ( $variations as $variation ) {

                                    $variationProduct = wc_get_product( $variation[ 'variation_id' ] );

                                    $currVarPrice    = $variation[ 'display_price' ];
                                    $price_arr       = $this->_wwp_wholesale_prices->get_product_wholesale_price_on_shop_v2( $variation[ 'variation_id' ] , $userWholesaleRole );
                                    $wholesalePrice  = $price_arr[ 'wholesale_price' ];
                                    $variationsArr[] = array(
                                        'variation_id'        => $variation[ 'variation_id' ],
                                        'raw_regular_price'   => (float) $currVarPrice,
                                        'raw_wholesale_price' => (float) $wholesalePrice,
                                        'has_wholesale_price' => is_numeric( $wholesalePrice )
                                    );

                                }

                                // #WWP-51
                                // Check if variable product has same regular price and same wholesale price
                                // If true then don't load the script below
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

                                $same_prices = $same_reg_price && $same_wholesale_price;

                                if ( !$same_prices ) // If prices are not the same, make sure to load the price html markup
                                    add_filter( 'woocommerce_show_variation_price' , function() { return true; } );

                            } // if ( !empty( $userWholesaleRole ) )

                        } // if ( WWP_Helper_Functions::wwp_get_product_type( $product ) === 'variable' )

                    } // if ( $post->post_type == 'product' )

                } // if wwpp is not active

            }

        }

        /**
         * Execute model.
         *
         * @since 1.4.0
         * @access public
         */
        public function run() {

            add_action( 'admin_enqueue_scripts' , array( $this , 'load_back_end_styles_and_scripts' )  , 10 , 1 );
            add_action( "wp_enqueue_scripts"    , array( $this , 'load_front_end_styles_and_scripts' ) , 10 );

        }

    }

}

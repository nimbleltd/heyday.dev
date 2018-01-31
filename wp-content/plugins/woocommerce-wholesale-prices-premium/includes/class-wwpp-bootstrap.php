<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_Bootstrap' ) ) {

    /**
     * Model that houses the logic of bootstrapping the plugin.
     *
     * @since 1.13.0
     */
    class WWPP_Bootstrap {
        
        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWPP_Bootstrap.
         *
         * @since 1.13.0
         * @access private
         * @var WWPP_Bootstrap
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
         * Model that houses the logic relating to payment gateways.
         *
         * @since 1.13.0
         * @access private
         * @var WWPP_Wholesale_Role_Payment_Gateway
         */
        private $_wwpp_wholesale_role_payment_gateway;

        /**
         * Array of registered wholesale roles.
         *
         * @since 1.13.0
         * @access private
         * @var array
         */
        private $_registered_wholesale_roles;

        /**
         * Current WWP version.
         *
         * @since 1.13.3
         * @access private
         * @var int
         */
        private $_wwpp_current_version;




        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        /**
         * WWPP_Bootstrap constructor.
         *
         * @since 1.13.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Bootstrap model.
         */
        public function __construct( $dependencies ) {

            $this->_wwpp_wholesale_roles                = $dependencies[ 'WWPP_Wholesale_Roles' ];
            $this->_wwpp_wholesale_role_payment_gateway = $dependencies[ 'WWPP_Wholesale_Role_Payment_Gateway' ];
            $this->_wwpp_current_version                = $dependencies[ 'WWPP_CURRENT_VERSION' ];

            $this->_registered_wholesale_roles = $this->_wwpp_wholesale_roles->getAllRegisteredWholesaleRoles();

        }

        /**
         * Ensure that only one instance of WWPP_Bootstrap is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.13.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Bootstrap model.
         * @return WWPP_Bootstrap
         */
        public static function instance( $dependencies ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }




        /*
        |------------------------------------------------------------------------------------------------------------------
        | Internationalization and Localization
        |------------------------------------------------------------------------------------------------------------------
        */

        /**
         * Load plugin text domain.
         *
         * @since 1.2.0
         * @since 1.13.0 Refactor codebase and move to its dedicated model.
         * @access public
         */
        public function load_plugin_text_domain() {

            load_plugin_textdomain( 'woocommerce-wholesale-prices-premium' , false , WWPP_PLUGIN_BASE_PATH . 'languages/' );

        }
        



        /*
         |------------------------------------------------------------------------------------------------------------------
         | Bootstrap/Shutdown Functions
         |------------------------------------------------------------------------------------------------------------------
         */
        
        /**
         * Plugin activation hook callback.
         *
         * @since 1.0.0
         * @since 1.12.5 Add flush rewrite rules
         * @since 1.13.0 Add multisite support
         * @access public
         */
        public function activate( $network_wide ) {

            global $wpdb;

            if ( is_multisite() ) {

                if ( $network_wide ) {

                    // get ids of all sites
                    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

                    foreach ( $blog_ids as $blog_id ) {

                        switch_to_blog( $blog_id );
                        $this->_activate( $blog_id );

                    }

                    restore_current_blog();

                } else
                    $this->_activate( $wpdb->blogid ); // activated on a single site, in a multi-site

            } else
                $this->_activate( $wpdb->blogid ); // activated on a single site
            
        }

        /**
         * Plugin activation codebase.
         *
         * @since 1.13.0
         * @access private
         *
         * @param int $blog_id Site id.
         */
        private function _activate( $blog_id ) {

            if ( !get_option( 'wwpp_settings_wholesale_price_title_text' , false ) )
                update_option( 'wwpp_settings_wholesale_price_title_text' , 'Wholesale Price:' );
            
            if ( !get_option( 'wwpp_settings_variable_product_price_display' , false ) )
                update_option( 'wwpp_settings_variable_product_price_display' , 'price-range' );

            // Initialize product visibility related meta
            wp_schedule_single_event( time() , WWPP_CRON_INITIALIZE_PRODUCT_WHOLESALE_VISIBILITY_FILTER );
            
            // Set all existing payment tokens as not default
            $this->_wwpp_wholesale_role_payment_gateway->undefault_existing_payment_tokens();

            flush_rewrite_rules();

            update_option( 'wwpp_option_activation_code_triggered' , 'yes' );

            update_option( 'wwpp_option_installed_version' , $this->_wwpp_current_version );


            // Clear WC Transients on activation
            // This is very important, we need to do this specially with the advent of version 1.15.0
            // Required by these functions ( filter_available_variable_product_variations )
            // If we don't clear the product transients, 'woocommerce_get_children' won't be triggered therefore 'filter_available_variable_product_variations' function will not be executed.
            // The reason being is WC will just use the transient data.
            // We only need to do this on plugin activation tho, as every subsequent product update, it will clear the transient for that specific product.
            // Only clear the product transient
            wc_delete_product_transients();

        }

        /**
         * The main purpose for this function as follows.
         * Get all products
         * Check if product has no 'wwpp_product_wholesale_visibility_filter' meta key yet
         * If above is true, then set a meta for the current product with a key of 'wwpp_product_wholesale_visibility_filter' and value of 'all'
         *
         * This in turn specify that this product is available for viewing for all users of the site.
         * and yup, the sql statement below does all that.
         *
         * @since 1.4.2
         * @since 1.13.0 Refactor codebase and move to its own model.
         * @since 1.14.0 Make it handle ajax callback 'wp_ajax_wwpp_initialize_product_visibility_meta'.
         * @access public
         *
         * @return bool Operation status.
         */
        public function initialize_product_visibility_filter_meta() {

            global $wpdb;

            /*
             * In version 1.13.0 we refactored the Wholesale Exclusive Variation feature.
             * Now it is an enhanced select box instead of the old check box.
             * This gives us more flexibility including the 'all' value if no wholesale role is selected.
             * In light to this, we must migrate the old <wholesale_role>_exclusive_variation data to the new 'wwpp_product_visibility_filter'.
             */
            foreach ( $this->_registered_wholesale_roles as $role_key => $role ) {

                $wpdb->query("
                    INSERT INTO $wpdb->postmeta ( post_id , meta_key , meta_value )
                    SELECT $wpdb->posts.ID , 'wwpp_product_wholesale_visibility_filter' , '" . $role_key . "'
                    FROM $wpdb->posts
                    WHERE $wpdb->posts.post_type IN ( 'product_variation' )
                    AND $wpdb->posts.ID IN (
                        SELECT $wpdb->posts.ID
                        FROM $wpdb->posts
                        INNER JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )
                        WHERE meta_key = '" . $role_key . "_exclusive_variation'
                        AND meta_value = 'yes'
                    )
                ");

            }
            

            /*
             * Initialize wwpp_product_wholesale_visibility_filter meta
             * This meta is in charge of product visibility. We need to set this to 'all' as mostly
             * all imported products will not have this meta. Meaning, all imported products
             * with no 'wwpp_product_wholesale_visibility_filter' meta set is visible to all users by default.
             */
            $wpdb->query("
                INSERT INTO $wpdb->postmeta ( post_id , meta_key , meta_value )
                SELECT $wpdb->posts.ID , 'wwpp_product_wholesale_visibility_filter' , 'all'
                FROM $wpdb->posts
                WHERE $wpdb->posts.post_type IN ( 'product' , 'product_variation' )
                AND $wpdb->posts.ID NOT IN (
                    SELECT $wpdb->posts.ID
                    FROM $wpdb->posts
                    INNER JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )
                    WHERE meta_key = 'wwpp_product_wholesale_visibility_filter' )
            ");

            /*
             * Address instances where the wwpp_product_wholesale_visibility_filter meta is present but have empty value.
             * This can possibly occur when importing products using external tool that tries to import meta data but fails to properly save the data.
             * Ticket : WWPP-434
             */
            $wpdb->query("
                UPDATE $wpdb->postmeta
                SET meta_value = 'all'
                WHERE meta_key = 'wwpp_product_wholesale_visibility_filter'
                AND meta_value = ''
            ");


            /*
             * Properly set {wholesale_role}_have_wholesale_price meta
             * There will be cases where users import products from external sources and they
             * "set up" wholesale prices via external tools prior to importing
             * We need to handle those cases.
             */
            foreach ( $this->_registered_wholesale_roles as $role_key => $role ) {

                // We need to delete prior to inserting, else we will have a stacked meta, same multiple meta for a single post
                $wpdb->query("
                    DELETE FROM $wpdb->postmeta
                    WHERE meta_key = '{$role_key}_have_wholesale_price'
                ");

                $wpdb->query("
                    INSERT INTO $wpdb->postmeta ( post_id , meta_key , meta_value )
                    SELECT $wpdb->posts.ID , '{$role_key}_have_wholesale_price' , 'yes'
                    FROM $wpdb->posts
                    WHERE $wpdb->posts.post_type = 'product'
                    AND $wpdb->posts.ID IN (

                            SELECT DISTINCT $wpdb->postmeta.post_id
                            FROM $wpdb->postmeta
                            WHERE (
                                    ( meta_key = '{$role_key}_wholesale_price' AND meta_value > 0  )
                                    OR
                                    ( meta_key = '{$role_key}_variations_with_wholesale_price' AND meta_value != '' )
                                )

                        )
                ");

            }

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
                
                @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
                echo wp_json_encode( array( 'status' => 'success' ) );
                wp_die();

            } else
                return true;
            
        }

        /**
         * Plugin deactivation hook callback.
         *
         * @since 1.0.0
         * @since 1.12.5 Add flush rewrite rules.
         * @since 1.13.0 Add multisite support.
         * @access public
         */
        public function deactivate( $network_wide ) {

            global $wpdb;

            // check if it is a multisite network
            if ( is_multisite() ) {

                // check if the plugin has been activated on the network or on a single site
                if ( $network_wide ) {

                    // get ids of all sites
                    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

                    foreach ( $blog_ids as $blog_id ) {

                        switch_to_blog( $blog_id );
                        $this->_deactivate( $wpdb->blogid );

                    }

                    restore_current_blog();

                } else
                    $this->_deactivate( $wpdb->blogid ); // activated on a single site, in a multi-site

            } else
                $this->_deactivate( $wpdb->blogid ); // activated on a single site

        }

        /**
         * Plugin deactivation codebase.
         *
         * @since 1.13.0
         * @access public
         *
         * @param int $blog_id Site id.
         */
        private function _deactivate( $blog_id ) {

            flush_rewrite_rules();

            wc_delete_product_transients();
            
        }

        /**
         * Method to initialize a newly created site in a multi site set up.
         *
         * @since 1.13.0
         * @access public
         *
         * @param int    $blog_id Blog ID.
         * @param int    $user_id User ID.
         * @param string $domain  Site domain.
         * @param string $path    Site path.
         * @param int    $site_id Site ID. Only relevant on multi-network installs.
         * @param array  $meta    Meta data. Used to set initial site options.
         */
        public function new_mu_site_init( $blog_id , $user_id , $domain , $path , $site_id , $meta ) {

            if ( is_plugin_active_for_network( 'woocommerce-wholesale-prices-premium/woocommerce-wholesale-prices-premium.plugin.php' ) ) {

                switch_to_blog( $blog_id );
                $this->_activate( $blog_id );
                restore_current_blog();

            }

        }

        /**
         * Plugin initializaton.
         * 
         * @since 1.2.9
         * @since 1.13.0 Add multi-site support.
         */
        public function initialize() {

            // Check if activation has been triggered, if not trigger it
            // Activation codes are not triggered if plugin dependencies are not present and this plugin is activated.
            if ( version_compare( get_option( 'wwpp_option_installed_version' , false ) , $this->_wwpp_current_version , '!=' ) || get_option( 'wwpp_option_activation_code_triggered' , false ) !== 'yes' ) {

                if ( ! function_exists( 'is_plugin_active_for_network' ) )
                    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

                $network_wide = is_plugin_active_for_network( 'woocommerce-wholesale-prices-premium/woocommerce-wholesale-prices-premium.plugin.php' );
                $this->activate( $network_wide );

            }
            
        }




        /*
        |---------------------------------------------------------------------------------------------------------------
        | Plugin Custom Action Links
        |---------------------------------------------------------------------------------------------------------------
        */

        /**
         * Add plugin listing custom action link ( settings ).
         *
         * @since 1.0.2
         * @since 1.12.8 Rename 'Plugin Settings' and 'License Settings' to just 'Settings' and 'Licence' respectively.
         * @since 1.14.0 Move to its proper model.
         * @access public
         * 
         * @param array  $links Array of links.
         * @param string $file  Plugin basename.
         * @return array Filtered array of links.
         */
        public function add_plugin_listing_custom_action_links ( $links , $file ) {

            if ( $file == plugin_basename( WWPP_PLUGIN_PATH . 'woocommerce-wholesale-prices-premium.bootstrap.php' ) ) {

                $settings_link = '<a href="admin.php?page=wc-settings&tab=wwp_settings">' . __( 'Settings' , 'woocommerce-wholesale-prices-premium' ) . '</a>';
                $license_link  = '<a href="options-general.php?page=wwc_license_settings&tab=wwpp">' . __( 'License' , 'woocommerce-wholesale-prices-premium' ) . '</a>';

                array_unshift( $links , $license_link );
                array_unshift( $links , $settings_link );

            }

            return $links;

        }




        /*
        |---------------------------------------------------------------------------------------------------------------
        | Execute Model
        |---------------------------------------------------------------------------------------------------------------
        */
        
        /**
         * Register model ajax handlers.
         *
         * @since 1.14.0
         * @access public
         */
        public function register_ajax_handler() {

            add_action( "wp_ajax_wwpp_initialize_product_visibility_meta" , array( $this , 'initialize_product_visibility_filter_meta' ) );

        }

        /**
         * Execute model.
         *
         * @since 1.13.0
         * @access public
         */
        public function run() {

            // Load Plugin Text Domain
            add_action( 'plugins_loaded' , array( $this , 'load_plugin_text_domain' ) );

            register_activation_hook( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'woocommerce-wholesale-prices-premium' . DIRECTORY_SEPARATOR . 'woocommerce-wholesale-prices-premium.bootstrap.php' , array( $this , 'activate' ) );
            register_deactivation_hook( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'woocommerce-wholesale-prices-premium' . DIRECTORY_SEPARATOR . 'woocommerce-wholesale-prices-premium.bootstrap.php' , array( $this , 'deactivate' ) );

            // Execute plugin initialization ( plugin activation ) on every newly created site in a multi site set up
            add_action( 'wpmu_new_blog' , array( $this , 'new_mu_site_init' ) , 10 , 6 );

            // Initialize Plugin
            add_action( 'init' , array( $this , 'initialize' ) );

            add_action( WWPP_CRON_INITIALIZE_PRODUCT_WHOLESALE_VISIBILITY_FILTER , array( $this , 'initialize_product_visibility_filter_meta' ) );

            add_action( 'init' , array( $this , 'register_ajax_handler' ) );

            add_filter( 'plugin_action_links' , array( $this , 'add_plugin_listing_custom_action_links' ) , 10 , 2 );

        }

    }

}

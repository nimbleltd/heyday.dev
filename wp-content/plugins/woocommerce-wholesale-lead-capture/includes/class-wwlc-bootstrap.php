<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWLC_Bootstrap' ) ) {

    class WWLC_Bootstrap {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWLC_Bootstrap.
         *
         * @since 1.6.3
         * @access private
         * @var WWLC_Bootstrap
         */
        private static $_instance;

        /**
         * Model that houses the logic of retrieving information relating to Forms.
         *
         * @since 1.6.3
         * @access private
         * @var WWLC_Forms
         */
        private $_wwlc_forms;

        /**
         * Current WWLC version.
         *
         * @since 1.6.3
         * @access private
         * @var int
         */
        private $_wwlc_current_version;

        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        /**
         * WWLC_Bootstrap constructor.
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWLC_Bootstrap model.
         *
         * @access public
         * @since 1.6.3
         */
        public function __construct( $dependencies ) {

            $this->_wwlc_forms = $dependencies[ 'WWLC_Forms' ];
            $this->_wwlc_current_version = $dependencies[ 'WWLC_CURRENT_VERSION' ];

        }

        /**
         * Ensure that only one instance of WWLC_Bootstrap is loaded or can be loaded (Singleton Pattern).
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWLC_Bootstrap model.
         *
         * @return WWLC_Bootstrap
         * @since 1.6.3
         */
        public static function instance( $dependencies  = null ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }

        /**
         * Load plugin text domain.
         *
         * @since 1.3.1
         */
        public function wwlc_load_plugin_text_domain () {

            load_plugin_textdomain( 'woocommerce-wholesale-lead-capture' , false , WWLC_PLUGIN_BASE_PATH . 'languages/' );

        }

        /**
         * Plugin initialization.
         *
         * @since 1.0.0
         * @since 1.6.3 Multisite compatibility. Run the initialization of plugin data only once.
         */
        public function wwlc_initialize() {

            $activation_flag = get_option( WWLC_ACTIVATION_CODE_TRIGGERED , false );
            $installed_version = get_option( WWLC_OPTION_INSTALLED_VERSION , false );
    
            if ( version_compare( $installed_version , $this->_wwlc_current_version , '!=' ) || $activation_flag != 'yes' ) {

                if ( ! function_exists( 'is_plugin_active_for_network' ) )
                    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

                $network_wide = is_plugin_active_for_network( 'woocommerce-wholesale-lead-capture/woocommerce-wholesale-lead-capture.bootstrap.php' );

                $this->wwlc_activate( $network_wide );

            }

        }

        /**
         * Plugin activation hook callback.
         *
         * @param bool $network_wide
         *
         * @since 1.0.0
         * @since 1.6.3 Multisite Compatibility
         */
        public function wwlc_activate( $network_wide ) {

            global $wpdb;

            if( is_multisite() ){

                if( $network_wide ){

                    // get ids of all sites
                    $blogIDs = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

                    foreach( $blogIDs as $blogID ){

                        switch_to_blog( $blogID );
                        $this->wwlc_activate_action( $blogID );

                    }

                    restore_current_blog();

                }else{

                    // activated on a single site, in a multi-site
                    $this->wwlc_activate_action( $wpdb->blogid );

                }

            }else{

                // activated on a single site
                $this->wwlc_activate_action( $wpdb->blogid );

            }

        }

        /**
         * Perform actions on plugin activation.
         *
         * @since 1.6.3
         */
        private function wwlc_activate_action(){

            // Add inactive user role
            add_role( WWLC_UNAPPROVED_ROLE , 'Unapproved' , array() );
            add_role( WWLC_UNMODERATED_ROLE , 'Unmoderated' , array() );
            add_role( WWLC_REJECTED_ROLE , 'Rejected' , array() );
            add_role( WWLC_INACTIVE_ROLE , 'Inactive' , array() );

            // On activation, create registration, thank you and login page
            // Then save these pages on the general settings of this plugin
            // relating to log in and registration page options.
            // But only do this if, the user has not yet set a login, thank you and registration page ( Don't overwrite the users settings )

            if ( !get_option( 'wwlc_general_login_page' ) && !get_option( 'wwlc_general_registration_page' ) && !get_option( 'wwlc_general_registration_thankyou' ) ) {

                if ( $this->_wwlc_forms->wwlc_create_lead_pages( null , false ) ) {

                    $login_page_url = get_permalink( (int) get_option( WWLC_OPTIONS_LOGIN_PAGE_ID ) );
                    $registration_page_url = get_permalink( (int) get_option( WWLC_OPTIONS_REGISTRATION_PAGE_ID ) );
                    $thank_you_page_url = get_permalink( (int) get_option( WWLC_OPTIONS_THANK_YOU_PAGE_ID ) );

                    update_option( 'wwlc_general_login_page' , $login_page_url );
                    update_option( 'wwlc_general_registration_page' , $registration_page_url );
                    update_option( 'wwlc_general_registration_thankyou' , $thank_you_page_url );

                }

            }

            // On activation, assign New Lead Role to Wholesale Customer role, if not present default to Customer
            // Get all user roles
            global $wp_roles;

            if( !isset( $wp_roles ) )
                $wp_roles = new WP_Roles();

            $all_user_roles = $wp_roles->get_names();

            // If 'wholesale_customer' exist in wp roles and 'wwlc_general_new_lead_role' is not yet set then we assign "New Lead Role" option value to default 'wholesale_customer' else we set 'customer'
            if( array_key_exists( 'wholesale_customer' , $all_user_roles ) && get_option( 'wwlc_general_new_lead_role' ) == false )
                update_option( 'wwlc_general_new_lead_role' , 'wholesale_customer' );
            else if( get_option( 'wwlc_general_new_lead_role' ) == false )
                update_option( 'wwlc_general_new_lead_role' , 'customer' );

            // on activation, add event in cron to delete all uploaded temporary files that haven't been assigned to a user.
            if ( ! wp_next_scheduled ( 'wwlc_delete_temp_files_daily' ) ) {
                wp_schedule_event( time() , 'daily' , 'wwlc_delete_temp_files_daily' );
            }

            // Address Placeholder Default
            if( get_option( 'wwlc_fields_address_placeholder' , '' ) == '' )
                update_option( 'wwlc_fields_address_placeholder' , __( 'Street address' , 'woocommerce-wholesale-lead-capture' ) );

            if( get_option( 'wwlc_fields_address2_placeholder' , '' ) == '' )
                update_option( 'wwlc_fields_address2_placeholder' , __( 'Apartment, suite, unit etc. (optional)' , 'woocommerce-wholesale-lead-capture' ) );
            
            if( get_option( 'wwlc_fields_city_placeholder' , '' ) == '' )
                update_option( 'wwlc_fields_city_placeholder' , __( 'Town / City' , 'woocommerce-wholesale-lead-capture' ) );
            
            if( get_option( 'wwlc_fields_state_placeholder' , '' ) == '' )
                update_option( 'wwlc_fields_state_placeholder' , __( 'State / County' , 'woocommerce-wholesale-lead-capture' ) );
            
            if( get_option( 'wwlc_fields_postcode_placeholder' , '' ) == '' )
                update_option( 'wwlc_fields_postcode_placeholder' , __( 'Postcode / Zip' , 'woocommerce-wholesale-lead-capture' ) );

            flush_rewrite_rules();

            update_option( WWLC_ACTIVATION_CODE_TRIGGERED , 'yes' );

            update_option( WWLC_OPTION_INSTALLED_VERSION , $this->_wwlc_current_version );

        }

        /**
         * Plugin deactivation hook callback.
         *
         * @param bool $network_wide
         *
         * @since 1.0.0
         */
        public function wwlc_deactivate( $network_wide ) {

            global $wpdb;

            // check if it is a multisite network
            if ( is_multisite() ) {

                // check if the plugin has been deactivated on the network or on a single site
                if ( $network_wide ) {

                    // get ids of all sites
                    $blogIDs = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

                    foreach ( $blogIDs as $blogID ) {

                        switch_to_blog( $blogID );
                        $this->wwlc_deactivate_action();

                    }

                    restore_current_blog();

                } else {

                    // deactivated on a single site, in a multi-site
                    $this->wwlc_deactivate_action();

                }

            } else {

                // deactivated on a single site
                $this->wwlc_deactivate_action();

            }

        }

        /**
         * Perform actions on plugin deactivation.
         *
         * @since 1.6.3
         */
        private function wwlc_deactivate_action(){

            // Remove inactive user role
            remove_role( WWLC_INACTIVE_ROLE );
            remove_role( WWLC_REJECTED_ROLE );
            remove_role( WWLC_UNMODERATED_ROLE );
            remove_role( WWLC_UNAPPROVED_ROLE );

            // clear scheduled cron event
            wp_clear_scheduled_hook( 'wwlc_delete_temp_files_daily' );

            flush_rewrite_rules();

        }

        /**
         * Plugin deactivation perform actions.
         *
         * @since 1.6.3
         */
        private function ecw_multisite_init(){

            if ( is_plugin_active_for_network( 'woocommerce-wholesale-lead-capture/woocommerce-wholesale-lead-capture.bootstrap.php' ) ) {

                switch_to_blog( $blogID );
                $this->wwlc_activate( $blogID );
                restore_current_blog();

            }

        }

        /**
         * Set temporary upload directory to for wp_handle_upload
         *
         * @param array $upload_dir_params
         *
         * @return array
         * @since 1.6.0
         */
        public function wwlc_set_temp_directory( $upload_dir_params ) {

            $temp_upload = get_option( 'wwlc_temp_upload_directory' );

            if ( empty( $temp_upload ) ) {

                $dir_name = uniqid( 'wwlc-temp-' );
                $temp_upload[ 'dir' ] = $upload_dir_params[ 'basedir' ] . '/' . $dir_name;
                $temp_upload[ 'url' ] = $upload_dir_params[ 'baseurl' ] . '/' . $dir_name;

                update_option( 'wwlc_temp_upload_directory', $temp_upload );
            }

            // In case the temp upload directory doesn't exist, create it
            if ( !file_exists( $temp_upload[ 'dir' ] ) )
                wp_mkdir_p( $temp_upload[ 'dir' ] );

            // Setup the params and pass back
            $upload_dir_params[ 'path' ] = $temp_upload[ 'dir' ];
            $upload_dir_params[ 'url' ] = $temp_upload[ 'url' ];

            return $upload_dir_params;

        }

        /**
         * Execute model.
         *
         * @since 1.6.3
         * @access public
         */
        public function run() {

            // Load Plugin Text Domain
            add_action( 'plugins_loaded' , array( $this , 'wwlc_load_plugin_text_domain' ) );

            // Register Activation Hook
            register_activation_hook( WWLC_PLUGIN_DIR . 'woocommerce-wholesale-lead-capture.bootstrap.php' , array( $this , 'wwlc_activate' ) );

            // Register Deactivation Hook
            register_deactivation_hook( WWLC_PLUGIN_DIR . 'woocommerce-wholesale-lead-capture.bootstrap.php' , array( $this , 'wwlc_deactivate' ) );

            // Plugin Initialization
            add_action( 'init' , array( $this , 'wwlc_initialize' ) );

            // Execute plugin initialization ( plugin activation ) on every newly created site in a multi site set up
            add_action( 'wpmu_new_blog', array( $this , 'ecw_multisite_init' ) , 10 , 6 );

        }
    }
}
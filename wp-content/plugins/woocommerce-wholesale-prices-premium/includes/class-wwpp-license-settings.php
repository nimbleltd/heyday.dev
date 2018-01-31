<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_License_Settings' ) ) {

    /**
     * Model that houses the logic of plugin license settings page.
     *
     * @since 1.14.0
     */
    class WWPP_License_Settings {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWPP_License_Settings.
         *
         * @since 1.14.0
         * @access private
         * @var WWPP_License_Settings
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




        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        /**
         * WWPP_License_Settings constructor.
         *
         * @since 1.14.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_License_Settings model.
         */
        public function __construct( $dependencies ) {

            $this->_wwpp_wholesale_roles = $dependencies[ 'WWPP_Wholesale_Roles' ];

        }

        /**
         * Ensure that only one instance of WWPP_License_Settings is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.14.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_License_Settings model.
         * @return WWPP_License_Settings
         */
        public static function instance( $dependencies ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }

        /**
         * Register general wws license settings page.
         *
         * @since 1.0.1
         * @since 1.14.0 Refactor codebase and move to its own model.
         * @access public
         */
        public function register_wws_license_settings_menu() {

            /*
            * Since we don't have a primary plugin to add this license settings, we have to check first if other plugins
            * belonging to the WWS plugin suite has already added a license settings page.
            */
            if ( !defined( 'WWS_LICENSE_SETTINGS_PAGE' ) ) {

                if ( !defined( 'WWS_LICENSE_SETTINGS_DEFAULT_PLUGIN' ) )
                    define( 'WWS_LICENSE_SETTINGS_DEFAULT_PLUGIN' , 'wwpp' );

                // Register WWS Settings Menu
                add_submenu_page(
                    'options-general.php', // Settings
                    __( 'WooCommerce Wholesale Suit License Settings' , 'woocommerce-wholesale-prices-premium' ),
                    __( 'WWS License' , 'woocommerce-wholesale-prices-premium' ),
                    apply_filters( 'wwpp_can_access_admin_menu_cap' , 'manage_options' ),
                    'wwc_license_settings',
                    array( $this , "wwc_general_license_settings_page" )
                );

                /*
                * We define this constant with the text domain of the plugin who added the settings page.
                */
                define( 'WWS_LICENSE_SETTINGS_PAGE' , 'woocommerce-wholesale-prices-premium' );

            }

        }

        /**
         * General WWS general license settings view.
         *
         * @since 1.0.2
         * @since 1.14.0 Refactor codebase and move to its own model.
         */
        public function wwc_general_license_settings_page() {

            require_once( WWPP_VIEWS_PATH . "wws-license-settings/wwpp-view-general-wws-settings-page.php" );

        }

        /**
         * WWPP WWS license settings header.
         *
         * @since 1.0.2
         * @since 1.14.0 Refactor codebase and move to its own model.
         */
        public function wwc_license_settings_header() {

            ob_start();

            if ( isset( $_GET[ 'tab' ] ) )
                $tab = $_GET[ 'tab' ];
            else
                $tab = WWS_LICENSE_SETTINGS_DEFAULT_PLUGIN;

            global $wp;
            $current_url = add_query_arg( $wp->query_string , '?' , home_url( $wp->request ) );
            $wwpp_license_settings_url = $current_url . "/wp-admin/options-general.php?page=wwc_license_settings&tab=wwpp"; ?>

            <a href="<?php echo $wwpp_license_settings_url; ?>" class="nav-tab <?php echo ( $tab == "wwpp" ) ? "nav-tab-active" : ""; ?>"><?php _e( 'Wholesale Prices' , 'woocommerce-wholesale-prices-premium' ); ?></a>

            <?php echo ob_get_clean();

        }

        /**
         * WWPP WWS license settings page content.
         *
         * @since 1.0.2
         * @since 1.14.0 Refactor codebase and move to its own model.
         */
        public function wwc_license_settings_page() {

            ob_start();

            require_once ( WWPP_VIEWS_PATH . "wws-license-settings/wwpp-view-wss-settings-page.php" );

            echo ob_get_clean();

        }

        /**
         * Check if in wwpp license settings page.
         *
         * @since 1.2.2
         * @since 1.14.0 Refactor codebase and move to its proper model.
         * @access public
         *
         * @return bool
         */
        public function check_if_in_wwpp_settings_page() {

            if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'wwc_license_settings' && isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'wwpp' )
                return true;
            else
                return false;

        }

        /**
         * Save wwpp license details.
         *
         * @since 1.0.1
         * @since 1.14.0 Refator codebase and move to its proper model.
         *
         * @param null|array $license_details License details.
         * @return bool Operation status.
         */
        public function save_license_details( $license_details = null ) {

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
                $license_details = $_POST[ 'licenseDetails' ];

            update_option( WWPP_OPTION_LICENSE_EMAIL , trim( $license_details[ 'license_email' ] ) );
            update_option( WWPP_OPTION_LICENSE_KEY , trim( $license_details[ 'license_key' ] ) );

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

                @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
                echo wp_json_encode( array( 'status' => 'success' ) );
                wp_die();

            } else
                return true;

        }




        /*
        |---------------------------------------------------------------------------------------------------------------
        | Execute model
        |---------------------------------------------------------------------------------------------------------------
        */
        
        /**
         * Register model ajax handlers.
         *
         * @since 1.14.0
         * @access public
         */
        public function register_ajax_handler() {

            add_action( "wp_ajax_wwppSaveLicenseDetails" , array( $this , 'save_license_details' ) );
            
        }

        /**
         * Execute model.
         *
         * @since 1.14.0
         * @access public
         */
        public function run() {

            add_action( "admin_menu"                       , array( $this , 'register_wws_license_settings_menu' ) );
            add_action( "wws_action_license_settings_tab"  , array( $this , 'wwc_license_settings_header' ) );
            add_action( "wws_action_license_settings_wwpp" , array( $this , 'wwc_license_settings_page' ) );

            add_action( 'init' , array( $this , 'register_ajax_handler' ) );

        }

    }

}
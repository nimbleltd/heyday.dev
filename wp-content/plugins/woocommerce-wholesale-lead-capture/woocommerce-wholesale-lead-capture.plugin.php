<?php
if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WooCommerce_Wholesale_Lead_Capture' ) ) {

	require_once ( WWLC_INCLUDES_ROOT_DIR . 'class-wwlc-bootstrap.php' );
	require_once ( WWLC_INCLUDES_ROOT_DIR . 'class-wwlc-scripts.php' );
    require_once ( WWLC_INCLUDES_ROOT_DIR . 'class-wwlc-ajax.php' );
	require_once ( WWLC_INCLUDES_ROOT_DIR . 'class-wwlc-forms.php' );
	require_once ( WWLC_INCLUDES_ROOT_DIR . 'class-wwlc-user-account.php' );
	require_once ( WWLC_INCLUDES_ROOT_DIR . 'class-wwlc-user-custom-fields.php' );
	require_once ( WWLC_INCLUDES_ROOT_DIR . 'class-wwlc-emails.php' );
    require_once ( WWLC_INCLUDES_ROOT_DIR . 'class-wwlc-wws-license-settings.php' );
    require_once ( WWLC_INCLUDES_ROOT_DIR . 'class-wwlc-registration-form-custom-fields.php' );
    require_once ( WWLC_INCLUDES_ROOT_DIR . 'class-wwlc-shortcode.php' );
    require_once ( WWLC_INCLUDES_ROOT_DIR . 'class-wwlc-cron.php' );

	class WooCommerce_Wholesale_Lead_Capture {

		/*
	     |--------------------------------------------------------------------------------------------------------------
	     | Class Members
	     |--------------------------------------------------------------------------------------------------------------
	     */

		private static $_instance;

		private $_wwlc_bootstrap;
		private $_wwlc_scripts;
		private $_wwlc_forms;
		private $_wwlc_user_account;
		private $_wwlc_user_custom_fields;
		private $_wwlc_emails;
        private $_wwlc_wws_license_setting;
        private $_wwlc_registration_form_custom_fields;
        private $_wwlc_shortcode;
        private $_wwlc_ajax;
        private $_wwlc_cron;

		const VERSION = '1.7.0';




		/*
	     |--------------------------------------------------------------------------------------------------------------
	     | Mesc Functions
	     |--------------------------------------------------------------------------------------------------------------
	     */

		/**
		 * Class constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

            $this->_wwlc_forms = WWLC_Forms::instance();
			$this->_wwlc_scripts = WWLC_Scripts::instance( array(
																	'WWLC_Forms' => $this->_wwlc_forms,
																	'WWLC_Version' => self::VERSION
															 	) );
            $this->_wwlc_user_account = WWLC_User_Account::instance();
            $this->_wwlc_emails = WWLC_Emails::instance();
            $this->_wwlc_wws_license_setting = WWLC_WWS_License_Settings::instance();
            $this->_wwlc_registration_form_custom_fields = WWLC_Registration_Form_Custom_Fields::instance();
			$this->_wwlc_bootstrap = WWLC_Bootstrap::instance( array(
                                                                        'WWLC_Forms' => $this->_wwlc_forms,
                                                                        'WWLC_CURRENT_VERSION' => self::VERSION
                                                                    ) );
			$this->_wwlc_user_custom_fields = WWLC_User_Custom_Fields::instance( array(
            														'WWLC_User_Account' => $this->_wwlc_user_account,
            														'WWLC_Emails' => $this->_wwlc_emails
        													) );
            $this->_wwlc_shortcode = WWLC_Shortcode::instance( array(
            														'WWLC_Forms' => $this->_wwlc_forms
        													) );
            $this->_wwlc_ajax = WWLC_AJAX::instance( array(
                                                            'WWLC_Bootstrap' => $this->_wwlc_bootstrap,
            												'WWLC_User_Account' => $this->_wwlc_user_account,
            												'WWLC_Emails' => $this->_wwlc_emails,
            												'WWLC_Forms' => $this->_wwlc_forms,
            												'WWLC_WWS_License_Settings' => $this->_wwlc_wws_license_setting,
            												'WWLC_Registration_Form_Custom_Fields' => $this->_wwlc_registration_form_custom_fields
        											) );
            $this->_wwlc_cron = WWLC_Cron::instance();

		}

		/**
		 * Singleton Pattern.
		 *
		 * @return WooCommerce_Wholesale_Lead_Capture
		 * @since 1.0.0
		 */
		public static function instance() {

			if ( !self::$_instance instanceof self )
				self::$_instance = new self;

			return self::$_instance;

		}

        /*
        |---------------------------------------------------------------------------------------------------------------
        | WooCommerce WholeSale Suit License Settings
        |---------------------------------------------------------------------------------------------------------------
        */

        /**
         * Register general wws license settings page.
         *
         * @since 1.0.1
         */
        public function registerWWSLicenseSettingsMenu() {

            /*
             * Since we don't have a primary plugin to add this license settings, we have to check first if other plugins
             * belonging to the WWS plugin suite has already added a license settings page.
             */
            if ( !defined( 'WWS_LICENSE_SETTINGS_PAGE' ) ) {

                if ( !defined( 'WWS_LICENSE_SETTINGS_DEFAULT_PLUGIN' ) )
                    define( 'WWS_LICENSE_SETTINGS_DEFAULT_PLUGIN' , 'wwlc' );

                // Register WWS Settings Menu
                add_submenu_page(
                    'options-general.php', // Settings
                    __( 'WooCommerce WholeSale Suit License Settings' , 'woocommerce-wholesale-lead-capture' ),
                    __( 'WWS License' , 'woocommerce-wholesale-lead-capture' ),
                    'manage_options',
                    'wwc_license_settings',
                    array( self::instance() , "wwcGeneralLicenseSettingsPage" )
                );

                /*
                 * We define this constant with the text domain of the plugin who added the settings page.
                 */
                define( 'WWS_LICENSE_SETTINGS_PAGE' , 'woocommerce-wholesale-lead-capture' );

            }

        }

        public function wwcGeneralLicenseSettingsPage() {

            require_once( 'views/wws-license-settings/view-wwlc-general-wws-settings-page.php' );

        }

        public function wwcLicenseSettingsHeader() {

            ob_start();

            if ( isset( $_GET[ 'tab' ] ) )
                $tab = $_GET[ 'tab' ];
            else
                $tab = WWS_LICENSE_SETTINGS_DEFAULT_PLUGIN;

            global $wp;
            $current_url = add_query_arg( $wp->query_string , '?' , home_url( $wp->request ) );
            $wwlc_license_settings_url = $current_url . "/wp-admin/options-general.php?page=wwc_license_settings&tab=wwlc"; ?>

			<a href="<?php echo $wwlc_license_settings_url; ?>" class="nav-tab <?php echo ( $tab == "wwlc" ) ? "nav-tab-active" : ""; ?>"><?php _e( 'Wholesale Lead' , 'woocommerce-wholesale-lead-capture' ); ?></a>

			<?php echo ob_get_clean();

        }

        public function wwcLicenseSettingsPage() {

            ob_start();

            require_once( "views/wws-license-settings/view-wwlc-wss-settings-page.php" );

            echo ob_get_clean();

        }

		/*
	    |---------------------------------------------------------------------------------------------------------------
	    | Settings
	    |---------------------------------------------------------------------------------------------------------------
	    */

		/**
		 * Initialize plugin settings.
		 *
		 * @since 1.0.0
		 */
		public function initializePluginSettings() {

			$settings[] = include( WWLC_INCLUDES_ROOT_DIR . "class-wwlc-settings.php" );

			return $settings;

		}

        /**
         * Add plugin listing custom action link ( settings ).
         *
         * @param $links
         * @param $file
         * @return mixed
         *
         * @since 1.0.2
         */
        public function addPluginListingCustomActionLinks( $links , $file ) {

            if ( $file == plugin_basename( WWLC_PLUGIN_DIR . 'woocommerce-wholesale-lead-capture.bootstrap.php' ) ) {

                $settings_link = '<a href="admin.php?page=wc-settings&tab=wwlc_settings">' . __( 'Settings' , 'woocommerce-wholesale-lead-capture' ) . '</a>';
                $license_link = '<a href="options-general.php?page=wwc_license_settings&tab=wwlc">' . __( 'License' , 'woocommerce-wholesale-lead-capture' ) . '</a>';
                array_unshift( $links , $license_link );
                array_unshift( $links , $settings_link );

            }

            return $links;

        }

        /**
         * Check if in wwlc license settings page.
         *
         * @return bool
         *
         * @since 1.1.1
         */
        public function checkIfInWWLCSettingsPage() {

            if ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'wwc_license_settings' && isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'wwlc' )
                return true;
            else
                return false;

        }

	    /*
	    |-------------------------------------------------------------------------------------------------------------------
	    | Execution WWLC
	    |
	    | This will be the new way of executing the plugin.
	    |-------------------------------------------------------------------------------------------------------------------
	    */

	    /**
	     * Execute WWLC. Triggers the execution codes of the plugin models.
	     *
	     * @since 1.6.3
	     * @access public
	     */
	    public function run() {

	    	$this->_wwlc_bootstrap->run();
	    	$this->_wwlc_scripts->run();
	        $this->_wwlc_user_account->run();
	        $this->_wwlc_ajax->run();
	        $this->_wwlc_shortcode->run();
	        $this->_wwlc_user_custom_fields->run();
			$this->_wwlc_cron->run();
	        $this->_wwlc_forms->run();

	    }
	}
}

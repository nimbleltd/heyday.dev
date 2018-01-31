<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWLC_Shortcode' ) ) {

	class WWLC_Shortcode {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

		/**
         * Property that holds the single main instance of WWLC_Shortcode.
         *
         * @since 1.6.3
         * @access private
         * @var WWLC_Shortcode
         */
		private static $_instance;

		/**
         * Get instance of WWLC_Forms class
         *
         * @since 1.6.3
         * @access private
         * @var WWLC_Forms
         */
        private $_wwlc_forms;

		/**
		 * Flag that tells if the registration form shortcode is already loaded or not on a page.
		 * This is to make sure the registration form is only loaded once in a single page.
		 *
		 * @since 1.7.0
		 * @access private
		 * @var boolean
		 */
		private $_wwlc_registration_form_loaded = false;

		/**
		 * Flag that tells if the login form shortcode is already loaded or not on a page.
		 * This is to make sure the login form is only loaded once in a single page.
		 *
		 * @since 1.7.0
		 * @access private
		 * @var boolean
		 */
		private $_wwlc_login_form_loaded = false;

		/*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        /**
         * WWLC_Shortcode constructor.
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWLC_Shortcode model.
         *
         * @access public
         * @since 1.6.3
         */
		public function __construct( $dependencies ) {

			$this->_wwlc_forms = $dependencies[ 'WWLC_Forms' ];

		}

        /**
         * Ensure that only one instance of WWLC_Shortcode is loaded or can be loaded (Singleton Pattern).
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWLC_Shortcode model.
         *
         * @return WWLC_Shortcode
         * @since 1.6.3
         */
        public static function instance( $dependencies = null  ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }

		/**
		 * Render log in form.
		 *
		 * @return string
		 * @since 1.0.0
		 * @since 1.6.3 WWLC-49 : Stopped using wp_login_form, setup a custom form for WWLC login instead.
		 * 				Reason is we can't set form action using wp_login_form which is used to redirect the user when login button is clicked.
		 * 				This function is transferred from WWLC_Forms to this class.
		 */
		public function wwlc_login_form( $atts ) {

			global $wp;

			ob_start();

			if ( is_user_logged_in() ) {

				$this->_wwlc_forms->_load_template(
					'wwlc-logout-page.php',
					array(),
					WWLC_TEMPLATES_ROOT_DIR
				);

			} else {

				// WWLC-195 : due to WooCommerce running shortcodes twice on product page content when short description is not defined
				//            we need to disable the flag condition ($this->_wwlc_login_form_loaded) for product pages by adding ! is_product() check.
				if ( $this->_wwlc_login_form_loaded && ! is_product() )
					return;

				$atts = shortcode_atts( array(
					'redirect' => ''
				) , $atts , 'wwlc_login_form' );

				$reditect_page_option = get_option( 'wwlc_general_login_redirect_page' );
				$redirect_page        = !empty( $reditect_page_option ) ? $reditect_page_option : get_permalink( wc_get_page_id( 'shop' ) );

				if ( $atts[ 'redirect' ] && filter_var( $atts[ 'redirect' ] , FILTER_VALIDATE_URL ) )
					$redirect_page = $atts[ 'redirect' ];
				elseif ( $atts[ 'redirect' ] == 'current_page' )
					$redirect_page = home_url( add_query_arg( array() , $wp->request ) );

				$action_url = $redirect_page ? home_url( add_query_arg( array() , $wp->request ) ) : get_option( 'wwlc_general_login_page' );

				$login_form_args = array(
									'form_id'        	=> 'wwlc_loginform',
									'form_method' 		=> 'post',
									'form_action'		=> $action_url,
									'redirect'       	=> $redirect_page,
									'label_username'	=> apply_filters( 'wwlc_filter_login_field_label_username' , __( 'Username', 'woocommerce-wholesale-lead-capture' ) ),
									'label_password' 	=> apply_filters( 'wwlc_filter_login_field_label_password' , __( 'Password', 'woocommerce-wholesale-lead-capture' ) ),
									'label_remember' 	=> apply_filters( 'wwlc_filter_login_field_label_remember_me' , __( 'Remember Me', 'woocommerce-wholesale-lead-capture' ) ),
									'label_log_in'   	=> apply_filters( 'wwlc_filter_login_field_label_login' , __( 'Log In', 'woocommerce-wholesale-lead-capture' ) ),
									'id_username'    	=> 'user_login',
									'id_password'    	=> 'user_pass',
									'id_remember'    	=> 'rememberme',
									'id_submit'      	=> 'wp-submit',
									'remember'       	=> true,
									'value_username' 	=> isset( $_POST[ 'wwlc_username' ] ) ? $_POST[ 'wwlc_username' ] : NULL,
									'value_remember' 	=> isset( $_POST[ 'rememberme' ] ) ? $_POST[ 'rememberme' ] : false
								);

				$this->_wwlc_forms->_load_template(
					'wwlc-login-form.php',
					array(
						'args'          => apply_filters( 'wwlc_login_form_args', $login_form_args ),
						'formProcessor' => $this->_wwlc_forms
					),
					WWLC_TEMPLATES_ROOT_DIR
				);

				$this->_wwlc_login_form_loaded = true;

			}

			return ob_get_clean();

		}

		/**
		 * Render registration form.
		 *
		 * @return string
		 * @since 1.0.0
		 * @since 1.6.3 This function is transferred from WWLC_Forms to this class.
		 * @since 1.7.0 Add shortcode attribute for 'redirect'. Setting value to 'current_page' will set form to stay on page.
		 *				Added code to make sure shortcode is loaded on a single page only once.
		 *				Added feature to define user role to be used via the 'role' shortcode attribute.
		 */
		public function wwlc_registration_form( $atts ) {

			// if shortcode is already loaded in the page or if user is logged in, then don't display anymore.
			// WWLC-195 : due to WooCommerce running shortcodes twice on product page content when short description is not defined
			//            we need to disable the flag condition ($this->_wwlc_registration_form_loaded) for product pages by adding ! is_product() check.
			if ( ( $this->_wwlc_registration_form_loaded && ! is_product() ) || is_user_logged_in() )
				return;

			$atts = shortcode_atts( array(
				'redirect' => '',
				'role'     => '',
			) , $atts , 'wwlc_registration_form' );

			// enqueue select2 script when address fields are enabled.
			if ( get_option( 'wwlc_fields_activate_address_field' ) == 'yes' )
				wp_enqueue_script( 'select2' );

			// enqueue registration form JS script.
			wp_enqueue_script( 'wwlc_RegistrationForm_js' );

			// enqueue password meter script if password field is enabled.
			if ( get_option( 'wwlc_fields_activate_password_field' ) == 'yes' )
				wp_enqueue_script( 'wwlc_password_meter_js' );

			// enqueue recaptcha script if recaptcha field is enabled.
			if ( get_option( 'wwlc_security_enable_recaptcha' ) == 'yes' )
				wp_enqueue_script( 'wwlc_recaptcha_api_js' );

			ob_start();

			global $WWLC_REGISTRATION_FIELDS;

            $custom_fields            = $this->_wwlc_forms->_get_formatted_custom_fields();
			$recaptcha_field          = $this->_wwlc_forms->_get_recaptcha_field();
            $registration_form_fields = array_merge( $WWLC_REGISTRATION_FIELDS, $custom_fields, $recaptcha_field );

            usort( $registration_form_fields, array( $this->_wwlc_forms, '_usort_callback' ) );

			// Load product listing template
			$this->_wwlc_forms->_load_template(
				'wwlc-registration-form.php',
				array(
					'formProcessor' =>  $this->_wwlc_forms,
					'formFields'    =>  apply_filters( 'wwlc_registration_form_fields', $registration_form_fields ),
					'redirect'      =>  ( $atts[ 'redirect' ] && filter_var( $atts[ 'redirect' ] , FILTER_VALIDATE_URL ) ) ? $atts[ 'redirect' ] : '',
					'role'          => WWLC_User_Account::sanitize_custom_role( $atts[ 'role' ] )
				),
				WWLC_TEMPLATES_ROOT_DIR
			);

			$this->_wwlc_registration_form_loaded = true;

			return ob_get_clean();

		}

	    /**
	     * Execute model.
	     *
	     * @since 1.6.3
	     * @access public
	     */
	    public function run() {

			// Registration Form
			add_shortcode( 'wwlc_registration_form' , array( $this , 'wwlc_registration_form' ) );

			// Login Form
			add_shortcode( 'wwlc_login_form' , array( $this , 'wwlc_login_form' ) );

	    }
	}
}

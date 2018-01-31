<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWLC_Emails' ) ) {

	class WWLC_Emails {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

		/**
         * Property that holds the single main instance of WWLC_Emails.
         *
         * @since 1.6.3
         * @access private
         * @var WWLC_Emails
         */
		private static $_instance;

		/*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        /**
         * WWLC_Emails constructor.
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWLC_Emails model.
         *
         * @access public
         * @since 1.6.3
         */
		public function __construct( $dependencies ) {}

        /**
         * Ensure that only one instance of WWLC_Emails is loaded or can be loaded (Singleton Pattern).
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWLC_Emails model.
         *
         * @return WWLC_Emails
         * @since 1.6.3
         */
        public static function instance( $dependencies = null ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }

		/**
		 * Get password reset url.
		 *
		 * @param $user_login
		 *
		 * @return bool
		 * @since 1.0.0
		 */
		private function _get_reset_password_url( $user_login ) {

			global $wpdb, $wp_hasher;

			$user_login = sanitize_text_field( $user_login );

			if ( empty( $user_login ) ) {

				return false;

			} elseif ( strpos( $user_login, '@' ) ) {

				$user_data = get_user_by( 'email' , trim( $user_login ) );
				if ( empty( $user_data ) )
					return false;

			} else {

				$login = trim( $user_login );
				$user_data = get_user_by( 'login' , $login );

			}

			do_action( 'lostpassword_post' );


			if ( !$user_data ) return false;

			// redefining user_login ensures we return the right case in the email
			$user_login = $user_data->user_login;
			$user_email = $user_data->user_email;

			do_action( 'retrieve_password' , $user_login );

			$allow = apply_filters( 'allow_password_reset' , true , $user_data->ID );

			if ( !$allow )
				return false;
			elseif ( is_wp_error( $allow ) )
				return false;

			$key = wp_generate_password( 20 , false );
			do_action( 'retrieve_password_key' , $user_login, $key );

			if ( empty( $wp_hasher ) ) {
				require_once ABSPATH . 'wp-includes/class-phpass.php';
				$wp_hasher = new PasswordHash( 8 , true );
			}

			$hashed = $wp_hasher->HashPassword( $key );
			$wpdb->update( $wpdb->users , array( 'user_activation_key' => $hashed ), array( 'user_login' => $user_login ) );

			return network_site_url( 'wp-login.php?action=rp&key=$key&login=' . rawurlencode( $user_login ) , 'login' );

		}

		/**
		 * Parse email contents, replace email template tags with appropriate values.
		 *
		 * @param      $userID
		 * @param      $content
		 * @param null $password
		 *
		 * @return mixed
		 * @since 1.0.0
		 */
		private function _parse_email_content( $userID , $content , $password = null ) {

			$new_user = get_userdata( $userID );
			$custom_fields = unserialize( base64_decode( get_option( WWLC_OPTION_REGISTRATION_FORM_CUSTOM_FIELDS ) ) );

			// "user_wholesale_role" template tag is used in "Approval Email Template"
			$find_replace[ 'user_wholesale_role' ] = '';
			if( class_exists( 'WWP_Wholesale_Roles' ) ) {

				$user_wholesale_role = array();
				$wwp_wholesale_role = WWP_Wholesale_Roles::getInstance();
				$wholesale_roles = $wwp_wholesale_role->getAllRegisteredWholesaleRoles();

				// Check wholesale role name
				foreach ( $new_user->roles as $role ) {
					if( isset( $wholesale_roles[ $role ] ) )
						$user_wholesale_role[] = $wholesale_roles[ $role ][ 'roleName' ];
				}

				if ( !empty( $user_wholesale_role ) )
					$find_replace[ 'user_wholesale_role' ] = $user_wholesale_role;

			}

			$find_replace[ 'wholesale_login_url' ] = get_option( 'wwlc_general_login_page' );
			$find_replace[ 'reset_password_url' ] = $this->_get_reset_password_url( $new_user->data->user_login );
			$find_replace[ 'site_name' ] = get_bloginfo( 'name' );
			$find_replace[ 'full_name' ] = $new_user->first_name . ' ' . $new_user->last_name ;
			$find_replace[ 'user_management_url' ] = get_admin_url( null , 'users.php' );
			$find_replace[ 'user_edit_profile_url' ] = admin_url( 'user-edit.php?user_id=' . $new_user->ID );

			$capability = maybe_unserialize( get_user_meta( $userID , 'wp_capabilities' , true) );
			// If {password} tag is used in "New User Email Template"
			if ( isset( $capability ) && ( isset( $capability[ 'wwlc_unapproved' ] ) && $capability[ 'wwlc_unapproved' ] == true ) && ( isset( $capability[ 'wwlc_unmoderated' ] ) && $capability[ 'wwlc_unmoderated' ] == true ) )
				$find_replace[ 'password' ] = 'Password will be generated and sent on approval.';
			else
				$find_replace[ 'password' ] = $password;

			$find_replace[ 'email' ] 		= $new_user->user_email;
			$find_replace[ 'first_name' ] 	= $new_user->first_name;
			$find_replace[ 'last_name' ] 	= $new_user->last_name;
			$find_replace[ 'username' ] 	= $new_user->user_login;
			$find_replace[ 'phone' ] 		= $new_user->wwlc_phone;
			$find_replace[ 'company_name' ] = $new_user->wwlc_company_name;

			// For backwards compatibility
			$find_replace[ 'address' ] = $new_user->wwlc_address;
			if ( $new_user->wwlc_address_2 ) $find_replace[ 'address' ] .= "<br/>" . $new_user->wwlc_address_2;
			if ( $new_user->wwlc_city ) 		$find_replace[ 'address' ] .= "<br/>" . $new_user->wwlc_city;
			if ( $new_user->wwlc_state ) 	$find_replace[ 'address' ] .= "<br/>" . $new_user->wwlc_state;
			if ( $new_user->wwlc_postcode ) 	$find_replace[ 'address' ] .= "<br/>" . $new_user->wwlc_postcode;
			if ( $new_user->wwlc_country ) 	$find_replace[ 'address' ] .= "<br/>" . $new_user->wwlc_country;

			// Specific address elements
			$find_replace[ 'address_1' ] 	= $new_user->wwlc_address;
			$find_replace[ 'address_2' ] 	= $new_user->wwlc_address_2;
			$find_replace[ 'city' ] 		= $new_user->wwlc_city;
			$find_replace[ 'state' ]    	= $new_user->wwlc_state;
			$find_replace[ 'postcode' ] 	= $new_user->wwlc_postcode;
			$find_replace[ 'country' ]  	= $new_user->wwlc_country;

			if ( is_array( $custom_fields ) && !empty( $custom_fields ) ){
				foreach ( $custom_fields as $field_id => $field ) {
					$find_replace[ 'custom_field:' . $field_id ] = $new_user->$field_id;
				}
			}

			foreach ( $find_replace as $find => $replace ) {

				if ( is_array( $replace ) ) {

					$replace_str = implode( ', ' , $replace );
					$content = str_replace( '{' . $find . '}' , $replace_str , $content );

				} else
					$content = str_replace( '{' . $find . '}' , $replace , $content );

			}

			return $content;

		}

		/*
		 |--------------------------------------------------------------------------------------------------------------
		 | Admin Emails
		 |--------------------------------------------------------------------------------------------------------------
		 */

		/**
		 * Email sent to admin on new user registration.
		 *
		 * @param $userID
		 * @param $subject
		 * @param $message
		 * @param $password
		 *
		 * @since 1.0.0
		 * @since 1.6.12 Added 'wwlc_send_new_user_admin_notice_email_headers_filter' filter to headers
		 */
		public function wwlc_send_new_user_admin_notice_email ( $userID , $subject , $message , $password ) {

			// check if notification is set to disabled
			if ( apply_filters( 'wwlc_disable_new_user_admin_notice_email', false ) )
				return;

			$wc_emails = WC_Emails::instance();

			$to = $this->_get_admin_email_recipients();
			$to = apply_filters( 'wwlc_filter_new_user_admin_notice_email_recipients' , $to );

			$cc = $this->_get_admin_email_cc();
			$cc = apply_filters( 'wwlc_filter_new_user_admin_notice_email_cc' , $cc );

			$bcc = $this->_get_admin_email_bcc();
			$bcc = apply_filters( 'wwlc_filter_new_user_admin_notice_email_bcc' , $bcc );

			$from_name = $this->_get_from_name();
			$from_email = $this->_get_from_email();

			if ( !$subject )
				$subject = __( 'New User Registration' , 'woocommerce-wholesale-lead-capture' );
			else
				$subject = $this->_parse_email_content( $userID , $subject , $password );

			$subject = apply_filters( 'wwlc_filter_new_user_admin_email_subject' , $subject );

			if ( !$message ) {

				global $newUserAdminNotificationEmailDefault;
				$message = $newUserAdminNotificationEmailDefault;

			}

			$message = $this->_parse_email_content( $userID , $message , $password );

			$wrap_email_with_wc_header_and_footer = trim( get_option( "wwlc_email_wrap_wc_header_footer" ) );
			if( $wrap_email_with_wc_header_and_footer == "yes" )
				$message = $wc_emails->wrap_message( $subject, $message );

			$message = apply_filters( 'wwlc_filter_new_user_admin_email_content' , html_entity_decode( $message ) , $userID , $password );

			$headers = $this->_construct_email_header( $from_name , $from_email , $cc , $bcc );
			$headers = apply_filters( 'wwlc_send_new_user_admin_notice_email_headers_filter' , $headers );

			// email attachments can be enabled via add_filter only for now
			$attachments = apply_filters( 'wwlc_enable_new_user_admin_notice_email_attachments', false ) ? $this->_get_custom_field_email_attachments( $userID ) : '';

			$wc_emails->send( $to , $subject , $message , $headers, $attachments );

		}

		/**
		 * Email sent to admin on new user registration that is auto approved.
		 *
		 * @param $userID
		 * @param $subject
		 * @param $message
		 * @param $password
		 *
		 * @since 1.0.0
		 * @since 1.6.12 Added 'wwlc_send_new_user_admin_notice_email_auto_approved_headers_filter' filter to headers
		 */
		public function wwlc_send_new_user_admin_notice_email_auto_approved ( $userID , $subject , $message , $password ) {

			// check if notification is set to disabled
			if ( apply_filters( 'wwlc_disable_new_user_auto_approved_notice_email', false ) )
				return;

			$wc_emails = WC_Emails::instance();

			$to = $this->_get_admin_email_recipients();
			$to = apply_filters( 'wwlc_filter_new_user_auto_approved_admin_notice_email_recipients' , $to );

			$cc = $this->_get_admin_email_cc();
			$cc = apply_filters( 'wwlc_filter_new_user_auto_approved_admin_notice_email_cc' , $cc );

			$bcc = $this->_get_admin_email_bcc();
			$bcc = apply_filters( 'wwlc_filter_new_user_auto_approved_admin_notice_email_bcc' , $bcc );

			$from_name = $this->_get_from_name();
			$from_email = $this->_get_from_email();

			$headers = $this->_construct_email_header( $from_name , $from_email , $cc , $bcc );
			$headers = apply_filters( 'wwlc_send_new_user_admin_notice_email_auto_approved_headers_filter' , $headers );

			if ( !$subject )
				$subject = __( 'New User Registered And Approved' , 'woocommerce-wholesale-lead-capture' );
			else
				$subject = $this->_parse_email_content( $userID , $subject , $password );

			$subject = apply_filters( 'wwlc_filter_new_user_auto_approved_admin_notice_email_subject' , $subject );

			if ( !$message ) {

				global $newUserAdminNotificationEmailAutoApprovedDefault;
				$message = $newUserAdminNotificationEmailAutoApprovedDefault;

			}

			$message = $this->_parse_email_content( $userID , $message , $password );

			$wrap_email_with_wc_header_and_footer = trim( get_option( "wwlc_email_wrap_wc_header_footer" ) );
			if( $wrap_email_with_wc_header_and_footer == "yes" )
				$message = $wc_emails->wrap_message( $subject, $message );

			$message = apply_filters( 'wwlc_filter_new_user_auto_approved_admin_notice_email_content' , html_entity_decode( $message ) , $userID , $password );

			// email attachments can be enabled via add_filter only for now
			$attachments = apply_filters( 'wwlc_enable_new_user_admin_notice_email_attachments', false ) ? $this->_get_custom_field_email_attachments( $userID ) : '';

			$wc_emails->send( $to , $subject , $message , $headers, $attachments );

		}

		/*
		 |--------------------------------------------------------------------------------------------------------------
		 | User Emails
		 |--------------------------------------------------------------------------------------------------------------
		 */

		/**
		 * Email sent to user on successful registration.
		 *
		 * @param $userID
		 * @param $subject
		 * @param $message
		 * @param $password
		 *
		 * @since 1.0.0
		 * @since 1.6.2 WWLC-130, Bug fix: New User Email Template didn't send after successful registration
		 * @since 1.6.12 Added 'wwlc_send_new_user_email_headers_filter' filter to headers
		 */
		public function wwlc_send_new_user_email ( $userID , $subject , $message , $password ) {

			$auto_approve_new_leads = get_option( 'wwlc_general_auto_approve_new_leads' , 'no' );
			$disable_new_user_email = get_option( 'wwlc_emails_new_user_disable_for_auto_approve' , 'no' );

			// Check if notification is set to disabled
			if ( apply_filters( 'wwlc_disable_new_user_notice_email' , false ) ||
				( $auto_approve_new_leads == 'yes' && $disable_new_user_email == 'yes' ) )
					return;

			$wc_emails = WC_Emails::instance();

			$new_user = get_userdata( $userID );
			$to = $new_user->data->user_email;

			$from_name = $this->_get_from_name();
			$from_email = $this->_get_from_email();

			$headers = $this->_construct_email_header( $from_name , $from_email );
			$headers = apply_filters( 'wwlc_send_new_user_email_headers_filter' , $headers );

			if ( !$subject )
				$subject = __( 'Registration Successful' , 'woocommerce-wholesale-lead-capture' );
			else
				$subject = $this->_parse_email_content( $userID , $subject , $password );

			$subject = apply_filters( 'wwlc_filter_new_user_user_notice_email_subject' , $subject );

			if ( !$message ) {

				global $newUserEmailDefault;
				$message = $newUserEmailDefault;

			}

			$message = $this->_parse_email_content( $userID , $message , $password );

			$wrap_email_with_wc_header_and_footer = trim( get_option( 'wwlc_email_wrap_wc_header_footer' ) );
			if( $wrap_email_with_wc_header_and_footer == 'yes' )
				$message = $wc_emails->wrap_message( $subject, $message );

			$message = apply_filters( 'wwlc_filter_new_user_user_notice_email_content' , html_entity_decode( $message ) , $userID , $password );

			$wc_emails->send( $to , $subject , $message , $headers );

		}

		/**
		 * Email sent to user on account approval.
		 *
		 * @param $userID
		 * @param $subject
		 * @param $message
		 * @param $password
		 *
		 * @since 1.0.0
		 * @since 1.6.12 Added 'wwlc_send_registration_approval_email_headers_filter' filter to headers
		 */
		public function wwlc_send_registration_approval_email ( $userID , $subject , $message , $password ) {

			// check if notification is set to disabled
			if ( apply_filters( 'wwlc_disable_registration_approved_user_notice_email' , false ) )
				return;

			$wc_emails = WC_Emails::instance();

			$new_user = get_userdata( $userID );
			$to = $new_user->data->user_email;

			$from_name = $this->_get_from_name();
			$from_email = $this->_get_from_email();

			$headers = $this->_construct_email_header( $from_name , $from_email );
			$headers = apply_filters( 'wwlc_send_registration_approval_email_headers_filter' , $headers );

			if ( !$subject )
				$subject = __( 'Registration Approved' , 'woocommerce-wholesale-lead-capture' );
			else
				$subject = $this->_parse_email_content( $userID , $subject , $password );

			$subject = apply_filters( 'wwlc_filter_registration_approved_user_notice_email_subject' , $subject );

			if ( !$message ) {

				global $approvedEmailDefault;
				$message = $approvedEmailDefault;

			}

			$message = $this->_parse_email_content( $userID , $message , $password );

			$wrap_email_with_wc_header_and_footer = trim( get_option( "wwlc_email_wrap_wc_header_footer" ) );
			if( $wrap_email_with_wc_header_and_footer == "yes" )
				$message = $wc_emails->wrap_message( $subject, $message );

			$message = apply_filters( 'wwlc_filter_registration_approved_user_notice_email_content' , html_entity_decode( $message ) , $userID , $password );

			$wc_emails->send( $to , $subject , $message , $headers );

		}

		/**
		 * Email sent to user on account rejection.
		 *
		 * @param $userID
		 * @param $subject
		 * @param $message
		 *
		 * @since 1.0.0
		 * @since 1.6.12 Added 'wwlc_send_registration_rejection_email_headers_filter' filter to headers
		 */
		public function wwlc_send_registration_rejection_email ( $userID , $subject , $message ) {

			// check if notification is set to disabled
			if ( apply_filters( 'wwlc_disable_registration_rejected_user_notice_email', false ) )
				return;

			$wc_emails = WC_Emails::instance();

			$new_user = get_userdata( $userID );
			$to = $new_user->data->user_email;

			$from_name = $this->_get_from_name();
			$from_email = $this->_get_from_email();

			$headers = $this->_construct_email_header( $from_name , $from_email );
			$headers = apply_filters( 'wwlc_send_registration_rejection_email_headers_filter' , $headers );

			if ( !$subject )
				$subject = __( 'Registration Rejected' , 'woocommerce-wholesale-lead-capture' );
			else
				$subject = $this->_parse_email_content( $userID , $subject );

			$subject = apply_filters( 'wwlc_filter_registration_rejected_user_notice_email_subject' , $subject );

			if ( !$message ) {

				global $rejectedEmailDefault;
				$message = $rejectedEmailDefault;

			}

			$message = $this->_parse_email_content( $userID , $message );

			$wrap_email_with_wc_header_and_footer = trim( get_option( 'wwlc_email_wrap_wc_header_footer' ) );
			if( $wrap_email_with_wc_header_and_footer == 'yes' )
				$message = $wc_emails->wrap_message( $subject, $message );

			$message = apply_filters( 'wwlc_filter_registration_rejected_user_notice_email_content' , html_entity_decode( $message ) , $userID );

			$wc_emails->send( $to , $subject , $message , $headers );

		}

		/*
		 |--------------------------------------------------------------------------------------------------------------
		 | Helper Functions
		 |--------------------------------------------------------------------------------------------------------------
		 */

		/**
		 * Get admin email recipients.
		 *
		 * @return array|string
		 *
		 * @since 1.3.0
		 */
		private function _get_admin_email_recipients () {

			$to = trim( get_option( 'wwlc_emails_main_recipient' ) );

			if ( $to )
				$to = explode( ',' , $to );
			else
				$to = array( get_option( 'admin_email' ) );

			return $to;

		}

		/**
		 * Get admin email cc.
		 *
		 * @return array|string
		 *
		 * @since 1.3.0
		 */
		private function _get_admin_email_cc () {

			$cc = trim( get_option( 'wwlc_emails_cc' ) );

			if ( $cc )
				$cc = explode( ',' , $cc );

			if ( !is_array( $cc ) )
				$cc = array();

			return $cc;

		}

		/**
		 * Get admin email bcc.
		 *
		 * @return array|string
		 *
		 * @since 1.3.0
		 */
		private function _get_admin_email_bcc () {

			$bcc = trim( get_option( 'wwlc_emails_bcc' ) );

			if ( $bcc )
				$bcc = explode( ',' , $bcc );

			if ( !is_array( $bcc ) )
				$bcc = array();

			return $bcc;

		}

		/**
		 * Get email from name.
		 *
		 * @return mixed
		 *
		 * @since 1.3.0
		 */
		private function _get_from_name () {

			$from_name = trim( get_option( "woocommerce_email_from_name" ) );

			if ( !$from_name )
				$from_name = get_bloginfo( 'name' );

			return apply_filters( 'wwlc_filter_from_name' , $from_name );

		}

		/**
		 * Get from email.
		 *
		 * @return mixed
		 *
		 * @since 1.3.0
		 */
		private function _get_from_email () {

			$from_email = trim( get_option( 'woocommerce_email_from_address' ) );

			if ( !$from_email )
				$from_email = get_option( 'admin_email' );

			return apply_filters( 'wwlc_filter_from_email' , $from_email );

		}

		/**
		 * Construct email headers.
		 *
		 * @param $from_name
		 * @param $from_email
		 * @param array $cc
		 * @param array $bcc
		 * @return array
		 *
		 * @since 1.3.0
		 */
		private function _construct_email_header ( $from_name , $from_email , $cc = array() , $bcc = array() ) {

			$headers[] = 'From: ' . $from_name  . ' <' . $from_email . '>';

			if ( is_array( $cc ) )
				foreach ( $cc as $c )
					$headers[] = 'Cc: ' . $c;

			if ( is_array( $bcc ) )
				foreach ( $bcc as $bc )
					$headers[] = 'Bcc: ' . $bc;

			$headers[] = 'Content-Type: text/html; charset=UTF-8';

			return $headers;

		}

		/**
		 * Retrieves the attachment
		 *
		 * @param $userID
		 * @return array
		 *
		 * @since 1.6.0
		 */
		private function _get_custom_field_email_attachments( $userID ) {

			$wwlc_forms = WWLC_Forms::instance();
			$file_fields = $wwlc_forms->wwlc_get_file_custom_fields();
			$upload_dir = wp_upload_dir();
			$user_wholesale_dir = $upload_dir[ 'basedir' ] . '/wholesale-customers/' . $userID;
			$attachments = array();

			if ( ! is_array( $file_fields ) )
				return;

			// process attachments
			foreach ( $file_fields as $field ) {

				$attachments = $user_wholesale_dir . '/' . get_user_meta( $userID, $field[ 'name' ] , true );
			}

			return $attachments;
		}

	}

}

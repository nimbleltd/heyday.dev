<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

// This is where you set various options affecting the plugin

// Path Constants ======================================================================================================
define( 'WWLC_PLUGIN_BASE_PATH' ,	basename( dirname( __FILE__ ) ) . '/' );
define( 'WWLC_PLUGIN_URL' ,         plugins_url() . '/woocommerce-wholesale-lead-capture/' );
define( 'WWLC_PLUGIN_DIR' ,         plugin_dir_path( __FILE__ ) );
define( 'WWLC_CSS_ROOT_URL' ,       WWLC_PLUGIN_URL . 'css/' );
define( 'WWLC_CSS_ROOT_DIR' ,       WWLC_PLUGIN_DIR . 'css/' );
define( 'WWLC_IMAGES_ROOT_URL' ,    WWLC_PLUGIN_URL . 'images/' );
define( 'WWLC_IMAGES_ROOT_DIR' ,    WWLC_PLUGIN_DIR . 'images/' );
define( 'WWLC_INCLUDES_ROOT_URL' ,  WWLC_PLUGIN_URL . 'includes/' );
define( 'WWLC_INCLUDES_ROOT_DIR' ,	WWLC_PLUGIN_DIR . 'includes/' );
define( 'WWLC_JS_ROOT_URL' ,        WWLC_PLUGIN_URL . 'js/' );
define( 'WWLC_JS_ROOT_DIR' ,        WWLC_PLUGIN_DIR . 'js/' );
define( 'WWLC_LOGS_ROOT_URL' ,      WWLC_PLUGIN_URL . 'logs/' );
define( 'WWLC_LOGS_ROOT_DIR' ,      WWLC_PLUGIN_DIR . 'logs/' );
define( 'WWLC_TEMPLATES_ROOT_URL' , WWLC_PLUGIN_URL . 'templates/' );
define( 'WWLC_TEMPLATES_ROOT_DIR' ,	WWLC_PLUGIN_DIR . 'templates/' );
define( 'WWLC_LANGUAGES_ROOT_URL' ,	WWLC_PLUGIN_URL . 'languages/' );
define( 'WWLC_LANGUAGES_ROOT_DIR' ,	WWLC_PLUGIN_DIR . 'languages/' );

// Option Constants Vars ===============================================================================================
define( 'WWLC_ACTIVATION_CODE_TRIGGERED' ,  			'wwlc_activation_code_triggered' );
define( 'WWLC_OPTION_INSTALLED_VERSION' ,  				'wwlc_option_installed_version' );
define( 'WWLC_UNAPPROVED_ROLE' ,                        'wwlc_unapproved' );
define( 'WWLC_UNMODERATED_ROLE' ,                       'wwlc_unmoderated' );
define( 'WWLC_REJECTED_ROLE' ,                          'wwlc_rejected' );
define( 'WWLC_INACTIVE_ROLE' ,                          'wwlc_inactive' );
define( 'WWLC_OPTIONS_REGISTRATION_PAGE_ID' ,           'wwlc_options_registration_page_id' );
define( 'WWLC_OPTIONS_LOGIN_PAGE_ID' ,                  'wwlc_options_login_page_id' );
define( 'WWLC_OPTIONS_THANK_YOU_PAGE_ID' ,              'wwlc_options_thank_you_page_id' );
define( 'WWLC_OPTION_LICENSE_EMAIL' ,                   'wwlc_option_license_email' );
define( 'WWLC_OPTION_LICENSE_KEY' ,                     'wwlc_option_license_key' );
define( 'WWLC_OPTION_REGISTRATION_FORM_CUSTOM_FIELDS' ,	'wwlc_option_registration_form_custom_fields' );

$WWLC_REGISTRATION_FIELDS = null;
$newUserAdminNotificationEmailDefault = null;
$newUserAdminNotificationEmailAutoApprovedDefault = null;
$newUserEmailDefault = null;
$approvedEmailDefault = null;
$rejectedEmailDefault = null;

function wwlcInitializeGlobalVariables () {

	global  $WWLC_REGISTRATION_FIELDS,
			$newUserAdminNotificationEmailDefault,
			$newUserAdminNotificationEmailAutoApprovedDefault,
			$newUserEmailDefault,
			$approvedEmailDefault,
			$rejectedEmailDefault;

	// For country dropdown list
	$countries 		= new WC_Countries();
	$countries 		= $countries->get_countries();
	$countryList 	= array();

	$index = 0;
	foreach ( $countries as $key => $value ) {

		$countryList[ $index ][ 'value' ] = $key;
		$countryList[ $index ][ 'text' ] = $value;
		$index++;

	}

	// Global Constant Vars ================================================================================================
	if ( !isset( $WWLC_REGISTRATION_FIELDS ) )
		$WWLC_REGISTRATION_FIELDS = array(
			'first_name'  	   => array(
				'label'        => apply_filters( 'wwlc_filter_first_name_field_form_label' , __( 'First Name' , 'woocommerce-wholesale-lead-capture' ) ),
				'name'         => 'first_name',
				'id'           => 'first_name',
				'class'        => 'wwlc_registration_field form_field',
				'type'         => 'text',
				'required'     => true,
				'custom_field' => false,
				'active'       => true,
				'validation'   => array(),
				'field_order'  => ( is_numeric( get_option( 'wwlc_fields_first_name_field_order' ) ) ) ? get_option( 'wwlc_fields_first_name_field_order' ) : 1,
				'placeholder'  => ( get_option( 'wwlc_fields_first_name_field_placeholder' ) ) ? get_option( 'wwlc_fields_first_name_field_placeholder' ) : ""
			),
			'last_name'  	   => array(
				'label'        => apply_filters( 'wwlc_filter_last_name_field_form_label' , __( 'Last Name' , 'woocommerce-wholesale-lead-capture' ) ),
				'name'         => 'last_name',
				'id'           => 'last_name',
				'class'        => 'wwlc_registration_field form_field',
				'type'         => 'text',
				'required'     => true,
				'custom_field' => false,
				'active'       => true,
				'validation'   => array(),
				'field_order'  => ( is_numeric( get_option( 'wwlc_fields_last_name_field_order' ) ) ) ? get_option( 'wwlc_fields_last_name_field_order' ) : 2,
				'placeholder'  => ( get_option( 'wwlc_fields_last_name_field_placeholder' ) ) ? get_option( 'wwlc_fields_last_name_field_placeholder' ) : ""
			),
			'wwlc_phone'  	   => array(
				'label'        => apply_filters( 'wwlc_filter_phone_field_form_label' , __( 'Phone' , 'woocommerce-wholesale-lead-capture' ) ),
				'name'         => 'wwlc_phone',
				'id'           => 'wwlc_phone',
				'class'        => 'wwlc_registration_field form_field',
				'type'         => 'phone',
				'required'     => ( get_option( 'wwlc_fields_require_phone_field' ) == 'yes' ) ? true : false,
				'custom_field' => true,
				'active'       => true,
				'validation'   => array(),
				'field_order'  => ( is_numeric( get_option( 'wwlc_fields_phone_field_order' ) ) ) ? get_option( 'wwlc_fields_phone_field_order' ) : 3,
				'placeholder'  => ( get_option( 'wwlc_fields_phone_field_placeholder' ) ) ? get_option( 'wwlc_fields_phone_field_placeholder' ) : ""
			),
			'user_email'  	   => array(
				'label'        => apply_filters( 'wwlc_filter_email_field_form_label' , __( 'Email' , 'woocommerce-wholesale-lead-capture' ) ),
				'name'         => 'user_email',
				'id'           => 'user_email',
				'class'        => 'wwlc_registration_field form_field',
				'type'         => 'email',
				'required'     => true,
				'custom_field' => false,
				'active'       => true,
				'validation'   => array(),
				'field_order'  => ( is_numeric( get_option( 'wwlc_fields_email_field_order' ) ) ) ? get_option( 'wwlc_fields_email_field_order' ) : 4,
				'placeholder'  => ( get_option( 'wwlc_fields_email_field_placeholder' ) ) ? get_option( 'wwlc_fields_email_field_placeholder' ) : ""
			),
			'wwlc_username'    => array(
				'label'        => apply_filters( 'wwlc_filter_username_form_label' , __( 'Username' , 'woocommerce-wholesale-lead-capture' ) ),
				'name'         => 'wwlc_username',
				'id'           => 'wwlc_username',
				'class'        => 'wwlc_registration_field form_field',
				'type'         => 'text',
				'required'     => true,
				'custom_field' => false,
				'active'       => ( get_option( 'wwlc_fields_username_active' ) == 'yes' ) ? true : false,
				'validation'   => array(),
				'field_order'  => ( is_numeric( get_option( 'wwlc_fields_username_order' ) ) ) ? get_option( 'wwlc_fields_username_order' ).".1" : 4.1,
				'placeholder'  => ( get_option( 'wwlc_fields_username_placeholder' ) ) ? get_option( 'wwlc_fields_username_placeholder' ) : ""
			),
			'wwlc_company_name'  => array(
				'label'        => apply_filters( 'wwlc_filter_company_field_form_label' , __( 'Company Name' , 'woocommerce-wholesale-lead-capture' ) ),
				'name'         => 'wwlc_company_name',
				'id'           => 'wwlc_company_name',
				'class'        => 'wwlc_registration_field form_field',
				'type'         => 'text',
				'required'     => ( get_option( 'wwlc_fields_require_company_name_field' ) == 'yes' ) ? true : false,
				'custom_field' => true,
				'active'       => ( get_option( 'wwlc_fields_activate_company_name_field' ) == 'yes' ) ? true : false,
				'validation'   => array(),
				'field_order'  => ( is_numeric( get_option( 'wwlc_fields_company_name_field_order' ) ) ) ? get_option( 'wwlc_fields_company_name_field_order' ) : 5,
				'placeholder'  => ( get_option( 'wwlc_fields_company_field_placeholder' ) ) ? get_option( 'wwlc_fields_company_field_placeholder' ) : ""
			),
			'wwlc_country'     => array(
				'label'        => apply_filters( 'wwlc_filter_country_field_form_label' , __( 'Country' , 'woocommerce-wholesale-lead-capture' ) ),
				'name'         => 'wwlc_country',
				'id'           => 'wwlc_country',
				'class'        => 'wwlc_registration_field form_field',
				'type'         => 'select',
				'required'     => ( get_option( 'wwlc_fields_require_address_field' ) == 'yes' ) ? true : false,
				'custom_field' => true,
				'active'       => ( get_option( 'wwlc_fields_activate_address_field' ) == 'yes' ) ? true : false,
				'validation'   => array(),
				'field_order'  => ( is_numeric( get_option( 'wwlc_fields_address_field_order' ) ) ) ? get_option( 'wwlc_fields_address_field_order' ).".1" : 6.1,
				'options'	   => $countryList
			),
			'wwlc_address'     => array(
				'label'        => apply_filters( 'wwlc_filter_address1_field_form_label' , __( 'Address 1' , 'woocommerce-wholesale-lead-capture' ) ),
				'name'         => 'wwlc_address',
				'id'           => 'wwlc_address',
				'class'        => 'wwlc_registration_field form_field',
				'type'         => 'text',
				'required'     => ( get_option( 'wwlc_fields_require_address_field' ) == 'yes' ) ? true : false,
				'custom_field' => true,
				'active'       => ( get_option( 'wwlc_fields_activate_address_field' ) == 'yes' ) ? true : false,
				'validation'   => array(),
				'field_order'  => ( is_numeric( get_option( 'wwlc_fields_address_field_order' ) ) ) ? get_option( 'wwlc_fields_address_field_order' ).".2" : 6.2,
				'placeholder'  => apply_filters( 'wwlc_filter_address1_field_form_placeholder' , get_option( 'wwlc_fields_address_placeholder', '' ) ),
			),
			'wwlc_address_2'   => array(
				'label'        => apply_filters( 'wwlc_filter_address2_field_form_label' , __( 'Address Line 2' , 'woocommerce-wholesale-lead-capture' ) ),
				'name'         => 'wwlc_address_2',
				'id'           => 'wwlc_address_2',
				'class'        => 'wwlc_registration_field form_field',
				'type'         => 'text',
				'required'     => false,
				'custom_field' => true,
				'active'       => ( get_option( 'wwlc_fields_activate_address_field' ) == 'yes' ) ? true : false,
				'validation'   => array(),
				'field_order'  => ( is_numeric( get_option( 'wwlc_fields_address_field_order' ) ) ) ? get_option( 'wwlc_fields_address_field_order' ).".3" : 6.3,
				'placeholder'  => apply_filters( 'wwlc_filter_address2_field_form_placeholder' , get_option( 'wwlc_fields_address2_placeholder', '' ) )
			),
			'wwlc_city'  	   => array(
				'label'        => apply_filters( 'wwlc_filter_city_field_form_label' , __( 'City' , 'woocommerce-wholesale-lead-capture' ) ),
				'name'         => 'wwlc_city',
				'id'           => 'wwlc_city',
				'class'        => 'wwlc_registration_field form_field',
				'type'         => 'text',
				'required'     => ( get_option( 'wwlc_fields_require_address_field' ) == 'yes' ) ? true : false,
				'custom_field' => true,
				'active'       => ( get_option( 'wwlc_fields_activate_address_field' ) == 'yes' ) ? true : false,
				'validation'   => array(),
				'field_order'  => ( is_numeric( get_option( 'wwlc_fields_address_field_order' ) ) ) ? get_option( 'wwlc_fields_address_field_order' ).".4" : 6.4,
				'placeholder'  => apply_filters( 'wwlc_filter_city_field_form_placeholder' , get_option( 'wwlc_fields_city_placeholder', '' ) )
			),
			'wwlc_state'  	   => array(
				'label'        => apply_filters( 'wwlc_filter_state_field_form_label' , __( 'State' , 'woocommerce-wholesale-lead-capture' ) ),
				'name'         => 'wwlc_state',
				'id'           => 'wwlc_state',
				'class'        => 'wwlc_registration_field form_field',
				'type'         => 'text',
				'required'     => ( get_option( 'wwlc_fields_require_address_field' ) == 'yes' ) ? true : false,
				'custom_field' => true,
				'active'       => ( get_option( 'wwlc_fields_activate_address_field' ) == 'yes' ) ? true : false,
				'validation'   => array(),
				'field_order'  => ( is_numeric( get_option( 'wwlc_fields_address_field_order' ) ) ) ? get_option( 'wwlc_fields_address_field_order' ).".5" : 6.5,
				'placeholder'  => apply_filters( 'wwlc_filter_state_field_form_placeholder' , get_option( 'wwlc_fields_state_placeholder' , '' ) )
			),
			'wwlc_postcode'    => array(
				'label'        => apply_filters( 'wwlc_filter_postcode_field_form_label' , __( 'Postcode' , 'woocommerce-wholesale-lead-capture' ) ),
				'name'         => 'wwlc_postcode',
				'id'           => 'wwlc_postcode',
				'class'        => 'wwlc_registration_field form_field',
				'type'         => 'text',
				'required'     => ( get_option( 'wwlc_fields_require_address_field' ) == 'yes' ) ? true : false,
				'custom_field' => true,
				'active'       => ( get_option( 'wwlc_fields_activate_address_field' ) == 'yes' ) ? true : false,
				'validation'   => array(),
				'field_order'  => ( is_numeric( get_option( 'wwlc_fields_address_field_order' ) ) ) ? get_option( 'wwlc_fields_address_field_order' ).".6" : 6.6,
				'placeholder'  => apply_filters( 'wwlc_filter_postcode_field_form_placeholder' , get_option( 'wwlc_fields_postcode_placeholder', '' ) )
			),
			'wwlc_password'    => array(
				'label'        => apply_filters( 'wwlc_filter_password_field_form_label' , __( 'Password' , 'woocommerce-wholesale-lead-capture' ) ),
				'name'         => 'wwlc_password',
				'id'           => 'wwlc_password',
				'class'        => 'wwlc_registration_field form_field',
				'type'         => 'wwlc_password',
				'required'     => ( get_option( 'wwlc_fields_require_password_field' ) == 'yes' ) ? true : false,
				'custom_field' => true,
				'active'       => ( get_option( 'wwlc_fields_activate_password_field' ) == 'yes' ) ? true : false,
				'validation'   => array(),
				'field_order'  => ( is_numeric( get_option( 'wwlc_fields_password_field_order' ) ) ) ? get_option( 'wwlc_fields_password_field_order' ).".7" : 7.7,
				'placeholder'  => ( get_option( 'wwlc_fields_password_field_placeholder' ) ) ? get_option( 'wwlc_fields_password_field_placeholder' ) : ""
			)
		);

	// Default Email Content Constants =====================================================================================
	if ( !isset( $newUserAdminNotificationEmailDefault ) )
		$newUserAdminNotificationEmailDefault	=	__( 'New User Registration,' , 'woocommerce-wholesale-lead-capture' ) . "\n\n" .
			sprintf( __( 'Full Name : %1$s' , 'woocommerce-wholesale-lead-capture' ) , "{full_name}" ) . "\n" .
			sprintf( __( 'Email : %1$s' , 'woocommerce-wholesale-lead-capture' ) , "{email}" ) . "\n\n" .
			"{user_management_url}\n\n";

	if ( !isset( $newUserAdminNotificationEmailAutoApprovedDefault ) )
		$newUserAdminNotificationEmailAutoApprovedDefault	=	__( 'New User Registered and Approved,' , 'woocommerce-wholesale-lead-capture' ) . "\n\n" .
			sprintf( __( 'Full Name : %1$s' , 'woocommerce-wholesale-lead-capture' ) , "{full_name}" ) . "\n" .
			sprintf( __( 'Email : %1$s' , 'woocommerce-wholesale-lead-capture' ) , "{email}" ) . "\n\n" .
			__( 'User is Auto Approved' , 'woocommerce-wholesale-lead-capture' ) . "\n\n";
	if ( !isset( $newUserEmailDefault ) )
		$newUserEmailDefault	=	sprintf( __( 'Hi %1$s,' , 'woocommerce-wholesale-lead-capture' ) , "{full_name}" ) . "\n\n" .
			__( 'You have successfully registered as a Wholesale customer' , 'woocommerce-wholesale-lead-capture' ) . "\n\n" .
			__( "We'll send you an email once your application has been approved" , 'woocommerce-wholesale-lead-capture' ) . "\n\n" .
			__( 'Kind Regards' , 'woocommerce-wholesale-lead-capture' ) . "\n" .
			"{site_name}\n\n";

	if ( !isset( $approvedEmailDefault ) )
		$approvedEmailDefault	=	sprintf( __( 'Hi %1$s,' , 'woocommerce-wholesale-lead-capture' ) , "{full_name}" ) . "\n\n" .
			__( 'Congratulations, you have been approved as a wholesale customer.' , 'woocommerce-wholesale-lead-capture' ) . "\n\n" .
			__( 'Login using your new account to start shopping as a wholesale user.' , 'woocommerce-wholesale-lead-capture' ) . "\n\n" .
			__( 'Please save your credentials below:' , 'woocommerce-wholesale-lead-capture' ) . "\n" .
			sprintf( __( 'username: %1$s' , 'woocommerce-wholesale-lead-capture' ) , "{username}" ) . "\n" .
			sprintf( __( 'password: %1$s' , 'woocommerce-wholesale-lead-capture' ) , "{password}" ) . "\n\n" .
			__( 'login link below:' , 'woocommerce-wholesale-lead-capture' ) . "\n" .
			"{wholesale_login_url}\n\n" .
			__( 'Kind Regards,' , 'woocommerce-wholesale-lead-capture' ) . "\n" .
			"{site_name}\n\n";

	if ( !isset( $rejectedEmailDefault ) )
		$rejectedEmailDefault	=	sprintf( __( 'Hi %1$s,' , 'woocommerce-wholesale-lead-capture' ) , "{full_name}" ) . "\n\n" .
			__( 'Unfortunately you have not been approved as a Wholesale customer at this time.' , 'woocommerce-wholesale-lead-capture' ) . "\n\n" .
			__( 'If you feel this decision has been made in error please get in touch.' , 'woocommerce-wholesale-lead-capture' ) . "\n\n" .
			__( 'Kind Regards,' , 'woocommerce-wholesale-lead-capture' ) . "\n" .
			"{site_name}\n\n";

}

add_action( 'init' , 'wwlcInitializeGlobalVariables' , 1 );

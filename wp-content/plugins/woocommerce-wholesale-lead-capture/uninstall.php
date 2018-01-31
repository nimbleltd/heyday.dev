<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) exit();

require_once ( 'woocommerce-wholesale-lead-capture.options.php' );

// General Settings
delete_option( 'wwlc_general_new_lead_role' );
delete_option( 'wwlc_general_auto_approve_new_leads' );
delete_option( 'wwlc_general_login_page' );
delete_option( 'wwlc_general_registration_page' );
delete_option( 'wwlc_general_registration_thankyou' );

// Fields Settings
delete_option( 'wwlc_fields_activate_company_name_field' );
delete_option( 'wwlc_fields_require_company_name_field' );
delete_option( 'wwlc_fields_activate_address_field' );
delete_option( 'wwlc_fields_require_address_field' );

// Email Settings
delete_option( 'wwlc_emails_new_user_admin_notification_template' );
delete_option( 'wwlc_emails_new_user_admin_notification_auto_approved_template' );
delete_option( 'wwlc_emails_new_user_template' );
delete_option( 'wwlc_emails_approval_email_template' );
delete_option( 'wwlc_emails_rejected_email_template' );

// Help Settings
delete_option( WWLC_OPTIONS_REGISTRATION_PAGE_ID );
delete_option( WWLC_OPTIONS_LOGIN_PAGE_ID );
delete_option( WWLC_OPTIONS_THANK_YOU_PAGE_ID );


flush_rewrite_rules();
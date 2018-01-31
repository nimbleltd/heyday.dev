<?php
/*
Plugin Name:    WooCommerce Wholesale Lead Capture
Plugin URI:     https://wholesalesuiteplugin.com/
Description:    WooCommerce extension to provide functionality of capturing wholesale leads.
Author:         Rymera Web Co
Version:        1.7.0
Author URI:     http://rymera.com.au/
Text Domain:    woocommerce-wholesale-lead-capture
WC requires at least: 3.0.9
WC tested up to: 3.2.6
*/

require_once ( 'woocommerce-wholesale-lead-capture.functions.php' );

// Delete code activation flag on plugin deactivate.
register_deactivation_hook( __FILE__, 'wwlc_global_plugin_deactivate' );

// Check if WooCommerce is active
if ( count( wwlc_check_plugin_dependencies() ) <= 0 ) {

	// Include Necessary Files
	require_once ( 'woocommerce-wholesale-lead-capture.options.php' );
	require_once ( 'woocommerce-wholesale-lead-capture.plugin.php' );

	// Get Instance of Main Plugin Class
	$wc_wholesale_lead_capture = WooCommerce_Wholesale_Lead_Capture::instance();
	$GLOBALS[ 'wc_wholesale_lead_capture' ] = $wc_wholesale_lead_capture;

    /*
    |------------------------------------------------------------------------------------------------------------------
    | WooCommerce WholeSale Suit License Settings
    |------------------------------------------------------------------------------------------------------------------
    */

    // Add WooCommerce Wholesale Suit License Settings
    add_action( "admin_menu" , array( $wc_wholesale_lead_capture , 'registerWWSLicenseSettingsMenu' ) );

    // Add WWS License Settings Header Tab Item
    add_action( "wws_action_license_settings_tab" , array( $wc_wholesale_lead_capture , 'wwcLicenseSettingsHeader' ) );

    // Add WWS License Settings Page (WWLC)
    add_action( "wws_action_license_settings_wwlc" , array( $wc_wholesale_lead_capture , 'wwcLicenseSettingsPage' ) );

	/*
    |-------------------------------------------------------------------------------------------------------------------
    | Settings
    |-------------------------------------------------------------------------------------------------------------------
    */

	// Register Settings Page
	add_filter( "woocommerce_get_settings_pages" , array ( $wc_wholesale_lead_capture , 'initializePluginSettings' ) );

    /*
	|-------------------------------------------------------------------------------------------------------------------
	| Add Custom Plugin Listing Action Links
	|-------------------------------------------------------------------------------------------------------------------
	*/

    // Settings
    add_filter( 'plugin_action_links' , array( $wc_wholesale_lead_capture , 'addPluginListingCustomActionLinks' ) , 10 , 2 );

    /*
    |---------------------------------------------------------------------------------------------------------------
    | Execute WWLC
    |---------------------------------------------------------------------------------------------------------------
    */

    $wc_wholesale_lead_capture->run();

    /*
    |-------------------------------------------------------------------------------------------------------------------
    | Update Checker
    |-------------------------------------------------------------------------------------------------------------------
    */

    // Get license email and key
    $wwlc_option_license_key = get_option( WWLC_OPTION_LICENSE_KEY );
    $wwlc_option_license_email = get_option( WWLC_OPTION_LICENSE_EMAIL );

    if ( $wwlc_option_license_key && $wwlc_option_license_email ) {

        require 'plugin-updates/class-wws-plugin-update-checker.php';

        $wws_wwlc_update_checker = new WWS_Plugin_Update_Checker(
            'https://wholesalesuiteplugin.com/wp-admin/admin-ajax.php?action=wumGetUpdateInfo&plugin=lead-capture&licence=' . $wwlc_option_license_key . '&email=' . $wwlc_option_license_email,
            __FILE__,
            'woocommerce-wholesale-lead-capture',
            12,
            ''
        );

    } else {

        /**
         * Check if show notice if license details is not entered.
         *
         * @since 1.1.1
         */
        function wwlcAdminNotices () {

            global $current_user ;
            $user_id = $current_user->ID;
            global $wc_wholesale_lead_capture;

            /* Check that the user hasn't already clicked to ignore the message */
            if ( !get_user_meta( $user_id , 'wwlc_ignore_empty_license_notice' ) && !$wc_wholesale_lead_capture->checkIfInWWLCSettingsPage() ) {

                $current_url = $_SERVER[ "HTTP_HOST" ] . $_SERVER[ "REQUEST_URI" ];

                if ( strpos( $current_url , '?' ) !== false )
                    $mod_current_url = '//' . $current_url . '&wwlc_ignore_empty_license_notice=0';
                else
                    $mod_current_url = '//' . $current_url . '?wwlc_ignore_empty_license_notice=0'; ?>

                <div class="error">
                    <p>
                        <?php echo sprintf( __( 'Please <a href="%1$s">enter your license details</a> for the <b>WooCommerce Wholesale Lead Capture</b> plugin to enable plugin updates.' , 'woocommerce-wholesale-lead-capture' ) , "options-general.php?page=wwc_license_settings&tab=wwlc" ); ?>
                        <a href="<?php echo $mod_current_url; ?>" style="float: right;" id="wwlc_ignore_empty_license_notice"><?php _e( 'Hide Notice' , 'woocommerce-wholesale-lead-capture' ); ?></a>
                    </p>
                </div>

            <?php }

        }

        add_action( 'admin_notices', 'wwlcAdminNotices' );

        /**
         * Ignore empty license notice.
         *
         * @since 1.1.1
         */
        function wwlcHideAdminNotices() {

            global $current_user;
            $user_id = $current_user->ID;

            /* If user clicks to ignore the notice, add that to their user meta */
            if ( isset( $_GET[ 'wwlc_ignore_empty_license_notice' ] ) && '0' == $_GET[ 'wwlc_ignore_empty_license_notice' ] )
                add_user_meta( $user_id , 'wwlc_ignore_empty_license_notice' , 'true' , true );

        }

        add_action( 'admin_init', 'wwlcHideAdminNotices' );

    }

} else {

    /**
     * Provide admin notice to users that a required plugin dependency of WooCommerce Wholesale Lead Capture plugin is missing.
     *
     * @since 1.6.2
     */
    function wwlcAdminNotices () {

        $plugins = wwlc_check_plugin_dependencies();
        $adminNoticeMsg = '';

        foreach ( $plugins as $plugin ) {

            $pluginFile     = $plugin[ 'plugin-base' ];
            $sptFile        = trailingslashit( WP_PLUGIN_DIR ) . plugin_basename( $pluginFile );

            $sptInstallText = '<a href="' . wp_nonce_url( 'update.php?action=install-plugin&plugin=' . $plugin[ 'plugin-key' ], 'install-plugin_' . $plugin[ 'plugin-key' ] ) . '">' . __( 'Click here to install from WordPress.org repo &rarr;', 'woocommerce-wholesale-lead-capture' ) . '</a>';
            if ( file_exists( $sptFile ) )
                $sptInstallText = '<a href="' . wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $pluginFile . '&amp;plugin_status=all&amp;s', 'activate-plugin_' . $pluginFile ) . '" title="' . __( 'Activate this plugin', 'woocommerce-wholesale-lead-capture' ) . '" class="edit">' . __( 'Click here to activate &rarr;', 'woocommerce-wholesale-lead-capture' ) . '</a>';

            $adminNoticeMsg .= sprintf( __( '<br/>Please ensure you have the <a href="%1$s" target="_blank">%2$s</a> plugin installed and activated.<br/>', 'woocommerce-wholesale-lead-capture' ), 'http://wordpress.org/plugins/' . $plugin[ 'plugin-key' ] . '/', $plugin[ 'plugin-name' ] );
            $adminNoticeMsg .= $sptInstallText . '<br/>';

        } ?>

        <div class="error">
            <p>
                <?php _e( '<b>WooCommerce Wholesale Lead Capture</b> plugin missing dependency.<br/>', 'woocommerce-wholesale-lead-capture' ); ?>
                <?php echo $adminNoticeMsg; ?>
            </p>
        </div><?php

    }

    add_action( 'admin_notices', 'wwlcAdminNotices' );

}

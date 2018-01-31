<?php
/*
Plugin Name:          WooCommerce Wholesale Prices Premium
Plugin URI:           https://wholesalesuiteplugin.com/
Description:          WooCommerce Premium Extension for the Woocommerce Wholesale Prices Plugin
Author:               Rymera Web Co
Version:              1.16.0
Author URI:           http://rymera.com.au/
Text Domain:          woocommerce-wholesale-prices-premium
WC requires at least: 3.0.9
WC tested up to:      3.2.6
*/

// This file is the main plugin boot loader

/**
 * Register Global Deactivation Hook.
 * Codebase that must be run on plugin deactivation whether or not dependencies are present.
 * Necessary to prevent activation code from being executed more than once.
 *
 * @since 1.12.5
 * @since 1.13.0 Add multisite support.
 *
 * @param boolean $network_wide Flag that determines if plugin is deactivated on network wide or not.
 */
function wwpp_global_plugin_deactivate( $network_wide ) {

    global $wpdb;

    // check if it is a multisite network
    if ( is_multisite() ) {

        // check if the plugin has been activated on the network or on a single site
        if ( $network_wide ) {

            // get ids of all sites
            $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

            foreach ( $blog_ids as $blog_id ) {

                switch_to_blog( $blog_id );
                delete_option( 'wwpp_option_activation_code_triggered' );
                delete_option( 'wwpp_option_installed_version' );

            }

            restore_current_blog();

        } else {

            // activated on a single site, in a multi-site
            delete_option( 'wwpp_option_activation_code_triggered' );
            delete_option( 'wwpp_option_installed_version' );

        }

    } else {

        // activated on a single site
        delete_option( 'wwpp_option_activation_code_triggered' );
        delete_option( 'wwpp_option_installed_version' );

    }

}

register_deactivation_hook( __FILE__ , 'wwpp_global_plugin_deactivate' );

// Makes sure the plugin is defined before trying to use it
if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'get_plugin_data' ) )
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

/**
 * Check if Woocommerce Wholesale Prices is installed and active
 */
if ( is_plugin_active( 'woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php' ) ) {

    $wwp_plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php' );

    // Check WWP version
    // WWPP ( 1.6.0 and up ) we need WWP 1.1.7
    // WWPP ( 1.7.0 and up ) we need WWP 1.2.0
    // WWPP ( 1.7.4 and up ) we need WWP 1.2.2
    // WWPP ( 1.8.0 and up ) we need WWP 1.2.3
    // WWPP ( 1.13.0 and up ) we need WWP 1.3.0
    // WWPP ( 1.13.4 and up ) we need WWP 1.3.1
    // WWPP ( 1.14.1 and up ) we need WWP 1.4.1
    // WWPP ( 1.14.4 and up ) we need WWP 1.4.4
    // WWPP ( 1.15.0 and up ) we need WWP 1.5.0
    // WWPP ( 1.16.0 and up ) we need WWP 1.6.0
    if ( version_compare( $wwp_plugin_data[ 'Version' ] , '1.6.0' , '<' ) ) {

        // Required minimum version of wwp is not met

        /**
         * Provide admin notice when WWP version does not meet the required version for this plugin.
         *
         * @since 1.14.1
         */
        function wwppAdminNotices () {

            global $current_user ;

            $user_id                     = $current_user->ID;
            $thirsty_affiliates_basename = plugin_basename( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'woocommerce-wholesale-prices' . DIRECTORY_SEPARATOR . 'woocommerce-wholesale-prices.bootstrap.php' );

            /* Check that the user hasn't already clicked to ignore the message */
            if ( ! get_user_meta( $user_id , 'wwpp_ignore_incompatible_free_version_notice' ) ) {

                $sptInstallText = sprintf( __( '<a href="%1$s">Click here to update WooCommerce Wholesale Prices Plugin &rarr;</a>' , 'woocommerce-wholesale-prices-premium' ) , wp_nonce_url( 'update.php?action=upgrade-plugin&plugin=' . $thirsty_affiliates_basename , 'upgrade-plugin_' . $thirsty_affiliates_basename ) ); ?>

                <div class="error">
                    <p><?php echo sprintf( __( 'Please ensure you have the latest version of <a href="%1$s" target="_blank">WooCommerce Wholesale Prices</a> plugin installed and activated along with the Premium extension.' , 'woocommerce-wholesale-prices-premium' ) , 'http://wordpress.org/plugins/woocommerce-wholesale-prices/' ); ?></p>
                    <p><?php echo $sptInstallText; ?></p>
                </div>

            <?php }

        }

        add_action( 'admin_notices', 'wwppAdminNotices' );

    } else if ( get_option( 'wwp_running' ) !== 'no' ) { // so if value is 'yes' or blank ( for older wwp version which wwp_running option is not yet introduced )
        
        // Only run wwpp if indeed wwp is running

        // Include Necessary Files
        require_once ( 'woocommerce-wholesale-prices-premium.options.php' );
        require_once ( 'woocommerce-wholesale-prices-premium.plugin.php' );

        // Get Instance of Main Plugin Class
        $wc_wholesale_prices_premium = WooCommerceWholeSalePricesPremium::instance();
        $GLOBALS[ 'wc_wholesale_prices_premium' ] = $wc_wholesale_prices_premium;

        // Execute WWPP
        $wc_wholesale_prices_premium->run();
        
        
        // Update Checker ==============================================================================================

        // Get license email and key
        $wwpp_option_license_key   = get_option( WWPP_OPTION_LICENSE_KEY );
        $wwpp_option_license_email = get_option( WWPP_OPTION_LICENSE_EMAIL );

        if ( $wwpp_option_license_key && $wwpp_option_license_email ) {

            require 'plugin-updates/class-wws-plugin-update-checker.php';

            $wws_wwpp_update_checker = new WWS_Plugin_Update_Checker(
                'https://wholesalesuiteplugin.com/wp-admin/admin-ajax.php?action=wumGetUpdateInfo&plugin=prices-premium&licence=' . $wwpp_option_license_key . '&email=' . $wwpp_option_license_email,
                __FILE__,
                'woocommerce-wholesale-prices-premium',
                12,
                ''
            );

        } else {

            /**
             * Check if show notice if license details is not entered.
             *
             * @since 1.2.2
             */
            function wwppAdminNotices () {

                global $current_user ;
                $user_id = $current_user->ID;
                global $wc_wholesale_prices_premium;

                /* Check that the user hasn't already clicked to ignore the message */
                if ( !get_user_meta( $user_id , 'wwpp_ignore_empty_license_notice' ) && !$wc_wholesale_prices_premium->wwpp_license_settings->check_if_in_wwpp_settings_page() ) {

                    $current_url = $_SERVER[ "HTTP_HOST" ] . $_SERVER[ "REQUEST_URI" ];

                    if ( strpos( $current_url , '?' ) !== false )
                        $mod_current_url = '//' . $current_url . '&wwpp_ignore_empty_license_notice=0';
                    else
                        $mod_current_url = '//' . $current_url . '?wwpp_ignore_empty_license_notice=0'; ?>

                    <div class="error">
                        <p>
                            <?php echo sprintf( __( 'Please <a href="%1$s">enter your license details</a> for the <b>WooCommerce Wholesale Prices Premium</b> plugin to enable plugin updates.' , 'woocommerce-wholesale-prices-premium' ) , 'options-general.php?page=wwc_license_settings&tab=wwpp' ); ?>
                            <a href="<?php echo $mod_current_url; ?>" style="float: right;" id="wwpp_ignore_empty_license_notice"><?php _e( 'Hide Notice' , 'woocommerce-wholesale-prices-premium' ); ?></a>
                        </p>
                    </div>

                <?php }

            }

            add_action( 'admin_notices', 'wwppAdminNotices' );

            /**
             * Ignore empty license notice.
             *
             * @since 1.2.2
             */
            function wwppHideAdminNotices() {

                global $current_user;
                $user_id = $current_user->ID;

                /* If user clicks to ignore the notice, add that to their user meta */
                if ( isset( $_GET[ 'wwpp_ignore_empty_license_notice' ] ) && '0' == $_GET[ 'wwpp_ignore_empty_license_notice' ] )
                    add_user_meta( $user_id , 'wwpp_ignore_empty_license_notice' , 'true' , true );

            }

            add_action( 'admin_init', 'wwppHideAdminNotices' );

        }

    }

} else {

    // WooCommerce Wholesale Prices plugin not installed or inactive

    /**
     * Provide admin admin notice when premium plugin is active but the WWP is either not installed or inactive.
     *
     * @since 1.0.0
     */
    function wwppAdminNotices () {

        global $current_user ;
        $user_id = $current_user->ID;

        /* Check that the user hasn't already clicked to ignore the message */
        if ( ! get_user_meta( $user_id , 'wwpp_ignore_inactive_free_version_notice' ) ) {

            $plugin_file = 'woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php';
            $sptFile = trailingslashit( WP_PLUGIN_DIR ) . plugin_basename( $plugin_file );

            $sptInstallText = '<a href="' . wp_nonce_url( 'update.php?action=install-plugin&plugin=woocommerce-wholesale-prices', 'install-plugin_woocommerce-wholesale-prices' ) . '">' . __( 'Click here to install from WordPress.org repo &rarr;' , 'woocommerce-wholesale-prices-premium' ) . '</a>';
            if ( file_exists( $sptFile ) )
                $sptInstallText = '<a href="' . wp_nonce_url('plugins.php?action=activate&amp;plugin=' . $plugin_file . '&amp;plugin_status=all&amp;s' , 'activate-plugin_' . $plugin_file ) . '" title="' . __( 'Activate this plugin' , 'woocommerce-wholesale-prices-premium' ) . '" class="edit">' . __( 'Click here to activate &rarr;' , 'woocommerce-wholesale-prices-premium' ) . '</a>';

            ?>

            <div class="error">
                <p>
                    <?php echo sprintf( __( 'Please ensure you have the <a href="%1$s" target="_blank">WooCommerce Wholesale Prices</a> plugin installed and activated along with the Premium extension.' , 'woocommerce-wholesale-prices-premium' ) , 'http://wordpress.org/plugins/woocommerce-wholesale-prices/' ); ?> <br/>
                    <?php echo $sptInstallText; ?>
                </p>
            </div>

        <?php }

    }

    add_action( 'admin_notices' , 'wwppAdminNotices' );

}

<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 |--------------------------------------------------------------------------------------------------------------
 | MISC Functions
 |--------------------------------------------------------------------------------------------------------------
 */

/**
 * Check for plugin dependencies of WooCommerce Wholesale Lead Capture plugin.
 *
 * @since 1.6.2
 * @return array
 */
if( ! function_exists( 'wwlc_check_plugin_dependencies' ) ){
    function wwlc_check_plugin_dependencies() {

        // Makes sure the plugin is defined before trying to use it
        if ( ! function_exists( 'is_plugin_active' ) )
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        $i = 0;
        $plugins = array();
        $requiredPlugins = apply_filters( 'wwlc_required_plugins', array(
                                    'woocommerce/woocommerce.php'
                                ) );

        foreach ( $requiredPlugins as $plugin ) {
            if ( ! is_plugin_active( $plugin ) ) {
                $pluginName = explode( '/', $plugin );
                $plugins[ $i ][ 'plugin-key' ] = $pluginName[ 0 ];
                $plugins[ $i ][ 'plugin-base' ] = $plugin;
                $plugins[ $i ][ 'plugin-name' ] = ucwords( str_replace( '-', ' ', $pluginName[ 0 ] ) );
            }
            $i++;
        }

        return $plugins;

    }
}

/**
 * Delete code activation flag on plugin deactivate.
 *
 * @param bool $network_wide
 *
 * @since 1.3.0
 */
if( ! function_exists( 'wwlc_global_plugin_deactivate' ) ){
    function wwlc_global_plugin_deactivate( $network_wide ) {

        global $wpdb;

        // check if it is a multisite network
        if ( is_multisite() ) {

            // check if the plugin has been deactivated on the network or on a single site
            if ( $network_wide ) {

                // get ids of all sites
                $blogIDs = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

                foreach ( $blogIDs as $blogID ) {

                    switch_to_blog( $blogID );
                    delete_option( 'wwlc_activation_code_triggered' );
                    delete_option( 'wwlc_option_installed_version' );

                }

                restore_current_blog();

            } else {

                // deactivated on a single site, in a multi-site
                delete_option( 'wwlc_activation_code_triggered' );
                delete_option( 'wwlc_option_installed_version' );

            }

        } else {

            // deactivated on a single site
            delete_option( 'wwlc_activation_code_triggered' );
            delete_option( 'wwlc_option_installed_version' );

        }
    }
}

/**
 * Log deprecated function error to the php_error.log file and display on screen when not on AJAX.
 *
 * @since 1.7.0
 * @access public
 *
 * @param array  $trace       debug_backtrace() output
 * @param string $function    Name of depecrated function.
 * @param string $version     Version when the function is set as depecrated since.
 * @param string $replacement Name of function to be replaced.
 */
function wwlc_deprecated_function( $trace , $function , $version , $replacement = null ) {

		$caller = array_shift( $trace );

		$log_string  = "The <em>{$function}</em> function is deprecated since version <em>{$version}</em>.";
		$log_string .= $replacement ? " Replace with <em>{$replacement}</em>." : '';
		$log_string .= ' Trace: <strong>' . $caller[ 'file' ] . '</strong> on line <strong>' . $caller[ 'line' ] . '</strong>';

		error_log( strip_tags( $log_string ) );

		if ( ! is_ajax() && WP_DEBUG && apply_filters( 'deprecated_function_trigger_error', true ) )
			echo $log_string;
	}

<?php if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) exit();

/**
 * Function that houses the code that cleans up the plugin options on un-installation.
 * Deletes options and post meta registered by the plugin.
 *
 * @since 1.10.0
 * @since 1.16.0 Remove additional options added on 1.16.0
 */
function wwpp_plugin_cleanup() {

    if ( get_option( "wwpp_settings_help_clean_plugin_options_on_uninstall" ) == 'yes' ) {
        
        // Settings

        // General
        delete_option( 'wwpp_settings_only_show_wholesale_products_to_wholesale_users' );
        delete_option( 'wwpp_settings_disable_coupons_for_wholesale_users' );
        delete_option( 'wwpp_settings_multiple_category_wholesale_discount_logic' );
        delete_option( 'wwpp_settings_hide_quantity_discount_table' );
        delete_option( 'wwpp_settings_thankyou_message' );
        delete_option( 'wwpp_settings_thankyou_message_position' );
        delete_option( 'wwpp_settings_always_allow_backorders_to_wholesale_users' );
        delete_option( 'wwpp_settings_minimum_order_quantity' );
        delete_option( 'wwpp_settings_minimum_order_price' );
        delete_option( 'wwpp_settings_minimum_requirements_logic' );
        delete_option( 'wwpp_settings_override_order_requirement_per_role' );
        delete_option( 'wwpp_settings_hide_product_categories_product_count' );
        delete_option( 'wwpp_settings_clear_cart_on_login' );

        // Price
        delete_option( 'wwpp_settings_wholesale_price_title_text' );        
        delete_option( 'wwpp_settings_explicitly_use_product_regular_price_on_discount_calc' );
        delete_option( 'wwpp_settings_hide_original_price' );        
        delete_option( 'wwpp_settings_variable_product_price_display' );
        delete_option( 'wwpp_hide_wholesale_price_on_product_listing' );

        // Tax
        delete_option( 'wwpp_settings_tax_exempt_wholesale_users' );
        delete_option( 'wwpp_settings_incl_excl_tax_on_wholesale_price' );
        delete_option( 'wwpp_settings_wholesale_tax_display_cart' );
        delete_option( 'wwpp_settings_override_price_suffix_regular_price' );
        delete_option( 'wwpp_settings_override_price_suffix' );
        delete_option( 'wwpp_settings_wholesale_role_tax_exemption_mapping_section_title' );
        delete_option( 'wwpp_option_wholesale_role_tax_class_options_mapping' );
        delete_option( 'wwpp_settings_mapped_tax_classes_for_wholesale_users_only' );
        
        // Shipping
        delete_option( 'wwpp_settings_wholesale_users_use_free_shipping' );
        delete_option( 'wwpp_dynamic_free_shipping_title' );
        delete_option( 'wwpp_settings_mapped_methods_for_wholesale_users_only' );
        delete_option( 'wwpp_settings_wholesale_shipping_section_title' );

        // Discount
        delete_option( 'enable_wholesale_role_cart_quantity_based_wholesale_discount' );
        delete_option( 'enable_wholesale_role_cart_quantity_based_wholesale_discount_mode_2' );

        // Payment Gateway

        // Cache
        delete_option( 'wwpp_enable_var_prod_price_range_caching' );
        delete_option( 'wwpp_product_cat_hash' );
        delete_option( 'wwpp_settings_hash' );

        // Help
        delete_option( 'wwpp_settings_help_clean_plugin_options_on_uninstall' );


        // Options
        delete_option( "wwpp_option_wholesale_role_shipping_method_mapping" );
        delete_option( "wwpp_option_license_email" );
        delete_option( "wwpp_option_license_key" );
        delete_option( "wwpp_option_wholesale_role_general_discount_mapping" );
        delete_option( "wwpp_option_wholesale_role_cart_qty_based_discount_mapping" );
        delete_option( "wwpp_option_payment_gateway_surcharge_mapping" );
        delete_option( "wwpp_option_wholesale_role_payment_gateway_mapping" );
        delete_option( "wwpp_option_activation_code_triggered" );
        delete_option( "wwpp_option_wholesale_role_order_requirement_mapping" );
        delete_option( "wwpp_option_wholesale_role_tax_option_mapping" );
        delete_option( "wwpp_option_wholesale_role_shipping_zone_method_mapping" );
        delete_option( "wwpp_option_ignore_wc_2_6_shipping_notice" );
        delete_option( "wwpp_option_product_cat_wholesale_role_filter" );

        // Post meta
        global $wpdb;
        $wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key IN ( 
            'wwpp_product_wholesale_visibility_filter' , 
            'wwpp_post_meta_enable_quantity_discount_rule' , 
            'wwpp_post_meta_quantity_discount_rule_mapping_view' , 
            'wwpp_post_meta_quantity_discount_rule_mapping' )" );
        
    }

}

wwpp_plugin_cleanup();

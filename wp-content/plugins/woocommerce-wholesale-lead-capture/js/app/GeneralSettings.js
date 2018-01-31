jQuery( document ).ready( function ( $ ) {

    // We do this to achieve deselecting a single value. WooCommerce uses select 2.
    $( "#wwlc_general_login_redirect_page" ).chosen( { allow_single_deselect: true, placeholder_text_single: GeneralSettingsVars.select_placeholder_text } );
    $( "#wwlc_general_logout_redirect_page" ).chosen( { allow_single_deselect: true, placeholder_text_single: GeneralSettingsVars.select_placeholder_text } );
    $( "#wwlc_general_login_page" ).chosen( { allow_single_deselect: true, placeholder_text_single: GeneralSettingsVars.select_placeholder_text } );
    $( "#wwlc_general_registration_page" ).chosen( { allow_single_deselect: true, placeholder_text_single: GeneralSettingsVars.select_placeholder_text } );
    $( "#wwlc_general_registration_thankyou" ).chosen( { allow_single_deselect: true, placeholder_text_single: GeneralSettingsVars.select_placeholder_text } );
    $( "#wwlc_general_terms_and_condition_page_url" ).chosen( { allow_single_deselect: true, placeholder_text_single: GeneralSettingsVars.select_placeholder_text } );

    $( '.woocommerce td.wwlc_chosen_select' ).on( 'change' , 'select' , function(e) {

        var $select = $(this),
            $td     = $select.closest( 'td.wwlc_chosen_select' ),
            $input  = $td.find( 'input[type="url"]' );

        if ( $(this).val() === 'custom' )
            $input.prop( 'disabled' , false ).show();
        else
            $input.prop( 'disabled' , true ).hide();
    } );

    $( '.woocommerce td.wwlc_chosen_select select' ).trigger( 'change' )

} );

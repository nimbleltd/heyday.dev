jQuery( document ).ready( function ( $ ) {

    var $wws_settings_wwlc = $( "#wws_settings_wwlc" ),
        $wws_wwlc_license_email = $wws_settings_wwlc.find( "#wws_wwlc_license_email" ),
        $wws_wwlc_license_key = $wws_settings_wwlc.find( "#wws_wwlc_license_key" ),
        $wws_save_btn = $wws_settings_wwlc.find( "#wws_save_btn" ),
        errorMessageDuration = '10000',
        successMessageDuration = '5000';

    $wws_save_btn.click( function () {

        var $this = $( this );

        $this
            .attr( "disabled" , "disabled" )
            .siblings( ".spinner" )
                .css( {
                    display : 'inline-block',
                    visibility : 'visible'
                } );

        var $licenseDetails = {
            'license_email' :   $.trim( $wws_wwlc_license_email.val() ),
            'license_key'   :   $.trim( $wws_wwlc_license_key.val() )
        };

        wwlcBackEndAjaxServices.saveWWLCLicenseDetails( $licenseDetails )
            .done( function ( data , textStatus , jqXHR ) {

                if ( data.status == "success" ) {

                    toastr.success( '' , WWSLicenseSettingsVars.success_save_message , { "closeButton" : true , "showDuration" : successMessageDuration } );

                } else {

                    toastr.error( '' , WWSLicenseSettingsVars.failed_save_message , { "closeButton" : true , "showDuration" : errorMessageDuration } );

                    console.log( WWSLicenseSettingsVars.failed_save_message );
                    console.log( data );
                    console.log( '----------' );

                }

            } )
            .fail( function ( jqXHR , textStatus , errorThrown ) {

                toastr.error( jqXHR.responseText , WWSLicenseSettingsVars.failed_save_message , { "closeButton" : true , "showDuration" : errorMessageDuration } );

                console.log( WWSLicenseSettingsVars.failed_save_message );
                console.log( jqXHR );
                console.log( '----------' );

            } )
            .always( function () {

                $this
                    .removeAttr( "disabled" )
                    .siblings( ".spinner" )
                        .css( {
                            display : 'none',
                            visibility : 'hidden'
                        } );

            } );

    } );

} );
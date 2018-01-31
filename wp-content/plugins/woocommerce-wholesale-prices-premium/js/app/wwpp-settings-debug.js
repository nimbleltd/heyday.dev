jQuery( document ).ready( function( $ ) {

    var $initialize_product_visibility_meta_btn = $( "#initialize-product-visibility-meta" ),
        error_message_duration = '10000',
        success_message_duration = '5000';

    $initialize_product_visibility_meta_btn.click( function() {

        var $this = $( this );

        $this
            .attr( 'disabled' , 'disabled' )
            .siblings( '.spinner' )
                .css( 'display' , 'inline-block' )
                .css( 'visibility' , 'visible' );

        wwppBackendAjaxServices.initialize_product_visibility_meta()
            .done( function( data , textStatus , jqXHR ) {

                if ( data.status == 'success' ) {

                    toastr.success( '' , wwpp_settings_debug_var.success_initialize_visibility_meta_txt , { "closeButton" : true , "showDuration" : success_message_duration } );

                } else {

                    toastr.error( '' , wwpp_settings_debug_var.failed_initialize_visibility_meta_txt , { "closeButton" : true , "showDuration" : error_message_duration } );

                    console.log( wwpp_settings_debug_var.failed_initialize_visibility_meta_txt );
                    console.log( data );
                    console.log( '----------' );

                }

            } )
            .fail( function( jqXHR , textStatus , errorThrown ) {

                toastr.error( '' , wwpp_settings_debug_var.failed_initialize_visibility_meta_txt , { "closeButton" : true , "showDuration" : error_message_duration } );

                console.log( wwpp_settings_debug_var.failed_initialize_visibility_meta_txt );
                console.log( jqXHR );
                console.log( '----------' );

            } )
            .always( function() {

                $this
                    .removeAttr( 'disabled' )
                    .siblings( '.spinner' )
                        .css( 'display' , 'none' )
                        .css( 'visibility' , 'hidden' );

            } );

    } );

} );
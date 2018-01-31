jQuery(document).ready(function($){

    // Variable Declaration And Selector Caching
    var $yourProfile    = $("#your-profile"),
        countryCode 	= $yourProfile.find("select#wwlc_country").val(),
    	stateSelected 	= $yourProfile.find("#wwlc_state").val();

    // Events
    $yourProfile.find("select#wwlc_country").select2();

    // On page load prefill state
    wwlcBackEndAjaxServices.getStates( countryCode )
        .done(function(data, textStatus, jqXHR){

            if ( data.status == 'success' ) {
            	
                wwlcFormActions.displayStatesDropdownField( $yourProfile, data.states, stateSelected );
                $yourProfile.find("select#wwlc_state").select2();

            } else {

                wwlcFormActions.displayStatesTextField( $yourProfile );

            }
        })
        .fail(function(jqXHR, textStatus, errorThrown){

            console.log( jqXHR.responseText );
            console.log( textStatus );
            console.log( errorThrown );
            console.log( '----------' );

        });

    $( "#wwlc_country" ).on( "change", function(){

        var cc = $(this).val();

        if( cc != "" ){

            wwlcBackEndAjaxServices.getStates( cc )
                .done(function(data, textStatus, jqXHR){

                    if ( data.status == 'success' ) {
                        console.log(data);
                        wwlcFormActions.displayStatesDropdownField( $yourProfile, data.states, stateSelected );
                        $yourProfile.find("select#wwlc_state").select2();

                    } else {

                        wwlcFormActions.displayStatesTextField( $yourProfile );

                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown){

                    console.log( jqXHR.responseText );
                    console.log( textStatus );
                    console.log( errorThrown );
                    console.log( '----------' );

                });
        }
    });

});
jQuery( document ).ready( function( $ ) {
    
    function update_variation_price_quantity_field_value() {
        
        var variations_data = JSON.parse( $( "form.variations_form" ).attr( "data-product_variations" ) ),
            $variations_form = $( ".variations_form" ),
            variation_id     = $variations_form.find( ".single_variation_wrap .variation_id" ).attr( 'value' ),
            $qty_field       = $variations_form.find( ".variations_button .qty" );

        if ( !variation_id ) // No variation selected
            $qty_field.val( 1 ).attr( 'step' , 1 ).attr( 'min'  , 1 );
        else {

            $qty_field.val( 1 ).attr( 'step' , 1 ).attr( 'min'  , 1 );

            for ( var i = 0 ; i < variations_data.length ; i++ ) {
                
                if ( variations_data[ i ].variation_id == variation_id ) {

                    if ( variations_data[ i ].input_value )
                        $qty_field.val( variations_data[ i ].input_value );
                    else
                        $qty_field.val( variations_data[ i ].min_qty );

                    $qty_field.attr( 'step' , variations_data[ i ].step );

                    if ( variations_data[ i ].min_value )
                        $qty_field.attr( 'min' , variations_data[ i ].min_value );
                    else
                        $qty_field.attr( 'min' , variations_data[ i ].min_qty );

                    break;
    
                } 
                
            }

        }
        
    }

    $( "body" ).on( "woocommerce_variation_has_changed" , ".variations_form" , update_variation_price_quantity_field_value );
    $( "body" ).on( "found_variation" , ".variations_form" , update_variation_price_quantity_field_value ); // Only triggered on ajax complete

} );

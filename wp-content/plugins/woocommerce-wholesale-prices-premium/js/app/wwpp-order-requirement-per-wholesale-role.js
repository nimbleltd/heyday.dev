jQuery( document ).ready( function( $ ) {

    var $override_chkbox = $( "#wwpp_settings_override_order_requirement_per_role" ),
        $fieldset = $override_chkbox.closest( 'fieldset'),
        error_message_duration = '10000',
        success_message_duration = '5000';

    /*
     |------------------------------------------------------------------------------------------------------------------
     | Functions
     |------------------------------------------------------------------------------------------------------------------
     */

    function render_wholesale_role_requirement_controls() {

        var wholesale_roles = '';
        for ( var key in wwpp_order_requirement_per_wholesale_role_var.wholesale_roles ) {

            if ( wwpp_order_requirement_per_wholesale_role_var.wholesale_roles.hasOwnProperty( key ) )
                wholesale_roles += '<option value="' + key + '">' + wwpp_order_requirement_per_wholesale_role_var.wholesale_roles[ key ][ 'roleName' ] + '</option>';

        }

        var wholesale_role_order_requirement_mapping = '', item_number = 0, tr_class = '';
        for ( var key in wwpp_order_requirement_per_wholesale_role_var.order_requirement ) {

            item_number++;

            if ( item_number % 2 === 0 )
                tr_class = 'even';
            else
                tr_class = 'odd alternate';

            if ( wwpp_order_requirement_per_wholesale_role_var.order_requirement.hasOwnProperty( key ) )
                wholesale_role_order_requirement_mapping +=
                    '<tr class="' + tr_class + '">' +
                        '<td class="meta hidden">' +
                            '<span class="wholesale_role">' + key + '</span>' +
                        '</td>' +
                        '<td class="wholesale_role_name">' + wwpp_order_requirement_per_wholesale_role_var.wholesale_roles[ key ][ 'roleName' ] + '</td>' +
                        '<td class="minimum_order_quantity">' + wwpp_order_requirement_per_wholesale_role_var.order_requirement[ key ][ 'minimum_order_quantity' ] + '</td>' +
                        '<td class="minimum_order_logic">' + wwpp_order_requirement_per_wholesale_role_var.order_requirement[ key ][ 'minimum_order_logic' ] + '</td>' +
                        '<td class="minimum_order_subtotal">' + wwpp_order_requirement_per_wholesale_role_var.order_requirement[ key ][ 'minimum_order_subtotal' ] + '</td>' +
                        '<td class="controls">' +
                            '<a class="edit dashicons dashicons-edit"></a>' +
                            '<a class="delete dashicons dashicons-no"></a>' +
                        '</td>' +
                    '</tr>';

        }

        if ( wholesale_role_order_requirement_mapping === '' ) {

            wholesale_role_order_requirement_mapping =
                '<tr class="no-items">' +
                    '<td class="colspanchange" colspan="5">' + wwpp_order_requirement_per_wholesale_role_var.no_mapping_txt + '</td>' +
                '</tr>';

        }

        var control_markup =
        '<div id="order-requirement-per-wholesale-role-controls" style="display: none;">' +

            // Form Controls
            '<div class="order-requirement-controls">' +

                '<div class="field-container wholesale-roles-field-container">' +
                    '<label>' + wwpp_order_requirement_per_wholesale_role_var.wholesale_role_txt_with_col + '</label>' +
                    '<select id="wholesale-roles" data-placeholder="' + wwpp_order_requirement_per_wholesale_role_var.choose_wholesale_role_txt + '">' +
                        '<option value=""></option>' +
                        wholesale_roles +
                    '</select>' +
                '</div>' +

                '<div class="field-container">' +
                    '<label>' + wwpp_order_requirement_per_wholesale_role_var.min_order_qty_txt_with_col + '</label>' +
                    '<input type="number" id="minimum-order-quantity">' +
                '</div>' +

                '<div class="field-container">' +
                    '<label>' + wwpp_order_requirement_per_wholesale_role_var.minimum_subtotal_txt_with_col + '</label>' +
                    '<input id="minimum-order-subtotal" type="text" class="wc_input_price">' +
                '</div>' +

                '<div class="field-container">' +
                    '<label>' + wwpp_order_requirement_per_wholesale_role_var.minimum_order_logic_txt_with_col + '</label>' +
                    '<select id="minimum-order-logic">' +
                        '<option value="and">' + wwpp_order_requirement_per_wholesale_role_var.and_txt + '</option>' +
                        '<option value="or">' + wwpp_order_requirement_per_wholesale_role_var.or_txt + '</option>' +
                    '</select>' +
                '</div>' +

            '</div><!--.order-requirement-controls-->' +

            // Button Controls
            '<div class="order-requirement-button-controls add-mode">' +

                '<input type="button" id="cancel-edit-mapping" class="button button-secondary" value="' + wwpp_order_requirement_per_wholesale_role_var.cancel_txt + '"/>' +
                '<input type="button" id="save-mapping" class="button button-primary" value="' + wwpp_order_requirement_per_wholesale_role_var.save_mapping_txt + '"/>' +
                '<input type="button" id="add-mapping" class="button button-primary" value="' + wwpp_order_requirement_per_wholesale_role_var.add_mapping_txt + '"/>' +
                '<span class="spinner"></span>' +

                '<div style="clear: both; float: none; display: block;"></div>' +

            '</div><!--.order-requirement-button-controls-->' +

            // Wholesale Role Order Requirement Table
            '<table id="order-requirement-per-wholesale-role" class="wp-list-table widefat">' +

                '<thead>' +
                    '<tr>' +
                        '<th>' + wwpp_order_requirement_per_wholesale_role_var.wholesale_role_txt + '</th>' +
                        '<th>' + wwpp_order_requirement_per_wholesale_role_var.min_order_qty_txt + '</th>' +
                        '<th>' + wwpp_order_requirement_per_wholesale_role_var.minimum_order_logic_txt + '</th>' +
                        '<th>' + wwpp_order_requirement_per_wholesale_role_var.minimum_subtotal_txt + '</th>' +
                        '<th></th>' +
                    '</tr>' +
                '</thead>' +

                '<tfoot>' +
                    '<tr>' +
                        '<th>' + wwpp_order_requirement_per_wholesale_role_var.wholesale_role_txt + '</th>' +
                        '<th>' + wwpp_order_requirement_per_wholesale_role_var.min_order_qty_txt + '</th>' +
                        '<th>' + wwpp_order_requirement_per_wholesale_role_var.minimum_order_logic_txt + '</th>' +
                        '<th>' + wwpp_order_requirement_per_wholesale_role_var.minimum_subtotal_txt + '</th>' +
                        '<th></th>' +
                    '</tr>' +
                '</tfoot>' +

                '<tbody>' +
                    wholesale_role_order_requirement_mapping +
                '</tbody>' +

            '</table>' +

        '</div><!--#order-requirement-per-wholesale-role-controls-->';

        $fieldset.append( control_markup );

        // Append help resource link about how wholesale role order requirement is logically structured.
        $( "#wwpp_settings_minimum_requirements_logic")
            .closest( "td" )
            .append(    '<br><br>' +
                        '<a href="https://wholesalesuiteplugin.com/kb/why-does-the-cart-show-retail-prices-until-the-minimums-are-met/?utm_source=Prices%20Premium%20Plugin&utm_medium=Settings&utm_campaign=Minimum%Order%20Setting%20" target="_blank">' +
                            'read more about how this function works.' +
                        '</a>' );

    }
    
    function remove_table_no_items_placeholder( $table ) {

        $table.find( "tbody" ).find( ".no-items" ).remove();

    }

    function reset_table_row_styling() {

        $( "#order-requirement-per-wholesale-role" )
            .find( "tbody" )
            .find( "tr" )
            .each( function( index ) {

                index++; // we do this coz index is zero base

                if (index % 2 === 0) {
                    // even
                    $(this)
                        .removeClass( "odd" )
                        .removeClass( "alternate" )
                        .addClass( "even" );

                } else {
                    // odd
                    $(this)
                        .removeClass( "even" )
                        .addClass( "odd" )
                        .addClass( "alternate" );

                }

            } );

    }

    function reset_fields() {

        $( "#wholesale-roles" ).removeAttr( "disabled" ).find( "option:first-child" ).attr( "selected" , "selected" ).end().trigger( "change" ).trigger( "chosen:updated" );
        $( "#minimum-order-quantity" ).val( "" );
        $( "#minimum-order-subtotal" ).val( "" );
        $( "#minimum-order-logic").find( "option:first-child" ).attr( "selected" , "selected" );

    }
    
    function validate_fields( wholesale_role , minimum_order_quantity , minimum_order_subtotal ) {

        var error_fields = [];

        if ( wholesale_role === "" )
            error_fields.push( wwpp_order_requirement_per_wholesale_role_var.wholesale_role_txt );

        if ( minimum_order_quantity === "" && minimum_order_subtotal === "" )
            error_fields.push( wwpp_order_requirement_per_wholesale_role_var.min_order_qty_txt + ' / ' + wwpp_order_requirement_per_wholesale_role_var.minimum_subtotal_txt );

        return error_fields;

    }




    /*
     |------------------------------------------------------------------------------------------------------------------
     | Events
     |------------------------------------------------------------------------------------------------------------------
     */

    $override_chkbox.change( function() {

        if ( $( this).is( ':checked' ) ) {

            $( "#order-requirement-per-wholesale-role-controls" ).slideDown();

        } else {

            $( "#order-requirement-per-wholesale-role-controls" ).slideUp();

        }

    } );

    $fieldset.delegate( '#add-mapping' , 'click' , function() {

        var $this = $( this ),
            wholesale_role = $.trim( $( "#wholesale-roles" ).val() ),
            wholesale_role_txt = $.trim( $( "#wholesale-roles option:selected" ).text() ),
            minimum_order_quantity = $.trim( $( "#minimum-order-quantity" ).val() ),
            minimum_order_subtotal = $.trim( $( "#minimum-order-subtotal").val() ),
            minimum_order_logic = $.trim( $( "#minimum-order-logic").val() ),
            $button_controls = $( ".order-requirement-button-controls" ),
            $mapping_table = $( "#order-requirement-per-wholesale-role" ),
            error_fields;

        $this.attr( 'disabled' , 'disabled' );
        $button_controls.addClass( 'processing' );

        error_fields = validate_fields( wholesale_role , minimum_order_quantity , minimum_order_subtotal );

        if ( error_fields.length > 0 ) {

            var msg = wwpp_order_requirement_per_wholesale_role_var.empty_fields_txt + "<br/><ul>";

            for ( var i = 0 ; i < error_fields.length ; i++ )
                msg += "<li>" + error_fields[ i ] + "</li>";

            msg += "</ul>";

            toastr.error( msg , wwpp_order_requirement_per_wholesale_role_var.form_error_txt , { "closeButton" : true , "showDuration" : error_message_duration } );

            $this.removeAttr( 'disabled' );
            $button_controls.removeClass( 'processing' );

            return false;

        }

        var mapping = {
                        'wholesale_role'            :   wholesale_role,
                        'minimum_order_quantity'    :   minimum_order_quantity,
                        'minimum_order_subtotal'    :   minimum_order_subtotal,
                        'minimum_order_logic'       :   minimum_order_logic
                    };

        wwppBackendAjaxServices.add_wholesale_role_order_requirement( mapping )
            .done( function( data , textStatus , jqXHR ) {

                if ( data.status == 'success' ) {

                    toastr.success( '' , wwpp_order_requirement_per_wholesale_role_var.success_add_mapping_txt , { "closeButton" : true , "showDuration" : success_message_duration } );

                    remove_table_no_items_placeholder( $mapping_table );

                    var tr_class = "";

                    if( $mapping_table.find( "tr" ).length % 2 === 0 ) // currently even, next add (our add) would make it odd
                        tr_class = "odd alternate";
                    else // currently odd, next add (our add) would make it even
                        tr_class = "even";

                    $mapping_table
                        .find( "tbody" )
                        .append(
                            '<tr class="' + tr_class + ' edited">' +
                                '<td class="meta hidden">' +
                                    '<span class="wholesale_role">' + mapping.wholesale_role + '</span>' +
                                '</td>' +
                                '<td class="wholesale_role_name">' + wholesale_role_txt + '</td>' +
                                '<td class="minimum_order_quantity">' + mapping.minimum_order_quantity + '</td>' +
                                '<td class="minimum_order_logic">' + mapping.minimum_order_logic + '</td>' +
                                '<td class="minimum_order_subtotal">' + mapping.minimum_order_subtotal + '</td>' +
                                '<td class="controls">' +
                                        '<a class="edit dashicons dashicons-edit"></a>' +
                                        '<a class="delete dashicons dashicons-no"></a>' +
                                    '</td>' +
                                '</tr>'
                        );

                    reset_fields();

                    // Remove edited class to the recently added user field
                    setTimeout( function () {

                        $mapping_table
                            .find( "tr.edited" )
                            .removeClass( "edited" );

                    } , 500 );

                } else {

                    toastr.error( data.error_message , wwpp_order_requirement_per_wholesale_role_var.failed_add_mapping_txt , { "closeButton" : true , "showDuration" : error_message_duration } );

                    console.log( wwpp_order_requirement_per_wholesale_role_var.failed_add_mapping_txt );
                    console.log( data );
                    console.log( '----------' );

                }

            } )
            .fail( function( jqXHR , textStatus , errorThrown ) {

                toastr.error( jqXHR.responseText , wwpp_order_requirement_per_wholesale_role_var.failed_add_mapping_txt , { "closeButton" : true , "showDuration" : error_message_duration } );

                console.log( wwpp_order_requirement_per_wholesale_role_var.failed_add_mapping_txt );
                console.log( jqXHR );
                console.log( '----------' );

            } )
            .always( function() {

                $this.removeAttr( 'disabled' );
                $button_controls.removeClass( 'processing' );

            } );

    } );

    $fieldset.delegate( '#save-mapping' , 'click' , function() {

        var $this = $( this ),
            wholesale_role = $.trim( $( "#wholesale-roles" ).val() ),
            wholesale_role_txt = $.trim( $( "#wholesale-roles option:selected" ).text() ),
            minimum_order_quantity = $.trim( $( "#minimum-order-quantity" ).val() ),
            minimum_order_subtotal = $.trim( $( "#minimum-order-subtotal").val() ),
            minimum_order_logic = $.trim( $( "#minimum-order-logic").val() ),
            $button_controls = $( ".order-requirement-button-controls" ),
            $mapping_table = $( "#order-requirement-per-wholesale-role" ),
            error_fields;

        $this.attr( 'disabled' , 'disabled' );
        $fieldset.find( '#cancel-edit-mapping' ).attr( 'disabled' , 'disabled' );
        $button_controls.addClass( 'processing' );

        error_fields = validate_fields( wholesale_role , minimum_order_quantity , minimum_order_subtotal );

        if ( error_fields.length > 0 ) {

            var msg = wwpp_order_requirement_per_wholesale_role_var.empty_fields_txt + "<br/><ul>";

            for ( var i = 0 ; i < error_fields.length ; i++ )
                msg += "<li>" + error_fields[ i ] + "</li>";

            msg += "</ul>";

            toastr.error( msg , wwpp_order_requirement_per_wholesale_role_var.form_error_txt , { "closeButton" : true , "showDuration" : errorMessageDuration } );

            $this.removeAttr( 'disabled' );
            $fieldset.find( '#cancel-edit-mapping' ).removeAttr( 'disabled' );
            $button_controls.removeClass( 'processing' );

            return false;

        }

        var mapping = {
                        'wholesale_role'            :   wholesale_role,
                        'minimum_order_quantity'    :   minimum_order_quantity,
                        'minimum_order_subtotal'    :   minimum_order_subtotal,
                        'minimum_order_logic'       :   minimum_order_logic
                    };

        wwppBackendAjaxServices.edit_wholesale_role_order_requirement( mapping )
            .done( function ( data , textStatus , jqXHR ) {

                if ( data.status == 'success' ) {

                    $mapping_table.find( "tr.edited" )
                        .find( ".meta" )
                            .find( ".wholesale-role" ).text( mapping.wholesale_role ).end()
                            .end()
                        .find( ".wholesale_role_name" ).text( wholesale_role_txt ).end()
                        .find( ".minimum_order_quantity" ).text( mapping.minimum_order_quantity ).end()
                        .find( ".minimum_order_subtotal" ).text( mapping.minimum_order_subtotal ).end()
                        .find( ".minimum_order_logic" ).text( mapping.minimum_order_logic );

                    $mapping_table.find( "tr .controls .dashicons" )
                        .css( "display" , "inline-block" );

                    reset_fields();

                    // Remove edited class to the recently added user field
                    setTimeout( function () {
                        $mapping_table
                            .find( "tr.edited" )
                            .removeClass( "edited" );
                    } , 500 );

                    $button_controls
                        .removeClass( "edit-mode" )
                        .addClass( "add-mode" );

                    toastr.success( '' , wwpp_order_requirement_per_wholesale_role_var.success_edit_mapping_txt , { "closeButton" : true , "showDuration" : success_message_duration } );

                } else {

                    toastr.error( data.error_message , wwpp_order_requirement_per_wholesale_role_var.failed_edit_mapping_txt , { "closeButton" : true , "showDuration" : error_message_duration } );

                    console.log( wwpp_order_requirement_per_wholesale_role_var.failed_edit_mapping_txt );
                    console.log( data );
                    console.log( '----------' );

                }

            } )
            .fail( function ( jqXHR , textStatus , errorThrown ) {

                toastr.error( jqXHR.responseText , wwpp_order_requirement_per_wholesale_role_var.failed_edit_mapping_txt , { "closeButton" : true , "showDuration" : error_message_duration } );

                console.log( wwpp_order_requirement_per_wholesale_role_var.failed_edit_mapping_txt );
                console.log( data );
                console.log( '----------' );

            } )
            .always( function () {

                $this.removeAttr( 'disabled' );
                $fieldset.find( '#cancel-edit-mapping' ).removeAttr( 'disabled' );
                $button_controls.removeClass( 'processing' );

            } );

    } );

    $fieldset.delegate( '#cancel-edit-mapping' , 'click' , function() {

        var $mapping_table = $( "#order-requirement-per-wholesale-role" ),
            $button_controls = $( ".order-requirement-button-controls" );

        reset_fields();

        $button_controls
            .removeClass( "edit-mode" )
            .addClass( "add-mode" );

        $mapping_table
            .find( "tbody tr" )
            .removeClass( "edited" )
            .find( ".controls .dashicons" )
            .css( "display" , "inline-block" );

    } );

    $( "body" ).delegate( "#order-requirement-per-wholesale-role .edit" , "click" , function () {

        var $this = $( this ),
            $current_tr = $this.closest( 'tr' ),
            $mapping_table = $( "#order-requirement-per-wholesale-role" ),
            $button_controls = $( ".order-requirement-button-controls" );

        $current_tr.addClass( "edited" );
        $mapping_table.find( ".controls .dashicons" ).css( "display" , "none" );

        var curr_mapping = {
            "wholesale_role"            :   $.trim( $current_tr.find( ".meta" ).find( ".wholesale_role" ).text() ),
            "minimum_order_quantity"    :   $.trim( $current_tr.find( ".minimum_order_quantity" ).text() ),
            "minimum_order_subtotal"    :   $.trim( $current_tr.find( ".minimum_order_subtotal" ).text() ),
            "minimum_order_logic"       :   $.trim( $current_tr.find( ".minimum_order_logic" ).text() )
        };

        $( "#wholesale-roles" ).val( curr_mapping.wholesale_role ).attr( 'disabled' , 'disabled' ).trigger( "change" ).trigger( "chosen:updated" );
        $( "#minimum-order-quantity" ).val( curr_mapping.minimum_order_quantity );
        $( "#minimum-order-subtotal" ).val( curr_mapping.minimum_order_subtotal );
        $( "#minimum-order-logic" ).val( curr_mapping.minimum_order_logic );

        $button_controls
            .removeClass( "add-mode" )
            .addClass( "edit-mode" );

    } );

    $( "body" ).delegate( "#order-requirement-per-wholesale-role .delete" , "click" , function() {

        var $this = $( this ),
            $current_tr = $this.closest( 'tr' ),
            $mapping_table = $( "#order-requirement-per-wholesale-role" );

        $current_tr.addClass( "edited" );

        if ( confirm( wwpp_order_requirement_per_wholesale_role_var.delete_mapping_prompt_txt ) ) {

            var wholesale_role = $.trim( $current_tr.find( ".meta" ).find( ".wholesale_role" ).text() );

            $mapping_table.find( ".controls .dashicons" )
                .css( "display" , "none" );

            wwppBackendAjaxServices.delete_wholesale_role_order_requirement( wholesale_role )
                .done( function ( data , textStatus , jqXHR ) {

                    if ( data.status == 'success' ) {

                        $current_tr.fadeOut( "fast" , function () {

                            $current_tr.remove();

                            reset_table_row_styling();

                            // If no more item then append the empty table placeholder
                            if ( $mapping_table.find( "tbody" ).find( "tr" ).length <= 0 ) {

                                $mapping_table
                                    .find( "tbody" )
                                    .html(  '<tr class="no-items">' +
                                            '<td class="colspanchange" colspan="6">' + wwpp_order_requirement_per_wholesale_role_var.no_mapping_txt + '</td>' +
                                            '</tr>' );

                            }

                        } );

                        toastr.success( '' , wwpp_order_requirement_per_wholesale_role_var.success_delete_mapping_txt , { "closeButton" : true , "showDuration" : success_message_duration } );

                    } else {

                        toastr.error( data.error_message , wwpp_order_requirement_per_wholesale_role_var.failed_delete_mapping_txt , { "closeButton" : true , "showDuration" : error_message_duration } );

                        console.log( wwpp_order_requirement_per_wholesale_role_var.failed_delete_mapping_txt );
                        console.log( data );
                        console.log( '----------' );

                    }

                } )
                .fail( function ( jqXHR , textStatus , errorThrown ) {

                    toastr.error( jqXHR.responseText , wwpp_order_requirement_per_wholesale_role_var.failed_delete_mapping_txt , { "closeButton" : true , "showDuration" : error_message_duration } );

                    console.log( wwpp_order_requirement_per_wholesale_role_var.failed_delete_mapping_txt );
                    console.log( jqXHR );
                    console.log( '----------' );

                } )
                .always( function () {

                    $mapping_table.find( ".controls .dashicons" )
                        .css( "display" , "inline-block" );

                } );

        } else {

            $current_tr.removeClass( "edited" );

        }

    } );




    /*
     |------------------------------------------------------------------------------------------------------------------
     | Page Load
     |------------------------------------------------------------------------------------------------------------------
     */

    render_wholesale_role_requirement_controls();

    $override_chkbox.trigger( 'change' );

    $( "#order-requirement-per-wholesale-role-controls #wholesale-roles" ).chosen( { allow_single_deselect : true , width : '300px' } );
    $( "#order-requirement-per-wholesale-role-controls #wholesale-roles" ).trigger( "change" ).trigger( "chosen:updated" );

} );
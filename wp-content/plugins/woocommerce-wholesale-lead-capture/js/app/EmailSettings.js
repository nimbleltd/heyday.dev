jQuery( document ).ready( function ( $ ) {

    var options = {
        plugins: [ 'restore_on_backspace' , 'remove_button' , 'drag_drop' ],
        delimiter: ',',
        persist: false,
        create: function( input ) {
            return {
                value: input,
                text: input
            }
        }
    }

    $( "#wwlc_emails_main_recipient" ).selectize( options );
    $( "#wwlc_emails_cc" ).selectize( options );
    $( "#wwlc_emails_bcc" ).selectize( options );

    // Show/Hide Custom Fields Template Tags
    var customField = $(".wwlc_custom_field"),
        showTags    = $(".wwlc_custom_field_template_tags");

    $(showTags).hide();

    $(customField).click(function(e){
        e.preventDefault();

        if( $(this).hasClass( "close" ) ){
            $(this).removeClass( "close" );
            $(this).addClass( "open" );
            var text = $(this).find("b").text();
            text= text.replace("+ Show", "- Hide");
            $(this).find("b").text(text);

        }else{
            $(this).removeClass( "open" );
            $(this).addClass( "close" );
            var text = $(this).find("b").text();
            console.log(text);
            text= text.replace("- Hide", "+ Show");
            $(this).find("b").text(text);
        }

        $(this).siblings(showTags).slideToggle("fast");

    });
} );
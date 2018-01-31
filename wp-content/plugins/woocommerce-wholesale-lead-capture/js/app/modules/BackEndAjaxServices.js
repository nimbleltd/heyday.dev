/**
 * A function implementing the revealing module pattern to house all ajax request. It implements the ajax promise methodology
 * @return {Ajax Promise} promise it returns a promise, I promise that #lamejoke
 *
 * Info:
 * ajaxurl points to admin ajax url for ajax call purposes. Added by wp when script is wp enqueued
 */
var wwlcBackEndAjaxServices = function(){

    var approveUser = function( userID, page ){

            return jQuery.ajax({
                url         :   ajaxurl,
                type        :   "POST",
                data        :   { action : "wwlc_approve_user" , userID : userID, page : page },
                dataType    :   "json"
            });

        },
        rejectUser = function( userID, page ){

            return jQuery.ajax({
                url         :   ajaxurl,
                type        :   "POST",
                data        :   { action : "wwlc_reject_user" , userID : userID, page : page },
                dataType    :   "json"
            });

        },
        activateUser = function( userID, page ){

            return jQuery.ajax({
                url         :   ajaxurl,
                type        :   "POST",
                data        :   { action : "wwlc_activate_user" , userID : userID, page : page },
                dataType    :   "json"
            });

        },
        deactivateUser = function( userID, page ){

            return jQuery.ajax({
                url         :   ajaxurl,
                type        :   "POST",
                data        :   { action : "wwlc_deactivate_user" , userID : userID, page : page },
                dataType    :   "json"
            });

        },
        createLeadPages = function(){

            return jQuery.ajax({
                url         :   ajaxurl,
                type        :   "POST",
                data        :   { action : "wwlc_create_lead_pages" },
                dataType    :   "json"
            });

        },
        saveWWLCLicenseDetails = function( licenseDetails ) {

            return jQuery.ajax({
                url         :   ajaxurl,
                type        :   "POST",
                data        :   { action : "wwlc_save_license_details" , licenseDetails : licenseDetails },
                dataType    :   "json"
            });

        },
        addRegistrationFormCustomField = function( customField ) {

            return jQuery.ajax({
                url         :   ajaxurl,
                type        :   "POST",
                data        :   { action : "wwlc_add_registration_form_custom_field" , customField : customField },
                dataType    :   "json"
            });

        },
        editRegistrationFormCustomField = function( customField ) {

            return jQuery.ajax({
                url         :   ajaxurl,
                type        :   "POST",
                data        :   { action : "wwlc_edit_registration_form_custom_field" , customField : customField },
                dataType    :   "json"
            });

        },
        deleteRegistrationFormCustomField = function( field_id ) {

            return jQuery.ajax({
                url         :   ajaxurl,
                type        :   "POST",
                data        :   { action : "wwlc_delete_registration_form_custom_field" , field_id : field_id },
                dataType    :   "json"
            });

        },
        getRegistrationFormCustomFieldByID = function ( field_id ) {

            return jQuery.ajax({
                url         :   ajaxurl,
                type        :   "POST",
                data        :   { action : "wwlc_get_custom_field_by_id" , field_id : field_id },
                dataType    :   "json"
            });

        },
        getStates = function( cc ){

            return jQuery.ajax({
                url         :   ajaxurl,
                type        :   "POST",
                data        :   { action : "wwlc_get_states" , cc : cc },
                dataType    :   "json"
            });

        },
        getContentFieldEditor = function( content ) {

            return jQuery.ajax({
                url         : ajaxurl,
                type        : "POST",
                data        : { action : "wwlc_content_wp_editor" , content : content },
                dataType    : 'text'
            });
        };

    return {
        getStates                           :   getStates,
        approveUser                         :   approveUser,
        rejectUser                          :   rejectUser,
        activateUser                        :   activateUser,
        deactivateUser                      :   deactivateUser,
        createLeadPages                     :   createLeadPages,
        saveWWLCLicenseDetails              :   saveWWLCLicenseDetails,
        addRegistrationFormCustomField      :   addRegistrationFormCustomField,
        editRegistrationFormCustomField     :   editRegistrationFormCustomField,
        deleteRegistrationFormCustomField   :   deleteRegistrationFormCustomField,
        getRegistrationFormCustomFieldByID  :   getRegistrationFormCustomFieldByID,
        getContentFieldEditor               :   getContentFieldEditor

    }

}();

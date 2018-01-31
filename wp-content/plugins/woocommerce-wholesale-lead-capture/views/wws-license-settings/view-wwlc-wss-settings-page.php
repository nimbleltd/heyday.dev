<?php
if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$wwlc_license_email = get_option( WWLC_OPTION_LICENSE_EMAIL );
$wwlc_license_key = get_option( WWLC_OPTION_LICENSE_KEY );
?>
<div id="wws_settings_wwlc" class="wws_license_settings_page_container">

    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="wws_wwlc_license_email"><?php _e( 'License Email' , 'woocommerce-wholesale-lead-capture' ); ?></label>
            </th>
            <td class="forminp forminp-text">
                <input type="text" id="wws_wwlc_license_email" class="regular-text ltr" value="<?php echo $wwlc_license_email; ?>"/>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="wws_wwlc_license_key"><?php _e( 'License Key' , 'woocommerce-wholesale-lead-capture' ); ?></label>
            </th>
            <td class="forminp forminp-text">
                <input type="text" id="wws_wwlc_license_key" class="regular-text ltr" value="<?php echo $wwlc_license_key; ?>"/>
            </td>
        </tr>
        </tbody>
    </table>

    <p class="submit">
        <input type="button" id="wws_save_btn" class="button button-primary" value="<?php _e( 'Save Changes' , 'woocommerce-wholesale-lead-capture' ); ?>"/>
        <span class="spinner"></span>
    </p>

</div><!--#wws_settings_wwlc-->
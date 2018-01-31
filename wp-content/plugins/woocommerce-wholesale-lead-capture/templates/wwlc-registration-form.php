<?php
/**
 * The template for displaying registration form
 *
 * Override this template by copying it to yourtheme/woocommerce/wwlc-registration-form.php
 *
 * @author 		Rymera Web Co
 * @package 	WooCommerceWholeSaleLeadCapture/Templates
 * @version     1.7.0
 */

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// NOTE: Don't Remove any ID or Classes inside this template when overriding it.
// Some JS Files Depend on it. You are free to add ID and Classes without any problem.
?>

<div id="wwlc-registration-form" data-redirect="<?php echo esc_attr( $redirect ); ?>"><?php

	$formProcessor->wwlc_initialize_registration_form();

	foreach ( $formFields as $field )
		$formProcessor->wwlc_form_field( $field );
?>
	<div class="field-set form-controls-section">
		<?php echo $formProcessor->wwlc_get_form_controls(); ?>
	</div>

	<?php $formProcessor->wwlc_end_registration_form( $role ); ?>

</div><!--#wwlc-registration-form-->

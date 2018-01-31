<?php
/**
 * The template for displaying registration form
 *
 * Override this template by copying it to yourtheme/woocommerce/wwlc-login-form.php
 *
 * @author 		Rymera Web Co
 * @package 	WooCommerceWholeSaleLeadCapture/Templates
 * @version     1.7.0
 */

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// NOTE: Don't Remove any ID or Classes inside this template when overriding it.
// Some JS Files Depend on it. You are free to add ID and Classes without any problem.

?>
<div id="wwlc-login-form">

	<?php do_action( 'wwlc_before_login_form', $args ); ?>

	<form name="<?php echo esc_attr( $args[ 'form_id' ] ); ?>"
		  id="<?php echo esc_attr( $args[ 'form_id' ] ); ?>"
		  action="<?php echo esc_attr( $args[ 'form_action' ] ); ?>"
		  method="<?php echo esc_attr( $args[ 'form_method' ] ); ?>">

		<p class="login-username">
			<label for="<?php echo esc_attr( $args[ 'id_username' ] ); ?>">
				<?php echo esc_html( $args[ 'label_username' ] ); ?>
				<span style="color:red">*</span>
			</label>
			<input type="text" name="wwlc_username" id="<?php echo esc_attr( $args[ 'id_username' ] ); ?>" class="input" value="<?php echo esc_attr( $args[ 'value_username' ] ); ?>" size="20" />
		</p>

		<p class="login-password">
			<label for="<?php echo esc_attr( $args[ 'id_password' ] ); ?>"><?php echo esc_html( $args[ 'label_password' ] ); ?> <span style="color:red">*</span></label>
			<input type="password" name="wwlc_password" id="<?php echo esc_attr( $args[ 'id_password' ] ); ?>" class="input" value="" size="20" />
		</p>

		<?php if ( $args[ 'remember' ] ) : ?>
			<p class="login-remember">
				<label>
					<input name="rememberme" type="checkbox" id="<?php echo esc_attr( $args[ 'id_remember' ] ); ?>" value="forever"<?php checked( $args[ 'value_remember' ] , true ); ?> />
					<?php echo esc_html( $args[ 'label_remember' ] ); ?>
				</label>
			</p>
		<?php endif; ?>

		<p class="login-submit">
			<input type="submit" name="wp-submit" id="<?php echo esc_attr( $args[ 'id_submit' ] ); ?>" class="button-primary" value="<?php echo esc_attr( $args[ 'label_log_in' ] ); ?>" />
			<input type="hidden" name="redirect_to" value="<?php echo esc_url( $args[ 'redirect' ] ); ?>" />
		</p>

		<?php wp_nonce_field( 'wwlc_login_form', 'wwlc_login_form_nonce_field' ); ?>
	</form>

	<?php do_action( 'wwlc_after_login_form', $args ); ?>

	<a class="register_link" href="<?php echo $formProcessor->get_wwlc_page_url( 'wwlc_general_registration_page' ); ?>" ><?php _e( 'Register' , 'woocommerce-wholesale-lead-capture' ); ?></a>
	<a class="lost_password_link" href="<?php echo wp_lostpassword_url(); ?>" ><?php _e( 'Lost Password' , 'woocommerce-wholesale-lead-capture' ); ?></a>

</div><!--#wwlc-login-form-->

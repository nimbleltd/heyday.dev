<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( isset( $_GET[ 'tab' ] ) )
    $tab = $_GET[ 'tab' ];
else
    $tab = WWS_LICENSE_SETTINGS_DEFAULT_PLUGIN; ?>

<h2 class="nav-tab-wrapper">
    <b style="display: block; float: none; margin-bottom: 15px;"><?php _e( 'WWS License Settings' , 'woocommerce-wholesale-prices-premium' ); ?></b>
    <?php do_action( 'wws_action_license_settings_tab' ); ?>
</h2>

<?php do_action( 'wws_action_license_settings_' . $tab ); ?>
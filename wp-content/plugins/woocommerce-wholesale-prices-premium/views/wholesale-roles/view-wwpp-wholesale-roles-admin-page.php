<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$wholesale_only_purchases_i18n = array(
    'no'  => __( 'No' , 'woocommerce-wholesale-prices-premium' ),
    'yes' => __( 'Yes' , 'woocommerce-wholesale-prices-premium' ),
); ?>

<div id='wwpp-wholesale-roles-page' class='wwpp-page wrap nosubsub'>
    <h2><?php _e( 'Wholesale Roles' , 'woocommerce-wholesale-prices-premium' ); ?></h2>

    <div id="col-container">

        <div id="col-right">

            <div class="col-wrap">

                <div>
                    <div class="tablenav top">

                        <div class="alignleft actions bulkactions">
                            <label for="bulk-action-selector-top" class="screen-reader-text"><?php _e( 'Select bulk action' , 'woocommerce-wholesale-prices-premium' ); ?></label>

                            <select name="action" id="bulk-action-selector-top">
                                <option value="-1" selected="selected"><?php _e( 'Bulk Actions' , 'woocommerce-wholesale-prices-premium' ); ?></option>
                                <option value="delete"><?php _e( 'Delete' , 'woocommerce-wholesale-prices-premium' ); ?></option>
                            </select>

                            <input class="button action" value="<?php _e( 'Apply' , 'woocommerce-wholesale-prices-premium' ); ?>" type="submit">
                        </div>

                        <div class="tablenav-pages">
                            <span class="displaying-num"><?php echo $wholesale_roles_total_text; ?></span>
                        </div>

                        <br class="clear">
                    </div>

                    <table class="wp-list-table widefat fixed tags">

                        <thead>
                            <tr>
                                <th scope="col" id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1"><?php _e( 'Select All' , 'woocommerce-wholesale-prices-premium' ); ?></label><input id="cb-select-all-1" type="checkbox"></th>
                                <th scope="col" id="role-name" class="manage-column column-role-name"><span><?php _e( 'Name' , 'woocommerce-wholesale-prices-premium' ); ?></span></th>
                                <th scope="col" id="role-key" class="manage-column column-role-key"><span><?php _e( 'Key' , 'woocommerce-wholesale-prices-premium' ); ?></span></th>
                                <th scope="col" id="only-allow-wholesale-purchases" class="manage-column column-only-allow-wholesale-purchases"><span><?php _e( 'Wholesale Purchases Only' , 'woocommerce-wholesale-prices-premium' ); ?></span></th>
                                <th scope="col" id="role-desc" class="manage-column column-role-desc"><span><?php _e( 'Description' , 'woocommerce-wholesale-prices-premium' ); ?></span></th>
                            </tr>
                        </thead>

                        <tbody id="the-list">
                        <?php
                        $count = 0;
                        foreach ( $all_registered_wholesale_roles as $role_key => $role ) {
                            $count++;
                            $alternate = '';

                            if ( $count % 2 != 0 )
                                $alternate = 'alternate';?>
                            
                            <tr class="<?php echo $alternate; ?>">
                                <th class="check-column" scope="row">
                                    <input type="checkbox">
                                </th>

                                <td class="role-name column-role-name">

                                    <?php if ( array_key_exists( 'main' , $role ) && $role[ 'main' ] ) { ?>

                                        <strong><a class="main-role-name"><?php echo $role[ 'roleName' ]; ?></a></strong>

                                        <div class="row-actions">
                                            <span class="edit"><a class="edit-role" href="#"><?php _e( 'Edit' , 'woocommerce-wholesale-prices-premium' ); ?></a>
                                        </div>

                                    <?php } else { ?>

                                        <strong><a><?php echo $role[ 'roleName' ]; ?></a></strong><br>

                                        <div class="row-actions">
                                            <span class="edit"><a class="edit-role" href="#"><?php _e( 'Edit' , 'woocommerce-wholesale-prices-premium' ); ?></a> | </span>
                                            <span class="delete"><a class="delete-role" href="#"><?php _e( 'Delete' , 'woocommerce-wholesale-prices-premium' ); ?></a></span>
                                        </div>

                                    <?php } ?>

                                </td>

                                <td class="role-key column-role-key"><?php echo $role_key; ?></td>

                                <td class="only-allow-wholesale-purchases column-only-allow-wholesale-purchases" data-attr-raw-data="<?php echo isset( $role[ 'onlyAllowWholesalePurchases' ] ) ? $role[ 'onlyAllowWholesalePurchases' ] : 'no'; ?>"><?php echo isset( $role[ 'onlyAllowWholesalePurchases' ] ) ? $wholesale_only_purchases_i18n[ $role[ 'onlyAllowWholesalePurchases' ] ] : $wholesale_only_purchases_i18n[ 'no' ]; ?></td>

                                <td class="role-desc column-role-desc"><?php echo $role[ 'desc' ]; ?></td>

                            </tr>
                        <?php } ?>
                        </tbody>

                        <tfoot>
                            <tr>
                                <th scope="col" id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1"><?php _e( 'Select All' , 'woocommerce-wholesale-prices-premium' ); ?></label><input id="cb-select-all-1" type="checkbox"></th>
                                <th scope="col" id="role-name" class="manage-column column-role-name"><span><?php _e( 'Name' , 'woocommerce-wholesale-prices-premium' ); ?></span></th>
                                <th scope="col" id="role-key" class="manage-column column-role-key"><span><?php _e( 'Key' , 'woocommerce-wholesale-prices-premium' ); ?></span></th>
                                <th scope="col" id="only-allow-wholesale-purchases" class="manage-column column-only-allow-wholesale-purchases"><span><?php _e( 'Wholesale Purchases Only' , 'woocommerce-wholesale-prices-premium' ); ?></span></th>                                
                                <th scope="col" id="role-desc" class="manage-column column-role-desc"><span><?php _e( 'Description' , 'woocommerce-wholesale-prices-premium' ); ?></span></th>
                            </tr>
                        </tfoot>

                    </table>

                    <div class="tablenav bottom">

                        <div class="alignleft actions bulkactions">
                            <label for="bulk-action-selector-bottom" class="screen-reader-text"><?php _e( 'Select bulk action' , 'woocommerce-wholesale-prices-premium' ); ?></label>

                            <select name="action2" id="bulk-action-selector-bottom">
                                <option value="-1" selected="selected"><?php _e( 'Bulk Actions' , 'woocommerce-wholesale-prices-premium' ); ?></option>
                                <option value="delete"><?php _e( 'Delete' , 'woocommerce-wholesale-prices-premium' ); ?></option>
                            </select>

                            <input class="button action" value="<?php _e( 'Apply' , 'woocommerce-wholesale-prices-premium' ); ?>" type="submit">
                        </div>

                        <div class="tablenav-pages">
                            <span class="displaying-num"><?php echo $wholesale_roles_total_text; ?></span>
                        </div>

                        <br class="clear">
                    </div>

                    <br class="clear">
                </div>

                <div class="form-wrap">
                    <p>
                        <strong><?php _e( 'Note:' , 'woocommerce-wholesale-prices-premium' ); ?></strong><br/>
                        <?php _e( 'When deleting a wholesale role, all users attached with that role will have the default wholesale role (Wholesale Customer) as their wholesale role.' , 'woocommerce-wholesale-prices-premium' ); ?>
                    </p>
                    <p>
                        <?php _e( "Wholesale Roles are just a copy of WooCommerce's Customer Role with an additional custom capability of 'have_wholesale_price'." , 'woocommerce-wholesale-prices-premium' ); ?>
                    </p>
                </div>

            </div><!--.col-wrap-->

        </div><!--#col-right-->

        <div id="col-left">

            <div class="col-wrap">

                <div class="form-wrap">
                    <h3><?php _e( 'Add New Wholesale Role' , 'woocommerce-wholesale-prices-premium' ); ?></h3>

                    <div id="wholesale-form">

                        <div class="form-field form-required">
                            <label for="role-name"><?php _e( 'Role Name' , 'woocommerce-wholesale-prices-premium' ); ?></label>
                            <input id="role-name" value="" size="40" type="text">
                            <p><?php _e( 'Required. Recommended to be unique.' , 'woocommerce-wholesale-prices-premium' ); ?></p>
                        </div>

                        <div class="form-field form-required">
                            <label for="role-key"><?php _e( 'Role Key' , 'woocommerce-wholesale-prices-premium' ); ?></label>
                            <input id="role-key" value="" size="40" type="text">
                            <p><?php _e( 'Required. Must be unique. Must only contain letters, numbers and underscores' , 'woocommerce-wholesale-prices-premium' ); ?></p>
                        </div>

                        <div class="form-field form-required">
                            <label for="role-desc"><?php _e( 'Description' , 'woocommerce-wholesale-prices-premium' ); ?></label>
                            <textarea id="role-desc" rows="5" cols="40"></textarea>
                            <p><?php _e( 'Optional.' , 'woocommerce-wholesale-prices-premium' ); ?></p>
                        </div>

                        <h2 style="margin-top: 20px;"><?php _e( 'Role Specific Settings' , 'woocommerce-wholesale-prices-premium' ); ?></h2>

                        <div class="form-field checkbox-field">
                            <input type="checkbox" id="only-allow-wholesale-purchase" autocomplete="off">
                            <label for="only-allow-wholesale-purchase">
                                <?php _e( 'Prevent purchase if wholesale condition is not met' , 'woocommerce-wholesale-prices-premium' ); ?>
                                <span class="dashicons dashicons-editor-help tooltip right" data-tip="<?php _e( 'Prevents customers from checking out if they haven\'t met the minimum requirements to activate wholesale pricing (as per the Minimum Order Requirements setting)' , 'woocommerce-wholesale-prices-premium' ); ?>"></span>
                            </label>
                        </div>

                        <p class="submit add-controls">
                            <input id="add-wholesale-role-submit" class="button button-primary" value="<?php _e( "Add New Wholesale Role" , "woocommerce-wholesale-prices-premium" ); ?>" type="button"><span class="spinner"></span>
                        </p>

                        <p class="submit edit-controls">
                            <input id="edit-wholesale-role-submit" class="button button-primary" value="<?php _e( "Edit Wholesale Role" , "woocommerce-wholesale-prices-premium" ); ?>" type="button"><span class="spinner"></span>
                            <input id="cancel-edit-wholesale-role-submit" class="button button-secondary" value="<?php _e( "Cancel Edit" , "woocommerce-wholesale-prices-premium" ); ?>" type="button"/>
                        </p>

                    </div>
                </div>

            </div><!--.col-wrap-->

        </div><!--#col-left-->

    </div><!--#col-container-->

</div><!--#wwpp-wholesale-roles-page-->
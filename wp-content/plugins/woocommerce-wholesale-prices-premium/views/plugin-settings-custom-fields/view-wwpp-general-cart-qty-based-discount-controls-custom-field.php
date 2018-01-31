<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<div class="field-controls">

    <input type="hidden" id="mapping-index" class="field-control" value="">

    <div class="field-container wrcqbwd-wholesale-roles-field-container">

        <label for="wrcqbwd-wholesale-roles"><?php _e( 'Wholesale Role' , 'woocommerce-wholesale-prices-premium' ); ?></label>
        <select id="wrcqbwd-wholesale-roles" class="field-control" data-placeholder="<?php _e( 'Choose wholesale role...' , 'woocommerce-wholesale-prices-premium' ); ?>" style="width: 400px;">
            <option value=""></option>
            <?php foreach ( $all_wholesale_roles as $wholesaleRoleKey => $wholesaleRole ) { ?>
                <option value="<?php echo $wholesaleRoleKey ?>"><?php echo $wholesaleRole[ 'roleName' ]; ?></option>
            <?php } ?>
        </select>
        <span class="dashicons dashicons-editor-help tooltip right" data-tip="<?php _e( 'Select wholesale role to which rule applies.' , 'woocommerce-wholesale-prices-premium' ); ?>"></span>

    </div>
    
    <div class="field-container">
                
        <label for="wrcqbwd-starting-qty"><?php _e( 'Starting Qty' , 'woocommerce-wholesale-prices-premium' ) ?></label>
        <input type="number" id="wrcqbwd-starting-qty" class="field-control">
        <span class="dashicons dashicons-editor-help tooltip right" data-tip="<?php _e( 'Minimum order quantity required for this rule. Must be a number.' , 'woocommerce-wholesale-prices-premium' ); ?>"></span>                                

    </div>

    <div class="field-container">
                
        <label for="wrcqbwd-ending-qty"><?php _e( 'Ending Qty' , 'woocommerce-wholesale-prices-premium' ); ?></label>
        <input type="number" id="wrcqbwd-ending-qty" class="field-control">
        <span class="dashicons dashicons-editor-help tooltip right" data-tip="<?php _e( 'Maximum order quantity required for this rule. Must be a number. Leave this blank for no maximum quantity.' , 'woocommerce-wholesale-prices-premium' ); ?>"></span>
        
    </div>

    <div class="field-container">

        <label for="wrcqbwd-percent-discount"><?php _e( 'Percent Discount' , 'woocommerce-wholesale-prices-premium' ); ?></label>
        <input type="number" min="0" step="1" id="wrcqbwd-percent-discount" class="field-control"/>
        <span class="dashicons dashicons-editor-help tooltip right" data-tip="<?php _e( 'The new % value off the regular price. This will be the discount value used for quantities within the given range.' , 'woocommerce-wholesale-prices-premium' ); ?>"></span>        
        <p class="desc"> <?php _e( 'New percentage amount off the retail price. Provide value in percent (%), Ex. 3 percent then input 3, 30 percent then input 30, 0.3 percent then input 0.3.' , 'woocommerce-wholesale-prices-premium' ); ?></p>

    </div>

    <div style="clear: both; float: none; display: block;"></div>

</div>

<div class="button-controls add-mode">

    <input type="button" id="wrcqbwd-save-mapping" class="button button-primary" value="<?php _e( 'Save Mapping' , 'woocommerce-wholesale-prices-premium' ); ?>"/>
    <input type="button" id="wrcqbwd-cancel-edit-mapping" class="button button-secondary" value="<?php _e( 'Cancel' , 'woocommerce-wholesale-prices-premium' ); ?>"/>
    <input type="button" id="wrcqbwd-add-mapping" class="button button-primary" value="<?php _e( 'Add Mapping' , 'woocommerce-wholesale-prices-premium' ); ?>"/>
    <span class="spinner"></span>

    <div style="clear: both; float: none; display: block;"></div>

</div>

<table id="wholesale-role-cart-qty-based-wholesale-discount-mapping" class="wp-list-table widefat fixed striped posts">
    <thead>
        <tr>
            <th><?php _e( 'Wholesale Role' , 'woocommerce-wholesale-prices-premium' ); ?></th>
            <th><?php _e( 'Starting Qty' , 'woocommerce-wholesale-prices-premium' ); ?></th>
            <th><?php _e( 'Ending Qty' , 'woocommerce-wholesale-prices-premium' ); ?></th>
            <th><?php _e( 'Percent Discount' , 'woocommerce-wholesale-prices-premium' ); ?></th>
            <th></th>
        </tr>
    </thead>

    <tfoot>
        <tr>
            <th><?php _e( 'Wholesale Role' , 'woocommerce-wholesale-prices-premium' ); ?></th>
            <th><?php _e( 'Starting Qty' , 'woocommerce-wholesale-prices-premium' ); ?></th>
            <th><?php _e( 'Ending Qty' , 'woocommerce-wholesale-prices-premium' ); ?></th>
            <th><?php _e( 'Percent Discount' , 'woocommerce-wholesale-prices-premium' ); ?></th>
            <th></th>
        </tr>
    </tfoot>

    <tbody>
    
        <?php if ( $cart_qty_discount_mapping ) {

            foreach( $cart_qty_discount_mapping as $index => $mapping ) { ?>

                <tr data-index="<?php echo $index; ?>">

                    <td class="wholesale_role"><?php echo $mapping[ 'wholesale_role' ]; ?></td>
                    <td class="start_qty"><?php echo $mapping[ 'start_qty' ]; ?></td>
                    <td class="end_qty"><?php echo $mapping[ 'end_qty' ]; ?></td>
                    <td class="percent_discount"><?php echo $mapping[ 'percent_discount' ] . '%'; ?></td>
                    <td class="controls">
                        <a class="edit dashicons dashicons-edit"></a>
                        <a class="delete dashicons dashicons-no"></a>
                    </td>

                </tr>

            <?php }

        } else { ?>
            <tr class="no-items">
                <td class="colspanchange" colspan="5"><?php _e( 'No Mappings Found' , 'woocommerce-wholesale-prices-premium' ); ?></td>
            </tr>
        <?php } ?>
    
    </tbody>

</table>


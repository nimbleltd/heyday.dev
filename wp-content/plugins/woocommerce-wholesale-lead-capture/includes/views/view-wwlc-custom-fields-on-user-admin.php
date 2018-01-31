<h3><?php _e( 'Extra profile information ( Wholesale Lead )' , 'woocommerce-wholesale-lead-capture' ); ?></h3>

<table class="form-table">

	<?php
  // Note: Password fields are special, we don't need to include them here. If they want to edit there passwords
  // then they can just do the wp way of changing password. Adding it here also exposes some security issues.
	foreach ( $registration_form_fields as $field ) {

		if ( !$field[ 'custom_field' ] || in_array( $field[ 'type' ] , array( 'password' , 'content' , 'terms_conditions' ) ) )
			continue;

        $disabledNotice = '';
        if ( !$field[ 'active' ] )
            $disabledNotice = '<span><b><i>' . __( ' ( Disabled )' , 'woocommerce-wholesale-lead-capture' ) . '</i></b></span>'; ?>

        <tr>
			<th><label for="<?php echo $field[ 'id' ]; ?>"><?php echo $field[ 'label' ] . $disabledNotice; ?></label></th>

			<?php if ( $field[ 'type' ] == 'text' || $field[ 'type' ] == 'email' || $field[ 'type' ] == 'url' ) { ?>

                <td>
					<input type="<?php echo $field[ 'type' ]; ?>" name="<?php echo $field[ 'name' ]; ?>" id="<?php echo $field[ 'id' ]; ?>" value="<?php echo esc_attr( get_user_meta( $user->ID , $field[ 'id' ] , true ) ); ?>" class="regular-text" /><br />
					<span class="description"><?php echo sprintf( __( 'Please enter your %1$s.' , 'woocommerce-wholesale-lead-capture' ) , $field[ 'label' ] ); ?></span>
				</td>

                <?php if( !empty( $field[ 'sub_fields' ] ) ){ ?>
                    </tr><?php
                        $countSubFields = $field[ 'sub_fields' ];
                        $i = 0;
                        foreach ( $field[ 'sub_fields' ] as $field ) {

                            $disabledNotice = '';
                            if ( !$field[ 'active' ] )
                                $disabledNotice = '<span><b><i>' . __( ' ( Disabled )' , 'woocommerce-wholesale-lead-capture' ) . '</i></b></span>'; ?>

                            <tr>
                                <th><label for="<?php echo $field[ 'id' ]; ?>"><?php echo $field[ 'label' ] . $disabledNotice; ?></label></th>
                                <td>
                                    <input type="<?php echo $field[ 'type' ]; ?>" name="<?php echo $field[ 'name' ]; ?>" id="<?php echo $field[ 'id' ]; ?>" value="<?php echo esc_attr( get_user_meta( $user->ID , $field[ 'id' ] , true ) ); ?>" class="regular-text" /><br />
                                    <span class="description"><?php echo sprintf( __( 'Please enter your %1$s.' , 'woocommerce-wholesale-lead-capture' ) , $field[ 'label' ] ); ?></span>
                                </td><?php
                                echo $i < $countSubFields ? "</tr>" : "";
                        } ?>
                <?php } ?>

			<?php } elseif ( $field[ 'type' ] == 'textarea' ) { ?>

                <td>
					<textarea name="<?php echo $field[ 'name' ]; ?>" id="<?php echo $field[ 'id' ]; ?>" cols="30" rows="5"><?php echo esc_attr( get_user_meta( $user->ID , $field[ 'id' ] , true ) ); ?></textarea>
                    <span class="description" style="display: block;"><?php echo sprintf( __( 'Please enter your %1$s.' , 'woocommerce-wholesale-lead-capture' ) , $field[ 'label' ] ); ?></span>
                </td>

			<?php } elseif ( $field[ 'type' ] == 'number' ) { ?>

                <td>
                    <input type="<?php echo $field[ 'type' ]; ?>" min="<?php echo $field[ 'attributes' ][ 'min' ] ?>" max="<?php echo $field[ 'attributes' ][ 'max' ] ?>" step="<?php echo $field[ 'attributes' ][ 'step' ] ?>" name="<?php echo $field[ 'name' ]; ?>" id="<?php echo $field[ 'id' ]; ?>" value="<?php echo esc_attr( get_user_meta( $user->ID , $field[ 'id' ] , true ) ); ?>" class="regular-text" /><br />
                    <span class="description"><?php echo sprintf( __( 'Please enter your %1$s.' , 'woocommerce-wholesale-lead-capture' ) , $field[ 'label' ] ); ?></span>
                </td>

            <?php } elseif ( $field[ 'type' ] == 'select' ) {

                $userMeta = esc_attr( get_user_meta( $user->ID , $field[ 'id' ] , true ) ); ?>

                <td>
                    <select name="<?php echo $field[ 'name' ]; ?>" id="<?php echo $field[ 'id' ]; ?>">
                        <?php foreach ( $field['options' ] as $option ) { ?>

                            <option value="<?php echo $option[ 'value' ] ?>" <?php echo ( $userMeta == $option[ 'value' ] ? 'selected' : '' ); ?> ><?php echo $option[ 'text' ] ?></option>

                        <?php } ?>
                    </select><br />
                    <span class="description"><?php echo sprintf( __( 'Please enter your %1$s.' , 'woocommerce-wholesale-lead-capture' ) , $field[ 'label' ] ); ?></span>
                </td>

            <?php } elseif ( $field[ 'type' ] == 'radio' ) {

                $userMeta = esc_attr( get_user_meta( $user->ID , $field[ 'id' ] , true ) ); ?>

                <td>
                <?php foreach ($field['options' ] as $option ) { ?>
                    <div style="margin-bottom: 6px;"><input style="display: inline-block; margin-right: 6px;" type="radio" name="<?php echo $field[ 'name' ]; ?>" value="<?php echo $option[ 'value' ]; ?>" <?php echo ( $userMeta == $option[ 'value' ] ? 'checked' : '' ); ?> /><span style="display: inline-block;"><?php echo $option[ 'text' ]; ?></span></div>
                <?php } ?>

                    <br />
                    <span class="description"><?php echo sprintf( __( 'Please enter your %1$s.' , 'woocommerce-wholesale-lead-capture' ) , $field[ 'label' ] ); ?></span>
                </td>

            <?php } elseif ( $field[ 'type' ] == 'checkbox' ) {

                $userMeta = get_user_meta( $user->ID , $field[ 'id' ] , true );
                if ( !is_array( $userMeta ) )
                    $userMeta = array(); ?>

                <td>
                    <?php foreach ($field['options' ] as $option ) { ?>
                        <div style="margin-bottom: 6px;"><input style="display: inline-block; margin-right: 6px;" type="checkbox" name="<?php echo $field[ 'name' ]; ?>[]" value="<?php echo $option[ 'value' ]; ?>" <?php echo in_array( $option[ 'value' ] , $userMeta ) ? "checked" : ""; ?> /><span style="display: inline-block;"><?php echo $option[ 'text' ]; ?></span></div>
                    <?php } ?>

                    <br />
                    <span class="description"><?php echo sprintf( __( 'Please enter your %1$s.' , 'woocommerce-wholesale-lead-capture' ) , $field[ 'label' ] ); ?></span>
                </td>

			<?php } elseif ( $field[ 'type' ] == 'phone' ) { ?>

				<td>
					<input type="<?php echo $field[ 'type' ]; ?>" name="<?php echo $field[ 'name' ]; ?>" id="<?php echo $field[ 'id' ]; ?>" value="<?php echo esc_attr( get_user_meta( $user->ID, $field['name'], true ) ); ?>" class="regular-text phone" /><br />
					<span class="description"><?php echo sprintf( __( 'Please enter your %1$s.' , 'woocommerce-wholesale-lead-capture' ) , $field[ 'label' ] ); ?></span>
				</td>

			<?php } elseif ( $field[ 'type' ] == 'hidden' ) { ?>

				<td>
					<span><?php echo esc_attr( get_user_meta( $user->ID, $field['name'], true ) ); ?></span>
				</td>

			<?php } elseif ( $field[ 'type' ] == 'file' ) {

				$file_url = esc_url( get_user_meta( $user->ID, $field['id'], true ) );
				$file_tmp = explode( '/', $file_url );
				$file_name = end( $file_tmp );
				?>
				<td>
					<a href="<?php echo $file_url; ?>"><?php echo $file_name; ?></a>
				</td>
            <?php } ?>

        </tr>

	<?php } ?>

</table>

<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWLC_Forms' ) ) {

	class WWLC_Forms {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

		/**
         * Property that holds the single main instance of WWLC_Forms.
         *
         * @since 1.6.3
         * @access private
         * @var WWLC_Forms
         */
		private static $_instance;

		/*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        /**
         * WWLC_Forms constructor.
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWLC_Forms model.
         *
         * @access public
         * @since 1.6.3
         */
		public function __construct( $dependencies ) {}

        /**
         * Ensure that only one instance of WWLC_Forms is loaded or can be loaded (Singleton Pattern).
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWLC_Forms model.
         *
         * @return WWLC_Forms
         * @since 1.6.3
         */
        public static function instance( $dependencies = null ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }

        /**
         * Return formatted custom fields. ( Abide to the formatting of existing fields ).
         *
         * @return array
         *
         * @since 1.1.0
         * @since 1.6.3 Insert default value in the array.
         * 				Made the function access method from private to public since we need this function called in WWLC_Shortcode class
         */
        public function _get_formatted_custom_fields () {

            $registration_form_custom_fields = unserialize( base64_decode( get_option( WWLC_OPTION_REGISTRATION_FORM_CUSTOM_FIELDS ) ) );
            if ( !is_array( $registration_form_custom_fields ) )
                $registration_form_custom_fields = array();

            $formatted_registration_form_custom_fields = array();

            foreach ( $registration_form_custom_fields as $field_id => $custom_field ) {

                $formatted_registration_form_custom_fields[] = array(
                    'label'         =>  $custom_field[ 'field_name' ],
                    'name'          =>  $field_id,
                    'id'            =>  $field_id,
                    'class'         =>  "wwlc_registration_field form_field wwlc_custom_field " . $custom_field[ 'field_type' ] . "_wwlc_custom_field",
                    'type'          =>  $custom_field[ 'field_type' ],
                    'required'      =>  ( $custom_field[ 'required' ] == '1' ) ? true : false,
                    'custom_field'  =>  true,
                    'active'        =>  ( $custom_field[ 'enabled' ] == '1' ) ? true : false,
                    'validation'    =>  array(),
                    'field_order'   =>  $custom_field[ 'field_order' ],
                    'attributes'    =>  isset( $custom_field[ 'attributes' ] ) ? $custom_field[ 'attributes' ] : array(),
                    'options'       =>  isset( $custom_field[ 'options' ] ) ? $custom_field[ 'options' ] : array(),
                    'placeholder'	=> 	isset( $custom_field[ 'field_placeholder' ] ) ? $custom_field[ 'field_placeholder' ] : "",
                    'default_value'	=> 	isset( $custom_field[ 'default_value' ] ) ? $custom_field[ 'default_value' ] : "",
                );

            }

            return $formatted_registration_form_custom_fields;

        }

		/**
		 * Retrives all file custom fields registed on the settings.
		 * Note: this can be converted to retrieve any type of custom fields.
		 *
		 * @return array
		 *
		 * @since 1.6.0
		 */
		public function wwlc_get_file_custom_fields() {

			$custom_fields = $this->_get_formatted_custom_fields();

			if ( ! is_array( $custom_fields ) )
				return;

			$file_fields = array();

			foreach ( $custom_fields as $key => $field ) {

				if ( $field[ 'type' ] == 'file' )
					$file_fields[] = $field;
			}

			return $file_fields;
		}

		/**
		 * Returns the recaptcha field.
		 *
		 * @since 1.6.0
		 * @since 1.6.3 Made the function access method from private to public since we need this function called in WWLC_Shortcode class
		 */
		public function _get_recaptcha_field() {

			if ( get_option('wwlc_security_enable_recaptcha') != 'yes' )
				return array();

			$recaptcha_field = array(
				'type'			=> 'recaptcha',
				'id'			=> 'recaptcha_field',
				'active'		=> true,
				'required'		=> true,
				'field_order'	=> 99999999
			);

			return array( $recaptcha_field );
		}

        /**
         * Usort callback for sorting associative arrays.
         * Used for sorting field ordering on the form. (Registration form).
         *
         * @param $arr1
         * @param $arr2
         * @return int
         *
         * @since 1.1.0
         */
        public function _usort_callback ( $arr1 , $arr2 ) {

            if ( $arr1[ 'field_order' ] == $arr2[ 'field_order' ] )
                return 0;

            return ( $arr1[ 'field_order' ] < $arr2[ 'field_order' ] ) ? -1 : 1;

        }

		/**
		 * Get field label markup.
		 *
		 * @deprecated 1.7.0
		 *
		 * @param $field
		 *
		 * @return string
		 * @since 1.0.0
		 */
		public function wwlc_get_label( $field ) {

			wwlc_deprecated_function( debug_backtrace() , 'WWLC_Forms::wwlc_get_label' , '1.7.0' , 'woocommerce_form_field' );
		}

		/**
		 * Get field markup.
		 *
		 * @deprecated 1.7.0
		 *
		 * @param $field
		 *
		 * @return string
		 * @since 1.0.0
		 */
		public function wwlc_get_field( $field ) {

			wwlc_deprecated_function( debug_backtrace() , 'WWLC_Forms::wwlc_get_field' , '1.7.0' , 'woocommerce_form_field' );

			ob_start();
			$this->wwlc_form_field( $field );
			return ob_get_clean();
		}

		/**
		 * Display WWLC form field.
		 *
		 * @deprecated 1.7.0
		 *
		 * @param $field
		 *
		 * @return string
		 * @since 1.0.0
		 */
		public function wwlc_form_field( $field ) {

			if ( ! $this->wwlc_is_field_active( $field ) )
				return;

			switch ( $field[ 'type' ] ) {

				case 'content' :
					echo '<div id="' . $field['id'] . '" class="content-field-wrap">';
					echo wp_kses( $field[ 'default_value' ] , wp_kses_allowed_html( 'post' ) );
					echo '</div>';
					break;

				case 'terms_conditions' :
					$show_text           = __( 'show' , 'woocommerce-wholesale-lead-capture' );
					$hide_text           = __( 'hide' , 'woocommerce-wholesale-lead-capture' );
					$field[ 'label' ]   .= ' (<a class="show-hide" href="javascript:void(0);" data-hide=' . $hide_text . '>' . $show_text . '</a>)';
					$field[ 'required' ] = true;
					$field[ 'label_class' ] = array( 'checkbox_options_holder' , 'terms_conditions_checkbox' );

					woocommerce_form_field( $field['id'] , $this->wwlc_reformat_field_args( $field ) );

					echo '<div id="' . $field['id'] . '-content" class="terms-conditions-wrap" style="display:none;">';
					echo wp_kses( $field[ 'default_value' ] , wp_kses_allowed_html( 'post' ) );
					echo '</div>';
					break;

				default :
					woocommerce_form_field( $field['id'] , $this->wwlc_reformat_field_args( $field ) );
					break;
			}
		}

		/**
		 * Get registration form controls.
		 *
		 * @return string
		 * @since 1.0.0
		 */
		public function wwlc_get_form_controls() {

			$terms_and_condition = '';
			if ( get_option( 'wwlc_general_show_terms_and_conditions' ) == 'yes' ) {

				$terms_and_condition_page_url = $this->get_wwlc_page_url( 'wwlc_general_terms_and_condition_page_url' );
				$terms_and_condition = '<p class="terms-and-condition-container">' . sprintf( __( 'By clicking register, you agree to the <a href="%1$s" target="_blank">Terms & Conditions</a>' , 'woocommerce-wholesale-lead-capture' ) , $terms_and_condition_page_url ) . '</p>';

			}

			$register = '<p class="register-button-container"><input type="submit" class="form-control button button-primary" id="wwlc-register" value="' . __( 'Register' , 'woocommerce-wholesale-lead-capture' ) . '" ><span class="wwlc-loader"></span></p>';
			$register = apply_filters( 'wwlc_filter_registration_form_register_control' , $register );

			$log_in = '<a class="form-control" id="log-in" href="' . $this->get_wwlc_page_url( 'wwlc_general_login_page' ) . '" >' . __( 'Log In' , 'woocommerce-wholesale-lead-capture' ) . '</a>';
			$log_in = apply_filters( 'wwlc_filter_registration_form_login_control' , $log_in );

			$lost_password = '<a class="form-control" id="lost-password" href="' . wp_lostpassword_url() . '" >' . __( 'Lost Password' , 'woocommerce-wholesale-lead-capture' ) . '</a>';
			$lost_password = apply_filters( 'wwlc_filter_registration_form_lost_password_control' , $lost_password );

			return $terms_and_condition . $register . $log_in . ' ' . $lost_password;

		}

		/**
		 * Check if field is active.
		 *
		 * @param $field
		 *
		 * @return mixed
		 * @since 1.0.0
		 */
		public function wwlc_is_field_active( $field ) {

			return $field[ 'active' ];

		}

		/**
		 * Do registration form initialization. Adding nonces and honey pot.
		 *
		 * @since 1.0.0
		 */
		public function wwlc_initialize_registration_form() {

			echo '<div class="woocommerce wwlc-form-error"></div>';

			echo '<form method="post" class="wwlc-register">';

			// echo nonce fields
			wp_nonce_field( 'wwlc_register_user', 'wwlc_register_user_nonce_field' );

			// echo honeypot fields
			$honey_pot_fields = '<div style="display: none !important;" class="honeypot">'.
			                    '<label for="honeypot-field">' . __( 'Please Leave This Empty:' , 'woocommerce-wholesale-lead-capture' ) . '</label>'.
			                    '<input type="text" id="honeypot-field" name="honeypot-field" val="">'.
			                  '</div>';

			echo $honey_pot_fields;

		}

		/**
		 * End the  registration form. Adding necesarry html endings and special fields.
		 *
		 * @param string $role Registration form set user role.
		 *
		 * @since 1.7.0
		 */
		public function wwlc_end_registration_form( $role ) {

			if ( $role )
				echo '<input class="input-hidden wwlc_form_field" type="hidden" name="wwlc_role" id="wwlc_role" value="' . esc_attr( $role ) . '">';

			echo '</form>';
		}

		/**
		 * Create lead pages. Necessary pages for the plugin to work correctly.
		 *
		 * @param null $dummy_arg
		 *
		 * @return bool
		 * @since 1.0.0
		 */
		public function wwlc_create_lead_pages( $dummy_arg = null ) {

			$registration_page_creation_status = $this->wwlc_create_registration_page();
			$log_in_page_creation_status = $this->wwlc_create_log_in_page();
			$thank_you_page_creation_status = $this->wwlc_create_thank_you_page();

			if ( $registration_page_creation_status && $log_in_page_creation_status && $thank_you_page_creation_status ) {

				if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

					header( 'Content-Type: application/json' ); // specify we return json
					echo json_encode( array(
						'status'    =>  'success'
					) );
					die();

				} else
					return true;

			} else {

				if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

					header( 'Content-Type: application/json' ); // specify we return json
					echo json_encode( array(
						'status'            =>  'failed',
						'error_message'     =>  __( 'Failed to create some or all wholesale lead pages' , 'woocommerce-wholesale-lead-capture' ),
						'registration_page' =>  $registration_page_creation_status,
						'login_page'        =>  $log_in_page_creation_status
					) );
					die();

				} else
					return false;

			}

		}

		/**
		 * Create registration page.
		 *
		 * @return bool
		 * @since 1.0.0
		 */
		public function wwlc_create_registration_page() {

			if ( get_post_status( get_option( WWLC_OPTIONS_REGISTRATION_PAGE_ID ) ) !== 'publish' && !get_page_by_title( 'Wholesale Registration Page' ) ) {

				$wholesale_page = array(
										'post_content'  =>  '[wwlc_registration_form]',// The full text of the post.
										'post_title'    =>  __( 'Wholesale Registration Page' , 'woocommerce-wholesale-lead-capture' ),// The title of your post.
										'post_status'   =>  'publish',
										'post_type'     =>  'page'
									);

				$result = wp_insert_post( $wholesale_page );

				if ( $result === 0 || is_wp_error( $result ) ) {

					return false;

				} else {

					update_option( WWLC_OPTIONS_REGISTRATION_PAGE_ID , $result );
					return true;

				}

			} else
				return true;

		}

		/**
		 * Create log in page.
		 *
		 * @return bool
		 * @since 1.0.0
		 */
		public function wwlc_create_log_in_page() {

			if ( get_post_status( get_option( WWLC_OPTIONS_LOGIN_PAGE_ID ) ) !== 'publish' && !get_page_by_title( 'Wholesale Log In Page' ) ) {

				$wholesale_page = array(
					'post_content'  =>  '[wwlc_login_form]',// The full text of the post.
					'post_title'    =>  __( 'Wholesale Log In Page' , 'woocommerce-wholesale-lead-capture' ),// The title of your post.
					'post_status'   =>  'publish',
					'post_type'     =>  'page'
				);

				$result = wp_insert_post( $wholesale_page );

				if ( $result === 0 || is_wp_error( $result ) ) {

					return false;

				} else {

					update_option( WWLC_OPTIONS_LOGIN_PAGE_ID , $result );
					return true;

				}

			} else
				return true;

		}


		/**
		 * Create Thank You page.
		 *
		 * @return bool
		 * @since 1.4.0
		 */
		public function wwlc_create_thank_you_page() {

			if ( get_post_status( get_option( WWLC_OPTIONS_THANK_YOU_PAGE_ID ) ) !== 'publish' && !get_page_by_title( 'Wholesale Thank You Page' ) ) {

				$wholesale_page = array(
					'post_content'  =>  'Thank you for your registration. We will be in touch shortly to discuss your account.',// The full text of the post.
					'post_title'    =>  __( 'Wholesale Thank You Page' , 'woocommerce-wholesale-lead-capture' ),// The title of your post.
					'post_status'   =>  'publish',
					'post_type'     =>  'page'
				);

				$result = wp_insert_post( $wholesale_page );

				if ( $result === 0 || is_wp_error( $result ) ) {

					return false;

				} else {

					update_option( WWLC_OPTIONS_THANK_YOU_PAGE_ID , $result );
					return true;

				}

			} else
				return true;

		}

		/*
	     |--------------------------------------------------------------------------------------------------------------
	     | Fields display related functions.
	     |--------------------------------------------------------------------------------------------------------------
	     */

		/**
		 * Reformat WWLC field args to be used for the woocommerce_form_field function.
		 *
		 * @since 1.7.0
		 * @access public
		 *
		 * @param array $field WWLC field data.
		 * @return array Filtered WWLC args to woocommerce_form_field args.
		 */
		public function wwlc_reformat_field_args( $field ) {

			$field_id    = isset( $field[ 'id' ] ) ? $field[ 'id' ] : '';
			$type        = isset( $field[ 'type' ] ) ? $field[ 'type' ] : 'text';
 			$class       = array( 'field-set' , $type . '-field-set' , $field_id );
 			$options     = array();
 			$label_class = isset( $field[ 'label_class' ] ) && is_array( $field[ 'label_class' ] ) ? $field[ 'label_class' ] : array();
 			$input_class = isset( $field[ 'input_class' ] ) && is_array( $field[ 'input_class' ] ) ? $field[ 'input_class' ] : array();
 			$attributes  = isset( $field[ 'custom_attributes' ] ) && is_array( 'custom_attributes' ) ? $field[ 'custom_attributes' ] : array();
 			$validate    = isset( $field[ 'validation' ] ) && is_array( $field[ 'validation' ] ) ? $field[ 'validation' ] : array();

			// add form_field to input class variable.
			$input_class[] = 'wwlc_form_field';

 			// change class string to array.
 			if ( isset( $field[ 'class' ] ) )
 				$class = array_merge( $class , is_array( $field[ 'class' ] ) ? $field[ 'class' ] : explode( ' ' , $field[ 'class' ] ) );

 			// remap options array list.
 			if ( isset( $field[ 'options' ] ) && is_array( $field[ 'options' ] ) ) {

 				foreach ( $field[ 'options' ] as $option  )
 					$options[ $option[ 'value' ] ] = $option[ 'text' ];
 			}

 			// change 'phone' type to 'tel'.
 			if ( $type == 'phone' ) {

 				$type                           = 'tel';
 				$input_class[]                  = 'phone';
 				$attributes[ 'data-phonemask' ] = get_option( 'wwlc_fields_phone_mask_pattern', 'No format' );

			} elseif ( $type == 'checkbox' )
				$type = 'wwlc_checkbox';
			elseif ( $type == 'terms_conditions' )
				$type = 'checkbox';
			elseif ( $type == 'number' ) {

				if ( isset( $field[ 'attributes' ][ 'min' ] ) && $field[ 'attributes' ][ 'min' ] )
					$attributes[ 'min' ] = intval( $field[ 'attributes' ][ 'min' ] );

				if ( isset( $field[ 'attributes' ][ 'max' ] ) && $field[ 'attributes' ][ 'max' ] )
					$attributes[ 'max' ] = intval( $field[ 'attributes' ][ 'max' ] );

				if ( isset( $field[ 'attributes' ][ 'step' ] ) && $field[ 'attributes' ][ 'step' ] )
					$attributes[ 'step' ] = intval( $field[ 'attributes' ][ 'step' ] );
			}

 			// add 'data-required' custom attribute.
 			if ( isset( $field[ 'required' ] ) && $field[ 'required' ] )
 				$attributes[ 'data-required' ] = 'yes';

 			return array(
 				'type'              => $type,
 				'label'             => isset( $field[ 'label' ] ) ? $field[ 'label' ] : '',
 	            'description'       => isset( $field[ 'description' ] ) ? $field[ 'description' ] : '',
 	            'placeholder'       => isset( $field[ 'placeholder' ] ) ? $field[ 'placeholder' ] : '',
 	            'maxlength'         => isset( $field[ 'maxlength' ] ) ? $field[ 'maxlength' ] : false,
 	            'required'          => isset( $field[ 'required' ] ) ? $field[ 'required' ] : false,
 	            'autocomplete'      => isset( $field[ 'autocomplete' ] ) ? $field[ 'autocomplete' ] : false,
 	            'id'                => $field_id,
 	            'class'             => $class,
 	            'label_class'       => $label_class,
 	            'input_class'       => $input_class,
 	            'return'            => isset( $field[ 'return' ] ) ? $field[ 'return' ] : false,
 	            'options'           => $options,
 	            'custom_attributes' => $attributes,
 	            'validate'          => $validate,
 	            'default'           => isset( $field[ 'default_value' ] ) ? $field[ 'default_value' ] : '',
 	            'autofocus'         => isset( $field[ 'autofocus' ] ) ? $field[ 'autofocus' ] : '',
 	            'priority'          => isset( $field[ 'priority' ] ) ? $field[ 'priority' ] : '',
 			);
 		}

		/**
		 * Add ability to toggle label for second address line field.
		 *
		 * @since 1.7.0
		 * @access public
		 *
		 * @param string $field Field html (blank).
		 * @param string $key   Field ID.
		 * @param array  $args  Field arguments.
		 * @param string $value Field value.
		 * @return string Recaptcha field html.
		 */
		public function wwlc_address_field_2( $field , $key , $args , $value ) {

			if ( $key == 'wwlc_address_2' && get_option( 'wwlc_fields_enable_address2_label' ) !== 'yes' ) {

				$field  = strip_tags( $field , '<p><input>' );
				$field  = str_replace( $args[ 'label' ] , '' , $field );
			}

			return $field;
		}

		/**
		 * Add support for WWLC url field by displaying it via woocommerce_form_field.
		 *
		 * @since 1.7.0
		 * @access public
		 *
		 * @param string $field Field html (blank).
		 * @param string $key   Field ID.
		 * @param array  $args  Field arguments.
		 * @param string $value Field value.
		 * @return string Recaptcha field html.
		 */
		public function wwlc_url_field_type( $field , $key , $args , $value ) {

			$required        = $args[ 'required' ] ? ' <abbr class="required" title="' . esc_attr__( 'required', 'woocommerce-wholesale-lead-capture' ) . '">*</abbr>' : '';
			$attributes      = $this->get_field_custom_attributes_markup( $args );
			$sort            = $args[ 'priority' ] ? $args[ 'priority' ] : '';
			$field_container = '<p class="form-row %1$s" id="%2$s" data-sort="' . esc_attr( $sort ) . '">%3$s</p>';
			$container_class = esc_attr( implode( ' ', $args[ 'class' ] ) );
			$field_html      = '<label for="' . esc_attr( $args[ 'id' ] ) . '" class="' . esc_attr( implode( ' ', $args[ 'label_class' ] ) ) . '">' . $args[ 'label' ] . $required . '</label>';
			$field_html     .= '<input type="url" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"  value="' . esc_attr( $value ) . '" ' . implode( ' ', $attributes ) . ' />';

			return sprintf( $field_container, $container_class, $key , $field_html );
		}

		/**
		 * Add support for hidden field by displaying it via woocommerce_form_field.
		 *
		 * @since 1.7.0
		 * @access public
		 *
		 * @param string $field Field html (blank).
		 * @param string $key   Field ID.
		 * @param array  $args  Field arguments.
		 * @param string $value Field value.
		 * @return string Recaptcha field html.
		 */
		public function wwlc_hidden_field_type( $field , $key , $args , $value ) {

			$attributes = $this->get_field_custom_attributes_markup( $args );
			$field_html = '<input type="hidden" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" value="' . esc_attr( $args['placeholder'] ) . '" ' . implode( ' ', $attributes ) . ' />';

			return $field_html;
		}

		/**
		 * Add support for recaptcha field by displaying it via woocommerce_form_field.
		 *
		 * @since 1.7.0
		 * @access public
		 *
		 * @param string $field Field html (blank).
		 * @param string $key   Field ID.
		 * @param array  $args  Field arguments.
		 * @param string $value Field value.
		 * @return string Recaptcha field html.
		 */
		public function wwlc_recaptcha_field_type( $field , $key , $args , $value ) {

			$recaptcha_sitekey = get_option( 'wwlc_security_recaptcha_site_key' );
			$sort              = $args[ 'priority' ] ? $args[ 'priority' ] : '';
			$field_container   = '<p class="form-row %1$s" id="%2$s" data-sort="' . esc_attr( $sort ) . '">%3$s</p>';
			$container_class   = esc_attr( implode( ' ', $args[ 'class' ] ) );
			$field_html        = '<span class="g-recaptcha" data-sitekey="' . $recaptcha_sitekey . '"></span>';

			return sprintf( $field_container, $container_class, $key , $field_html );
		}

		/**
		 * Add support for file upload field by displaying it via woocommerce_form_field.
		 *
		 * @since 1.7.0
		 * @access public
		 *
		 * @param string $field Field html (blank).
		 * @param string $key   Field ID.
		 * @param array  $args  Field arguments.
		 * @param string $value Field value.
		 * @return string File upload field html.
		 */
		public function wwlc_file_field_type( $field , $key , $args , $value ) {

			$required        = $args[ 'required' ] ? ' <abbr class="required" title="' . esc_attr__( 'required', 'woocommerce-wholesale-lead-capture' ) . '">*</abbr>' : '';
			$attributes      = $this->get_field_custom_attributes_markup( $args );
			$sort            = $args[ 'priority' ] ? $args[ 'priority' ] : '';
			$field_container = '<p class="form-row %1$s" id="%2$s" data-sort="' . esc_attr( $sort ) . '">%3$s</p>';
			$container_class = esc_attr( implode( ' ', $args[ 'class' ] ) );
			$field_html      = '<label for="' . esc_attr( $args[ 'id' ] ) . '" class="' . esc_attr( implode( ' ', $args[ 'label_class' ] ) ) . '">' . $args[ 'label' ] . $required . '</label>';
			$field_html     .= '<span class="wwlc-file-upload-form">';
			$field_html     .= '<input name="file" type="' . $args[ 'type' ] . '">';
			$field_html     .= '<span class="wwlc-loader"></span>';
			$field_html     .= '<span class="placeholder" style="display:none;"></span>';
			$field_html     .= '<input type="hidden" class="input-text ' . esc_attr( implode( ' ' , $args[ 'input_class' ] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args[ 'id' ] ) . '" placeholder="' . esc_attr( $args[ 'placeholder' ] ) . '"  value="' . esc_attr( $value ) . '" ' . implode( ' ', $attributes ) . ' />';
			$field_html     .= '</span>';

			return sprintf( $field_container, $container_class, $key , $field_html );
		}

		/**
		 * Add support for wwlc_password field.
		 *
		 * @since 1.7.0
		 * @access public
		 *
		 * @param string $field Field html (blank).
		 * @param string $key   Field ID.
		 * @param array  $args  Field arguments.
		 * @param string $value Field value.
		 * @return string File upload field html.
		 */
		public function wwlc_password_field_type( $field , $key , $args , $value ) {

			$required        = $args[ 'required' ] ? ' <abbr class="required" title="' . esc_attr__( 'required', 'woocommerce-wholesale-lead-capture' ) . '">*</abbr>' : '';
			$attributes      = $this->get_field_custom_attributes_markup( $args );
			$sort            = $args[ 'priority' ] ? $args[ 'priority' ] : '';
			$field_container = '<p class="form-row %1$s" id="%2$s" data-sort="' . esc_attr( $sort ) . '">%3$s</p>';
			$container_class = esc_attr( implode( ' ', $args[ 'class' ] ) );
			$field_html      = '<label for="' . esc_attr( $args[ 'id' ] ) . '" class="' . esc_attr( implode( ' ', $args[ 'label_class' ] ) ) . '">' . $args[ 'label' ] . $required . '</label>';
			$field_html     .= '<input type="password" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"  value="' . esc_attr( $value ) . '" ' . implode( ' ', $attributes ) . ' />';
			$field_html     .= '<span id="wwlc-password-strength" class="wwlc-password-strength"></span>';

			if ( get_option( 'wwlc_fields_enable_confirm_password_field' ) == 'yes' ) {

				$confirm_label       = __( 'Confirm password' , 'woocommerce-wholesale-lead-capture' );
				$confirm_placeholder = get_option( 'wwlc_fields_confirm_password_field_placeholder' );
				$field_html         .= '</p><p class="form-row ' . esc_attr( $args[ 'id' ] ) . '_confirm-field-set ' . esc_attr( $args[ 'id' ] ) . '_confirm form_field">';
				$field_html         .= '<label for="' . esc_attr( $args[ 'id' ] ) . '_confirm" class="' . esc_attr( implode( ' ', $args[ 'label_class' ] ) ) . '">' . $confirm_label . '</label>';
				$field_html         .= '<input type="password" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" id="' . esc_attr( $args['id'] ) . '_confirm" placeholder="' . $confirm_placeholder . '">';

			}

			return sprintf( $field_container, $container_class, $key , $field_html );
		}

		/**
		 * Add support for wwlc_checkbox field.
		 *
		 * @since 1.7.0
		 * @access public
		 *
		 * @param string $field Field html (blank).
		 * @param string $key   Field ID.
		 * @param array  $args  Field arguments.
		 * @param string $value Field value.
		 * @return string File upload field html.
		 */
		public function wwlc_checkbox_field_type( $field , $key , $args , $value ) {

			$required        = $args[ 'required' ] ? ' <abbr class="required" title="' . esc_attr__( 'required', 'woocommerce-wholesale-lead-capture' ) . '">*</abbr>' : '';
			$attributes      = $this->get_field_custom_attributes_markup( $args );
			$sort            = $args[ 'priority' ] ? $args[ 'priority' ] : '';
			$field_container = '<p class="form-row %1$s" id="%2$s" data-sort="' . esc_attr( $sort ) . '">%3$s</p>';
			$container_class = esc_attr( implode( ' ', $args[ 'class' ] ) );
			$field_html      = '<label for="' . esc_attr( $args[ 'id' ] ) . '" class="' . esc_attr( implode( ' ' , $args[ 'label_class' ] ) ) . '">' . $args[ 'label' ] . $required . '</label>';
			$field_html     .= '<span class="wwlc_checkboxes_container checkbox_options_holder" ' . implode( ' ' , $attributes ) . '>';

			foreach ( $args[ 'options' ] as $option_value => $checkbox_label ) {
				$field_html .= '<label class="checkbox_options">';
				$field_html .= '<input type="checkbox" class="input-checkbox ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $option_value ) . '" ' . checked( $value, $option_value, false ) . ' />';
				$field_html .= esc_html( $checkbox_label ) . '</label>';
			}

			$field_html .= '</span>';

			$field_html     .= '<input type="checkbox" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"  value="' . esc_attr( $value ) . '" ' . implode( ' ', $attributes ) . ' />';

			return sprintf( $field_container, $container_class, $key , $field_html );
		}

		/**
		 * Modify radio field type.
		 *
		 * @since 1.7.0
		 * @access public
		 *
		 * @param string $field Field html (blank).
		 * @param string $key   Field ID.
		 * @param array  $args  Field arguments.
		 * @param string $value Field value.
		 * @return string File upload field html.
		 */
		public function wwlc_radio_field_type( $field , $key , $args , $value ) {

			$attributes = $this->get_field_custom_attributes_markup( $args );
			$field      = preg_replace( '/<\/label>/' , '</label><span class="radio_options_holder" ' . implode( ' ' , $attributes ) . '>' , $field , 1 );
			$field      = str_replace( '</p>' , '</span></p>' , $field );

			return $field;
		}




		/*
	     |--------------------------------------------------------------------------------------------------------------
	     | Utility Functions
	     |--------------------------------------------------------------------------------------------------------------
	     */

		/**
		 * @param $template String template path
		 * @param $options Array array of options
		 * @param $default_template_path String default template path
		 *
		 * @since 1.0.0
		 * @since 1.6.3 Made the function access method from private to public since we need this function called in WWLC_Shortcode class
		 */
		public function _load_template( $template , $options , $default_template_path ) {

			wc_get_template( $template , $options , '' , $default_template_path );

		}

		/**
		 * Generate .
		 *
		 * @since 1.7.0
		 * @access public
		 *
		 * @param array $args Field arguments.
		 */
		private function get_field_custom_attributes_markup( $args ) {

			$custom_attributes = array();

			if ( ! isset( $args[ 'custom_attributes' ] ) || empty( $args[ 'custom_attributes' ] ) || ! is_array( $args[ 'custom_attributes' ] ) )
				return $custom_attributes;

            foreach ( $args[ 'custom_attributes' ] as $attribute => $attribute_value ) {
                $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
            }

			return $custom_attributes;
		}

		/**
		 * Get the actual permalink of a WWLC page set on the settings.
		 * WWLC-201 : This is needed to properly support WPML
		 *
		 * @since 1.7.0
		 * @access public
		 *
		 * @param string $option_name Option id of the WWLC page.
		 * @param string get_permalink equivalent of a given WWLC page url.
		 */
		public function get_wwlc_page_url( $option_name ) {

			$raw_url = trim( get_option( $option_name ) );
			$page_id = url_to_postid( $raw_url );

			return $page_id ? get_permalink( $page_id ) : $raw_url;
		}




		/*
	     |--------------------------------------------------------------------------------------------------------------
	     | Execute model.
	     |--------------------------------------------------------------------------------------------------------------
	     */

		/**
 	     * Execute model.
 	     *
 	     * @since 1.7.0
 	     * @access public
 	     */
		public function run() {

			add_filter( 'woocommerce_form_field_text' , array( $this , 'wwlc_address_field_2' ) , 10 , 4 );
			add_filter( 'woocommerce_form_field_recaptcha' , array( $this , 'wwlc_recaptcha_field_type' ) , 10 , 4 );
			add_filter( 'woocommerce_form_field_file' , array( $this , 'wwlc_file_field_type' ) , 10 , 4 );
			add_filter( 'woocommerce_form_field_url' , array( $this , 'wwlc_url_field_type' ) , 10 , 4 );
			add_filter( 'woocommerce_form_field_hidden' , array( $this , 'wwlc_hidden_field_type' ) , 10 , 4 );
			add_filter( 'woocommerce_form_field_wwlc_password' , array( $this , 'wwlc_password_field_type' ) , 10 , 4 );
			add_filter( 'woocommerce_form_field_wwlc_checkbox' , array( $this , 'wwlc_checkbox_field_type' ) , 10 , 4 );
			add_filter( 'woocommerce_form_field_radio' , array( $this , 'wwlc_radio_field_type' ) , 10 , 4 );
		}

	}

}

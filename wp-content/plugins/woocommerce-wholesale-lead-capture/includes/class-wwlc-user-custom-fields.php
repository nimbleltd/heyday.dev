<?php
if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWLC_User_Custom_Fields' ) ) {

	class WWLC_User_Custom_Fields {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

		/**
         * Property that holds the single main instance of WWLC_User_Custom_Fields.
         *
         * @since 1.0.0
         * @access private
         * @var WWLC_User_Custom_Fields
         */
		private static $_instance;

		/**
         * Property that holds the single main instance of WWLC_User_Custom_Fields.
         *
         * @since 1.0.0
         * @access private
         * @var WWLC_User_Custom_Fields
         */
		private $_wwlc_user_account;

		/**
         * Property that holds the single main instance of WWLC_User_Custom_Fields.
         *
         * @since 1.0.0
         * @access private
         * @var WWLC_User_Custom_Fields
         */
		private $_wwlc_emails;

		/*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        /**
         * WWLC_User_Custom_Fields constructor.
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWLC_User_Custom_Fields model.
         *
         * @access public
         * @since 1.0.0
         * @since 1.6.3 Code Refactor
         */
		public function __construct( $dependencies ) {

			$this->_wwlc_user_account = $dependencies[ 'WWLC_User_Account' ];
			$this->_wwlc_emails = $dependencies[ 'WWLC_Emails' ];

		}

        /**
         * Ensure that only one instance of WWLC_User_Custom_Fields is loaded or can be loaded (Singleton Pattern).
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWLC_User_Custom_Fields model.
         *
         * @return WWLC_User_Custom_Fields
         * @since 1.0.0
         * @since 1.6.3 Code Refactor : add dependency on new instance
         */
        public static function instance( $dependencies = null ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }

		/**
		 * Add custom row action to user listing page.
		 *
		 * @param $actions
		 * @param $user_object
		 *
		 * @return mixed
		 * @since 1.0.0
		 */
		public function wwlc_add_user_list_custom_row_action_ui( $actions, $user_object ) {

			// Admins and Shop managers can manage wholesale users
			if ( ( current_user_can( 'manage_options' ) || current_user_can( 'manage_woocommerce' ) ) && get_current_user_id() != $user_object->ID ) {

				$user = get_userdata( $user_object->ID );

				if ( in_array( WWLC_UNAPPROVED_ROLE , $user->roles ) ) {

					$actions[ 'wwlc_user_row_action_approve' ] = '<a class="wwlc_approve wwlc_user_row_action" data-userID="' . $user_object->ID . '" href="#">' . __( 'Approve' , 'woocommerce-wholesale-lead-capture' ) .'</a>';
					$actions[ 'wwlc_user_row_action_reject' ] = '<a class="wwlc_reject wwlc_user_row_action" data-userID="' . $user_object->ID . '" href="#">' . __( 'Reject' , 'woocommerce-wholesale-lead-capture' ) .'</a>';

				} elseif ( in_array( WWLC_REJECTED_ROLE , $user->roles ) ) {

					$actions[ 'wwlc_user_row_action_approve' ] = '<a class="wwlc_approve wwlc_user_row_action" data-userID="' . $user_object->ID . '" href="#">' . __( 'Approve' , 'woocommerce-wholesale-lead-capture' ) .'</a>';

				} elseif ( in_array( WWLC_INACTIVE_ROLE , $user->roles ) ) {

					$actions[ 'wwlc_user_row_action_activate' ] = '<a class="wwlc_activate wwlc_user_row_action" data-userID="' . $user_object->ID . '" href="#">' . __( 'Activate' , 'woocommerce-wholesale-lead-capture' ) . '</a>';

				} else {

					$actions[ 'wwlc_user_row_action_deactivate' ] = '<a class="wwlc_deactivate wwlc_user_row_action" data-userID="' . $user_object->ID . '" href="#">' . __( 'Deactivate' , 'woocommerce-wholesale-lead-capture' ) . '</a>';

				}

			}

			return $actions;

		}

		/**
		 * Add custom column to user listing page.
		 *
		 * @param $columns
		 *
		 * @return mixed
		 * @since 1.0.0
		 */
		public function wwlc_add_user_listing_custom_column( $columns ) {

			$array_keys = array_keys( $columns );
			$last_index = $array_keys[ count( $array_keys ) - 1 ];
			$last_value = $columns[ $last_index ];
			array_pop( $columns );

			$columns[ 'wwlc_user_status' ] = __( 'Status' , 'woocommerce-wholesale-lead-capture' );
			$columns[ 'wwlc_registration_date' ] = __( 'Registration Date' , 'woocommerce-wholesale-lead-capture' );
			$columns[ 'wwlc_approval_date' ] = __( 'Approval Date' , 'woocommerce-wholesale-lead-capture' );
			$columns[ 'wwlc_rejection_date' ] = __( 'Rejection Date' , 'woocommerce-wholesale-lead-capture' );

			$columns[ $last_index ] = $last_value;

			return $columns;

		}

		/**
		 * Add content to custom column to user listing page.
		 *
		 * @param $val
		 * @param $column_name
		 * @param $user_id
		 *
		 * @return string
		 * @since 1.0.0
		 */
		public function wwlc_add_user_listing_custom_column_content( $val , $column_name , $user_id ) {

			$user = get_userdata( $user_id );

			if ( $column_name == 'wwlc_user_status' ) {

				if ( in_array( WWLC_UNAPPROVED_ROLE , $user->roles ) )
					return "<span style='width: 80px; text-align: center; color: #fff; background-color: black; display: inline-block; padding: 0 6px;'>" . __( 'Unapproved' , 'woocommerce-wholesale-lead-capture' ) . "</span>";
				elseif ( in_array( WWLC_REJECTED_ROLE , $user->roles ) )
					return "<span style='width: 80px; text-align: center; color: #fff; background-color: orange; display: inline-block; padding: 0 6px;'>" . __( 'Rejected' , 'woocommerce-wholesale-lead-capture' ) . "</span>";
				elseif ( in_array( WWLC_INACTIVE_ROLE , $user->roles ) )
					return "<span style='width: 80px; text-align: center; color: #fff; background-color: grey; display: inline-block; padding: 0 6px;'>" . __( 'Inactive' , 'woocommerce-wholesale-lead-capture' ) . "</span>";
				else
					return "<span style='width: 80px; text-align: center; color: #fff; background-color: green; display: inline-block; padding: 0 6px;'>" . __( 'Active' , 'woocommerce-wholesale-lead-capture' ) . "</span>";

			} elseif ( $column_name == 'wwlc_registration_date' ) {

				return "<span class='wwlc_registration_date' >" . get_date_from_gmt( $user->user_registered , 'Y-m-d H:i:s' ) . "</span>";

			} elseif ( $column_name == 'wwlc_approval_date' ) {

				if ( !in_array( WWLC_UNAPPROVED_ROLE , $user->roles ) && !in_array( WWLC_REJECTED_ROLE , $user->roles ) ) {

					$approval_date = get_user_meta( $user->ID , 'wwlc_approval_date' , true );

					// For older versions of this plugin (prior to 1.3.1) we don't save approval dates.
					// If approval date is not present, we will use the registration date by default.
					if ( !$approval_date )
						$approval_date = $user->user_registered;

					return "<span class='wwlc_approval_date'>" . $approval_date . "</span>";

				}

			} elseif ( $column_name == 'wwlc_rejection_date' ) {

				if ( in_array( WWLC_REJECTED_ROLE , $user->roles ) ) {

					$rejection_date = get_user_meta( $user->ID , 'wwlc_rejection_date' , true );

					return "<span class='wwlc_rejection_date'>" . $rejection_date . "</span>";

				}

			} else return  $val;

		}

		/**
		 * Add custom admin notices on user listing page. WWLC related.
		 *
		 * @since 1.0.0
		 */
		public function wwlc_custom_submissions_bulk_action_notices() {

			global $post_type, $pagenow;

			if ( $pagenow == 'users.php' ) {

				if ( ( isset( $_REQUEST[ 'users_approved' ] ) && (int) $_REQUEST[ 'users_approved' ] ) ||
					( isset( $_REQUEST[ 'users_rejected' ] ) && (int) $_REQUEST[ 'users_rejected' ] ) ||
					( isset( $_REQUEST[ 'users_activated' ] ) && (int) $_REQUEST[ 'users_activated' ] ) ||
					( isset( $_REQUEST[ 'users_deactivated' ] ) && (int) $_REQUEST[ 'users_deactivated' ] ) ) {

					if ( ! empty( $_REQUEST[ 'users_approved' ] ) ) {

						$action = "approved";
						$affected = $_REQUEST[ 'users_approved' ];

					} if ( ! empty( $_REQUEST[ 'users_rejected' ] ) ) {

						$action = "rejected";
						$affected = $_REQUEST[ 'users_rejected' ];

					} if ( ! empty( $_REQUEST[ 'users_activated' ] ) ) {

						$action = "activated";
						$affected = $_REQUEST[ 'users_activated' ];


					} if ( ! empty( $_REQUEST[ 'users_deactivated' ] ) ){

						$action = "deactivated";
						$affected = $_REQUEST[ 'users_deactivated' ];

					}

					$message = sprintf( _n( 'User %2$s.' , '%1$s users %2$s.' , $affected , 'woocommerce-wholesale-lead-capture' ) , number_format_i18n( $affected ) , $action );
					echo "<div class=\"updated\"><p>{$message}</p></div>";

				} elseif (  isset( $_REQUEST[ 'action' ] ) &&  $_REQUEST[ 'action' ] == "wwlc_approve" ||
					isset( $_REQUEST[ 'action' ] ) &&  $_REQUEST[ 'action' ] == "wwlc_reject" ||
					isset( $_REQUEST[ 'action' ] ) &&  $_REQUEST[ 'action' ] == "wwlc_activate" ||
					isset( $_REQUEST[ 'action' ] ) &&  $_REQUEST[ 'action' ] == "wwlc_deactivate" ) {

					if ( isset( $_REQUEST[ 'users' ] ) ) {

						if ( count( $_REQUEST[ 'users' ] ) > 0 ) {

							if ( $_REQUEST[ 'action' ] == "wwlc_approve" )
								$action = "approved";
							if ( $_REQUEST[ 'action' ] == "wwlc_reject" )
								$action = "rejected";
							if ( $_REQUEST[ 'action' ] == "wwlc_activate" )
								$action = "activated";
							if ( $_REQUEST[ 'action' ] == "wwlc_deactivate" )
								$action = "deactivated";

							$message = sprintf( _n( 'User %2$s.' , '%1$s users %2$s.' , count( $_REQUEST[ 'users' ] ) , 'woocommerce-wholesale-lead-capture' ) , number_format_i18n( count( $_REQUEST[ 'users' ] ) ) , $action );
							echo "<div class=\"updated\"><p>{$message}</p></div>";

						}

					}

				}

			}

		}

		/**
		 * Add custom user listing bulk action items on the action select boxes. Done via JS.
		 *
		 * @since 1.0.0
		 */
		public function wwlc_custom_user_listing_bulk_action_footer_js () {

			global $pagenow;

			if ( $pagenow == 'users.php' && ( current_user_can( 'manage_options' ) || current_user_can( 'manage_woocommerce' ) ) ) { ?>

				<script type="text/javascript">

					jQuery( document ).ready( function() {

						jQuery( '<option>' ).val( 'wwlc_approve' ).text( '<?php _e( 'Approve' , 'woocommerce-wholesale-lead-capture' ); ?>' ).appendTo( "select[name='action']" );
						jQuery( '<option>' ).val( 'wwlc_approve' ).text( '<?php _e( 'Approve' , 'woocommerce-wholesale-lead-capture' ); ?>' ).appendTo( "select[name='action2']" );

						jQuery( '<option>' ).val( 'wwlc_reject' ).text( '<?php _e( 'Reject' , 'woocommerce-wholesale-lead-capture' ); ?>' ).appendTo( "select[name='action']" );
						jQuery( '<option>' ).val( 'wwlc_reject' ).text( '<?php _e( 'Reject' , 'woocommerce-wholesale-lead-capture' ); ?>' ).appendTo( "select[name='action2']" );

						jQuery( '<option>' ).val( 'wwlc_activate' ).text( '<?php _e( 'Activate' , 'woocommerce-wholesale-lead-capture' ); ?>' ).appendTo( "select[name='action']" );
						jQuery( '<option>' ).val( 'wwlc_activate' ).text( '<?php _e( 'Activate' , 'woocommerce-wholesale-lead-capture' ); ?>' ).appendTo( "select[name='action2']" );

						jQuery( '<option>' ).val( 'wwlc_deactivate' ).text( '<?php _e( 'Deactivate' , 'woocommerce-wholesale-lead-capture' ); ?>' ).appendTo( "select[name='action']" );
						jQuery( '<option>' ).val( 'wwlc_deactivate' ).text( '<?php _e( 'Deactivate' , 'woocommerce-wholesale-lead-capture' ); ?>' ).appendTo( "select[name='action2'] ");

					});

				</script>

			<?php }

		}

		/**
		 * Add custom user listing bulk action.
		 *
		 * @since 1.3.3
		 */
		public function wwlc_custom_user_listing_bulk_action() {

			global $pagenow;

			if ( $pagenow == 'users.php' && ( current_user_can( 'manage_options' ) || current_user_can( 'manage_woocommerce' ) ) ) {

				// get the current action
				$wp_list_table = _get_list_table( 'WP_Users_List_Table' );  // depending on your resource type this could be WP_Users_List_Table, WP_Comments_List_Table, etc
				$action = $wp_list_table->current_action();

				// set allowed actions, and check if current action is in allowed actions
				$allowed_actions = array( "wwlc_approve" , "wwlc_reject" , "wwlc_activate" , "wwlc_deactivate" );
				if ( !in_array( $action , $allowed_actions ) ) return;

				// security check
				check_admin_referer( 'bulk-users' );

				// make sure ids are submitted.  depending on the resource type, this may be 'media' or 'ids' or 'users'
				if ( isset( $_REQUEST[ 'users' ] ) )
					$user_ids = $_REQUEST[ 'users' ];

				if ( empty( $user_ids ) ) return;

				// this is based on wp-admin/edit.php
				$sendback = remove_query_arg( array( 'wwlc_approve' , 'wwlc_reject' , 'wwlc_activate' , 'wwlc_deactivate' , 'untrashed' , 'deleted' , 'ids' ), wp_get_referer() );
				if ( ! $sendback )
					$sendback = admin_url( 'users.php' );

				$pagenum = $wp_list_table->get_pagenum();
				$sendback = add_query_arg( 'paged', $pagenum, $sendback );

				switch( $action ) {

					case 'wwlc_approve':

						$users_activated = 0;
						foreach( $user_ids as $user_id ) {

							if ( get_current_user_id() != $user_id )
								if ( $this->_wwlc_user_account->wwlc_approve_user( array( 'userID' => $user_id ) , $this->_wwlc_emails ) )
									$users_activated++;

						}

						$sendback = add_query_arg( array( 'users_approved' => $users_activated , 'ids' => join( ',' , $user_ids ) ), $sendback );
						break;

					case 'wwlc_reject':

						$users_rejected = 0;
						foreach( $user_ids as $user_id ) {

							if ( get_current_user_id() != $user_id )
								if ( $this->_wwlc_user_account->wwlc_reject_user( array( 'userID' => $user_id ) , $this->_wwlc_emails ) )
									$users_rejected++;

						}

						$sendback = add_query_arg( array( 'users_rejected' => $users_rejected , 'ids' => join( ',' , $user_ids ) ), $sendback );
						break;

					case 'wwlc_activate':
						// if we set up user permissions/capabilities, the code might look like:
						//if ( !current_user_can($post_type_object->cap->export_post, $post_id) )
						//    wp_die( __('You are not allowed to export this post.') );

						$users_activated = 0;
						foreach( $user_ids as $user_id ) {

							if ( get_current_user_id() != $user_id )
								if ( $this->_wwlc_user_account->wwlc_activate_user( array( 'userID' => $user_id ) ) )
									$users_activated++;

						}

						$sendback = add_query_arg( array( 'users_activated' => $users_activated , 'ids' => join( ',' , $user_ids ) ), $sendback );
						break;

					case 'wwlc_deactivate':

						$users_deactivated = 0;
						foreach( $user_ids as $user_id ) {

							if ( get_current_user_id() != $user_id )
								if ( $this->_wwlc_user_account->wwlc_deactivate_user( array( 'userID' => $user_id ) ) )
									$users_deactivated++;

						}

						$sendback = add_query_arg( array( 'users_deactivated' => $users_deactivated , 'ids' => join( ',' , $user_ids) ), $sendback );
						break;

					default: return;

				}

				$sendback = remove_query_arg( array( 'action' , 'action2' , 'tags_input' , 'post_author' , 'comment_status', 'ping_status' , '_status',  'post', 'bulk_edit', 'post_view'), $sendback );

				wp_redirect( $sendback );
				exit();

			}

		}

		/**
		 * Display custom fields on user admin.
		 *
		 * @param $user
		 *
		 * @since 1.0.0
		 */
		public function wwlc_display_custom_fields_on_user_admin_page( $user ) {

            global $WWLC_REGISTRATION_FIELDS;

            $custom_fields = $this->_get_formatted_custom_fields();

            $registration_form_fields = array_merge( $WWLC_REGISTRATION_FIELDS , $custom_fields );

            usort( $registration_form_fields , array( $this , 'usortCallback' ) );

			require_once ( 'views/view-wwlc-custom-fields-on-user-admin.php' );

		}

        /**
         * Return formatted custom fields. ( Abide to the formatting of existing fields ).
         *
         * @return array
         *
         * @since 1.1.0
         */
        private function _get_formatted_custom_fields() {

            $registration_form_custom_fields = unserialize( base64_decode( get_option( WWLC_OPTION_REGISTRATION_FORM_CUSTOM_FIELDS ) ) );
            if ( !is_array( $registration_form_custom_fields ) )
                $registration_form_custom_fields = array();

            $formatted_registration_form_custom_fields = array();

            foreach ( $registration_form_custom_fields as $field_id => $custom_field ) {

                $formatted_registration_form_custom_fields[] = array(
                    'label'         =>  $custom_field[ 'field_name' ],
                    'name'          =>  $field_id,
                    'id'            =>  $field_id,
                    'class'         =>  'wwlc_registration_field form_field wwlc_custom_field',
                    'type'          =>  $custom_field[ 'field_type' ],
                    'required'      =>  ( $custom_field[ 'required' ] == '1' ) ? true : false,
                    'custom_field'  =>  true,
                    'active'        =>  ( $custom_field[ 'enabled' ] == '1' ) ? true : false,
                    'validation'    =>  array(),
                    'field_order'   =>  $custom_field[ 'field_order' ],
                    'attributes'    =>  isset( $custom_field[ 'attributes' ] ) ? $custom_field[ 'attributes' ] : '',
                    'options'       =>  isset( $custom_field[ 'options' ] ) ? $custom_field[ 'options' ] : ''
                );

            }

            return $formatted_registration_form_custom_fields;

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
        public function usortCallback ( $arr1 , $arr2 ) {

            if ( $arr1[ 'field_order' ] == $arr2[ 'field_order' ] )
                return 0;

            return ( $arr1[ 'field_order' ] < $arr2[ 'field_order' ] ) ? -1 : 1;

        }

		/**
		 * Save custom fields on user admin.
		 *
		 * @param $user_id
		 *
		 * @return bool
		 * @since 1.0.0
		 */
		public function wwlc_save_custom_fields_on_user_admin_page( $user_id ) {

			if ( !current_user_can( 'edit_user', $user_id ) )
				return false;

            global $WWLC_REGISTRATION_FIELDS;

            $custom_fields = $this->_get_formatted_custom_fields();

            $registration_form_fields = array_merge( $WWLC_REGISTRATION_FIELDS , $custom_fields );

            usort( $registration_form_fields , array( $this , 'usortCallback' ) );

			foreach( $registration_form_fields as $field ) {

				if ( ! $field[ 'custom_field' ] )
					continue;

                if ( array_key_exists( $field[ 'id' ] , $_POST ) )
                    update_user_meta( $user_id , $field[ 'id' ] , $_POST[ $field[ 'id' ] ] );
                elseif ( $field[ 'type' ] == 'checkbox' && $field[ 'custom_field' ] )
                    update_user_meta( $user_id , $field[ 'id' ] , array() );

			}

		}

	    /**
	     * Execute model.
	     *
	     * @since 1.6.3
	     * @access public
	     */
	    public function run() {

			// Custom Row Action UI
			add_filter( 'user_row_actions', array( $this , 'wwlc_add_user_list_custom_row_action_ui' ), 10, 2 );

			// Custom Admin Notices Related To WWLC Actions
			add_action( 'admin_notices' , array( $this , 'wwlc_custom_submissions_bulk_action_notices' ) );

			// Add Custom Column To User Listing Page
			add_filter( 'manage_users_columns' , array( $this , 'wwlc_add_user_listing_custom_column' ) );

			// Add Content To Custom Column On User Listing Page
			add_filter( 'manage_users_custom_column' , array( $this , 'wwlc_add_user_listing_custom_column_content' ) , 10 , 3 );

			// Add Custom Bulk Action Options On Actions Select Box. Done Via JS
			add_action( 'admin_footer-users.php' , array( $this , 'wwlc_custom_user_listing_bulk_action_footer_js' ) );

			// Add Custom Bulk Action
			add_action( 'load-users.php' , array( $this , 'wwlc_custom_user_listing_bulk_action' ) );

			// Add Custom Fields To Admin User Edit Page.
			add_action( 'show_user_profile' , array( $this , 'wwlc_display_custom_fields_on_user_admin_page' ) );
			add_action( 'edit_user_profile' , array( $this , 'wwlc_display_custom_fields_on_user_admin_page' ) );

			// Save Custom Fields On Admin User Edit Page.
			add_action( 'personal_options_update' , array( $this , 'wwlc_save_custom_fields_on_user_admin_page' ) );
			add_action( 'edit_user_profile_update' , array( $this , 'wwlc_save_custom_fields_on_user_admin_page' ) );

	    }

	}

}

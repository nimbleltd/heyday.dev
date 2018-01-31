<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_Wholesale_Role_Tax_Option_Mapping' ) ) {

    /**
     * Model that houses the logic of wholesale role tax options mapping.
     *
     * @since 1.14.0
     */
    class WWPP_Wholesale_Role_Tax_Option_Mapping {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWPP_Wholesale_Role_Tax_Option_Mapping.
         *
         * @since 1.14.0
         * @access private
         * @var WWPP_Wholesale_Role_Tax_Option_Mapping
         */
        private static $_instance;
        
        /**
         * Model that houses the logic of retrieving information relating to wholesale role/s of a user.
         *
         * @since 1.14.0
         * @access private
         * @var WWPP_Wholesale_Roles
         */
        private $_wwpp_wholesale_roles;



        
        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        /**
         * WWPP_Wholesale_Role_Tax_Option_Mapping constructor.
         *
         * @since 1.14.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Wholesale_Role_Tax_Option_Mapping model.
         */
        public function __construct( $dependencies ) {

            $this->_wwpp_wholesale_roles = $dependencies[ 'WWPP_Wholesale_Roles' ];

        }

        /**
         * Ensure that only one instance of WWPP_Wholesale_Role_Tax_Option_Mapping is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.14.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Wholesale_Role_Tax_Option_Mapping model.
         * @return WWPP_Wholesale_Role_Tax_Option_Mapping
         */
        public static function instance( $dependencies ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }




        /*
        |---------------------------------------------------------------------------------------------------------------
        | Tax Exemption
        |---------------------------------------------------------------------------------------------------------------
        */

        /**
         * Add an entry to wholesale role / tax option mapping.
         * Design based on trust that the caller will supply an array with the following elements below.
         *
         * wholesale_role
         * tax_exempted
         *
         * @since 1.4.7
         * @since 1.14.0 Refactor codebase and move to its proper model.
         * @access public
         *
         * @param null|array $mapping Mapping entry.
         * @return array Operation status.
         */
        public function wwpp_add_wholesale_role_tax_option( $mapping = null ) {

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
                $mapping = $_POST[ 'mapping' ];

            $tax_option_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_TAX_OPTION_MAPPING , array() );
            if ( !is_array( $tax_option_mapping ) )
                $tax_option_mapping = array();

            if ( array_key_exists( $mapping[ 'wholesale_role' ] , $tax_option_mapping ) ) {

                $response = array(
                    'status'        => 'fail',
                    'error_message' => __( 'Duplicate Wholesale Role Tax Option Entry, Already Exist' , 'woocommerce-wholesale-prices-premium' )
                );

            } else {

                $wholesale_role = $mapping[ 'wholesale_role' ];
                unset( $mapping[ 'wholesale_role' ] );

                $tax_option_mapping[ $wholesale_role ] = $mapping;

                update_option( WWPP_OPTION_WHOLESALE_ROLE_TAX_OPTION_MAPPING , $tax_option_mapping );

                $response = array( 'status' => 'success' );

            }

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

                @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
                echo wp_json_encode( $response );
                wp_die();

            } else
                return $response;

        }

        /**
         * Edit an entry of wholesale role / tax option mapping.
         *
         * Design based on trust that the caller will supply an array with the following elements below.
         *
         * wholesale_role
         * tax_exempted
         *
         * @since 1.4.7
         * @since 1.14.0 Refactor codebase and move to its proper model.
         * @access public
         *
         * @param null|null $mapping Mapping entry.
         * @return array Operation status.
         */
        public function wwpp_edit_wholesale_role_tax_option( $mapping = null ) {

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
                $mapping = $_POST[ 'mapping' ];

            $tax_option_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_TAX_OPTION_MAPPING , array() );
            if ( !is_array( $tax_option_mapping ) )
                $tax_option_mapping = array();

            if ( !array_key_exists( $mapping[ 'wholesale_role' ] , $tax_option_mapping ) ) {

                $response = array(
                    'status'        => 'fail',
                    'error_message' => __( 'Wholesale Role Tax Option Entry You Wish To Edit Does Not Exist' , 'woocommerce-wholesale-prices-premium' )
                );

            } else {

                $wholesale_role = $mapping[ 'wholesale_role' ];
                unset( $mapping[ 'wholesale_role' ] );

                $tax_option_mapping[ $wholesale_role ] = $mapping;

                update_option( WWPP_OPTION_WHOLESALE_ROLE_TAX_OPTION_MAPPING , $tax_option_mapping );

                $response = array( 'status' => 'success' );

            }

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

                @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
                echo wp_json_encode( $response );
                wp_die();

            } else
                return $response;

        }

        /**
         * Delete an entry of wholesale role / tax option mapping.
         *
         * @since 1.4.7
         * @since 1.14.0 Refactor codebase and move to its proper model.
         * @access public
         *
         * @param null|string $wholesale_role Wholeslae role key.
         * @return array Operation status.
         */
        public function wwpp_delete_wholesale_role_tax_option( $wholesale_role = null ) {

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
                $wholesale_role = $_POST[ 'wholesale_role' ];

            $tax_option_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_TAX_OPTION_MAPPING , array() );
            if ( !is_array( $tax_option_mapping ) )
                $tax_option_mapping = array();

            if ( !array_key_exists( $wholesale_role , $tax_option_mapping ) ) {

                $response = array(
                    'status'        => 'fail',
                    'error_message' => __( 'Wholesale Role Tax Option Entry You Wish To Delete Does Not Exist' , 'woocommerce-wholesale-prices-premium' )
                );

            } else {

                unset( $tax_option_mapping[ $wholesale_role ] );

                update_option( WWPP_OPTION_WHOLESALE_ROLE_TAX_OPTION_MAPPING , $tax_option_mapping );

                $response = array( 'status' => 'success' );

            }

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

                @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
                echo wp_json_encode( $response );
                wp_die();

            } else
                return $response;

        }
        



        /*
        |---------------------------------------------------------------------------------------------------------------
        | Tax Classes
        |---------------------------------------------------------------------------------------------------------------
        */

        /**
         * Generate mapping entry markup.
         * 
         * @since 1.16.0
         * @access public
         * 
         * @param string $wholesale_role_key Wholesale role key.
         * @param array  $mapping_entry      Mapping entry.
         * @return string Mapping entry markup.
         */
        private function _generate_mapping_entry_markup( $wholesale_role_key , $mapping_entry ) {

            ?>
            
            <tr>
            
                <td class="meta hidden">
                    <span class="wholesale-role"><?php echo $wholesale_role_key; ?></span>
                    <ul class="tax-class"><?php echo $mapping_entry[ 'tax-class' ]; ?></ul>
                </td>
                <td class="wholesale-role-name"><?php echo $mapping_entry[ 'wholesale-role-name' ]; ?></td>
                <td class="tax-classes-name"><ul><?php echo $mapping_entry[ 'tax-class-name' ]; ?></ul></td>
                <td class="controls">
                    <a class="edit dashicons dashicons-edit"></a>
                    <a class="delete dashicons dashicons-no"></a>
                </td>

            </tr>
            
            <?php return ob_get_clean();

        }

        /**
         * Save tax class mapping entry.
         * 
         * @since 1.16.0
         * @access public
         */
        public function ajax_save_tax_class_mapping() {

            if ( !defined( 'DOING_AJAX' ) || !DOING_AJAX )
                $response = array( 'status' => 'fail' , 'error_msg' => __( 'Invalid ajax request' , 'woocommerce-wholesale-prices-premium' ) );
            elseif ( !isset( $_POST[ 'mapping-data' ] ) )
                $response = array( 'status' => 'fail' , 'error_msg' => __( 'Required data not supplied' , 'woocommerce-wholesale-prices-premium' ) );
            else {

                $mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_TAX_CLASS_OPTIONS_MAPPING );
                if ( !is_array( $mapping ) )
                    $mapping = array();

                $wholesale_role_key = $_POST[ 'mapping-data' ][ 'wholesale-role-key' ];
                $mode               = $_POST[ 'mapping-data' ][ 'mode' ];

                if ( $mode === 'add' && array_key_exists( $wholesale_role_key , $mapping ) )
                    $response = array( 'status' => 'fail' , 'error_msg' => __( 'Wholesale role mapping entry already exist' , 'woocommerce-wholesale-prices-premium' ) );
                elseif ( $mode === 'edit' && !array_key_exists( $wholesale_role_key , $mapping ) )
                    $response = array( 'status' => 'fail' , 'error_msg' => __( 'Wholesale role mapping entry you are trying to edit does not exist' , 'woocommerce-wholesale-prices-premium' ) );
                else {

                    unset( $_POST[ 'mapping-data' ][ 'wholesale-role-key' ] );
                    unset( $_POST[ 'mapping-data' ][ 'mode' ] );

                    $mapping[ $wholesale_role_key ] = $_POST[ 'mapping-data' ];

                    update_option( WWPP_OPTION_WHOLESALE_ROLE_TAX_CLASS_OPTIONS_MAPPING , $mapping );

                    $response = array( 'status' => 'success' , 'entry_data' => array( $wholesale_role_key => $mapping[ $wholesale_role_key ] ) , 'entry_data_markup' => $this->_generate_mapping_entry_markup( $wholesale_role_key , $mapping[ $wholesale_role_key ] ) );

                }

            }

            @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
            echo wp_json_encode( $response );
            wp_die();

        }

        /**
         * Delete tax class mapping entry.
         * 
         * @since 1.16.0
         * @access public
         */
        public function ajax_delete_tax_class_mapping() {

            if ( !defined( 'DOING_AJAX' ) || !DOING_AJAX )
                $response = array( 'status' => 'fail' , 'error_msg' => __( 'Invalid ajax request' , 'woocommerce-wholesale-prices-premium' ) );
            elseif ( !isset( $_POST[ 'wholesale-role-key' ] ) )
                $response = array( 'status' => 'fail' , 'error_msg' => __( 'Required data not supplied' , 'woocommerce-wholesale-prices-premium' ) );
            else {

                $mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_TAX_CLASS_OPTIONS_MAPPING );
                if ( !is_array( $mapping ) )
                    $mapping = array();

                if ( !array_key_exists( $_POST[ 'wholesale-role-key' ] , $mapping ) )
                    $response = array( 'status' => 'fail' , 'error_msg' => __( 'Mapping entry you are trying to delete does not exist' , 'woocommerce-wholesale-prices-premium' ) );
                else {

                    unset( $mapping[ $_POST[ 'wholesale-role-key' ] ] );

                    update_option( WWPP_OPTION_WHOLESALE_ROLE_TAX_CLASS_OPTIONS_MAPPING , $mapping );

                    $response = array( 'status' => 'success' );

                }

            }

            @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
            echo wp_json_encode( $response );
            wp_die();

        }




        /*
        |---------------------------------------------------------------------------------------------------------------
        | Execute model
        |---------------------------------------------------------------------------------------------------------------
        */
        
        /**
         * Register model ajax handlers.
         *
         * @since 1.14.0
         * @access public
         */
        public function register_ajax_handler() {

            add_action( "wp_ajax_wwpp_add_wholesale_role_tax_option"    , array( $this , 'wwpp_add_wholesale_role_tax_option' ) );
            add_action( "wp_ajax_wwpp_edit_wholesale_role_tax_option"   , array( $this , 'wwpp_edit_wholesale_role_tax_option' ) );
            add_action( "wp_ajax_wwpp_delete_wholesale_role_tax_option" , array( $this , 'wwpp_delete_wholesale_role_tax_option' ) );

            add_action( "wp_ajax_wwpp_save_tax_class_mapping"           , array( $this , 'ajax_save_tax_class_mapping' ) );
            add_action( "wp_ajax_wwpp_delete_tax_class_mapping"         , array( $this , 'ajax_delete_tax_class_mapping' ) );

        }

        /**
         * Execute model.
         *
         * @since 1.14.0
         * @access public
         */
        public function run() {

            add_action( 'init' , array( $this , 'register_ajax_handler' ) );

            // delete_option( WWPP_OPTION_WHOLESALE_ROLE_TAX_CLASS_OPTIONS_MAPPING );

        }

    }

}
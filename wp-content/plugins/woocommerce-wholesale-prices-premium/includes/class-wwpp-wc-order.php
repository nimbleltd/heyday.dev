<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_WC_Order' ) ) {

    /**
     * Model that houses the logic of WWPP integration with WC orders.
     *
     * @since 1.14.0
     */
    class WWPP_WC_Order {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWPP_WC_Order.
         *
         * @since 1.14.0
         * @access private
         * @var WWPP_WC_Order
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
         * WWPP_WC_Order constructor.
         *
         * @since 1.14.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_WC_Order model.
         */
        public function __construct( $dependencies ) {

            $this->_wwpp_wholesale_roles = $dependencies[ 'WWPP_Wholesale_Roles' ];

        }

        /**
         * Ensure that only one instance of WWPP_WC_Order is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.14.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_WC_Order model.
         * @return WWPP_WC_Order
         */
        public static function instance( $dependencies ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }




        /*
        |------------------------------------------------------------------------------------------------------------------
        | Custom Wholesale Order Thank You Message
        |------------------------------------------------------------------------------------------------------------------
        */

        /**
         * Add custom thank you message to thank you page after successful order.
         *
         * @since 1.0.0
         * @since 1.7.4 Only applies to wholesale users now.
         * @since 1.14.0 Refactor codebase and move to its proper model.
         * @access public
         *
         * @param string $orig_msg Original order completed thank you message.
         * @return string Filtered original order completed thank you message.
         */
        public function custom_thank_you_message( $orig_msg ) {

            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();

            if ( !empty( $user_wholesale_role ) ) {

                $new_msg = html_entity_decode( trim( get_option( 'wwpp_settings_thankyou_message' ) ) );

                if ( strcasecmp( $new_msg , "" ) != 0 ) {

                    $pos = get_option( 'wwpp_settings_thankyou_message_position' );

                    switch ( $pos ) {

                        case 'append':
                            return $orig_msg . '<br>' . $new_msg;
                            break;

                        case 'prepend':
                            return $new_msg . '<br>' . $orig_msg;
                            break;

                        default:
                            return $new_msg;
                            break;

                    }

                }

            }

            return $orig_msg;

        }




        /*
        |------------------------------------------------------------------------------------------------------------------
        | WC Order Custom Column
        |------------------------------------------------------------------------------------------------------------------
        */

        /**
         * Add custom column to order listing page.
         *
         * @since 1.0.0
         * @since 1.14.0 Refactor codebase and move to its own model.
         * @access public
         * 
         * @param array $columns Orders cpt listing columns.
         * @return array Filtered orders cpt listing columns.
         */
        public function add_orders_listing_custom_column( $columns ){

            $arrayKeys = array_keys( $columns );
            $lastIndex = $arrayKeys[ count( $arrayKeys ) - 1 ];
            $lastValue = $columns[ $lastIndex ];
            array_pop( $columns );

            $columns[ 'wwpp_order_type' ] = __(  'Order Type' , 'woocommerce-wholesale-prices-premium' );

            $columns[ $lastIndex ] = $lastValue;

            return $columns;

        }

        /**
         * Add content to the custom column on order listing page.
         *
         * @since 1.0.0
         * @since 1.14.0 Refactor codebase and move to its own model.
         * @access public
         *
         * @param string $column  Current column key.
         * @param int    $post_id Current post id.
         */
        public function add_orders_listing_custom_column_content( $column , $post_id ) {

            $allRegisteredWholesaleRoles = $this->_wwpp_wholesale_roles->getAllRegisteredWholesaleRoles();

            if ( $column == 'wwpp_order_type' ) {

                $orderType = get_post_meta( $post_id, '_wwpp_order_type', true );

                if ( $orderType == '' || $orderType == null || $orderType == false || $orderType == 'retail' ) {

                    _e( "Retail" , "woocommerce-wholesale-prices-premium" );

                } elseif ( $orderType == 'wholesale' ) {

                    $wholesaleOrderType = get_post_meta( $post_id, '_wwpp_wholesale_order_type', true );
                    echo sprintf( __( 'Wholesale ( %1$s )' , 'woocommerce-wholesale-prices-premium' ) , $allRegisteredWholesaleRoles[ $wholesaleOrderType ][ 'roleName' ] );

                }

            }

        }
        



        /*
        |------------------------------------------------------------------------------------------------------------------
        | WWPP order type meta
        |------------------------------------------------------------------------------------------------------------------
        */

        /**
         * Attach custom meta to orders ( the order type metadata ) to be used later for filtering orders by order type
         * on the order listing page.
         * 
         * @since 1.0.0
         * @since 1.14.0 Refactor codebase and move to its own model.
         * @access public
         * 
         * @param int $order_id Order id.
         */
        public function add_order_type_meta_to_wc_orders( $order_id ) {

            $all_registered_wholesale_roles = $this->_wwpp_wholesale_roles->getAllRegisteredWholesaleRoles();
            $current_order                  = new WC_Order( $order_id );
            $current_order_wp_user          = get_userdata( $current_order->get_user_id() );
            $current_order_user_roles       = array();

            if ( $current_order_wp_user )
                $current_order_user_roles = $current_order_wp_user->roles;

            if ( !is_array( $current_order_user_roles ) )
                $current_order_user_roles = array();

            $all_registered_wholesale_roles_keys = array();
            foreach ( $all_registered_wholesale_roles as $roleKey => $role )
                $all_registered_wholesale_roles_keys[] = $roleKey;

            $orderUserWholesaleRole = array_intersect( $current_order_user_roles, $all_registered_wholesale_roles_keys );

            if ( !empty( $orderUserWholesaleRole ) ) {

                update_post_meta( $order_id , '_wwpp_order_type' , 'wholesale' );
                update_post_meta( $order_id , '_wwpp_wholesale_order_type' , $orderUserWholesaleRole[ 0 ] );

            } else {

                update_post_meta( $order_id , '_wwpp_order_type', 'retail' );
                update_post_meta( $order_id , '_wwpp_wholesale_order_type', '' );

            }

        }

        /**
         * Add custom filter on order listing page ( order type filter ).
         * 
         * @since 1.0.0
         * @since 1.14.0 Refactor codebase and move to its own model.
         * @access public
         */
        public function add_wholesale_role_order_listing_filter() {

            global $typenow;

            $all_registered_wholesale_roles = $this->_wwpp_wholesale_roles->getAllRegisteredWholesaleRoles();

            if ( $typenow == 'shop_order' ) {

                ob_start();

                $wwpp_fbwr = null;
                if ( isset( $_GET[ 'wwpp_fbwr' ] ) )
                    $wwpp_fbwr = $_GET[ 'wwpp_fbwr' ];

                $all_registered_wholesale_roles = array( 'all_wholesale_orders' => array( 'roleName' => __( 'All Wholesale Orders' , 'woocommerce-wholesale-prices-premium' ) ) ) + $all_registered_wholesale_roles;
                $all_registered_wholesale_roles = array( 'all_retail_orders' => array( 'roleName' => __( 'All Retail Orders' , 'woocommerce-wholesale-prices-premium' ) ) ) + $all_registered_wholesale_roles;
                $all_registered_wholesale_roles = array( 'all_order_types' => array( 'roleName' => __( 'Show all order types' , 'woocommerce-wholesale-prices-premium' ) ) ) + $all_registered_wholesale_roles;

                //wwpp_fbwr = Filter By Wholesale Role ?>

                <select name="wwpp_fbwr" id="filter-by-wholesale-role" class="chosen_select">

                    <?php foreach ( $all_registered_wholesale_roles as $roleKey => $role ) { ?>
                        <option value="<?php echo $roleKey; ?>" <?php echo ( $roleKey == $wwpp_fbwr ) ? 'selected' : '' ; ?>><?php echo $role[ "roleName" ]; ?></option>
                    <?php } ?>

                </select>

                <?php echo ob_get_clean();

            }

        }

        /**
         * Add functionality to the custom filter added on order listing page ( order type filter ).
         * 
         * @since 1.0.0
         * @since 1.14.0 Refactor codebase and move to its own model.
         * @access public
         * 
         * @param WP_Query $query WP_Query object.
         */
        public function wholesale_role_order_listing_filter( $query ) {

            global $pagenow;
            $wholesale_filter = null;

            if ( isset( $_GET[ 'wwpp_fbwr' ] ) )
                $wholesale_filter = trim( $_GET[ 'wwpp_fbwr' ] );

            if ( $pagenow == 'edit.php' && isset( $query->query_vars[ 'post_type' ] ) && $query->query_vars[ 'post_type' ] == 'shop_order' && !is_null( $wholesale_filter ) ) {

                switch ( $wholesale_filter ) {

                    case 'all_order_types':
                        // Do nothing
                        break;

                    case 'all_retail_orders':

                        $query->set(
                            'meta_query',
                            array(
                                'relation'      =>  'OR',
                                array(
                                    'key'       =>  '_wwpp_order_type',
                                    'value'     =>  array('retail'),
                                    'compare'   =>  'IN'
                                ),
                                array(
                                    'key'       =>  '_wwpp_order_type',
                                    'value'     =>  'gebbirish', // Pre WP 3.9 bug, must set string for NOT EXISTS to work
                                    'compare'   =>  'NOT EXISTS'
                                )
                            )
                        );

                        break;

                    case 'all_wholesale_orders':

                        $query->query_vars[ 'meta_key' ]   = '_wwpp_order_type';
                        $query->query_vars[ 'meta_value' ] = 'wholesale';

                        break;

                    default:

                        $query->query_vars[ 'meta_key' ]   = '_wwpp_wholesale_order_type';
                        $query->query_vars[ 'meta_value' ] = $wholesale_filter;

                        break;

                }

            }

            return $query;

        }




        /*
        |---------------------------------------------------------------------------------------------------------------
        | Execute model
        |---------------------------------------------------------------------------------------------------------------
        */

        /**
         * Execute model.
         *
         * @since 1.14.0
         * @access public
         */
        public function run() {

            add_filter( 'woocommerce_thankyou_order_received_text' , array( $this , 'custom_thank_you_message' ) , 10 , 1 );

            add_filter('manage_edit-shop_order_columns'        , array( $this, 'add_orders_listing_custom_column' )    , 15 , 1 );
            add_action('manage_shop_order_posts_custom_column' , array( $this, 'add_orders_listing_custom_column_content' ) , 10 , 2 );

            add_action( 'woocommerce_checkout_order_processed' , array( $this, 'add_order_type_meta_to_wc_orders' )        , 10 , 2 );
            add_action( 'restrict_manage_posts' ,                array( $this, 'add_wholesale_role_order_listing_filter' ) , 10 , 1 );
            add_filter( 'parse_query' ,                          array( $this, 'wholesale_role_order_listing_filter' )     , 10 , 1 );

        }

    }

}
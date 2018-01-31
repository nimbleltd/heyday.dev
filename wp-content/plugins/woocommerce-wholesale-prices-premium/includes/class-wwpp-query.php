<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_Query' ) ) {

    /**
     * Model that houses the logic of filtering on woocommerce query.
     * 
     * @since 1.12.8
     * @see WWPP_Product_Visibility They are related in a way that WWPP_Product_Visibility filter product to be visible only to certain user roles.
     */
    class WWPP_Query {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWPP_Query.
         *
         * @since 1.12.8
         * @access private
         * @var WWPP_Query
         */
        private static $_instance;
        
        /**
         * Model that houses the logic of retrieving information relating to wholesale role/s of a user.
         *
         * @since 1.12.8
         * @access private
         * @var WWPP_Wholesale_Roles
         */
        private $_wwpp_wholesale_roles;

        /**
         * Model that houses the logic of product wholesale price on per wholesale role level.
         * 
         * @since 1.16.0
         * @access private
         * @var WWPP_Wholesale_Price_Wholesale_Role
         */
        private $_wwpp_wholesale_price_wholesale_role;

        /**
         * Array of registered wholesale roles.
         *
         * @since 1.13.0
         * @access private
         * @var array
         */
        private $_registered_wholesale_roles;

        /**
         * Product category wholesale role filter.
         * 
         * @since 1.16.0
         * @access public
         * @var array
         */
        private $_product_cat_wholesale_role_filter;




        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        /**
         * WWPP_Query constructor.
         *
         * @since 1.12.8
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Query model.
         */
        public function __construct( $dependencies ) {

            $this->_wwpp_wholesale_roles                = $dependencies[ 'WWPP_Wholesale_Roles' ];
            $this->_wwpp_wholesale_price_wholesale_role = $dependencies[ 'WWPP_Wholesale_Price_Wholesale_Role' ];

            $this->_registered_wholesale_roles = $this->_wwpp_wholesale_roles->getAllRegisteredWholesaleRoles();

            $this->_product_cat_wholesale_role_filter = get_option( WWPP_OPTION_PRODUCT_CAT_WHOLESALE_ROLE_FILTER );
            if ( !is_array( $this->_product_cat_wholesale_role_filter ) )
                $this->_product_cat_wholesale_role_filter = array();

        }

        /**
         * Ensure that only one instance of WWPP_Query is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.12.8
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Query model.
         * @return WWPP_Query
         */
        public static function instance( $dependencies ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }

        /**
         * Apply wholesale roles filter to shop and archive pages.
         *
         * @since 1.0.0
         * @since 1.7.4
         * There is a bug where is you do 2 separate set->('meta_query', $args)
         * then that meta query becomes an or query not an and, can't figure out why.
         * So we need to set->('meta_query',$args) the 2 filters at the same time
         * The product visibility filter and the show only wholesale products to wholesale users filter
         * @since 1.12.8 Refactor code base for effeciency and maintanability.
         * @since 1.13.1 Prevent query stacking. (Applying the same filter to the same query multiple times).
         * @since 1.15.3 Silence notices thrown by function is_shop.
         * @since 1.16.0 Add support for per category wholesale role filter.
         * @access public
         *
         * @param WP_Query $query WP_Query object.
         */
        public function pre_get_posts( $query ) {

            // Check if user is not an admin, else we don't want to restrict admins in any way.
            if ( !current_user_can( 'manage_options' ) && !current_user_can( 'manage_woocommerce' ) ) { // Admin and Shop Manager
                
                if ( ! $query->is_main_query() ) return;

                if ( is_search() && ( !isset( $_GET[ 'post_type' ] ) || $_GET[ 'post_type' ] !== 'product' ) ) {

                    // Normal WP search, exclude product related stuff here and terminate function early

                    $public_post_types = get_post_types( array(
                        'public'              => true,
                        'publicly_queryable'  => true,
                        'exclude_from_search' => false
                    ) );

                    if ( array_key_exists( 'product' , $public_post_types ) )
                        unset( $public_post_types[ 'product' ] );

                    if ( !array_key_exists( 'page' , $public_post_types ) )
                        $public_post_types[ 'page' ] = 'page';
                    
                    $query->set( 'post_type' , array_keys( $public_post_types ) );
                    
                    return;

                }

                $user_wholesale_role   = $this->_get_current_user_wholesale_role();
                $meta_query            = is_array( $query->get( 'meta_query' ) ) ? $query->get( 'meta_query' ) : array();
                $serialized_meta_query = maybe_serialize( $meta_query );
                $front_page_id         = get_option( 'page_on_front' );
                $current_page_id       = $query->get( 'page_id' );
                $shop_page_id          = apply_filters( 'woocommerce_get_shop_page_id' , get_option( 'woocommerce_shop_page_id' ) );
                $is_static_front_page  = 'page' == get_option( 'show_on_front' );

                // We do this way in determining the shop page for cases where the shop page is set as the front page
                if ( $is_static_front_page && $front_page_id == $current_page_id  )
                    $is_shop_page = ( $current_page_id == $shop_page_id ) ? true : false;
                else
                    $is_shop_page = @is_shop();

                if ( ! is_admin() && ( $is_shop_page || is_product_category() || is_product_taxonomy() || is_search() ) ) {

                    // Make sure we don't re add this query if it is already added
                    if ( strpos( $serialized_meta_query , WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER ) === false ) {

                        $meta_query[] = array(
                                            'key'     => WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER,
                                            'value'   => array( $user_wholesale_role , 'all' ),
                                            'compare' => 'IN'
                                        );
                        
                    }
                    
                    // Tax query init
                    $filtered_term_ids = array();

                    if ( !empty( $user_wholesale_role ) && 
                         get_option( 'wwpp_settings_only_show_wholesale_products_to_wholesale_users' , false ) === 'yes' && 
                         !$this->_wholesale_user_have_general_role_discount( $user_wholesale_role ) ) {

                        // Make sure we don't re add this query if it is already added
                        if ( strpos( $serialized_meta_query , $user_wholesale_role . '_have_wholesale_price' ) === false ) {
                            
                            // If there is a default general wholesale discount set for this wholesale role then all products are considered wholesale for this dude
                            // If no mapping is present, then we only show products with wholesale prices specific for this wholesale role
                            
                            $meta_query[] = array(
                                                'relation'    => 'OR',
                                                array(
                                                    'key'     => $user_wholesale_role . '_have_wholesale_price',
                                                    'value'   => 'yes',
                                                    'compare' => '='
                                                ),
                                                array( // WWPP-158 : Compatibility with WooCommerce Show Single Variations
                                                    'key'     => $user_wholesale_role . '_wholesale_price',
                                                    'value'   => 0,
                                                    'compare' => '>',
                                                    'type'    => 'NUMERIC'
                                                )
                                            );
                            
                            if ( !empty( $this->_product_cat_wholesale_role_filter ) )
                                $filtered_term_ids = $this->_get_restricted_product_cat_ids_for_wholesale_user( $user_wholesale_role );

                        }
                        
                    } elseif ( !empty( $user_wholesale_role ) && !empty( $this->_product_cat_wholesale_role_filter ) )
                        $filtered_term_ids = $this->_get_restricted_product_cat_ids_for_wholesale_user( $user_wholesale_role );
                    elseif ( empty( $user_wholesale_role ) && !empty( $this->_product_cat_wholesale_role_filter ) )
                        $filtered_term_ids = array_keys( $this->_product_cat_wholesale_role_filter ); // Non wholesale user

                    // Set tax query
                    if ( !empty( $filtered_term_ids ) ) {
                        
                        $tax_query                    = is_array( $query->get( 'tax_query' ) ) ? $query->get( 'tax_query' ) : array();
                        $serialized_tax_query         = maybe_serialize( $tax_query );
                        $serialized_filtered_term_ids = maybe_serialize( $filtered_term_ids );

                        // The goal here is to not repeatedly add this tax query as pre_get_posts can be called multiple times
                        if ( strpos( $serialized_tax_query , $serialized_filtered_term_ids ) === false ) {

                            $tax_query[] = array(
                                'taxonomy' => 'product_cat',
                                'field'    => 'term_id',
                                'terms'    => array_map( 'intval' , $filtered_term_ids ),
                                'operator' => 'NOT IN'
                            );
    
                            $query->set( 'tax_query' , $tax_query );

                        }

                    }

                    // Due to a WordPress bug, we need to set meta query the 2 filters at the same time in one go
                    $query->set( 'meta_query' , $meta_query );

                }

            }

            remove_action( 'pre_get_posts' , array( $this , 'pre_get_posts' ) , 10 );

        }

        /**
         * Same as pre_get_posts function but only intended for WooCommerce Wholesale Order Form integration,
         * you see the WWOF uses custom query, so unlike the usual way of filter query object, we can't do that with WWOF,
         * but we can filter the query args thus achieving the same effect.
         *
         * @since 1.0.0
         * @since 1.7.4  Apply "Only Show Wholesale Products To Wholesale Users" filter.
         * @since 1.12.8 Refactor code base for effeciency and maintanability.
         * @since 1.16.0 Add support for per category wholesale role filter.
         * @access public
         *
         * @param array $query_args Query args array.
         * @return mixed
         */
        public function pre_get_posts_arg( $query_args ) {

            // Check if user is not an admin, else we don't want to restrict admins in any way.
            if ( !current_user_can( 'manage_options' ) ) {

                $user_wholesale_role   = $this->_get_current_user_wholesale_role();
                $serialized_query_args = maybe_serialize( $query_args );

                // Make sure we don't re add this query if it is already added
                if ( strpos( $serialized_query_args , WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER ) === false ) {

                    $query_args[ 'meta_query' ][] = array(
                                                        'key'     => WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER,
                                                        'value'   => array( $user_wholesale_role , 'all' ),
                                                        'compare' => 'IN'
                                                    );
                    
                }

                // Tax query init
                $filtered_term_ids = array();

                if ( $user_wholesale_role &&
                     get_option( 'wwpp_settings_only_show_wholesale_products_to_wholesale_users' , false ) === 'yes' && 
                     !$this->_wholesale_user_have_general_role_discount( $user_wholesale_role ) ) {
                    
                    // Make sure we don't re add this query if it is already added
                    if ( strpos( $serialized_query_args , $user_wholesale_role . '_have_wholesale_price' ) === false ) {
                        
                        // If there is a default general wholesale discount set for this wholesale role then all products are considered wholesale for this dude
                        // If no mapping is present, then we only show products with wholesale prices specific for this wholesale role

                        $query_args[ 'meta_query' ][] = array(
                                                            'relation'    => 'OR',
                                                            array(
                                                                'key'     => $user_wholesale_role . '_have_wholesale_price',
                                                                'value'   => 'yes',
                                                                'compare' => '='
                                                            ),
                                                            array( // WWPP-158 : Compatibility with WooCommerce Show Single Variations
                                                                'key'     => $user_wholesale_role . '_wholesale_price',
                                                                'value'   => 0,
                                                                'compare' => '>',
                                                                'type'    => 'NUMERIC'
                                                            )
                                                        );
                        
                        if ( !empty( $this->_product_cat_wholesale_role_filter ) )
                            $filtered_term_ids = $this->_get_restricted_product_cat_ids_for_wholesale_user( $user_wholesale_role );

                    }

                } elseif ( !empty( $user_wholesale_role ) && !empty( $this->_product_cat_wholesale_role_filter ) )
                    $filtered_term_ids = $this->_get_restricted_product_cat_ids_for_wholesale_user( $user_wholesale_role );
                elseif ( empty( $user_wholesale_role ) && !empty( $this->_product_cat_wholesale_role_filter ) )
                    $filtered_term_ids = array_keys( $this->_product_cat_wholesale_role_filter ); // Non wholesale user
                
                if ( !empty( $filtered_term_ids ) ) {

                    if ( !isset( $query_args[ 'tax_query' ] ) )
                        $query_args[ 'tax_query' ] = array();

                    $serialized_tax_query         = maybe_serialize( $query_args[ 'tax_query' ] );
                    $serialized_filtered_term_ids = maybe_serialize( $filtered_term_ids );

                    // The goal here is to not repeatedly add this tax query as pre_get_posts can be called multiple times
                    if ( strpos( $serialized_tax_query , $serialized_filtered_term_ids ) === false ) {

                        $query_args[ 'tax_query' ][] = array(
                            'taxonomy' => 'product_cat',
                            'field'    => 'term_id',
                            'terms'    => array_map( 'intval' , $filtered_term_ids ),
                            'operator' => 'NOT IN'
                        );

                    }

                }

            }

            return $query_args;

        }

        /**
         * Filter product query. New in WC 3.0.7, they are now trying to implement prepared statements style on their product sql query.
         *
         * @since 1.14.6
         * @access public
         *
         * @param array $query_arr  Query array.
         * @param int   $product_id Product id.
         * @return array Filtered product query.
         */
        public function product_query_filter( $query_arr , $product_id ) {

            global $wpdb;

            // Check if user is not an admin, else we don't want to restrict admins in any way.
            if ( !current_user_can( 'manage_options' ) ) {

                $user_wholesale_role  = $this->_get_current_user_wholesale_role();
                $serialized_query_arr = maybe_serialize( $query_arr );
                
                // Make sure we don't re add this query if it is already added
                if ( strpos( $serialized_query_arr , WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER ) === false ) {

                    $query_arr[ 'where' ] .= " AND p.ID IN (
                                                    SELECT DISTINCT pt.ID
                                                    FROM $wpdb->posts pt
                                                    INNER JOIN $wpdb->postmeta pmt
                                                    ON pt.ID = pmt.post_id
                                                    WHERE pmt.meta_key = '" . WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER . "'
                                                    AND pmt.meta_value IN ( '" . $user_wholesale_role . "' , 'all' )
                                                )";
                    
                }
                
                if ( $user_wholesale_role &&
                     get_option( 'wwpp_settings_only_show_wholesale_products_to_wholesale_users' , false ) === 'yes' && 
                     !$this->_wholesale_user_have_general_role_discount( $user_wholesale_role ) ) {
                    
                    // Make sure we don't re add this query if it is already added
                    if ( strpos( $serialized_query_arr , $user_wholesale_role . '_have_wholesale_price' ) === false ) {
                        
                        // If there is a default general wholesale discount set for this wholesale role then all products are considered wholesale for this dude
                        // If no mapping is present, then we only show products with wholesale prices specific for this wholesale role
                        
                        $query_arr[ 'where' ] .= " AND p.ID IN (
                                                        SELECT DISTINCT pt.ID
                                                        FROM $wpdb->posts pt
                                                        INNER JOIN $wpdb->postmeta pmt
                                                        ON pt.ID = pmt.post_id
                                                        WHERE ( pmt.meta_key = '" . $user_wholesale_role . "_have_wholesale_price' AND pmt.meta_value = 'yes' )
                                                        OR ( pmt.meta_key = '" . $user_wholesale_role . "_wholesale_price' AND pmt.meta_value > 0 )
                                                    )";
                        
                    }

                }

            }

            return $query_arr;

        }

        /**
         * WC Layer Nav Widget  query is not really optimized well for extension.
         * The widget query alone is fast, however, if it is extended it became very slow.
         * Ticket ID: WWPP-437
         *
         * @param array $query Array of sql query.
         * @return array Filtered array of sql query.
         */
        public function optimize_wwpp_query_for_layer_nav_query( $query ) {

            global $wpdb;

            $user_wholesale_role = $this->_get_current_user_wholesale_role();

            $query = str_replace( "INNER JOIN wp_postmeta ON ( wp_posts.ID = wp_postmeta.post_id )" , "" , $query );
            $query = str_replace( 
                        "( wp_postmeta.meta_key = 'wwpp_product_wholesale_visibility_filter' AND wp_postmeta.meta_value IN ('$user_wholesale_role','all') )",
                        "( wp_posts.ID IN ( SELECT DISTINCT pt.ID FROM $wpdb->posts pt INNER JOIN $wpdb->postmeta pmt ON pt.ID = pmt.post_id WHERE pmt.meta_key = '" . WWPP_PRODUCT_WHOLESALE_VISIBILITY_FILTER . "' AND pmt.meta_value IN ( '$user_wholesale_role' , 'all' ) ) )",
                        $query 
                    );

            return $query;

        }




        /*
        |---------------------------------------------------------------------------------------------------------------
        | Helper Functions
        |---------------------------------------------------------------------------------------------------------------
        */

        /**
         * Get curent user wholesale role.
         *
         * @since 1.12.8
         * @access private
         *
         * @return string User role string or empty string.
         */
        private function _get_current_user_wholesale_role() {
            
            $user_wholesale_role = $this->_wwpp_wholesale_roles->getUserWholesaleRole();
            
            return ( is_array( $user_wholesale_role ) && !empty( $user_wholesale_role ) ) ? $user_wholesale_role[ 0 ] : '';

        }

        /**
         * Check if a wholesale user have an entry on general role discount mapping.
         * WooCommerce > Settings > Wholesale Prices > Discount > General Discount Options
         *
         * @since 1.12.8 
         * @since 1.16.0 Refactor code base to get wholesale discount wholesale role level from 'WWPP_Wholesale_Price_Wholesale_Role' model.
         * @access private
         *
         * @param string $user_wholesale_role User Wholesale Role Key.
         * @return boolean Whether wholesale user have mapping entry or not.
         */
        private function _wholesale_user_have_general_role_discount( $user_wholesale_role ) {

            $user_wholesale_discount = $this->_wwpp_wholesale_price_wholesale_role->get_user_wholesale_role_level_discount( get_current_user_id() , $user_wholesale_role );
            return !empty( $user_wholesale_discount[ 'discount' ] );
            
        }

        /**
         * Get restricted term ids for the current wholesale user.
         * 
         * @since 1.16.0
         * @access public
         * 
         * @param string $user_wholesale_role User wholesale role.
         * @return array Array of restricted term ids for the current wholesale user.
         */
        private function _get_restricted_product_cat_ids_for_wholesale_user( $user_wholesale_role ) {

            $filtered_terms_ids = array();

            foreach ( $this->_product_cat_wholesale_role_filter as $term_id => $filtered_wholesale_roles )
                if ( !in_array( $user_wholesale_role , $filtered_wholesale_roles ) )
                    $filtered_terms_ids[] = $term_id;

            return $filtered_terms_ids;

        }

        
        

        /*
        |---------------------------------------------------------------------------------------------------------------
        | Execute Model
        |---------------------------------------------------------------------------------------------------------------
        */

        /**
         * Execute model.
         *
         * @since 1.12.8
         * @access public
         */
        public function run() {

            add_action( 'pre_get_posts' , array( $this , 'pre_get_posts' ) , 10 , 1 );

            // Filter various woocommerce product related queries
            add_filter( 'woocommerce_product_query_meta_query'    , array( $this , 'pre_get_posts_arg' )    , 10 , 1 );
            add_filter( 'woocommerce_grouped_children_args'       , array( $this , 'pre_get_posts_arg' )    , 10 , 1 );
            add_filter( 'woocommerce_products_widget_query_args'  , array( $this , 'pre_get_posts_arg' )    , 10 , 1 );
            add_filter( 'woocommerce_related_products_args'       , array( $this , 'pre_get_posts_arg' )    , 10 , 1 );
            add_filter( 'woocommerce_product_related_posts_query' , array( $this , 'product_query_filter' ) , 10 , 2 ); // WC 3.0.7
            add_filter( 'woocommerce_shortcode_products_query'    , array( $this , 'pre_get_posts_arg' )    , 10 , 1 );

            // // Filter product query in wwof
            add_filter( 'wwof_filter_product_listing_query_arg' ,  array( $this , 'pre_get_posts_arg' ) , 10 , 1 );

            // // Fix slow query on wc layer nav query
            add_filter( 'woocommerce_get_filtered_term_product_counts_query' , array( $this , 'optimize_wwpp_query_for_layer_nav_query' ) , 10 , 1 );

        }

    }

}

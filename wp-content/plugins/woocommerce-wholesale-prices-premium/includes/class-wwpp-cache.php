<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_Cache' ) ) {
    
    /**
     * Model that houses the logic relating caching.
     *
     * @since 1.16.0
     */
    class WWPP_Cache {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWPP_Cache.
         *
         * @since 1.16.0
         * @access private
         * @var WWPP_Cache
         */
        private static $_instance;



        
        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        /**
         * WWPP_Cache constructor.
         *
         * @since 1.16.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Cache model.
         */
        public function __construct( $dependencies ) {}
        
        /**
         * Ensure that only one instance of WWPP_Cache is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.16.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Cache model.
         * @return WWPP_Cache
         */
        public static function instance( $dependencies ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }




        /*
        |-------------------------------------------------------------------------------------------------------------------
        | Hashing
        |-------------------------------------------------------------------------------------------------------------------
        */

        /**
         * Set settings meta hash.
         * 
         * @since 1.16.0
         * @access public
         * 
         * @return string Generated hash.
         */
        public function set_settings_meta_hash() {

            $hash = uniqid( '' , true );

            update_option( 'wwpp_settings_hash' , $hash );

            return $hash;

        }

        /**
         * Set product category meta hash.
         * 
         * @since 1.16.0
         * @access public
         * 
         * @param int    $term_id          Term Id.
         * @param int    $taxonomy_term_id Taxonomy term id.
         * @param string $taxonomy         Taxonomy
         * @return string|boolean Generated hash or false when operation fails
         */
        public function set_product_category_meta_hash( $term_id , $taxonomy_term_id , $taxonomy = 'product_cat' ) {

            if ( $taxonomy === 'product_cat' ) {

                $hash = uniqid( '' , true );

                update_option( 'wwpp_product_cat_hash' , $hash );

                return $hash;

            }

            return false;

        }

        /**
         * Set product category meta hash.
         * 
         * @since 1.16.0
         * @access public
         * 
         * @param int    $term_id          Term Id.
         * @param int    $taxonomy_term_id Taxonomy term id.
         * @param object $deleted_term     Deleted term object.
         * @param array  $object_ids       List of term object ids.
         */
        public function set_product_category_meta_hash_delete_term( $term_id , $taxonomy_term_id , $deleted_term , $object_ids ) {

            $this->set_product_category_meta_hash( $term_id , $taxonomy_term_id , 'product_cat' );

        }

        /**
         * Set product meta hash.
         * 
         * @since 1.16.0
         * @access public
         * 
         * @param int     $post_id      Post id.
         * @param boolean $bypass_check Flag to whether bypass the action validity check.
         * @return string|boolean Generated hash or false when operation fails
         */
        public function set_product_meta_hash( $post_id , $bypass_check = false ) {

            if ( $bypass_check === true || WWP_Helper_Functions::check_if_valid_save_post_action( $post_id , 'product' ) ) {

                $hash = uniqid( '' , true );

                update_post_meta( $post_id , 'wwpp_product_hash' , $hash );

                return $hash;

            }

            return false;

        }
        

        

        /*
        |-------------------------------------------------------------------------------------------------------------------
        | Public Functions
        |-------------------------------------------------------------------------------------------------------------------
        */

        /**
         * Check variable product price range cache if valid.
         * 
         * @since 1.16.0
         * @access public
         * 
         * @param int        $user_id    User Id.
         * @param WC_Product $product    WC_Product object.
         * @param array      $cache_data Cache data.
         * @return boolean True if cache is valid, false otherwise.
         */
        public function check_variable_product_price_range_cache_if_valid( $user_id , $product , $cache_data ) {

            if ( WWP_Helper_Functions::wwp_get_product_type( $product ) === "variable" ) {

                $settings_hash    = get_option( 'wwpp_settings_hash' );
                $product_cat_hash = get_option( 'wwpp_product_cat_hash' );
                $product_hash     = get_post_meta( WWP_Helper_Functions::wwp_get_product_id( $product ) , 'wwpp_product_hash' , true );

                if ( !empty( $settings_hash ) && !empty( $product_cat_hash ) && !empty( $product_hash ) )
                    return $settings_hash === $cache_data[ 'wwpp_settings_hash' ] && $product_cat_hash === $cache_data[ 'wwpp_product_cat_hash' ] && $product_hash === $cache_data[ 'wwpp_product_hash' ];

            }

            return false;

        }

        /**
         * Set variable product price range cache.
         * 
         * @since 1.16.0
         * @access public
         * 
         * @param int        $user_id User Id.
         * @param WC_Product $product WC_Product object.
         * @param array      $args    Data to cache.
         */
        public function set_variable_product_price_range_cache( $user_id , $product , $args ) {

            if ( WWP_Helper_Functions::wwp_get_product_type( $product ) === "variable" ) {

                $product_id       = WWP_Helper_Functions::wwp_get_product_id( $product );
                $settings_hash    = get_option( 'wwpp_settings_hash' );
                $product_cat_hash = get_option( 'wwpp_product_cat_hash' );
                $product_hash     = get_post_meta( $product_id , 'wwpp_product_hash' , true );

                if ( empty( $settings_hash ) )
                    $settings_hash = $this->set_settings_meta_hash();

                if ( empty( $product_cat_hash ) )
                    $product_cat_hash = $this->set_product_category_meta_hash( false , false , 'product_cat' );

                if ( empty( $product_hash ) )
                    $product_hash = $this->set_product_meta_hash( $product_id , true );

                $user_cached_data = get_user_meta( $user_id , 'wwpp_variable_product_price_range_cache' , true );
                if ( !is_array( $user_cached_data ) )
                    $user_cached_data = array();

                $harhes_arr = array(
                    'wwpp_settings_hash'    => $settings_hash,
                    'wwpp_product_cat_hash' => $product_cat_hash,
                    'wwpp_product_hash'     => $product_hash
                );

                $cache_data = wp_parse_args( $args , $harhes_arr );

                $user_cached_data[ $product_id ] = $cache_data;

                update_user_meta( $user_id , 'wwpp_variable_product_price_range_cache' , $user_cached_data );

            }

        }

        /**
         * Get cache variable product price range cache.
         * 
         * @since 1.16.0
         * @access public
         * 
         * @param int        $user_id User Id.
         * @param WC_Product $product WC_Product object.
         * @return array|boolean Array of cached data if successful, boolean false otherwise.
         */
        public function get_cache_variable_product_price_range_cache( $user_id , $product ) {

            if ( WWP_Helper_Functions::wwp_get_product_type( $product ) === "variable" ) {

                $product_id = WWP_Helper_Functions::wwp_get_product_id( $product );

                $user_cached_data = get_user_meta( $user_id , 'wwpp_variable_product_price_range_cache' , true );
                if ( !is_array( $user_cached_data ) )
                    $user_cached_data = array();

                return array_key_exists( $product_id , $user_cached_data ) ? $user_cached_data[ $product_id ] : false;

            }

            return false;

        }

        /**
         * Get variable product price range cache.
         * 
         * @since 1.16.0
         * @access public
         * 
         * @param boolean|array $cache_data Cached data, false by default.
         * @param int           $user_id User Id.
         * @param WC_Product    $product WC_Product object.
         * @param array         $user_wholesale_role Array of wholesale roles for the current user.
         * @return array Cached data.
         */
        public function get_variable_product_price_range_cache( $cache_data , $user_id , $product , $user_wholesale_role ) {

            if ( get_option( 'wwpp_enable_var_prod_price_range_caching' ) !== 'yes' )
                return false;

            $variable_product_price_range_cache = $this->get_cache_variable_product_price_range_cache( $user_id , $product );
            if ( $variable_product_price_range_cache !== false && $this->check_variable_product_price_range_cache_if_valid( $user_id , $product , $variable_product_price_range_cache ) )
                $cache_data = $variable_product_price_range_cache;

            return $cache_data;

        }

        /**
         * Hey, I just met you and this is crazy, But here's my number, so call me, maybe set variable price range cache.
         * 
         * @since 1.16.0
         * @access public
         * 
         * @param int        $user_id             User Id.
         * @param WC_Product $product             WC_Product object.
         * @param array      $user_wholesale_role Array of wholesale roles for the current user.
         * @param array      $args                Array of data to cache.
         */
        public function maybe_set_variable_price_range_cache( $user_id , $product , $user_wholesale_role , $args ) {

            if ( get_option( 'wwpp_enable_var_prod_price_range_caching' ) !== 'yes' )
                return false;

            $variable_product_price_range_cache = $this->get_cache_variable_product_price_range_cache( $user_id , $product );

            if ( $variable_product_price_range_cache === false || !$this->check_variable_product_price_range_cache_if_valid( $user_id , $product , $variable_product_price_range_cache ) )
                if ( is_array( $args ) && isset( $args[ 'min_price' ] ) && isset( $args[ 'max_price' ] ) && isset( $args[ 'some_variations_have_wholesale_price' ] ) )
                    $this->set_variable_product_price_range_cache( $user_id , $product , $args );

        }




        /*
        |-------------------------------------------------------------------------------------------------------------------
        | AJAX
        |-------------------------------------------------------------------------------------------------------------------
        */

        /**
         * Regenerate new hash for caching feature. This will in turn invalidate all existing cache.
         * 
         * @since 1.16.0
         * @access public
         */
        public function ajax_regenerate_new_cache_hash() {

            if ( !defined( "DOING_AJAX" ) || !DOING_AJAX )
                $response = array( 'status' => 'fail' , 'error_msg' => __( 'Invalid AJAX Operation' , 'woocommerce-wholesale-prices-premium' ) );
            elseif ( !check_ajax_referer( 'wwpp_regenerate_new_cache_hash' , 'ajax-nonce' , false ) )
                $response = array( 'status' => 'fail' , 'error_msg' => __( 'Security check failed' , 'woocommerce-wholesale-prices-premium' ) );
            else {

                $this->set_settings_meta_hash();
                $this->set_product_category_meta_hash( null , null , 'product_cat' );

                $response = array( 'status' => 'success' , 'success_msg' => __( 'Successfully cleared all variable product price range cache' , 'woocommerce-wholesale-prices-premium' ) );

            }

            @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
            echo wp_json_encode( $response );
            wp_die();

        }

        /**
         * Register ajax handlers.
         * 
         * @since 1.16.0
         * @access public
         */
        public function register_ajax_handlers() {

            add_action( 'wp_ajax_wwpp_regenerate_new_cache' , array( $this , 'ajax_regenerate_new_cache_hash' ) );

        }



        
        /*
        |-------------------------------------------------------------------------------------------------------------------
        | Execute Model
        |-------------------------------------------------------------------------------------------------------------------
        */

        /**
         * Execute model.
         *
         * @since 1.16.0
         * @access public
         */
        public function run() {
            
            // On every product category change, WC settings change and Product update, we create new hashes
            add_action( 'woocommerce_settings_saved' , array( $this , 'set_settings_meta_hash' )                     , 10 );
            add_action( 'created_product_cat'        , array( $this , 'set_product_category_meta_hash' )             , 10 , 2 ); // New Product Cat
            add_action( 'edit_term'                  , array( $this , 'set_product_category_meta_hash' )             , 10 , 3 ); // Edit Product Cat
            add_action( 'delete_product_cat'         , array( $this , 'set_product_category_meta_hash_delete_term' ) , 10 , 4 ); // Delete Product Cat
            add_action( 'save_post'                  , array( $this , 'set_product_meta_hash' )                      , 10 , 1 );

            add_filter( 'wwp_get_variable_product_price_range_cache'     , array( $this , 'get_variable_product_price_range_cache' ) , 10 , 4 );
            add_action( 'wwp_after_variable_product_compute_price_range' , array( $this , 'maybe_set_variable_price_range_cache' )   , 10 , 4 );

            add_action( 'init' , array( $this , 'register_ajax_handlers' ) );

        }

    }

}
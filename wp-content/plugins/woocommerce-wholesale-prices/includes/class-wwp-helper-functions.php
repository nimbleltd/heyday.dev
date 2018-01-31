<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWP_Helper_Functions' ) ) {

    /**
     * Model that houses plugin helper functions.
     *
     * @since 1.3.0
     */
    final class WWP_Helper_Functions {

        /**
         * Utility function that determines if a plugin is active or not.
         *
         * @since 1.3.0
         * @access public
         *
         * @param string $plugin_basename Plugin base name. Ex. woocommerce/woocommerce.php
         * @return boolean True if active, false otherwise.
         */
        public static function is_plugin_active( $plugin_basename ) {

            // Makes sure the plugin is defined before trying to use it
            if ( !function_exists( 'is_plugin_active' ) )
                include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

            return is_plugin_active( $plugin_basename );

        }

        /**
         * Get data about the current woocommerce installation.
         *
         * @since 1.3.1
         * @access public
         *
         * @return array Array of data about the current woocommerce installation.
         */
        public static function get_woocommerce_data() {

            return self::get_plugin_data( 'woocommerce/woocommerce.php' );

        }

        /**
         * Get plugin data.
         *
         * @since 1.4.0
         * @access public
         *
         * @oaran string $plugin_basename Plugin basename.
         * @return array Array of data about the current woocommerce installation.
         */
        public static function get_plugin_data( $plugin_basename ) {

            if ( ! function_exists( 'get_plugin_data' ) )
                require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

            return get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_basename );

        }

        /**
         * Output a text input box.
         * This gonna be 99.99% similar to woocommerce_wp_text_input function of WooCommerce.
         * Only difference is the position of the tooltip.
         * This is essential for wholesale prices field on variation products where the tooltip should be next to the label, not the text field.
         *
         * @since 1.3.0
         * @access public
         *
         * @param array $field Field data.
         */
        public static function wwp_woocommerce_wp_text_input( $field ) {
            global $thepostid, $post;

            $thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
            $field['placeholder']   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
            $field['class']         = isset( $field['class'] ) ? $field['class'] : 'short';
            $field['style']         = isset( $field['style'] ) ? $field['style'] : '';
            $field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
            $field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
            $field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
            $field['type']          = isset( $field['type'] ) ? $field['type'] : 'text';
            $data_type              = empty( $field['data_type'] ) ? '' : $field['data_type'];

            switch ( $data_type ) {
                case 'price' :
                    $field['class'] .= ' wc_input_price';
                    $field['value']  = wc_format_localized_price( $field['value'] );
                    break;
                case 'decimal' :
                    $field['class'] .= ' wc_input_decimal';
                    $field['value']  = wc_format_localized_decimal( $field['value'] );
                    break;
                case 'stock' :
                    $field['class'] .= ' wc_input_stock';
                    $field['value']  = wc_stock_amount( $field['value'] );
                    break;
                case 'url' :
                    $field['class'] .= ' wc_input_url';
                    $field['value']  = esc_url( $field['value'] );
                    break;

                default :
                    break;
            }

            // Custom attribute handling
            $custom_attributes = array();

            if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {

                foreach ( $field['custom_attributes'] as $attribute => $value ){
                    $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
                }
            }

            // Custom part
            $desc_tootip = '';
            if ( ! empty( $field['description'] ) ) {

                if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
                    $desc_tootip = '<span class="wwp_wc_help_tip_container" style="top: -3px; position: relative; display: inline-block;">' . wc_help_tip( $field['description'] ) . '</span>';
                } else {
                    $desc_tootip = '<br><span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
                }

            }

            echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">
                    <label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . $desc_tootip . '</label>
                    <input type="' . esc_attr( $field['type'] ) . '" class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" ' . implode( ' ', $custom_attributes ) . ' /> ';
            echo '</p>';

        }

        /**
         * Return formatted price.
         * WPML compatible.
         *
         * @since 1.4.0
         * @since 1.6.0 Bug fix. Default currency wholesale price have no currency symbol. WWP-143
         * @access public
         *
         * @param float $price Raw price.
         * @return string Formatted price.
         */
        public static function wwp_formatted_price( $price , $args = array() ) {

            if ( self::is_plugin_active( 'woocommerce-multilingual/wpml-woocommerce.php' ) ) {

                global $woocommerce_wpml;

                if ( !defined( 'WCML_MULTI_CURRENCIES_INDEPENDENT' ) )
                    include_once ( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'wpml-woocommerce' . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'constants.php' );

                if ( $woocommerce_wpml->settings[ 'enable_multi_currency' ] === WCML_MULTI_CURRENCIES_INDEPENDENT ) {

                    /**
                     * On class WCML_Multi_Currency on function _load_filters
                     * If price shown on front end is default currency, WPML will not load its filters
                     * That is it will not load 'wcml_formatted_price' filter so that is why the default currency wholesale price is naked ( No currency symbol and not formatted )
                     * We will do the same, if currency loaded is same as the default currency, then we just do regular woocommerce wc_price
                     */
                    if ( !is_admin() && $woocommerce_wpml->multi_currency->get_client_currency() != get_option('woocommerce_currency') )
                        return apply_filters( 'wcml_formatted_price' , $price );
                    else
                        return wc_price( $price , $args );

                } else
                    return wc_price( $price , $args );

            } else
                return wc_price( $price , $args );

        }

        /**
         * Return price.
         * WPML compatible. Converts price accordingly.
         *
         * @since 1.4.0
         * @access public
         *
         * @param float $price Raw price.
         * @return float Processed price.
         */
        public static function wwp_wpml_price( $price ) {

            if ( self::is_plugin_active( 'woocommerce-multilingual/wpml-woocommerce.php' ) ) {

                global $woocommerce_wpml;

                if ( !defined( 'WCML_MULTI_CURRENCIES_INDEPENDENT' ) )
                    include_once ( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'wpml-woocommerce' . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'constants.php' );

                if ( $woocommerce_wpml->settings[ 'enable_multi_currency' ] === WCML_MULTI_CURRENCIES_INDEPENDENT )
                    return apply_filters( 'woocommerce_adjust_price' , $price );
                else
                    return $price;

            } else
                return $price;

        }

        /**
         * Get product price including tax. WC 2.7.
         *
         * @since 1.3.1
         * @access public
         *
         * @param WC_Product $product Product object.
         * @param array      $ars     Array of arguments data.
         * @return float Product price with tax.
         */
        public static function wwp_get_price_including_tax( $product , $args ) {

            $woocommerce_data = self::get_woocommerce_data();

            if ( version_compare( $woocommerce_data[ 'Version' ] , '3.0.0' , '>=' ) )
                return wc_get_price_including_tax( $product , $args );
            else {

                $qty   = (int) $args['qty'] ? $args[ 'qty' ] : 1;
                $price = $args[ 'price' ];

                return $product->get_price_including_tax( $qty, $price );

            }

        }

        /**
         * Get product price excluding tax. WC 2.7.
         *
         * @since 1.3.1
         * @access public
         *
         * @param WC_Product $product Product object.
         * @param array      $ars     Array of arguments data.
         * @return float Product price with no tax.
         */
        public static function wwp_get_price_excluding_tax( $product , $args ) {

            $woocommerce_data = self::get_woocommerce_data();

            if ( version_compare( $woocommerce_data[ 'Version' ] , '3.0.0' , '>=' ) )
                return wc_get_price_excluding_tax( $product , $args );
            else {

                $qty   = (int) $args['qty'] ? $args[ 'qty' ] : 1;
                $price = $args[ 'price' ];

                return $product->get_price_excluding_tax( $qty , $price );

            }

        }

        /**
         * Get product id. WC 2.7.
         *
         * @since 1.3.1
         * @access public
         *
         * @param WC_Product $product Product object.
         * @return int Product id.
         */
        public static function wwp_get_product_id( $product ) {

            if ( is_a( $product , 'WC_Product' ) ) {

                $woocommerce_data = self::get_woocommerce_data();

                if ( version_compare( $woocommerce_data[ 'Version' ] , '3.0.0' , '>=' ) )
                    return $product->get_id();
                else {

                    switch ( $product->product_type ) {

                        case 'simple':
                        case 'variable':
                        case 'external':
                            return $product->id;
                        case 'variation':
                            return $product->variation_id;
                        default:
                            return apply_filters( 'wwp_third_party_product_id' , 0 , $product );

                    }

                }

            } else {

                error_log( 'WWP Error : wwp_get_product_id helper functions expect parameter $product of type WC_Product.' );
                return 0;

            }

        }

        /**
         * Get variation parent variable product id. WC 2.7.
         *
         * @since 1.3.1
         * @access public
         *
         * @param WC_Product_Variation $variation Variation object.
         * @return int Variable product id.
         */
        public static function wwp_get_parent_variable_id( $variation ) {

            $woocommerce_data = self::get_woocommerce_data();

            if ( version_compare( $woocommerce_data[ 'Version' ] , '3.0.0' , '>=' ) ) {

                if ( self::wwp_get_product_type( $variation ) === 'variation' )
                    return $variation->get_parent_id();
                else {

                    error_log( 'WWP Error: wwp_get_parent_variable_id helper function expect parameter $variation as a product variation.' );
                    return 0;

                }

            } else {

                if ( $variation->product_type === 'variation' )
                    return $variation->parent->id;
                else {

                    error_log( 'WWP Error: wwp_get_parent_variable_id helper function expect parameter $variation as a product variation.' );
                    return 0;

                }

            }

        }

        /**
         * Get product type. WC 2.7.
         *
         * @since 1.3.1
         * @access public
         *
         * @param WC_Product $product Product type.
         * @return string Product type.
         */
        public static function wwp_get_product_type( $product ) {

            if ( is_a( $product , 'WC_Product' ) ) {

                $woocommerce_data = self::get_woocommerce_data();

                if ( version_compare( $woocommerce_data[ 'Version' ] , '3.0.0' , '>=' ) )
                    return $product->get_type();
                else
                    return $product->product_type;

            } else {

                error_log( 'WWP Error : wwp_get_product_type helper functions expect parameter $product of type WC_Product.' );
                return 0;

            }

        }

        /**
         * Get product display price. WC 2.7.
         *
         * @since 1.3.1
         * @access public
         *
         * @param WC_Product $product Product object.
         * @param array      $args    Array of additional data.
         * @return float Product display price.
         */
        public static function wwp_get_product_display_price( $product , $args = array() ) {

            $woocommerce_data = self::get_woocommerce_data();

            if ( version_compare( $woocommerce_data[ 'Version' ] , '3.0.0' , '>=' ) )
                return wc_get_price_to_display( $product , $args );
            else {

                $price = ( isset( $args[ 'price' ] ) && $args[ 'price' ] ) ? $args[ 'price' ] : '';
                $qty   = ( isset( $args[ 'qty' ] ) && $args[ 'qty' ] ) ? $args[ 'qty' ] : 1;

                return $product->get_display_price( $price , $qty );

            }

        }

        /**
         * Match a variation to a given set of attributes using a WP_Query. WC 2.7.
         *
         * @since 1.3.1
         * @access public
         *
         * @param WC_Product_Variable $variable         Variable product.
         * @param array               $match_attributes Attributes to match with.
         * @return int Matched variation id.
         */
        public static function wwp_get_matching_variation( $variable , $match_attributes = array() ) {

            $woocommerce_data = self::get_woocommerce_data();

            if ( version_compare( $woocommerce_data[ 'Version' ] , '3.0.0' , '>=' ) ) {

                $data_store = WC_Data_Store::load( 'product' );
                return $data_store->find_matching_product_variation( $variable , $match_attributes );

            } else
                return $variable->get_matching_variation( $match_attributes );

        }

        /**
         * This is a requirement for 'wwp_get_variable_product_variations' helper function.
         * You see 'get_available_variations' function indirectly calls 'get_price_html', which will then call 'woocommerce_get_price_html'.
         * So what happens is every time we call 'get_available_variations', we fire the 'woocommerce_get_price_html' filter, then our callbacks gets triggered.
         * In short we are executing our callbacks unnecessarily.
         *
         * @since 1.5.0
         * @access public
         *
         * @param boolean $return Boolean flag to determine to either show variation price or not.
         * @return boolean Hard boolean false.
         */
        public static function wwp_hide_woocommerce_show_variation_price( $return ) {

            return false;

        }

        /**
         * Efficient way of getting all variations of a variable product.
         * Please see 'wwp_hide_woocommerce_show_variation_price' above.
         *
         * @since 1.5.0
         * @access public
         *
         * @param WC_Product_Variable $variable_product Variable product object.
         * @return array Array of variable product variations.
         */
        public static function wwp_get_variable_product_variations( $variable_product ) {

            add_filter( 'woocommerce_show_variation_price' , array( 'WWP_Helper_Functions' , 'wwp_hide_woocommerce_show_variation_price' ) );
            $variations = $variable_product->get_available_variations();
            remove_filter( 'woocommerce_show_variation_price' , array( 'WWP_Helper_Functions' , 'wwp_hide_woocommerce_show_variation_price' ) );

            return $variations;

        }

        /**
         * Check validity of a save post action.
         *
         * @since 1.6.0
         * @access public
         *
         * @param int    $post_id   Id of the coupon post.
         * @param string $post_type Post type to check.
         * @return bool True if valid save post action, False otherwise.
         */
        public static function check_if_valid_save_post_action( $post_id , $post_type ) {
    
            if ( get_post_type() != $post_type || empty( $_POST ) || wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) || !current_user_can( 'edit_page' , $post_id ) )
                return false;
            else
                return true;
            
        }

    }

}

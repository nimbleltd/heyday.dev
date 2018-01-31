<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_Wholesale_Role_Shipping_Method' ) ) {

    /**
     * Model that houses the logic of wholesale role shipping methods.
     *
     * @since 1.14.0
     */
    class WWPP_Wholesale_Role_Shipping_Method {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWPP_Wholesale_Role_Shipping_Method.
         *
         * @since 1.14.0
         * @access private
         * @var WWPP_Wholesale_Role_Shipping_Method
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
         * WWPP_Wholesale_Role_Shipping_Method constructor.
         *
         * @since 1.14.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Wholesale_Role_Shipping_Method model.
         */
        public function __construct( $dependencies ) {

            $this->_wwpp_wholesale_roles = $dependencies[ 'WWPP_Wholesale_Roles' ];

        }

        /**
         * Ensure that only one instance of WWPP_Wholesale_Role_Shipping_Method is loaded or can be loaded (Singleton Pattern).
         *
         * @since 1.14.0
         * @access public
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWPP_Wholesale_Role_Shipping_Method model.
         * @return WWPP_Wholesale_Role_Shipping_Method
         */
        public static function instance( $dependencies ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }

        /**
         * Get per user shipping option.
         * 
         * @since 1.16.0
         * @access public
         * 
         * @param int    $user_id User id.
         * @param string $user_wholesale_role User wholesale role.
         * @return boolean|array False if no per user override, array of override data otherwise.
         */
        public function get_per_user_shipping_option( $user_id , $user_wholesale_role ) {
            
            $return_data = false;

            if ( get_user_meta( $user_id , 'wwpp_override_shipping_options' , true ) === 'yes' ) {

                $method_type = get_user_meta( $user_id , 'wwpp_shipping_methods_type' , true );

                if ( $method_type === 'force_free_shipping' ) {

                    $return_data = array(
                        'force_free_shipping'     => 'yes',
                        'shipping_method_mapping' => array()
                    );

                } elseif ( $method_type === 'specify_shipping_methods' ) {

                    $sp               = array();
                    $shipping_zone    = get_user_meta( $user_id , 'wwpp_shipping_zone' , true );
                    $shipping_methods = get_user_meta( $user_id , 'wwpp_shipping_methods' , true );

                    // Check zoned methods
                    if ( !empty( $shipping_methods ) && is_array( $shipping_methods ) ) {

                        $sp = array_map( function( $item ) use ( $user_wholesale_role , $shipping_zone ) {

                            return array(
                                'wholesale_role'                => $user_wholesale_role,
                                'use_non_zoned_shipping_method' => 'no',
                                'shipping_zone'                 => $shipping_zone,
                                'shipping_method'               => $item
                            );

                        } , $shipping_methods );

                    }
                    
                    // Check if user specified non zoned methods
                    if ( get_user_meta( $user_id , 'wwpp_specify_non_zoned_shipping_methods' , true ) === 'yes' ) {

                        $non_zone_methods = get_user_meta( $user_id , 'wwpp_non_zoned_shipping_methods' , true );

                        if ( !empty( $non_zone_methods ) && is_array( $non_zone_methods ) ) {

                            foreach ( $non_zone_methods as $nzm ) {

                                $sp[] = array(
                                    'wholesale_role'                => $user_wholesale_role,
                                    'use_non_zoned_shipping_method' => 'yes',
                                    'non_zoned_shipping_method'     => $nzm
                                );
                                                                
                            }

                        }

                    }

                    // Check if there are mappings, if so, use it
                    if ( !empty( $sp ) )
                        $return_data = array(
                            'force_free_shipping'     => 'no',
                            'shipping_method_mapping' => $sp
                        );
                    
                }

            }

            return $return_data;

        }

        /**
         * Apply appropriate shipping method to products in a cart.
         *
         * @since 1.0.3
         * @since 1.9.1 WooCommerce 2.6.0 have completely overhauled there shipping functionality. Because of this we
         * created 2 different shipping feature for wwpp, one for pre WC 2.6.0 and new one for WC 2.6.0 and above.
         * They are totally different beings, so options for the shipping feature for pre WC 2.6.0 is not compatible
         * with the shipping feature for WC 2.6.0 and above.
         * @since 1.9.4 Add feature to support both zoned and non-zoned shipping methods at the same time
         * @since 1.14.0 Important Note. 
         * We are now not supporting the legacy shipping method of WooCommerce (Shipping method prior to WC 2.6)
         * From now on we will only support the new shipping methods introduced on WC 2.6.
         * We are now removing our old code for our old shipping mapping for the old pre WC 2.6 shipping methods.
         * Therefore in effect, we have lost our integration with these third party plugins
         * WooCommerce Table Rate Shipping : this is actually the shipping plugin that gets baked into WooCommerce 2.6.0, so no prob here.
         * WooCommerce Table Rate Shipping ( Code Canyon Version ) : We are integrating with this on our pre WC 2.6.0 codebase. We didn't re integrate it on WC 2.6.0 since the problem that
         *                                                           this plugin tries to solve is already solve right in to woocommerce.
         * Table Rate Shipping Plus ( Mango Hour )                 : Same as the above comment with code canyon one.
         * With this in mind, people coming from WC 2.6 and update to this version of WWPP have no prob at all.
         * People coming from WC 2.5 and update to this version of WWPP, they have to sort out there problem first on updating to WC 2.6, then updating there other plugins, then update WWPP.
         * Coz if they are still on WC 2.5, then they will have lots of problems ( From WC dropping the old shipping method, from other plugins that already moved on, etc... )
         * Alternatively we advice them not to update to WWPP 1.14.0 if they are still on WC 2.5 and below.
         * We still support 'Use Non-Zoned Shipping Methods' though, the pre WC 2.6 shipping methods that used to stand on their own.
         * Shipping methods introduced on WC 2.6 can not stand on their own and is dependent on the zone.
         * @since 1.14.8 If the package rates contains free shipping, it will force use free shipping. This is only for wholesale customers.
         * @since 1.16.0 Add support for per user shipping options override.
         * @access public
         *  
         * @param array $package_rates Package rates.
         * @param array $package       Package.
         * @return array Filtered package rates data.
         */
        public function apply_appropriate_shipping_method( $package_rates , $package ) {

            // Changes in shipping functionality due to WooCommerce 2.6.0 major overhaul on their shipping functionality.
            // Further changes, now we allow mapping of both zoned and non-zoned mappings
            $user_wholesale_role    = $this->_wwpp_wholesale_roles->getUserWholesaleRole();
            $user_shipping_override = !empty( $user_wholesale_role ) ? $this->get_per_user_shipping_option( get_current_user_id() , $user_wholesale_role[ 0 ] ) : false;

            if ( $user_shipping_override === false ) {

                $wholesale_user_free_shipping      = get_option( 'wwpp_settings_wholesale_users_use_free_shipping' );
                $wholesale_zone_mapping            = get_option( WWPP_OPTION_WHOLESALE_ROLE_SHIPPING_ZONE_METHOD_MAPPING , array() );

            } else {

                $wholesale_user_free_shipping      = $user_shipping_override[ 'force_free_shipping' ];
                $wholesale_zone_mapping            = $user_shipping_override[ 'shipping_method_mapping' ];

            }

            // Non-Zoned Shipping Methods
            $non_zoned_shipping_methods = array();
            $wc_shipping_methods        = WC()->shipping->load_shipping_methods();

            foreach ( $wc_shipping_methods as $shipping_method ) {

                if ( !$shipping_method->supports( 'shipping-zones' ) && $shipping_method->enabled == 'yes' )
                    $non_zoned_shipping_methods[ $shipping_method->id ] = $shipping_method;

            }

            // Zoned Shipping Methods
            $matched_shipping_zone = WC_Shipping_Zones::get_zone_matching_package( $package );
            $zone_shipping_methods = $matched_shipping_zone->get_shipping_methods();

            $filtered_package_rates = array();
            $role_has_mapping       = false;

            $shipping_methods_blocked_for_non_wholesale_users = $this->_get_shipping_methods_blocked_by_per_user_shipping_override( 'hide_from_non_wholesale_users' );
            $shipping_methods_blocked_for_all_other_users     = $this->_get_shipping_methods_blocked_by_per_user_shipping_override( 'hide_from_all_other_users' );

            if ( !empty( $wholesale_zone_mapping ) && !empty( $user_wholesale_role ) ) {

                foreach ( $wholesale_zone_mapping as $mapping ) {

                    if ( $mapping[ 'wholesale_role' ] == $user_wholesale_role[ 0 ] &&
                         $mapping[ 'use_non_zoned_shipping_method' ] == 'yes' &&
                         !empty( $non_zoned_shipping_methods ) && is_array( $non_zoned_shipping_methods ) &&
                         array_key_exists( $mapping[ 'non_zoned_shipping_method' ] , $non_zoned_shipping_methods ) &&
                         isset( $non_zoned_shipping_methods[ $mapping[ 'non_zoned_shipping_method' ] ] ) ) {

                        // Non-zoned shipping method

                        $role_has_mapping = true;

                        // Even if non-zoned shipping method is mapped, we still check for the following:
                        // Is it enabled
                        // Does the current package meets the method's requirements
                        // Both of these must be passed in order for this non-zoned shipping method to be applied

                        $sm = new $non_zoned_shipping_methods[ $mapping[ 'non_zoned_shipping_method' ] ];

                        if ( $sm->enabled == 'yes' && $sm->is_available( $package ) ) {

                            $sm->calculate_shipping( $package );

                            if ( !empty( $sm->rates ) && is_array( $sm->rates ) ) {

                                foreach ( $sm->rates as $rate )
                                    $filtered_package_rates[ $rate->id ] = $rate;

                            }

                        }

                    } elseif ( $mapping[ 'wholesale_role' ] == $user_wholesale_role[ 0 ] &&
                               $mapping[ 'use_non_zoned_shipping_method' ] == 'no' &&
                               ( int ) $mapping[ 'shipping_zone' ] == ( int ) $matched_shipping_zone->get_id() &&
                               !empty( $zone_shipping_methods ) && is_array( $zone_shipping_methods ) &&
                               array_key_exists( $mapping[ 'shipping_method' ] , $zone_shipping_methods ) &&
                               isset( $zone_shipping_methods[ $mapping[ 'shipping_method' ] ] ) ) {

                        // Zoned shipping method

                        $role_has_mapping = true;

                        // We still check if this package is qualified for this zoned shipping method

                        if ( $zone_shipping_methods[ $mapping[ 'shipping_method' ] ]->is_available( $package ) ) {

                            $zone_shipping_methods[ $mapping[ 'shipping_method' ] ]->calculate_shipping( $package );

                            if ( !empty( $zone_shipping_methods[ $mapping[ 'shipping_method' ] ]->rates ) && is_array( $zone_shipping_methods[ $mapping[ 'shipping_method' ] ]->rates ) ) {

                                foreach ( $zone_shipping_methods[ $mapping[ 'shipping_method' ] ]->rates as $rate )
                                    $filtered_package_rates[ $rate->id ] = $rate;

                            }

                        }

                    }

                }

                if ( $role_has_mapping ) {
                    
                    // If role has mapping, then we return the filtered package rates whether its empty or not
                    // Simply because it only means that if the filtered package rates is empty, then meaning
                    // the current wholesale user did not qualify for the mapped methods so it got an empty filtered package rates
                    // If we don't allow empty rates, then it will just use the shipping method set by woocommerce
                    // rendering void the purpose of the mapping.

                    $package_rates = $filtered_package_rates;

                } else {

                    if ( $wholesale_user_free_shipping === 'yes' )
                        $package_rates = $this->_add_free_shipping_method_to_wholesale_customer( $package_rates , $package );
                    elseif ( !empty( $shipping_methods_blocked_for_all_other_users ) ) {

                        foreach ( WC()->shipping()->load_shipping_methods( $package ) as $shipping_method ) {
                            
                            // Check if shipping method is blocked by per wholesale user shipping override

                            if ( $this->_is_shipping_method_blocked_by_per_user_override( $shipping_method , $shipping_methods_blocked_for_all_other_users ) ) {

                                $force_allow_shipping_method =  apply_filters( 'wwpp_force_allow_blocked_shipping_method_for_all_other_users' , false , $shipping_method , $shipping_methods_blocked_for_all_other_users );
                                
                                if ( !$force_allow_shipping_method )
                                    continue;

                            }

                            // Shipping instances need an ID
                            if ( ! $shipping_method->supports( 'shipping-zones' ) || $shipping_method->get_instance_id() )
                                $filtered_package_rates = $filtered_package_rates + $shipping_method->get_rates_for_package( $package ); // + instead of array_merge maintains numeric keys

                        }

                        // Yes we allow empty package rates.
                        // If all shipping methods are blocked by per user override, 
                        // Then we allow it, this is a user misconfiguration.
                        $package_rates = $filtered_package_rates;

                    }

                }

            } elseif ( !empty( $wholesale_zone_mapping ) && empty( $user_wholesale_role ) ) {

                $mapped_methods_for_wholesale_only = get_option( 'wwpp_settings_mapped_methods_for_wholesale_users_only' );

                if ( $mapped_methods_for_wholesale_only == 'yes' || !empty( $shipping_methods_blocked_for_non_wholesale_users ) || !empty( $shipping_methods_blocked_for_all_other_users ) ) {

                    // Prevent non-wholesale users from using mapped shipping zone methods.

                    foreach ( WC()->shipping()->load_shipping_methods( $package ) as $shipping_method ) {

                        // Check if shipping method is mapped
                        if ( $mapped_methods_for_wholesale_only == 'yes' && $this->_is_shipping_method_mapped( $shipping_method , $wholesale_zone_mapping ) !== false ) {

                            // Ok so shipping method is mapped, but we still add a way for end users to override the behavior
                            // of skipping this mapped method to the non-wholesale customers
                            $force_allow_shipping_method = apply_filters( 'wwpp_force_allow_mapped_shipping_method_for_non_wholesale' , false , $shipping_method , $wholesale_zone_mapping );

                            if ( !$force_allow_shipping_method )
                                continue;

                        }

                        // Check if shipping method is blocked by per wholesale user shipping override

                        if ( $this->_is_shipping_method_blocked_by_per_user_override( $shipping_method , $shipping_methods_blocked_for_non_wholesale_users ) ) {

                            $force_allow_shipping_method =  apply_filters( 'wwpp_force_allow_blocked_shipping_method_for_non_wholesale_users' , false , $shipping_method , $shipping_methods_blocked_for_non_wholesale_users );

                            if ( !$force_allow_shipping_method )
                                continue;

                        }

                        if ( $this->_is_shipping_method_blocked_by_per_user_override( $shipping_method , $shipping_methods_blocked_for_all_other_users ) ) {

                            $force_allow_shipping_method =  apply_filters( 'wwpp_force_allow_blocked_shipping_method_for_all_other_users' , false , $shipping_method , $shipping_methods_blocked_for_all_other_users );
                            
                            if ( !$force_allow_shipping_method )
                                continue;

                        }

                        // Shipping instances need an ID
                        if ( ! $shipping_method->supports( 'shipping-zones' ) || $shipping_method->get_instance_id() )
                            $filtered_package_rates = $filtered_package_rates + $shipping_method->get_rates_for_package( $package ); // + instead of array_merge maintains numeric keys

                    }

                    // Yes we allow empty package rates.
                    // What if all shipping methods of a zone is mapped? if we don't allow empty rates
                    // Then WC will just used those methods anyways right?
                    // If all methods of a zone is mapped, meaning no method to use, meaning no rates.
                    // This is of course a misconfiguration by the user.
                    $package_rates = $filtered_package_rates;

                }

            } elseif ( empty( $wholesale_zone_mapping ) && empty( $user_wholesale_role ) ) {

                if ( !empty( $shipping_methods_blocked_for_non_wholesale_users ) || !empty( $shipping_methods_blocked_for_all_other_users ) ) {

                    // Prevent non-wholesale users from per user blocked shipping methods.

                    foreach ( WC()->shipping()->load_shipping_methods( $package ) as $shipping_method ) {

                        // Check if shipping method is blocked by per wholesale user shipping override

                        if ( $this->_is_shipping_method_blocked_by_per_user_override( $shipping_method , $shipping_methods_blocked_for_non_wholesale_users ) ) {

                            $force_allow_shipping_method =  apply_filters( 'wwpp_force_allow_mapped_shipping_method_for_non_wholesale_per_user_override' , false , $shipping_method , $shipping_methods_blocked_for_non_wholesale_users );

                            if ( !$force_allow_shipping_method )
                                continue;

                        }

                        if ( $this->_is_shipping_method_blocked_by_per_user_override( $shipping_method , $shipping_methods_blocked_for_all_other_users ) ) {

                            $force_allow_shipping_method =  apply_filters( 'wwpp_force_allow_blocked_shipping_method_for_all_other_users' , false , $shipping_method , $shipping_methods_blocked_for_all_other_users );
                            
                            if ( !$force_allow_shipping_method )
                                continue;

                        }

                        // Shipping instances need an ID
                        if ( ! $shipping_method->supports( 'shipping-zones' ) || $shipping_method->get_instance_id() )
                            $filtered_package_rates = $filtered_package_rates + $shipping_method->get_rates_for_package( $package ); // + instead of array_merge maintains numeric keys

                    }

                    // Yes we allow empty package rates.
                    // If all shipping methods are blocked by per user override, 
                    // Then we allow it, this is a user misconfiguration.
                    $package_rates = $filtered_package_rates;

                }

            } elseif ( empty( $wholesale_zone_mapping ) && !empty( $user_wholesale_role ) ) {

                if ( !empty( $shipping_methods_blocked_for_all_other_users ) ) {

                    foreach ( WC()->shipping()->load_shipping_methods( $package ) as $shipping_method ) {
                        
                        // Check if shipping method is blocked by per wholesale user shipping override

                        if ( $this->_is_shipping_method_blocked_by_per_user_override( $shipping_method , $shipping_methods_blocked_for_all_other_users ) ) {

                            $force_allow_shipping_method = apply_filters( 'wwpp_force_allow_blocked_shipping_method_for_all_other_users' , false , $shipping_method , $shipping_methods_blocked_for_all_other_users );
                            
                            if ( !$force_allow_shipping_method )
                                continue;

                        }

                        // Shipping instances need an ID
                        if ( ! $shipping_method->supports( 'shipping-zones' ) || $shipping_method->get_instance_id() )
                            $filtered_package_rates = $filtered_package_rates + $shipping_method->get_rates_for_package( $package ); // + instead of array_merge maintains numeric keys

                    }

                    // Yes we allow empty package rates.
                    // If all shipping methods are blocked by per user override, 
                    // Then we allow it, this is a user misconfiguration.
                    $package_rates = $filtered_package_rates;

                } else {

                    if ( $wholesale_user_free_shipping === 'yes' )
                        $package_rates = $this->_add_free_shipping_method_to_wholesale_customer( $package_rates , $package );

                }

            }

            // Force use free shipping if applicable
            $final_package_rates = array();
            
            if ( !empty( $user_wholesale_role ) && $wholesale_user_free_shipping === 'yes' ) {

                foreach ( $package_rates as $pr_key => $pr ) {

                    if ( $pr_key === 'free_shipping:WWPP' ) {

                        $final_package_rates[ $pr_key ] = $pr;
                        break;

                    }

                }

            }

            return empty( $final_package_rates ) ? $package_rates : $final_package_rates;

        }

        /**
         * Get the shipping methods of a given shipping zone.
         *
         * @since 1.9.1
         * @since 1.14.0 Refactor codebase and move to its proper model.
         * @access public
         *
         * @param null|int $zone_id Zone id.
         * @return array Operation status.
         */
        public function wwpp_get_zone_shipping_methods( $zone_id = null ) {

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
                $zone_id = (int) $_POST[ 'zone_id' ];

            $shipping_zone    = WC_Shipping_Zones::get_zone( $zone_id );
            $shipping_methods = array();

            foreach ( $shipping_zone->get_shipping_methods() as $sm )
                $shipping_methods[ $sm->instance_id ] = $sm->title;

            $response = array( 'status' => 'success' , 'shipping_methods' => $shipping_methods );

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

                @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
                echo wp_json_encode( $response );
                wp_die();

            } else
                return $response;

        }

        /**
         * Add new wholesale / shipping zone ( shipping method ) mapping.
         *
         * @since 1.9.1
         * @since 1.14.0 Refactor codebase and move to its proper model.
         * @access public
         *
         * @param null|array $mapping Mapping data.
         * @return array Operation status.
         */
        public function wwpp_add_wholesale_zone_mapping( $mapping = null ) {

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
                $mapping = $_POST[ 'mapping' ];

            $wholesale_zone_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_SHIPPING_ZONE_METHOD_MAPPING , array() );

            if ( $this->_check_if_mapping_exists( $mapping , $wholesale_zone_mapping ) !== false ) {

                $response = array(
                    'status'        => 'fail',
                    'error_message' => __( 'The mapping you wish to add already exists' , 'woocommerce-wholesale-prices-premium' )
                );

            } else {

                // Clean $mapping of unnecessary data ( WWPP-186 )
                unset( $mapping[ 'wholesale_role_text' ] );

                if ( $mapping[ 'use_non_zoned_shipping_method' ] == 'yes' ) {

                    unset( $mapping[ 'non_zoned_shipping_method_text' ] );

                } else {

                    unset( $mapping[ 'shipping_zone_text' ] );
                    unset( $mapping[ 'shipping_method_text' ] );

                }

                $wholesale_zone_mapping[] = $mapping;
                update_option( WWPP_OPTION_WHOLESALE_ROLE_SHIPPING_ZONE_METHOD_MAPPING , $wholesale_zone_mapping );
                end( $wholesale_zone_mapping );
                $mapping_index = key( $wholesale_zone_mapping );

                $response = array(
                    'status'        => 'success',
                    'mapping_index' => $mapping_index
                );

            }

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

                @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
                echo wp_json_encode( $response );
                wp_die();

            } else
                return $response;

        }

        /**
         * Edit wholesale / shipping zone ( shipping method ) mapping.
         *
         * @since 1.9.1
         * @since 1.14.0 Refactor codebase and move to its proper model.
         * @access public
         *
         * @param null|int   $index   Mapping entry index.
         * @param null|array $mapping Mapping data.
         * @return array Operation status.
         */
        public function wwpp_edit_wholesale_zone_mapping( $index = null , $mapping = null ) {

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

                $index   = $_POST[ 'index' ];
                $mapping = $_POST[ 'mapping' ];

            }

            $wholesale_zone_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_SHIPPING_ZONE_METHOD_MAPPING , array() );
            $mapping_exists_check   = $this->_check_if_mapping_exists( $mapping , $wholesale_zone_mapping );

            if ( !array_key_exists( $index , $wholesale_zone_mapping ) ) {

                $response = array(
                    'status'        => 'fail',
                    'error_message' => __( 'The mapping you wish to edit does not exists' , 'woocommerce-wholesale-prices-premium' )
                );

            } elseif ( $mapping_exists_check !== false && $mapping_exists_check != $index ) {

                $response = array(
                    'status'        => 'fail',
                    'error_message' => __( 'The new mapping data you want to save duplicates with another existing mapping' , 'woocommerce-wholesale-prices-premium' )
                );

            } else {

                // Clean $mapping of unnecessary data ( WWPP-186 )
                unset( $mapping[ 'wholesale_role_text' ] );

                if ( $mapping[ 'use_non_zoned_shipping_method' ] == 'yes' ) {

                    unset( $mapping[ 'non_zoned_shipping_method_text' ] );

                } else {

                    unset( $mapping[ 'shipping_zone_text' ] );
                    unset( $mapping[ 'shipping_method_text' ] );

                }

                $wholesale_zone_mapping[ $index ] = $mapping;
                update_option( WWPP_OPTION_WHOLESALE_ROLE_SHIPPING_ZONE_METHOD_MAPPING , $wholesale_zone_mapping );
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
         * Delete wholesale / shipping zone ( shipping method ) mapping.
         *
         * @since 1.9.1
         * @since 1.14.0 Refactor codebase and move to its proper model.
         * @access public
         *
         * @param null|int $index Mapping entry index.
         * @return array Operation status.
         */
        public function wwpp_delete_wholesale_zone_mapping( $index = null ) {

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
                $index = $_POST[ 'index' ];

            $wholesale_zone_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_SHIPPING_ZONE_METHOD_MAPPING , array() );

            if ( !array_key_exists( $index , $wholesale_zone_mapping ) ) {

                $response = array(
                    'status'        => 'fail',
                    'error_message' => __( 'The mapping you wish to delete does not exists' , 'woocommerce-wholesale-prices-premium' )
                );

            } else {

                unset( $wholesale_zone_mapping[ $index ] );
                update_option( WWPP_OPTION_WHOLESALE_ROLE_SHIPPING_ZONE_METHOD_MAPPING , $wholesale_zone_mapping );
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
        | Helpers
        |---------------------------------------------------------------------------------------------------------------
        */

        /**
         * Check if a mapping already existed on wholesale zone mapping.
         *
         * @since 1.9.1
         * @since 1.14.0 Refactor codebase and move to its proper model.
         * @access public
         *
         * @param array $mapping           Wholesale shipping mapping entry data.
         * @param array $wholesale_mapping Wholesale shipping mapping data.
         * @return bool True if exists, false otherwise.
         */
        private function _check_if_mapping_exists( $mapping , $wholesale_mapping ) {

            if ( $mapping[ 'use_non_zoned_shipping_method' ] == 'yes' ) {

                foreach ( $wholesale_mapping as $index => $wm ) {
                    
                    if ( !isset( $wm[ 'wholesale_role' ] ) || !isset( $wm[ 'non_zoned_shipping_method' ] ) ||
                         !isset( $mapping[ 'wholesale_role' ] ) || !isset( $mapping[ 'non_zoned_shipping_method' ] ) )
                         continue;
                    
                    if ( $mapping[ 'wholesale_role' ] == $wm[ 'wholesale_role' ] &&
                         $mapping[ 'non_zoned_shipping_method' ] == $wm[ 'non_zoned_shipping_method' ] )
                        return $index;
                    
                }

            } else {

                foreach ( $wholesale_mapping as $index => $wm ) {

                    if ( !isset( $wm[ 'wholesale_role' ] ) || !isset( $wm[ 'shipping_zone' ] ) || !isset( $wm[ 'shipping_method' ] ) ||
                         !isset( $mapping[ 'wholesale_role' ] ) || !isset( $mapping[ 'shipping_zone' ] ) || !isset( $mapping[ 'shipping_method' ] ) )
                         continue;
                    
                    if ( $mapping[ 'wholesale_role' ]  == $wm[ 'wholesale_role' ] &&
                         $mapping[ 'shipping_zone' ]   == $wm[ 'shipping_zone' ]  &&
                         $mapping[ 'shipping_method' ] == $wm[ 'shipping_method' ] )
                        return $index;
                    
                }

            }

            return false;

        }

        /**
         * Check if a shipping zone method is mapped.
         *
         * @since 1.9.1
         * @since 1.9.4 Check if both zoned and non-zoned shipping methods are mapped.
         * @since 1.14.0 Refactor codebase and move to its proper model.
         * @access public
         *
         * @param object $shipping_method   Shipping method object.
         * @param array  $wholesale_mapping Wholesale shipping mapping data.
         * @return bool False if not mapped, index of the mapping entry if mapped.
         */
        private function _is_shipping_method_mapped( $shipping_method , $wholesale_mapping ) {

            if ( !$shipping_method->supports( 'shipping-zones' ) ) {

                // Non-Zoned Shipping Method

                foreach ( $wholesale_mapping as $index => $wm ) {

                    if ( $wm[ 'use_non_zoned_shipping_method' ] == 'yes' && $wm[ 'non_zoned_shipping_method' ] == $shipping_method->id ) {

                        return $index;
                        break;

                    }

                }

            } elseif ( $shipping_method->get_instance_id() ) {

                // Zoned Shipping Method

                foreach ( $wholesale_mapping as $index => $wm ) {

                    if ( $wm[ 'use_non_zoned_shipping_method' ] == 'no' && $wm[ 'shipping_method' ] == $shipping_method->get_instance_id() ) {

                        return $index;
                        break;

                    }

                }

            }

            return false;

        }

        /**
         * Check if a given shipping method is blocked by per user shipping option override.
         * 
         * @since 1.16.0
         * @access public
         * 
         * @param object $shipping_method Shipping method object.
         * @param array  $blocked_methods Array of blocked shipping methods that is from per wholesale user shipping options override.
         * @return bool False if not blocked, True otherwise.
         */
        private function _is_shipping_method_blocked_by_per_user_override( $shipping_method , $blocked_methods ) {

            $method_identifier = null;

            if ( !$shipping_method->supports( 'shipping-zones' ) )
                $method_identifier = $shipping_method->id; // Non zoned shipping method
            elseif ( $shipping_method->get_instance_id() )
                $method_identifier = $shipping_method->get_instance_id(); // Zoned shipping method

            return in_array( $method_identifier , $blocked_methods );

        }

        /**
         * Get all shipping methods that are blocked for non wholesale customers by per wholesale user shipping options override.
         * 
         * @since 1.16.0
         * @access private
         * 
         * @param string $meta_value Meta value.
         * @return array Array of shipping methods that are blocked for other wholesale users.
         */
        private function _get_shipping_methods_blocked_by_per_user_shipping_override( $meta_value ) {

            global $wpdb;

            $combined_arrays = array();

            $result = $wpdb->get_col( "SELECT DISTINCT( meta_value )
                                    FROM $wpdb->usermeta 
                                    WHERE ( meta_key = 'wwpp_shipping_methods' OR meta_key = 'wwpp_non_zoned_shipping_methods' )
                                    AND user_id IN (

                                        SELECT user_id
                                        FROM $wpdb->usermeta
                                        WHERE meta_key = 'wwpp_hide_selected_methods_from_others'
                                        AND meta_value = '" . $meta_value . "'

                                    )" );
            
            if ( !empty( $result ) && is_array( $result ) )
                foreach ( $result as $arr )
                    $combined_arrays =  array_merge( $combined_arrays , unserialize( $arr ) );
            
            return $combined_arrays;
            
        }
        
        /**
         * Add free shipping to the list of available shipping methods for the current wholesale user.
         *
         * @since 1.14.8
         * @access private
         *
         * @param array $package_rates Package rates.
         * @param array $package       Package.
         * @return array Filtered package rates data.
         */
        private function _add_free_shipping_method_to_wholesale_customer( $package_rates , $package ) {

            $title = get_option( 'wwpp_dynamic_free_shipping_title' );
            if ( empty( $title ) )
                $title = __( 'Free Shipping' , 'woocommerce-wholesale-prices-premium' );

            $sm = new WC_Shipping_Free_Shipping();
            $sm->enabled     = 'yes';
            $sm->title       = $title;
            $sm->instance_id = 'WWPP';

            $sm->calculate_shipping( $package );

            if ( !empty( $sm->rates ) && is_array( $sm->rates ) )
                foreach ( $sm->rates as $rate )
                    $package_rates[ $rate->id ] = $rate;
            
            return $package_rates;

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
            
            add_action( "wp_ajax_wwpp_get_zone_shipping_methods"     , array( $this , 'wwpp_get_zone_shipping_methods' ) );
            add_action( "wp_ajax_wwpp_add_wholesale_zone_mapping"    , array( $this , 'wwpp_add_wholesale_zone_mapping' ) );
            add_action( "wp_ajax_wwpp_edit_wholesale_zone_mapping"   , array( $this , 'wwpp_edit_wholesale_zone_mapping' ) );
            add_action( "wp_ajax_wwpp_delete_wholesale_zone_mapping" , array( $this , 'wwpp_delete_wholesale_zone_mapping' ) );

        }

        /**
         * Execute model.
         *
         * @since 1.14.0
         * @access public
         */
        public function run() {
            
            add_filter( 'woocommerce_package_rates' , array( $this , 'apply_appropriate_shipping_method' ) , 10 , 2 );

            add_action( 'init' , array( $this , 'register_ajax_handler' ) );

            // WC_Tax::get_tax_classes(); wont trigger 'woocommerce_product_get_tax_class' which is good for us
            // add_filter( 'woocommerce_product_get_tax_class' , function( $tax_class , $product ) {

            //     error_log( 'woocommerce_product_get_tax_class triggered' );

            // } , 10 , 2 );
            
            // error_log( get_option( 'woocommerce_shipping_tax_class' ) );

            // add_filter( 'option_woocommerce_shipping_tax_class' , function( $option_value ) {

            //     // global $wc_wholesale_prices;
                
            //     // if ( $wc_wholesale_prices && is_a( $wc_wholesale_prices , 'WooCommerceWholeSalePrices' ) ) {

            //     //     $current_user_wholesale_roles = $wc_wholesale_prices->wwp_wholesale_roles->getUserWholesaleRole();
                    
            //     //     if ( in_array( 'wholesale_role_you_want_to_check' , $current_user_wholesale_roles ) )
            //     //         return 'tax-class-name';

            //     // }

            //     return 'wkwkwkwk';
            //     // return $option_value;

            // } , 10 , 1 );

            // error_log( get_option( 'woocommerce_shipping_tax_class' ) );

            // // woocommerce_shipping_tax_class






            // WC_Tax::get_tax_classes();
            // woocommerce_product_get_tax_class
            // woocommerce_shipping_tax




            // get_shipping_classes()
            // add_filter( 'woocommerce_get_shipping_classes' , function( $shipping_classes ) {
                
            //     global $wc_wholesale_prices;

            //     if ( $wc_wholesale_prices && is_a( $wc_wholesale_prices , 'WooCommerceWholeSalePrices' ) ) {

            //         $current_user_wholesale_roles = $wc_wholesale_prices->wwp_wholesale_roles->getUserWholesaleRole();
                    
            //         if ( in_array( 'wholesale_role_you_want_to_check' , $current_user_wholesale_roles ) )
            //             foreach ( $shipping_classes as $shipping_class )
            //                 if ( $shipping_class->slug === 'shipping_class_you_added_that_is_for_wholesale_customer_only' )
            //                     return array( $shipping_class ); // Yeah we need to wrap it in an array

            //     }

            //     return $shipping_class;

            // } , 10 , 1 );


            // add_filter( 'woocommerce_order_get_shipping_tax', 'specialWholesaleTaxClass', 1, 2 );
            // add_filter( 'woocommerce_order_amount_shipping_tax', 'specialWholesaleTaxClass', 1, 2 );

        }

    }

}

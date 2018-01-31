<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWPP_Helper_Functions' ) ) {

    /**
     * Model that house various generic plugin helper functions.
     *
     * @since 1.12.8
     */
    final class WWPP_Helper_Functions {
        
        /**
         * Check if specific user is wwpp tax exempted.
         * 
         * @since 1.16.0
         * @access public
         * 
         * @param string $user_wholesale_role User wholesale role.
         * @param int    $user_id             User id.
         * @return boolean True if wwpp tax exempted, False otherwise.
         */
        public static function is_user_wwpp_tax_exempted( $user_id , $user_wholesale_role ) {
            
            $wwpp_tax_exempted = get_option( 'wwpp_settings_tax_exempt_wholesale_users' );
            
            $wholesale_role_tax_option_mapping = get_option( WWPP_OPTION_WHOLESALE_ROLE_TAX_OPTION_MAPPING , array() );
            if ( !is_array( $wholesale_role_tax_option_mapping ) )
                $wholesale_role_tax_option_mapping = array();

            if ( array_key_exists( $user_wholesale_role , $wholesale_role_tax_option_mapping ) )
                $wwpp_tax_exempted = $wholesale_role_tax_option_mapping[ $user_wholesale_role ][ 'tax_exempted' ];

            $user_tax_exempted = get_user_meta( $user_id , 'wwpp_tax_exemption' , true );
            if ( $user_tax_exempted !== 'global' && in_array( $user_tax_exempted , array( 'yes' , 'no' ) ) )
                $wwpp_tax_exempted = $user_tax_exempted;

            return $wwpp_tax_exempted;

        }
        
    }

}

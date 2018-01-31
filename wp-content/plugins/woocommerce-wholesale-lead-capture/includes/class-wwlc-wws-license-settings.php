<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WWLC_WWS_License_Settings' ) ) {

    class WWLC_WWS_License_Settings {

        /*
        |--------------------------------------------------------------------------
        | Class Properties
        |--------------------------------------------------------------------------
        */

        /**
         * Property that holds the single main instance of WWLC_WWS_License_Settings.
         *
         * @since 1.6.3
         * @access private
         * @var WWLC_WWS_License_Settings
         */
        private static $_instance;

        /*
        |--------------------------------------------------------------------------
        | Class Methods
        |--------------------------------------------------------------------------
        */

        /**
         * WWLC_WWS_License_Settings constructor.
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWLC_WWS_License_Settings model.
         *
         * @access public
         * @since 1.6.3
         */
        public function __construct( $dependencies ) {}

        /**
         * Ensure that only one instance of WWLC_WWS_License_Settings is loaded or can be loaded (Singleton Pattern).
         *
         * @param array $dependencies Array of instance objects of all dependencies of WWLC_WWS_License_Settings model.
         *
         * @return WWLC_WWS_License_Settings
         * @since 1.6.3
         */
        public static function instance( $dependencies = null ) {

            if ( !self::$_instance instanceof self )
                self::$_instance = new self( $dependencies );

            return self::$_instance;

        }

        /**
         * Save wwlc license details.
         *
         * @param null $license_details
         * @return bool
         *
         * @since 1.0.1
         */
        public function wwlc_save_license_details( $license_details = null ) {

            if( defined( 'DOING_AJAX' ) && DOING_AJAX )
                $license_details = $_POST[ 'licenseDetails' ];

            update_option( WWLC_OPTION_LICENSE_EMAIL , sanitize_text_field( trim( $license_details[ 'license_email' ] ) ) );
            update_option( WWLC_OPTION_LICENSE_KEY , sanitize_text_field( trim( $license_details[ 'license_key' ] ) ) );

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

                header( 'Content-Type: application/json' ); // specify we return json
                echo json_encode( array(
                    'status'    =>  'success',
                ) );
                die();

            } else return true;

        }

    }

}
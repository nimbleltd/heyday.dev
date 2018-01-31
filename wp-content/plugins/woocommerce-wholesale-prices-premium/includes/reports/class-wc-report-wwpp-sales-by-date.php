<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WC_Report_WWPP_Sales_By_Date' ) ) {

    include_once ( WP_PLUGIN_DIR . '/woocommerce/includes/admin/reports/class-wc-report-sales-by-date.php' );

    /**
     * Model that handles the logic of wholesale sales by date.
     * 
     * We name the class with prepend of 'WC_Report', this is intentional.
     * Purpose is so we hook smoothly on this filter 'wc_admin_reports_path'.
     *
     * @since 1.13.0
     */
    class WC_Report_WWPP_Sales_By_Date extends WC_Report_Sales_By_Date {

        /**
         * Model that houses the logic of retrieving information relating to wholesale role/s of a user.
         *
         * @since 1.13.0
         * @access private
         * @var WWPP_Wholesale_Roles
         */
        private $_wwpp_wholesale_roles;

        /**
         * Array of registered wholesale roles.
         *
         * @since 1.13.0
         * @access private
         * @var array
         */
        private $_registered_wholesale_roles;

        public function __construct() {

            // Parent class has no constructor

            $this->_wwpp_wholesale_roles       = WWP_Wholesale_Roles::getInstance();
            $this->_registered_wholesale_roles = $this->_wwpp_wholesale_roles->getAllRegisteredWholesaleRoles();

        }

        /**
         * Filter ther report query to only retrieve wholesale orders.
         * As of v1.13.0 wholesale orders means orders made by wholesale customers.
         * It does not take in to account if the items in the order is indeed wholesale priced.
         * As long as the customer making the order is have a wholesale role, then the order is considered as wholesale order.
         *
         * @since 1.13.0
         * @access public
         * 
         * @param array $query Array of sql query data.
         * @return array Filtered array of sql query data.
         */
        public function filter_report_query( $query ) {

            global $wpdb;

            $wwpp_where_query = " AND posts.ID IN (
                SELECT $wpdb->postmeta.post_id FROM $wpdb->postmeta 
                WHERE $wpdb->postmeta.meta_key = '_billing_email'
                AND $wpdb->postmeta.meta_value IN (
                    SELECT $wpdb->users.user_email FROM $wpdb->users 
                    INNER JOIN $wpdb->usermeta
                    ON $wpdb->users.ID = $wpdb->usermeta.user_id
                    WHERE $wpdb->usermeta.meta_key = '" . $wpdb->prefix . "capabilities'
                    AND (";
            
            $counter = 1;
            foreach ( $this->_registered_wholesale_roles as $role_key => $role_data ) {

                $wwpp_where_query .= " $wpdb->usermeta.meta_value LIKE '%$role_key%' ";

                if ( $counter < count( $this->_registered_wholesale_roles ) )
                    $wwpp_where_query .= " OR ";

                $counter++;

            }
            
            $wwpp_where_query .= ") ) )";

            $query[ 'where' ] .= $wwpp_where_query;

            return $query;

        }

        /**
         * Output the report.
         * Override the parent's output report function.
         * We add hooks before and after the report is outputed to alter the sql query.
         *
         * @since 1.13.0
         * @access public
         */
        public function output_report() {

            add_filter( 'woocommerce_reports_get_order_report_query' , array( $this , 'filter_report_query' ) , 999 , 1 );

            parent::output_report();

            remove_filter( 'woocommerce_reports_get_order_report_query' , array( $this , 'filter_report_query' ) , 999 );

        }

    }

}
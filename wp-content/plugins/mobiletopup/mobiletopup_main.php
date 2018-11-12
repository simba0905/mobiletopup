<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              korn@mobiletopup.com
 * @since             1.0.0
 * @package           MobileTopup
 *
 * @wordpress-plugin
 * Plugin Name:       Woo Thai SIM recharge via ThailandTopup.com
 * Plugin URI:        http://blog.thailandtopup.com/
 * Description:       This plugin for WooCommerce works with ThailandTopup.com API and allows anyone to send topup to a Thai pre-paid SIM with a WooCommerce virtual product.
 * Version:           1.0.0
 * Author:            ThailandTopup
 * Author URI:        https://ThailandTopup.com
 * WC requires at least: 3.0.0
 * WC tested up to:   3.3.1
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       thailandtopup
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Currently plugin version.
 * Start at version 1.0.0
 */
define( 'PLUGIN_MOBILETOPUP_DNAME_VERSION', '1.0.0' );

// Load plugin libraries
require_once( 'includes/mobiletopup-settings.php' );
require_once( 'includes/lib/display-meta-box-field.php' );
require_once( 'includes/lib/add-meta-box.php' );
require_once( 'includes/lib/add-custom-fields-product-page.php' );
require_once( 'includes/lib/order-completed.php' );
require_once( 'includes/lib/service-api.php' );
require_once( 'includes/lib/check-in-cart.php' );
require_once( 'includes/lib/web-hook-thailand-top-up.php' );


register_activation_hook( __FILE__, 'my_plugin_create_db' );
new mobileTopUpSetting();
function my_plugin_create_db() {



    // create Table for Thailand topup
    // $create_mobiletopup_table = "CREATE TABLE IF NOT EXISTS wp_thailand_top_up (
    //     'id' int(11) NOT NULL AUTO_INCREMENT,
    //     'ORDER_ID' varchar(255) DEFAULT NULL,
    //     'ORDER_ID_THAILAD_TOPUP' varchar(255) DEFAULT NULL,
    //     'TOKENT_API' varchar(255) DEFAULT NULL,
    //     PRIMARY KEY ('id')
    //     ) $charset_collate ;
    // ";
    // require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    // dbDelta($create_mobiletopup_table);

    // if( dbDelta($create_mobiletopup_table)){
    //     var_dump(ABSPATH. 'wp-admin');
    //     exit();
    // }
    // else{
    //     var_dump('ssss');
    //     exit();
    // }
}

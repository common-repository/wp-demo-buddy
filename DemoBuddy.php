<?php
/**
 * Plugin Name: WP Demo Buddy
 * Plugin URI:  http://wpdemobuddy.com
 * Description: Instantly creates dedicated expiring demo of ANY plugin or theme for your visitors.  Can install multiple plugins for each demo.  
 * Version:     1.0.2
 * Author:      Sam@affordableplugins.com
 * Author URI:  http://affordableplugins.com
 * Text Domain: DemoBuddy
 * Domain Path: /lang
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit; 

$DemoBuddy6_db_version  = '1.3';
$installed_ver  = get_option("DemoBuddy6_db_version");
if ($installed_ver != $DemoBuddy6_db_version) {
	include_once "admin/model/db.php";
	$DemoBuddy6_db_model = new DemoBuddy_db_model();
    $DemoBuddy6_db_model->create_table();
    update_option("DemoBuddy6_db_version", $DemoBuddy6_db_version);	
}
require_once( plugin_dir_path( __FILE__ ) . 'DemoBuddy-class.php' );
require_once( plugin_dir_path( __FILE__ ) . 'DemoBuddy-config.php' );


DemoBuddy::get_instance();
?>
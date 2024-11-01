<?php
if ( ! defined( 'ABSPATH' ) ) exit; 
if (!defined('DemoBuddy6_PLUGIN_URL'))
  define('DemoBuddy6_PLUGIN_URL', plugins_url('', __FILE__));
  
if (!defined('DemoBuddy6_PLUGIN_DIR'))
  define('DemoBuddy6_PLUGIN_DIR', dirname(__FILE__));
  
if (!defined('DemoBuddy6_LOG_URL'))
  define('DemoBuddy6_LOG_URL', plugins_url('', __FILE__).'/log');
  
if (!defined('DemoBuddy6_LOG_DIR'))
  define('DemoBuddy6_LOG_DIR', dirname(__FILE__).'/log');  
/////////////////////////////////////  
if (!defined('DemoBuddy6_PLUGIN_NAME'))
  define('DemoBuddy6_PLUGIN_NAME','DemoBuddy/DemoBuddy.php');
?>
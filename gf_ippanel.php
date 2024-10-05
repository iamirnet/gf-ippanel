<?php
/*
Plugin Name: IPPanel for Gravity Forms
Description: The most comprehensive SMS plugin for Gravity Forms
Version: 2.3
Plugin URI: https://iamir.net/plugins/wp/gf-ippanel
Author: Amirhossein Jahani
Author URI: https://iamir.net/
Text Domain: GF_IPPanel
Domain Path: /languages/
*/
 if ( ! defined( 'ABSPATH' ) ) { exit; } if ( ! defined( 'GF_SMS_DIR' ) ) { define( 'GF_SMS_DIR', plugin_dir_path( __FILE__ ) ); } if ( ! defined( 'GF_SMS_URL' ) ) { define( 'GF_SMS_URL', plugins_url( null, __FILE__ ) ); } if ( ! defined( 'GF_SMS_GATEWAY' ) ) { define( 'GF_SMS_GATEWAY', plugin_dir_path( __FILE__ ) . 'includes/gateways/' ); } add_action( 'plugins_loaded', 'gravitysms_load_textdomain' ); function gravitysms_load_textdomain() { load_plugin_textdomain( 'GF_IPPanel', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); } require_once( GF_SMS_DIR . 'includes/main.php' ); add_action( 'plugins_loaded', array( 'GFIPPANELSMS_Pro', 'construct' ), 10 ); register_activation_hook( __FILE__, array( 'GFIPPANELSMS_Pro', 'active' ) ); register_deactivation_hook( __FILE__, array( 'GFIPPANELSMS_Pro', 'deactive' ) ); function gvDebug($data,$file = 'gv_debug.txt'){ $url = plugin_dir_path(__FILE__).$file; $file = @file_get_contents($url); if(is_array($data) || is_object($data)) $data = json_encode($data); @file_put_contents($url,$file."\n".$data); return; }
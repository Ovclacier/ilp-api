<?php
/**
 * Plugin Name: IDEAS Learnpress API
 * Author: Simon Fuhlhaber (EXELOP)
 * Version: 1.0
 */

//define('WP_USE_THEMES', false);
//require_once("./wp-load.php");

//function ilp_api_install(){
//	
//}
//
//function ilp_api_deactivation(){
//	
//}
//
//function ilp_api_uninstall(){
//	
//}
//
//register_activation_hook( __FILE__, 'ilp_api_install' );
//register_deactivation_hook( __FILE__, 'ilp_api_deactivation' );
//register_uninstall_hook(__FILE__, 'ilp_api_uninstall');

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

define( 'LP_ADDON_IDEAS_API_FILE', __FILE__ );


class LP_Addon_IDEAS_API_Preload {

	/**
	 * LP_Addon_Course_Review_Preload constructor.
	 */
	public function __construct() {
		add_action( 'learn-press/ready', array( $this, 'load' ) );
	}

	/**
	 * Load addon
	 */
	public function load() {
		LP_Addon::load( 'LP_Addon_IDEAS_API', 'inc/load.php', __FILE__ );
		//remove_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}
}

new LP_Addon_IDEAS_API_Preload();

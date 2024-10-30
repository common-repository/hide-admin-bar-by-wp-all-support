<?php
/*
Plugin Name: Hide Admin Bar by WP ALL SUPPORT
Plugin URL: https://wpallsupport.com
Description: This plugin is used to hide or show admin bar in frontend based on user roles conditional logic.
Version: 1.0.3
Author: WP ALL SUPPORT
Author URI: http://www.wpallsupport.com
Text Domain: wpas-hide-admin-bar
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

if ( !defined( 'WPAS_HIDE_ADMIN_BAR_VERSION' ) ) {
    define( 'WPAS_HIDE_ADMIN_BAR_VERSION', '1.0.3' );
}

if ( !defined( 'WPAS_HIDE_ADMIN_BAR_FILE' ) ) {
    define( 'WPAS_HIDE_ADMIN_BAR_FILE', __FILE__ );
}

if ( !defined( 'WPAS_HIDE_ADMIN_BAR_PLUGIN_PATH' ) ) {
    define( 'WPAS_HIDE_ADMIN_BAR_PLUGIN_PATH', plugin_dir_path( WPAS_HIDE_ADMIN_BAR_FILE) );
}

if ( !defined( 'WPAS_HIDE_ADMIN_BAR_PLUGIN_URL' ) ) {
    define( 'WPAS_HIDE_ADMIN_BAR_PLUGIN_URL', plugin_dir_url( WPAS_HIDE_ADMIN_BAR_FILE ) );
}

require_once WPAS_HIDE_ADMIN_BAR_PLUGIN_PATH . '/includes/class-wpas-hide-admin-bar.php';

register_activation_hook( __FILE__, 'activate_wpas_adminbar' );
function activate_wpas_adminbar(){
	require_once('includes/activator.php');
	WPAS_Hide_Admin_Bar_Activator::activate();
}

function init_wpas_hide_admin_bar() {

    WPAS_Hide_Admin_Bar::instance();

}
add_action( 'plugins_loaded', 'init_wpas_hide_admin_bar' );
<?php
/**
 * Uninstalling the plugin data.
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$settings = get_option('wpas_admin_bar_settings', array());
if ( 1 == $settings[ 'uninstall_data' ] ) {
    
    $options = array(
        'hide_for_all',
        'hide_admin_bar_for_role',
        'hide_admin_bar_roles',
        'hide_admin_bar_users',
        'custom_rules',
        'uninstall_data',
    );

    $options = apply_filters('wpas_hide_admin_bar_uninstall_data', $options);
    
    if ( !empty( $options ) ) {
        foreach ( $options as $option ) {
            unset( $settings[$option] );
        }
    }

    update_option('wpas_admin_bar_settings', $settings);
}
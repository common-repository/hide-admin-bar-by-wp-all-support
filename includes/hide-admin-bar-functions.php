<?php

function get_wpas_ab_settings( $key = '' ) {

    $settings = get_option('wpas_admin_bar_settings');

    if(empty($key)) {
        return $settings;
    }

    return !empty($settings[$key]) ? $settings[$key] : '';
}

function get_wpas_user_role_lists() {
    global $wp_roles;

    $roles = array();
    if(!empty($wp_roles->roles) && is_array($wp_roles->roles)) {
        foreach ( $wp_roles->roles as $key => $role ) {
            $roles[$key] = $role['name'];
        }
    }

    return apply_filters('wpas_hide_admin_bar_editable_roles', $roles);
}

function get_wpas_current_user_roles() {

    $roles = array();

    if( is_user_logged_in() ) {
        $user = wp_get_current_user();
        $roles = (array)$user->roles;

    }

    return apply_filters('wpas_hide_admin_bar_get_current_user_roles', $roles);
}

function wpas_get_templates_dir() {
    return WPAS_HIDE_ADMIN_BAR_PLUGIN_PATH . 'templates';
}

function wpas_locate_template($template_name) {

    $default_path = wpas_get_templates_dir();

    $template = untrailingslashit( $default_path ) . '/' . $template_name;

    return apply_filters( 'wpas_hide_admin_bar_locate_template', $template, $template_name, $default_path );

}

function wpas_get_template( $template_name, $args = array() ) {
    if ( ! empty( $args ) && is_array( $args ) ) {
        extract( $args );
    }

    $located = wpas_locate_template( $template_name);

    if ( ! file_exists( $located ) ) {
        return;
    }

    $located = apply_filters( 'wpas_hide_admin_bar_get_template', $located, $template_name, $args);

    do_action( 'wpas_hide_admin_bar_before_template_part', $template_name, $located, $args );

    include( $located );

    do_action( 'wpas_hide_admin_bar_after_template_part', $template_name, $located, $args );
}
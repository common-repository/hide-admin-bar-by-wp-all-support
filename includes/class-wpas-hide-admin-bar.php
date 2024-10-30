<?php

if(!class_exists('WPAS_Hide_Admin_Bar')) {

    final class WPAS_Hide_Admin_Bar{

        private static $instance = null;

        public static function instance() {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct() {
            $this->includes();
            $this->setup_actions();
        }

        public function includes() {

            require_once( WPAS_HIDE_ADMIN_BAR_PLUGIN_PATH . '/includes/hide-admin-bar-functions.php' );
            require_once( WPAS_HIDE_ADMIN_BAR_PLUGIN_PATH . '/admin/class-wpas-hide-admin-bar-admin.php' );
        }

        public function setup_actions() {
            add_action('plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
            add_action('wp', array($this,'wp_action'));
        }

        public function load_plugin_textdomain() {
            load_plugin_textdomain(
                'wpas-hide-admin-bar',
                FALSE,
	            basename(WPAS_HIDE_ADMIN_BAR_PLUGIN_PATH . '/languages/')
            );

	        do_action('wpas_hide_admin_bar_loaded');
        }

        public function wp_action(){

            if(is_user_logged_in()) {

                $current_page_id = get_the_ID();
                $current_user_id = get_current_user_id();
                $get_settings = get_wpas_ab_settings();
                $current_user_roles = get_wpas_current_user_roles();

                $hide_for_all = !empty($get_settings['hide_for_all']) ? $get_settings['hide_for_all'] : 0;
                $hide_admin_bar_for_role = !empty($get_settings['hide_admin_bar_for_role']) ? $get_settings['hide_admin_bar_for_role'] : 0;
                $hide_user_roles = !empty($get_settings['hide_admin_bar_roles']) ? $get_settings['hide_admin_bar_roles'] : 0;
                $hide_users = !empty($get_settings['hide_admin_bar_users']) ? $get_settings['hide_admin_bar_users'] : 0;
                $custom_rules = !empty($get_settings['custom_rules']) ? $get_settings['custom_rules'] : 0;

                $show_admin_bar = true;

                if (!empty($hide_for_all)) {
                    $show_admin_bar = false;
                } elseif(!empty($hide_admin_bar_for_role)){

                    if (!empty($hide_user_roles) && is_array($hide_user_roles) && !empty($current_user_roles) && is_array($current_user_roles)) {
                        $intersect_result = array_intersect($hide_user_roles, $current_user_roles);
                        if (!empty($intersect_result)) {
                            $show_admin_bar = false;
                        }
                    }

                } else {

                    if(!empty($hide_users) && is_array($hide_users) && in_array($current_user_id,$hide_users)) {
                        $show_admin_bar = false;
                    } else{

                        $manage_rule = array();
                        if (!empty($custom_rules) && is_array($custom_rules)) {
                            foreach ($custom_rules as $key => $rules) {
                                $post_type = !empty($rules['post_type']) ? $rules['post_type'] : '';
                                $post_page_ids = !empty($rules['post_page_id']) ? $rules['post_page_id'] : array();

                                if (!empty($manage_rule) && isset($post_type) && array_key_exists($post_type, $manage_rule)) {
                                    $get_set_rule = !empty($manage_rule[$post_type]) ? $manage_rule[$post_type] : array();
                                    $manage_rule[$post_type] = array_merge($get_set_rule, $post_page_ids);
                                } else {
                                    if (!empty($post_page_ids) && is_array($post_page_ids)) {
                                        $manage_rule[$post_type] = $post_page_ids;
                                    }
                                }
                            }
                        }

                        if(!empty($manage_rule) && is_array($manage_rule)) {
                            $current_post_type = get_post_type($current_page_id);

                            if(!empty($current_post_type) && !empty($manage_rule[$current_post_type]) && in_array($current_page_id,$manage_rule[$current_post_type])) {
                                $show_admin_bar = false;
                            }
                        }

                    }

                }

                $show_admin_bar = apply_filters('wpas_hide_admin_bar', $show_admin_bar);

                show_admin_bar($show_admin_bar);
            }
        }
    }
}
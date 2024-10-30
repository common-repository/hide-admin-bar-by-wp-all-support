<?php
if(!class_exists('WPAS_Hide_Admin_Bar_Admin')) {

    class WPAS_Hide_Admin_Bar_Admin {

        public function __construct() {
	        add_action( 'admin_init', array( $this, 'activation_redirect' ) );
            add_action('admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
            add_action('admin_menu', array($this,'admin_menu'));
            add_action('admin_post_wpas_admin_bar_settings', array($this,'save_settings'));
            add_action('wp_ajax_wpas_create_new_custom_rule', array($this,'create_new_rule_action'));
            add_action('wp_ajax_wpas_get_posts', array($this,'process_get_posts'));
        }

	    /**
	     * Redirect to the settings page on activation.
	     *
	     * @since 1.0.0
	     */
	    public function activation_redirect() {
		    // Bail if no activation redirect
		    if ( !get_transient( '_wpas_adminbar_activation_redirect' ) ) {
			    return;
		    }

		    // Delete the redirect transient
		    delete_transient( '_wpas_adminbar_activation_redirect' );

		    // Bail if activating from network, or bulk
		    if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
			    return;
		    }

		    wp_safe_redirect( admin_url( 'admin.php?page=wpas-admin-bar-settings' ) );
		    exit;
	    }

        public function admin_enqueue_scripts($hook) {

	        if ('toplevel_page_wpas-admin-bar-settings' != $hook) {
		        return;
	        }

            wp_enqueue_style('select2', WPAS_HIDE_ADMIN_BAR_PLUGIN_URL.'assets/css/select2.min.css' );
            wp_enqueue_style('font-awesome-min-style',WPAS_HIDE_ADMIN_BAR_PLUGIN_URL.'assets/css/font-awesome.min.css');
            wp_enqueue_style('wpas-hide-admin-bar-admin-style',WPAS_HIDE_ADMIN_BAR_PLUGIN_URL.'assets/css/wpas-hide-admin-bar-admin.css');

            wp_enqueue_script('select2', WPAS_HIDE_ADMIN_BAR_PLUGIN_URL.'assets/js/select2.min.js', array('jquery') );
            wp_enqueue_script('wpas-hide-admin-bar-script',WPAS_HIDE_ADMIN_BAR_PLUGIN_URL.'assets/js/wpas-hide-admin-bar.js',array('jquery'),'',true);
        }

        public function admin_menu() {
            add_menu_page(
                __( 'Hide Admin Bar Settings', 'wpas-hide-admin-bar' ),
                __( 'Hide Admin Bar Settings', 'wpas-hide-admin-bar' ),
                'manage_options',
                'wpas-admin-bar-settings',
                array($this,'admin_bar_settings_content'),
                WPAS_HIDE_ADMIN_BAR_PLUGIN_URL.'assets/images/admin-menu.png'
            );
        }

        public function admin_bar_settings_content() {

            $get_settings = get_wpas_ab_settings();
            $hide_for_all = !empty($get_settings['hide_for_all']) ? $get_settings['hide_for_all'] : 0;
            $hide_admin_bar_for_role = !empty($get_settings['hide_admin_bar_for_role']) ? $get_settings['hide_admin_bar_for_role'] : 0;
            $hide_admin_bar_users = !empty($get_settings['hide_admin_bar_users']) ? $get_settings['hide_admin_bar_users'] : array();
	        $hide_admin_bar_uninstall = !empty($get_settings['uninstall_data']) ? $get_settings['uninstall_data'] : 0;
            $hide_admin_bar_roles = !empty($get_settings['hide_admin_bar_roles']) ? $get_settings['hide_admin_bar_roles'] : array();
            $custom_rules = !empty($get_settings['custom_rules']) ? $get_settings['custom_rules'] : array();
            ?>
            <div class="wrap">
                <h1><?php _e( 'Hide Admin Bar Settings', 'wpas-hide-admin-bar' ); ?></h1>
                <div id="wpas_admin_bar_settings_wrap">
                    <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
                        <input type="hidden" name="action" value="wpas_admin_bar_settings">
                        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('wpas-admin-bar-settings'); ?>">
                        <table class="form-table" role="presentation">
                            <tbody>
                                <tr>
                                    <th scope="row"><label for="hide_for_all"><?php _e('Force hide for all?','wpas-hide-admin-bar'); ?></label></th>
                                    <td><input type="checkbox" name="hide_for_all" id="hide_for_all" <?php checked($hide_for_all,1); ?> value="<?php echo $hide_for_all; ?>" class="regular-text"></td>
                                </tr>
                                <tr class="is_hide_bar_using_users">
                                    <th scope="row"><label for="hide_admin_bar_users"><?php _e('Hide for selected users?','wpas-hide-admin-bar'); ?></label></th>
                                    <td>
                                        <select name="hide_admin_bar_users[]" id="hide_admin_bar_users" class="rander-select2 regular-text" multiple data-placeholder="<?php _e('Select users','wpas-hide-admin-bar'); ?>">
                                            <?php

                                            $get_users = get_users();

                                            if(!empty($get_users) && is_array($get_users)) {
                                                foreach ($get_users as $key => $user ) {

                                                    $user_id = $user->ID;
                                                    $user_name = $user->display_name;

                                                    $checked = '';
                                                    if(!empty($hide_admin_bar_users) && in_array($user_id, $hide_admin_bar_users)) {
                                                        $checked = selected($user_id,$user_id,false);
                                                    }
                                                    ?>
                                                    <option <?php echo $checked; ?> value="<?php echo $user_id; ?>"><?php echo $user_name; ?></option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="hide_admin_bar_for_role"><?php _e('Hide by user roles?','wpas-hide-admin-bar'); ?></label></th>
                                    <td><input type="checkbox" name="hide_admin_bar_for_role" id="hide_admin_bar_for_role" <?php checked($hide_admin_bar_for_role,1); ?> value="<?php echo $hide_admin_bar_for_role; ?>" class="regular-text"></td>
                                </tr>
                                <tr class="is_hide_bar_using_user_role">
                                    <th scope="row"><label for="hide_admin_bar_roles"><?php _e('Select roles to hide admin bar','wpas-hide-admin-bar'); ?></label></th>
                                    <td>
                                        <?php $roles_lists = get_wpas_user_role_lists(); ?>
                                        <select name="hide_admin_bar_roles[]" id="hide_admin_bar_roles" class="rander-select2 regular-text" multiple data-placeholder="<?php _e('Select user roles','wpas-hide-admin-bar'); ?>">
                                            <?php
                                            if(!empty($roles_lists) && is_array($roles_lists)) {
                                                foreach ($roles_lists as $key => $role ) {

                                                    $checked = '';
                                                    if(!empty($hide_admin_bar_roles) && in_array($key,$hide_admin_bar_roles)) {
                                                        $checked = selected($key,$key,false);
                                                    }
                                                    ?>
                                                    <option <?php echo $checked; ?> value="<?php echo $key; ?>"><?php echo $role; ?></option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr class="is_hide_bar_using_custom_rule">
                                    <th scope="row"><label for="hide_admin_bar_if"><?php _e('Hide admin bar if...','wpas-hide-admin-bar'); ?></label></th>
                                    <td>
                                        <div class="wpas-hide-bar-heading">
                                            <a href="javascript:void(0);" data-counter_id="<?php echo !empty($custom_rules) ? count($custom_rules) : 0; ?>" class="create-new-custom-rule button button-primary"><i class="fas fa-plus"></i> <?php _e('Create a new rule','wpas-hide-admin-bar'); ?></a>
                                        </div>
                                        <div class="wpas-hide-bar-content">
                                            <ul class="rule-items">
                                                <?php
                                                if(!empty($custom_rules) && is_array($custom_rules)) {
                                                    foreach ($custom_rules as $key => $rule ) {
                                                        $args = array(
                                                            'current_id' => $key,
                                                            'rule' => $rule,
                                                        );
                                                        wpas_get_template('add-new-rule.php',$args);
                                                    }
                                                }
                                                ?>
                                            </ul>
                                        </div>
                                        <div class="wpas-hide-bar-footer">
                                            <input type="hidden" name="wpas_custom_rule_count" id="wpas_custom_rule_count" value="<?php echo !empty($custom_rules) ? count($custom_rules) : 0; ?>">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="uninstall_data"><?php _e('Delete data on uninstall?','wpas-hide-admin-bar'); ?></label></th>
                                    <td><input type="checkbox" name="uninstall_data" id="uninstall_data" <?php checked($hide_admin_bar_uninstall,1); ?> value="<?php echo $hide_admin_bar_uninstall; ?>" class="regular-text"></td>
                                </tr>
                            </tbody>
                        </table>
                        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes','wpas-hide-admin-bar'); ?>"></p>
                    </form>
                </div>
            </div>
            <?php
        }

        public function save_settings() {

            $hide_for_all = !empty($_POST['hide_for_all']) ? sanitize_text_field($_POST['hide_for_all']) : 0;
            $hide_admin_bar_for_role = !empty($_POST['hide_admin_bar_for_role']) ? sanitize_text_field($_POST['hide_admin_bar_for_role']) : 0;
            $hide_admin_bar_roles = !empty($_POST['hide_admin_bar_roles']) ? (array)$_POST['hide_admin_bar_roles'] : array();
            $hide_admin_bar_users = !empty($_POST['hide_admin_bar_users']) ? (array)$_POST['hide_admin_bar_users'] : array();
	        $hide_admin_bar_roles = array_map( 'sanitize_text_field', $hide_admin_bar_roles );
	        $uninstall_data = !empty($_POST['uninstall_data']) ? sanitize_text_field($_POST['uninstall_data']) : 0;
            $total_rule_count = !empty($_POST['wpas_custom_rule_count']) ? sanitize_text_field($_POST['wpas_custom_rule_count']) : '';

            $custom_rules = array();
            if(!empty($total_rule_count) && (int)$total_rule_count > 0 ) {
                for ($i = 1; $i <= $total_rule_count; $i++) {
                    $key = ($i > 0 ) ? '_'.$i : '';
                    $post_type = !empty($_POST['custom_rule_post_type'.$key]) ? sanitize_text_field($_POST['custom_rule_post_type'.$key]) : 0;
	                $custom_rule_post_page = !empty($_POST['custom_rule_post_page'.$key]) ? (array)$_POST['custom_rule_post_page'.$key] : array();
                    $custom_rule_post_page = array_map( 'sanitize_text_field', $custom_rule_post_page );

                    $post_page_id = $custom_rule_post_page;
                    $custom_rules[] = array(
                        'post_type' => $post_type,
                        'post_page_id' => $post_page_id,
                    );
                }
            }

            $settings_arr = array(
                'hide_for_all' => (int)$hide_for_all,
                'hide_admin_bar_for_role' => (int)$hide_admin_bar_for_role,
                'hide_admin_bar_roles' => $hide_admin_bar_roles,
                'hide_admin_bar_users' => $hide_admin_bar_users,
                'custom_rules' => $custom_rules,
                'uninstall_data' =>(int)$uninstall_data,
            );

            update_option('wpas_admin_bar_settings',$settings_arr);

            wp_safe_redirect(esc_url(admin_url('admin.php?page=wpas-admin-bar-settings')));
            exit();
        }

        public function create_new_rule_action() {
            $counter_id = !empty($_POST['counter_id']) ? (int)$_POST['counter_id'] : 0;

            $args = array(
                'current_id' => $counter_id,
                'rule' => array(),
            );

            ob_start();
            wpas_get_template('add-new-rule.php',$args);

            $rule_html = ob_get_clean();

            $response = array(
                'counter_id' => $counter_id + 1,
                'html' => $rule_html,
            );

            echo json_encode($response);
            wp_die();
        }

        public function process_get_posts() {

	        $posttype = !empty($_POST['posttype']) ? $_POST['posttype'] : '';

	        $posts_option = '';
	        if(!empty($posttype)) {

                $post_status = apply_filters('wpas_hide_admin_bar_post_status',array('any'));

                $get_posts = get_posts(array(
                    'posts_per_page' => -1,
                    'post_type' => $posttype,
                    'post_status' => $post_status,
                    'order' => 'DESC',
                    'orderby' => 'title',
                ));

                if(!empty($get_posts) && is_array($get_posts)) {
                    foreach ($get_posts as $key => $post ) {
                        $post_id = !empty($post->ID) ? $post->ID : 0;
                        $post_title = !empty($post->post_title) ? $post->post_title : 0;
                        $posts_option .='<option value="'.$post_id.'">'.$post_title.'</option>';
                    }
                }
            }

	        $response = array(
	          'options' => $posts_option,
            );

	        echo json_encode($response);
	        die();
        }
    }

    new WPAS_Hide_Admin_Bar_Admin();
}
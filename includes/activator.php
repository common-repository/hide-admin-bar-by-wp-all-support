<?php
/**
 * Fired during plugin activation
 */

class WPAS_Hide_Admin_Bar_Activator
{
	public static function activate()
	{

        $settings_arr = array(
            'hide_for_all' => 1,
        );

        update_option('wpas_admin_bar_settings',$settings_arr);

		set_transient( '_wpas_adminbar_activation_redirect', true, 30 );
	}

}
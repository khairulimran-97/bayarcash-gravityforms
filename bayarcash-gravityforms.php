<?php

/**
 * Plugin Name: Bayarcash for Gravity Forms
 * Plugin URI: https://wordpress.org/plugins/bayarcash-for-woocommerce/
 * Description: Bayarcash - Better Payment & Business Solutions
 * Version: 1.0.4
 * Author: Bayarcash In Sdn Bhd
 * Author URI: http://www.bayarcash.com
 *
 * Copyright: © 2023 Bayarcash
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

defined( 'ABSPATH' ) || die();

define( 'GF_BAYARCASH_MODULE_VERSION', 'v1.0.4');
define( 'GF_BAYARCASH_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

add_action( 'gform_loaded', array( 'GF_BAYARCASH_Bootstrap', 'load_addon' ), 5 );

// Enqueue JavaScript file outside the class
add_action('admin_enqueue_scripts', 'enqueue_bayarcash_script');

function enqueue_bayarcash_script() {
	$js_file_path = plugin_dir_path(__FILE__) . 'bayarcash-script.js';
	wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');

	// Check if the JavaScript file exists
	if (file_exists($js_file_path)) {
		// Enqueue the script if it exists
		wp_enqueue_script('bayarcash-script', plugin_dir_url(__FILE__) . 'bayarcash-script.js', array('jquery'), null, true);
	} else {
		// Log an error if the file doesn't exist
		error_log('Bayarcash JavaScript file not found at: ' . $js_file_path);
	}
}

class GF_BAYARCASH_Bootstrap {
	public static function load_addon() {
		// Include necessary PHP files
		require_once GF_BAYARCASH_PLUGIN_PATH . '/api.php';
		require_once GF_BAYARCASH_PLUGIN_PATH . '/class-gf-bayarcash.php';
        	require_once GF_BAYARCASH_PLUGIN_PATH . '/bayarcash-gf-cron.php';

		GFAddOn::register('GF_Bayarcash');
		add_filter('plugin_action_links_' . plugin_basename(__FILE__), array('GF_BAYARCASH_Bootstrap', 'gf_bayarcash_setting_link'));
	}


	public static function gf_bayarcash_setting_link($links) {
		$new_links = array(
			'settings' => sprintf(
				'<a href="%1$s">%2$s</a>',
				admin_url('admin.php?page=gf_settings&subview=gravityformsbayarcash'),
				esc_html__('Settings', 'gravityformsbayarcash')
			)
		);

		return array_merge($new_links, $links);
	}
}

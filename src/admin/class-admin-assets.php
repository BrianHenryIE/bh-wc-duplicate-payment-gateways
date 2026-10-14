<?php
/**
 * Register the wp-admin JavaScript and CSS.
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\Admin;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\Settings_Interface;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 *
 * @author     BrianHenryIE <BrianHenryIE@gmail.com>
 */
class Admin_Assets {

	/**
	 * The plugin setting.
	 *
	 * @uses Settings_Interface::get_plugin_version()
	 *
	 * @var Settings_Interface
	 */
	protected Settings_Interface $settings;

	/**
	 * Admin constructor.
	 *
	 * @param Settings_Interface $settings The plugin's settings.
	 */
	public function __construct( Settings_Interface $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @hooked admin_enqueue_scripts
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles(): void {

		$url     = plugin_dir_url( $this->settings->get_plugin_basename() ) . 'assets/bh-wc-duplicate-payment-gateways-admin.css';
		$version = $this->settings->get_plugin_version();

		wp_enqueue_style( 'bh-wc-duplicate-payment-gateways', $url, array(), $version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @hooked admin_enqueue_scripts
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts(): void {

		// TODO: Only add on the payment gateways list?

		$url = plugin_dir_url( $this->settings->get_plugin_basename() ) . 'assets/bh-wc-duplicate-payment-gateways-admin.js';

		$handle = 'bh-wc-duplicate-payment-gateways';
		wp_enqueue_script( $handle, $url, array( 'jquery' ), $this->settings->get_plugin_version(), true );

		$data      = array(
			'nonce'    => wp_create_nonce( AJAX::NONCE_ACTION ),
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		);
		$json_data = wp_json_encode( $data, JSON_PRETTY_PRINT );

		$script = <<<EOD
var bh_wc_duplicate_payment_gateways = $json_data;
EOD;

		wp_add_inline_script(
			$handle,
			$script
		);
	}
}

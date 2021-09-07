<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    BH_WC_Duplicate_Payment_Gateways
 * @subpackage BH_WC_Duplicate_Payment_Gateways/admin
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\Admin;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\API\Settings_Interface;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    BH_WC_Duplicate_Payment_Gateways
 * @subpackage BH_WC_Duplicate_Payment_Gateways/admin
 * @author     BrianHenryIE <BrianHenryIE@gmail.com>
 */
class Admin {

	protected Settings_Interface $settings;

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

		wp_enqueue_style( 'bh-wc-duplicate-payment-gateways', plugin_dir_url( __FILE__ ) . 'css/bh-wc-duplicate-payment-gateways-admin.css', array(), $this->settings->get_plugin_version(), 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @hooked admin_enqueue_scripts
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts(): void {

		// TODO: Only add on the payment gateways list.

		$handle = 'bh-wc-duplicate-payment-gateways';
		wp_enqueue_script( $handle, plugin_dir_url( __FILE__ ) . 'js/bh-wc-duplicate-payment-gateways-admin.js', array( 'jquery' ), $this->settings->get_plugin_version(), true );

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

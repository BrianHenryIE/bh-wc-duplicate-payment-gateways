<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * frontend-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    BH_WC_Duplicate_Payment_Gateways
 * @subpackage BH_WC_Duplicate_Payment_Gateways/includes
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\Includes;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\Admin\Admin;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\Admin\AJAX;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\Admin\Plugins_Page;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\API\API_Interface;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\API\Settings_Interface;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Payment_Gateway;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Payment_Gateway_Settings;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Payment_Gateways;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Payment_Gateways_List_UI;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * frontend-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    BH_WC_Duplicate_Payment_Gateways
 * @subpackage BH_WC_Duplicate_Payment_Gateways/includes
 * @author     BrianHenryIE <BrianHenryIE@gmail.com>
 */
class BH_WC_Duplicate_Payment_Gateways {

	protected Settings_Interface $settings;

	protected API_Interface $api;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the frontend-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct( API_Interface $api, Settings_Interface $settings ) {

		$this->settings = $settings;
		$this->api      = $api;

		$this->set_locale();

		$this->define_admin_hooks();
		$this->define_plugins_page_hooks();

		$this->define_add_payment_gateways_hooks();
		// $this->define_add_new_gateway_settings_hooks();
		$this->define_payment_gateway_hooks();
		$this->define_woocommerce_payment_gateways_list_ui_hooks();
		$this->define_ajax_hooks();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	protected function set_locale(): void {

		$plugin_i18n = new I18n();

		add_action( 'plugins_loaded', array( $plugin_i18n, 'load_plugin_textdomain' ) );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	protected function define_admin_hooks(): void {

		$admin = new Admin( $this->settings );

		add_action( 'admin_enqueue_scripts', array( $admin, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $admin, 'enqueue_scripts' ) );
	}

	protected function define_plugins_page_hooks(): void {

		$plugins_page = new Plugins_Page();

		$basename = 'bh-wc-duplicate-payment-gateways/bh-wc-duplicate-payment-gateways.php';

		add_filter( "plugin_action_links_{$basename}", array( $plugins_page, 'action_links' ) );
	}


	protected function define_add_payment_gateways_hooks(): void {

		$payment_gateways = new Payment_Gateways( $this->settings );

		add_filter( 'woocommerce_payment_gateways', array( $payment_gateways, 'add_gateways' ) );
	}

	protected function define_add_new_gateway_settings_hooks(): void {

		$payment_gateway_settings = new Payment_Gateway_Settings();

		add_filter( 'woocommerce_after_register_post_type', array( $payment_gateway_settings, 'register_filter_on_each_gateway' ) );
	}

	protected function define_payment_gateway_hooks(): void {

		$payment_gateway = new Payment_Gateway();

		add_filter( 'woocommerce_gateway_method_title', array( $payment_gateway, 'replace_title' ), 10, 2 );
	}

	protected function define_woocommerce_payment_gateways_list_ui_hooks(): void {

		$payment_gateways_list_ui = new Payment_Gateways_List_UI();

		add_filter( 'woocommerce_payment_gateways_setting_columns', array( $payment_gateways_list_ui, 'add_column' ) );
		add_action( 'woocommerce_payment_gateways_setting_column_duplicate', array( $payment_gateways_list_ui, 'print_column_for_duplicate_ui' ) );
	}

	/**
	 * Hooks to handle the new
	 */
	protected function define_ajax_hooks(): void {

		$ajax = new AJAX( $this->api );

		add_action( 'wp_ajax_add_new_duplicate_payment_gateway', array( $ajax, 'add_new_duplicate_payment_gateway' ) );
		add_action( 'wp_ajax_delete_duplicate_payment_gateway', array( $ajax, 'delete_duplicate_payment_gateway' ) );
	}

}

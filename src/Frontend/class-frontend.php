<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    BH_WC_Duplicate_Gateway
 * @subpackage BH_WC_Duplicate_Gateway/frontend
 */

namespace BH_WC_Duplicate_Gateway\Frontend;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the frontend-facing stylesheet and JavaScript.
 *
 * @package    BH_WC_Duplicate_Gateway
 * @subpackage BH_WC_Duplicate_Gateway/frontend
 * @author     BrianHenryIE <BrianHenryIE@gmail.com>
 */
class Frontend {

	/**
	 * Register the stylesheets for the frontend-facing side of the site.
	 *
	 * @hooked wp_enqueue_scripts
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles(): void {

		wp_enqueue_style( 'bh-wc-duplicate-gateway', plugin_dir_url( __FILE__ ) . 'css/bh-wc-duplicate-gateway-frontend.css', array(), BH_WC_DUPLICATE_GATEWAY_VERSION, 'all' );

	}

	/**
	 * Register the JavaScript for the frontend-facing side of the site.
	 *
	 * @hooked wp_enqueue_scripts
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts(): void {

		wp_enqueue_script( 'bh-wc-duplicate-gateway', plugin_dir_url( __FILE__ ) . 'js/bh-wc-duplicate-gateway-frontend.js', array( 'jquery' ), BH_WC_DUPLICATE_GATEWAY_VERSION, false );

	}

}

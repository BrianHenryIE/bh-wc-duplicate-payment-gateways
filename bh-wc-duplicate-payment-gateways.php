<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 *
 * @wordpress-plugin
 * Plugin Name:       Duplicate Payment Gateways
 * Plugin URI:        http://github.com/BrianHenryIE/bh-wc-duplicate-payment-gateways/
 * Description:       Enables multiple instances of WooCommerce payment gateways. NB: Not compatible with 100% of payment gateway plugins.
 * Version:           1.6.0
 * Author:            BrianHenryIE
 * Author URI:        http://BrianHenry.ie/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       bh-wc-duplicate-payment-gateways
 * Domain Path:       /languages
 * Requires PHP:      7.4
 * Requires Plugins:  woocommerce
 * WC tested up to:   11.2
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\API\API;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\API\Settings;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Gateway_Duplicability;


// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	throw new \Exception();
}

require_once plugin_dir_path( __FILE__ ) . 'autoload.php';

define( 'BH_WC_DUPLICATE_PAYMENT_GATEWAYS_VERSION', '1.6.0' );
define( 'BH_WC_DUPLICATE_PAYMENT_GATEWAYS_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function instantiate_bh_wc_duplicate_payment_gateways(): API_Interface {

	$settings              = new Settings();
	$gateway_duplicability = new Gateway_Duplicability();
	$api                   = new API( $settings, $gateway_duplicability );

	new BH_WC_Duplicate_Payment_Gateways( $api, $settings, $gateway_duplicability );

	// \BH_WC_Duplicate_Payment_Gateways_SLSWC_Client::get_instance( 'https://bhwp.ie/', __FILE__ );

	return $api;
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and frontend-facing site hooks.
 */
$GLOBALS['bh_wc_duplicate_payment_gateways'] = instantiate_bh_wc_duplicate_payment_gateways();

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
 * @package           BH_WC_Duplicate_Payment_Gateways
 *
 * @wordpress-plugin
 * Plugin Name:       Duplicate Payment Gateways
 * Plugin URI:        http://github.com/BrianHenryIE/bh-wc-duplicate-payment-gateways/
 * Description:       Enables multiple instances of WooCommerce payment gateways. NB: Not compatible with 100% of payment gateway plugins.
 * Version:           1.4.2
 * Author:            BrianHenryIE
 * Author URI:        http://BrianHenry.ie/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       bh-wc-duplicate-payment-gateways
 * Domain Path:       /languages
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\API\API;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\API\Settings;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\Includes\BH_WC_Duplicate_Payment_Gateways;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	throw new \Exception();
}

require_once plugin_dir_path( __FILE__ ) . 'autoload.php';

/**
 * Current plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'BH_WC_DUPLICATE_PAYMENT_GATEWAYS_VERSION', '1.4.2' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function instantiate_bh_wc_duplicate_payment_gateways(): API {

	$settings = new Settings();
	$api      = new API( $settings );

	new BH_WC_Duplicate_Payment_Gateways( $api, $settings );

	return $api;
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and frontend-facing site hooks.
 */
$GLOBALS['bh_wc_duplicate_payment_gateways'] = instantiate_bh_wc_duplicate_payment_gateways();

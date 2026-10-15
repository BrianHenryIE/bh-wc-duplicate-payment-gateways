<?php
/**
 * Plugin Name:       Duplicate Payment Gateways Development Plugin
 * Description:       Demo gateways and test helpers for Duplicate Payment Gateways. Not for production.
 * Plugin URI:        http://github.com/BrianHenryIE/bh-wc-duplicate-payment-gateways/
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\Development_Plugin;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\Alley_Interactive\Autoloader\Autoloader;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\Development_Plugin\Gateways\Demo_Gateways;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\Development_Plugin\Rest\Duplicates;

if ( ! defined( 'WPINC' ) ) {
	return;
}

if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if ( ! is_plugin_active( 'bh-wc-duplicate-payment-gateways/bh-wc-duplicate-payment-gateways.php' ) ) {
	return;
}

// Plugins load alphabetically, so the main plugin's autoloader may not have run yet.
require_once WP_PLUGIN_DIR . '/bh-wc-duplicate-payment-gateways/autoload.php';

Autoloader::generate(
	'BrianHenryIE\\WC_Duplicate_Payment_Gateways\\Development_Plugin',
	__DIR__,
)->register();

// Disable WooCommerce's first-run wizards, which otherwise intercept wp-admin page loads.
( new First_Run_Wizards() )->register_hooks();

// Payment gateways to demonstrate duplicating.
( new Demo_Gateways() )->register_hooks();

// REST endpoints for arranging E2E tests.
( new Duplicates() )->register_hooks();

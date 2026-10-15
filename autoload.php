<?php
/**
 * Loads all required classes
 *
 * The prefixed dependencies' autoloader, and the Alley Interactive WordPress-filename autoloader for `src`.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           brianhenryie/bh-wc-duplicate-payment-gateways
 *
 * @see https://github.com/alleyinteractive/wordpress-autoloader
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\Alley_Interactive\Autoloader\Autoloader;

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

// Prefixed dependencies, i.e. the Alley Interactive autoloader.
require_once __DIR__ . '/vendor-prefixed/autoload.php';

Autoloader::generate(
	'BrianHenryIE\WC_Duplicate_Payment_Gateways',
	__DIR__ . '/src',
)->register();

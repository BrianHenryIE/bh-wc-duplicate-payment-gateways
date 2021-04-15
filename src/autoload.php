<?php
/**
 * Loads all required classes
 *
 * Uses classmap, PSR4 & wp-namespace-autoloader.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           BH_WC_Duplicate_Gateway
 *
 * @see https://github.com/pablo-sg-pacheco/wp-namespace-autoloader/
 */

namespace BH_WC_Duplicate_Gateway;

use BH_WC_Duplicate_Gateway\Pablo_Pacheco\WP_Namespace_Autoloader\WP_Namespace_Autoloader;

require_once __DIR__ . '/strauss/autoload.php';

$wpcs_autoloader = new WP_Namespace_Autoloader();
$wpcs_autoloader->init();

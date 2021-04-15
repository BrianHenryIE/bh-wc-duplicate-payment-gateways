<?php
/**
 * PHPUnit bootstrap file for WP_Mock.
 *
 * @package           BH_WC_Duplicate_Gateway
 */

global $plugin_root_dir;
require_once $plugin_root_dir . '/autoload.php';

WP_Mock::bootstrap();

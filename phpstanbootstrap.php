<?php
/**
 * Define constants that PhpStan cannot find.
 *
 * @see https://phpstan.org/user-guide/discovering-symbols#global-constants
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

define( 'WP_CONTENT_DIR', __DIR__ . '/wp-content' );
define( 'WP_PLUGIN_DIR', __DIR__ . '/wp-content/plugins' );

/**
 * For missing constants when analysing /tests/ folder.
 *
 * @see \Codeception\Module\WPLoader::_getConstants()
 */
define( 'WP_TESTS_DOMAIN', 'example.org' );

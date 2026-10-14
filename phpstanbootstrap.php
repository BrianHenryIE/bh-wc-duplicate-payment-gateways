<?php
/**
 * Define constants that PhpStan cannot find.
 *
 * @see https://phpstan.org/user-guide/discovering-symbols#global-constants
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

defined( 'WP_CONTENT_DIR' ) || define( 'WP_CONTENT_DIR', __DIR__ . '/wp-content' );
defined( 'WP_PLUGIN_DIR' ) || define( 'WP_PLUGIN_DIR', __DIR__ . '/wp-content/plugins' );

/**
 * For missing constants when analysing /tests/ folder.
 *
 * @see \Codeception\Module\WPLoader::_getConstants()
 */
defined( 'WP_TESTS_DOMAIN' ) || define( 'WP_TESTS_DOMAIN', 'example.org' );

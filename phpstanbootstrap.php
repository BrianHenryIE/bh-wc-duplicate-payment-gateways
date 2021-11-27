<?php
/**
 * Config for PHPStan missing constants.
 *
 * @see https://phpstan.org/user-guide/discovering-symbols
 *
 * @package     brianhenryie/bh-wc-duplicate-payment-gateways
 */

define( 'WP_CONTENT_DIR', __DIR__ . '/wp-content' );
define( 'WP_PLUGIN_DIR', __DIR__ . '/wp-content/plugins' );

/**
 * For missing constants when analysing /tests/ folder.
 *
 * @see \Codeception\Module\WPLoader::_getConstants()
 */
define( 'WP_TESTS_DOMAIN', 'example.org' );



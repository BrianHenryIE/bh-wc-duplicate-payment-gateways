<?php
/**
 * Tests for the root plugin file.
 *
 * @package BH_WC_Duplicate_Gateway
 * @author  BrianHenryIE <BrianHenryIE@gmail.com>
 */

namespace BH_WC_Duplicate_Gateway;

use BH_WC_Duplicate_Gateway\Includes\BH_WC_Duplicate_Gateway;

/**
 * Class Plugin_WP_Mock_Test
 *
 * @coversNothing
 */
class Plugin_Unit_Test extends \Codeception\Test\Unit {

	protected function _before() {
		\WP_Mock::setUp();
	}

	// This is required for `'times' => 1` to be verified.
	protected function _tearDown() {
		parent::_tearDown();
		\WP_Mock::tearDown();
	}
	
	/**
	 * Verifies the plugin initialization.
	 */
	public function test_plugin_include() {

		$plugin_root_dir = dirname( __DIR__, 2 ) . '/src';

		\WP_Mock::userFunction(
			'plugin_dir_path',
			array(
				'args'   => array( \WP_Mock\Functions::type( 'string' ) ),
				'return' => $plugin_root_dir . '/',
			)
		);

		\WP_Mock::userFunction(
			'register_activation_hook'
		);

		\WP_Mock::userFunction(
			'register_deactivation_hook'
		);

		require_once $plugin_root_dir . '/bh-wc-duplicate-gateway.php';

		$this->assertArrayHasKey( 'bh_wc_duplicate_gateway', $GLOBALS );

		$this->assertInstanceOf( BH_WC_Duplicate_Gateway::class, $GLOBALS['bh_wc_duplicate_gateway'] );

	}


	/**
	 * Verifies the plugin does not output anything to screen.
	 */
	public function test_plugin_include_no_output() {

		$plugin_root_dir = dirname( __DIR__, 2 ) . '/src';

		\WP_Mock::userFunction(
			'plugin_dir_path',
			array(
				'args'   => array( \WP_Mock\Functions::type( 'string' ) ),
				'return' => $plugin_root_dir . '/',
			)
		);

		\WP_Mock::userFunction(
			'register_activation_hook'
		);

		\WP_Mock::userFunction(
			'register_deactivation_hook'
		);

		ob_start();

		require_once $plugin_root_dir . '/bh-wc-duplicate-gateway.php';

		$printed_output = ob_get_contents();

		ob_end_clean();

		$this->assertEmpty( $printed_output );

	}

}

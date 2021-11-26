<?php
/**
 * Tests for the root plugin file.
 *
 * @package BH_WC_Duplicate_Payment_Gateways
 * @author  BrianHenryIE <BrianHenryIE@gmail.com>
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\API\API;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\Includes\BH_WC_Duplicate_Payment_Gateways;

/**
 * Class Plugin_WP_Mock_Test
 */
class Plugin_Unit_Test extends \Codeception\Test\Unit {

	protected function setup(): void {
		\WP_Mock::setUp();
	}

	protected function tearDown(): void {
		\WP_Mock::tearDown();
		\Patchwork\restoreAll();
	}

	/**
	 * Verifies the plugin initialization.
	 */
	public function test_plugin_include() {

		// Prevents code-coverage counting, and removes the need to define the WordPress functions that are used in that class.
		\Patchwork\redefine(
			array( BH_WC_Duplicate_Payment_Gateways::class, '__construct' ),
			function( $api, $settings ) {}
		);

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

		// \WP_Mock::userFunction(
		// 'get_option',
		// array(
		// 'args'   => array( 'bh-wc-shipment-tracking-updates-log-level', 'notice' ),
		// 'return' => 'notice',
		// )
		// );

		// \WP_Mock::userFunction(
		// 'get_option',
		// array(
		// 'args'   => array( 'active_plugins' ),
		// 'return' => array(),
		// )
		// );

		// \WP_Mock::userFunction(
		// 'is_admin',
		// array(
		// 'return_arg' => false,
		// )
		// );

		// \WP_Mock::userFunction(
		// 'get_current_user_id'
		// );
		//
		// \WP_Mock::userFunction(
		// 'wp_normalize_path',
		// array(
		// 'return_arg' => true,
		// )
		// );

		// \WP_Mock::userFunction(
		// 'get_option',
		// array(
		// 'args'   => array( 'bh_wc_duplicate_payment_gateways_log_level', 'info' ),
		// 'return' => 'info',
		// )
		// );

		ob_start();

		include $plugin_root_dir . '/bh-wc-duplicate-payment-gateways.php';

		$printed_output = ob_get_contents();

		ob_end_clean();

		$this->assertEmpty( $printed_output );

		$this->assertArrayHasKey( 'bh_wc_duplicate_payment_gateways', $GLOBALS );

		$this->assertInstanceOf( API::class, $GLOBALS['bh_wc_duplicate_payment_gateways'] );

	}

}

<?php
/**
 *
 *
 * @package BH_WC_Duplicate_Payment_Gateways
 * @author  BrianHenryIE <BrianHenryIE@gmail.com>
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\Includes;

/**
 * Class Plugin_WP_Mock_Test
 *
 * @coversDefaultClass  \BrianHenryIE\WC_Duplicate_Payment_Gateways\Includes\I18n
 */
class I18n_Unit_Test extends \Codeception\Test\Unit {

	protected function setUp(): void {
		\WP_Mock::setUp();
	}

	// This is required for `'times' => 1` to be verified.
	protected function tearDown(): void {
		parent::_tearDown();
		\WP_Mock::tearDown();
	}

	/**
	 * Verify load_plugin_textdomain is correctly called.
	 *
	 * @covers ::load_plugin_textdomain
	 */
	public function test_load_plugin_textdomain() {

		global $plugin_root_dir;

		\WP_Mock::userFunction(
			'load_plugin_textdomain',
			array(
				'args' => array(
					'bh-wc-duplicate-payment-gateways',
					false,
					$plugin_root_dir . '/languages/',
				),
			)
		);
	}
}

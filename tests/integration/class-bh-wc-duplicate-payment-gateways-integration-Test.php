<?php
/**
 * Class Plugin_Test. Tests the root plugin setup.
 *
 * @package BH_WC_Duplicate_Payment_Gateways
 * @author     BrianHenryIE <BrianHenryIE@gmail.com>
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\API\API;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\Includes\BH_WC_Duplicate_Payment_Gateways;

/**
 * Verifies the plugin has been instantiated and added to PHP's $GLOBALS variable.
 */
class Plugin_Integration_Test extends \Codeception\TestCase\WPTestCase {

	/**
	 * Test the main plugin object is added to PHP's GLOBALS and that it is the correct class.
	 */
	public function test_plugin_instantiated() {

		$this->assertArrayHasKey( 'bh_wc_duplicate_payment_gateways', $GLOBALS );

		$this->assertInstanceOf( API::class, $GLOBALS['bh_wc_duplicate_payment_gateways'] );
	}

}

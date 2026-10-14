<?php

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\API;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\Settings_Interface;

/**
 * @coversDefaultClass \BrianHenryIE\WC_Duplicate_Payment_Gateways\API\API
 */
class API_WPUnit_Test extends \Codeception\TestCase\WPTestCase {

	public function test_is_duplicate_gateway_slug(): void {

		$settings = $this->makeEmpty( Settings_Interface::class );

		$api = new API( $settings );

		$result = $api->is_basename_or_slug_gateway_duplicate_grandparent( 'woocommerce/woocommerce.php' );

		$this->assertTrue( $result );

	}
}

<?php

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\API\Settings_Interface;
use WC_Gateway_BACS;

class Payment_Gateways_WPUnit_Test extends \Codeception\TestCase\WPTestCase {


	public function test_add_gateways() {

		$configs = array(
			array(
				'class_name' => WC_Gateway_BACS::class,
				'new_id'     => 'bacs_2',
			),
		);

		$settings = $this->makeEmpty(
			Settings_Interface::class,
			array(
				'get_configs' => $configs,
			)
		);

		$sut = new Payment_Gateways( $settings );

		$result = $sut->add_gateways( array() );

		$this->assertCount( 1, $result );

	}



}

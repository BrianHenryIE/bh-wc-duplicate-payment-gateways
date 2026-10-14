<?php

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\Settings_Interface;
use Codeception\TestCase\WPTestCase;

/**
 * @coversDefaultClass \BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Payment_Gateways
 */
class Payment_Gateways_WPUnit_Test extends WPTestCase {

	/**
	 * @param array<string, array{class_name:string, new_id:string}> $configs Duplicates keyed by id.
	 */
	protected function get_sut( array $configs ): Payment_Gateways {
		$settings = $this->makeEmpty(
			Settings_Interface::class,
			array(
				'get_configs' => $configs,
			)
		);

		return new Payment_Gateways( $settings );
	}

	/**
	 * @covers ::add_gateways
	 */
	public function test_add_gateways(): void {

		$sut = $this->get_sut(
			array(
				'duplicable_test_gateway_2' => array(
					'class_name' => Duplicable_Test_Gateway::class,
					'new_id'     => 'duplicable_test_gateway_2',
				),
			)
		);

		$result = $sut->add_gateways( array() );

		$this->assertCount( 1, $result );
		$this->assertInstanceOf( Duplicable_Test_Gateway::class, $result[0] );
		$this->assertEquals( 'duplicable_test_gateway_2', $result[0]->id );
	}

	/**
	 * The `Gateway_n` class aliases persist for the request; a new instance should be able to reuse them when they
	 * refer to the same gateway class.
	 *
	 * @covers ::add_gateways
	 */
	public function test_add_gateways_second_instance(): void {

		$configs = array(
			'duplicable_test_gateway_2' => array(
				'class_name' => Duplicable_Test_Gateway::class,
				'new_id'     => 'duplicable_test_gateway_2',
			),
		);

		$this->get_sut( $configs )->add_gateways( array() );

		$result = $this->get_sut( $configs )->add_gateways( array() );

		$this->assertCount( 1, $result );
	}
}

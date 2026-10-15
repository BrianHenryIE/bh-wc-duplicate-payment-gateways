<?php

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\API\Gateway_Copy_Interface;
use Codeception\TestCase\WPTestCase;

/**
 * @coversDefaultClass \BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Payment_Gateway
 */
class Payment_Gateway_WPUnit_Test extends WPTestCase {

	protected function get_duplicate(): Duplicable_Test_Gateway {
		return new class() extends Duplicable_Test_Gateway implements Gateway_Copy_Interface {
			public function __construct() {
				$this->id = 'duplicable_test_gateway_title';
				parent::__construct();
			}
		};
	}

	/**
	 * Otherwise the duplicate and the original have the same title in WooCommerce / Settings / Payments.
	 *
	 * @covers ::replace_title
	 */
	public function test_duplicate_title_includes_id_by_default(): void {

		$sut = new Payment_Gateway();

		$result = $sut->replace_title( 'Duplicable Test Gateway', $this->get_duplicate() );

		$this->assertEquals( 'Duplicable Test Gateway (duplicable_test_gateway_title)', $result );
	}

	/**
	 * @covers ::replace_title
	 */
	public function test_duplicate_title_uses_admin_title(): void {

		$duplicate = $this->get_duplicate();
		$duplicate->update_option( Payment_Gateway_Settings::ADMIN_METHOD_TITLE, 'Phone orders' );

		$sut = new Payment_Gateway();

		$result = $sut->replace_title( 'Duplicable Test Gateway', $duplicate );

		$this->assertEquals( 'Phone orders', $result );
	}

	/**
	 * @covers ::replace_title
	 */
	public function test_original_title_unchanged(): void {

		$sut = new Payment_Gateway();

		$result = $sut->replace_title( 'Duplicable Test Gateway', new Duplicable_Test_Gateway() );

		$this->assertEquals( 'Duplicable Test Gateway', $result );
	}
}

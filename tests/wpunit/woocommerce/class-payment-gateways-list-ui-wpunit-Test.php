<?php
/**
 * Tests for the (legacy, pre-WooCommerce 9.x) gateways list table column.
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\API_Interface;
use Codeception\TestCase\WPTestCase;
use WC_Gateway_Cheque;

/**
 * @coversDefaultClass \BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Payment_Gateways_List_UI
 */
class Payment_Gateways_List_UI_WPUnit_Test extends WPTestCase {

	/**
	 * @covers ::add_column
	 */
	public function test_add_column_after_action(): void {

		$sut = new Payment_Gateways_List_UI( $this->makeEmpty( API_Interface::class ) );

		$result = $sut->add_column(
			array(
				'sort'   => '',
				'name'   => 'Method',
				'action' => '',
				'status' => 'Enabled',
			)
		);

		$this->assertEquals( array( 'sort', 'name', 'action', 'duplicate', 'status' ), array_keys( $result ) );
	}

	/**
	 * @covers ::print_column_for_duplicate_ui
	 * @covers ::is_safe_to_duplicate
	 */
	public function test_print_column_duplicable_gateway_has_add_button(): void {

		$sut = new Payment_Gateways_List_UI( $this->makeEmpty( API_Interface::class ) );

		ob_start();
		$sut->print_column_for_duplicate_ui( new Duplicable_Test_Gateway() );
		$output = (string) ob_get_clean();

		$this->assertStringContainsString( 'duplicate-add', $output );
	}

	/**
	 * Core gateways set `$this->id` unconditionally in their constructors, so a duplicate's id would be overwritten.
	 *
	 * @covers ::print_column_for_duplicate_ui
	 * @covers ::is_safe_to_duplicate
	 */
	public function test_print_column_cheque_has_no_add_button(): void {

		$sut = new Payment_Gateways_List_UI( $this->makeEmpty( API_Interface::class ) );

		ob_start();
		$sut->print_column_for_duplicate_ui( new WC_Gateway_Cheque() );
		$output = (string) ob_get_clean();

		$this->assertStringNotContainsString( 'duplicate-add', $output );
	}
}

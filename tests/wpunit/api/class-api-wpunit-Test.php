<?php
/**
 * Tests for the API class against real WooCommerce gateways.
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\API;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Duplicable_Test_Gateway;
use Codeception\TestCase\WPTestCase;

/**
 * @coversDefaultClass \BrianHenryIE\WC_Duplicate_Payment_Gateways\API\API
 */
class API_WPUnit_Test extends WPTestCase {

	protected function setUp(): void {
		parent::setUp();

		add_filter(
			'woocommerce_payment_gateways',
			function ( array $gateways ): array {
				$gateways[] = Duplicable_Test_Gateway::class;
				return $gateways;
			}
		);

		$this->reload_gateways();
	}

	/**
	 * `WC_Payment_Gateways::init()` appends to the existing list, so empty it first.
	 */
	protected function reload_gateways(): void {
		WC()->payment_gateways()->payment_gateways = array();
		WC()->payment_gateways()->init();
	}

	/**
	 * @covers ::add_new_duplicate_payment_gateway
	 */
	public function test_add_new_duplicate_saves_config(): void {

		$settings = new Settings();
		$sut      = new API( $settings );

		$result = $sut->add_new_duplicate_payment_gateway( 'duplicable_test_gateway', 'duplicable_test_gateway_2' );

		$this->assertTrue( $result['success'] );

		$configs = $settings->get_configs();
		$this->assertArrayHasKey( 'duplicable_test_gateway_2', $configs );
		$this->assertEquals( Duplicable_Test_Gateway::class, $configs['duplicable_test_gateway_2']['class_name'] );
	}

	/**
	 * @covers ::add_new_duplicate_payment_gateway
	 */
	public function test_add_new_duplicate_fails_when_id_exists(): void {

		$sut = new API( new Settings() );

		$result = $sut->add_new_duplicate_payment_gateway( 'duplicable_test_gateway', 'cheque' );

		$this->assertFalse( $result['success'] );
	}

	/**
	 * @covers ::add_new_duplicate_payment_gateway
	 */
	public function test_add_new_duplicate_fails_when_gateway_does_not_exist(): void {

		$sut = new API( new Settings() );

		$result = $sut->add_new_duplicate_payment_gateway( 'no_such_gateway', 'no_such_gateway_2' );

		$this->assertFalse( $result['success'] );
	}

	/**
	 * @covers ::delete_duplicate_payment_gateway
	 */
	public function test_delete_refuses_original_gateway(): void {

		$sut = new API( new Settings() );

		$result = $sut->delete_duplicate_payment_gateway( 'cheque' );

		$this->assertFalse( $result['success'] );
	}

	/**
	 * @covers ::get_plugin_gateways
	 */
	public function test_get_plugin_gateways_lists_woocommerce_core_gateways(): void {

		$sut = new API( new Settings() );

		// It only calculates when there are duplicates.
		$sut->add_new_duplicate_payment_gateway( 'duplicable_test_gateway', 'duplicable_test_gateway_2' );

		$result = $sut->get_plugin_gateways( 'woocommerce/woocommerce.php' );

		$this->assertArrayHasKey( 'cheque', $result );
		$this->assertArrayHasKey( 'bacs', $result );
	}
}

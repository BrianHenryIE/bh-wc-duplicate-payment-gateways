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

	/**
	 * `is_callable( 'parent::__construct' )` is deprecated since PHP 8.2.
	 *
	 * @covers ::add_gateways
	 */
	public function test_add_gateways_no_deprecations(): void {

		$sut = $this->get_sut(
			array(
				'duplicable_test_gateway_2' => array(
					'class_name' => Duplicable_Test_Gateway::class,
					'new_id'     => 'duplicable_test_gateway_2',
				),
			)
		);

		$deprecations = array();
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler
		set_error_handler(
			function ( int $errno, string $errstr ) use ( &$deprecations ): bool {
				$deprecations[] = $errstr;
				return true;
			},
			E_DEPRECATED
		);

		try {
			$sut->add_gateways( array() );
		} finally {
			restore_error_handler();
		}

		$this->assertEmpty( $deprecations, implode( PHP_EOL, $deprecations ) );
	}

	/**
	 * WooCommerce runs the `woocommerce_payment_gateways` filter again, e.g. `WC_Payment_Gateways::init()` after saving
	 * the gateway order. Every duplicate should be returned each time.
	 *
	 * @covers ::add_gateways
	 */
	public function test_add_gateways_twice_returns_all_duplicates(): void {

		$sut = $this->get_sut(
			array(
				'duplicable_test_gateway_2' => array(
					'class_name' => Duplicable_Test_Gateway::class,
					'new_id'     => 'duplicable_test_gateway_2',
				),
				'duplicable_test_gateway_3' => array(
					'class_name' => Duplicable_Test_Gateway::class,
					'new_id'     => 'duplicable_test_gateway_3',
				),
			)
		);

		$sut->add_gateways( array() );
		$result = $sut->add_gateways( array() );

		$this->assertCount( 2, $result );
	}

	/**
	 * The block checkout gets the available gateways from the Store API, where `is_checkout()` is false, so a duplicate
	 * set to "hide on checkout" must be removed outside wp-admin generally, not only when `is_checkout()`.
	 *
	 * @covers ::hide_gateways_at_checkout
	 */
	public function test_hide_gateways_at_checkout_in_store_api_request(): void {

		$sut = $this->get_sut(
			array(
				'duplicable_test_gateway_hidden' => array(
					'class_name' => Duplicable_Test_Gateway::class,
					'new_id'     => 'duplicable_test_gateway_hidden',
				),
			)
		);

		$duplicates = $sut->add_gateways( array() );
		$duplicate  = $duplicates[0];
		$this->assertInstanceOf( \WC_Payment_Gateway::class, $duplicate );
		$duplicate->update_option( Payment_Gateway_Settings::HIDE_ON_CHECKOUT, 'yes' );

		$this->assertFalse( is_checkout() );
		$this->assertFalse( is_admin() );

		$result = $sut->hide_gateways_at_checkout(
			array(
				'duplicable_test_gateway'        => new Duplicable_Test_Gateway(),
				'duplicable_test_gateway_hidden' => $duplicate,
			)
		);

		$this->assertArrayHasKey( 'duplicable_test_gateway', $result );
		$this->assertArrayNotHasKey( 'duplicable_test_gateway_hidden', $result );
	}

	/**
	 * In wp-admin, e.g. when a shop manager creates an order, hidden duplicates are still available.
	 *
	 * @covers ::hide_gateways_at_checkout
	 */
	public function test_hide_gateways_at_checkout_not_in_admin(): void {

		$sut = $this->get_sut(
			array(
				'duplicable_test_gateway_hidden' => array(
					'class_name' => Duplicable_Test_Gateway::class,
					'new_id'     => 'duplicable_test_gateway_hidden',
				),
			)
		);

		$duplicate = $sut->add_gateways( array() )[0];
		$this->assertInstanceOf( \WC_Payment_Gateway::class, $duplicate );
		$duplicate->update_option( Payment_Gateway_Settings::HIDE_ON_CHECKOUT, 'yes' );

		set_current_screen( 'edit-shop_order' );

		$result = $sut->hide_gateways_at_checkout( array( 'duplicable_test_gateway_hidden' => $duplicate ) );

		set_current_screen( 'front' );

		$this->assertArrayHasKey( 'duplicable_test_gateway_hidden', $result );
	}

	/**
	 * The trait was listed in a stale `autoload-classmap.php` with a path that no longer existed; it is found by the
	 * Alley Interactive autoloader instead.
	 */
	public function test_trait_is_autoloadable(): void {
		$this->assertTrue( trait_exists( CC_Gateway_Parameter_Names_Trait::class ) );
	}
}

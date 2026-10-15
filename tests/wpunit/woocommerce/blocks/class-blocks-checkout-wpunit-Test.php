<?php

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Blocks;

use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\API\Gateway_Copy_Interface;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\Settings_Interface;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Duplicable_Test_Gateway;
use Codeception\TestCase\WPTestCase;

/**
 * @coversDefaultClass \BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Blocks\Blocks_Checkout
 */
class Blocks_Checkout_WPUnit_Test extends WPTestCase {

	/**
	 * @param bool $has_fields Does the duplicate have payment fields.
	 */
	protected function register_duplicate( bool $has_fields ): PaymentMethodRegistry {

		$duplicate             = new class() extends Duplicable_Test_Gateway implements Gateway_Copy_Interface {
			public function __construct() {
				$this->id = 'duplicable_test_gateway_blocks';
				parent::__construct();
			}
		};
		$duplicate->has_fields = $has_fields;

		add_filter(
			'woocommerce_payment_gateways',
			function ( array $gateways ) use ( $duplicate ): array {
				$gateways[] = $duplicate;
				return $gateways;
			}
		);
		WC()->payment_gateways()->payment_gateways = array();
		WC()->payment_gateways()->init();

		$settings = $this->makeEmpty(
			Settings_Interface::class,
			array(
				'get_plugin_basename' => 'bh-wc-duplicate-payment-gateways/bh-wc-duplicate-payment-gateways.php',
				'get_plugin_version'  => '1.6.0',
			)
		);

		$registry = new PaymentMethodRegistry();

		( new Blocks_Checkout( $settings ) )->register_duplicates( $registry );

		return $registry;
	}

	/**
	 * @covers ::register_duplicates
	 */
	public function test_registers_duplicate_without_fields(): void {

		$registry = $this->register_duplicate( false );

		$this->assertTrue( $registry->is_registered( 'duplicable_test_gateway_blocks' ) );

		$integration = $registry->get_registered( 'duplicable_test_gateway_blocks' );
		$this->assertInstanceOf( Duplicate_Payment_Method_Type::class, $integration );

		$data = $integration->get_payment_method_data();
		$this->assertArrayHasKey( 'bh_wc_duplicate_payment_gateway', $data );
	}

	/**
	 * The generic integration cannot render another plugin's payment fields.
	 *
	 * @covers ::register_duplicates
	 */
	public function test_does_not_register_duplicate_with_fields(): void {

		$registry = $this->register_duplicate( true );

		$this->assertFalse( $registry->is_registered( 'duplicable_test_gateway_blocks' ) );
	}

	/**
	 * @covers ::register_duplicates
	 */
	public function test_filter_can_register_duplicate_with_fields(): void {

		add_filter( 'bh_wc_duplicate_payment_gateways_register_blocks_integration', '__return_true' );

		$registry = $this->register_duplicate( true );

		$this->assertTrue( $registry->is_registered( 'duplicable_test_gateway_blocks' ) );
	}
}

<?php

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Blocks;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;
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
	 * @param bool                  $has_fields Does the duplicate have payment fields.
	 * @param ?IntegrationInterface $original_integration The original gateway's own integration, already registered.
	 */
	protected function register_duplicate( bool $has_fields, ?IntegrationInterface $original_integration = null ): PaymentMethodRegistry {

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
				$gateways[] = Duplicable_Test_Gateway::class;
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
		if ( $original_integration ) {
			$registry->register( $original_integration );
		}

		( new Blocks_Checkout( $settings, new Gateway_Integration_Duplicator() ) )->register_duplicates( $registry );

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

	/**
	 * When the original gateway's own integration takes its name from the gateway passed to its constructor, a new
	 * instance of it is registered for the duplicate, so the duplicate gets the gateway's own payment fields.
	 *
	 * @covers ::register_duplicates
	 */
	public function test_registers_original_integration_class_for_duplicate(): void {

		$registry = $this->register_duplicate(
			true,
			new Gateway_Named_Test_Payment_Method_Type( new Duplicable_Test_Gateway() )
		);

		$integration = $registry->get_registered( 'duplicable_test_gateway_blocks' );
		$this->assertInstanceOf( Gateway_Named_Test_Payment_Method_Type::class, $integration );
		$this->assertEquals( 'duplicable_test_gateway_blocks', $integration->gateway->id );

		// The original's is unchanged.
		$this->assertTrue( $registry->is_registered( 'duplicable_test_gateway' ) );
	}

	/**
	 * When the original's integration has a hardcoded name, the generic integration is used (for gateways without
	 * payment fields).
	 *
	 * @covers ::register_duplicates
	 */
	public function test_falls_back_when_original_integration_name_is_hardcoded(): void {

		$registry = $this->register_duplicate(
			false,
			new Hardcoded_Name_Test_Payment_Method_Type( new Duplicable_Test_Gateway() )
		);

		$this->assertInstanceOf(
			Duplicate_Payment_Method_Type::class,
			$registry->get_registered( 'duplicable_test_gateway_blocks' )
		);
	}

	/**
	 * An exception from another plugin's code, e.g. a filter or an integration, must not break the block checkout.
	 *
	 * @covers ::register_duplicates
	 */
	public function test_exception_is_caught(): void {

		// @phpstan-ignore return.missing
		add_filter(
			'bh_wc_duplicate_payment_gateways_register_blocks_integration',
			function (): bool {
				throw new \Exception( 'Another plugin\'s bug.' );
			}
		);

		$registry = $this->register_duplicate( false );

		$this->assertFalse( $registry->is_registered( 'duplicable_test_gateway_blocks' ) );
	}
}

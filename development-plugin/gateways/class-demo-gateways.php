<?php
/**
 * Register the demo gateways with WooCommerce.
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\Development_Plugin\Gateways;

use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;

/**
 * Adds four gateways which demonstrate what can and cannot be duplicated.
 */
class Demo_Gateways {

	/**
	 * Add filters.
	 */
	public function register_hooks(): void {
		add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateways' ) );
		add_action( 'woocommerce_blocks_payment_method_type_registration', array( $this, 'register_blocks_integration' ) );
	}

	/**
	 * Register the demo gateways with the block checkout.
	 *
	 * @hooked woocommerce_blocks_payment_method_type_registration
	 * @see \Automattic\WooCommerce\Blocks\Payments\Api::initialize_payment_method_types()
	 *
	 * @param PaymentMethodRegistry $payment_method_registry The block checkout's payment methods.
	 */
	public function register_blocks_integration( PaymentMethodRegistry $payment_method_registry ): void {
		$payment_method_registry->register( new Demo_Gateway_Blocks() );

		// For the original only; Duplicate Payment Gateways registers a new instance of it for each duplicate.
		$gateways = WC()->payment_gateways()->payment_gateways();
		if ( isset( $gateways['demo_gateway_fields'] ) && $gateways['demo_gateway_fields'] instanceof Demo_Gateway_With_Fields ) {
			$payment_method_registry->register( new Demo_Gateway_With_Fields_Blocks( $gateways['demo_gateway_fields'] ) );
		}
	}

	/**
	 * Add the demo gateways by class name, as most gateway plugins do.
	 *
	 * @hooked woocommerce_payment_gateways
	 * @see \WC_Payment_Gateways::init()
	 *
	 * @param array<string|\WC_Payment_Gateway> $gateways Gateway class names or instances.
	 * @return array<string|\WC_Payment_Gateway>
	 */
	public function add_gateways( array $gateways ): array {
		$gateways[] = Demo_Gateway::class;
		$gateways[] = Demo_Gateway_With_Fields::class;
		$gateways[] = new Demo_Gateway_Hardcoded_Id();
		$gateways[] = new Demo_Gateway_With_Constructor_Arguments( 'demo_constructor_args' );
		return $gateways;
	}
}

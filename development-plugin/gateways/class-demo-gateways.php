<?php
/**
 * Register the demo gateways with WooCommerce.
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\Development_Plugin\Gateways;

/**
 * Adds three gateways which demonstrate what can and cannot be duplicated.
 */
class Demo_Gateways {

	/**
	 * Add filters.
	 */
	public function register_hooks(): void {
		add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateways' ) );
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
		$gateways[] = new Demo_Gateway_Hardcoded_Id();
		$gateways[] = new Demo_Gateway_With_Constructor_Arguments( 'demo_constructor_args' );
		return $gateways;
	}
}

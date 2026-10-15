<?php
/**
 * Register a block checkout integration for each duplicate of a gateway without payment fields.
 *
 * Gateways with payment fields (e.g. credit card forms) render them with their own block checkout JavaScript, which
 * is registered for the original gateway's id only, so their duplicates cannot be supported generically.
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Blocks;

use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\API\Gateway_Copy_Interface;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\Settings_Interface;
use WC_Payment_Gateway;

/**
 * Hooked to the block checkout's payment method registration.
 */
class Blocks_Checkout {

	/**
	 * For the plugin URL and version.
	 *
	 * @var Settings_Interface
	 */
	protected Settings_Interface $settings;

	/**
	 * Constructor.
	 *
	 * @param Settings_Interface $settings The plugin settings.
	 */
	public function __construct( Settings_Interface $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Register an integration for each duplicate.
	 *
	 * @hooked woocommerce_blocks_payment_method_type_registration
	 * @see \Automattic\WooCommerce\Blocks\Payments\Api::initialize_payment_method_types()
	 *
	 * @param PaymentMethodRegistry $payment_method_registry The block checkout's payment methods.
	 */
	public function register_duplicates( PaymentMethodRegistry $payment_method_registry ): void {

		/**
		 * All payment gateways registered with WooCommerce.
		 *
		 * @var array<string, WC_Payment_Gateway> $gateways
		 */
		$gateways = WC()->payment_gateways()->payment_gateways();

		$plugin_url = plugin_dir_url( $this->settings->get_plugin_basename() );

		foreach ( $gateways as $gateway ) {

			if ( ! ( $gateway instanceof Gateway_Copy_Interface ) ) {
				continue;
			}

			// Another plugin may already have registered one, e.g. a gateway plugin aware of duplicates.
			if ( $payment_method_registry->is_registered( $gateway->id ) ) {
				continue;
			}

			/**
			 * Should the generic block checkout integration (title and description, no payment fields) be registered
			 * for this duplicate? By default, only for gateways without payment fields.
			 *
			 * @param bool               $register Whether to register the integration.
			 * @param WC_Payment_Gateway $gateway The duplicate gateway.
			 */
			$register = (bool) apply_filters( 'bh_wc_duplicate_payment_gateways_register_blocks_integration', ! $gateway->has_fields, $gateway );

			if ( ! $register ) {
				continue;
			}

			$payment_method_registry->register(
				new Duplicate_Payment_Method_Type( $gateway, $plugin_url, $this->settings->get_plugin_version() )
			);
		}
	}
}

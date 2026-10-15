<?php
/**
 * Register a block checkout integration for each duplicate.
 *
 * Preferably a new instance of the gateway plugin's own integration, for the duplicate, when the plugin supports it.
 * Otherwise, for gateways without payment fields, a generic integration showing the title and description.
 *
 * Gateways with payment fields (e.g. credit card forms) render them with their own block checkout JavaScript, so
 * their duplicates cannot be supported generically.
 *
 * @see Gateway_Integration_Duplicator
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Blocks;

use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\API\Gateway_Copy_Interface;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\Settings_Interface;
use Throwable;
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
	 * Creates the gateway plugin's own integration for a duplicate, when possible.
	 *
	 * @var Gateway_Integration_Duplicator
	 */
	protected Gateway_Integration_Duplicator $gateway_integration_duplicator;

	/**
	 * Constructor.
	 *
	 * @param Settings_Interface             $settings The plugin settings.
	 * @param Gateway_Integration_Duplicator $gateway_integration_duplicator Creates gateway plugins' integrations for duplicates.
	 */
	public function __construct( Settings_Interface $settings, Gateway_Integration_Duplicator $gateway_integration_duplicator ) {
		$this->settings                       = $settings;
		$this->gateway_integration_duplicator = $gateway_integration_duplicator;
	}

	/**
	 * Register an integration for each duplicate.
	 *
	 * Hooked late, so gateway plugins have registered their own integrations (for their gateways, and perhaps for
	 * duplicates).
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

			// Another plugin's integration code runs here; one duplicate's failure should not break the checkout.
			try {
				$this->register_duplicate( $payment_method_registry, $gateways, $gateway, $plugin_url );
			} catch ( Throwable $exception ) {
				continue;
			}
		}
	}

	/**
	 * Register the gateway plugin's own integration for the duplicate, if possible, otherwise the generic one, if
	 * appropriate.
	 *
	 * @param PaymentMethodRegistry             $payment_method_registry The block checkout's payment methods.
	 * @param array<string, WC_Payment_Gateway> $gateways All payment gateways.
	 * @param WC_Payment_Gateway                $gateway The duplicate gateway.
	 * @param string                            $plugin_url This plugin's URL, for the generic integration's script.
	 */
	protected function register_duplicate( PaymentMethodRegistry $payment_method_registry, array $gateways, WC_Payment_Gateway $gateway, string $plugin_url ): void {

		// Another plugin may already have registered one, e.g. a gateway plugin aware of duplicates.
		if ( $payment_method_registry->is_registered( $gateway->id ) ) {
			return;
		}

		$original_id = $this->get_original_gateway_id( $gateways, $gateway );
		if ( ! is_null( $original_id ) && $payment_method_registry->is_registered( $original_id ) ) {
			$original_integration = $payment_method_registry->get_registered( $original_id );
			$integration          = is_null( $original_integration )
				? null
				: $this->gateway_integration_duplicator->duplicate( $original_integration, $gateway );
			if ( ! is_null( $integration ) ) {
				$payment_method_registry->register( $integration );
				return;
			}
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
			return;
		}

		$payment_method_registry->register(
			new Duplicate_Payment_Method_Type( $gateway, $plugin_url, $this->settings->get_plugin_version() )
		);
	}

	/**
	 * Find the id of the gateway the duplicate was copied from.
	 *
	 * The duplicate's parent class is the original gateway's class (via its `Gateway_n` alias).
	 *
	 * @param array<string, WC_Payment_Gateway> $gateways All payment gateways.
	 * @param WC_Payment_Gateway                $duplicate The duplicate gateway.
	 */
	protected function get_original_gateway_id( array $gateways, WC_Payment_Gateway $duplicate ): ?string {

		$original_class = get_parent_class( $duplicate );

		foreach ( $gateways as $gateway ) {
			if ( ! ( $gateway instanceof Gateway_Copy_Interface ) && get_class( $gateway ) === $original_class ) {
				return $gateway->id;
			}
		}

		return null;
	}
}

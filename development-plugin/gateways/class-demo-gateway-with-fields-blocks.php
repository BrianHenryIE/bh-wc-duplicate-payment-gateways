<?php
/**
 * The demo gateway with fields' block checkout integration, written so Duplicate Payment Gateways can use it for
 * duplicates:
 *
 * * its constructor takes the gateway, and its name is the gateway's id;
 * * its script registers each payment method it has data for (identified by `is_demo_gateway_with_fields`), rather
 *   than a hardcoded name.
 *
 * Like most gateway plugins, it is only registered for the original gateway; Duplicate Payment Gateways registers a
 * new instance of it for each duplicate.
 *
 * @see \BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Blocks\Gateway_Integration_Duplicator
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\Development_Plugin\Gateways;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * Title, description and the reference field.
 */
class Demo_Gateway_With_Fields_Blocks extends AbstractPaymentMethodType {

	/**
	 * The gateway (instance) this integration is for.
	 *
	 * @var Demo_Gateway_With_Fields
	 */
	protected Demo_Gateway_With_Fields $gateway;

	/**
	 * Constructor.
	 *
	 * @param Demo_Gateway_With_Fields $gateway The original gateway, or a duplicate.
	 */
	public function __construct( Demo_Gateway_With_Fields $gateway ) {
		$this->gateway = $gateway;
		$this->name    = $gateway->id;
	}

	/**
	 * Load the gateway's settings.
	 */
	public function initialize(): void {
		$this->settings = $this->gateway->settings;
	}

	/**
	 * The block checkout separately checks the gateway is available for the cart.
	 */
	public function is_active(): bool {
		return wc_string_to_bool( $this->gateway->enabled );
	}

	/**
	 * Register the script (once, for every instance).
	 *
	 * @return string[]
	 */
	public function get_payment_method_script_handles(): array {
		wp_register_script(
			'demo-gateway-with-fields-blocks',
			plugin_dir_url( __DIR__ ) . 'assets/demo-gateway-with-fields-blocks.js',
			array( 'wc-blocks-registry', 'wc-settings', 'wp-element', 'wp-html-entities' ),
			'1.0.0',
			true
		);
		return array( 'demo-gateway-with-fields-blocks' );
	}

	/**
	 * Read in JavaScript with `wc.wcSettings.getSetting( 'paymentMethodData' )[ name ]`.
	 *
	 * @return array{title:string, description:string, reference_field_name:string, is_demo_gateway_with_fields:true}
	 */
	public function get_payment_method_data(): array {
		return array(
			'title'                       => $this->gateway->get_title(),
			'description'                 => $this->gateway->get_description(),
			'reference_field_name'        => $this->gateway->get_reference_field_name(),
			'is_demo_gateway_with_fields' => true,
		);
	}
}

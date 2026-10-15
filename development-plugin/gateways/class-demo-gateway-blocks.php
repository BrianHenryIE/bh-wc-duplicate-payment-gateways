<?php
/**
 * The demo gateway's own block checkout integration, as a gateway plugin would provide.
 *
 * It is registered for `demo_gateway` only; duplicates get Duplicate Payment Gateways' generic integration.
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\Development_Plugin\Gateways;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * Title and description.
 */
class Demo_Gateway_Blocks extends AbstractPaymentMethodType {

	/**
	 * The gateway id.
	 *
	 * @var string
	 */
	protected $name = 'demo_gateway';

	/**
	 * Load the gateway's settings.
	 */
	public function initialize(): void {
		$this->settings = (array) get_option( 'woocommerce_demo_gateway_settings', array() );
	}

	/**
	 * Enabled unless disabled.
	 */
	public function is_active(): bool {
		return 'no' !== $this->get_setting( 'enabled', 'yes' );
	}

	/**
	 * Register the script.
	 *
	 * @return string[]
	 */
	public function get_payment_method_script_handles(): array {
		wp_register_script(
			'demo-gateway-blocks',
			plugin_dir_url( __DIR__ ) . 'assets/demo-gateway-blocks.js',
			array( 'wc-blocks-registry', 'wc-settings', 'wp-element', 'wp-html-entities' ),
			'1.0.0',
			true
		);
		return array( 'demo-gateway-blocks' );
	}

	/**
	 * Read in JavaScript with `wc.wcSettings.getSetting( 'demo_gateway_data' )`.
	 *
	 * @return array{title:string, description:string}
	 */
	public function get_payment_method_data(): array {
		return array(
			'title'       => (string) $this->get_setting( 'title', 'Demo Gateway' ),
			'description' => (string) $this->get_setting( 'description', 'Pay with the demo gateway.' ),
		);
	}
}

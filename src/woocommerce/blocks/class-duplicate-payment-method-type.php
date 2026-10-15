<?php
/**
 * A block checkout integration for a duplicate gateway: its title and description, no payment fields.
 *
 * The block checkout only lists gateways that have a blocks integration registered under their id. A gateway
 * plugin's own integration is registered under the original gateway's id, so duplicates need their own.
 *
 * @see https://developer.woocommerce.com/docs/block-development/extensible-blocks/cart-and-checkout-blocks/checkout-payment-methods/payment-method-integration/
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Blocks;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use WC_Payment_Gateway;

/**
 * The data the shared `assets/bh-wc-duplicate-payment-gateways-blocks.js` registers each duplicate with.
 */
class Duplicate_Payment_Method_Type extends AbstractPaymentMethodType {

	const SCRIPT_HANDLE = 'bh-wc-duplicate-payment-gateways-blocks';

	/**
	 * The duplicate gateway.
	 *
	 * @var WC_Payment_Gateway
	 */
	protected WC_Payment_Gateway $gateway;

	/**
	 * The URL of the plugin's directory, for the script's URL.
	 *
	 * @var string
	 */
	protected string $plugin_url;

	/**
	 * The plugin version, for the script's cache-busting.
	 *
	 * @var string
	 */
	protected string $plugin_version;

	/**
	 * Constructor.
	 *
	 * @param WC_Payment_Gateway $gateway The duplicate gateway.
	 * @param string             $plugin_url The plugin directory's URL, with trailing slash.
	 * @param string             $plugin_version The plugin version.
	 */
	public function __construct( WC_Payment_Gateway $gateway, string $plugin_url, string $plugin_version ) {
		$this->gateway        = $gateway;
		$this->name           = $gateway->id;
		$this->plugin_url     = $plugin_url;
		$this->plugin_version = $plugin_version;
	}

	/**
	 * Load the gateway's settings.
	 */
	public function initialize(): void {
		$this->settings = $this->gateway->settings;
	}

	/**
	 * The block checkout separately checks the gateway is available for the cart.
	 *
	 * @see \WC_Payment_Gateways::get_available_payment_gateways()
	 */
	public function is_active(): bool {
		return wc_string_to_bool( $this->gateway->enabled );
	}

	/**
	 * One script registers every duplicate.
	 *
	 * @return string[]
	 */
	public function get_payment_method_script_handles(): array {
		wp_register_script(
			self::SCRIPT_HANDLE,
			$this->plugin_url . 'assets/bh-wc-duplicate-payment-gateways-blocks.js',
			array( 'wc-blocks-registry', 'wc-settings', 'wp-element', 'wp-html-entities' ),
			$this->plugin_version,
			true
		);
		return array( self::SCRIPT_HANDLE );
	}

	/**
	 * Read in JavaScript with `wc.wcSettings.getSetting( 'paymentMethodData' )[ id ]`.
	 *
	 * @return array{title:string, description:string, supports:string[], bh_wc_duplicate_payment_gateway:true}
	 */
	public function get_payment_method_data(): array {
		return array(
			'title'                           => $this->gateway->get_title(),
			'description'                     => $this->gateway->get_description(),
			'supports'                        => array_values( array_filter( $this->gateway->supports, array( $this->gateway, 'supports' ) ) ),
			'bh_wc_duplicate_payment_gateway' => true,
		);
	}
}

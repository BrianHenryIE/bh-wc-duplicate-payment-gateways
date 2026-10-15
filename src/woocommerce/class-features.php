<?php
/**
 * Declare compatibility with WooCommerce features, otherwise WooCommerce warns it is "uncertain".
 *
 * The plugin does not read or write orders directly, so is compatible with High-Performance Order Storage, and it
 * registers duplicates with the block checkout.
 *
 * @see https://developer.woocommerce.com/docs/features/high-performance-order-storage/recipe-book/#declaring-extension-incompatibility
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce;

use Automattic\WooCommerce\Utilities\FeaturesUtil;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\Settings_Interface;

/**
 * Hooked before WooCommerce initializes.
 */
class Features {

	/**
	 * For the plugin basename.
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
	 * Declare compatibility with HPOS and the cart/checkout blocks.
	 *
	 * @hooked before_woocommerce_init
	 * @see \WooCommerce::init()
	 */
	public function declare_compatibility(): void {
		if ( ! class_exists( FeaturesUtil::class ) ) {
			return;
		}

		FeaturesUtil::declare_compatibility( 'custom_order_tables', $this->settings->get_plugin_basename(), true );
		FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', $this->settings->get_plugin_basename(), true );
	}
}

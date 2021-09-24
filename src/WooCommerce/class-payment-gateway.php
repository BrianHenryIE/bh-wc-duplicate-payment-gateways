<?php
/**
 * Not an actual gateway, just filters.
 *
 * If a new gateway title or description has been set, display that instead of the parent gateway's description/...
 *
 * @package     brianhenryie/bh-wc-duplicate-payment-gateways
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce;

use WC_Payment_Gateway;

/**
 * Replace title, replace description.
 */
class Payment_Gateway {

	// return apply_filters( 'woocommerce_gateway_method_title', $this->method_title, $this );

	/**
	 * If a new title has been specified for the gateway, replace the parent title.
	 *
	 * @hooked woocommerce_gateway_method_title
	 * @see WC_Payment_Gateway::get_method_title()
	 *
	 * @param string             $method_title
	 * @param WC_Payment_Gateway $gateway
	 * @return string
	 */
	public function replace_title( string $method_title, WC_Payment_Gateway $gateway ): string {

		$new_title = $gateway->get_option( Payment_Gateway_Settings::ADMIN_METHOD_TITLE );

		if ( ! empty( $new_title ) ) {
			return $new_title;
		}

		return $method_title;
	}

	/**
	 * @hooked woocommerce_gateway_method_description
	 * @see WC_Payment_Gateway::get_method_description()
	 *
	 * @param string             $method_description
	 * @param WC_Payment_Gateway $gateway
	 * @return string
	 */
	public function replace_description( string $method_description, WC_Payment_Gateway $gateway ): string {

		$new_description = $gateway->get_option( Payment_Gateway_Settings::ADMIN_DESCRIPTION );

		if ( ! empty( $new_description ) ) {
			return $new_description;
		}

		return $method_description;
	}

}

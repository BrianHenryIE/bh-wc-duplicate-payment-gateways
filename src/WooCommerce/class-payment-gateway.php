<?php
/**
 * Not an actual gateway, just filters.
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce;

use WC_Payment_Gateway;

/**
 *
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

		$new_title = $gateway->get_option( Payment_Gateway_Settings::ADMIN_TITLE );

		if ( ! empty( $new_title ) ) {
			return $new_title;
		}

		return $method_title;
	}

}

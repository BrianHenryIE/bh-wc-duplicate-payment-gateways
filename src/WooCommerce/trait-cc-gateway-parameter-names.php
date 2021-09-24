<?php
/**
 * When duplicating a CC gateway, the second thing to be aware of is the $_POST variable names.
 *
 * The credit card form incorporates the gateway id in its input ids.
 *
 * @see WC_Payment_Gateway_CC::form()
 *
 * @package     brianhenryie/bh-wc-duplicate-payment-gateways
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce;

/**
 * Override and call parent WC_Payment_Gateway::validate_fields().
 */
trait CC_Gateway_Parameter_Names_Trait {

	/**
	 * If the POSTed credit card fields contain the wrong gateway id, replace it with the duplicate's gateway id.
	 *
	 * @return bool
	 *
	 * @phpcs:disable WordPress.Security.NonceVerification.Missing
	 */
	public function validate_fields() {

		if ( $this instanceof \WC_Payment_Gateway_CC ) {

			foreach ( $_POST as $key => $value ) {
				if ( 1 === preg_match( '/^(.*).card.number$/', $key, $output_array ) ) {
					$parent_gateway_id = $output_array[1];
				}
			}
			if ( $parent_gateway_id !== $this->id ) {
				foreach ( $_POST as $key => $value ) {
					if ( false !== stristr( $key, $parent_gateway_id ) ) {
						$new_key           = str_replace( $parent_gateway_id, $this->id, $key );
						$_POST[ $new_key ] = $value;
					}
				}
			}
		}

		return parent::validate_fields();
	}
}

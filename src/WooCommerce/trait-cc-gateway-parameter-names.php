<?php
/**
 * When duplicating a CC gateway, the second thing to be aware of is the $_POST variable names.
 *
 * The credit card form incorporates the gateway id in its input ids.
 * But some gateways also hard-code the form.
 *
 * So we need the subclass to sometimes change the HTML form input ids from the id hardcoded in the form, to the id
 * of the new gateway.
 * And sometime we need to change (copy) the HTML form inputs ids from the subclass id to the parent class ids which
 * are presumably hardcoded in the parent validate and process-payment methods.
 *
 * @see WC_Payment_Gateway_CC::form()
 *
 * @package     brianhenryie/bh-wc-duplicate-payment-gateways
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce;

use WC_Payment_Gateway;

/**
 * Override and call parent WC_Payment_Gateway::validate_fields().
 */
trait CC_Gateway_Parameter_Names_Trait {

	/**
	 * If the POSTed credit card fields contain the wrong gateway id, replace it with the duplicate's gateway id.
	 *
	 * @see \WC_Payment_Gateway_CC::form()
	 *
	 * @return bool
	 *
	 * @phpcs:disable WordPress.Security.NonceVerification.Missing
	 */
	public function validate_fields() {

		if ( $this instanceof \WC_Payment_Gateway_CC ) {

			$this_subclass_id = $this->id;

			$parent_classes = class_parents( $this );
			// @phpstan-ignore-next-line WC_Payment_Gateway itself is a subclass, so the next line will never return false.
			$first_parent_class = array_key_first( $parent_classes );
			/**
			 * Instance of the gateway that has been duplicated.
			 *
			 * @var WC_Payment_Gateway $parent_instance
			 */
			$parent_instance   = new $first_parent_class();
			$parent_gateway_id = $parent_instance->id;

			if ( $parent_gateway_id !== $this_subclass_id ) {
				foreach ( $_POST as $key => $value ) {
					if ( false !== stristr( $key, $parent_gateway_id ) ) {
						$new_key           = str_replace( $parent_gateway_id, $this_subclass_id, $key );
						$_POST[ $new_key ] = $value;
					}
					if ( false !== stristr( $key, $this_subclass_id ) ) {
						$new_key           = str_replace( $this_subclass_id, $parent_gateway_id, $key );
						$_POST[ $new_key ] = $value;
					}
				}
			}
		}

		return parent::validate_fields();
	}
}

<?php
/**
 * A credit card gateway whose untyped `validate_fields()` returns nothing.
 *
 * @see \BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Gateway_Duplicability::is_validate_fields_compatible()
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce;

class Null_Returning_Validate_Fields_CC_Test_Gateway extends Dummy_CC_Gateway {
	/**
	 * Untyped, and returns nothing.
	 *
	 * @phpstan-ignore return.missing
	 */
	public function validate_fields() {
	}
}

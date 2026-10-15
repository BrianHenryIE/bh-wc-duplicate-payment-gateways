<?php
/**
 * A gateway whose `validate_fields()` has a parameter, which cannot be duplicated.
 *
 * @see \BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Gateway_Duplicability::is_validate_fields_compatible()
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce;

class Parameter_Validate_Fields_Test_Gateway extends Duplicable_Test_Gateway {
	public function validate_fields( bool $strict = false ) {
		return true;
	}
}

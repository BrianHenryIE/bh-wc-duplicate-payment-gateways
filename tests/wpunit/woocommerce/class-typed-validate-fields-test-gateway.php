<?php
/**
 * A duplicable gateway which declares `validate_fields(): bool`, as modern gateways (e.g. Venmo Gateway Pro) do.
 *
 * @see \BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Gateway_Duplicability::is_validate_fields_compatible()
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce;

use WC_Payment_Gateway;

class Typed_Validate_Fields_Test_Gateway extends WC_Payment_Gateway {

	public function __construct() {
		$this->id           = $this->id ?: 'typed_validate_fields_test_gateway';
		$this->method_title = 'Typed Validate Fields Test Gateway';
		$this->init_settings();
	}

	public function validate_fields(): bool {
		return true;
	}
}

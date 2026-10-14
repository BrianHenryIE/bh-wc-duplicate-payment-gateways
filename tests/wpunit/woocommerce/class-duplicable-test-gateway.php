<?php
/**
 * A gateway which can be duplicated: it only sets its id when it is not already set.
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce;

use WC_Payment_Gateway;

class Duplicable_Test_Gateway extends WC_Payment_Gateway {

	public function __construct() {
		$this->id           = $this->id ?: 'duplicable_test_gateway';
		$this->method_title = 'Duplicable Test Gateway';
		$this->init_settings();
	}
}

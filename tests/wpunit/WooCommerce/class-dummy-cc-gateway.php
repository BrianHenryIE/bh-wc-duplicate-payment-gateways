<?php
/**
 * The test to rename the $_POST fields for credit card fields needs to extend a class that extends
 * WC_Payment_Gateway_CC because WC_Payment_Gateway_CC has no id to replace.
 *
 * @used-by \BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\CC_Gateway_Parameter_Names_Trait_WPUnit_Test
 *
 * @package           brianhenryie/bh-wc-duplicate-payment-gateways
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce;

use WC_Payment_Gateway_CC;

/**
 * Just gives a WC_Payment_Gateway and id.
 */
class Dummy_CC_Gateway extends WC_Payment_Gateway_CC {
	public function __construct() {
		$this->id = 'old-gateway-id';
	}
}

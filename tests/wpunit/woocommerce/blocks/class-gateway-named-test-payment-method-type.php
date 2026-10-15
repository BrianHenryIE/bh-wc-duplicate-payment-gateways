<?php
/**
 * A gateway plugin's block checkout integration which takes its name from the gateway passed to its constructor, so
 * it can be instantiated for each duplicate.
 *
 * @see \BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Blocks\Gateway_Integration_Duplicator
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Blocks;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Duplicable_Test_Gateway;

class Gateway_Named_Test_Payment_Method_Type extends AbstractPaymentMethodType {

	public Duplicable_Test_Gateway $gateway;

	public function __construct( Duplicable_Test_Gateway $gateway ) {
		$this->gateway = $gateway;
		$this->name    = $gateway->id;
	}

	public function initialize(): void {
	}
}

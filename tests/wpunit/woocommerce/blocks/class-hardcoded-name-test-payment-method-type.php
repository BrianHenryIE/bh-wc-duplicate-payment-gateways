<?php
/**
 * A gateway plugin's block checkout integration whose name is hardcoded, so it cannot be used for a duplicate.
 *
 * @see \BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Blocks\Gateway_Integration_Duplicator
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Blocks;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Duplicable_Test_Gateway;

class Hardcoded_Name_Test_Payment_Method_Type extends AbstractPaymentMethodType {

	/**
	 * @var string
	 */
	protected $name = 'duplicable_test_gateway';

	/**
	 * @param Duplicable_Test_Gateway $gateway Ignored.
	 *
	 * @phpstan-ignore constructor.unusedParameter
	 */
	public function __construct( Duplicable_Test_Gateway $gateway ) {
	}

	public function initialize(): void {
	}
}

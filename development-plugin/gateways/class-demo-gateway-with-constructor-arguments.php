<?php
/**
 * A gateway whose constructor requires an argument (like WooCommerce Payments) so cannot be duplicated.
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\Development_Plugin\Gateways;

/**
 * Requires its id passed to the constructor.
 */
class Demo_Gateway_With_Constructor_Arguments extends Demo_Gateway {

	/**
	 * Constructor.
	 *
	 * @param string $id The gateway id.
	 */
	public function __construct( string $id ) {
		$this->id = $id;
		parent::__construct();
		$this->method_description = 'A development gateway whose constructor requires arguments, so cannot be duplicated.';
		$this->method_title       = 'Demo Gateway (constructor arguments)';
	}
}

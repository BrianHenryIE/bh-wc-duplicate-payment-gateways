<?php
/**
 * A gateway which always sets its id in the constructor, so a duplicate's id would be overwritten.
 *
 * The plugin detects this and does not offer to duplicate it.
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\Development_Plugin\Gateways;

/**
 * Overwrites `$this->id` unconditionally.
 */
class Demo_Gateway_Hardcoded_Id extends Demo_Gateway {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id = 'demo_hardcoded_id';
		parent::__construct();
		$this->method_description = 'A development gateway that sets its id in its constructor, so cannot be duplicated.';
		$this->method_title       = 'Demo Gateway (hard-coded id)';
	}
}

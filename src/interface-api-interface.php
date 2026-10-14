<?php
/**
 * The main functions for creating and deleting duplicates.
 * Which can then be retrieved through the Settings object.
 *
 * @see \BrianHenryIE\WC_Duplicate_Payment_Gateways\Settings_Interface::get_configs()
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\Admin\AJAX;

/**
 * Functions for duplicating gateways and deleting those duplicated.
 */
interface API_Interface {

	/**
	 * Duplicate a payment gateway.
	 *
	 * @used-by AJAX::add_new_duplicate_payment_gateway()
	 *
	 * @param string $gateway_id The gateway to be duplicated.
	 * @param string $duplicate_id The id to give the duplicate gateway.
	 *
	 * @return array{success: bool, message:string}
	 */
	public function add_new_duplicate_payment_gateway( string $gateway_id, string $duplicate_id ): array;

	/**
	 * Delete an earlier created duplicate gateway.
	 *
	 * @used-by AJAX::delete_duplicate_payment_gateway()
	 *
	 * @param string $duplicate_id The id of the duplicate gateway to be deleted.
	 *
	 * @return array{success: bool, message:string}
	 */
	public function delete_duplicate_payment_gateway( string $duplicate_id ): array;

	public function get_plugin_gateways( string $basename_or_slug ): array;
}

<?php
/**
 * Functions to add and delete duplicates.
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\API;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\Admin\AJAX;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Payment_Gateways;
use WC_Payment_Gateway;

/**
 * Adds and deletes from the plugin settings, deletes from WooCommerce Settings API settings for deleted duplicate gateways.
 *
 * @used-by AJAX
 */
class API implements API_Interface {

	/**
	 * Used to read and delete the settings.
	 *
	 * @var Settings_Interface
	 */
	protected Settings_Interface $settings;

	/**
	 * API constructor.
	 *
	 * @param Settings_Interface $settings The plugin's settings.
	 */
	public function __construct( Settings_Interface $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Duplicate a payment gateway.
	 * * Record the entry in this plugin's settings, which will in future be read each time WooCommerce builds its list
	 * of gateways.
	 *
	 * @see Payment_Gateways::add_gateways()
	 *
	 * NB: Does not check if the gateway id is set in the parent class constructor (which would usually make a
	 * gateway ineligible for duplication).
	 *
	 * @used-by AJAX::add_new_duplicate_payment_gateway()
	 *
	 * @param string $gateway_id The id of the gateway to duplicate.
	 * @param string $duplicate_id A unique id for the new gateway.
	 *
	 * @return array{success: bool, message:string}
	 */
	public function add_new_duplicate_payment_gateway( string $gateway_id, string $duplicate_id ): array {

		/**
		 * All payment gateways registered with WooCommerce.
		 *
		 * @var array<string, WC_Payment_Gateway> $gateways
		 */
		$gateways = \WC_Payment_Gateways::instance()->payment_gateways();

		if ( isset( $gateways[ $duplicate_id ] ) ) {

			// A gateway with this id already exists.

			$result = array(
				'success' => false,
				'message' => 'A gateway with this id already exists.',
			);

			return $result;
		}

		if ( ! isset( $gateways[ $gateway_id ] ) ) {

			$result = array(
				'success' => false,
				'message' => 'The gateway to duplicate does not exist.',
			);

			return $result;
		}

		/**
		 * TODO: Check the max number ollowed.
		 *
		 * @see Payment_Gateways::MAX_DUPLICATE_GATEWAYS
		 */

		$superclass = get_class( $gateways[ $gateway_id ] );

		$this->settings->add_new_duplicate( $duplicate_id, $superclass );

		// Right now it just dumps the duplicate at the bottom of the list.
		// Could use `woocommerce_gateway_order` to place the new gateway immediately below the existing.

		$result = array(
			'success' => true,
			'message' => "New gateway {$duplicate_id} created.",
		);

		return $result;

	}

	/**
	 * Delete a duplicate payment gateway:
	 * * delete its WooCommerce Settings API settings
	 * * delete its entry in this plugin's settings
	 *
	 * TODO: Log the settings before deleting.
	 *
	 * @param string $gateway_id The id of the duplicate gateway to delete.
	 *
	 * @see \WC_Settings_API
	 * @used-by AJAX::delete_duplicate_payment_gateway()
	 *
	 * @return array{success: bool, message:string}
	 */
	public function delete_duplicate_payment_gateway( string $gateway_id ): array {

		/**
		 * All payment gateways registered with WooCommerce.
		 *
		 * @var array<string, WC_Payment_Gateway> $gateways
		 */
		$gateways = \WC_Payment_Gateways::instance()->payment_gateways();

		if ( ! isset( $gateways[ $gateway_id ] ) ) {
			return array(
				'success' => false,
				'message' => 'No gateway found with id: ' . $gateway_id,
			);
		}

		$gateway = $gateways[ $gateway_id ];

		if ( ! ( $gateway instanceof Gateway_Copy_Interface ) || ! ( $gateway instanceof WC_Payment_Gateway ) ) {
			return array(
				'success' => false,
				'message' => 'Not a duplicate payment gateway. Cannot delete',
			);
		}

		// Delete the settings in WooCommerce.
		delete_option( $gateway->get_option_key() );

		// Delete from this plugin's settings.
		$this->settings->delete_duplicate( $gateway_id );

		$result = array(
			'success' => true,
			'message' => $gateway_id . ' deleted',
		);

		return $result;
	}

}

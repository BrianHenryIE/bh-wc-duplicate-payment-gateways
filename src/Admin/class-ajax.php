<?php

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\Admin;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\API\API_Interface;

class AJAX {

	const NONCE_ACTION = 'bh_wc_duplicate_payment_gateways_protect';

	protected API_Interface $api;

	public function __construct( API_Interface $api ) {
		$this->api = $api;
	}

	/**
	 * A JavaScript UI should prompt the user for a new gateway id.
	 *
	 * This function then needs the original gateway id and the new gateway id.
	 *
	 * From the original id, we need to find the class
	 *
	 * The return of this will be success->refresh, or fail->display message
	 *
	 * @hooked wp_ajax_add_new_duplicate_payment_gateway
	 */
	public function add_new_duplicate_payment_gateway(): void {

		if ( false === check_ajax_referer( self::NONCE_ACTION ) ) {

			$result = array(
				'success' => false,
				'message' => 'nonce failure',
			);
			wp_send_json_error( $result );
			die();
		}

		$posted = (array) wc_clean( $_POST );

		$gateway_id   = $posted['gateway_id'];
		$duplicate_id = $posted['duplicate_id'];

		$result = $this->api->add_new_duplicate_payment_gateway( $gateway_id, $duplicate_id );

		if ( true === $result['success'] ) {
			wp_send_json( $result );
		} else {
			$result = array(
				'success' => false,
				'message' => 'failed',
			);
			wp_send_json_error( $result );
		}
		die();
	}


	public function delete_duplicate_payment_gateway(): void {

		if ( false === check_ajax_referer( self::NONCE_ACTION ) ) {

			$result = array(
				'success' => false,
				'message' => 'nonce failure',
			);
			wp_send_json_error( $result );
			die();
		}

		$posted = (array) wc_clean( $_POST );

		$gateway_id = $posted['gateway_id'];

		$result = $this->api->delete_duplicate_payment_gateway( $gateway_id );

		if ( true === $result['success'] ) {
			wp_send_json( $result );
		} else {
			$result = array(
				'success' => false,
				'message' => 'failed',
			);
			wp_send_json_error( $result );
		}
		die();
	}

}

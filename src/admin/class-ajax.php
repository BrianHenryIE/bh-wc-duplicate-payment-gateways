<?php
/**
 * Handles the AJAX functions as gateways are duplicated and duplicates deleted on the WooCommerce payment gateways page.
 *
 * I.e. sanitizes the input and presents the output between AJAX and the API class.
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\Admin;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\API_Interface;

/**
 * Verify the nonce and capability, validate the input, call the API, return its result as JSON.
 */
class AJAX {

	const NONCE_ACTION = 'bh_wc_duplicate_payment_gateways_protect';

	/**
	 * Gateway ids are used in option names and HTML attributes.
	 */
	const GATEWAY_ID_PATTERN = '/^[a-z0-9_-]+$/';

	/**
	 * The plugin's main functions.
	 *
	 * @var API_Interface
	 */
	protected API_Interface $api;

	/**
	 * Constructor.
	 *
	 * @param API_Interface $api The main plugin functions.
	 */
	public function __construct( API_Interface $api ) {
		$this->api = $api;
	}

	/**
	 * Duplicate the gateway `gateway_id` with the new id `duplicate_id`.
	 *
	 * Responds `{success: true, data: {success: true, message: string}}` or `{success: false, data: {message: string}}`.
	 *
	 * @hooked wp_ajax_add_new_duplicate_payment_gateway
	 * @see \BrianHenryIE\WC_Duplicate_Payment_Gateways\BH_WC_Duplicate_Payment_Gateways::define_ajax_hooks()
	 */
	public function add_new_duplicate_payment_gateway(): void {

		$this->verify_request();

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Verified in `verify_request()`.
		$gateway_id   = isset( $_POST['gateway_id'] ) ? strtolower( sanitize_text_field( wp_unslash( $_POST['gateway_id'] ) ) ) : '';
		$duplicate_id = isset( $_POST['duplicate_id'] ) ? strtolower( sanitize_text_field( wp_unslash( $_POST['duplicate_id'] ) ) ) : '';
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		if ( 1 !== preg_match( self::GATEWAY_ID_PATTERN, $gateway_id ) || 1 !== preg_match( self::GATEWAY_ID_PATTERN, $duplicate_id ) ) {
			wp_send_json_error(
				array(
					'success' => false,
					'message' => __( 'The new id may only contain letters, numbers, underscores and hyphens.', 'bh-wc-duplicate-payment-gateways' ),
				)
			);
		}

		$this->send_result( $this->api->add_new_duplicate_payment_gateway( $gateway_id, $duplicate_id ) );
	}

	/**
	 * Delete the duplicate gateway `gateway_id`.
	 *
	 * @hooked wp_ajax_delete_duplicate_payment_gateway
	 * @see \BrianHenryIE\WC_Duplicate_Payment_Gateways\BH_WC_Duplicate_Payment_Gateways::define_ajax_hooks()
	 */
	public function delete_duplicate_payment_gateway(): void {

		$this->verify_request();

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified in `verify_request()`.
		$gateway_id = isset( $_POST['gateway_id'] ) ? sanitize_text_field( wp_unslash( $_POST['gateway_id'] ) ) : '';

		if ( empty( $gateway_id ) ) {
			wp_send_json_error(
				array(
					'success' => false,
					'message' => __( 'No gateway id.', 'bh-wc-duplicate-payment-gateways' ),
				)
			);
		}

		$this->send_result( $this->api->delete_duplicate_payment_gateway( $gateway_id ) );
	}

	/**
	 * Check the nonce and that the user can manage WooCommerce, or respond with an error (and exit).
	 */
	protected function verify_request(): void {

		if ( false === check_ajax_referer( self::NONCE_ACTION, false, false ) ) {
			wp_send_json_error(
				array(
					'success' => false,
					'message' => __( 'Nonce verification failed. Please reload the page.', 'bh-wc-duplicate-payment-gateways' ),
				),
				403
			);
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error(
				array(
					'success' => false,
					'message' => __( 'You are not allowed to manage payment gateways.', 'bh-wc-duplicate-payment-gateways' ),
				),
				403
			);
		}
	}

	/**
	 * Respond with the API's result (and exit).
	 *
	 * @param array{success: bool, message:string} $result The API result.
	 */
	protected function send_result( array $result ): void {
		if ( true === $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}
}

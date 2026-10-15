<?php
/**
 * An offline gateway with a payment field (a reference), which can be duplicated, including in the block checkout.
 *
 * Its field name includes the gateway id, so a duplicate's field does not collide with the original's when both are
 * in the shortcode checkout's form.
 *
 * @see Demo_Gateway_With_Fields_Blocks
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\Development_Plugin\Gateways;

use WC_Order;
use WC_Payment_Gateway;

/**
 * Saves the customer's reference to the order and places it on-hold.
 */
class Demo_Gateway_With_Fields extends WC_Payment_Gateway {

	/**
	 * The order meta key for the customer's reference.
	 */
	const REFERENCE_META_KEY = '_demo_reference';

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                 = $this->id ?: 'demo_gateway_fields';
		$this->method_title       = 'Demo Gateway With Fields';
		$this->method_description = 'A development gateway with a payment field, which can be duplicated.';
		$this->has_fields         = true;

		$this->init_form_fields();
		$this->init_settings();

		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'save_settings' ) );
	}

	/**
	 * Save the settings posted from the gateway's settings page.
	 *
	 * @hooked woocommerce_update_options_payment_gateways_{id}
	 * @see \WC_Settings_Payment_Gateways::save()
	 */
	public function save_settings(): void {
		$this->process_admin_options();
	}

	/**
	 * Settings fields.
	 */
	public function init_form_fields(): void {
		$this->form_fields = array(
			'enabled'     => array(
				'title'   => 'Enable/Disable',
				'type'    => 'checkbox',
				'label'   => 'Enable Demo Gateway With Fields',
				'default' => 'yes',
			),
			'title'       => array(
				'title'   => 'Title',
				'type'    => 'text',
				'default' => 'Demo Gateway With Fields',
			),
			'description' => array(
				'title'   => 'Description',
				'type'    => 'textarea',
				'default' => 'Pay with a reference.',
			),
		);
	}

	/**
	 * The POSTed field's name, unique to this gateway (instance).
	 *
	 * The block checkout sends it as payment data, which WooCommerce sets as `$_POST` for `process_payment()`.
	 *
	 * @see \Automattic\WooCommerce\StoreApi\Legacy::process_legacy_payment()
	 */
	public function get_reference_field_name(): string {
		return $this->id . '_reference';
	}

	/**
	 * The shortcode checkout's field.
	 */
	public function payment_fields(): void {
		parent::payment_fields();

		woocommerce_form_field(
			$this->get_reference_field_name(),
			array(
				'label'    => 'Reference',
				'required' => true,
			)
		);
	}

	/**
	 * Require the reference.
	 */
	public function validate_fields(): bool {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce verifies the checkout nonce.
		if ( empty( $_POST[ $this->get_reference_field_name() ] ) ) {
			wc_add_notice( 'Please enter a reference.', 'error' );
			return false;
		}
		return true;
	}

	/**
	 * Save the reference, put the order on-hold and empty the cart.
	 *
	 * @param int $order_id The order being paid.
	 * @return array{result:string, redirect:string}
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! ( $order instanceof WC_Order ) ) {
			return array(
				'result'   => 'failure',
				'redirect' => '',
			);
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce verifies the checkout nonce.
		$reference = isset( $_POST[ $this->get_reference_field_name() ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->get_reference_field_name() ] ) ) : '';
		$order->update_meta_data( self::REFERENCE_META_KEY, $reference );

		$order->update_status( 'on-hold', 'Awaiting demo payment.' );
		WC()->cart->empty_cart();

		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}
}

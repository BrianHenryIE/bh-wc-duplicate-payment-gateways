<?php
/**
 * A simple offline gateway, which can be duplicated.
 *
 * Setting `$this->id` in the constructor *only if it is not already set* is the pattern that allows a gateway to be
 * duplicated – the duplicate sets its own id before calling the parent constructor.
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\Development_Plugin\Gateways;

use WC_Order;
use WC_Payment_Gateway;

/**
 * Places the order on-hold, like the core cheque gateway.
 */
class Demo_Gateway extends WC_Payment_Gateway {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                 = $this->id ?: 'demo_gateway';
		$this->method_title       = 'Demo Gateway';
		$this->method_description = 'A development gateway that can be duplicated.';
		$this->has_fields         = false;

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
				'label'   => 'Enable Demo Gateway',
				'default' => 'yes',
			),
			'title'       => array(
				'title'   => 'Title',
				'type'    => 'text',
				'default' => 'Demo Gateway',
			),
			'description' => array(
				'title'   => 'Description',
				'type'    => 'textarea',
				'default' => 'Pay with the demo gateway.',
			),
		);
	}

	/**
	 * No fields to validate.
	 *
	 * Declares its return type, as modern gateways do (e.g. Venmo Gateway Pro), which duplicates must be compatible
	 * with.
	 *
	 * @see \BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\CC_Gateway_Parameter_Names_Trait::validate_fields()
	 */
	public function validate_fields(): bool {
		return true;
	}

	/**
	 * Put the order on-hold and empty the cart.
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

		$order->update_status( 'on-hold', 'Awaiting demo payment.' );
		WC()->cart->empty_cart();

		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}
}

<?php
/**
 * Add a column to WooCommerce / Settings / Payments list of gateways, to allow duplicating, and deleting duplicates.
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\API\Gateway_Copy_Interface;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\API_Interface;
use WC_Payment_Gateway;

/**
 * Register the column; print the column.
 *
 * @see WC_Settings_Payment_Gateways::payment_gateways_setting()
 */
class Payment_Gateways_List_UI {

	/**
	 * The plugin's main functions.
	 *
	 * @var API_Interface
	 */
	protected API_Interface $api;

	/**
	 * Used to check is a gateway safe to duplicate before printing the duplicate button.
	 *
	 * @var Gateway_Duplicability
	 */
	protected Gateway_Duplicability $gateway_duplicability;

	/**
	 * Constructor.
	 *
	 * @param API_Interface         $api The main plugin functions.
	 * @param Gateway_Duplicability $gateway_duplicability Checks can a gateway be duplicated.
	 */
	public function __construct( API_Interface $api, Gateway_Duplicability $gateway_duplicability ) {
		$this->api                   = $api;
		$this->gateway_duplicability = $gateway_duplicability;
	}

	/**
	 * Add a 'duplicate' column after the 'sort' column.
	 *
	 * @hooked woocommerce_payment_gateways_setting_columns
	 *
	 * @param array<string, string> $columns Column id: column title.
	 * @return array<string, string>
	 */
	public function add_column( array $columns ): array {

		$new_columns = array();

		foreach ( $columns as $id => $title ) {
			$new_columns[ $id ] = $title;
			if ( 'action' === $id ) {
				$new_columns['duplicate'] = '';
			}
		}

		return $new_columns;
	}

	/**
	 * If the gateway is a duplicate, add a delete button.
	 * if it is an original, print a duplicate button.
	 *
	 * @hooked woocommerce_payment_gateways_setting_column_duplicate
	 *
	 * @param WC_Payment_Gateway $gateway The gateway object for the current row.
	 */
	public function print_column_for_duplicate_ui( WC_Payment_Gateway $gateway ): void {

		// TODO: Should all shop admins be allowed do this?
		// Add a "duplicate-gateways" role and if empty, allow shop managers, if not empty, restrict.

		$key   = 'duplicate';
		$width = '2%';

		echo '<td class="' . esc_attr( $key ) . '" width="' . esc_attr( $width ) . '">';

		$method_title = wp_kses( $gateway->get_method_title(), array() );

		if ( $gateway instanceof Gateway_Copy_Interface ) {
			// Print a delete button.
			/* translators: %s: The payment gateway ID. */
			echo '<span class="duplicate-payment-gateway duplicate-delete dashicons dashicons-trash" data-gateway_id="' . esc_attr( $gateway->id ) . '" title="' . esc_attr( sprintf( __( 'Delete the duplicate "%s" payment method.', 'bh-wc-duplicate-payment-gateways' ), $method_title ) ) . '"></span>';
		} elseif ( $this->is_safe_to_duplicate( $gateway ) ) {
			// Print an add button.
			/* translators: %s: The payment gateway ID. */
			echo '<span class="duplicate-payment-gateway duplicate-add dashicons dashicons-admin-page" data-gateway_id="' . esc_attr( $gateway->id ) . '" title="' . esc_attr( sprintf( __( 'Duplicate the "%s" payment method.', 'bh-wc-duplicate-payment-gateways' ), $method_title ) ) . '"></span>';
		}

		echo '</td>';
	}

	/**
	 * Check can the gateway be duplicated.
	 *
	 * @param WC_Payment_Gateway $gateway_to_copy A gateway we might want to duplicate.
	 */
	protected function is_safe_to_duplicate( WC_Payment_Gateway $gateway_to_copy ): bool {
		return $this->gateway_duplicability->is_safe_to_duplicate( $gateway_to_copy );
	}
}

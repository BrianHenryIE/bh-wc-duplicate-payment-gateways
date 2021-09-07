<?php
/**
 * When a payment gateway is a duplicate of another, fields for the display names and a delete button should be shown.
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\API\Gateway_Copy_Interface;

/**
 * Filters settings of other gateways.
 */
class Payment_Gateway_Settings {

	const ADMIN_TITLE       = 'bh_wc_duplicate_payment_gateways_admin_title';
	const ADMIN_DESCRIPTION = 'bh_wc_duplicate_payment_gateways_admin_description';

	/**
	 * Filters each payment gateway's settings output to print out the gateway id on the WC Settings API page.
	 *
	 * @see WC_Settings_API::get_form_fields()
	 *
	 * @hooked woocommerce_after_register_post_type
	 */
	public function register_filter_on_each_gateway(): void {

		$gateways = \WC_Payment_Gateways::instance()->payment_gateways();

		foreach ( $gateways as $gateway_id => $gateway ) {

			if ( ! is_a( $gateway, Gateway_Copy_Interface::class, true ) ) {
				continue;
			}

			add_filter( "woocommerce_settings_api_form_fields_{$gateway_id}", array( $this, 'add_extra_gateway_settings_page' ), 20, 1 );
			add_action( "woocommerce_update_options_payment_gateways_{$gateway_id}", array( $this, 'save_settings' ) );

		}

	}

	/**
	 * The form field to add to each settings page.
	 *
	 * @param array<string|int, mixed> $form_fields The gateway's existing settings.
	 * @return array<string|int, mixed>
	 */
	public function add_extra_gateway_settings_page( array $form_fields ): array {

		$form_fields[ self::ADMIN_TITLE ] = array(
			'title'       => __( 'Admin title', 'bh-wc-duplicate-payment-gateways' ),
			'type'        => 'text',
			'description' => __( '[Duplicate gateway] Change the title displayed to shop managers in the WooCommerce admin area.', 'bh-wc-duplicate-payment-gateways' ),
			'default'     => '',
			'id'          => self::ADMIN_TITLE,
		);

		$form_fields[ self::ADMIN_DESCRIPTION ] = array(
			'title'       => __( 'Admin description', 'bh-wc-duplicate-payment-gateways' ),
			'type'        => 'text',
			'description' => __( '[Duplicate gateway] Change the description displayed to shop managers in the WooCommerce admin area.', 'bh-wc-duplicate-payment-gateways' ),
			'default'     => '',
			'id'          => self::ADMIN_DESCRIPTION,
		);

		return $form_fields;
	}


	public function save_settings(): void {

		$filter_name = current_filter();
		$gateway_id  = substr( $filter_name, strlen( 'woocommerce_update_options_payment_gateways_' ) );

		$gateways = \WC_Payment_Gateways::instance()->payment_gateways();

		/** @var \WC_Payment_Gateway $gateway */
		$gateway = $gateways[ $gateway_id ];

		$posted = (array) wc_clean( $_POST );

		$option_names = array(
			self::ADMIN_TITLE,
			self::ADMIN_DESCRIPTION,
		);

		foreach ( $option_names as $option_name ) {

			$posted_option_name = "woocommerce_{$gateway_id}_{$option_name}";

			if ( isset( $posted[ $posted_option_name ] ) ) {
				$gateway->update_option( self::ADMIN_DESCRIPTION, $posted[ $posted_option_name ] );
			}
		}

	}

}

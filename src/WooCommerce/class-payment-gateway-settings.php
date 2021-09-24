<?php
/**
 * When a payment gateway is a duplicate of another, fields for the display names and a delete button should be shown.
 *
 * @package     brianhenryie/bh-wc-duplicate-payment-gateways
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\API\Gateway_Copy_Interface;
use WC_Payment_Gateway;

/**
 * Filters settings of other gateways.
 */
class Payment_Gateway_Settings {

	const ADMIN_METHOD_TITLE = 'bh_wc_duplicate_payment_gateways_admin_title';
	const ADMIN_DESCRIPTION  = 'bh_wc_duplicate_payment_gateways_admin_description';

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

			if ( ! is_a( $gateway, Gateway_Copy_Interface::class, true ) || ! ( $gateway instanceof WC_Payment_Gateway ) ) {
				continue;
			}

			add_filter(
				"woocommerce_settings_api_form_fields_{$gateway_id}",
				function( $form_fields ) use ( $gateway ) {
					return $this->add_extra_gateway_settings_page( $form_fields, $gateway );
				},
				20,
				1
			);

			add_action( "woocommerce_update_options_payment_gateways_{$gateway_id}", array( $this, 'save_settings' ) );

		}

	}

	/**
	 * The form field to add to each settings page.
	 *
	 * TODO: Add the existing/default text as placeholder.
	 *
	 * @param array<string|int, mixed> $form_fields The gateway's existing settings.
	 * @param WC_Payment_Gateway       $gateway The gateway instance.
	 * @return array<string|int, mixed>
	 */
	protected function add_extra_gateway_settings_page( array $form_fields, WC_Payment_Gateway $gateway ): array {

		$parent_classes = class_parents( $gateway );
		// @phpstan-ignore-next-line WC_Payment_Gateway itself is a subclass, so the next line will never return false.
		$first_parent_class = array_key_first( $parent_classes );
		/**
		 * Instance of the gateway that has been duplicated.
		 *
		 * @var WC_Payment_Gateway $parent_instance
		 */
		$parent_instance = new $first_parent_class();

		$form_fields[ self::ADMIN_METHOD_TITLE ] = array(
			'title'       => __( 'Admin title', 'bh-wc-duplicate-payment-gateways' ),
			'type'        => 'text',
			'description' => __( '[Duplicate gateway] Change the title displayed to shop managers in the WooCommerce admin area.', 'bh-wc-duplicate-payment-gateways' ),
			'default'     => '',
			'id'          => self::ADMIN_METHOD_TITLE,
			'placeholder' => $parent_instance->get_method_title(),
		);

		$form_fields[ self::ADMIN_DESCRIPTION ] = array(
			'title'       => __( 'Admin description', 'bh-wc-duplicate-payment-gateways' ),
			'type'        => 'text',
			'description' => __( '[Duplicate gateway] Change the description displayed to shop managers in the WooCommerce admin area.', 'bh-wc-duplicate-payment-gateways' ),
			'default'     => '',
			'id'          => self::ADMIN_DESCRIPTION,
			'placeholder' => $parent_instance->get_method_description(),
		);

		return $form_fields;
	}

	/**
	 * Save the additional payment gateway settings.
	 *
	 * Find the gateway id from the current filter name, get the gateway instance and save the settings.
	 * TODO: Is there a better way than this?!
	 */
	public function save_settings(): void {

		$filter_name = current_filter();
		$gateway_id  = substr( $filter_name, strlen( 'woocommerce_update_options_payment_gateways_' ) );

		$gateways = \WC_Payment_Gateways::instance()->payment_gateways();

		/**
		 * The gateway instance to update.
		 *
		 * @var WC_Payment_Gateway $gateway
		 */
		$gateway = $gateways[ $gateway_id ];

		$posted = (array) wc_clean( $_POST );

		$option_names = array(
			self::ADMIN_METHOD_TITLE,
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

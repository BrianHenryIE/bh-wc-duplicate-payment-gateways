<?php
/**
 * When a payment gateway is a duplicate of another, fields for the display names and a delete button should be shown.
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\API\Gateway_Copy_Interface;
use WC_Payment_Gateway;
use WC_Payment_Gateways;

/**
 * Filters settings of other gateways.
 */
class Payment_Gateway_Settings {

	const ADMIN_METHOD_TITLE = 'bh_wc_duplicate_payment_gateways_admin_title';
	const ADMIN_DESCRIPTION  = 'bh_wc_duplicate_payment_gateways_admin_description';

	/**
	 * Gateway ids whose filters have already been added, since WooCommerce may initialize the gateways more than once.
	 *
	 * @var array<string, true>
	 */
	protected array $registered_gateway_ids = array();

	/**
	 * Filter each duplicate gateway's settings fields to add the admin title and admin description fields.
	 *
	 * Uses the `WC_Payment_Gateways` instance passed to the action, rather than `WC_Payment_Gateways::instance()`,
	 * since the action runs inside `WC_Payment_Gateways::__construct()`, before the singleton is assigned.
	 *
	 * @hooked wc_payment_gateways_initialized
	 * @see WC_Payment_Gateways::init()
	 * @see WC_Settings_API::get_form_fields()
	 *
	 * @param WC_Payment_Gateways $wc_payment_gateways The gateways that have just been loaded.
	 */
	public function register_filter_on_each_gateway( WC_Payment_Gateways $wc_payment_gateways ): void {

		foreach ( $wc_payment_gateways->payment_gateways as $gateway ) {

			if ( ! ( $gateway instanceof Gateway_Copy_Interface ) ) {
				continue;
			}

			$gateway_id = $gateway->id;

			if ( isset( $this->registered_gateway_ids[ $gateway_id ] ) ) {
				continue;
			}
			$this->registered_gateway_ids[ $gateway_id ] = true;

			add_filter(
				"woocommerce_settings_api_form_fields_{$gateway_id}",
				function ( $form_fields ) use ( $gateway ) {
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
	 * The placeholders use the `method_title` and `method_description` properties, since `get_method_title()` is
	 * filtered by `Payment_Gateway::replace_title()`, which reads the settings, which calls this: infinite recursion.
	 *
	 * @param array<string|int, mixed> $form_fields The gateway's existing settings.
	 * @param WC_Payment_Gateway       $gateway The gateway instance.
	 * @return array<string|int, mixed>
	 */
	protected function add_extra_gateway_settings_page( array $form_fields, WC_Payment_Gateway $gateway ): array {

		$form_fields[ self::ADMIN_METHOD_TITLE ] = array(
			'title'       => __( 'Admin title', 'bh-wc-duplicate-payment-gateways' ),
			'type'        => 'text',
			'description' => __( '[Duplicate gateway] Change the title displayed to shop managers in the WooCommerce admin area.', 'bh-wc-duplicate-payment-gateways' ),
			'default'     => '',
			'id'          => self::ADMIN_METHOD_TITLE,
			'placeholder' => wp_strip_all_tags( $gateway->method_title ),
		);

		$form_fields[ self::ADMIN_DESCRIPTION ] = array(
			'title'       => __( 'Admin description', 'bh-wc-duplicate-payment-gateways' ),
			'type'        => 'text',
			'description' => __( '[Duplicate gateway] Change the description displayed to shop managers in the WooCommerce admin area.', 'bh-wc-duplicate-payment-gateways' ),
			'default'     => '',
			'id'          => self::ADMIN_DESCRIPTION,
			'placeholder' => wp_strip_all_tags( $gateway->method_description ),
		);

		return $form_fields;
	}

	/**
	 * Save the additional payment gateway settings.
	 *
	 * Find the gateway id from the current filter name, get the gateway instance and save the settings.
	 * TODO: Is there a better way than this?!
	 *
	 * @see WC_Settings_Payment_Gateways::save()
	 */
	public function save_settings(): void {

		$filter_name = (string) current_filter();
		$gateway_id  = substr( $filter_name, strlen( 'woocommerce_update_options_payment_gateways_' ) );

		$gateways = \WC_Payment_Gateways::instance()->payment_gateways();

		/**
		 * The gateway instance to update.
		 *
		 * @var WC_Payment_Gateway $gateway
		 */
		$gateway = $gateways[ $gateway_id ];

		if ( ! isset( $_POST['_wpnonce'] ) || false === wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'woocommerce-settings' ) ) {
			// TODO: Log.
			return;
		}

		$posted = (array) wc_clean( $_POST );

		$option_names = array(
			self::ADMIN_METHOD_TITLE,
			self::ADMIN_DESCRIPTION,
		);

		foreach ( $option_names as $option_name ) {

			$posted_option_name = "woocommerce_{$gateway_id}_{$option_name}";

			if ( isset( $posted[ $posted_option_name ] ) ) {
				$gateway->update_option( $option_name, $posted[ $posted_option_name ] );
			}
		}
	}
}

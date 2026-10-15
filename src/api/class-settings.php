<?php
/**
 * Nice typed object over saved wp_options.
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\API;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\Settings_Interface;

/**
 * CRUD for the saved duplicates' settings.
 */
class Settings implements Settings_Interface {

	const OPTION_NAME = 'bh_wc_duplicate_payment_gateways';

	/**
	 * Get the gateways to duplicate.
	 *
	 * @return array<string, array{class_name:string, new_id:string}> Keyed by duplicate gateway id.
	 */
	public function get_configs(): array {

		return get_option( self::OPTION_NAME, array() );
	}

	/**
	 * Save the newly created duplicate to wp_options.
	 *
	 * @param string $id The id of the duplicate gateway.
	 * @param string $super_class The classFQN to duplicate.
	 */
	public function add_new_duplicate( string $id, string $super_class ): void {

		$config = $this->get_configs();

		$config[ $id ] = array(
			'class_name' => $super_class,
			'new_id'     => $id,
		);

		update_option( self::OPTION_NAME, $config );
	}

	/**
	 * Remove the config for the specified duplicate id and save. Delete the option if there are no duplicates remaining.
	 *
	 * @param string $duplicate_gateway_id The id of the duplicate gateway to remove.
	 */
	public function delete_duplicate( string $duplicate_gateway_id ): void {
		$config = $this->get_configs();

		if ( empty( $config ) ) {
			return;
		}

		if ( isset( $config[ $duplicate_gateway_id ] ) ) {

			// TODO: It would be good to log the data before deleting it.

			unset( $config[ $duplicate_gateway_id ] );
			if ( ! empty( $config ) ) {
				update_option( self::OPTION_NAME, $config );
			} else {
				delete_option( self::OPTION_NAME );
			}
		}
	}

	/**
	 * The current plugin version.
	 *
	 * @used-by Admin_Assets::enqueue_scripts()
	 * @used-by Admin_Assets::enqueue_styles()
	 *
	 * @return string
	 */
	public function get_plugin_version(): string {
		return defined( 'BH_WC_DUPLICATE_PAYMENT_GATEWAYS_VERSION' )
			? BH_WC_DUPLICATE_PAYMENT_GATEWAYS_VERSION
			: '1.5.4';
	}

	/**
	 * The plugin's directory and main file, as defined in the main plugin file.
	 *
	 * @used-by Admin_Assets::enqueue_scripts()
	 * @used-by BH_WC_Duplicate_Payment_Gateways::define_plugins_page_hooks()
	 */
	public function get_plugin_basename(): string {
		return defined( 'BH_WC_DUPLICATE_PAYMENT_GATEWAYS_BASENAME' )
			? BH_WC_DUPLICATE_PAYMENT_GATEWAYS_BASENAME
			: 'bh-wc-duplicate-payment-gateways/bh-wc-duplicate-payment-gateways.php';
	}
}

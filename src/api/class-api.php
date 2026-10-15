<?php
/**
 * Functions to add and delete duplicates.
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\API;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\Admin\AJAX;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\API_Interface;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\Settings_Interface;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Gateway_Duplicability;
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
	 * Used to refuse to duplicate gateways which would overwrite the duplicate's id.
	 */
	protected Gateway_Duplicability $gateway_duplicability;

	/**
	 * Cache for `get_plugin_gateways()`, keyed by a hash of the duplicates' configs.
	 *
	 * @var array<string, array<string, array<string, string[]>>>
	 */
	protected array $plugin_gateways_cache = array();

	/**
	 * API constructor.
	 *
	 * @param Settings_Interface    $settings The plugin's settings.
	 * @param Gateway_Duplicability $gateway_duplicability Checks can a gateway be duplicated.
	 */
	public function __construct( Settings_Interface $settings, Gateway_Duplicability $gateway_duplicability ) {
		$this->settings              = $settings;
		$this->gateway_duplicability = $gateway_duplicability;
	}

	/**
	 * Duplicate a payment gateway.
	 * * Record the entry in this plugin's settings, which will in future be read each time WooCommerce builds its list
	 * of gateways.
	 *
	 * @see Payment_Gateways::add_gateways()
	 *
	 * Refuses gateways whose constructors require arguments or overwrite the id, since the duplicate would
	 * replace the original.
	 *
	 * @see Gateway_Duplicability::is_safe_to_duplicate()
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

		if ( ! $this->gateway_duplicability->is_safe_to_duplicate( $gateways[ $gateway_id ] ) ) {
			return array(
				'success' => false,
				'message' => 'The gateway cannot be duplicated: its constructor requires arguments or sets its id.',
			);
		}

		/**
		 * TODO: Check the max number allowed.
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

			// The duplicate may not be loaded because the gateway it copies is no longer active.
			if ( isset( $this->settings->get_configs()[ $gateway_id ] ) ) {
				delete_option( "woocommerce_{$gateway_id}_settings" );
				$this->settings->delete_duplicate( $gateway_id );

				return array(
					'success' => true,
					'message' => $gateway_id . ' deleted',
				);
			}

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


	/**
	 * Given a plugin slug or basename, get the list of related extended gateways.
	 *
	 * Used on plugins.php to maybe edit the action links (to link to the list of gateways).
	 *
	 * @param string $basename_or_slug E.g. `woocommerce/woocommerce.php` or `woocommerce`.
	 *
	 * @return array<string,string[]> List of the plugin's gateway ids and each one's children's ids.
	 */
	public function get_plugin_gateways( string $basename_or_slug ): array {

		$query_plugin_slug = $this->get_slug( $basename_or_slug );

		if ( empty( $query_plugin_slug ) ) {
			return array();
		}

		$configs = $this->settings->get_configs();

		if ( empty( $configs ) ) {
			return array();
		}

		// Only recalculate when the duplicates change.
		$cache_key = md5( (string) wp_json_encode( $configs ) );
		if ( ! isset( $this->plugin_gateways_cache[ $cache_key ] ) ) {
			$this->plugin_gateways_cache = array(
				$cache_key => $this->calculate_plugin_gateways( $configs ),
			);
		}

		return $this->plugin_gateways_cache[ $cache_key ][ $query_plugin_slug ] ?? array();
	}

	/**
	 * Find each gateway's plugin, and each gateway's duplicates.
	 *
	 * @param array<string, array{class_name:string,new_id:string}> $configs The duplicates, keyed by duplicate id.
	 *
	 * @return array<string, array<string, string[]>> Plugin slug: gateway id: duplicate ids.
	 */
	protected function calculate_plugin_gateways( array $configs ): array {

		/**
		 * All payment gateways registered with WooCommerce.
		 *
		 * @var array<string, WC_Payment_Gateway> $gateway_ids_to_instances
		 */
		$gateway_ids_to_instances = WC()->payment_gateways()->payment_gateways();

		$gateway_classnames_to_ids   = array();
		$plugin_slugs_to_gateway_ids = array();

		foreach ( $gateway_ids_to_instances as $id => $gateway ) {

			$gateway_classnames_to_ids[ strtolower( get_class( $gateway ) ) ] = $id;

			$file = ( new \ReflectionObject( $gateway ) )->getFileName();
			if ( false === $file ) {
				continue;
			}

			// `plugin_basename()` resolves symlinked plugins.
			$plugin_slug = $this->get_slug( plugin_basename( $file ) );
			if ( empty( $plugin_slug ) ) {
				continue;
			}

			$plugin_slugs_to_gateway_ids[ $plugin_slug ][] = $id;
		}

		$parent_gateway_ids_to_child_ids = array();

		foreach ( $configs as $child_id => $config ) {

			$parent_class_name = strtolower( ltrim( $config['class_name'], '\\' ) );

			// The gateway being duplicated is not loaded, e.g. its plugin is inactive.
			if ( ! isset( $gateway_classnames_to_ids[ $parent_class_name ] ) ) {
				continue;
			}

			$parent_gateway_ids_to_child_ids[ $gateway_classnames_to_ids[ $parent_class_name ] ][] = $child_id;
		}

		$plugin_slugs_to_gateway_ids_with_child_ids = array();

		foreach ( $plugin_slugs_to_gateway_ids as $plugin_slug => $gateway_ids ) {
			foreach ( $gateway_ids as $gateway_id ) {
				$plugin_slugs_to_gateway_ids_with_child_ids[ $plugin_slug ][ $gateway_id ] = $parent_gateway_ids_to_child_ids[ $gateway_id ] ?? array();
			}
		}

		return $plugin_slugs_to_gateway_ids_with_child_ids;
	}

	/**
	 * Get the plugin directory name from a plugin basename.
	 *
	 * @param string $basename_or_slug E.g. `woocommerce/woocommerce.php`.
	 *
	 * @return string E.g. `woocommerce`, or empty string when no match.
	 */
	protected function get_slug( string $basename_or_slug ): string {
		return 1 === preg_match( '/^[\w-]+/', $basename_or_slug, $output_array ) ? $output_array[0] : '';
	}
}

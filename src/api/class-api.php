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
	 * API constructor.
	 *
	 * @param Settings_Interface $settings The plugin's settings.
	 */
	public function __construct( Settings_Interface $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Duplicate a payment gateway.
	 * * Record the entry in this plugin's settings, which will in future be read each time WooCommerce builds its list
	 * of gateways.
	 *
	 * @see Payment_Gateways::add_gateways()
	 *
	 * NB: Does not check if the gateway id is set in the parent class constructor (which would usually make a
	 * gateway ineligible for duplication). This step is done in the UI, so useful but not foolproof.
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
	 * @param string $basename_or_slug
	 *
	 * @return array<string,string[]> List of the plugin's gateway ids and each one's children's ids.
	 */
	public function get_plugin_gateways( string $basename_or_slug ): array {

		if ( 1 !== preg_match( '/^[\w_-]*/', $basename_or_slug, $slug_output_array ) || empty( $slug_output_array[0] ) ) {
			// Something went wrong.
			return array();
		}

		$query_plugin_slug = $slug_output_array[0];

		// The duplicates.
		/** @var array<string, array{class_name:string,new_id:string}> $config */
		$extended_payment_gateways = $this->settings->get_configs();

		if ( empty( $extended_payment_gateways ) ) {
			return array();
		}

		/**
		 * Use a static property to avoid recalculating the rest.
		 *
		 * @var array<string, array<string, string[]>>  $plugin_slugs_to_gateway_ids_with_child_ids
		 */
		static $plugin_slugs_to_gateway_ids_with_child_ids = null;
		if ( is_array( $plugin_slugs_to_gateway_ids_with_child_ids ) ) {
			$queried_plugin_gateway_ids_with_child_ids = isset( $plugin_slugs_to_gateway_ids_with_child_ids[ $query_plugin_slug ] )
			? $plugin_slugs_to_gateway_ids_with_child_ids[ $query_plugin_slug ] : array();
			return $queried_plugin_gateway_ids_with_child_ids;
		}

		$plugin_slugs_to_gateway_ids_with_child_ids = array();

		$plugin_slugs_to_gateways    = array();
		$gateway_ids_to_plugin_slugs = array();
		$plugin_slugs_to_gateway_ids = array();

		// ** @var array<string, \WC_Payment_Gateway> $wc_payment_gateways */
		$gateway_ids_to_classes = WC()->payment_gateways()->payment_gateways();

		$gateway_classnames_to_ids = array();

		foreach ( $gateway_ids_to_classes as $id => $gateway_class ) {

			$gateway_classnames_to_ids[ get_class( $gateway_class ) ] = $id;

			$reflected_gateway = new \ReflectionObject( $gateway_class );
			$file              = $reflected_gateway->getFileName();

			// This deals with symlinks.
			$plugin_subdir_file = plugin_basename( $file );

			if ( 1 !== preg_match( '/^[\w_-]*/', $plugin_subdir_file, $output_array ) || empty( $output_array[0] ) ) {
				// Something went wrong.
				continue;
			}
			$plugin_slug = $output_array[0];

			$gateway_ids_to_plugin_slugs[ $id ] = $plugin_slug;

			if ( ! isset( $plugin_slugs_to_gateway_ids[ $plugin_slug ] ) ) {
				$plugin_slugs_to_gateway_ids[ $plugin_slug ] = array();
			}
			$plugin_slugs_to_gateway_ids[ $plugin_slug ][] = $id;

			if ( ! isset( $plugin_slugs_to_gateways[ $plugin_slug ] ) ) {
				$plugin_slugs_to_gateways[ $plugin_slug ] = array();
			}
			$plugin_slugs_to_gateways[ $plugin_slug ][] = get_class( $gateway_class );

		}

		$gateway_classnames_to_child_ids = array();

		foreach ( $extended_payment_gateways as $child_id => $config ) {

			$parent_gateway_classname = $config['class_name'];

			if ( ! isset( $gateway_classnames_to_child_ids[ $parent_gateway_classname ] ) ) {
				$gateway_classnames_to_child_ids[ $parent_gateway_classname ] = array();
			}

			$gateway_classnames_to_child_ids[ $parent_gateway_classname ][] = $child_id;
		}

		// Next figure the id of the parent gateway.

		$parent_gateway_ids_to_child_ids = array();

		foreach ( $gateway_classnames_to_child_ids as $gateway_classname => $child_ids ) {
			$parent_gateway_id = $gateway_classnames_to_ids[ $gateway_classname ];

			$parent_gateway_ids_to_child_ids[ $parent_gateway_id ] = $child_ids;
		}

		foreach ( $plugin_slugs_to_gateway_ids as $plugin_slug => $gateway_ids ) {

			if ( ! isset( $plugin_slugs_to_gateway_ids_with_child_ids[ $plugin_slug ] ) ) {
				$plugin_slugs_to_gateway_ids_with_child_ids[ $plugin_slug ] = array();
			}

			foreach ( $gateway_ids as $gateway_id ) {

				$child_ids = isset( $parent_gateway_ids_to_child_ids[ $gateway_id ] ) ? $parent_gateway_ids_to_child_ids[ $gateway_id ] : array();
				$plugin_slugs_to_gateway_ids_with_child_ids[ $plugin_slug ][ $gateway_id ] = $child_ids;
			}
		}

		$queried_plugin_gateway_ids_with_child_ids = isset( $plugin_slugs_to_gateway_ids_with_child_ids[ $query_plugin_slug ] )
			? $plugin_slugs_to_gateway_ids_with_child_ids[ $query_plugin_slug ] : array();
		return $queried_plugin_gateway_ids_with_child_ids;
	}

}

<?php
/**
 * The saved duplicates, and plugin metadata.
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways;

/**
 * Typed access to the plugin's settings.
 */
interface Settings_Interface {

	/**
	 * Array key is the new gateway id.
	 *
	 * @return array<string, array{class_name:string,new_id:string}>
	 */
	public function get_configs(): array;

	/**
	 * Save a new duplicate.
	 *
	 * @param string $id The id of the duplicate gateway.
	 * @param string $super_class The class name of the gateway being duplicated.
	 */
	public function add_new_duplicate( string $id, string $super_class ): void;

	/**
	 * Remove a duplicate from the saved duplicates.
	 *
	 * @param string $duplicate_gateway_id The id of the duplicate gateway.
	 */
	public function delete_duplicate( string $duplicate_gateway_id ): void;

	/**
	 * The plugin version, for cache-busting scripts and styles.
	 *
	 * @used-by Admin_Assets::enqueue_scripts()
	 * @used-by Admin_Assets::enqueue_styles()
	 *
	 * @return string Semver
	 */
	public function get_plugin_version(): string;

	/**
	 * The plugin's directory and main file, e.g. `bh-wc-duplicate-payment-gateways/bh-wc-duplicate-payment-gateways.php`.
	 */
	public function get_plugin_basename(): string;
}

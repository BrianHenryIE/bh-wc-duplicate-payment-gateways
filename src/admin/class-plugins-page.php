<?php
/**
 * Add a link to WooCommerce / Settings / Payments under the plugin name on plugins.php.
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 */

declare(strict_types=1);

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\Admin;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\API_Interface;

/**
 * Add to action links array.
 */
class Plugins_Page {

	/**
	 * Used to find which plugins' gateways have been duplicated.
	 *
	 * @var API_Interface
	 */
	protected API_Interface $api;

	/**
	 * Constructor.
	 *
	 * @param API_Interface $api The plugin's main functions.
	 */
	public function __construct( API_Interface $api ) {
		$this->api = $api;
	}

	/**
	 * Add link to the Duplicate Payment Gateways page in plugins.php list.
	 *
	 * @hooked plugin_action_links_{basename}
	 *
	 * @param array<int|string, string> $links_array The existing plugin links (usually "Deactivate"). May or may not be indexed with a string.
	 *
	 * @return array<int|string, string> The links to display below the plugin name on plugins.php.
	 */
	public function action_links( array $links_array ): array {

		array_unshift(
			$links_array,
			'<a href="' . esc_url( Duplicate_Gateways_Page::get_url() ) . '">' . esc_html__( 'Duplicate Gateways', 'bh-wc-duplicate-payment-gateways' ) . '</a>'
		);

		return $links_array;
	}

	/**
	 * Before each `plugin_action_links_{basename}` hook is a more generic action, where we will check is the plugin
	 * a gateway plugin relevant to us, and if so add a filter to _after_ its probable `plugin_action_links_{basename}` hook.
	 *
	 * @hooked plugin_action_links
	 * @see \WP_Plugins_List_Table::single_row()
	 * @see wp-admin/includes/class-wp-plugins-list-table.php:952 `$actions = apply_filters( 'plugin_action_links', $actions, $plugin_file, $plugin_data, $context );`.
	 *
	 * @param array<int|string, string>  $actions The existing plugin links (usually "Deactivate").
	 * @param string                     $plugin_basename The plugin's directory/filename.php.
	 * @param array<string, string|bool> $_plugin_data Associative array including PluginURI, slug, Author, Version. See `get_plugin_data()`.
	 * @param string                     $_context     The plugin context. By default this can include 'all', 'active', 'inactive',
	 *                                                'recently_activated', 'upgrade', 'mustuse', 'dropins', and 'search'.
	 *
	 * @return array<int|string, string> The links to display below the plugin name on plugins.php.
	 */
	public function add_gateways_plugins_hooks( array $actions, string $plugin_basename, $_plugin_data, $_context ): array {
		// While this is a filter, we're using is basically as an action, to add actions.

		if ( 'woocommerce/woocommerce.php' === $plugin_basename ) {
			return $actions;
		}

		$plugins_gateways = $this->api->get_plugin_gateways( $plugin_basename );

		$all_children = array();
		foreach ( $plugins_gateways as $gateway_id => $child_ids ) {
			$all_children = array_merge( $all_children, $child_ids );
		}

		if ( ! empty( $all_children ) ) {
			add_filter( "plugin_action_links_{$plugin_basename}", array( $this, 'modify_client_gateways_action_hooks' ), 1000, 2 );
		}

		return $actions;
	}


	/**
	 * If another plugin is the plugin for a gateway that we are duplicating, add a "Gateways" link to list all the duplicates.
	 *
	 * @hooked plugin_action_links_{basename}
	 * @see self::add_gateways_plugins_hooks()
	 *
	 * @param array<int|string, string> $links_array The plugin's action links.
	 * @param string                    $plugin_file The plugin's basename.
	 *
	 * @return array<int|string, string>
	 */
	public function modify_client_gateways_action_hooks( array $links_array, string $plugin_file ): array {

		// Add a Gateways link to the list of gateways.
		// Remove any individual gateway link
		// PS/TODO: do similarly with the filter-orders-by-gateway-plugin.

		// <a href="http://localhost:8080/bh-wc-duplicate-payment-gateways/wp-admin/admin.php?page=wc-settings&tab=checkout&section=gateway-id">Settings</a>
		$plugins_gateways     = $this->api->get_plugin_gateways( $plugin_file );
		$plugins_gateways_ids = array_keys( $plugins_gateways );

		foreach ( $links_array as $key => $link ) {
			if ( 1 === preg_match( '/.*href="(?<url>.*?)".*?>(?<title>.*?)</', $link, $output_array ) ) {

				$querystring = (string) wp_parse_url( html_entity_decode( $output_array['url'] ), PHP_URL_QUERY );
				$query_parts = array();
				wp_parse_str( $querystring, $query_parts );

				if ( isset( $query_parts['section'] ) && in_array( $query_parts['section'], $plugins_gateways_ids, true ) ) {
					unset( $links_array[ $key ] );
				}
			}
		}

		if ( 1 !== preg_match( '/^[\w_-]*/', $plugin_file, $slug_output_array ) || empty( $slug_output_array[0] ) ) {
			// Something went wrong.
			return $links_array;
		}

		$plugin_slug = $slug_output_array[0];

		$setting_link = Duplicate_Gateways_Page::get_url( $plugin_slug );

		$plugin_links   = array();
		$plugin_links[] = '<a href="' . esc_url( $setting_link ) . '">' . __( 'Gateways', 'bh-wc-duplicate-payment-gateways' ) . '</a>';

		return array_merge( $plugin_links, $links_array );
	}
}

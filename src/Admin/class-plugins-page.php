<?php
/**
 * Add a link to WooCommerce / Settings / Payments under the plugin name on plugins.php.
 *
 * @package     brianhenryie/bh-wc-duplicate-payment-gateways
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\Admin;

/**
 * Add to action links array.
 */
class Plugins_Page {

	/**
	 * Add link to WooCommerce/Settings/Payments page in plugins.php list.
	 *
	 * @hooked plugin_action_links_{basename}
	 *
	 * @param array<int|string, string> $links_array The existing plugin links (usually "Deactivate"). May or may not be indexed with a string.
	 *
	 * @return array<int|string, string> The links to display below the plugin name on plugins.php.
	 */
	public function action_links( array $links_array ): array {

		$settings_url = admin_url( 'admin.php?page=wc-settings&tab=checkout' );
		array_unshift( $links_array, '<a href="' . $settings_url . '">Payment Gateways</a>' );

		return $links_array;
	}

}

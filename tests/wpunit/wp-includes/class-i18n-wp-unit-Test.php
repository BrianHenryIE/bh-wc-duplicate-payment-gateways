<?php
/**
 * Tests for I18n. Tests load_plugin_textdomain.
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 * @author  BrianHenryIE <BrianHenryIE@gmail.com>
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\WP_Includes;

/**
 * Class I18n_Test
 *
 * @see I18n
 */
class I18n_WP_Unit_Test extends \Codeception\TestCase\WPTestCase {

	/**
	 * Since WordPress 6.7, `load_plugin_textdomain()` only registers the path, and translations are loaded
	 * just-in-time, so check the path was registered.
	 *
	 * @see load_plugin_textdomain()
	 * @see \WP_Textdomain_Registry::set_custom_path()
	 */
	public function test_load_plugin_textdomain_function(): void {

		$i18n = new I18n();

		$i18n->load_plugin_textdomain();

		/** @var \WP_Textdomain_Registry $wp_textdomain_registry */
		global $wp_textdomain_registry;

		$custom_paths = ( new \ReflectionProperty( \WP_Textdomain_Registry::class, 'custom_paths' ) )->getValue( $wp_textdomain_registry );

		$this->assertArrayHasKey( 'bh-wc-duplicate-payment-gateways', $custom_paths );
		$this->assertStringEndsWith( '/bh-wc-duplicate-payment-gateways/languages', $custom_paths['bh-wc-duplicate-payment-gateways'] );
	}
}

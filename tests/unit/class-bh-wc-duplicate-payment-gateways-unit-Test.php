<?php
/**
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 * @author  BrianHenryIE <BrianHenryIE@gmail.com>
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\Admin\Admin_Assets;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\Admin\Plugins_Page;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\API_Interface;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\Settings_Interface;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\WooCommerce\Gateway_Duplicability;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\WP_Includes\I18n;
use WP_Mock\Matcher\AnyInstance;

/**
 * Class BH_WC_Duplicate_Payment_Gateways_Unit_Test
 *
 * @coversDefaultClass \BrianHenryIE\WC_Duplicate_Payment_Gateways\BH_WC_Duplicate_Payment_Gateways
 */
class BH_WC_Duplicate_Payment_Gateways_Unit_Test extends \Codeception\Test\Unit {

	protected function setup(): void {
		\WP_Mock::setUp();
	}

	/**
	 * This is required for `'times' => 1` to be verified.
	 */
	protected function tearDown(): void {
		parent::_tearDown();
		\WP_Mock::tearDown();
	}

	/**
	 * @covers ::set_locale
	 */
	public function test_set_locale_hooked(): void {

		\WP_Mock::expectActionAdded(
			'plugins_loaded',
			array( new AnyInstance( I18n::class ), 'load_plugin_textdomain' )
		);

		$api      = $this->makeEmpty( API_Interface::class );
		$settings = $this->makeEmpty( Settings_Interface::class );
		new BH_WC_Duplicate_Payment_Gateways( $api, $settings, new Gateway_Duplicability() );
	}

	/**
	 * @covers ::define_admin_hooks
	 */
	public function test_admin_hooks(): void {

		\WP_Mock::expectActionAdded(
			'admin_enqueue_scripts',
			array( new AnyInstance( Admin_Assets::class ), 'enqueue_styles' )
		);

		\WP_Mock::expectActionAdded(
			'admin_enqueue_scripts',
			array( new AnyInstance( Admin_Assets::class ), 'enqueue_scripts' )
		);

		$api      = $this->makeEmpty( API_Interface::class );
		$settings = $this->makeEmpty( Settings_Interface::class );
		new BH_WC_Duplicate_Payment_Gateways( $api, $settings, new Gateway_Duplicability() );
	}

	/**
	 * The plugin's action link filter uses the actual basename, e.g. when the plugin folder has been renamed.
	 *
	 * @covers ::define_plugins_page_hooks
	 */
	public function test_plugins_page_hooks_use_basename(): void {

		\WP_Mock::expectFilterAdded(
			'plugin_action_links_renamed-folder/bh-wc-duplicate-payment-gateways.php',
			array( new AnyInstance( Plugins_Page::class ), 'action_links' )
		);

		$api      = $this->makeEmpty( API_Interface::class );
		$settings = $this->makeEmpty(
			Settings_Interface::class,
			array(
				'get_plugin_basename' => 'renamed-folder/bh-wc-duplicate-payment-gateways.php',
			)
		);
		new BH_WC_Duplicate_Payment_Gateways( $api, $settings, new Gateway_Duplicability() );
	}
}

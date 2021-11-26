<?php
/**
 * @package BH_WC_Duplicate_Payment_Gateways_Unit_Name
 * @author  BrianHenryIE <BrianHenryIE@gmail.com>
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\Includes;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\Admin\Admin;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\API\API_Interface;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\API\Settings_Interface;
use WP_Mock\Matcher\AnyInstance;

/**
 * Class BH_WC_Duplicate_Payment_Gateways_Unit_Test
 *
 * @coversDefaultClass \BrianHenryIE\WC_Duplicate_Payment_Gateways\Includes\BH_WC_Duplicate_Payment_Gateways
 */
class BH_WC_Duplicate_Payment_Gateways_Unit_Test extends \Codeception\Test\Unit {

	protected function setup(): void {
		\WP_Mock::setUp();
	}

	// This is required for `'times' => 1` to be verified.
	protected function tearDown(): void {
		parent::_tearDown();
		\WP_Mock::tearDown();
	}

	/**
	 * @covers ::set_locale
	 */
	public function test_set_locale_hooked() {

		\WP_Mock::expectActionAdded(
			'plugins_loaded',
			array( new AnyInstance( I18n::class ), 'load_plugin_textdomain' )
		);

		$api      = $this->makeEmpty( API_Interface::class );
		$settings = $this->makeEmpty( Settings_Interface::class );
		new BH_WC_Duplicate_Payment_Gateways( $api, $settings );
	}

	/**
	 * @covers ::define_admin_hooks
	 */
	public function test_admin_hooks() {

		\WP_Mock::expectActionAdded(
			'admin_enqueue_scripts',
			array( new AnyInstance( Admin::class ), 'enqueue_styles' )
		);

		\WP_Mock::expectActionAdded(
			'admin_enqueue_scripts',
			array( new AnyInstance( Admin::class ), 'enqueue_scripts' )
		);

		$api      = $this->makeEmpty( API_Interface::class );
		$settings = $this->makeEmpty( Settings_Interface::class );
		new BH_WC_Duplicate_Payment_Gateways( $api, $settings );
	}

}

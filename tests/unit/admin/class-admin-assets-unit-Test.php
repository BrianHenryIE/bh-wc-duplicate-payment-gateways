<?php
/**
 * Tests for Admin.
 *
 * @see Admin
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 * @author Brian Henry <BrianHenryIE@gmail.com>
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\Admin;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\Settings_Interface;

/**
 * Class Admin_Test
 *
 * @coversDefaultClass \BrianHenryIE\WC_Duplicate_Payment_Gateways\Admin\Admin_Assets
 */
class Admin_Assets_Test extends \Codeception\Test\Unit {

	protected function setup(): void {
		\WP_Mock::setUp();
	}

	protected function tearDown(): void {
		parent::tearDown();
		\WP_Mock::tearDown();
	}

	/**
	 * Verifies enqueue_styles() calls wp_enqueue_style() with appropriate parameters.
	 * Verifies the .css file exists.
	 *
	 * @covers ::enqueue_styles
	 * @see wp_enqueue_style()
	 */
	public function test_enqueue_styles() {

		global $plugin_root_dir;

		$plugin_test_url = 'http://example.org/wp-content/plugins/bh-wc-duplicate-payment-gateways/';

		\WP_Mock::userFunction(
			'plugin_dir_url',
			array(
				'return' => $plugin_test_url,
			)
		);

		$css_file = $plugin_root_dir . '/assets/bh-wc-duplicate-payment-gateways-admin.css';
		$js_url   = $plugin_test_url . 'assets/bh-wc-duplicate-payment-gateways-admin.css';

		$plugin_name = 'bh-wc-duplicate-payment-gateways';

		\WP_Mock::userFunction(
			'wp_enqueue_style',
			array(
				'times' => 1,
				'args'  => array( $plugin_name, $js_url, array(), '1.0.0', 'all' ),
			)
		);

		$settings = $this->makeEmpty(
			Settings_Interface::class,
			array(
				'get_plugin_version' => '1.0.0',
			)
		);

		$admin = new Admin_Assets( $settings );

		$admin->enqueue_styles();

		$this->assertFileExists( $css_file );
	}

	/**
	 * Verifies enqueue_scripts() calls wp_enqueue_script() with appropriate parameters.
	 * Verifies the .js file exists.
	 *
	 * @covers ::enqueue_scripts
	 * @see wp_enqueue_script()
	 */
	public function test_enqueue_scripts() {

		global $plugin_root_dir;

		$plugin_test_url = 'http://example.org/wp-content/plugins/bh-wc-duplicate-payment-gateways/';

		\WP_Mock::userFunction(
			'plugin_dir_url',
			array(
				'return' => $plugin_test_url,
			)
		);

		$js_file = $plugin_root_dir . '/assets/bh-wc-duplicate-payment-gateways-admin.js';
		$js_url  = $plugin_test_url . 'assets/bh-wc-duplicate-payment-gateways-admin.js';

		$nonce = 'abc123';

		\WP_Mock::userFunction(
			'wp_create_nonce',
			array(
				'args'   => array( AJAX::NONCE_ACTION ),
				'return' => $nonce,
			)
		);

		\WP_Mock::userFunction(
			'admin_url',
			array(
				'args'       => array( 'admin-ajax.php' ),
				'return_arg' => 0,
			)
		);

		\WP_Mock::userFunction(
			'wp_json_encode',
			array(
				// 'args'  => array( \WP_Mock\Functions::type( 'array' ) ),
								'return' => '{"json"=>"json"}',
			)
		);

		\WP_Mock::userFunction(
			'wp_add_inline_script',
			array(
				'args' => array( 'bh-wc-duplicate-payment-gateways', \WP_Mock\Functions::type( 'string' ) ),
			)
		);

		$handle = 'bh-wc-duplicate-payment-gateways';

		$deps      = array( 'jquery' );
		$ver       = '1.0.0';
		$in_footer = true;

		\WP_Mock::userFunction(
			'wp_enqueue_script',
			array(
				'times' => 1,
				'args'  => array( $handle, $js_url, $deps, $ver, $in_footer ),
			)
		);

		$settings = $this->makeEmpty(
			Settings_Interface::class,
			array(
				'get_plugin_version' => '1.0.0',
			)
		);

		$admin = new Admin_Assets( $settings );

		$admin->enqueue_scripts();

		$this->assertFileExists( $js_file );
	}
}

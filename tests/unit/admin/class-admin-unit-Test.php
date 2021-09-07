<?php
/**
 * Tests for Admin.
 *
 * @see Admin
 *
 * @package bh-wc-duplicate-payment-gateways
 * @author Brian Henry <BrianHenryIE@gmail.com>
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways\Admin;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\API\Settings_Interface;

/**
 * Class Admin_Test
 *
 * @coversDefaultClass \BrianHenryIE\WC_Duplicate_Payment_Gateways\Admin\Admin
 */
class Admin_Test extends \Codeception\Test\Unit {

	protected function _before() {
		\WP_Mock::setUp();
	}

	// This is required for `'times' => 1` to be verified.
	protected function _tearDown() {
		parent::_tearDown();
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

		// Return any old url.
		\WP_Mock::userFunction(
			'plugin_dir_url',
			array(
				'return' => $plugin_root_dir . '/admin/',
			)
		);

		$css_file = $plugin_root_dir . '/admin/css/bh-wc-duplicate-payment-gateways-admin.css';

        $plugin_name = 'bh-wc-duplicate-payment-gateways';

		\WP_Mock::userFunction(
			'wp_enqueue_style',
			array(
				'times' => 1,
				'args'  => array( $plugin_name, $css_file, array(), '1.0.0', 'all' ),
			)
		);

        $settings = $this->makeEmpty( Settings_Interface::class,
        array(
            'get_plugin_version' => '1.0.0'
        ));

		$admin = new Admin( $settings );

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

		// Return any old url.
		\WP_Mock::userFunction(
			'plugin_dir_url',
			array(
				'return' => $plugin_root_dir . '/admin/',
			)
		);

        $nonce = 'abc123';

        \WP_Mock::userFunction(
            'wp_create_nonce',
            array(
                'args'  => array( AJAX::NONCE_ACTION ),
                'return' => $nonce,
            )
        );

        \WP_Mock::userFunction(
            'admin_url',
            array(
                'args'  => array( 'admin-ajax.php' ),
                'return_arg' => 0,
            )
        );

        \WP_Mock::userFunction(
            'wp_json_encode',
            array(
//                'args'  => array( \WP_Mock\Functions::type( 'array' ) ),
                'return' => '{"json"=>"json"}',
            )
        );

        \WP_Mock::userFunction(
            'wp_add_inline_script',
            array(
                'args'  => array( 'bh-wc-duplicate-payment-gateways', \WP_Mock\Functions::type( 'string' ) ),
            )
        );

		$handle    = 'bh-wc-duplicate-payment-gateways';
		$src       = $plugin_root_dir . '/admin/js/bh-wc-duplicate-payment-gateways-admin.js';
		$deps      = array( 'jquery' );
		$ver       = '1.0.0';
		$in_footer = true;

		\WP_Mock::userFunction(
			'wp_enqueue_script',
			array(
				'times' => 1,
				'args'  => array( $handle, $src, $deps, $ver, $in_footer ),
			)
		);

        $settings = $this->makeEmpty( Settings_Interface::class, array(
            'get_plugin_version' => '1.0.0'
        ));

		$admin = new Admin( $settings );


		$admin->enqueue_scripts();

		$this->assertFileExists( $src );
	}
}

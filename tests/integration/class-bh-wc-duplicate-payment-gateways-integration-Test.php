<?php
/**
 * Tests for BH_WC_Duplicate_Payment_Gateways main setup class. Tests the actions are correctly added.
 *
 * @package brianhenryie/bh-wc-duplicate-payment-gateways
 * @author  BrianHenryIE <BrianHenryIE@gmail.com>
 */

namespace BrianHenryIE\WC_Duplicate_Payment_Gateways;

use BrianHenryIE\WC_Duplicate_Payment_Gateways\Admin\Admin_Assets;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\Admin\Duplicate_Gateways_Page;
use BrianHenryIE\WC_Duplicate_Payment_Gateways\WP_Includes\I18n;

/**
 * Class Develop_Test
 *
 * @coversNothing
 */
class BH_WC_Duplicate_Payment_Gateways_Integration_Test extends \Codeception\TestCase\WPTestCase {

	/**
	 * Verify admin_enqueue_scripts action is correctly added for styles, at priority 10.
	 */
	public function test_action_admin_enqueue_scripts_styles(): void {

		$action_name       = 'admin_enqueue_scripts';
		$expected_priority = 10;
		$class_type        = Admin_Assets::class;
		$method_name       = 'enqueue_styles';

		$function_is_hooked = $this->is_function_hooked_on_action( $class_type, $method_name, $action_name, $expected_priority );

		$this->assertNotFalse( $function_is_hooked );
	}

	/**
	 * Verify admin_enqueue_scripts action is added for scripts, at priority 10.
	 */
	public function test_action_admin_enqueue_scripts_scripts(): void {

		$action_name       = 'admin_enqueue_scripts';
		$expected_priority = 10;
		$class_type        = Admin_Assets::class;
		$method_name       = 'enqueue_scripts';

		$function_is_hooked = $this->is_function_hooked_on_action( $class_type, $method_name, $action_name, $expected_priority );

		$this->assertNotFalse( $function_is_hooked );
	}

	/**
	 * Verify action to call load textdomain is added.
	 */
	public function test_action_plugins_loaded_load_plugin_textdomain(): void {

		$action_name       = 'plugins_loaded';
		$expected_priority = 10;
		$class_type        = I18n::class;
		$method_name       = 'load_plugin_textdomain';

		$function_is_hooked = $this->is_function_hooked_on_action( $class_type, $method_name, $action_name, $expected_priority );

		$this->assertNotFalse( $function_is_hooked );
	}


	/**
	 * The Duplicate Gateways page is added to the WooCommerce menu, after WooCommerce adds its menu.
	 */
	public function test_action_admin_menu_duplicate_gateways_page(): void {

		$function_is_hooked = $this->is_function_hooked_on_action( Duplicate_Gateways_Page::class, 'add_submenu_page', 'admin_menu', 60 );

		$this->assertNotFalse( $function_is_hooked );
	}

	/**
	 * The link to the Duplicate Gateways page is printed before WooCommerce / Settings / Payments.
	 */
	public function test_action_woocommerce_settings_checkout_link(): void {

		$function_is_hooked = $this->is_function_hooked_on_action( Duplicate_Gateways_Page::class, 'print_link_on_payments_settings', 'woocommerce_settings_checkout', 5 );

		$this->assertNotFalse( $function_is_hooked );
	}

	protected function is_function_hooked_on_action( string $class_type, string $method_name, string $action_name, int $expected_priority = 10 ): bool {

		global $wp_filter;

		$this->assertArrayHasKey( $action_name, $wp_filter, "$method_name definitely not hooked to $action_name" );

		$actions_hooked = $wp_filter[ $action_name ];

		$this->assertArrayHasKey( $expected_priority, $actions_hooked, "$method_name definitely not hooked to $action_name priority $expected_priority" );

		$hooked_method = null;
		foreach ( $actions_hooked[ $expected_priority ] as $action ) {
			$action_function = $action['function'];
			if ( is_array( $action_function ) ) {
				if ( $action_function[0] instanceof $class_type ) {
					if ( $method_name === $action_function[1] ) {
						$hooked_method = $action_function[1];
						break;
					}
				}
			}
		}

		$this->assertNotNull( $hooked_method, "No methods on an instance of $class_type hooked to $action_name" );

		$this->assertEquals( $method_name, $hooked_method, "Unexpected method name for $class_type class hooked to $action_name" );

		return true;
	}
}
